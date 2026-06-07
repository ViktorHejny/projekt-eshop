<?php
declare(strict_types=1);

/**
 * Vytvoří/resetuje SQLite databázi e-shopu a naplní ji vzorovými daty.
 * Spuštění:  php projekt-eshop/database/init.php
 */

$dbPath = __DIR__ . '/eshop.db';

if (file_exists($dbPath)) {
    unlink($dbPath);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

// ============================================================
// SCHÉMA
// ============================================================
$pdo->exec("
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    image TEXT,
    description TEXT
);

CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL REFERENCES categories(id),
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    price REAL NOT NULL,
    original_price REAL,
    description TEXT NOT NULL DEFAULT '',
    image TEXT,
    is_featured INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE product_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    image TEXT NOT NULL
);

CREATE TABLE product_parameters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    value TEXT NOT NULL,
    type TEXT NOT NULL DEFAULT 'info' CHECK (type IN ('info','select'))
);

CREATE TABLE shipping_methods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price REAL NOT NULL DEFAULT 0,
    delivery_days TEXT
);

CREATE TABLE payment_methods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price REAL NOT NULL DEFAULT 0
);

CREATE TABLE customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT NOT NULL,
    street TEXT NOT NULL,
    city TEXT NOT NULL,
    zip TEXT NOT NULL
);

CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL REFERENCES customers(id),
    shipping_method_id INTEGER NOT NULL REFERENCES shipping_methods(id),
    payment_method_id INTEGER NOT NULL REFERENCES payment_methods(id),
    shipping_price REAL NOT NULL DEFAULT 0,
    payment_price REAL NOT NULL DEFAULT 0,
    items_price REAL NOT NULL DEFAULT 0,
    total_price REAL NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'new',
    note TEXT,
    created_at TEXT NOT NULL
);

CREATE TABLE order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL,
    product_name TEXT NOT NULL,
    variant TEXT NOT NULL DEFAULT '',
    quantity INTEGER NOT NULL,
    unit_price REAL NOT NULL
);
");

// ============================================================
// DATA – Kategorie
// ============================================================
$categories = [
    ['Herní myši',     'mysi',         'assets/images/kategoriemysi.jpg',         'Přesné herní myši pro FPS i MOBA.'],
    ['Klávesnice',     'klavesnice',   'assets/images/kategorieklavesnic.jpg',    'Mechanické a herní klávesnice.'],
    ['Headsety',       'headsety',     'assets/images/kategorieheadsetu.jpg',     'Headsety s prostorovým zvukem.'],
    ['Monitory',       'monitory',     'assets/images/kategoriemonitoru.jpg',     'Herní monitory s vysokou obnovovací frekvencí.'],
    ['Konzole',        'konzole',      'assets/images/kategoriekonzoli.jpg',      'Herní konzole nové generace.'],
    ['Příslušenství',  'prislusenstvi','assets/images/kategorieprislusenstvi.jpg','Podložky, kabely a další doplňky.'],
];
$stmt = $pdo->prepare('INSERT INTO categories (name, slug, image, description) VALUES (?, ?, ?, ?)');
foreach ($categories as $c) {
    $stmt->execute($c);
}

// ============================================================
// DATA – Produkty
// (category_id, name, slug, price, original_price, description, image, is_featured)
// ============================================================
$mouse = 'assets/images/myš.jpg';
$products = [
    // Myši (kat 1)
    [1, 'Logitech G Pro X Superlight', 'logitech-g-pro-x-superlight', 2999, 3499, 'Lehká bezdrátová myš pro kompetitivní hraní.', $mouse, 1],
    [1, 'Glorious Model O Wireless',   'glorious-model-o-wireless',   2300, null, 'Ultralehká myš s děrovaným tělem.', 'assets/images/mys2.jpg', 1],
    [1, 'HyperX Pulsefire Haste 2',    'hyperx-pulsefire-haste-2',    1900, null, 'Bezdrátová herní myš s 26 000 DPI senzorem.', 'assets/images/mys3.jpg', 0],
    [1, 'Razer DeathAdder V3',         'razer-deathadder-v3',          1799, 1999, 'Ergonomická myš pro pravotočivé hráče.', $mouse, 0],

    // Klávesnice (kat 2)
    [2, 'Razer Huntsman Mini',         'razer-huntsman-mini',         2700, null, 'Kompaktní 60% mechanická klávesnice.', 'assets/images/klavesnice.jpg', 1],
    [2, 'Logitech G Pro X TKL',        'logitech-g-pro-x-tkl',        3499, 3899, 'Bezdrátová tenkeyless herní klávesnice.', 'assets/images/klavesnice.jpg', 0],
    [2, 'SteelSeries Apex Pro',        'steelseries-apex-pro',        4990, null, 'Klávesnice s nastavitelnou aktivační silou.', 'assets/images/klavesnice.jpg', 0],

    // Headsety (kat 3)
    [3, 'HyperX Cloud II',             'hyperx-cloud-ii',             2199, 2599, 'Pohodlný headset s 7.1 surround zvukem.', 'assets/images/headset.jpg', 1],
    [3, 'SteelSeries Arctis Nova 7',   'steelseries-arctis-nova-7',   4290, null, 'Bezdrátový herní headset s dvojím připojením.', 'assets/images/headset.jpg', 0],

    // Monitory (kat 4)
    [4, 'AOC 24G2',                    'aoc-24g2',                    3999, null, '24" IPS monitor s 144 Hz pro herní zážitek.', 'assets/images/monitor.jpg', 1],
    [4, 'LG UltraGear 27GP850',        'lg-ultragear-27gp850',        9990, 11990, '27" QHD herní monitor s 165 Hz.', 'assets/images/monitor.jpg', 0],

    // Konzole (kat 5)
    [5, 'PlayStation 5 Slim',          'playstation-5-slim',          12990, null, 'Konzole nové generace, vydání Slim.', 'assets/images/PS5.jpg', 1],

    // Příslušenství (kat 6)
    [6, 'Podložka pod myš XL',         'podlozka-pod-mys-xl',          499, 699, 'Velká herní podložka 90×40 cm.', 'assets/images/podlozka.jpg', 0],
];
$stmt = $pdo->prepare('INSERT INTO products (category_id, name, slug, price, original_price, description, image, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
foreach ($products as $p) {
    $stmt->execute($p);
}

// ============================================================
// DATA – galerie a parametry vybraných produktů
// ============================================================
$imgStmt = $pdo->prepare('INSERT INTO product_images (product_id, image) VALUES (?, ?)');
foreach ([$mouse, 'assets/images/mys2.jpg', 'assets/images/mys3.jpg'] as $img) {
    $imgStmt->execute([1, $img]);
}

$paramStmt = $pdo->prepare('INSERT INTO product_parameters (product_id, name, value, type) VALUES (?, ?, ?, ?)');
// Logitech G Pro X Superlight (id 1) – výběr barvy + info
$paramStmt->execute([1, 'Barva', 'Černá, Bílá, Růžová', 'select']);
$paramStmt->execute([1, 'Typ', 'Bezdrátová', 'info']);
$paramStmt->execute([1, 'Hmotnost', '63 g', 'info']);
$paramStmt->execute([1, 'Rozlišení', '25 600 DPI', 'info']);
$paramStmt->execute([1, 'Připojení', 'USB / 2.4 GHz', 'info']);

// Razer Huntsman Mini (id 5) – výběr barvy
$paramStmt->execute([5, 'Barva', 'Černá, Bílá', 'select']);
$paramStmt->execute([5, 'Layout', '60%', 'info']);
$paramStmt->execute([5, 'Spínače', 'Razer Optical', 'info']);

// HyperX Cloud II (id 8) – výběr barvy
$paramStmt->execute([8, 'Barva', 'Černá, Červená', 'select']);
$paramStmt->execute([8, 'Zvuk', '7.1 Surround', 'info']);
$paramStmt->execute([8, 'Konektor', '3.5 mm + USB', 'info']);

// Monitor AOC 24G2 (id 10)
$paramStmt->execute([10, 'Úhlopříčka', '24"', 'info']);
$paramStmt->execute([10, 'Obnovovací frekvence', '144 Hz', 'info']);
$paramStmt->execute([10, 'Panel', 'IPS', 'info']);

// ============================================================
// DATA – doprava a platba
// ============================================================
$shipStmt = $pdo->prepare('INSERT INTO shipping_methods (name, price, delivery_days) VALUES (?, ?, ?)');
$shipStmt->execute(['Kurýr DPD',        99.0,  '1–2 pracovní dny']);
$shipStmt->execute(['Zásilkovna',       69.0,  '2–3 pracovní dny']);
$shipStmt->execute(['Osobní odběr Praha', 0.0, 'Do 24 hodin']);

$payStmt = $pdo->prepare('INSERT INTO payment_methods (name, price) VALUES (?, ?)');
$payStmt->execute(['Platba kartou online', 0.0]);
$payStmt->execute(['Bankovní převod',      0.0]);
$payStmt->execute(['Dobírka',              39.0]);

echo "Databáze byla vytvořena: $dbPath\n";
echo "Kategorií: " . count($categories) . "\n";
echo "Produktů: "  . count($products)   . "\n";
