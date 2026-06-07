<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$productRepo = new ProductRepository();
$cart = new Cart();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403); exit('Neplatný bezpečnostní token.');
    }
    $product = $productRepo->getById((int)$_POST['product_id']);
    if ($product !== null && !$product->hasVariants) {
        $cart->add(
            productId: $product->id,
            productName: $product->name,
            unitPrice: $product->price,
            image: $product->image,
        );
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$query = trim((string)($_GET['q'] ?? ''));
$results = $query !== '' ? $productRepo->search($query) : [];

$pageTitle = 'Vyhledávání – GamingObchod';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="page">
    <div class="container">

      <header class="page-header">
        <h1>Výsledky vyhledávání</h1>
        <?php if ($query !== ''): ?>
          <p class="search-result">Výsledky pro: <strong>„<?= htmlspecialchars($query) ?>“</strong> (<?= count($results) ?>)</p>
        <?php else: ?>
          <p>Zadejte hledaný výraz do vyhledávacího pole.</p>
        <?php endif; ?>
      </header>

      <section class="products">
        <?php if ($query !== '' && $results === []): ?>
          <p>Žádné produkty neodpovídají hledanému výrazu.</p>
        <?php else: ?>
          <div class="product-list">
            <?php foreach ($results as $product):
                require __DIR__ . '/partials/product-card.php';
            endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
