<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
$orderRepo = new OrderRepository();
$customerRepo = new CustomerRepository();
$shippingRepo = new ShippingMethodRepository();
$paymentRepo = new PaymentMethodRepository();

$orderId = (int)($_GET['id'] ?? 0);
$order = $orderId > 0 ? $orderRepo->getById($orderId) : null;

if ($order === null) {
    header('Location: 404.php'); exit;
}

$customer = $customerRepo->getById($order->customerId);
$shipping = $shippingRepo->getById($order->shippingMethodId);
$payment  = $paymentRepo->getById($order->paymentMethodId);

$pageTitle = 'Potvrzení objednávky #' . $order->id;
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="checkout">
    <div class="container">

      <section class="confirmation-card">
        <h1>Objednávka #<?= $order->id ?> byla úspěšně odeslána</h1>
        <p>Děkujeme za nákup! Vaši objednávku jsme přijali a brzy ji začneme zpracovávat.</p>

        <div class="confirmation-grid">

          <section class="confirmation-box">
            <h2>Shrnutí objednávky</h2>

            <?php foreach ($order->items as $item): ?>
              <div class="cart-item">
                <div>
                  <strong><?= htmlspecialchars($item->productName) ?></strong>
                  <span><?= $item->quantity ?> ks<?= $item->variant !== '' ? ' – ' . htmlspecialchars($item->variant) : '' ?></span>
                </div>
                <span><?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč</span>
              </div>
            <?php endforeach; ?>

            <div class="cart-item">
              <div><strong>Doprava</strong></div>
              <span><?= number_format($order->shippingPrice, 0, ',', ' ') ?> Kč</span>
            </div>
            <div class="cart-item">
              <div><strong>Platba</strong></div>
              <span><?= number_format($order->paymentPrice, 0, ',', ' ') ?> Kč</span>
            </div>

            <div class="cart-line"></div>
            <div class="cart-summary">
              <div><span>Celkem</span><strong><?= number_format($order->totalPrice, 0, ',', ' ') ?> Kč</strong></div>
            </div>
          </section>

          <section class="confirmation-box">
            <h2>Doručení</h2>
            <?php if ($customer): ?>
              <p><strong>Jméno:</strong> <?= htmlspecialchars($customer->getFullName()) ?></p>
              <p><strong>Adresa:</strong> <?= htmlspecialchars($customer->getFullAddress()) ?></p>
              <p><strong>E-mail:</strong> <?= htmlspecialchars($customer->email) ?></p>
              <p><strong>Telefon:</strong> <?= htmlspecialchars($customer->phone) ?></p>
            <?php endif; ?>

            <div class="summary-line"></div>

            <?php if ($shipping): ?>
              <p><strong>Doprava:</strong> <?= htmlspecialchars($shipping->name) ?></p>
            <?php endif; ?>
            <?php if ($payment): ?>
              <p><strong>Platba:</strong> <?= htmlspecialchars($payment->name) ?></p>
            <?php endif; ?>
            <?php if ($order->note): ?>
              <p><strong>Poznámka:</strong> <?= nl2br(htmlspecialchars($order->note)) ?></p>
            <?php endif; ?>
          </section>

        </div>

        <div class="confirmation-actions">
          <a href="index.php" class="btn btn-detail">Zpět na hlavní stránku</a>
          <a href="produkty.php" class="btn btn-cart">Pokračovat v nákupu</a>
        </div>
      </section>

    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
