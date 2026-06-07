<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();

$pageTitle = 'O nás – GamingObchod';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="page">
    <div class="container">
      <header class="page-header">
        <h1>O nás</h1>
        <p>GamingObchod je e-shop zaměřený na vybavení pro hráče – od myší až po monitory.</p>
      </header>

      <section class="content-block">
        <h2>Naše mise</h2>
        <p>
          Chceme, aby si každý gamer našel vybavení, které mu sedne – ať už hraje kompetitivně,
          nebo jen pro zábavu. Zaměřujeme se na ověřené značky a dobrý poměr cena/výkon.
        </p>
      </section>

      <section class="content-block">
        <h2>Proč nakoupit u nás</h2>
        <ul class="bullets">
          <li>Pečlivě vybrané produkty pro hráče</li>
          <li>Rychlé doručení a jednoduchý košík</li>
          <li>Jasné kategorie a přehledné filtrování</li>
        </ul>
      </section>

      <section class="content-block">
        <h2>Jak projekt vznikl</h2>
        <p>
          Tento web je školní projekt vytvořený v PHP nad SQLite databází s komponentovým přístupem.
        </p>
      </section>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
