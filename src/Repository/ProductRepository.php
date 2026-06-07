<?php
declare(strict_types=1);

final class ProductRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function baseSelect(): string
    {
        return "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                EXISTS(SELECT 1 FROM product_parameters pp WHERE pp.product_id = p.id AND pp.type = 'select') AS has_variants
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id";
    }

    /** @return ProductDTO[] */
    public function getAll(): array
    {
        $rows = $this->db->query($this->baseSelect() . ' ORDER BY p.id')->fetchAll();
        return array_map(fn($r) => ProductDTO::fromRow($r), $rows);
    }

    /** @return ProductDTO[] */
    public function getFeatured(int $limit = 6): array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE p.is_featured = 1 ORDER BY p.id LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn($r) => ProductDTO::fromRow($r), $stmt->fetchAll());
    }

    public function getById(int $id): ?ProductDTO
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE p.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? ProductDTO::fromRow($row) : null;
    }

    public function getBySlug(string $slug): ?ProductDTO
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE p.slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? ProductDTO::fromRow($row) : null;
    }

    /** @return ProductDTO[] */
    public function getByCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE p.category_id = :cid ORDER BY p.id');
        $stmt->execute(['cid' => $categoryId]);
        return array_map(fn($r) => ProductDTO::fromRow($r), $stmt->fetchAll());
    }

    /** @return ProductDTO[] */
    public function getByCategorySlug(string $slug): array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE c.slug = :slug ORDER BY p.id');
        $stmt->execute(['slug' => $slug]);
        return array_map(fn($r) => ProductDTO::fromRow($r), $stmt->fetchAll());
    }

    /** @return ProductDTO[] */
    public function search(string $query): array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE p.name LIKE :q OR p.description LIKE :q ORDER BY p.id');
        $stmt->execute(['q' => '%' . $query . '%']);
        return array_map(fn($r) => ProductDTO::fromRow($r), $stmt->fetchAll());
    }

    /** @return ProductImageDTO[] */
    public function getImages(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM product_images WHERE product_id = :id ORDER BY id');
        $stmt->execute(['id' => $productId]);
        return array_map(fn($r) => ProductImageDTO::fromRow($r), $stmt->fetchAll());
    }

    /** @return ProductParameterDTO[] */
    public function getParameters(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM product_parameters WHERE product_id = :id ORDER BY id');
        $stmt->execute(['id' => $productId]);
        return array_map(fn($r) => ProductParameterDTO::fromRow($r), $stmt->fetchAll());
    }
}
