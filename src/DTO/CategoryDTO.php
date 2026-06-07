<?php
declare(strict_types=1);

final class CategoryDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $image = null,
        public ?string $description = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)$row['id'],
            name: (string)$row['name'],
            slug: (string)$row['slug'],
            image: $row['image'] ?? null,
            description: $row['description'] ?? null,
        );
    }
}