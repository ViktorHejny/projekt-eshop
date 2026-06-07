<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$productRepo = new ProductRepository();
$categoryRepo = new CategoryRepository();
$cart = new Cart();

// Zpracování "přidat do košíku" z produktové karty
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

$categories = $categoryRepo->getAll();
$featured = $productRepo->getFeatured(limit: 6);

$pageTitle = 'GamingObchod – vybavení pro hráče';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="hero">
    <div class="container">
      <h1>Vybavení pro hráče</h1>
      <p>Nejlepší herní vybavení.</p>
    </div>
  </section>

  <section class="categories">
    <div class="container">
      <h2>Kategorie</h2>

      <div class="category-list">
        <?php foreach ($categories as $cat): ?>
          <article class="category-item">
            <a href="produkty.php?slug=<?= urlencode($cat->slug) ?>"><?= htmlspecialchars($cat->name) ?></a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="products">
    <div class="container">
      <h2>Doporučené produkty</h2>

      <div class="product-list">
        <?php foreach ($featured as $product):
            require __DIR__ . '/partials/product-card.php';
        endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
