<?php
declare(strict_types=1);

final class CartItemDTO
{
    public function __construct(
        public int $productId,
        public string $productName,
        public float $unitPrice,
        public int $quantity,
        public ?string $image = null,
        public string $variant = '',
    ) {}

    public function getTotalPrice(): float
    {
        return $this->unitPrice * $this->quantity;
    }

    public function getKey(): string
    {
        return self::makeKey($this->productId, $this->variant);
    }

    public static function makeKey(int $productId, string $variant = ''): string
    {
        return $productId . '|' . $variant;
    }
}