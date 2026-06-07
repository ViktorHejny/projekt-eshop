<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$categoryRepo = new CategoryRepository();
$cart = new Cart();
$categories = $categoryRepo->getAll();

$pageTitle = 'Kategorie – GamingObchod';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="page-header">
    <div class="container">
      <h1>Kategorie</h1>
      <p>Vyber si kategorii a prohlédni si produkty.</p>
    </div>
  </section>

  <section class="categories-page">
    <div class="container">
      <div class="category-grid">
        <?php foreach ($categories as $cat): ?>
          <article class="category-card">
            <a href="produkty.php?slug=<?= urlencode($cat->slug) ?>">
              <?php if ($cat->image): ?>
                <img src="<?= htmlspecialchars($cat->image) ?>" alt="<?= htmlspecialchars($cat->name) ?>">
              <?php endif; ?>
              <h2><?= htmlspecialchars($cat->name) ?></h2>
              <?php if (!empty($cat->description)): ?>
                <p><?= htmlspecialchars($cat->description) ?></p>
              <?php endif; ?>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
