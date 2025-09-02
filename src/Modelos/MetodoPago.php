<?php  

namespace App\Modelos;

use PDO;

class MetodosPago {
    
    private $pdo;
    private $id_metodo_pago;
    private $descripcion_mp;

    public function __construct(PDO $pdo, int $id_metodo_pago = null, string $descripcion_mp = '') {
        $this->pdo = $pdo;
        $this->id_metodo_pago = $id_metodo_pago;
        $this->descripcion_mp = $descripcion_mp;
    }

    // Getters
    public function getIdMetodoPago(): ?int {
        return $this->id_metodo_pago;
    }

    public function getDescripcion(): string {
        return $this->descripcion_mp;
    }

    // Setters
    public function setDescripcion(string $descripcion_mp): void {
        $this->descripcion_mp = $descripcion_mp;
    }


    public function crearMetodoPago(string $descripcion_mp): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO metodo_pago (descripcion_mp) 
            VALUES (:descripcion_mp)
        ");
        return $stmt->execute([
            ':descripcion_mp' => $descripcion_mp
        ]);
    }

    public function actualizarMetodoPago(int $id_metodo_pago, string $descripcion_mp): bool {
        $stmt = $this->pdo->prepare("
            UPDATE metodo_pago 
            SET descripcion_mp = :descripcion_mp 
            WHERE id_metodo_pago = :id_metodo_pago
        ");
        return $stmt->execute([
            ':descripcion_mp' => $descripcion_mp,
            ':id_metodo_pago' => $id_metodo_pago
        ]);
    }

    public function eliminarMetodoPago(int $id_metodo_pago): bool {
        $stmt = $this->pdo->prepare("DELETE FROM metodo_pago WHERE id_metodo_pago = :id_metodo_pago");
        return $stmt->execute([':id_metodo_pago' => $id_metodo_pago]);
    }

    public function traerMetodosPago(): array {
        $stmt = $this->pdo->query("SELECT * FROM metodo_pago");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traerMetodoPagoPorId(int $id_metodo_pago): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM metodo_pago WHERE id_metodo_pago = :id_metodo_pago");
        $stmt->execute([':id_metodo_pago' => $id_metodo_pago]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}

?>
