<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$productRepo = new ProductRepository();
$categoryRepo = new CategoryRepository();
$cart = new Cart();

$slug = trim((string)($_GET['slug'] ?? ''));
$category = null;

if ($slug !== '') {
    $category = $categoryRepo->getBySlug($slug);
    if ($category === null) {
        header('Location: 404.php'); exit;
    }
}

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

$products = $category !== null
    ? $productRepo->getByCategory($category->id)
    : $productRepo->getAll();

$pageTitle = ($category !== null ? $category->name : 'Produkty') . ' – GamingObchod';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="page-header">
    <div class="container">
      <h1><?= htmlspecialchars($category !== null ? $category->name : 'Produkty') ?></h1>
      <p><?= htmlspecialchars($category->description ?? 'Kompletní nabídka herního vybavení.') ?></p>
    </div>
  </section>

  <section class="products">
    <div class="container">
      <?php if ($products === []): ?>
        <p>V této kategorii zatím nejsou žádné produkty.</p>
      <?php else: ?>
        <div class="product-list">
          <?php foreach ($products as $product):
              require __DIR__ . '/partials/product-card.php';
          endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
