<?php
declare(strict_types=1);

final class OrderRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * @param CartItemDTO[] $cartItems
     */
    public function create(
        int $customerId,
        int $shippingMethodId,
        int $paymentMethodId,
        array $cartItems,
        ?string $note = null,
    ): OrderDTO {
        $shippingRepo = new ShippingMethodRepository();
        $paymentRepo = new PaymentMethodRepository();
        $shipping = $shippingRepo->getById($shippingMethodId);
        $payment = $paymentRepo->getById($paymentMethodId);

        if ($shipping === null || $payment === null) {
            throw new RuntimeException('Neplatný způsob dopravy nebo platby.');
        }

        $itemsPrice = 0.0;
        foreach ($cartItems as $item) {
            $itemsPrice += $item->getTotalPrice();
        }
        $totalPrice = $itemsPrice + $shipping->price + $payment->price;
        $status = 'new';
        $createdAt = date('Y-m-d H:i:s');

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO orders (customer_id, shipping_method_id, payment_method_id, shipping_price, payment_price, items_price, total_price, status, note, created_at)
                 VALUES (:customer_id, :shipping_method_id, :payment_method_id, :shipping_price, :payment_price, :items_price, :total_price, :status, :note, :created_at)'
            );
            $stmt->execute([
                'customer_id'        => $customerId,
                'shipping_method_id' => $shippingMethodId,
                'payment_method_id'  => $paymentMethodId,
                'shipping_price'     => $shipping->price,
                'payment_price'      => $payment->price,
                'items_price'        => $itemsPrice,
                'total_price'        => $totalPrice,
                'status'             => $status,
                'note'               => $note,
                'created_at'         => $createdAt,
            ]);
            $orderId = (int)$this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, variant, quantity, unit_price)
                 VALUES (:order_id, :product_id, :product_name, :variant, :quantity, :unit_price)'
            );
            $createdItems = [];
            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    'order_id'     => $orderId,
                    'product_id'   => $item->productId,
                    'product_name' => $item->productName,
                    'variant'      => $item->variant,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unitPrice,
                ]);
                $createdItems[] = new OrderItemDTO(
                    id: (int)$this->db->lastInsertId(),
                    orderId: $orderId,
                    productId: $item->productId,
                    productName: $item->productName,
                    variant: $item->variant,
                    quantity: $item->quantity,
                    unitPrice: $item->unitPrice,
                );
            }

            $this->db->commit();

            return new OrderDTO(
                id: $orderId,
                customerId: $customerId,
                shippingMethodId: $shippingMethodId,
                paymentMethodId: $paymentMethodId,
                shippingPrice: $shipping->price,
                paymentPrice: $payment->price,
                itemsPrice: $itemsPrice,
                totalPrice: $totalPrice,
                status: $status,
                note: $note,
                createdAt: $createdAt,
                items: $createdItems,
            );
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getById(int $id): ?OrderDTO
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $itemStmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id = :id ORDER BY id');
        $itemStmt->execute(['id' => $id]);
        $items = array_map(fn($r) => OrderItemDTO::fromRow($r), $itemStmt->fetchAll());
        return OrderDTO::fromRow($row, $items);
    }
}
