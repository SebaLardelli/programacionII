<?php  

namespace App\Modelos;

use PDO;

class Productos {

    private $pdo;
    private $id_producto;
    private $nombre_p;
    private $descripcion_p;
    private $precio;
    private $stock;
    private $id_estado_p;
    private $tamaño;
    private $id_categoria;
    private $imagen_url;

    public function __construct(
        PDO $pdo,
        int $id_producto,
        string $nombre_p = '',
        string $descripcion_p = '',
        float $precio = 0.0,
        int $stock = 0,
        int $id_estado_p,
        string $tamaño = '',
        int $id_categoria,
        string $imagen_url = ''
    ) {
        $this->pdo = $pdo;
        $this->id_producto = $id_producto;
        $this->nombre_p = $nombre_p;
        $this->descripcion_p = $descripcion_p;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->id_estado_p = $id_estado_p;
        $this->tamaño = $tamaño;
        $this->id_categoria = $id_categoria;
        $this->imagen_url = $imagen_url;
    }

    // Getters
    public function getIdProducto(): int {
        return $this->id_producto;
    }

    public function getNombreP(): string {
        return $this->nombre_p;
    }

    public function getDescripcionP(): string {
        return $this->descripcion_p;
    }

    public function getPrecio(): float {
        return $this->precio;
    }

    public function getStock(): int {
        return $this->stock;
    }

    public function getIdEstadoP(): ?int {
        return $this->id_estado_p;
    }

    public function getTamaño(): string {
        return $this->tamaño;
    }

    public function getIdCategoria(): ?int {
        return $this->id_categoria;
    }

    public function getImagenUrl(): string {
        return $this->imagen_url;
    }

    // Setters
    public function setNombreP(string $nombre_p): void {
        $this->nombre_p = $nombre_p;
    }

    public function setDescripcionP(string $descripcion_p): void {
        $this->descripcion_p = $descripcion_p;
    }

    public function setPrecio(float $precio): void {
        $this->precio = $precio;
    }

    public function setStock(int $stock): void {
        $this->stock = $stock;
    }

    public function setIdEstadoP(int $id_estado_p): void {
        $this->id_estado_p = $id_estado_p;
    }

    public function setTamaño(string $tamaño): void {
        $this->tamaño = $tamaño;
    }

    public function setIdCategoria(int $id_categoria): void {
        $this->id_categoria = $id_categoria;
    }

    public function setImagenUrl(string $imagen_url): void {
        $this->imagen_url = $imagen_url;
    }

    public function crearProducto(
        string $nombre_p,
        string $descripcion_p,
        float $precio,
        int $stock,
        int $id_estado_p,
        string $tamaño,
        int $id_categoria,
        string $imagen_url
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO producto (nombre_p, descripcion_p, precio, stock, id_estado_p, tamaño, id_categoria, imagen_url)
            VALUES (:nombre_p, :descripcion_p, :precio, :stock, :id_estado_p, :tamaño, :id_categoria, :imagen_url)
        ");

        return $stmt->execute([
            ':nombre_p' => $nombre_p,
            ':descripcion_p' => $descripcion_p,
            ':precio' => $precio,
            ':stock' => $stock,
            ':id_estado_p' => $id_estado_p,
            ':tamaño' => $tamaño,
            ':id_categoria' => $id_categoria,
            ':imagen_url' => $imagen_url
        ]);
    }

    public function actualizarProducto(
        int $id_producto,
        string $nombre_p,
        string $descripcion_p,
        float $precio,
        int $stock,
        int $id_estado_p,
        string $tamaño,
        int $id_categoria,
        string $imagen_url
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE producto 
            SET nombre_p = :nombre_p, descripcion_p = :descripcion_p, precio = :precio, stock = :stock,
                id_estado_p = :id_estado_p, tamaño = :tamaño, id_categoria = :id_categoria, imagen_url = :imagen_url
            WHERE id_producto = :id_producto
        ");

        return $stmt->execute([
            ':id_producto' => $id_producto,
            ':nombre_p' => $nombre_p,
            ':descripcion_p' => $descripcion_p,
            ':precio' => $precio,
            ':stock' => $stock,
            ':id_estado_p' => $id_estado_p,
            ':tamaño' => $tamaño,
            ':id_categoria' => $id_categoria,
            ':imagen_url' => $imagen_url
        ]);
    }

    public function eliminarProducto(int $id_producto): bool {
        $stmt = $this->pdo->prepare("DELETE FROM producto WHERE id_producto = :id_producto");
        return $stmt->execute([':id_producto' => $id_producto]);
    }

    public function traerProductos(): array {
        $stmt = $this->pdo->query("SELECT * FROM producto");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traerProductoPorId(int $id_producto): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM producto WHERE id_producto = :id_producto");
        $stmt->execute([':id_producto' => $id_producto]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}

?>