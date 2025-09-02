<?php

namespace App\Modelos;

use PDO;

class Localidades {

    private PDO $pdo;
    private string $codigo_postal;
    private string $provincia;
    private string $nombre_localidad;

    public function __construct(PDO $pdo, string $codigo_postal, string $provincia = '', string $nombre_localidad = '') {
        $this->pdo = $pdo;
        $this->codigo_postal = $codigo_postal;
        $this->provincia = $provincia;
        $this->nombre_localidad = $nombre_localidad;

    }

    // Getters
    public function getCodigoPostal(): ?string {
        return $this->codigo_postal;
    }

    public function getProvincia(): string {
        return $this->provincia;
    }

    public function getNombreLocalidad(): string {
        return $this->nombre_localidad;
    }

    // Setters
    public function setCodigoPostal(string $codigo_postal): void {
        $this->codigo_postal = $codigo_postal;
    }

    public function setProvincia(string $provincia): void {
        $this->provincia = $provincia;
    }

    public function setNombreLocalidad(string $nombre_localidad): void {
        $this->nombre_localidad = $nombre_localidad;
    }

    // CRUD
    public function crear(): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO localidades (codigo_postal, provincia, nombre_localidad) 
             VALUES (:codigo_postal, :provincia, :nombre_localidad)"
        );
        return $stmt->execute([
            ':codigo_postal' => $this->codigo_postal,
            ':provincia' => $this->provincia,
            ':nombre_localidad' => $this->nombre_localidad
        ]);
    }

    public function actualizar(): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE localidades 
             SET provincia = :provincia, nombre_localidad = :nombre_localidad 
             WHERE codigo_postal = :codigo_postal"
        );
        return $stmt->execute([
            ':codigo_postal' => $this->codigo_postal,
            ':provincia' => $this->provincia,
            ':nombre_localidad' => $this->nombre_localidad
        ]);
    }

    public function eliminar(): bool {
        $stmt = $this->pdo->prepare("DELETE FROM localidades WHERE codigo_postal = :codigo_postal");
        return $stmt->execute([':codigo_postal' => $this->codigo_postal]);
    }

}
