<?php  

namespace App\Modelos;

use PDO;

class Tematica {
    
    private $pdo;
    private $id_tematica;
    private $id_categoria;
    private $nombre_t;
    private $descripcion_t;

    public function __construct(
        PDO $pdo,
        int $id_tematica = null,
        int $id_categoria = null,
        string $nombre_t = '',
        string $descripcion_t = ''
    ) {
        $this->pdo = $pdo;
        $this->id_tematica = $id_tematica;
        $this->id_categoria = $id_categoria;
        $this->nombre_t = $nombre_t;
        $this->descripcion_t = $descripcion_t;
    }

    // Getters
    public function getIdTematica(): ?int {
        return $this->id_tematica;
    }

    public function getIdCategoria(): ?int {
        return $this->id_categoria;
    }

    public function getNombreT(): string {
        return $this->nombre_t;
    }

    public function getDescripcionT(): string {
        return $this->descripcion_t;
    }

    // Setters
    public function setIdCategoria(int $id_categoria): void {
        $this->id_categoria = $id_categoria;
    }

    public function setNombreT(string $nombre_t): void {
        $this->nombre_t = $nombre_t;
    }

    public function setDescripcionT(string $descripcion_t): void {
        $this->descripcion_t = $descripcion_t;
    }


    public function crearTematica(
        string $nombre_t,
        string $descripcion_t
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO tematica (nombre_t, descripcion_t)
            VALUES (:nombre_t, :descripcion_t)
        ");

        return $stmt->execute([
            ':nombre_t' => $nombre_t,
            ':descripcion_t' => $descripcion_t
        ]);
    }

    public function actualizarTematica(
        int $id_tematica,
        string $nombre_t,
        string $descripcion_t
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE tematica 
            SET nombre_t = :nombre_t, descripcion_t = :descripcion_t
            WHERE id_tematica = :id_tematica
        ");

        $stmt->execute([
            ':id_tematica' => $id_tematica,
            ':nombre_t' => $nombre_t,
            ':descripcion_t' => $descripcion_t
        ]);

    }

    public function eliminarTematica(int $id_tematica): bool {
        $stmt = $this->pdo->prepare("DELETE FROM tematica WHERE id_tematica = :id_tematica");
        $stmt->execute([':id_tematica' => $id_tematica]);

    }

    public function traerTematicas(): array {
        $stmt = $this->pdo->query("SELECT * FROM tematica");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traerTematicaPorId(int $id_tematica): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM tematica WHERE id_tematica = :id_tematica");
        $stmt->execute([':id_tematica' => $id_tematica]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}

?>
