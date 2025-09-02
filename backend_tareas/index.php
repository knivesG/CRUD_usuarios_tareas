<?php

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir configuración CORS
// include_once 'config/cors.php';

// Manejar CORS globalmente
// $cors = new CorsHandler();
// $cors->handle();

// Obtener la URI de la petición
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remover la parte del script name de la URI
$path = str_replace(dirname($script_name), '', $request_uri);
$path = trim($path, '/');

// Separar la ruta y los parámetros
$path_parts = explode('?', $path);
$route = $path_parts[0];

// Router simple
switch($route) {
    case '':
    case 'api':
        // Ruta raíz de la API
        http_response_code(200);
        echo json_encode(array(
            "mensaje" => "API Administrador de tareas",
            "endpoints" => array(
                "usuarios" => "/api/usuarios",
                "tareas" => "/api/tareas"
            ),
            "version" => "1.0.0",
            "autor" => "Erick"
        ));
        break;
        
    case 'api/usuarios':
        include_once 'api/usuarios.php';
        break;
        
    case 'api/tareas':
        include_once 'api/tareas.php';
        break;
        
    default:
        // Ruta no encontrada
        http_response_code(404);
        echo json_encode(array(
            "error" => "Endpoint no encontrado",
            "ruta_solicitada" => $route,
            "rutas_disponibles" => array(
                "/api/usuarios",
                "/api/tareas"
            )
        ));
        break;
}