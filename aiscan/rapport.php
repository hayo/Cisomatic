<?php

/**
 * Het rapport: de scan als blokken.
 *
 * Na de titelpagina komt een samenvatting, dan de plichten uit de
 * AI-verordening en, bij generatieve AI, de toets aan het standpunt. Daarna
 * de antwoorden en de bronnen. Een blok is [soort, inhoud]; zie ../README.md.
 */

declare(strict_types=1);

/** @return list<array<int,mixed>> */
function ai_rapport(array $a, array $u): array
{
    $naam = trim((string) ($a['projectnaam'] ?? '')) ?: 'Naamloze toepassing';
    $opmerkingen = cm_regels((string) ($a['ai_opmerkingen'] ?? ''));

    return [
        ...ai_rapport_inleiding($a, $naam),
        ...ai_rapport_samenvatting($u),
        ...ai_rapport_plichten($u),
        ...($u['standpunt'] === null ? [] : ai_rapport_standpunt($u['standpunt'])),
        ...ai_rapport_antwoorden($a, $u),
        ...ai_rapport_bronnen(),
        ...($opmerkingen === [] ? [] : [
            ['h2', 'Opmerkingen'],
            ...array_map(static fn (string $regel): array => ['alinea', $regel], $opmerkingen),
        ]),
    ];
}

function ai_rapport_inleiding(array $a, string $naam): array
{
    return [
        ['h1', 'AI scan ' . $naam],
        ['term', 'Datum', cm_datum_nl(new DateTimeImmutable('now'))],
        ['term', 'Opgesteld door', (string) $a['aanvrager']],
        ...(($a['organisatieonderdeel'] ?? '') === '' ? [] : [['term', 'Organisatie', (string) $a['organisatieonderdeel']]]),
        ...(($a['email'] ?? '') === '' ? [] : [['term', 'Contact', (string) $a['email']]]),
        ['h2', 'De toepassing'],
        ...array_map(static fn (string $regel): array => ['alinea', $regel], cm_regels((string) $a['ai_omschrijving'])),
    ];
}

/** De uitkomst in termen, voor rapport en samenvatting. @return array<string,string> */
function ai_kort(array $u): array
{
    $r = $u['route'];
    $klasse = AI_KLASSEN[$u['klasse']];

    return array_filter([
        'AI verordening' => $r['niet'] === '' ? 'Geldt' : 'Geldt niet',
        'Waarom niet' => AI_NIET[$r['niet']] ?? '',
        'Rol' => ucfirst(cm_opsomming($r['rollen'])),
        'Soort' => AI_SOORTEN[$r['soort']] ?? '',
        'Risicogroep' => $r['niet'] === '' ? $klasse['label'] : '',
        'Generatieve AI' => $u['standpunt'] === null ? '' : GAI_OORDELEN[$u['standpunt']['oordeel']]['tekst'],
    ]);
}

function ai_rapport_samenvatting(array $u): array
{
    $klasse = AI_KLASSEN[$u['klasse']];
    $termen = [];

    foreach (ai_kort($u) as $term => $tekst) {
        $termen[] = ['term', $term, $tekst];
    }

    return [
        ['h2', 'Samenvatting'],
        ['alinea', 'Deze scan zoekt uit wat de AI verordening van de toepassing vraagt. Hij volgt de beslishulp van '
            . 'het ministerie van BZK.'],
        ...$termen,
        ['h3', 'Wat betekent dit?'],
        ['alinea', $klasse['uitleg']],
        ['h3', 'Vervolgstappen'],
        ['genummerd', $u['vervolg']],
    ];
}

function ai_rapport_plichten(array $u): array
{
    $b = [
        ['h2', 'Wat de AI verordening vraagt'],
        ['alinea', $u['route']['niet'] === ''
            ? 'Dit zijn de plichten die bij jullie rol en de risicogroep horen. De nummers verwijzen naar de artikelen '
                . 'van de AI verordening.'
            : 'De AI verordening geldt niet. ' . AI_NIET[$u['route']['niet']]],
    ];

    foreach ($u['plichten'] as $plicht) {
        array_push($b, ['h3', $plicht['titel']], ['lijst', $plicht['punten']]);
        if ($plicht['vanaf'] !== '') {
            $b[] = ['alinea', $plicht['vanaf']];
        }
    }

    return $b;
}

/** De toets aan het standpunt, en de tips uit de handreiking. */
function ai_rapport_standpunt(array $s): array
{
    $rijen = [];
    foreach ($s['voorwaarden'] as $naam => $gehaald) {
        $rijen[] = [$naam, $gehaald];
    }

    return [
        ['h2', 'Generatieve AI'],
        ['alinea', 'De overheid heeft een standpunt over generatieve AI. Daarin staat wanneer een overheid die AI mag '
            . 'inkopen, maken of gebruiken. De handreiking van BZK legt uit hoe je dat goed regelt.'],
        ['term', 'Oordeel', GAI_OORDELEN[$s['oordeel']]['tekst']],
        ['tabel', [
            'kop' => ['Voorwaarde uit het standpunt', 'Gehaald?'],
            'rijen' => $rijen,
            'breedtes' => [12.6, 4],
            'rijkop' => true,
        ]],
        ...($s['acties'] === [] ? [] : [['h3', 'Wat nog moet'], ['lijst', $s['acties']]]),
        ['h3', 'Tips uit de handreiking'],
        ['lijst', $s['tips']],
    ];
}

/** De vragen op de route, en de vragen over generatieve AI, met de antwoorden. */
function ai_rapport_antwoorden(array $a, array $u): array
{
    $rij = static fn (string $key): array => [
        (string) cm_alle_vragen()[$key]['label'],
        cm_antwoord_tekst($key, $a) ?: 'Geen van deze',
    ];

    $gai = array_filter(
        array_keys(cm_alle_vragen()),
        static fn (string $key): bool => str_starts_with($key, 'gai_') && ($a[$key] ?? '') !== ''
    );

    return [
        ['h2', 'De antwoorden'],
        ['alinea', 'Dit zijn de vragen uit de beslishulp die voor deze toepassing golden. Elk antwoord bepaalde de '
            . 'volgende vraag.'],
        ['tabel', [
            'kop' => ['Vraag', 'Antwoord'],
            'rijen' => array_map($rij, $u['route']['pad']),
            'breedtes' => [8.6, 8],
        ]],
        ...($gai === [] ? [] : [
            ['h3', 'Generatieve AI'],
            ['tabel', [
                'kop' => ['Vraag', 'Antwoord'],
                'rijen' => array_map($rij, array_values($gai)),
                'breedtes' => [8.6, 8],
            ]],
        ]),
    ];
}

function ai_rapport_bronnen(): array
{
    return [
        ['h2', 'Over deze scan'],
        ['alinea', 'De vragen over de AI verordening komen uit de Beslishulp AI verordening van het ministerie van BZK. '
            . 'Die staat in het Algoritmekader. Deze scan volgt de versie van 14 september 2026, in eenvoudige taal.'],
        ['alinea', 'Sinds juli 2026 staan er andere datums in de AI verordening. De regels voor een hoog risico gaan '
            . 'daardoor later in. De scan gebruikt die nieuwe datums.'],
        ['alinea', 'De vragen over generatieve AI komen uit twee stukken van BZK, uit januari 2025. Het eerste is het '
            . 'overheidsbrede standpunt over generatieve AI. Daarnaast is er de handreiking voor verantwoorde inzet ervan.'],
        ['alinea', 'Deze scan geeft een eerste beeld, en vervangt het oordeel van een jurist niet. Twijfel je over een '
            . 'antwoord? Vraag dan advies, bijvoorbeeld aan een jurist of de privacy officer.'],
    ];
}
