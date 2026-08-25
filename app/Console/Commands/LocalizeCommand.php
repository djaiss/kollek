<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class LocalizeCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'kollek:localize {locales}';

    /**
     * @var string
     */
    protected $description = 'Extract translation strings and synchronize locale JSON files';

    public function handle(): int
    {
        $localesArgument = (string) $this->argument('locales');
        $locales = array_filter(array_map(trim(...), explode(',', $localesArgument)));

        if ($locales === []) {
            $this->error('No locales provided.');

            return self::FAILURE;
        }

        $translationKeys = $this->extractTranslationKeys();

        $this->syncEnglishLocaleFile($translationKeys);

        foreach ($locales as $locale) {
            if ($locale === 'en') {
                continue;
            }

            $this->syncLocaleFile($locale, $translationKeys);
        }

        $this->info('Locale files synchronized.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function extractTranslationKeys(): array
    {
        // Everywhere user facing copy is written. The list used to stop at the
        // controllers, the actions and the jobs, which meant a string on an enum
        // or a view model was never extracted, and a key used from a model was
        // reported as stale and deleted on the next run.
        //
        // app/Console stays out on purpose: it holds command output rather than
        // screen copy, and the extractor reads comments as well as code, so the
        // `__('10')` in this very file would become a key.
        $directories = [
            base_path('resources/views'),
            base_path('app/Actions'),
            base_path('app/Enums'),
            base_path('app/Helpers'),
            base_path('app/Http'),
            base_path('app/Jobs'),
            base_path('app/Mail'),
            base_path('app/Models'),
            base_path('app/Rules'),
            base_path('app/Services'),
            base_path('app/ValueObjects'),
            base_path('app/ViewModels'),
        ];

        $keysByValue = [];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new RegexIterator(
                new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)),
                '/^.+\.php$/i',
            );

            foreach ($iterator as $fileInfo) {
                $contents = file_get_contents($fileInfo->getPathname());

                if ($contents === false) {
                    continue;
                }

                foreach ($this->extractKeysFromContent($contents) as $key) {
                    $keysByValue[$key] = true;
                }
            }
        }

        // A key that looks like a number, e.g. __('10'), comes back out of the array as an int,
        // since PHP coerces numeric string keys. Cast it back so the callers keep their strings.
        return array_map(strval(...), array_keys($keysByValue));
    }

    /**
     * @return array<int, string>
     */
    private function extractKeysFromContent(string $content): array
    {
        // trans_choice carries the pluralized keys, e.g. ":count item|:count items". It was
        // missing here, so none of them ever reached the locale files.
        $functions = ['__', 'trans', 'trans_choice', '@lang', 'trans_key'];
        $patterns = [];

        foreach ($functions as $function) {
            $patterns[] = '/'.preg_quote($function, '/').'\(\s*\'((?:\\\\.|[^\'\\\\])*)\'/';
            $patterns[] = '/'.preg_quote($function, '/').'\(\s*"((?:\\\\.|[^"\\\\])*)"/';
        }

        $keys = [];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);

            foreach ($matches[1] ?? [] as $match) {
                $keys[] = stripcslashes($match);
            }
        }

        return $keys;
    }

    /**
     * @param  array<int, string>  $translationKeys
     */
    private function syncEnglishLocaleFile(array $translationKeys): void
    {
        $this->syncLocaleFile('en', $translationKeys);
    }

    /**
     * @param  array<int, string>  $translationKeys
     */
    private function syncLocaleFile(string $locale, array $translationKeys): void
    {
        $localeFile = lang_path($locale.'.json');
        $existingTranslations = [];

        if (is_file($localeFile)) {
            $contents = file_get_contents($localeFile);
            $decoded = json_decode((string) $contents, true);

            if (is_array($decoded)) {
                $existingTranslations = $decoded;
            }
        }

        $syncedTranslations = [];

        foreach ($translationKeys as $key) {
            $syncedTranslations[$key] = $existingTranslations[$key] ?? $this->defaultValueForLocale($locale, $key);
        }

        ksort($syncedTranslations);

        file_put_contents(
            $localeFile,
            json_encode($syncedTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL,
        );
    }

    private function defaultValueForLocale(string $locale, string $key): string
    {
        if ($locale === 'en') {
            return $key;
        }

        return '';
    }
}
