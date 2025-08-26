<?php

namespace App\Modelos;

use PDO;

class Localidades {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function crearLocalidad(string $codigo_postal, string $provincia, string $nombre_localidad): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO localidades (codigo_postal, provincia, nombre_localidad) 
             VALUES (:codigo_postal, :provincia, :nombre_localidad)"
        );
        return $stmt->execute([
            ':codigo_postal' => $codigo_postal,
            ':provincia' => $provincia,
            ':nombre_localidad' => $nombre_localidad
        ]);
    }

    public function actualizarLocalidad(string $codigo_postal, string $provincia, string $nombre_localidad): bool {
    $stmt = $this->pdo->prepare(
        "UPDATE localidades 
         SET provincia = :provincia, nombre_localidad = :nombre_localidad 
         WHERE codigo_postal = :codigo_postal"
    );
    return $stmt->execute([
        ':codigo_postal' => $codigo_postal,
        ':provincia' => $provincia,
        ':nombre_localidad' => $nombre_localidad
    ]);
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

    public function eliminarLocalidad(string $codigo_postal): bool {
        $stmt = $this->pdo->prepare("DELETE FROM localidades WHERE codigo_postal = :codigo_postal");
        return $stmt->execute([':codigo_postal' => $codigo_postal]);
    }
}

?>