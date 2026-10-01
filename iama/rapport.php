<?php

/**
 * Het document: de IAMA in de vijf delen, als blokken.
 *
 * De delen volgen de vragenlijst zelf: elke sectie een paragraaf, elke vraag
 * een kop met het antwoord eronder. Zo lopen formulier en document nooit
 * uit elkaar. Alleen de inleiding, de actiepunten en het besluit staan hier
 * los. Een blok is [soort, inhoud]; zie ../README.md.
 */

declare(strict_types=1);

const IA_DELEN = [1 => '1 Waarom?', 2 => '2 Wat?', 3 => '3 Hoe?', 4 => '4 Grondrechten', 5 => '5 Afsluiting'];

/** @return list<array<int,mixed>> */
function ia_rapport(array $a, array $u): array
{
    $naam = trim((string) ($a['projectnaam'] ?? '')) ?: 'Naamloos algoritme';
    $b = ia_rapport_inleiding($a, $u, $naam);
    $deel = 0;
    $later = false;
    $leeg = true;

    foreach (cm_secties() as $sectie) {
        if (!isset($sectie['stap'])) {
            continue;
        }

        if ($sectie['stap'] !== $deel) {
            $deel = $sectie['stap'];
            [$later, $leeg] = [false, true];
            $b[] = ['h2', IA_DELEN[$deel]];

            if ($deel === 5 && $u['gestopt'] !== '') {
                array_push($b, ...ia_rapport_gestopt($u));
            }
        }

        if ($sectie['sessie'] > $u['sessie']) {
            if (!$later) {
                $b[] = ['alinea', ($leeg ? 'Dit deel' : 'De rest van dit deel') . ' volgt in een volgende sessie.'];
                $later = true;
            }
            continue;
        }

        if (!cm_zichtbaar($sectie, $a)) {
            continue;
        }

        $leeg = false;
        $b[] = ['h3', $sectie['titel']];

        foreach ($sectie['vragen'] as $vraag) {
            if (cm_zichtbaar($vraag, $a)) {
                array_push($b, ...ia_antwoord($vraag, $a, $u));
            }
        }
    }

    // Tot de laatste sessie staan de actiepunten die al uit de antwoorden volgen apart achteraan.
    if ($u['sessie'] < 3 && $u['actiepunten'] !== []) {
        array_push($b, ['h2', 'Actiepunten tot nu toe'], ...ia_rijen(cm_alle_vragen()['ia_actiepunten'], $u['actiepunten']));
    }

    return $u['sessie'] === 3 && $u['gestopt'] === '' ? [...$b, ...ia_rapport_besluit()] : $b;
}

/** Tekst uit een tekstvak als alinea's, één per regel. */
function ia_alineas(string $tekst): array
{
    return array_map(static fn (string $regel): array => ['alinea', $regel], cm_regels($tekst));
}

/**
 * Een vraag met zijn antwoord. Een vraag met 'bij' hoort bij de vraag ervoor,
 * en krijgt geen eigen kop.
 *
 * @param array<string,mixed> $vraag
 * @return list<array<int,mixed>>
 */
function ia_antwoord(array $vraag, array $a, array $u): array
{
    $key = $vraag['key'];
    $waarde = $a[$key] ?? '';
    $kop = empty($vraag['bij'])
        ? [['h4', $vraag['label'] . (empty($vraag['art27']) ? '' : ' (artikel 27 AI verordening)')]]
        : [];

    return match ($vraag['type']) {
        'kop' => [['h4', $vraag['label']]],
        'afgeleid' => [],
        'rijen' => [...$kop, ...ia_rijen($vraag, $key === 'ia_actiepunten' ? $u['actiepunten'] : (array) $waarde)],
        'checkbox' => [
            ...$kop,
            $waarde === []
                ? ['alinea', 'Geen van deze.']
                : ['lijst', array_map(static fn (string $w): string => $vraag['opties'][$w]['label'], $waarde)],
        ],
        'radio', 'select' => [...$kop, ['alinea', cm_antwoord_tekst($key, $a) . '.']],
        default => match (true) {
            cm_regels((string) $waarde) === [] => $kop === [] ? [] : [...$kop, ['alinea', 'Geen.']],
            !empty($vraag['opsomming']) => [...$kop, ['lijst', cm_regels((string) $waarde)]],
            default => [...$kop, ...ia_alineas((string) $waarde)],
        },
    };
}

/**
 * Rijen als tabel. Een kolom met 'bij' krijgt geen eigen kolom: zijn regels
 * komen in de cel van de kolom die hij noemt.
 *
 * @param array<string,mixed> $vraag
 * @param list<array<string,string>> $rijen
 * @return list<array<int,mixed>>
 */
function ia_rijen(array $vraag, array $rijen): array
{
    if ($rijen === []) {
        return [['alinea', 'Geen.']];
    }

    $kolommen = array_filter($vraag['kolommen'], static fn (array $def): bool => empty($def['bij']));
    $extra = $vraag['extra_kolommen'] ?? [];

    $tabelrijen = [];
    foreach ($rijen as $rij) {
        $cellen = [];
        foreach ($kolommen as $kolom => $def) {
            $waarde = (string) ($rij[$kolom] ?? '');
            $cel = isset($def['opties']) ? [(string) ($def['opties'][$waarde]['label'] ?? $waarde)] : cm_regels($waarde);

            foreach ($vraag['kolommen'] as $bij => $bijDef) {
                if (($bijDef['bij'] ?? '') === $kolom) {
                    array_push($cel, ...cm_regels((string) ($rij[$bij] ?? '')));
                }
            }
            $cellen[] = $cel;
        }
        $tabelrijen[] = [...$cellen, ...array_fill(0, count($extra), '')];
    }

    return [['tabel', [
        'kop' => [...array_map(static fn (array $def): string => $def['kort'] ?? $def['label'], array_values($kolommen)), ...array_keys($extra)],
        'rijen' => $tabelrijen,
        'breedtes' => [...array_column($kolommen, 'breedte'), ...array_values($extra)],
    ]]];
}

function ia_rapport_inleiding(array $a, array $u, string $naam): array
{
    $datum = cm_datum_nl(new DateTimeImmutable('now'));

    return [
        ['h1', 'IAMA ' . $naam],
        ['term', 'Versie', $u['versie']],
        ['term', 'Datum', $datum],
        ['term', 'Opgesteld door', (string) $a['aanvrager']],
        ['term', 'Afdeling', (string) $a['organisatieonderdeel']],
        ...(($a['email'] ?? '') === '' ? [] : [['term', 'Contact', (string) $a['email']]]),

        ['h2', 'Inleiding'],
        ['alinea', 'Het Impact Assessment Mensenrechten en Algoritmes (IAMA) helpt een team om vooraf te bespreken '
            . 'wat een algoritme doet. Het gaat om de gevolgen voor grondrechten, en om andere belangen. Aan het eind '
            . 'geeft het team een advies: het algoritme wel of niet gebruiken, en onder welke voorwaarden.'],
        ['h3', 'Aanleiding'],
        ['alinea', cm_antwoord_tekst('ia_reden', $a) . '.'],
        ...ia_alineas((string) ($a['ia_reden_toelichting'] ?? '')),
        ['h3', 'Het team'],
        ...ia_rijen(cm_alle_vragen()['ia_team'], (array) $a['ia_team']),
        ['h3', 'Aanpak'],
        ['alinea', 'Dit document volgt de opbouw van het IAMA, versie 2 uit 2026. Het team heeft de vragen samen '
            . 'besproken. ' . match ($u['sessie']) {
                1 => 'Tot nu toe zijn deel 1 en 2 besproken.',
                2 => 'Tot nu toe is alles besproken tot en met vraag 4.1.',
                default => 'Alle delen zijn besproken.',
            }],
        ['alinea', 'Bij sommige vragen staat ‘artikel 27 AI verordening’. Die vragen horen bij de beoordeling van '
            . 'grondrechten die de AI verordening vraagt voor een AI systeem met een hoog risico.'],
        ['h3', 'Versiebeheer'],
        ['tabel', [
            'kop' => ['Versie', 'Datum', 'Door', 'Toelichting'],
            'rijen' => [[$u['versie'], $datum, (string) $a['aanvrager'], trim((string) ($a['versie_toelichting'] ?? '')) ?: 'Eerste opzet']],
            'breedtes' => [2, 3.5, 5, 6.1],
        ]],
    ];
}

/** Deel 5 als het team bij 4.4.2 of 4.5.4 stopte. */
function ia_rapport_gestopt(array $u): array
{
    return [
        ['alinea', sprintf('Het team stopte bij vraag %s. Het vindt het niet redelijk om het algoritme te gebruiken. '
            . 'Daarom is er geen afweging en geen advies.', $u['gestopt'])],
        ['alinea', 'Het algoritme gaat terug naar de tekentafel. Zoek naar maatregelen die de inbreuk kleiner maken, '
            . 'of naar andere oplossingen. Doorloop het IAMA daarna opnieuw.'],
        ['h3', 'Actiepunten'],
        ...ia_rijen(cm_alle_vragen()['ia_actiepunten'], $u['actiepunten']),
    ];
}

function ia_rapport_besluit(): array
{
    return [
        ['h3', 'Besluit'],
        ['alinea', 'De verantwoordelijke neemt het besluit, op basis van dit advies. Die leest ook de restrisico’s, en '
            . 'aanvaardt ze of wijst ze af.'],
        ['tabel', [
            'rijen' => [['Naam', ''], ['Functie', ''], ['Datum', ''], ['Besluit', ''], ['Handtekening', '']],
            'breedtes' => [4, 12.6],
            'rijkop' => true,
        ]],
    ];
}
