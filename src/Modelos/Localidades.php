<?php

namespace App\Modelos;

use PDO;

class Localidades {

    private $pdo;
    private $codigo_postal;
    private $provincia;
    private $nombre_localidad;

    public function __construct(
        PDO $pdo,
        string $codigo_postal = '',
        string $provincia = '',
        string $nombre_localidad = ''
    ) {
        $this->pdo = $pdo;
        $this->codigo_postal = $codigo_postal;
        $this->provincia = $provincia;
        $this->nombre_localidad = $nombre_localidad;
    }

    // Getters
    public function getCodigoPostal(): string {
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
    public function crearLocalidad(
        string $codigo_postal,
        string $provincia,
        string $nombre_localidad
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO localidades (codigo_postal, provincia, nombre_localidad)
            VALUES (:codigo_postal, :provincia, :nombre_localidad)
        ");
        return $stmt->execute([
            ':codigo_postal' => $codigo_postal,
            ':provincia' => $provincia,
            ':nombre_localidad' => $nombre_localidad
        ]);
    }

    public function actualizarLocalidad(
        string $codigo_postal,
        string $provincia,
        string $nombre_localidad
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE localidades
            SET provincia = :provincia, nombre_localidad = :nombre_localidad
            WHERE codigo_postal = :codigo_postal
        ");
        $stmt->execute([
            ':codigo_postal' => $codigo_postal,
            ':provincia' => $provincia,
            ':nombre_localidad' => $nombre_localidad
        ]);

    }

    public function eliminarLocalidad(string $codigo_postal): bool {
        $stmt = $this->pdo->prepare("DELETE FROM localidades WHERE codigo_postal = :codigo_postal");
        $stmt->execute([':codigo_postal' => $codigo_postal]);

    }

    public function traerLocalidades(): array {
        $stmt = $this->pdo->query("SELECT * FROM localidades");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traerLocalidadPorCP(string $codigo_postal): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM localidades WHERE codigo_postal = :codigo_postal");
        $stmt->execute([':codigo_postal' => $codigo_postal]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}

?>