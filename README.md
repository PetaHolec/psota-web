# PSOTA Group – nový web (čisté HTML + CSS + JS)

Žádné PHP, žádná databáze. Na hosting nahrajte vše kromě složky `_generator/`.

- Otevřít lokálně: `python3 -m http.server 8080` ve složce, pak http://localhost:8080
- Jazyky: čeština v kořeni, překlady v `en/`, `de/`, `pl/` (stejná struktura stránek). Přepínač vlajek v hlavičce odkazuje na stejnou stránku v jiném jazyce.
- Styl: `assets/css/style.css`, chování a animace: `assets/js/main.js`
- Obrázky a PDF: `assets/images/`, náhledy fotogalerií `assets/thumbs/`
- Kontaktní formulář otevře e-mailový program s předvyplněnou zprávou pro info@psota-znojmo.cz

## Obsah webu

| Sekce | Adresa | Poznámka |
|---|---|---|
| Nákladní doprava | `/nakladni-doprava` | mapa tras, nabídka služeb, historie |
| Osobní doprava | `/osobni-doprava` | dříve zds-psota.cz: linky IDS JMK, turistický vláček, zájezdy, CNG a čerpací stanice, kariéra, ceny pohonných hmot, přepravní řád |
| Servisy a mytí | `/servisy` | dříve pplautocentrum.cz: autoservis, pneuservis, mytí, náhradní díly, IVECO, půjčovna dodávek, elektroauta BirdieCar/Cenntro |
| STK a emise | `/stk` | ceníky, stanice, aktuality |
| Učňovské středisko, Restaurace, Autocentrum, O nás, Kontakt | … | |

Sekce „Osobní doprava“ a „Servisy a mytí“ jsou vloženy jako podstránky přímo do tohoto webu (původně odkazovaly na jiné weby). Rezervace půjčovny, AI asistent a formulář ztrát a nálezů jsou statické ukázky bez serveru.

## Mapy provozoven

Mapy Google jsou jen na stránkách, kde je mají i původní weby (kontakty divizí, pobočky STK, půjčovna dodávek, ceny pohonných hmot, penzion, úvodní stránka). Přesné polohy a seznam stránek jsou v `_generator/data/locations.json` a `_generator/data/loc_rules.json`. Místa, která mají vlastní záznam v Google, se otevřou přímo na něm (`cid`); ostatní jsou vystředěná na souřadnice s červenou špendlíkovou značkou.

## Úpravy obsahu (volitelné, vyžaduje PHP a Python 3)

HTML stránky se generují ze složky `_generator/`:

1. texty a ceníky: `_generator/data/pages.json`, aktuality: `_generator/data/news.json`
2. překlady: `_generator/i18n/en.json`, `de.json`, `pl.json` (klíč = český text)
3. sestavení: `sh _generator/build_all.sh` (PHP → překlad → přepis odkazů; trvá několik minut)

Ruční úpravy v hotových `.html` souborech se při dalším sestavení přepíšou. Po úpravě `style.css` nebo `main.js` spusťte `build_all.sh` také, aby se aktualizoval parametr `?v=` (obejití cache prohlížeče).

## Známá omezení

- Aktuality starší než 10/2025 jsou jen česky; v jiných jazycích je zobrazen pouze nový výběr.
- Právně závazné texty (přepravní řád turistického vláčku, podmínky) zůstávají česky.
- Ceny, poplatky a údaje o STK/emisích jsou převzaty z původních webů; před spuštěním je nechte zkontrolovat provozovatelem.
