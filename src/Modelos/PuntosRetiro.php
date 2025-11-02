<?php  

namespace App\Modelos;

use PDO;

class PuntosRetiro {
    
    private $pdo;
    private $id_punto_retiro;
    private $nombre_punto;
    private $direccion;
    private $horarios;
    private $codigo_postal;

    public function __construct(
        PDO $pdo,
        int $id_punto_retiro = null,
        string $nombre_punto = '',
        string $direccion = '',
        string $horarios = '',
        string $codigo_postal = ''
    ) {
        $this->pdo = $pdo;
        $this->id_punto_retiro = $id_punto_retiro;
        $this->nombre_punto = $nombre_punto;
        $this->direccion = $direccion;
        $this->horarios = $horarios;
        $this->codigo_postal = $codigo_postal;
    }

    // Getters
    public function getIdPuntoRetiro(): ?int {
        return $this->id_punto_retiro;
    }

    public function getNombrePunto(): string {
        return $this->nombre_punto;
    }

    public function getDireccion(): string {
        return $this->direccion;
    }

    public function getHorarios(): string {
        return $this->horarios;
    }

    public function getCodigoPostal(): string {
        return $this->codigo_postal;
    }

    // Setters
    public function setNombrePunto(string $nombre_punto): void {
        $this->nombre_punto = $nombre_punto;
    }

    public function setDireccion(string $direccion): void {
        $this->direccion = $direccion;
    }

    public function setHorarios(string $horarios): void {
        $this->horarios = $horarios;
    }

    public function setCodigoPostal(string $codigo_postal): void {
        $this->codigo_postal = $codigo_postal;
    }


    public function crearPuntoRetiro(
        string $nombre_punto,
        string $direccion,
        string $horarios,
        string $codigo_postal
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO punto_retiro (nombre_punto, direccion, horarios, codigo_postal)
            VALUES (:nombre_punto, :direccion, :horarios, :codigo_postal)
        ");

        return $stmt->execute([
            ':nombre_punto' => $nombre_punto,
            ':direccion' => $direccion,
            ':horarios' => $horarios,
            ':codigo_postal' => $codigo_postal
        ]);
    }

    public function actualizarPuntoRetiro(
        int $id_punto_retiro,
        string $nombre_punto,
        string $direccion,
        string $horarios,
        string $codigo_postal
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE punto_retiro 
            SET nombre_punto = :nombre_punto, direccion = :direccion, horarios = :horarios, codigo_postal = :codigo_postal
            WHERE id_punto_retiro = :id_punto_retiro
        ");

        return $stmt->execute([
            ':id_punto_retiro' => $id_punto_retiro,
            ':nombre_punto' => $nombre_punto,
            ':direccion' => $direccion,
            ':horarios' => $horarios,
            ':codigo_postal' => $codigo_postal
        ]);
    }

    public function eliminarPuntoRetiro(int $id_punto_retiro): bool {
        $stmt = $this->pdo->prepare("DELETE FROM punto_retiro WHERE id_punto_retiro = :id_punto_retiro");
        return $stmt->execute([':id_punto_retiro' => $id_punto_retiro]);
    }

    public function traerPuntosRetiro(): array {
        $stmt = $this->pdo->query("SELECT * FROM punto_retiro");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traerPuntoRetiroPorId(int $id_punto_retiro): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM punto_retiro WHERE id_punto_retiro = :id_punto_retiro");
        $stmt->execute([':id_punto_retiro' => $id_punto_retiro]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}

?>
