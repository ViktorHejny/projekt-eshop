<?php
declare(strict_types=1);

final class CategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** @return CategoryDTO[] */
    public function getAll(): array
    {
        $rows = $this->db->query('SELECT * FROM categories ORDER BY id')->fetchAll();
        return array_map(fn($r) => CategoryDTO::fromRow($r), $rows);
    }

    public function getById(int $id): ?CategoryDTO
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? CategoryDTO::fromRow($row) : null;
    }

    public function getBySlug(string $slug): ?CategoryDTO
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? CategoryDTO::fromRow($row) : null;
    }
}
