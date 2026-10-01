<?php

/**
 * Laadt de motor, de instellingen en het gekozen formulier.
 *
 * Een formulier is een map naast src/, met een definitie.php. Die geeft de
 * teksten, de bestanden van het formulier en de namen van de functies die de
 * motor aanroept. Zie README.md.
 */

declare(strict_types=1);

const CM_ROOT = __DIR__ . '/..';

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/antwoorden.php';
require_once __DIR__ . '/formulier.php';
require_once __DIR__ . '/opmaak.php';
require_once __DIR__ . '/odt.php';
require_once __DIR__ . '/opslag.php';

/** @return array<string,mixed> */
function cm_config(): array
{
    static $config = null;

    if ($config === null) {
        $config = require CM_ROOT . '/config.php';
        date_default_timezone_set((string) ($config['timezone'] ?? 'Europe/Amsterdam'));
    }

    return $config;
}

/**
 * URL van een bestand in deze map, met de wijzigingstijd erachter.
 *
 * Afgeleid uit de documentroot, zodat het pad klopt binnen de site en los
 * daarvan, waar de map ook staat. De tijd erachter laat een nieuwe versie
 * meteen doorkomen, ook bij een browser die het bestand een jaar bewaart.
 */
function cm_asset(string $pad): string
{
    $slash = static fn (string $p): string => str_replace('\\', '/', $p);
    $root = rtrim($slash((string) realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''))), '/');
    $map = $slash((string) realpath(CM_ROOT));

    if ($root === '' || !str_starts_with($map . '/', $root . '/')) {
        throw new RuntimeException('De map van de Cisomatic staat niet onder de documentroot.');
    }

    return substr($map, strlen($root)) . '/' . $pad . '?v=' . filemtime(CM_ROOT . '/' . $pad);
}

/**
 * De definitie van een formulier, zonder de code ervan te laden.
 *
 * @return array<string,mixed>
 */
function cm_definitie(string $id): array
{
    if (!in_array($id, cm_config()['formulieren'], true)) {
        throw new RuntimeException('Er is geen formulier met de naam ' . $id . '.');
    }

    return ['id' => $id] + require CM_ROOT . '/' . $id . '/definitie.php';
}

/**
 * Het formulier van dit verzoek. De eerste aanroep, met een id, laadt het. Er
 * is per verzoek maar één formulier, dus de motor vraagt het hier op.
 *
 * @return array<string,mixed>
 */
function cm_formulier(?string $id = null): array
{
    static $formulier = null;

    if ($id !== null && $formulier === null) {
        $formulier = cm_definitie($id);

        foreach ($formulier['bestanden'] as $bestand) {
            require_once CM_ROOT . '/' . $id . '/' . $bestand;
        }
    }

    return $formulier ?? throw new LogicException('Er is nog geen formulier geladen.');
}

/**
 * Een werkplek bewaart het concept op de server in plaats van in de browser,
 * zoals de utilities achter de login. De host zet hem vóór cm_verwerk().
 *
 * - antwoorden: waarmee het formulier opent
 * - velden: verborgen velden in het formulier, zoals een CSRF-token
 * - lijst: het adres van de lijst met documenten
 *
 * @param array{antwoorden:array<string,mixed>,velden:string,lijst:string}|null $werkplek
 * @return array{antwoorden:array<string,mixed>,velden:string,lijst:string}|null
 */
function cm_werkplek(?array $werkplek = null): ?array
{
    static $huidig = null;

    return $huidig = $werkplek ?? $huidig;
}

/**
 * Toont een formulier, of rekent de antwoorden door tot een document.
 *
 * Via de parser van de site levert dit alleen de inhoud, en zet de site er kop,
 * menu en opmaak omheen. Zelfstandig levert het een hele pagina met een eigen,
 * neutrale opmaak. Zo draait dezelfde map ook los van de site.
 */
function cm_verwerk(string $id, bool $zelfstandig): void
{
    $formulier = cm_formulier($id);
    $antwoorden = cm_werkplek()['antwoorden'] ?? [];
    $fouten = [];
    $uitkomst = null;
    $blokken = null;
    $opgeslagen = null;
    $opslagFout = null;

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        // De downloadknop stuurt de antwoorden terug als JSON. De server rekent alles
        // opnieuw uit, dus tussen twee verzoeken wordt niets bewaard.
        $alsDocument = ($_POST['uitvoer'] ?? '') === 'odt';
        $json = $_POST['antwoorden'] ?? null;
        $invoer = $alsDocument ? (is_string($json) ? json_decode($json, true, 16) : null) : $_POST;

        $antwoorden = cm_lees_antwoorden(is_array($invoer) ? $invoer : []);
        $fouten = cm_valideer($antwoorden);

        if ($fouten === []) {
            $uitkomst = $formulier['evalueer']($antwoorden);
            $blokken = $formulier['document']($antwoorden, $uitkomst);

            if ($alsDocument) {
                cm_stuur_odt($blokken, cm_bestandsnaam((string) ($antwoorden['projectnaam'] ?? ''), 'odt'), (string) ($antwoorden['aanvrager'] ?? ''));
            }

            try {
                $opgeslagen = cm_bewaar($blokken, $antwoorden, $uitkomst);
            } catch (RuntimeException $e) {
                $opslagFout = $e->getMessage();
            }
        }
    }

    $titel = $blokken === null ? $formulier['titel'] : cm_documenttitel($blokken);
    $inhoud = __DIR__ . '/pagina.php';

    require $zelfstandig ? __DIR__ . '/document.php' : $inhoud;
}

/** De voordeur: een lijst van alle formulieren. */
function cm_kiezer(bool $zelfstandig): void
{
    $formulieren = array_map('cm_definitie', cm_config()['formulieren']);

    // Een formulier staat een map dieper dan deze pagina, zowel binnen de site
    // als zelfstandig. Alleen het staartje verschilt.
    $pad = rtrim((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
    [$basis, $staart] = match (true) {
        str_ends_with($pad, '/standalone') => [substr($pad, 0, -strlen('/standalone')), '/standalone'],
        str_ends_with($pad, '/index.php') => [substr($pad, 0, -strlen('/index.php')), '/index.php'],
        default => [$pad, ''],
    };

    $titel = 'Cisomatic';
    $inhoud = __DIR__ . '/kiezer.php';

    require $zelfstandig ? __DIR__ . '/document.php' : $inhoud;
}
