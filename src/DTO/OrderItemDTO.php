<?php
declare(strict_types=1);

final readonly class OrderItemDTO
{
    public function __construct(
        public int $id,
        public int $orderId,
        public int $productId,
        public string $productName,
        public string $variant,
        public int $quantity,
        public float $unitPrice,
    ) {}

    public function getTotalPrice(): float
    {
        return $this->unitPrice * $this->quantity;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)$row['id'],
            orderId: (int)$row['order_id'],
            productId: (int)$row['product_id'],
            productName: (string)$row['product_name'],
            variant: (string)($row['variant'] ?? ''),
            quantity: (int)$row['quantity'],
            unitPrice: (float)$row['unit_price'],
        );
    }
}
