<?php

if (!function_exists('array_chunks')) {
    /**
     * Divide um array em blocos menores de forma segura para grandes processamentos.
     *
     * @return iterable<int, array<mixed>>
     */
    function array_chunks(array $items, int $size = 100): iterable
    {
        if ($size < 1) {
            $size = 1;
        }

        $chunk = [];

        foreach ($items as $item) {
            $chunk[] = $item;

            if (count($chunk) === $size) {
                yield $chunk;
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            yield $chunk;
        }
    }
}

if (!function_exists('format_currency')) {
    /**
     * Formata valores numéricos respeitando locale/moeda sem acoplar domínio.
     */
    function format_currency(mixed $value, string $locale = 'pt_BR', string $currency = 'BRL'): string
    {
        if (is_string($value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }

        if (!is_numeric($value)) {
            return (string) $value;
        }

        $number = (float) $value;

        $fallback = static fn(): string => number_format(
            $number,
            2,
            $locale === 'pt_BR' ? ',' : '.',
            $locale === 'pt_BR' ? '.' : ','
        );

        if (!class_exists(\NumberFormatter::class)) {
            return $fallback();
        }

        try {
            $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
            $formatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
            $formatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, '');

            $formatted = $formatter->format($number);

            if ($formatted === false) {
                return $fallback();
            }

            $formatted = str_replace(' ', '', $formatted);

            if ($locale === 'pt_BR' && $currency === 'BRL') {
                $formatted = str_replace(',', '.', $formatted);
                $pos = strrpos($formatted, '.');

                if ($pos !== false) {
                    $formatted = substr_replace($formatted, ',', $pos, 1);
                }
            }

            return $formatted;
        } catch (\Throwable) {
            return $fallback();
        }
    }
}

if (!function_exists('read_csv_chunks')) {
    /**
     * Lê um arquivo CSV em blocos menores para evitar alto uso de memória.
     *
     * @return iterable<int, array<int, array<string, mixed>|array<int, mixed>>>
     */
    function read_csv_chunks(
        string $path,
        int $chunkSize = 1000,
        string $delimiter = ',',
        bool $hasHeader = true
    ): iterable {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        if ($chunkSize < 1) {
            $chunkSize = 1;
        }

        $file = new \SplFileObject($path, 'r');
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($delimiter);

        $headers = null;
        $chunk = [];

        foreach ($file as $row) {
            if ($row === false || $row === [null]) {
                continue;
            }

            if ($hasHeader && $headers === null) {
                $headers = $row;
                continue;
            }

            $chunk[] = $hasHeader && is_array($headers)
                ? map_row_to_headers($headers, $row)
                : $row;

            if (count($chunk) === $chunkSize) {
                yield $chunk;
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            yield $chunk;
        }
    }
}
