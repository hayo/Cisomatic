<?php

/**
 * Het document: de business case en intaketoets als blokken.
 *
 * Na de titelpagina komt het advies aan het MT, met per toets de uitkomst.
 * Daarna volgen de toetsen en het voorstel, elk een hoofdstuk, met elke vraag
 * als kop en het antwoord eronder. Aan het eind tekent het MT het besluit. Een
 * blok is [soort, inhoud]; zie ../README.md.
 */

declare(strict_types=1);

/** @return list<array<int,mixed>> */
function bc_rapport(array $a, array $u): array
{
    $naam = trim((string) ($a['projectnaam'] ?? '')) ?: 'Naamloos project';
    $b = [...bc_rapport_inleiding($a, $u, $naam), ...bc_rapport_advies($a, $u)];

    foreach (cm_secties() as $sectie) {
        if (!isset($sectie['stap'])) {
            continue;
        }

        $b[] = ['h2', ($sectie['stap'] <= 5 ? 'Toets ' . $sectie['stap'] . ': ' : '') . $sectie['titel']];

        foreach ($sectie['vragen'] as $vraag) {
            if (cm_zichtbaar($vraag, $a)) {
                array_push($b, ...bc_antwoord($vraag, $a, $u));
            }
        }
    }

    return [...$b, ...bc_rapport_besluit($a)];
}

/** Tekst uit een tekstvak als alinea's, één per regel. */
function bc_alineas(string $tekst): array
{
    return array_map(static fn (string $regel): array => ['alinea', $regel], cm_regels($tekst));
}

/**
 * Een vraag met zijn antwoord. Een vraag met 'bij' hoort bij de vraag ervoor,
 * en krijgt geen eigen kop. Een lege optionele vraag valt weg. Een
 * uitkomstblok zegt hoe de toets uitvalt, of hoe jullie inkopen.
 *
 * @param array<string,mixed> $vraag
 * @return list<array<int,mixed>>
 */
function bc_antwoord(array $vraag, array $a, array $u): array
{
    $key = $vraag['key'];
    $kop = empty($vraag['bij']) ? [['h4', $vraag['label']]] : [];
    $uit = $vraag['type'] === 'afgeleid'
        ? ($vraag['bron'] === 'inkoop' ? $u['inkoop'] : $u['toetsen'][$vraag['bron']])
        : null;

    return match ($vraag['type']) {
        'afgeleid' => [['h4', $vraag['bron'] === 'inkoop' ? $vraag['label'] : 'Uitkomst'], ['alinea', $uit['label'] . '.'],
            ['lijst', $uit['redenen']]],
        'radio' => [...$kop, ['alinea', cm_antwoord_tekst($key, $a) . '.']],
        'checkbox' => [...$kop, ['lijst', array_map(
            static fn (string $w): string => $vraag['opties'][$w]['label'],
            (array) ($a[$key] ?? [])
        )]],
        default => cm_regels((string) ($a[$key] ?? '')) === []
            ? ($kop === [] || !empty($vraag['optioneel']) ? [] : [...$kop, ['alinea', 'Geen.']])
            : [...$kop, ...bc_alineas((string) $a[$key])],
    };
}

function bc_rapport_inleiding(array $a, array $u, string $naam): array
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

/** De pagina voor het MT: het risico, de toetsen, wat opvalt, en wat er daarna gebeurt. */
function bc_rapport_advies(array $a, array $u): array
{
    $toetsen = array_map(
        static fn (array $p): array => ['Toets ' . $p['stap'] . ': ' . $p['titel'], [$p['label'] . '.', ...$p['redenen']]],
        array_values($u['toetsen'])
    );

    return [
        ['h2', 'Advies aan het MT'],
        ['alinea', 'Dit document toetst niet of de oplossing goed is. Het toetst of het probleem belangrijk genoeg is, '
            . 'en of een nieuw systeem nu de juiste stap is. Dat gebeurt in vijf toetsen. Het MT besluit daarna.'],
        ['term', 'Risico', $u['risico']['label']],
        ['term', 'Voor de burger', cm_antwoord_tekst('bc_burger_merkt', $a)],
        ['term', 'Eenmalig', (string) $a['bc_kosten_eenmalig']],
        ['term', 'Per jaar', (string) $a['bc_kosten_jaarlijks']],
        ['term', 'Inkoop', $u['inkoop']['label']],
        ['alinea', $u['risico']['advies']],
        ['tabel', [
            'kop' => ['Toets', 'Uitkomst'],
            'rijen' => $toetsen,
            'breedtes' => [5, 11.6],
            'rijkop' => true,
        ]],
        ['h3', 'Het probleem'],
        ...bc_alineas((string) $a['bc_probleem']),
        ['h3', 'Het voorstel'],
        ...bc_alineas((string) $a['bc_oplossing']),
        ...($u['aandachtspunten'] === [] ? [] : [['h3', 'Aandachtspunten'], ['lijst', $u['aandachtspunten']]]),
        ['h3', 'Vervolgstappen'],
        ['genummerd', $u['vervolg']],
    ];
}

function bc_rapport_besluit(array $a): array
{
    return [
        ['h2', 'Besluit van het MT'],
        ['alinea', 'Het MT kiest een van de drie besluiten, en tekent hieronder.'],
        ['tabel', [
            'rijen' => [
                ['Datum', ''],
                ['Besluit', ['☐ Doorgaan met het project', '☐ Eerst meer uitzoeken', '☐ Stoppen, of een andere route kiezen']],
                ['Voorwaarden of toelichting', ''],
                ['Handtekening', ''],
            ],
            'breedtes' => [4.5, 12.1],
            'rijkop' => true,
        ]],
    ];
}
