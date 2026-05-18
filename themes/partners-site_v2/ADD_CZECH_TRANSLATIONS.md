# Adding Czech Translations to partners_site_v2 Theme

## Current Status

✅ **Translation Infrastructure EXISTS**
- Czech PO file: `languages/partners-site_v2-cs_CZ.po` (31 strings translated)
- Czech MO file: `languages/partners-site_v2-cs_CZ.mo` (compiled)
- i18n setup: `includes/i18n-setup.php` (properly configured)
- Locale support: Czech (cs_CZ) included in locale map

❌ **Twig Templates NOT Translatable**
- 149 Twig files with hardcoded Polish text
- No translation functions registered in Twig
- Frontend shows Polish to Czech users

❌ **ACF Fields NOT Translatable**
- 50+ ACF field files with hardcoded Polish labels
- Admin interface shows Polish to Czech editors

## Implementation Plan

### Phase 1: Add Translation Functions to Twig (30 minutes)

**File to modify:** `/includes/classes/Controller.class.php`

**Add after line 44 (after uniqueID function):**

```php
// Add translation functions for Twig templates
$customFunctions[] = new \\Twig\\TwigFunction(
	'__',
	function ( $text, $domain = 'partners-site_v2' ) {
		return __( $text, $domain );
	}
);

$customFunctions[] = new \\Twig\\TwigFunction(
	'_e',
	function ( $text, $domain = 'partners-site_v2' ) {
		return __( $text, $domain );
	}
);

$customFunctions[] = new \\Twig\\TwigFunction(
	'trans',
	function ( $text, $domain = 'partners-site_v2' ) {
		return __( $text, $domain );
	}
);
```

**Also add in the `render()` method** (around line 75, same location).

### Phase 2: Update High-Priority Twig Files (4-6 hours)

**Priority 1 - Sell Car Form (6 files):**

1. `/layouts/sellcar/steps/step1.twig`
2. `/layouts/sellcar/steps/step2.twig`
3. `/layouts/sellcar/steps/step3.twig`
4. `/layouts/sellcar/steps/step4.twig`
5. `/layouts/sellcar/steps/step5.twig`
6. `/layouts/sellcar/steps/step6.twig`

**Example changes:**

```twig
{# Before #}
<h2>Krok 1/6</h2>
<label>Model Pojazdu</label>
<p>Jaki posiadasz model pojazdu?</p>

{# After #}
<h2>{{ __('Krok 1/6') }}</h2>
<label>{{ __('Model Pojazdu') }}</label>
<p>{{ __('Jaki posiadasz model pojazdu?') }}</p>
```

**Priority 2 - Forms & Contact (4 files):**

7. `/components/organisms/form/form.twig`
8. `/components/organisms/form-test-drive/form-test-drive.twig`
9. `/layouts/contact/contact.twig`
10. `/components/organisms/cookies/cookies.twig`

**Priority 3 - Stock Cars (3 files):**

11. `/components/organisms/stock-car/stock-car.twig`
12. `/layouts/stock-car-single/stock-car-single.twig`
13. `/layouts/stock/stock.twig`

### Phase 3: Update ACF Field Files (6-8 hours)

**High-Priority ACF Files:**

1. `/includes/acf-fields/stock-car.php` (1,653 lines)
2. `/includes/acf-fields/form-options.php` (259 lines)
3. `/includes/acf-fields/model.php`
4. `/includes/acf-fields/campaign.php`
5. `/includes/acf-fields/lead.php`

**Example changes:**

```php
// Before
'label' => 'Auto w archiwum',
'instructions' => 'Wprowadź model samochodu',

// After
'label' => __('Auto w archiwum', 'partners-site_v2'),
'instructions' => __('Wprowadź model samochodu', 'partners-site_v2'),
```

### Phase 4: Update Czech Translation File (1 hour)

**File:** `/languages/partners-site_v2-cs_CZ.po`

**Add all new strings with Czech translations:**

```po
msgid "Krok 1/6"
msgstr "Krok 1/6"

msgid "Model Pojazdu"
msgstr "Model vozidla"

msgid "Jaki posiadasz model pojazdu?"
msgstr "Jaký model vozidla máte?"

msgid "Imię"
msgstr "Jméno"

msgid "Nazwisko"
msgstr "Příjmení"

msgid "Numer telefonu"
msgstr "Telefonní číslo"

msgid "E-mail"
msgstr "E-mail"

msgid "Wiadomość"
msgstr "Zpráva"

msgid "Wyślij wiadomość"
msgstr "Odeslat zprávu"

msgid "Pola obowiązkowe"
msgstr "Povinná pole"

msgid "Zaakceptuj wszystkie"
msgstr "Přijmout vše"

msgid "Zaakceptuj tylko wymagane"
msgstr "Přijmout pouze požadované"
```

### Phase 5: Compile Translations (5 minutes)

```bash
cd /www/wwwroot/main-stage.volvotest.pl/web/app/themes/partners-site_v2/languages
msgfmt partners-site_v2-cs_CZ.po -o partners-site_v2-cs_CZ.mo
```

## Quick Start - Add Translation Function

**Step 1: Edit Controller.class.php**

```bash
nano /www/wwwroot/main-stage.volvotest.pl/web/app/themes/partners-site_v2/includes/classes/Controller.class.php
```

**Step 2: Find line 44** (after uniqueID function)

**Step 3: Add this code:**

```php
// Add translation functions for Twig templates
$customFunctions[] = new \\Twig\\TwigFunction(
	'__',
	function ( $text, $domain = 'partners-site_v2' ) {
		return __( $text, $domain );
	}
);

$customFunctions[] = new \\Twig\\TwigFunction(
	'trans',
	function ( $text, $domain = 'partners-site_v2' ) {
		return __( $text, $domain );
	}
);
```

**Step 4: Repeat for the `render()` method** (around line 75)

**Step 5: Save and test**

## Testing Translation Function

**Create test file:** `/web/app/themes/partners-site_v2/test-translation.php`

```php
<?php
require_once __DIR__ . '/../../wp/wp-load.php';

// Switch to Czech site
switch_to_blog(CZECH_SITE_ID);

// Test translation
echo __('Imię', 'partners-site_v2'); // Should output Czech translation

restore_current_blog();
```

Run:
```bash
php /www/wwwroot/main-stage.volvotest.pl/web/app/themes/partners-site_v2/test-translation.php
```

## Czech Translation Reference

### Common UI Strings

| Polish | Czech |
|--------|-------|
| Imię | Jméno |
| Nazwisko | Příjmení |
| Numer telefonu | Telefonní číslo |
| E-mail | E-mail |
| Wiadomość | Zpráva |
| Wyślij | Odeslat |
| Wybierz | Vyberte |
| Pola obowiązkowe | Povinná pole |
| Zaakceptuj | Přijmout |
| Wszystkie | Vše |
| Tylko wymagane | Pouze požadované |
| Kontakt | Kontakt |
| O nas | O nás |
| Polityka prywatności | Zásady ochrany osobních údajů |
| Regulamin | Podmínky |

### Car-Related Terms

| Polish | Czech |
|--------|-------|
| Model Pojazdu | Model vozidla |
| Pierwsza rejestracja | První registrace |
| Silnik | Motor |
| Wyposażenie | Výbava |
| Przebieg | Ujetá vzdálenost |
| Skrzynia biegów | Převodovka |
| Paliwo | Palivo |
| Kolor | Barva |
| Wnętrze | Interiér |
| Cena | Cena |
| Dostępność | Dostupnost |

### Form Labels

| Polish | Czech |
|--------|-------|
| Dane kontaktowe | Kontaktní údaje |
| Informacje ogólne | Obecné informace |
| Wyrażam zgodę | Souhlasím |
| Zapoznałem się | Seznámil jsem se |
| Regulamin | Předpisy |
| Polityka prywatności | Zásady ochrany osobních údajů |
| To pole jest obowiązkowe | Toto pole je povinné |
| Wpisz swoje imię | Zadejte své jméno |
| Wpisz prawidłowy adres | Zadejte platnou adresu |

## Automated Translation Helper Script

I can create a script to help automate this process. Would you like me to:

1. **Create a script** that scans all Twig files and lists translatable strings?
2. **Create a script** that automatically wraps strings in `__()` function?
3. **Generate a complete Czech PO file** with all strings?

## Estimated Timeline

| Phase | Task | Time |
|-------|------|------|
| 1 | Add Twig translation functions | 30 min |
| 2 | Update 13 high-priority Twig files | 4-6 hours |
| 3 | Update remaining 136 Twig files | 8-12 hours |
| 4 | Update 50+ ACF field files | 6-8 hours |
| 5 | Update Czech PO file | 2-3 hours |
| 6 | Professional translation review | 4-6 hours |
| 7 | Testing | 2 hours |
| **Total** | **Full implementation** | **27-38 hours** |

## Quick Win - Priority Files Only

If you want Czech support for the most important parts only:

**Focus on:**
- Sell car form (6 files) - 4 hours
- Contact forms (4 files) - 2 hours
- Stock car pages (3 files) - 2 hours
- ACF stock-car fields (1 file) - 2 hours
- Update Czech PO file - 1 hour
- **Total: 11 hours**

This covers 80% of user-facing Czech text.

## Next Steps

**Option 1: Full Implementation**
- I can implement all 149 Twig files + 50 ACF files
- Complete Czech translation coverage
- 27-38 hours of work

**Option 2: Priority Files Only**
- Focus on user-facing forms and pages
- 80% coverage with 11 hours of work
- Can expand later

**Option 3: Manual Approach**
- I provide you with the helper script
- You or your team updates files manually
- I provide guidance and review

**Which approach would you prefer?**
