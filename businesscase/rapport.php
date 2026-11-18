<?php

/**
 * Het document: de business case en intaketoets als blokken.
 *
 * Kort gehouden. Na de titel komt het advies aan het MT, met per toets de
 * uitkomst. Daarna het probleem en het voorstel, en de vijf toetsen, elk als
 * tabel met de vraag en het antwoord. Een toelichting staat in de cel van zijn
 * vraag. Aan het eind tekent het MT het besluit. Een blok is [soort, inhoud];
 * zie ../README.md.
 */

declare(strict_types=1);

/** @return list<array<int,mixed>> */
function bc_rapport(array $a, array $u): array
{
    $naam = trim((string) ($a['projectnaam'] ?? '')) ?: 'Naamloos project';
    $secties = array_values(array_filter(cm_secties(), static fn (array $s): bool => isset($s['stap'])));
    $toetsen = array_filter($secties, static fn (array $s): bool => isset($u['toetsen'][$s['id']]));
    $overig = array_filter($secties, static fn (array $s): bool => !isset($u['toetsen'][$s['id']]));

    $b = [...bc_rapport_inleiding($a, $naam, $u), ...bc_rapport_advies($a, $u)];

    $b[] = ['h2', 'Probleem en voorstel'];
    $b[] = bc_tabel(array_merge(...array_map(static fn (array $s): array => bc_rijen($s, $a, $u), $overig)));

    $b[] = ['h2', 'De vijf toetsen'];
    foreach ($toetsen as $sectie) {
        $toets = $u['toetsen'][$sectie['id']];
        $b[] = ['h3', 'Toets ' . $toets['stap'] . ': ' . $toets['titel']];
        $b[] = bc_tabel(bc_rijen($sectie, $a, $u));
        $b[] = ['alinea', 'Uitkomst: ' . $toets['label'] . '. ' . implode(' ', $toets['redenen'])];
    }

    return [...$b, ...bc_rapport_besluit()];
}

/** Een tabel met de vraag links en het antwoord rechts. @param list<array{0:string,1:list<string>}> $rijen */
function bc_tabel(array $rijen): array
{
    return ['tabel', ['rijen' => $rijen, 'breedtes' => [6, 10.6], 'rijkop' => true]];
}

/**
 * De vragen van een sectie als rijen. Een vraag met 'bij' komt in de cel van
 * de vraag ervoor. Een lege optionele vraag valt weg. Het uitkomstblok van een
 * toets staat onder de tabel, dat van de inkoop als rij.
 *
 * @return list<array{0:string,1:list<string>}>
 */
function bc_rijen(array $sectie, array $a, array $u): array
{
    $rijen = [];

    foreach ($sectie['vragen'] as $vraag) {
        if (!cm_zichtbaar($vraag, $a)) {
            continue;
        }

        if ($vraag['type'] === 'afgeleid') {
            if ($vraag['bron'] === 'inkoop') {
                $rijen[] = [$vraag['label'], [$u['inkoop']['label'] . '.', ...$u['inkoop']['redenen']]];
            }
            continue;
        }

        $antwoord = bc_antwoord($vraag, $a);

        if (!empty($vraag['bij']) && $rijen !== []) {
            array_push($rijen[count($rijen) - 1][1], ...$antwoord);
        } elseif ($antwoord !== []) {
            $rijen[] = [$vraag['label'], $antwoord];
        } elseif (empty($vraag['optioneel'])) {
            $rijen[] = [$vraag['label'], ['Geen.']];
        }
    }

    return $rijen;
}

/** Het antwoord op één vraag, als regels. @return list<string> */
function bc_antwoord(array $vraag, array $a): array
{
    $key = $vraag['key'];

    return match ($vraag['type']) {
        'radio' => ($a[$key] ?? '') === '' ? [] : [cm_antwoord_tekst($key, $a) . '.'],
        'checkbox' => array_map(
            static fn (string $w): string => $vraag['opties'][$w]['label'],
            (array) ($a[$key] ?? [])
        ),
        default => cm_regels((string) ($a[$key] ?? '')),
    };
}

function bc_rapport_inleiding(array $a, string $naam, array $u): array
{
    return [
        ['h1', 'Business case en intaketoets ' . $naam],
        ['term', 'Versie', $u['versie']],
        ['term', 'Datum', cm_datum_nl(new DateTimeImmutable('now'))],
        ['term', 'Opgesteld door', (string) $a['aanvrager']],
        ['term', 'Afdeling', (string) $a['organisatieonderdeel']],
        ...(($a['email'] ?? '') === '' ? [] : [['term', 'Contact', (string) $a['email']]]),
    ];
}

/** Het advies voor het MT: het risico, de toetsen, wat opvalt, en wat er daarna gebeurt. */
function bc_rapport_advies(array $a, array $u): array
{
    $toetsen = array_map(
        static fn (array $p): array => ['Toets ' . $p['stap'] . ': ' . $p['titel'], $p['label']],
        array_values($u['toetsen'])
    );

    return [
        ['h2', 'Advies aan het MT'],
        ['term', 'Risico', $u['risico']['label']],
        ['alinea', $u['risico']['advies']],
        ['tabel', [
            'rijen' => [
                ...$toetsen,
                ['Voor de burger', cm_antwoord_tekst('bc_burger_merkt', $a)],
                ['Kosten', ['Eenmalig: ' . $a['bc_kosten_eenmalig'], 'Per jaar: ' . $a['bc_kosten_jaarlijks']]],
                ['Inkoop', $u['inkoop']['label']],
            ],
            'breedtes' => [6, 10.6],
            'rijkop' => true,
        ]],
        ...($u['aandachtspunten'] === [] ? [] : [['h3', 'Aandachtspunten'], ['lijst', $u['aandachtspunten']]]),
        ['h3', 'Vervolgstappen'],
        ['genummerd', $u['vervolg']],
    ];
}

function bc_rapport_besluit(): array
{
    return [
        ['h2', 'Besluit van het MT'],
        ['tabel', [
            'rijen' => [
                ['Datum', ''],
                ['Besluit', ['☐ Doorgaan met het project', '☐ Eerst meer uitzoeken', '☐ Stoppen, of een andere route kiezen']],
                ['Voorwaarden of toelichting', ''],
                ['Handtekening', ''],
            ],
            'breedtes' => [6, 10.6],
            'rijkop' => true,
        ]],
    ];
}
