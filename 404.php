<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

http_response_code(404);

$cart = new Cart();
$pageTitle = '404 – Stránka nenalezena';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="page-404">
    <div class="container">
      <h1>404 – Stránka nenalezena</h1>
      <p>Litujeme, požadovanou stránku nebo produkt se nepodařilo najít.</p>
      <p>
        <a href="index.php" class="btn btn-cart">Zpět na hlavní stránku</a>
        <a href="produkty.php" class="btn btn-detail">Procházet produkty</a>
      </p>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
