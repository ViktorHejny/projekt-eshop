<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$cart = new Cart();
$shippingRepo = new ShippingMethodRepository();
$paymentRepo = new PaymentMethodRepository();
$customerRepo = new CustomerRepository();
$orderRepo = new OrderRepository();

if ($cart->isEmpty()) {
    header('Location: kosik.php'); exit;
}
if (empty($_SESSION['checkout']['customer'])) {
    header('Location: objednavka-1.php'); exit;
}
if (empty($_SESSION['checkout']['shipping_id']) || empty($_SESSION['checkout']['payment_id'])) {
    header('Location: objednavka-2.php'); exit;
}

$customerData = $_SESSION['checkout']['customer'];
$shipping = $shippingRepo->getById((int)$_SESSION['checkout']['shipping_id']);
$payment  = $paymentRepo->getById((int)$_SESSION['checkout']['payment_id']);

if ($shipping === null || $payment === null) {
    header('Location: objednavka-2.php'); exit;
}

$validator = new Validator();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403); exit('Neplatný bezpečnostní token.');
    }
    $validator->required('terms', $_POST['terms'] ?? '', 'Musíte souhlasit s obchodními podmínkami.');

    if ($validator->isValid()) {
        $customer = $customerRepo->create(
            firstName: $customerData['first_name'],
            lastName:  $customerData['last_name'],
            email:     $customerData['email'],
            phone:     $customerData['phone'],
            street:    $customerData['street'],
            city:      $customerData['city'],
            zip:       $customerData['zip'],
        );

        $order = $orderRepo->create(
            customerId: $customer->id,
            shippingMethodId: $shipping->id,
            paymentMethodId: $payment->id,
            cartItems: $cart->getItems(),
            note: $customerData['note'] !== '' ? $customerData['note'] : null,
        );

        $cart->clear();
        unset($_SESSION['checkout']);
        header('Location: objednavka-potvrzeni.php?id=' . $order->id); exit;
    }
}

$itemsTotal = $cart->getTotalPrice();
$grandTotal = $itemsTotal + $shipping->price + $payment->price;

$pageTitle = 'Objednávka 3/3 – Shrnutí';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="checkout">
    <div class="container">

      <header class="page-header">
        <h1>Objednávka</h1>
        <p>Krok 3 ze 3 – zkontrolujte a odešlete.</p>
      </header>

      <ol class="checkout-steps">
        <li>1. Údaje</li>
        <li>2. Doprava a platba</li>
        <li class="active">3. Shrnutí</li>
      </ol>

      <section class="checkout-card">
        <div class="checkout-grid">

          <section class="cart-preview">
            <h3>Položky</h3>
            <?php foreach ($cart->getItems() as $item): ?>
              <div class="cart-item">
                <div>
                  <strong><?= htmlspecialchars($item->productName) ?></strong>
                  <span><?= $item->quantity ?> ks<?= $item->variant !== '' ? ' – ' . htmlspecialchars($item->variant) : '' ?></span>
                </div>
                <span><?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč</span>
              </div>
            <?php endforeach; ?>

            <div class="cart-item">
              <div><strong>Doprava:</strong> <span><?= htmlspecialchars($shipping->name) ?></span></div>
              <span><?= $shipping->isFree() ? 'zdarma' : number_format($shipping->price, 0, ',', ' ') . ' Kč' ?></span>
            </div>
            <div class="cart-item">
              <div><strong>Platba:</strong> <span><?= htmlspecialchars($payment->name) ?></span></div>
              <span><?= $payment->isFree() ? 'zdarma' : number_format($payment->price, 0, ',', ' ') . ' Kč' ?></span>
            </div>

            <div class="cart-line"></div>
            <div class="cart-summary">
              <div><span>Cena za zboží</span><strong><?= number_format($itemsTotal, 0, ',', ' ') ?> Kč</strong></div>
              <div><span>Celkem k úhradě</span><strong><?= number_format($grandTotal, 0, ',', ' ') ?> Kč</strong></div>
            </div>
          </section>

          <section class="summary-box">
            <h3>Doručení</h3>
            <p>
              <strong><?= htmlspecialchars($customerData['first_name'] . ' ' . $customerData['last_name']) ?></strong><br>
              <?= htmlspecialchars($customerData['street']) ?><br>
              <?= htmlspecialchars($customerData['zip'] . ' ' . $customerData['city']) ?>
            </p>
            <p>
              <strong>E-mail:</strong> <?= htmlspecialchars($customerData['email']) ?><br>
              <strong>Telefon:</strong> <?= htmlspecialchars($customerData['phone']) ?>
            </p>
            <?php if (!empty($customerData['note'])): ?>
              <p><strong>Poznámka:</strong> <?= nl2br(htmlspecialchars($customerData['note'])) ?></p>
            <?php endif; ?>

            <div class="summary-line"></div>

            <form method="post">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="place_order" value="1">

              <label class="checkbox">
                <input type="checkbox" name="terms" value="1" required>
                Souhlasím s obchodními podmínkami
              </label>
              <?php if ($validator->hasError('terms')): ?>
                <p class="error"><?= htmlspecialchars($validator->getError('terms')) ?></p>
              <?php endif; ?>

              <div class="form-actions">
                <a href="objednavka-2.php" class="btn btn-detail">Zpět</a>
                <button type="submit" class="btn btn-cart">Odeslat objednávku</button>
              </div>
            </form>
          </section>

        </div>
      </section>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
