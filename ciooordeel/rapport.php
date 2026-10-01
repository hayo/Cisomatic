<?php

/**
 * Het rapport: het CIO oordeel als blokken.
 *
 * De opbouw volgt een CIO oordeel: eerst de kern, dan wat goed gaat, wat beter
 * kan en de aanbevelingen. Daarna elk gebied als hoofdstuk, het onderzoek, de
 * opvolging, en hoe het voorstel tot stand kwam. Een blok is [soort, inhoud];
 * zie ../README.md.
 */

declare(strict_types=1);

/** @return list<array<int,mixed>> */
function co_rapport(array $a, array $u): array
{
    $naam = trim((string) ($a['projectnaam'] ?? '')) ?: 'Naamloos project';

    return [
        ...co_rapport_inleiding($a, $u, $naam),
        ...co_rapport_kern($a, $u),
        ...co_rapport_bevindingen($u),
        ...co_rapport_gebieden($a, $u),
        ...co_rapport_onderzoek($a),
        ...co_rapport_opvolging($a, $u),
        ...co_rapport_werkwijze(),
    ];
}

function co_rapport_inleiding(array $a, array $u, string $naam): array
{
    return [
        ['h1', 'CIO oordeel ' . $naam],
        ['term', 'Oordeel', CO_OORDELEN[$u['oordeel']]['label']],
        ['term', 'Onderdeel', (string) $a['organisatieonderdeel']],
        ['term', 'Opdrachtgever', (string) $a['co_opdrachtgever']],
        ['term', 'Moment', cm_antwoord_tekst('co_moment', $a)],
        ['term', 'Datum', cm_datum_nl(new DateTimeImmutable('now'))],
        ['term', 'Opgesteld door', (string) $a['aanvrager']],
        ...(($a['email'] ?? '') === '' ? [] : [['term', 'Contact', (string) $a['email']]]),
    ];
}

function co_rapport_kern(array $a, array $u): array
{
    $rijen = [];
    foreach ($u['gebieden'] as $id => $gebied) {
        $tel = array_count_values(array_column($gebied['aspecten'], 'waarde'));
        $rijen[] = [
            (array_search($id, array_keys(CO_GEBIEDEN), true) + 1) . '. ' . CO_GEBIEDEN[$id]['naam'],
            cm_niveau_label($gebied['niveau']),
            (string) (($tel['aandacht'] ?? 0) + ($tel['onbekend'] ?? 0)),
            (string) ($tel['risico'] ?? 0),
        ];
    }

    return [
        ['h2', 'Kern van het oordeel'],
        ['term', 'Oordeel', CO_OORDELEN[$u['oordeel']]['label']],
        ['alinea', CO_OORDELEN[$u['oordeel']]['betekenis']],
        ...array_map(static fn (string $regel): array => ['alinea', $regel], cm_regels((string) ($a['co_kern'] ?? ''))),
        ...(!$u['afwijking'] ? [] : [
            ['h3', 'Afwijking van het voorstel'],
            ['alinea', 'Het formulier stelde voor: ' . mb_strtolower(CO_OORDELEN[$u['voorstel']]['label'], 'UTF-8') . '.'],
            ...array_map(static fn (string $r): array => ['alinea', $r], cm_regels((string) ($a['co_afwijking'] ?? ''))),
        ]),
        ...($u['voorwaarden'] === [] ? [] : [
            ['h3', 'Voorwaarden'],
            ['alinea', 'Deze punten moeten geregeld zijn voordat het project verder mag.'],
            ['genummerd', $u['voorwaarden']],
        ]),
        ['h3', 'Risico per aandachtsgebied'],
        ['tabel', [
            'kop' => ['Aandachtsgebied', 'Risico', 'Aandacht', 'Risico’s'],
            'rijen' => $rijen,
            'breedtes' => [9.4, 2.4, 2.4, 2.4],
            'rijkop' => true,
        ]],
        ['alinea', 'Aandacht telt de aandachtspunten, en wat niet te beoordelen was. Risico’s telt de aspecten die '
            . 'het succes van het project bedreigen.'],
    ];
}

function co_rapport_bevindingen(array $u): array
{
    $verbeter = [];
    foreach ($u['verbeter'] as $id => $aspecten) {
        $nummer = array_search($id, array_keys(CO_GEBIEDEN), true) + 1;
        $verbeter[] = ['h3', $nummer . '. ' . CO_GEBIEDEN[$id]['naam']];
        foreach ($aspecten as $x) {
            $verbeter[] = ['term', CO_KEUZES[$x['waarde']]['label'] . ' ' . $nummer . '.' . $x['nummer'], $x['tekst']];
            if ($x['bevinding'] !== '') {
                $verbeter[] = ['alinea', $x['bevinding']];
            }
        }
    }

    return [
        ['h2', 'Wat goed gaat'],
        $u['goed'] === []
            ? ['alinea', 'Bij de aspecten die op orde zijn, staat geen toelichting.']
            : ['lijst', $u['goed']],
        ['h2', 'Wat beter kan'],
        ...($verbeter === [] ? [['alinea', 'Er zijn geen aandachtspunten en geen risico’s.']] : $verbeter),
        ...($u['aanbevelingen'] === [] ? [] : [['h2', 'Aanbevelingen'], ['genummerd', $u['aanbevelingen']]]),
        ...($u['onbekend'] === [] ? [] : [
            ['h2', 'Niet te beoordelen'],
            ['alinea', 'De stukken en de gesprekken gaven op deze punten geen antwoord. Ze tellen als aandachtspunt.'],
            ['lijst', $u['onbekend']],
        ]),
    ];
}

/** Per gebied het risico, en elk aspect met oordeel en bevinding. */
function co_rapport_gebieden(array $a, array $u): array
{
    $b = [
        ['h2', 'De aandachtsgebieden'],
        ['alinea', 'Per gebied staan hier alle aspecten met het oordeel. Het zwaarste oordeel bepaalt het risico van '
            . 'het gebied. Een aspect dat niet geldt voor dit project, staat er niet bij. Dat volgt uit de vragen '
            . 'over uitbesteden, de cloud, persoonsgegevens en algoritmes in het hoofdstuk over het onderzoek.'],
    ];

    $nummer = 0;
    foreach (CO_GEBIEDEN as $id => $gebied) {
        $nummer++;
        $rijen = array_map(static fn (array $x): array => [
            $nummer . '.' . $x['nummer'] . ' ' . $x['tekst'],
            CO_KEUZES[$x['waarde']]['label'] . ($x['voorwaarde'] ? ', voorwaarde' : ''),
            $x['bevinding'],
        ], $u['gebieden'][$id]['aspecten']);

        $b = [
            ...$b,
            ['h3', $nummer . '. ' . $gebied['naam']],
            ['term', 'Risico', cm_niveau_label($u['gebieden'][$id]['niveau'])],
            ...array_map(static fn (string $r): array => ['alinea', $r],
                cm_regels((string) ($a['co_' . $id . '_toelichting'] ?? ''))),
            ['tabel', [
                'kop' => ['Aspect', 'Oordeel', 'Bevinding'],
                'rijen' => $rijen,
                'breedtes' => [7.2, 3, 6.4],
            ]],
        ];
    }

    return $b;
}

function co_rapport_onderzoek(array $a): array
{
    $lijst = static fn (string $key): array => cm_regels((string) ($a[$key] ?? ''));

    return [
        ['h2', 'Het onderzoek'],
        ['term', 'Waar het project over gaat', (string) $a['proces_omschrijving']],
        ['term', 'Waarom een oordeel', cm_antwoord_tekst('co_aanleiding', $a)],
        ['term', 'Kosten', (string) $a['co_kosten']],
        ['term', 'Werk uitbesteed', cm_antwoord_tekst('co_uitbesteden', $a)],
        ['term', 'Cloud', cm_antwoord_tekst('co_cloud', $a)],
        ['term', 'Persoonsgegevens', cm_antwoord_tekst('co_persoonsgegevens', $a)],
        ['term', 'Algoritme of AI', cm_antwoord_tekst('co_algoritme', $a)],
        ['term', 'Adviescollege ICT-toetsing', cm_antwoord_tekst('co_acict', $a)],
        ['term', 'Gelezen stukken', cm_antwoord_tekst('co_stukken', $a)],
        ...($lijst('co_stukken_lijst') === [] ? [] : [['h3', 'Stukken'], ['lijst', $lijst('co_stukken_lijst')]]),
        ...($lijst('co_gesprekken') === [] ? [] : [['h3', 'Gesprekken'], ['lijst', $lijst('co_gesprekken')]]),
        ...($lijst('co_team') === [] ? [] : [['h3', 'Het team'], ['lijst', [(string) $a['aanvrager'], ...$lijst('co_team')]]]),
    ];
}

function co_rapport_opvolging(array $a, array $u): array
{
    return [
        ['h2', 'Opvolging'],
        ...array_map(static fn (string $r): array => ['alinea', $r], cm_regels((string) ($a['co_opvolging'] ?? ''))),
        ['genummerd', $u['vervolg']],
        ['h3', 'Reactie van de opdrachtgever'],
        ['alinea', 'Neemt de opdrachtgever het oordeel over? Wat gebeurt er met elke voorwaarde en elke aanbeveling, '
            . 'en wanneer?'],
        ['term', 'Naam', ''],
        ['term', 'Datum', ''],
        ['term', 'Reactie', ''],
    ];
}

/** Waar de gebieden vandaan komen, en hoe het voorstel tot stand komt. */
function co_rapport_werkwijze(): array
{
    return [
        ['h2', 'Zo komt het voorstel tot stand'],
        ['alinea', 'Voor een project met een grote ICT component is een positief CIO oordeel nodig. Dat staat in '
            . 'artikel 5 van het Besluit CIO-stelsel Rijksdienst 2026. Wijkt het ministerie af van een negatief '
            . 'oordeel, dan moet de SG uitleggen waarom.'],
        ['alinea', 'De gebieden 1 tot en met 9 komen uit het toetskader projecten 2026 van het Adviescollege '
            . 'ICT-toetsing. De aspecten staan er in eenvoudiger woorden. Het Kwaliteitskader CIO-oordelen voegt twee '
            . 'gebieden toe: informatiebeveiliging en privacy, en duurzame toegankelijkheid. De aspecten van die twee '
            . 'gebieden zijn een eigen uitwerking, net als het aspect over digitale toegankelijkheid.'],
        ['alinea', 'Elk aspect krijgt een oordeel. Het zwaarste oordeel bepaalt het risico van een gebied:'],
        ['lijst', [
            'Hoog: er is minstens één risico.',
            'Midden: er is een aandachtspunt, of iets wat niet te beoordelen was.',
            'Laag: alles is op orde, of niet van toepassing.',
        ]],
        ['alinea', 'Uit de gebieden volgt een voorstel voor het oordeel:'],
        ['lijst', [
            'Negatief: ' . CO_GRENS_NEGATIEF . ' of meer gebieden hebben een hoog risico.',
            'Positief, onder voorwaarden: er is minstens één voorwaarde.',
            'Positief, met aanbevelingen: er is een aandachtspunt of een risico.',
            'Positief: alles is op orde.',
        ]],
        ['alinea', 'Die regel is een eigen vereenvoudiging. Het oordeel zelf is van de CIO. Wijkt het oordeel af van '
            . 'het voorstel, dan staat de reden bij de kern van het oordeel.'],
    ];
}
