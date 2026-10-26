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
require_once __DIR__ . '/verantwoording.php';

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
 * Afgeleid uit de documentroot, zodat het pad klopt binnen een site en los
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
 * bijvoorbeeld achter een login. De host zet hem vóór cm_verwerk().
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
 * Draait de motor los, of binnen een site? Los is het opgevraagde script een
 * bestand in deze map, zoals de index.php van een formulier. Binnen een site
 * is het de parser van de site, die index.php alleen insluit. Een host die het
 * anders wil, geeft het zelf mee aan cm_verwerk() of cm_kiezer().
 */
function cm_zelfstandig(): bool
{
    $script = realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));

    return $script !== false && str_starts_with($script, (string) realpath(CM_ROOT) . DIRECTORY_SEPARATOR);
}

/**
 * Toont een formulier, of rekent de antwoorden door tot een document.
 *
 * Binnen een site levert dit alleen de inhoud, en zet de site er kop, menu en
 * opmaak omheen. Los levert het een hele pagina met de neutrale opmaak van
 * standalone.css. Zie cm_zelfstandig().
 */
function cm_verwerk(string $id, ?bool $zelfstandig = null): void
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
            $blokken = cm_verantwoord($formulier['document']($antwoorden, $uitkomst), $antwoorden, $uitkomst);

            if ($alsDocument) {
                cm_stuur_odt($blokken, cm_bestandsnaam((string) ($antwoorden['projectnaam'] ?? ''), 'odt'),
                    (string) ($antwoorden['aanvrager'] ?? ''), cm_kenmerk(cm_vingerafdruk($antwoorden)));
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

    require ($zelfstandig ?? cm_zelfstandig()) ? __DIR__ . '/document.php' : $inhoud;
}

/**
 * Het adres van de voordeur, en het staartje achter elk formulier.
 *
 * Een formulier staat een map dieper dan de voordeur. Alleen het staartje
 * verschilt: index.php, of /standalone als een site daarmee de losse pagina
 * opvraagt. Vanaf een formulier haalt $formulier die map eraf.
 *
 * @return array{0:string,1:string} de basis zonder slash aan het eind, en het staartje
 */
function cm_adres(?string $formulier = null): array
{
    $pad = rtrim((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
    [$basis, $staart] = match (true) {
        str_ends_with($pad, '/standalone') => [substr($pad, 0, -strlen('/standalone')), '/standalone'],
        str_ends_with($pad, '/index.php') => [substr($pad, 0, -strlen('/index.php')), '/index.php'],
        default => [$pad, ''],
    };

    if ($formulier !== null && str_ends_with($basis, '/' . $formulier)) {
        $basis = substr($basis, 0, -strlen('/' . $formulier));
    }

    return [$basis, $staart];
}

/** De URL van de voordeur, ook vanaf een formulier. */
function cm_voordeur(?string $formulier = null): string
{
    [$basis, $staart] = cm_adres($formulier);

    return $basis . ($staart === '' ? '/' : $staart);
}

/** De fasen op de voordeur, in volgorde. Een formulier noemt zijn fase in 'fase'. */
const CM_FASEN = [
    'start' => [
        'titel' => 'Vóór de start',
        'intro' => 'Moet dit project er komen? Dit vul je in voordat er geld naar een project gaat.',
    ],
    'ontwerp' => [
        'titel' => 'Ontwerp en risico’s',
        'intro' => 'Wat vragen de wetten en regels, en welke risico’s zijn er? De quickscan laat zien welke van de '
            . 'andere scans nodig zijn.',
    ],
    'toetsen' => [
        'titel' => 'Toetsen en inkoop',
        'intro' => 'Voldoet de dienst van een leverancier, en staat het project er goed voor? Deze vult een ander '
            . 'in: de leverancier of het CIO office.',
    ],
    '' => [
        'titel' => 'Overige formulieren',
        'intro' => '',
    ],
];

/**
 * De normenkaders van een dossier. Een formulier met 'normenkader' hoort bij
 * één ervan; het dossier toont alleen de formulieren van zijn eigen kader.
 */
const CM_NORMENKADERS = [
    'bio2' => 'BIO2 en Cyberbeveiligingswet',
    'bio' => 'BIO',
];

/** De voordeur: de formulieren per fase, en de dossiers. */
function cm_kiezer(?bool $zelfstandig = null): void
{
    [$basis, $staart] = cm_adres();
    $formulieren = [];

    foreach (cm_config()['formulieren'] as $id) {
        $formulier = cm_definitie($id);
        $formulier['href'] = $basis . '/' . $id . $staart;
        $formulieren[] = $formulier;
    }

    $fasen = [];
    foreach ($formulieren as $formulier) {
        $fase = isset(CM_FASEN[$formulier['fase'] ?? '']) ? (string) ($formulier['fase'] ?? '') : '';
        $fasen[$fase][] = $formulier;
    }
    $fasen = array_filter(array_replace(array_fill_keys(array_keys(CM_FASEN), []), $fasen));

    $titel = 'Cisomatic';
    $inhoud = __DIR__ . '/kiezer.php';

    require ($zelfstandig ?? cm_zelfstandig()) ? __DIR__ . '/document.php' : $inhoud;
}

/**
 * Wat het script van het dossier over de formulieren moet weten: titel,
 * fase, normenkader, adres, en of het in een dossier kan.
 *
 * @param list<array<string,mixed>> $formulieren
 * @return list<array<string,mixed>>
 */
function cm_dossier_formulieren(array $formulieren): array
{
    return array_map(static fn (array $f): array => [
        'id' => $f['id'],
        'titel' => $f['titel'],
        'naam' => $f['naam'],
        'fase' => $f['fase'] ?? '',
        'normenkader' => $f['normenkader'] ?? '',
        'dossier' => $f['dossier'] ?? true,
        'opvolger' => $f['opvolger'] ?? '',
        'href' => $f['href'],
    ], $formulieren);
}

/**
 * De versies uit wijzigingen/versies.php, de nieuwste eerst.
 *
 * @return list<array{versie:string,datum:string,wijzigingen:list<string>}>
 */
function cm_versies(): array
{
    static $versies = null;

    return $versies ??= require CM_ROOT . '/wijzigingen/versies.php';
}

/** De versie die nu draait: de bovenste uit wijzigingen/versies.php. */
function cm_versie(): string
{
    return cm_versies()[0]['versie'];
}

/** Het adres van de pagina met wijzigingen, ook vanaf een formulier of die pagina zelf. */
function cm_wijzigingen_adres(?string $vanaf = null): string
{
    [$basis, $staart] = cm_adres($vanaf);

    return $basis . '/wijzigingen' . $staart;
}

/** De pagina met wijzigingen: per versie wat er veranderd is. */
function cm_wijzigingen(?bool $zelfstandig = null): void
{
    $versies = cm_versies();
    $voordeur = cm_voordeur('wijzigingen');

    $titel = 'Wat is er veranderd · Cisomatic';
    $inhoud = __DIR__ . '/wijzigingen.php';

    require ($zelfstandig ?? cm_zelfstandig()) ? __DIR__ . '/document.php' : $inhoud;
}
