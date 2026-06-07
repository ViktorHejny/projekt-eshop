<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$cart = new Cart();

$values = ['name' => '', 'email' => '', 'message' => ''];
$validator = new Validator();
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403); exit('Neplatný bezpečnostní token.');
    }
    $values = [
        'name'    => trim((string)($_POST['name'] ?? '')),
        'email'   => trim((string)($_POST['email'] ?? '')),
        'message' => trim((string)($_POST['message'] ?? '')),
    ];

    $validator
        ->required('name', $values['name'], 'Zadejte jméno.')
        ->minLength('name', $values['name'], 2, 'Jméno musí mít alespoň 2 znaky.')
        ->required('email', $values['email'], 'E-mail je povinný.')
        ->email('email', $values['email'], 'Neplatný formát e-mailu.')
        ->required('message', $values['message'], 'Napište zprávu.')
        ->minLength('message', $values['message'], 5, 'Zpráva je příliš krátká.');

    if ($validator->isValid()) {
        $_SESSION['contact_success'] = true;
        header('Location: kontakt.php'); exit;
    }
}

if (!empty($_SESSION['contact_success'])) {
    $success = true;
    unset($_SESSION['contact_success']);
}

$pageTitle = 'Kontakt – GamingObchod';
$cartItemCount = $cart->getTotalQuantity();
require __DIR__ . '/partials/header.php';
?>

<main>
  <section class="page">
    <div class="container">
      <header class="page-header">
        <h1>Kontakt</h1>
        <p>Máte dotaz? Neváhejte nás kontaktovat.</p>
      </header>

      <div class="contact-grid">
        <section class="contact-box">
          <h2>Kontaktní údaje</h2>
          <p>
            <strong>GamingObchod s.r.o.</strong><br>
            Herní 12<br>
            100 00 Praha
          </p>
          <p>
            <strong>Email:</strong> info@gamingobchod.cz<br>
            <strong>Telefon:</strong> +420 777 123 456
          </p>

          <h3>Otevírací doba</h3>
          <table class="opening-hours">
            <tr><td>Pondělí – Pátek</td><td>9:00 – 18:00</td></tr>
            <tr><td>Sobota</td><td>9:00 – 12:00</td></tr>
            <tr><td>Neděle</td><td>Zavřeno</td></tr>
          </table>
        </section>

        <section class="contact-box">
          <h2>Napište nám</h2>

          <?php if ($success): ?>
            <p class="success">Zpráva byla odeslána.</p>
          <?php endif; ?>

          <form class="form" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-field">
              <label for="name">Jméno</label>
              <input type="text" id="name" name="name" value="<?= htmlspecialchars($values['name']) ?>"
                     class="<?= $validator->hasError('name') ? 'input--error' : '' ?>">
              <?php if ($validator->hasError('name')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('name')) ?></small>
              <?php endif; ?>
            </div>
            <div class="form-field">
              <label for="email">E-mail</label>
              <input type="email" id="email" name="email" value="<?= htmlspecialchars($values['email']) ?>"
                     class="<?= $validator->hasError('email') ? 'input--error' : '' ?>">
              <?php if ($validator->hasError('email')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('email')) ?></small>
              <?php endif; ?>
            </div>
            <div class="form-field">
              <label for="message">Zpráva</label>
              <textarea id="message" name="message" rows="5"
                        class="<?= $validator->hasError('message') ? 'input--error' : '' ?>"><?= htmlspecialchars($values['message']) ?></textarea>
              <?php if ($validator->hasError('message')): ?>
                <small class="error"><?= htmlspecialchars($validator->getError('message')) ?></small>
              <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-cart">Odeslat</button>
          </form>
        </section>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
