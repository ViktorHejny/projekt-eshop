<?php
declare(strict_types=1);

final class Cart
{
    private const SESSION_KEY = 'cart';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    public function add(
        int $productId,
        string $productName,
        float $unitPrice,
        ?string $image = null,
        string $variant = '',
        int $quantity = 1,
    ): void {
        $key = CartItemDTO::makeKey($productId, $variant);
        if (isset($_SESSION[self::SESSION_KEY][$key])) {
            $_SESSION[self::SESSION_KEY][$key]['quantity'] += $quantity;
        } else {
            $_SESSION[self::SESSION_KEY][$key] = [
                'productId'   => $productId,
                'productName' => $productName,
                'unitPrice'   => $unitPrice,
                'image'       => $image,
                'variant'     => $variant,
                'quantity'    => $quantity,
            ];
        }
    }

    public function updateQuantity(int $productId, int $quantity, string $variant = ''): void
    {
        $key = CartItemDTO::makeKey($productId, $variant);
        if (!isset($_SESSION[self::SESSION_KEY][$key])) {
            return;
        }
        if ($quantity <= 0) {
            $this->remove($productId, $variant);
            return;
        }
        $_SESSION[self::SESSION_KEY][$key]['quantity'] = $quantity;
    }

    public function remove(int $productId, string $variant = ''): void
    {
        $key = CartItemDTO::makeKey($productId, $variant);
        unset($_SESSION[self::SESSION_KEY][$key]);
    }

    /** @return CartItemDTO[] */
    public function getItems(): array
    {
        $items = [];
        foreach ($_SESSION[self::SESSION_KEY] as $row) {
            $items[] = new CartItemDTO(
                productId: (int)$row['productId'],
                productName: (string)$row['productName'],
                unitPrice: (float)$row['unitPrice'],
                quantity: (int)$row['quantity'],
                image: $row['image'] ?? null,
                variant: (string)($row['variant'] ?? ''),
            );
        }
        return $items;
    }

    public function getTotalPrice(): float
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += $item->getTotalPrice();
        }
        return $total;
    }

    public function getTotalQuantity(): int
    {
        $count = 0;
        foreach ($_SESSION[self::SESSION_KEY] as $row) {
            $count += (int)$row['quantity'];
        }
        return $count;
    }

    public function isEmpty(): bool
    {
        return $_SESSION[self::SESSION_KEY] === [];
    }

    public function clear(): void
    {
        $_SESSION[self::SESSION_KEY] = [];
    }
}
