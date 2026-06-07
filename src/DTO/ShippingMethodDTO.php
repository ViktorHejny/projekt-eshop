<?php
declare(strict_types=1);

final class ShippingMethodDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public float $price,
        public ?string $deliveryDays = null,
    ) {}

    public function isFree(): bool
    {
        return $this->price <= 0.0;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)$row['id'],
            name: (string)$row['name'],
            price: (float)$row['price'],
            deliveryDays: $row['delivery_days'] ?? null,
        );
    }
}
