<?php
declare(strict_types=1);

final class ProductParameterDTO
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $name,
        public string $value,
        public string $type, // 'select' | 'info'
    ) {}

    public function isSelectable(): bool
    {
        return $this->type === 'select';
    }

    /** @return string[] */
    public function getOptions(): array
    {
        if (!$this->isSelectable()) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $this->value)), fn($v) => $v !== ''));
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)$row['id'],
            productId: (int)$row['product_id'],
            name: (string)$row['name'],
            value: (string)$row['value'],
            type: (string)$row['type'],
        );
    }
}
