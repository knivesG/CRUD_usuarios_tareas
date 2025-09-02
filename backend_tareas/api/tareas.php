<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../models/tarea.php';

// Obtener conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar modelo de tarea
$task = new Tarea($db);

// Obtener método HTTP y acción
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

// Logs para debugging
error_log("Método recibido: " . $method);
error_log("URL: " . $_SERVER['REQUEST_URI']);
error_log("ID recibido: " . ($id ?? 'null'));

// Router para tareas
switch($method) {
    case 'GET':
        handleGetTasks();
        break;
    case 'POST':
        handlePostTasks();
        break;
    case 'PUT':
        if($action === 'asignar') {
            handleAssignTask();
        } elseif($action === 'desasignar') {
            handleUnassignTask();
        } else {
            handlePutTasks();
        }
        break;
    case 'DELETE':
        handleDeleteTasks();
        break;
    default:
        http_response_code(405);
        echo json_encode(array("mensaje" => "Método no permitido"));
        break;
}

function handleGetTasks() {
    global $task;
    
    $id = $_GET['id'] ?? null;
    $usuario_id = $_GET['usuario_id'] ?? null;
    
    if($id) {
        // Obtener tarea específica
        $task->id = $id;
        
        if($task->getById()) {
            $task_arr = array(
                    "id" => intval($task->id),
                    "titulo" => $task->titulo ?? '',
                    "descripcion" => $task->descripcion ?? '',
                    "estado" => (!empty($task->estado)) ? $task->estado : 'pendiente',
                    "entrega_fecha" => (!empty($task->entrega_fecha)) ? $task->entrega_fecha : null,
                    "usuario_id" => (!empty($task->usuario_id)) ? intval($task->usuario_id) : null
                );
            
            http_response_code(200);
            echo json_encode($task_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("mensaje" => "Tarea no encontrada"));
        }
    } else {
        // Obtener tareas de un usuario específico
        $stmt = $task->getAll();
        $tasks_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $task_item = array(
                "id" => intval($id),
                "titulo" => (!empty($titulo)) ? $titulo : 'Sin título',
                "descripcion" => $descripcion ?? '',
                "estado" => (!empty($estado)) ? $estado : 'pendiente',
                "entrega_fecha" => (!empty($entrega_fecha)) ? $entrega_fecha : null,
                "usuario_id" => (!empty($usuario_id)) ? intval($usuario_id) : null,
                "usuario_nombre" => $usuario_nombre ?? ''
            );
            
            array_push($tasks_arr, $task_item);
        }
        
        http_response_code(200);
        echo json_encode($tasks_arr);
    }
}

function handlePostTasks() {
    global $task;
    
    // Obtener datos del cuerpo de la petición
    $data = json_decode(file_get_contents("php://input"));
    
    // Validar datos requeridos
    if(empty($data->titulo) || empty($data->estado)) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "Título y estado son requeridos"));
        return;
    }
    
    // Validar estado
    $estados_validos = ['pendiente', 'en_progreso', 'completada'];
    if(!in_array($data->estado, $estados_validos)) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "Estado inválido"));
        return;
    }
    
    // Asignar valores
    $task->titulo = $data->titulo;
    $task->descripcion = $data->descripcion ?? '';
    $task->estado = $data->estado;
    $task->entrega_fecha = $data->entrega_fecha ?? null;
    $task->usuario_id = $data->usuario_id ?? null;
    
    // Crear tarea
    if($task->create()) {
        http_response_code(201);
        echo json_encode(array(
            "mensaje" => "Tarea creada exitosamente",
            "id" => $task->id,
            "titulo" => $task->titulo,
            "estado" => $task->estado
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("mensaje" => "No se pudo crear la tarea"));
    }
}

function handlePutTasks() {
    global $task;
    
    // Obtener ID de la URL
    $id = $_GET['id'] ?? null;
    
    if(!$id) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "ID de tarea requerido"));
        return;
    }
    
    // Obtener datos del cuerpo de la petición
    $data = json_decode(file_get_contents("php://input"));
    
    // Validar datos requeridos
    if(empty($data->titulo) || empty($data->estado)) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "Título y estado son requeridos"));
        return;
    }
    
    // Validar estado
    $estados_validos = ['pendiente', 'en_progreso', 'completada'];
    if(!in_array($data->estado, $estados_validos)) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "Estado inválido"));
        return;
    }
    
    // Asignar valores
    $task->id = $id;
    
    // Verificar si la tarea existe
    if(!$task->getById()) {
        http_response_code(404);
        echo json_encode(array("mensaje" => "Tarea no encontrada"));
        return;
    }
    
    // Actualizar valores
    $task->titulo = $data->titulo;
    $task->descripcion = $data->descripcion ?? '';
    $task->estado = $data->estado;
    $task->entrega_fecha = $data->entrega_fecha ?? null;
    $task->usuario_id = $data->usuario_id ?? null;
    
    // Actualizar tarea
    if($task->update()) {
        http_response_code(200);
        echo json_encode(array(
            "mensaje" => "Tarea actualizada exitosamente",
            "id" => $task->id,
            "titulo" => $task->titulo,
            "estado" => $task->estado
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("mensaje" => "No se pudo actualizar la tarea"));
    }
}

function handleDeleteTasks() {
    global $task;
    $id = $_GET['id'] ?? null;

    if(!$id) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "ID de tarea requerido"));
        return;
    }
    
    $task->id = $id;
    
    if(!$task->getById()) {
        http_response_code(404);
        echo json_encode(array("mensaje" => "Tarea no encontrada"));
        return;
    }
    
    if($task->delete()) {
        http_response_code(200);
        echo json_encode(array("mensaje" => "Tarea eliminada exitosamente"));
    } else {
        http_response_code(503);
        echo json_encode(array("mensaje" => "No se pudo eliminar la tarea"));
    }
}

function handleAssignTask() {
    global $task;
    
    $id = $_GET['id'] ?? null;
    
    if(!$id) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "ID de tarea requerido"));
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"));
    
    if(empty($data->usuario_id)) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "ID de usuario requerido"));
        return;
    }
    
    $task->id = $id;
    
    if($task->assignToUser($data->usuario_id)) {
        http_response_code(200);
        echo json_encode(array("mensaje" => "Tarea asignada exitosamente"));
    } else {
        http_response_code(503);
        echo json_encode(array("mensaje" => "No se pudo asignar la tarea"));
    }
}

function handleUnassignTask() {
    global $task;
    
    $id = $_GET['id'] ?? null;
    
    if(!$id) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "ID de tarea requerido"));
        return;
    }
    
    $task->id = $id;
    
    if($task->unassignFromUser()) {
        http_response_code(200);
        echo json_encode(array("mensaje" => "Tarea desasignada exitosamente"));
    } else {
        http_response_code(503);
        echo json_encode(array("mensaje" => "No se pudo desasignar la tarea"));
    }
}
?>