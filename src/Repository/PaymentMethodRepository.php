<?php
declare(strict_types=1);

final class PaymentMethodRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** @return PaymentMethodDTO[] */
    public function getAll(): array
    {
        $rows = $this->db->query('SELECT * FROM payment_methods ORDER BY id')->fetchAll();
        return array_map(fn($r) => PaymentMethodDTO::fromRow($r), $rows);
    }

    public function getById(int $id): ?PaymentMethodDTO
    {
        $stmt = $this->db->prepare('SELECT * FROM payment_methods WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? PaymentMethodDTO::fromRow($row) : null;
    }
}
