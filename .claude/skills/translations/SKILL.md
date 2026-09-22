---
name: translations
description: Keep the lang/*.json files in step with the code using the kollek:localize command. Use when UI copy changes, new strings are added, or locale files are out of sync. Trigger whenever translation keys, lang files, i18n, or __() / @lang() strings are mentioned.
---

# Translations

The strings themselves are the keys, in one JSON file per locale under `lang/`. A key missing from a locale falls back to the key, which is the English sentence, so a missing translation reads as English rather than as a blank.

`composer kollek:locale` is what keeps those files in step with the code. It runs `php artisan kollek:localize` across every locale the app ships. You MUST NOT add, remove or reorder a key by hand.

The command takes the locales as a required comma separated argument, so running the artisan command directly means naming them: `php artisan kollek:localize en,fr_FR,es_ES,de_DE,pt_BR,zh_CN,ja_JP`. The composer script already holds that list, which is why it is the one to reach for.

## What the command does

- Reads every `__()`, `trans()`, `trans_choice()`, `trans_key()` and `@lang()` in `resources/views` and in `app/Actions`, `app/Enums`, `app/Helpers`, `app/Http`, `app/Jobs`, `app/Mail`, `app/Models`, `app/Rules`, `app/Services`, `app/ValueObjects` and `app/ViewModels`. `app/Console` is left out on purpose, since it holds command output rather than screen copy.
- Writes `lang/en.json` with every string found, each key its own value.
- Writes every other locale with the same keys in the same order, keeping the translations already there and leaving an empty string for anything new. An empty value is how the command says "this one still needs translating".
- Drops any key the code no longer asks for, from every locale.
- Says only `Locale files synchronized.` when it is done. It does not report what it added, dropped or is still missing, so you MUST read the files to find the empty values.

## Checking

`bash ./scripts/check-translations.sh` is the check, and CI runs it. It fails when a locale does not hold exactly the same keys in exactly the same order as `lang/en.json`, when a key or value is not a string, or when any value is empty or only whitespace.

`tests/Feature/LocalizationTest.php` covers the same ground from the suite: a file per locale in `config('app.supported_locales')`, every English key translated, and every placeholder preserved.

## When you change or add copy

1. Write the string in the code as usual.
2. Run `composer kollek:locale`.
3. Open each `lang/*.json` and translate every value the command left empty.
4. Run `bash ./scripts/check-translations.sh` and confirm it passes.

Running the command rewrites all of the locale files, so it can pull unrelated untranslated keys into your diff. For a focused change touching one or two strings, adding the keys by hand to every locale, in the same position in each file, keeps the diff readable. The check is strict about order, so the position has to match.

## Writing a translation

- You MUST preserve placeholders exactly: `:name`, `:count`, `:app`, `:time`.
- You MUST preserve any HTML or Markdown, identically across locales.
- You MUST match the tense, tone and terminology already used in that locale's file. The English is plain and calm, and the translations are too.
- You MUST NOT leave a value empty, and you MUST NOT leave the key out. The check fails on both: an empty value is reported as untranslated, and a missing key breaks the key for key comparison against `lang/en.json`. Every key has to carry a real translation in every locale.
- Logs read as something somebody did, and each language has its own way of saying it: simple past in English ("Created the account"), passé composé in French ("A créé le compte"), Perfekt in German ("Hat das Konto erstellt").
