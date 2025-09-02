<?php 

namespace App\Modelos;

use PDO;

class Categorias {
    private $pdo;
    private $id_categoria;
    private $nombre_c;
    private $descripcion_c;

    public function __construct(
        PDO $pdo,
        string $nombre_c = '',
        string $descripcion_c = '',
        int $id_categoria 
    ) {
        $this->pdo = $pdo;
        $this->nombre_c = $nombre_c;
        $this->descripcion_c = $descripcion_c;
        $this->id_categoria = $id_categoria;
    }

    // Getters
    public function getIdCategoria(): int {
        return $this->id_categoria;
    }

    public function getNombreC(): string {
        return $this->nombre_c;
    }

    public function getDescripcionC(): string {
        return $this->descripcion_c;
    }

    // Setters
    public function setNombreC(string $nombre_c): void {
        $this->nombre_c = $nombre_c;
    }

    public function setDescripcionC(string $descripcion_c): void {
        $this->descripcion_c = $descripcion_c;
    }

    public function crearCategoria(string $nombre_c, string $descripcion_c): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO categoria (nombre_c, decripcion_c) 
            VALUES (:nombre_c, :descripcion_c)
        ");
        return $stmt->execute([
            ':nombre_c' => $nombre_c,
            ':descripcion_c' => $descripcion_c
        ]);
    }

    public function actualizarCategoria(int $id_categoria, string $nombre_c, string $descripcion_c): bool {
        $stmt = $this->pdo->prepare("
            UPDATE categoria 
            SET nombre_c = :nombre_c, decripcion_c = :descripcion_c
            WHERE id_categoria = :id_categoria
        ");
        return $stmt->execute([
            ':id_categoria' => $id_categoria,
            ':nombre_c' => $nombre_c,
            ':descripcion_c' => $descripcion_c
        ]);
    }

    public function eliminarCategoria(int $id_categoria): bool {
        $stmt = $this->pdo->prepare("DELETE FROM categoria WHERE id_categoria = :id_categoria");
        return $stmt->execute([':id_categoria' => $id_categoria]);
    }

    public function traerCategorias(): array {
        $stmt = $this->pdo->query("SELECT * FROM categoria");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
