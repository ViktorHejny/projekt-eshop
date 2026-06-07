<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$cart = new Cart();

if ($cart->isEmpty()) {
    header('Location: kosik.php'); exit;
}

$values = $_SESSION['checkout']['customer'] ?? [
    'first_name' => '',
    'last_name'  => '',
    'email'      => '',
    'phone'      => '',
    'street'     => '',
    'city'       => '',
    'zip'        => '',
    'note'       => '',
];

$validator = new Validator();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403); exit('Neplatný bezpečnostní token.');
    }

    $values = [
        'first_name' => trim((string)($_POST['first_name'] ?? '')),
        'last_name'  => trim((string)($_POST['last_name'] ?? '')),
        'email'      => trim((string)($_POST['email'] ?? '')),
        'phone'      => trim((string)($_POST['phone'] ?? '')),
        'street'     => trim((string)($_POST['street'] ?? '')),
        'city'       => trim((string)($_POST['city'] ?? '')),
        'zip'        => trim((string)($_POST['zip'] ?? '')),
        'note'       => trim((string)($_POST['note'] ?? '')),
    ];

    $validator
        ->required('first_name', $values['first_name'], 'Zadejte jméno.')
        ->minLength('first_name', $values['first_name'], 2, 'Jméno musí mít alespoň 2 znaky.')
        ->required('last_name', $values['last_name'], 'Zadejte příjmení.')
        ->minLength('last_name', $values['last_name'], 2, 'Příjmení musí mít alespoň 2 znaky.')
        ->required('email', $values['email'], 'E-mail je povinný.')
        ->email('email', $values['email'], 'Neplatný formát e-mailu.')
        ->required('phone', $values['phone'], 'Zadejte telefon.')
        ->required('street', $values['street'], 'Zadejte ulici a č. p.')
        ->required('city', $values['city'], 'Zadejte město.')
        ->required('zip', $values['zip'], 'PSČ je povinné.')
        ->pattern('zip', $values['zip'], '/^\d{3}\s?\d{2}$/', 'PSČ musí mít 5 číslic.');

    if ($validator->isValid()) {
        $_SESSION['checkout']['customer'] = $values;
        header('Location: objednavka-2.php'); exit;
    }
}

$pageTitle = 'Objednávka 1/3 – Dodací údaje';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="checkout">
    <div class="container">

      <header class="page-header">
        <h1>Objednávka</h1>
        <p>Krok 1 ze 3 – dodací údaje.</p>
      </header>

      <ol class="checkout-steps" aria-label="Postup objednávky">
        <li class="active">1. Údaje</li>
        <li>2. Doprava a platba</li>
        <li>3. Shrnutí</li>
      </ol>

      <section class="checkout-card">
        <form class="form" method="post" aria-label="Doručovací údaje" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

          <div class="form-grid">
            <div class="form-field">
              <label for="first_name">Jméno</label>
              <input type="text" id="first_name" name="first_name"
                     value="<?= htmlspecialchars($values['first_name']) ?>"
                     class="<?= $validator->hasError('first_name') ? 'input--error' : '' ?>" required>
              <?php if ($validator->hasError('first_name')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('first_name')) ?></small>
              <?php endif; ?>
            </div>
            <div class="form-field">
              <label for="last_name">Příjmení</label>
              <input type="text" id="last_name" name="last_name"
                     value="<?= htmlspecialchars($values['last_name']) ?>"
                     class="<?= $validator->hasError('last_name') ? 'input--error' : '' ?>" required>
              <?php if ($validator->hasError('last_name')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('last_name')) ?></small>
              <?php endif; ?>
            </div>
            <div class="form-field">
              <label for="email">E-mail</label>
              <input type="email" id="email" name="email"
                     value="<?= htmlspecialchars($values['email']) ?>"
                     class="<?= $validator->hasError('email') ? 'input--error' : '' ?>" required>
              <?php if ($validator->hasError('email')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('email')) ?></small>
              <?php endif; ?>
            </div>
            <div class="form-field">
              <label for="phone">Telefon</label>
              <input type="tel" id="phone" name="phone"
                     value="<?= htmlspecialchars($values['phone']) ?>"
                     class="<?= $validator->hasError('phone') ? 'input--error' : '' ?>" required>
              <?php if ($validator->hasError('phone')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('phone')) ?></small>
              <?php endif; ?>
            </div>
            <div class="form-field">
              <label for="street">Ulice a č. p.</label>
              <input type="text" id="street" name="street"
                     value="<?= htmlspecialchars($values['street']) ?>"
                     class="<?= $validator->hasError('street') ? 'input--error' : '' ?>" required>
              <?php if ($validator->hasError('street')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('street')) ?></small>
              <?php endif; ?>
            </div>
            <div class="form-field">
              <label for="city">Město</label>
              <input type="text" id="city" name="city"
                     value="<?= htmlspecialchars($values['city']) ?>"
                     class="<?= $validator->hasError('city') ? 'input--error' : '' ?>" required>
              <?php if ($validator->hasError('city')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('city')) ?></small>
              <?php endif; ?>
            </div>
            <div class="form-field">
              <label for="zip">PSČ</label>
              <input type="text" id="zip" name="zip"
                     value="<?= htmlspecialchars($values['zip']) ?>"
                     class="<?= $validator->hasError('zip') ? 'input--error' : '' ?>" required>
              <?php if ($validator->hasError('zip')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('zip')) ?></small>
              <?php endif; ?>
            </div>
            <div class="form-field form-field-full">
              <label for="note">Poznámka (nepovinné)</label>
              <textarea id="note" name="note" rows="4"><?= htmlspecialchars($values['note']) ?></textarea>
            </div>
          </div>

          <div class="form-actions">
            <a href="kosik.php" class="btn btn-detail">Zpět do košíku</a>
            <button type="submit" class="btn btn-cart">Pokračovat k dopravě</button>
          </div>
        </form>
      </section>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
