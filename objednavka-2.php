<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$cart = new Cart();
$shippingRepo = new ShippingMethodRepository();
$paymentRepo = new PaymentMethodRepository();

if ($cart->isEmpty()) {
    header('Location: kosik.php'); exit;
}
if (empty($_SESSION['checkout']['customer'])) {
    header('Location: objednavka-1.php'); exit;
}

$shippingMethods = $shippingRepo->getAll();
$paymentMethods = $paymentRepo->getAll();

$selectedShipping = (int)($_SESSION['checkout']['shipping_id'] ?? 0);
$selectedPayment  = (int)($_SESSION['checkout']['payment_id']  ?? 0);

$validator = new Validator();
$shippingIds = array_map(fn($s) => $s->id, $shippingMethods);
$paymentIds  = array_map(fn($p) => $p->id, $paymentMethods);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403); exit('Neplatný bezpečnostní token.');
    }
    $selectedShipping = (int)($_POST['shipping_id'] ?? 0);
    $selectedPayment  = (int)($_POST['payment_id']  ?? 0);

    $validator
        ->in('shipping_id', $selectedShipping, $shippingIds, 'Vyberte způsob dopravy.')
        ->in('payment_id', $selectedPayment, $paymentIds, 'Vyberte způsob platby.');

    if ($validator->isValid()) {
        $_SESSION['checkout']['shipping_id'] = $selectedShipping;
        $_SESSION['checkout']['payment_id']  = $selectedPayment;
        header('Location: objednavka-3.php'); exit;
    }
}

$pageTitle = 'Objednávka 2/3 – Doprava a platba';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="checkout">
    <div class="container">

      <header class="page-header">
        <h1>Objednávka</h1>
        <p>Krok 2 ze 3 – doprava a platba.</p>
      </header>

      <ol class="checkout-steps">
        <li>1. Údaje</li>
        <li class="active">2. Doprava a platba</li>
        <li>3. Shrnutí</li>
      </ol>

      <section class="checkout-card">
        <div class="checkout-grid">

          <section class="cart-preview">
            <h3>Váš košík</h3>
            <?php foreach ($cart->getItems() as $item): ?>
              <div class="cart-item">
                <div>
                  <strong><?= htmlspecialchars($item->productName) ?></strong>
                  <span><?= $item->quantity ?> ks<?= $item->variant !== '' ? ' – ' . htmlspecialchars($item->variant) : '' ?></span>
                </div>
                <span><?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč</span>
              </div>
            <?php endforeach; ?>
            <div class="cart-line"></div>
            <div class="cart-summary">
              <div><span>Mezisoučet</span><strong><?= number_format($cart->getTotalPrice(), 0, ',', ' ') ?> Kč</strong></div>
            </div>
          </section>

          <form class="form" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <h3>Doprava</h3>
            <?php if ($validator->hasError('shipping_id')): ?>
              <p class="error"><?= htmlspecialchars($validator->getError('shipping_id')) ?></p>
            <?php endif; ?>
            <?php foreach ($shippingMethods as $s): ?>
              <label class="option-item">
                <input type="radio" name="shipping_id" value="<?= $s->id ?>" <?= $s->id === $selectedShipping ? 'checked' : '' ?>>
                <span>
                  <?= htmlspecialchars($s->name) ?> –
                  <?= $s->isFree() ? 'zdarma' : number_format($s->price, 0, ',', ' ') . ' Kč' ?>
                  <?php if ($s->deliveryDays): ?>
                    <small>(<?= htmlspecialchars($s->deliveryDays) ?>)</small>
                  <?php endif; ?>
                </span>
              </label>
            <?php endforeach; ?>

            <h3>Platba</h3>
            <?php if ($validator->hasError('payment_id')): ?>
              <p class="error"><?= htmlspecialchars($validator->getError('payment_id')) ?></p>
            <?php endif; ?>
            <?php foreach ($paymentMethods as $p): ?>
              <label class="option-item">
                <input type="radio" name="payment_id" value="<?= $p->id ?>" <?= $p->id === $selectedPayment ? 'checked' : '' ?>>
                <span>
                  <?= htmlspecialchars($p->name) ?> –
                  <?= $p->isFree() ? 'zdarma' : number_format($p->price, 0, ',', ' ') . ' Kč' ?>
                </span>
              </label>
            <?php endforeach; ?>

            <div class="form-actions">
              <a href="objednavka-1.php" class="btn btn-detail">Zpět</a>
              <button type="submit" class="btn btn-cart">Pokračovat ke shrnutí</button>
            </div>
          </form>

        </div>
      </section>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
