<?php

/**
 * Gedeeld door de scripts in deze map: alleen op de opdrachtregel, de motor
 * laden en een formulier kiezen. Zie README.md in deze map.
 */

declare(strict_types=1);

// Deze map komt mee naar een site. Via de webserver doen de scripts niets.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../src/bootstrap.php';

/** Stopt met een melding op stderr. */
function tl_stop(string $melding, int $code = 1): never
{
    fwrite(STDERR, $melding . "\n");
    exit($code);
}

/**
 * Laadt het formulier met deze naam, of stopt met de namen die er zijn.
 *
 * @return array<string,mixed>
 */
function tl_formulier(string $id): array
{
    $namen = cm_config()['formulieren'];

    if (!in_array($id, $namen, true)) {
        tl_stop('Onbekend formulier: ' . $id . '. Kies uit: ' . implode(', ', $namen) . '.');
    }

    return cm_formulier($id);
}

/** De opties die bij de argumenten staan, zoals --tekst, en de rest. @return array{0:list<string>,1:list<string>} */
function tl_argumenten(array $argv): array
{
    $vlaggen = [];
    $rest = [];

    foreach (array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '--')) {
            $vlaggen[] = substr($arg, 2);
        } else {
            $rest[] = $arg;
        }
    }

    return [$vlaggen, $rest];
}

/** Een voorwaarde als korte tekst: rol=samen, of uitkomst:omvang=hoog|zeer_hoog. */
function tl_voorwaarde(array $item, string $veld = 'toon_als'): string
{
    $delen = [];

    foreach ($item[$veld] ?? [] as $key => $waarden) {
        $delen[] = $key . '=' . implode('|', array_map('strval', (array) $waarden));
    }
    foreach ($item[$veld . '_uitkomst'] ?? [] as $naam => $waarden) {
        $delen[] = 'uitkomst:' . $naam . '=' . implode('|', array_map('strval', (array) $waarden));
    }

    return implode(' & ', $delen);
}
