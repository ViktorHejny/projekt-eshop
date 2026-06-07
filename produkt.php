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
    if ($product !== null) {
        $variant = '';
        if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
            $parts = $_POST['variants'];
            ksort($parts);
            $bits = [];
            foreach ($parts as $name => $value) {
                if (trim((string)$value) === '') {
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                }
                $bits[] = $name . ': ' . $value;
            }
            $variant = implode(', ', $bits);
        }
        $cart->add(
            productId: $product->id,
            productName: $product->name,
            unitPrice: $product->price,
            image: $product->image,
            variant: $variant,
        );
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$slug = trim((string)($_GET['slug'] ?? ''));
$product = $slug !== '' ? $productRepo->getBySlug($slug) : null;

if ($product === null) {
    header('Location: 404.php');
    exit;
}

$images = $productRepo->getImages($product->id);
$params = $productRepo->getParameters($product->id);
$selectableParams = array_filter($params, fn(ProductParameterDTO $p) => $p->isSelectable());
$infoParams = array_filter($params, fn(ProductParameterDTO $p) => !$p->isSelectable());

$pageTitle = $product->name . ' – GamingObchod';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <nav class="breadcrumb" aria-label="Drobečková navigace">
    <div class="container">
      <a href="index.php">Domů</a>
      <span aria-hidden="true">/</span>
      <?php if ($product->categorySlug): ?>
        <a href="produkty.php?slug=<?= urlencode($product->categorySlug) ?>"><?= htmlspecialchars($product->categoryName) ?></a>
        <span aria-hidden="true">/</span>
      <?php endif; ?>
      <span><?= htmlspecialchars($product->name) ?></span>
    </div>
  </nav>

  <section class="product-detail">
    <div class="container">

      <header class="page-header">
        <h1><?= htmlspecialchars($product->name) ?></h1>
        <?php if ($product->categoryName): ?>
          <p><?= htmlspecialchars($product->categoryName) ?></p>
        <?php endif; ?>
      </header>

      <div class="product-detail-grid">

        <div class="product-gallery">
          <img src="<?= htmlspecialchars($product->image ?? '') ?>" alt="<?= htmlspecialchars($product->name) ?>">
          <?php if ($images !== []): ?>
            <div class="product-thumbs" aria-label="Galerie produktu">
              <?php foreach ($images as $img): ?>
                <img src="<?= htmlspecialchars($img->image) ?>" alt="Náhled">
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="product-info">
          <p class="price">
            <?= number_format($product->price, 0, ',', ' ') ?> Kč
            <?php if ($product->hasDiscount()): ?>
              <small class="price-original">
                <?= number_format($product->originalPrice, 0, ',', ' ') ?> Kč
              </small>
            <?php endif; ?>
          </p>

          <p><?= nl2br(htmlspecialchars($product->description)) ?></p>

          <form method="post" class="product-actions">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="add_to_cart" value="1">
            <input type="hidden" name="product_id" value="<?= $product->id ?>">

            <?php foreach ($selectableParams as $param): ?>
              <div class="form-field">
                <label for="variant-<?= htmlspecialchars($param->name) ?>">
                  <?= htmlspecialchars($param->name) ?>:
                </label>
                <select name="variants[<?= htmlspecialchars($param->name) ?>]"
                        id="variant-<?= htmlspecialchars($param->name) ?>" required>
                  <option value="">-- Vyberte --</option>
                  <?php foreach ($param->getOptions() as $option): ?>
                    <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-cart">Přidat do košíku</button>
            <?php if ($product->categorySlug): ?>
              <a href="produkty.php?slug=<?= urlencode($product->categorySlug) ?>" class="btn btn-detail">Zpět na výpis</a>
            <?php endif; ?>
          </form>

          <?php if ($infoParams !== []): ?>
            <section class="product-params">
              <h2>Parametry</h2>
              <table class="params-table">
                <?php foreach ($infoParams as $param): ?>
                  <tr>
                    <th><?= htmlspecialchars($param->name) ?></th>
                    <td><?= htmlspecialchars($param->value) ?></td>
                  </tr>
                <?php endforeach; ?>
              </table>
            </section>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
