<?php

namespace App\Modelos;

use PDO;

class Carrito {

    private PDO $pdo;
    private ?int $id_carrito;
    private int $id_usuario;
    private string $fecha_creacion;
    private string $fecha_ultima_actualizacion;
    private float $importe_total;
    private int $id_estado_car;

    public function __construct(
        PDO $pdo,
        int $id_usuario = 0,
        string $fecha_creacion = '',
        string $fecha_ultima_actualizacion = '',
        float $importe_total = 0.0,
        int $id_estado_car = 0,
        ?int $id_carrito = null
    ) {
        $this->pdo = $pdo;
        $this->id_usuario = $id_usuario;
        $this->fecha_creacion = $fecha_creacion;
        $this->fecha_ultima_actualizacion = $fecha_ultima_actualizacion;
        $this->importe_total = $importe_total;
        $this->id_estado_car = $id_estado_car;
        $this->id_carrito = $id_carrito;
    }

    //Getters

    public function getIdCarrito(): int {
        return $this->id_carrito;
    }

    public function getIdUsuario(): int {
        return $this->id_usuario;
    }

    public function getFechaCreacion(): string {
        return $this->fecha_creacion;
    }

    public function getFechaUltimaActualizacion(): string {
        return $this->fecha_ultima_actualizacion;
    }

    public function getImporteTotal(): float {
        return $this->importe_total;
    }

    public function getIdEstadoCar(): int {
        return $this->id_estado_car;
    }


    //Setters

    public function setIdUsuario(int $id_usuario): void {
        $this->id_usuario = $id_usuario;
    }

    public function setFechaCreacion(string $fecha_creacion): void {
        $this->fecha_creacion = $fecha_creacion;
    }

    public function setFechaUltimaActualizacion(string $fecha_ultima_actualizacion): void {
        $this->fecha_ultima_actualizacion = $fecha_ultima_actualizacion;
    }

    public function setImporteTotal(float $importe_total): void {
        $this->importe_total = $importe_total;
    }

    public function setIdEstadoCar(int $id_estado_car): void {
        $this->id_estado_car = $id_estado_car;
    }


    public function crearCarrito(
        int $id_usuario,
        string $fecha_creacion,
        string $fecha_ultima_actualizacion,
        float $importe_total,
        int $id_estado_car
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO carrito
            (id_usuario, fecha_creacion, fecha_ultima_actualizacion, importe_total, id_estado_car)
            VALUES (:id_usuario, :fecha_creacion, :fecha_ultima_actualizacion, :importe_total, :id_estado_car)
        ");

        return $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':fecha_creacion' => $fecha_creacion,
            ':fecha_ultima_actualizacion' => $fecha_ultima_actualizacion,
            ':importe_total' => $importe_total,
            ':id_estado_car' => $id_estado_car
        ]);
    }

    
    public function traerCarritos(): array {
        $stmt = $this->pdo->query("SELECT * FROM carrito");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traerCarritoPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM carrito WHERE id_carrito = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function actualizarCarrito(
        int $id_carrito,
        int $id_usuario,
        string $fecha_creacion,
        string $fecha_ultima_actualizacion,
        float $importe_total,
        int $id_estado_car
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE carrito
            SET id_usuario = :id_usuario,
                fecha_creacion = :fecha_creacion,
                fecha_ultima_actualizacion = :fecha_ultima_actualizacion,
                importe_total = :importe_total,
                id_estado_car = :id_estado_car
            WHERE id_carrito = :id_carrito
        ");

        return $stmt->execute([
            ':id_carrito' => $id_carrito,
            ':id_usuario' => $id_usuario,
            ':fecha_creacion' => $fecha_creacion,
            ':fecha_ultima_actualizacion' => $fecha_ultima_actualizacion,
            ':importe_total' => $importe_total,
            ':id_estado_car' => $id_estado_car
        ]);
    }

    public function eliminarCarrito(int $id_carrito): bool {
        $stmt = $this->pdo->prepare("DELETE FROM carrito WHERE id_carrito = :id");
        $stmt->execute([':id' => $id_carrito]);
        return $stmt->rowCount() > 0; // true si realmente borró
    }

}

?>






