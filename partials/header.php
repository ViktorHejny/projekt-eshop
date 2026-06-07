<?php
/** @var string $pageTitle */
/** @var int $cartItemCount */
$pageTitle = $pageTitle ?? 'GamingObchod';
$cartItemCount = $cartItemCount ?? 0;
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
$isActive = fn(array $names) => in_array($currentScript, $names, true) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a href="index.php" class="logo">
      <img src="assets/images/logo.png" alt="GamingObchod logo">
    </a>

    <form class="header-search" action="vyhledavani.php" method="get">
      <input type="search" name="q" placeholder="Hledat produkty..." aria-label="Hledat"
             value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      <button type="submit" class="btn btn-search">Hledat</button>
    </form>
  </div>
</header>

<nav class="main-nav">
  <ul>
    <li><a class="<?= $isActive(['index.php']) ?>" href="index.php">Domů</a></li>
    <li><a class="<?= $isActive(['kategorie.php']) ?>" href="kategorie.php">Kategorie</a></li>
    <li><a class="<?= $isActive(['produkty.php','produkt.php']) ?>" href="produkty.php">Produkty</a></li>
    <li><a class="<?= $isActive(['kontakt.php']) ?>" href="kontakt.php">Kontakt</a></li>
    <li><a class="<?= $isActive(['kosik.php','objednavka-1.php','objednavka-2.php','objednavka-3.php','objednavka-potvrzeni.php']) ?>" href="kosik.php">
      Košík<?php if ($cartItemCount > 0): ?> (<?= $cartItemCount ?>)<?php endif; ?>
    </a></li>
    <li><a class="<?= $isActive(['o-nas.php']) ?>" href="o-nas.php">O nás</a></li>
  </ul>
</nav>
