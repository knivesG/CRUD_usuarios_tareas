<?php
class CorsHandler {
    private $allowed_origins = [
        'http://localhost:4200',    // Angular development
        'http://127.0.0.1:4200',
        // 'https://tu-dominio.com'    // Producción
    ];
    
    public function handle() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Verificar origen permitido
        if (in_array($origin, $this->allowed_origins) || $origin === '') {
            header('Access-Control-Allow-Origin: ' . ($origin ?: 'http://localhost:4200'));
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        
        // Manejar preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
}
?>