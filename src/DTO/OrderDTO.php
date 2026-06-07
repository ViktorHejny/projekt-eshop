<?php
declare(strict_types=1);

final class OrderDTO
{
    /**
     * @param OrderItemDTO[] $items
     */
    public function __construct(
        public int $id,
        public int $customerId,
        public int $shippingMethodId,
        public int $paymentMethodId,
        public float $shippingPrice,
        public float $paymentPrice,
        public float $itemsPrice,
        public float $totalPrice,
        public string $status,
        public ?string $note,
        public string $createdAt,
        public array $items = [],
    ) {}

    public static function fromRow(array $row, array $items = []): self
    {
        return new self(
            id: (int)$row['id'],
            customerId: (int)$row['customer_id'],
            shippingMethodId: (int)$row['shipping_method_id'],
            paymentMethodId: (int)$row['payment_method_id'],
            shippingPrice: (float)$row['shipping_price'],
            paymentPrice: (float)$row['payment_price'],
            itemsPrice: (float)$row['items_price'],
            totalPrice: (float)$row['total_price'],
            status: (string)$row['status'],
            note: $row['note'] ?? null,
            createdAt: (string)$row['created_at'],
            items: $items,
        );
    }
}
