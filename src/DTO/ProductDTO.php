<?php
declare(strict_types=1);

final class ProductDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public float $price,
        public ?float $originalPrice,
        public string $description,
        public ?string $image,
        public bool $isFeatured,
        public int $categoryId,
        public ?string $categoryName = null,
        public ?string $categorySlug = null,
        public bool $hasVariants = false,
    ) {}

    public function hasDiscount(): bool
    {
        return $this->originalPrice !== null && $this->originalPrice > $this->price;
    }

    public function getDiscountPercent(): int
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return (int) round((1 - $this->price / $this->originalPrice) * 100);
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)$row['id'],
            name: (string)$row['name'],
            slug: (string)$row['slug'],
            price: (float)$row['price'],
            originalPrice: isset($row['original_price']) ? (float)$row['original_price'] : null,
            description: (string)($row['description'] ?? ''),
            image: $row['image'] ?? null,
            isFeatured: (bool)($row['is_featured'] ?? false),
            categoryId: (int)$row['category_id'],
            categoryName: $row['category_name'] ?? null,
            categorySlug: $row['category_slug'] ?? null,
            hasVariants: (bool)($row['has_variants'] ?? false),
        );
    }
}
