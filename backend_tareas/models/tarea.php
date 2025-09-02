<?php
 class Tarea {
    private $conn;
    private $table_name = "tareas";

    public $id;
    public $titulo;
    public $descripcion;
    public $estado;
    public $entrega_fecha;
    public $usuario_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las tareas
    function getAll() {
        $query = "SELECT t.id, t.titulo, t.descripcion, t.estado, 
                         t.entrega_fecha, t.usuario_id,
                         u.nombre as usuario_nombre
                  FROM " . $this->table_name . " t
                  LEFT JOIN usuarios u ON t.usuario_id = u.id
                  ORDER BY t.entrega_fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener tarea por ID
    function getById() {
        $query = "SELECT t.id, t.titulo, t.descripcion, t.estado, 
                         t.entrega_fecha, t.usuario_id,
                         u.nombre as usuario_nombre
                  FROM " . $this->table_name . " t
                  LEFT JOIN usuarios u ON t.usuario_id = u.id
                  WHERE t.id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->titulo = $row['titulo'];
            $this->descripcion = $row['descripcion'];
            $this->estado = $row['estado'];
            $this->entrega_fecha = $row['entrega_fecha'];
            $this->usuario_id = $row['usuario_id'];
            return true;
        }
        return false;
    }

    // Crear nueva tarea
    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET titulo= :titulo, 
                      descripcion= :descripcion, 
                      estado=:estado,
                      entrega_fecha= :entrega_fecha, 
                      usuario_id=:usuario_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Bind de parámetros
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":entrega_fecha", $this->entrega_fecha);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Actualizar tarea
    function update() {
        $query = "UPDATE " . $this->table_name . " 
                     SET titulo = :titulo, 
                         descripcion = :descripcion, 
                         estado = :estado, 
                         entrega_fecha = :entrega_fecha,
                         usuario_id = :usuario_id
                   WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind de parámetros
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':entrega_fecha', $this->entrega_fecha);
        $stmt->bindParam(':usuario_id', $this->usuario_id);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Eliminar tarea
    function delete() {
        // Validar que el ID sea válido
        if(empty($this->id) || !is_numeric($this->id)) {
            return false;
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Asignar tarea a usuario
    function assignToUser($usuario_id) {
        $query = "UPDATE " . $this->table_name . "
                  SET usuario_id = :usuario_id 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Desasignar tarea de usuario
    function unassignFromUser() {
        $query = "UPDATE " . $this->table_name . " 
                  SET usuario_id = NULL 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Obtener tareas por usuario
    function getByUser($usuario_id) {
        $query = "SELECT t.id, t.titulo, t.descripcion, t.estado,
                         t.entrega_fecha, t.usuario_id,
                         u.nombre as usuario_nombre
                  FROM " . $this->table_name . " t
                  LEFT JOIN usuarios u ON t.usuario_id = u.id
                  WHERE t.usuario_id = :usuario_id
                  ORDER BY t.entrega_fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?>