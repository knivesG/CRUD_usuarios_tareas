<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $email;
    public $fecha_nacimiento;
    public $fecha_creacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los usuarios
    function getAll() {
        $query = "SELECT id, nombre, email, fecha_creacion, fecha_nacimiento
                  FROM " . $this->table_name . " 
                  ORDER BY fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un usuario por ID
    function getById() {
        $query = "SELECT id, nombre, email, fecha_creacion, fecha_nacimiento
                    FROM " . $this->table_name . " 
                   WHERE id = :id 
                   LIMIT 1";
        
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nombre = $row['nombre'];
            $this->email = $row['email'];
            $this->fecha_creacion = $row['fecha_creacion'];
            $this->fecha_nacimiento = $row['fecha_nacimiento'];
            return true;
        }
        return false;
    }

    // Crear nuevo usuario
    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre=:nombre, 
                      email=:email, 
                      fecha_creacion = CURRENT_TIMESTAMP(),
                      fecha_nacimiento = :fecha_nacimiento";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Bind de parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Actualizar usuario
    function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, 
                      email = :email,
                      fecha_nacimiento = :fecha_nacimiento
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind de parámetros
        $stmt->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Eliminar usuario
    function delete() {
        // Primero desasignar tareas del usuario
        $query_unassign = "UPDATE tareas SET usuario_id = NULL WHERE usuario_id = :id";
        $stmt_unassign = $this->conn->prepare($query_unassign);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt_unassign->bindParam(':id', $this->id);

        $stmt_unassign->execute();
        
        // Eliminar usuario
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Validar email único
    function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE email = :email AND id != :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>