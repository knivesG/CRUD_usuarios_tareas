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
// Incluir archivos necesarios
include_once '../config/database.php';
include_once '../models/usuario.php';

// Obtener conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar modelo de usuario
$user = new User($db);

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

error_log("Método recibido: " . $method);
error_log("URL: " . $_SERVER['REQUEST_URI']);
error_log("ID recibido: " . ($id ?? 'null'));
// Router simple basado en método HTTP
switch($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(array("mensaje" => "Método no permitido"));
        break;
}

function handleGet() {
    global $user;
    
    // Verificar si se solicita un usuario específico
    $id = $_GET['id'] ?? null;
    
    if($id) {
        // Obtener usuario específico
        $user->id = $id;
        
        if($user->getById()) {
            $user_arr = array(
                "id" => $user->id,
                "nombre" => $user->nombre,
                "email" => $user->email,
                "fecha_creacion" => (!empty($user->fecha_creacion))? $user->fecha_creacion : null,
                "fecha_nacimiento" => (!empty($user->fecha_nacimiento))? $user->fecha_nacimiento : null,
            );
            
            http_response_code(200);
            echo json_encode($user_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("mensaje" => "Usuario no encontrado"));
        }
    } else {
        // Obtener todos los usuarios
        $stmt = $user->getAll();
        $num = $stmt->rowCount();
        
        if($num > 0) {
            $users_arr = array();
            $users_arr["usuarios"] = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                
                $user_item = array(
                    "id" => intval($id),
                    "nombre" => (!empty($nombre)) ? $nombre : 'Sin nombre',
                    "email" => (!empty($email)) ? $email : 'Sin email',
                    "fecha_creacion" => (!empty($fecha_creacion)) ? $fecha_creacion : null,
                    "fecha_nacimiento" => (!empty($fecha_nacimiento)) ? $fecha_nacimiento : null,
                );
                
                array_push($users_arr["usuarios"], $user_item);
            }
            
            http_response_code(200);
            echo json_encode($users_arr["usuarios"]);
        } else {
            http_response_code(200);
            echo json_encode(array());
        }
    }
}

function handlePost() {
    global $user;
    
    // Obtener datos del cuerpo de la petición
    $data = json_decode(file_get_contents("php://input"));
    
    // Validar datos requeridos
    if(empty($data->nombre) || empty($data->email)) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "Nombre y email son requeridos"));
        return;
    }
    
    // Validar formato de email
    if(!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "Formato de email inválido"));
        return;
    }
    
    // Asignar valores
    $user->nombre = $data->nombre ?? '';
    $user->email = $data->email ?? '';
    $user->fecha_creacion = $data->fecha_creacion ?? null;
    $user->fecha_nacimiento = $data->fecha_nacimiento ?? null;
    
    // Verificar si el email ya existe
    if($user->emailExists()) {
        http_response_code(409);
        echo json_encode(array("mensaje" => "El email ya está registrado"));
        return;
    }
    
    // Crear usuario
    if($user->create()) {
        http_response_code(201);
        echo json_encode(array(
            "mensaje" => "Usuario creado exitosamente",
            "id" => $user->id,
            "nombre" => $user->nombre,
            "email" => $user->email,
            "fecha_nacimiento" => $user ->fecha_nacimiento
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("mensaje" => "No se pudo crear el usuario"));
    }
}

function handlePut() {
    global $user;
    
    // Obtener ID de la URL
    $id = $_GET['id'] ?? null;
    
    if(!$id) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "ID de usuario requerido"));
        return;
    }
    
    // Obtener datos del cuerpo de la petición
    $data = json_decode(file_get_contents("php://input"));
    
    // Validar datos requeridos
    if(empty($data->nombre) || empty($data->email)) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "Nombre y email son requeridos"));
        return;
    }
    
    // Validar formato de email
    if(!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "Formato de email inválido"));
        return;
    }
    
    // Asignar valores
    $user->id = $id;
    $user->nombre = $data->nombre ?? '';
    $user->email = $data->email ?? '';
    $user->fecha_creacion = $data->fecha_creacion ?? null;
    $user->fecha_nacimiento = $data->fecha_nacimiento ?? null;
    
    // Verificar si el usuario existe
    if(!$user->getById()) {
        http_response_code(404);
        echo json_encode(array("mensaje" => "Usuario no encontrado"));
        return;
    }
    
    // Reasignar valores después de getById
    $user->nombre = $data->nombre ?? '';
    $user->email = $data->email ?? '';
    $user->fecha_creacion = $data->fecha_creacion ?? null;
    $user->fecha_nacimiento = $data->fecha_nacimiento ?? null;

    // Verificar si el email ya existe (excepto para este usuario)
    if($user->emailExists()) {
        http_response_code(409);
        echo json_encode(array("mensaje" => "El email ya está registrado"));
        return;
    }
    
    // Actualizar usuario
    if($user->update()) {
        http_response_code(200);
        echo json_encode(array(
            "mensaje" => "Usuario actualizado exitosamente",
            "id" => $user->id,
            "nombre" => $user->nombre,
            "email" => $user->email,
            "fecha_creacion" => $user->fecha_creacion,
            "fecha_nacimiento" => $user->fecha_nacimiento
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("mensaje" => "No se pudo actualizar el usuario"));
    }
}

function handleDelete() {
    global $user;
    
    // Obtener ID de la URL
    $id = $_GET['id'] ?? null;
    
    if(!$id) {
        http_response_code(400);
        echo json_encode(array("mensaje" => "ID de usuario requerido"));
        return;
    }
    
    $user->id = $id;
    
    // Verificar si el usuario existe
    if(!$user->getById()) {
        http_response_code(404);
        echo json_encode(array("mensaje" => "Usuario no encontrado"));
        return;
    }
    
    // Eliminar usuario
    if($user->delete()) {
        http_response_code(200);
        echo json_encode(array("mensaje" => "Usuario eliminado exitosamente"));
    } else {
        http_response_code(503);
        echo json_encode(array("mensaje" => "No se pudo eliminar el usuario"));
    }
}

?>