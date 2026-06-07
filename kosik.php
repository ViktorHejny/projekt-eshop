<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$cart = new Cart();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403); exit('Neplatný bezpečnostní token.');
    }
    if (isset($_POST['remove_item'])) {
        $cart->remove((int)$_POST['product_id'], (string)($_POST['variant'] ?? ''));
    } elseif (isset($_POST['update_qty'])) {
        $cart->updateQuantity(
            productId: (int)$_POST['product_id'],
            quantity: max(0, (int)($_POST['quantity'] ?? 1)),
            variant: (string)($_POST['variant'] ?? ''),
        );
    }
    header('Location: kosik.php'); exit;
}

$items = $cart->getItems();
$pageTitle = 'Košík – GamingObchod';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="checkout">
    <div class="container">

      <header class="page-header">
        <h1>Košík</h1>
      </header>

      <section class="checkout-card">
        <?php if ($items === []): ?>
          <p>Váš košík je prázdný. <a href="produkty.php">Pokračovat v nákupu</a>.</p>
        <?php else: ?>
          <section class="cart-preview" aria-label="Položky košíku">
            <?php foreach ($items as $item): ?>
              <div class="cart-item">
                <div>
                  <strong><?= htmlspecialchars($item->productName) ?></strong>
                  <?php if ($item->variant !== ''): ?>
                    <span><?= htmlspecialchars($item->variant) ?></span>
                  <?php endif; ?>
                  <form method="post" class="cart-qty-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="update_qty" value="1">
                    <input type="hidden" name="product_id" value="<?= $item->productId ?>">
                    <input type="hidden" name="variant" value="<?= htmlspecialchars($item->variant) ?>">
                    <input type="number" name="quantity" min="1" max="99" value="<?= $item->quantity ?>">
                    <button type="submit" class="btn btn-detail">Změnit</button>
                  </form>
                </div>
                <div class="cart-item-right">
                  <span><?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč</span>
                  <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="remove_item" value="1">
                    <input type="hidden" name="product_id" value="<?= $item->productId ?>">
                    <input type="hidden" name="variant" value="<?= htmlspecialchars($item->variant) ?>">
                    <button type="submit" class="btn btn-detail">Odebrat</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>

            <div class="cart-line"></div>
            <div class="cart-summary">
              <div>
                <span>Mezisoučet</span>
                <strong><?= number_format($cart->getTotalPrice(), 0, ',', ' ') ?> Kč</strong>
              </div>
            </div>
          </section>

          <div class="form-actions">
            <a href="produkty.php" class="btn btn-detail">Pokračovat v nákupu</a>
            <a href="objednavka-1.php" class="btn btn-cart">Pokračovat k objednávce</a>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
