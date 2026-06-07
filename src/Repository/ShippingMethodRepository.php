<?php
declare(strict_types=1);

final class ShippingMethodRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** @return ShippingMethodDTO[] */
    public function getAll(): array
    {
        $rows = $this->db->query('SELECT * FROM shipping_methods ORDER BY id')->fetchAll();
        return array_map(fn($r) => ShippingMethodDTO::fromRow($r), $rows);
    }

    public function getById(int $id): ?ShippingMethodDTO
    {
        $stmt = $this->db->prepare('SELECT * FROM shipping_methods WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? ShippingMethodDTO::fromRow($row) : null;
    }
}
