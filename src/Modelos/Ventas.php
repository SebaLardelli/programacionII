<?php 

namespace App\Modelos;

use PDO;

class Ventas {
    
    private $pdo;
    private $id_venta;
    private $id_usuario;
    private $id_carrito;
    private $fecha_venta;
    private $id_metodo_pago;
    private $importe_total;
    private $id_estado_v;
    private $id_punto_retiro;
    private $id_estado_p;
    
    public function __construct(
        PDO $pdo,
        int $id_venta = 0,
        int $id_usuario = 0,
        int $id_carrito = 0,
        string $fecha_venta = '',
        int $id_metodo_pago = 0,
        float $importe_total = 0.0,
        int $id_estado_v = 0,
        int $id_punto_retiro = 0,
        int $id_estado_p = 0
    ) {
        $this->pdo = $pdo;
        $this->id_venta = $id_venta;
        $this->id_usuario = $id_usuario;
        $this->id_carrito = $id_carrito;
        $this->fecha_venta = $fecha_venta;
        $this->id_metodo_pago = $id_metodo_pago;
        $this->importe_total = $importe_total;
        $this->id_estado_v = $id_estado_v;
        $this->id_punto_retiro = $id_punto_retiro;
        $this->id_estado_p = $id_estado_p;
    }

    // Getters
    public function getIdVenta(): int {
        return $this->id_venta;
    }

    public function getIdUsuario(): int {
        return $this->id_usuario;
    }

    public function getIdCarrito(): int {
        return $this->id_carrito;
    }

    public function getFechaVenta(): string {
        return $this->fecha_venta;
    }

    public function getIdMetodoPago(): int {
        return $this->id_metodo_pago;
    }

    public function getImporteTotal(): float {
        return $this->importe_total;
    }

    public function getIdEstadoV(): int {
        return $this->id_estado_v;
    }

    public function getIdPuntoRetiro(): int {
        return $this->id_punto_retiro;
    }

    public function getIdEstadoP(): int {
        return $this->id_estado_p;
    }

    // Setters
    public function setIdUsuario(int $id_usuario): void {
        $this->id_usuario = $id_usuario;
    }

    public function setIdCarrito(int $id_carrito): void {
        $this->id_carrito = $id_carrito;
    }

    public function setFechaVenta(string $fecha_venta): void {
        $this->fecha_venta = $fecha_venta;
    }

    public function setIdMetodoPago(int $id_metodo_pago): void {
        $this->id_metodo_pago = $id_metodo_pago;
    }

    public function setImporteTotal(float $importe_total): void {
        $this->importe_total = $importe_total;
    }

    public function setIdEstadoV(int $id_estado_v): void {
        $this->id_estado_v = $id_estado_v;
    }

    public function setIdPuntoRetiro(int $id_punto_retiro): void {
        $this->id_punto_retiro = $id_punto_retiro;
    }

    public function setIdEstadoP(int $id_estado_p): void {
        $this->id_estado_p = $id_estado_p;
    }

    public function crearVenta(
        int $id_usuario,
        int $id_carrito,
        string $fecha_venta,
        int $id_metodo_pago,
        float $importe_total,
        int $id_estado_v,
        int $id_punto_retiro,
        int $id_estado_p
    ) {
        $stmt = $this->pdo->prepare("
            INSERT INTO ventas
            (id_usuario, id_carrito, fecha_venta, id_metodo_pago, importe_total, id_estado_v, id_punto_retiro, id_estado_p)
            VALUES
            (:id_usuario, :id_carrito, :fecha_venta, :id_metodo_pago, :importe_total, :id_estado_v, :id_punto_retiro, :id_estado_p)
        ");
        return $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':id_carrito' => $id_carrito,
            ':fecha_venta' => $fecha_venta,
            ':id_metodo_pago' => $id_metodo_pago,
            ':importe_total' => $importe_total,
            ':id_estado_v' => $id_estado_v,
            ':id_punto_retiro' => $id_punto_retiro,
            ':id_estado_p' => $id_estado_p
        ]);
    }

    public function actualizarVenta(
        int $id_venta,
        int $id_usuario,
        int $id_carrito,
        string $fecha_venta,
        int $id_metodo_pago,
        float $importe_total,
        int $id_estado_v,
        int $id_punto_retiro,
        int $id_estado_p
    ) {
        $stmt = $this->pdo->prepare("
            UPDATE ventas
            SET id_usuario = :id_usuario, id_carrito = :id_carrito, fecha_venta = :fecha_venta,
                id_metodo_pago = :id_metodo_pago, importe_total = :importe_total, id_estado_v = :id_estado_v,
                id_punto_retiro = :id_punto_retiro, id_estado_p = :id_estado_p
            WHERE id_venta = :id_venta
        ");
        return $stmt->execute([
            ':id_venta' => $id_venta,
            ':id_usuario' => $id_usuario,
            ':id_carrito' => $id_carrito,
            ':fecha_venta' => $fecha_venta,
            ':id_metodo_pago' => $id_metodo_pago,
            ':importe_total' => $importe_total,
            ':id_estado_v' => $id_estado_v,
            ':id_punto_retiro' => $id_punto_retiro,
            ':id_estado_p' => $id_estado_p
        ]);
    }

    public function eliminarVenta(int $id_venta): bool {
        $stmt = $this->pdo->prepare("DELETE FROM ventas WHERE id_venta = :id_venta");
        return $stmt->execute([':id_venta' => $id_venta]);
    }

    public function traerVentas() {
        $stmt = $this->pdo->query("SELECT * FROM ventas");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traerVentaPorId(int $id_venta): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM ventas WHERE id_venta = :id_venta");
        $stmt->execute([':id_venta' => $id_venta]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

?>
