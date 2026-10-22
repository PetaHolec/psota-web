# PSOTA Group – nový web

HTML + CSS + JS + PHP (bez frameworku a bez databáze). Struktura URL je stejná jako na původním webu
(`/stk/ceniky/stk-znojmo`, `/nakladni-doprava/kontakty`, `/aktuality-stk/121-…`), takže staré odkazy a SEO zůstanou funkční.

## Spuštění lokálně

```bash
php -S localhost:8080 router.php
```

## Nasazení

Nahrát celý adresář na hosting s PHP 8.1+ (Apache s `mod_rewrite` – `.htaccess` je připravený).
Pro nginx: všechny požadavky, které nejsou soubor, směrovat na `index.php`; blokovat `/inc`, `/views`, `/data`.

## Struktura

| Cesta | Obsah |
|---|---|
| `index.php` | front controller / router |
| `inc/lib.php` | pomocné funkce, mapa divizí, pobočky STK, navigace, ikony |
| `inc/layout.php` | hlavička, patička, hero, formulář, boční karta s kontakty |
| `views/` | šablony stránek (úvod, obecná stránka, aktuality, kontakt, o nás, 404, sitemap) |
| `data/pages.json` | veškerý obsah stránek převzatý z původního webu (texty, ceníky, kontakty, otevírací doby) |
| `data/news.json` | aktuality STK |
| `api/contact.php` | zpracování kontaktního formuláře (honeypot, validace, `mail()` na info@psota-znojmo.cz, záloha do `data/messages.log`) |
| `assets/css/style.css`, `assets/js/main.js` | design a animace |
| `assets/images/`, `assets/thumbs/` | obrázky a PDF z původního webu (velké fotky zmenšeny na 1600 px, náhledy 720 px) |

## Úpravy obsahu

Texty a ceníky jsou v `data/pages.json` (klíč = cesta stránky, `/` nahrazeno `__`). Nové aktuality přidejte na začátek `data/news.json`
(`slug`, `title`, `date` ve tvaru `DD.MM.RRRR`, `iso` ve tvaru `RRRR-MM-DD`, `body` jako HTML).
Týdenní menu restaurace se mění v `views/page.php` (obrázky `MENU_…png`, `menu_…png` v `assets/images/`).
