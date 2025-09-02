<?php 

namespace App\Modelos;

use PDO;

class Usuarios {

    private $pdo;
    private $id_usuario;
    private $nombre_usuario;
    private $apellido;
    private $email;
    private $contrasena_hash; 
    private $telefono;
    private $direccion;
    private $codigo_postal;
    private $cuenta_verificada;
    private $fecha_registro;
    private $id_rol;
    
    public function __construct(
        PDO $pdo,
        string $nombre_usuario = '',
        string $apellido = '',
        string $email = '',
        string $contrasena_hash = '',
        string $direccion = '',
        string $telefono = '',
        string $codigo_postal = '',
        bool $cuenta_verificada = false,
        string $fecha_registro,
        int $id_usuario,
        int $id_rol
    ) {
        $this->pdo = $pdo;
        $this->nombre_usuario = $nombre_usuario;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->contrasena_hash = $contrasena_hash;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
        $this->codigo_postal = $codigo_postal;
        $this->cuenta_verificada = $cuenta_verificada;
        $this->fecha_registro = $fecha_registro;
        $this->id_usuario = $id_usuario;
        $this->id_rol = $id_rol;
    }

    // Getters
    public function getIdUsuario(): int {
        return $this->id_usuario;
    }

    public function getNombreUsuario(): string {
        return $this->nombre_usuario;
    }

    public function getApellido(): string {
        return $this->apellido;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getContrasenaHash(): string {
        return $this->contrasena_hash;
    }

    public function getTelefono(): string {
        return $this->telefono;
    }

    public function getDireccion(): string {
        return $this->direccion;
    }

    public function getCodigoPostal(): string {
        return $this->codigo_postal;
    }

    public function isCuentaVerificada(): bool {
        return $this->cuenta_verificada;
    }

    public function getFechaRegistro(): string {
        return $this->fecha_registro;
    }

    public function getIdRol(): int {
        return $this->id_rol;
    }

    //Setters    
    
    public function setNombreUsuario(string $nombre_usuario): void {
        $this->nombre_usuario = $nombre_usuario;
    }

    public function setApellido(string $apellido): void {
        $this->apellido = $apellido;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setContrasenaHash(string $contrasena_hash): void {
        $this->contrasena_hash = $contrasena_hash;
    }

    public function setTelefono(string $telefono): void {
        $this->telefono = $telefono;
    }

    public function setDireccion(string $direccion): void {
        $this->direccion = $direccion;
    }

    public function setCodigoPostal(string $codigo_postal): void {
        $this->codigo_postal = $codigo_postal;
    }

    public function setCuentaVerificada(bool $cuenta_verificada): void {
        $this->cuenta_verificada = $cuenta_verificada;
    }

    public function setFechaRegistro(string $fecha_registro): void {
        $this->fecha_registro = $fecha_registro;
    }

    public function setIdRol(int $id_rol): void {
        $this->id_rol = $id_rol;
    }


    public function crearUsuario(
        $nombre_usuario,
        $apellido,
        $email,
        $contrasena_hash,
        $direccion,
        $telefono,
        $codigo_postal,
        $cuenta_verificada = false,
        $fecha_registro = null,
        $id_rol = null
    ) {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios 
            (nombre_usuario, apellido, email, contrasena_hash, direccion, telefono, codigo_postal, cuenta_verificada, fecha_registro, id_rol)
            VALUES 
            (:nombre_usuario, :apellido, :email, :contrasena_hash, :direccion, :telefono, :codigo_postal, :cuenta_verificada, :fecha_registro, :id_rol)
        ");

        return $stmt->execute([
            ':nombre_usuario' => $nombre_usuario,
            ':apellido' => $apellido,
            ':email' => $email,
            ':contrasena_hash' => $contrasena_hash,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':codigo_postal' => $codigo_postal,
            ':cuenta_verificada' => $cuenta_verificada,
            ':fecha_registro' => $fecha_registro,
            ':id_rol' => $id_rol
        ]);
    }

    public function actualizarUsuario(
        $id_usuario,
        $nombre_usuario,
        $apellido,
        $email,
        $contrasena_hash,
        $direccion,
        $telefono,
        $codigo_postal,
        $cuenta_verificada,
        $fecha_registro,
        $id_rol
    ) {
        $stmt = $this->pdo->prepare("
            UPDATE usuarios 
            SET nombre_usuario = :nombre_usuario, apellido = :apellido, email = :email, contrasena_hash = :contrasena_hash,
                direccion = :direccion, telefono = :telefono, codigo_postal = :codigo_postal,
                cuenta_verificada = :cuenta_verificada, fecha_registro = :fecha_registro, id_rol = :id_rol
            WHERE id_usuario = :id_usuario
        ");
        return $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':nombre_usuario' => $nombre_usuario,
            ':apellido' => $apellido,
            ':email' => $email,
            ':contrasena_hash' => $contrasena_hash,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':codigo_postal' => $codigo_postal,
            ':cuenta_verificada' => $cuenta_verificada,
            ':fecha_registro' => $fecha_registro,
            ':id_rol' => $id_rol
        ]);
    }

    public function eliminarUsuario(int $id_usuario): bool {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
        return $stmt->execute([':id_usuario' => $id_usuario]);
    }

    public function traerUsuarios() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>