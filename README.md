# E-shop – 2. fáze (PHP + SQLite)

Druhá fáze projektu e-shopu – frontend z 1. fáze propojený s PHP backendem a SQLite databází.

## Spuštění

```bash
php projekt-eshop/database/init.php     # inicializace databáze (eshop.db)
php -S 0.0.0.0:8080 -t projekt-eshop    # spuštění dev serveru
```

Aplikace běží na http://localhost:8080.

## Struktura

```
projekt-eshop/
├── index.php                       # homepage – doporučené produkty, kategorie
├── kategorie.php                   # výpis všech kategorií
├── produkty.php                    # produkty (?slug=kategorie pro filtr)
├── produkt.php                     # detail produktu (?slug=...)
├── kosik.php                       # obsah košíku, změna množství
├── objednavka-1.php                # krok 1: dodací údaje + validace
├── objednavka-2.php                # krok 2: doprava a platba
├── objednavka-3.php                # krok 3: shrnutí + odeslání
├── objednavka-potvrzeni.php        # potvrzení objednávky
├── vyhledavani.php                 # vyhledávání produktů
├── kontakt.php                     # kontaktní formulář + validace
├── o-nas.php
├── 404.php                         # stránka pro neexistující obsah
├── src/                            # bootstrap, Database, Cart, Validator, DTO, Repository
├── partials/                       # header, footer, product-card
├── assets/                         # CSS + obrázky
└── database/                       # init.php + eshop.db
```

## Implementované funkce

- Dynamické načítání kategorií a produktů z DB
- Detail produktu s galerií, parametry a variantami (dropdown)
- Funkční košík v session (přidání, změna množství, odebrání, varianty)
- Třístupňový objednávkový proces s validací (Validator, fluent interface)
- Vyhledávání produktů podle názvu a popisu
- Vlastní 404 stránka
- CSRF token a Post/Redirect/Get u všech POST formulářů
- htmlspecialchars u všech výpisů, prepared statements ve všech dotazech
