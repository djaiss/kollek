---
name: add-locale
description: Add a new locale to the application. Use when the user wants to support an additional language, register a locale in config, and generate its lang/*.json translation file. Trigger whenever adding a language, new locale, or i18n support is mentioned.
---

# Add a new locale

You MUST use ISO 15897 names for region-specific languages, e.g. `fr_FR` for French from France and `es_ES` for Spanish from Spain.

A locale is registered in two places, and both are needed. `config/app.php` holds the flat list the validation rules and the tests read. `config/docs.php` holds what the public site needs to draw a language and build its URLs. Neither is derived from the other.

You MUST follow these steps, in order:

1. You MUST add the locale to the `supported_locales` array in `config/app.php`:
   ```php
   'supported_locales' => ['en', 'fr_FR', 'es_ES', 'de_DE', 'pt_BR', 'zh_CN', 'ja_JP', 'nl_NL'],
   ```

2. You MUST add the locale to the `locales` array in `config/docs.php`, with a `url` (the prefix the public site serves it under, the bare language), a `code` (the same thing upper case, shown as a badge), a `label` (the language in its own words) and a `flag` (the flag emoji, never an image and never CSS):
   ```php
   'nl_NL' => ['url' => 'nl', 'code' => 'NL', 'label' => 'Nederlands', 'flag' => '🇳🇱'],
   ```

3. You MUST add the locale to the `kollek:locale` script in `composer.json`, which carries the list the localize command is run with. A locale missing from it never gets a file.

4. You MUST run `composer kollek:locale`. It creates `lang/{locale}.json` holding every key, with an empty string for each one still to translate. You MUST NOT create or order that file by hand. The command prints only `Locale files synchronized.`, so it will not tell you what is missing: the empty values are what is missing.

5. You MUST translate every empty value in `lang/{locale}.json`. Keep the `:placeholders` untouched, and match the plain, calm tone of `lang/en.json`. You MUST NOT leave a value empty and you MUST NOT remove a key, since the check compares the keys of every locale against `lang/en.json` in order.

6. You MUST run `bash ./scripts/check-translations.sh` and confirm it passes.

7. You MUST NOT touch the language pickers. The marketing picker and the docs pages read `config('docs.locales')`, so a locale registered in step 2 shows up on its own. The validation rules are a different matter: they read `config('app.supported_locales')`, which is exactly why step 1 exists.

8. You MUST run `php artisan test` afterwards. `tests/Feature/LocalizationTest.php` walks every supported locale, so a locale registered without a translation file, or with a placeholder dropped, fails there.
