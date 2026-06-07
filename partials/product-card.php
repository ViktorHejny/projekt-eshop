<?php
/** @var ProductDTO $product */
?>
<article class="product-card">
  <img src="<?= htmlspecialchars($product->image ?? '') ?>" alt="<?= htmlspecialchars($product->name) ?>">
  <h3><?= htmlspecialchars($product->name) ?></h3>
  <p class="price">
    <?= number_format($product->price, 0, ',', ' ') ?> Kč
    <?php if ($product->hasDiscount()): ?>
      <small class="price-original">
        <?= number_format($product->originalPrice, 0, ',', ' ') ?> Kč
      </small>
    <?php endif; ?>
  </p>

  <div class="product-actions">
    <a href="produkt.php?slug=<?= urlencode($product->slug) ?>" class="btn btn-detail">Detail produktu</a>
    <?php if ($product->hasVariants): ?>
      <a href="produkt.php?slug=<?= urlencode($product->slug) ?>" class="btn btn-cart">Vybrat variantu</a>
    <?php else: ?>
      <form method="post" action="" class="inline-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="add_to_cart" value="1">
        <input type="hidden" name="product_id" value="<?= $product->id ?>">
        <button type="submit" class="btn btn-cart">Přidat do košíku</button>
      </form>
    <?php endif; ?>
  </div>
</article>
