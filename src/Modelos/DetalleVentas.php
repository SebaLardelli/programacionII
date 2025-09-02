<?php 

namespace App\Modelos;

use PDO;

class DetalleVentas {

    private $pdo;
    private $id_fila;
    private $id_venta;
    private $id_carrito;
    private $precio_unitario;
    private $importe_total;
    private $id_producto;
    private $cantidad;

    public function __construct(
        PDO $pdo,
        int $id_fila,
        int $id_venta ,
        int $id_carrito,
        float $precio_unitario = 0.0,
        float $importe_total = 0.0,
        int $id_producto ,
        int $cantidad = 0
    ) {
        $this->pdo = $pdo;
        $this->id_fila = $id_fila;
        $this->id_venta = $id_venta;
        $this->id_carrito = $id_carrito;
        $this->precio_unitario = $precio_unitario;
        $this->importe_total = $importe_total;
        $this->id_producto = $id_producto;
        $this->cantidad = $cantidad;
    }

    // Getters
    public function getIdFila(): int {
        return $this->id_fila;
    }

    public function getIdVenta(): int {
        return $this->id_venta;
    }

    public function getIdCarrito(): ?int {
        return $this->id_carrito;
    }

    public function getPrecioUnitario(): float {
        return $this->precio_unitario;
    }

    public function getImporteTotal(): float {
        return $this->importe_total;
    }

    public function getIdProducto(): int {
        return $this->id_producto;
    }

    public function getCantidad(): int {
        return $this->cantidad;
    }

    // Setters
    public function setIdVenta(int $id_venta): void {
        $this->id_venta = $id_venta;
    }

    public function setIdCarrito(int $id_carrito): void {
        $this->id_carrito = $id_carrito;
    }

    public function setPrecioUnitario(float $precio_unitario): void {
        $this->precio_unitario = $precio_unitario;
    }

    public function setImporteTotal(float $importe_total): void {
        $this->importe_total = $importe_total;
    }

    public function setIdProducto(int $id_producto): void {
        $this->id_producto = $id_producto;
    }

    public function setCantidad(int $cantidad): void {
        $this->cantidad = $cantidad;
    }

    public function crearDetalleVenta(
        int $id_venta,
        int $id_carrito,
        float $precio_unitario,
        float $importe_total,
        int $id_producto,
        int $cantidad
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO detalle_venta
            (id_venta, id_carrito, precio_unitario, importe_total, id_producto, cantidad)
            VALUES (:id_venta, :id_carrito, :precio_unitario, :importe_total, :id_producto, :cantidad)
        ");

        return $stmt->execute([
            ':id_venta' => $id_venta,
            ':id_carrito' => $id_carrito,
            ':precio_unitario' => $precio_unitario,
            ':importe_total' => $importe_total,
            ':id_producto' => $id_producto,
            ':cantidad' => $cantidad
        ]);
    }

    public function actualizarDetalleVenta(
        int $id_fila,
        int $id_venta,
        int $id_carrito,
        float $precio_unitario,
        float $importe_total,
        int $id_producto,
        int $cantidad
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE detalle_venta
            SET id_venta = :id_venta,
                id_carrito = :id_carrito,
                precio_unitario = :precio_unitario,
                importe_total = :importe_total,
                id_producto = :id_producto,
                cantidad = :cantidad
            WHERE id_fila = :id_fila
        ");

        return $stmt->execute([
            ':id_fila' => $id_fila,
            ':id_venta' => $id_venta,
            ':id_carrito' => $id_carrito,
            ':precio_unitario' => $precio_unitario,
            ':importe_total' => $importe_total,
            ':id_producto' => $id_producto,
            ':cantidad' => $cantidad
        ]);
    }

    public function eliminarDetalleVenta(int $id_fila): bool {
        $stmt = $this->pdo->prepare("DELETE FROM detalle_venta WHERE id_fila = :id_fila");
        return $stmt->execute([':id_fila' => $id_fila]);
    }

    public function traerDetalleVentas(): array {
        $stmt = $this->pdo->query("SELECT * FROM detalle_venta");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traerDetalleVentaPorId(int $id_fila): array {
        $stmt = $this->pdo->prepare("SELECT * FROM detalle_venta WHERE id_fila = :id_fila");
        $stmt->execute([':id_fila' => $id_fila]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}

?>
