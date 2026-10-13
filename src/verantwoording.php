<?php

/**
 * De verantwoording bij elk document: wat een auditor nodig heeft om het te
 * controleren. De motor zet het kenmerk op de titelpagina en in de
 * voettekst, en voegt onderaan een hoofdstuk toe:
 *
 * - Over dit document: formulier, kenmerk, moment, opsteller, versie van de
 *   regels en de grondslag
 * - Vaststelling: wie opstelt, beoordeelt en vaststelt, met ruimte voor een
 *   handtekening
 * - Alle antwoorden: elke vraag die van toepassing was, met het antwoord
 * - Controle: de vingerafdruk van de antwoorden, en hoe je die nagaat
 *
 * Een formulier dat de vaststelling of alle antwoorden al zelf in het
 * document zet, zet 'vaststelling' of 'antwoorden' in zijn definitie op false.
 */

declare(strict_types=1);

/**
 * De vingerafdruk van de antwoorden: SHA-256 over de schone antwoorden als
 * JSON. Dezelfde antwoorden geven altijd dezelfde vingerafdruk, ook als ze
 * later uit het antwoordbestand komen.
 */
function cm_vingerafdruk(array $a): string
{
    return hash('sha256', (string) json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

/** Het kenmerk van een document: het formulier en het begin van de vingerafdruk, zoals FITGAP-3F9A2C1B. */
function cm_kenmerk(string $vingerafdruk): string
{
    return strtoupper(cm_formulier()['id'] . '-' . substr($vingerafdruk, 0, 8));
}

/**
 * De versie van de regels: een vingerafdruk van de code die de uitkomst
 * bepaalt, de motor en de bestanden van het formulier. Andere code kan een
 * andere uitkomst geven; dezelfde versie met dezelfde antwoorden niet.
 */
function cm_regelversie(): string
{
    $formulier = cm_formulier();
    $map = CM_ROOT . '/' . $formulier['id'];
    $bestanden = [...glob(__DIR__ . '/*.php') ?: [], $map . '/definitie.php'];

    foreach ($formulier['bestanden'] as $bestand) {
        $bestanden[] = $map . '/' . $bestand;
    }

    $inhoud = '';
    foreach ($bestanden as $bestand) {
        $inhoud .= (string) file_get_contents($bestand);
    }

    return substr(hash('sha256', $inhoud), 0, 12);
}

/**
 * Maakt het document af: het kenmerk op de titelpagina, en de verantwoording
 * als laatste hoofdstuk.
 *
 * @param list<array<int,mixed>> $blokken
 * @param array<string,mixed> $u de uitkomst van het formulier
 * @return list<array<int,mixed>>
 */
function cm_verantwoord(array $blokken, array $a, array $u): array
{
    $vingerafdruk = cm_vingerafdruk($a);
    $kenmerk = cm_kenmerk($vingerafdruk);

    // De titelpagina is alles tot het eerste hoofdstuk.
    $eerste = count($blokken);
    foreach ($blokken as $i => $blok) {
        if ($blok[0] === 'h2') {
            $eerste = $i;
            break;
        }
    }
    array_splice($blokken, $eerste, 0, [['term', 'Kenmerk', $kenmerk]]);

    return [...$blokken, ...cm_verantwoording($a, $u, $vingerafdruk, $kenmerk)];
}

/** @return list<array<int,mixed>> */
function cm_verantwoording(array $a, array $u, string $vingerafdruk, string $kenmerk): array
{
    $formulier = cm_formulier();
    $moment = new DateTimeImmutable('now');
    $vaststelling = cm_vaststelling($a, $u);
    $antwoorden = ($formulier['antwoorden'] ?? true) === false ? [] : cm_antwoorden_blokken($a);

    return [
        ['h2', 'Verantwoording'],
        ['alinea', 'Dit hoofdstuk is er voor wie het document controleert. Het zegt hoe het document tot stand kwam, '
            . 'en hoe je nagaat dat het bij de antwoorden hoort.'],
        ['h3', 'Over dit document'],
        ['term', 'Formulier', (string) $formulier['titel']],
        ['term', 'Kenmerk', $kenmerk],
        ['term', 'Opgesteld op', cm_datum_nl($moment, true)],
        ['term', 'Opgesteld door', trim((string) ($a['aanvrager'] ?? '')) ?: 'Niet ingevuld'],
        ['term', 'Versie van de regels', cm_regelversie()],
        ['term', 'Grondslag', (string) $formulier['voet']],
        ['alinea', 'Het formulier rekent de uitkomst uit met vaste regels. Dezelfde antwoorden geven met dezelfde '
            . 'versie van de regels altijd dezelfde uitkomst.'],
        ...($vaststelling === [] ? [] : [
            ['h3', 'Vaststelling'],
            ['alinea', 'Het document is pas vastgesteld als het hieronder is ondertekend.'],
            ['tabel', [
                'kop' => ['Rol', 'Naam', 'Datum', 'Handtekening'],
                'rijen' => $vaststelling,
                'breedtes' => [5, 4.6, 3, 4],
                'rijkop' => true,
            ]],
        ]),
        ...($antwoorden === [] ? [] : [
            ['h3', 'Alle antwoorden'],
            ['alinea', 'Elke vraag die bij deze antwoorden van toepassing was, met het antwoord. Vragen die niet van '
                . 'toepassing waren, staan er niet bij.'],
            ...$antwoorden,
        ]),
        ['h3', 'Controle'],
        ['term', 'Vingerafdruk van de antwoorden', $vingerafdruk],
        ['term', 'Antwoordbestand', cm_bestandsnaam((string) ($a['projectnaam'] ?? ''), 'json')],
        ['alinea', 'De vingerafdruk is een SHA-256 over alle antwoorden. Het antwoordbestand heeft dezelfde '
            . 'vingerafdruk. Laad dat bestand in het formulier, en maak het document opnieuw. Komt dezelfde '
            . 'vingerafdruk eruit, dan hoort dit document bij die antwoorden. Is ook de versie van de regels gelijk, '
            . 'dan is de uitkomst dezelfde.'],
    ];
}

/**
 * De rijen voor de vaststelling: rol, naam, datum en handtekening. Zonder
 * 'vaststelling' in de definitie: opsteller, beoordelaar en vaststeller. Met
 * een lijst: per rol de sleutel van het antwoord met de naam, of null. Met
 * een functienaam: die geeft de lijst, uit de antwoorden en de uitkomst.
 *
 * @return list<list<string>>
 */
function cm_vaststelling(array $a, array $u): array
{
    $instelling = cm_formulier()['vaststelling'] ?? [
        ['Opgesteld door', 'aanvrager'],
        ['Beoordeeld door', null],
        ['Vastgesteld door', null],
    ];

    if ($instelling === false) {
        return [];
    }

    $rollen = is_string($instelling) ? $instelling($a, $u) : $instelling;

    return array_map(static fn (array $rol): array => [
        $rol[0],
        $rol[1] === null ? '' : trim((string) ($a[$rol[1]] ?? '')),
        '',
        '',
    ], $rollen);
}

/**
 * Alle vragen die van toepassing waren, per sectie in een tabel. Bij een
 * vraag in een groep, zoals een risico, staat de kop van de groep erboven.
 *
 * @return list<array<int,mixed>>
 */
function cm_antwoorden_blokken(array $a): array
{
    $b = [];

    foreach (cm_secties() as $sectie) {
        if (!cm_zichtbaar($sectie, $a)) {
            continue;
        }

        $koppen = [];
        $rijen = [];

        foreach ($sectie['vragen'] as $vraag) {
            if (isset($vraag['groep']) && $vraag['type'] === 'kop') {
                $koppen[$vraag['groep']] = (string) $vraag['label'];
            }
            if (!cm_is_invoer($vraag) || !cm_zichtbaar($vraag, $a)) {
                continue;
            }

            $tekst = cm_antwoord_regels($vraag, $a[$vraag['key']] ?? '');
            $rijen[] = [
                isset($vraag['groep'], $koppen[$vraag['groep']])
                    ? [$koppen[$vraag['groep']], (string) $vraag['label']]
                    : (string) $vraag['label'],
                $tekst === [] ? 'Niet ingevuld' : $tekst,
            ];
        }

        if ($rijen !== []) {
            $b[] = ['h4', (string) $sectie['titel']];
            $b[] = ['tabel', ['kop' => ['Vraag', 'Antwoord'], 'rijen' => $rijen, 'breedtes' => [7.4, 9.2]]];
        }
    }

    return $b;
}

/**
 * Een antwoord als regels tekst: het label van een keuze, de regels uit een
 * tekstvak, een regel per rij, of per maatregel de keuze.
 *
 * @return list<string>
 */
function cm_antwoord_regels(array $vraag, mixed $waarde): array
{
    $label = static fn (array $opties, string $w): string => (string) ($opties[$w]['label'] ?? $w);

    return match ($vraag['type']) {
        'radio', 'select' => $waarde === '' ? [] : [$label($vraag['opties'] ?? [], (string) $waarde)],
        'checkbox' => array_map(static fn (string $w): string => $label($vraag['opties'] ?? [], $w), (array) $waarde),
        'maatregelen' => array_map(
            static fn (string $m, string $k): string => $label($vraag['opties'], $m) . ': ' . CM_MAATREGELKEUZES[$k],
            array_keys((array) $waarde),
            array_values((array) $waarde)
        ),
        'rijen' => array_map(static fn (array $rij): string => implode('; ', array_filter(array_map(
            static fn (string $kolom, array $def): string => ($rij[$kolom] ?? '') === '' ? ''
                : $def['label'] . ': ' . ($def['type'] === 'select' ? $label($def['opties'], $rij[$kolom]) : $rij[$kolom]),
            array_keys($vraag['kolommen']),
            array_values($vraag['kolommen'])
        ))), (array) $waarde),
        default => cm_regels((string) $waarde),
    };
}
