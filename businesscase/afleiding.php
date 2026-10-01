<?php

/**
 * Afleidingen: wat de tool uit de antwoorden haalt.
 *
 * Per toets een uitkomst, en daaruit het risico voor het MT. Verder hoe het
 * Rijk dit moet inkopen, wat opvalt, en wat de volgende stappen zijn.
 */

declare(strict_types=1);

/**
 * Per inkoopweg de uitkomst. regels.js leest deze teksten uit het formulier,
 * dus ze staan maar op één plek. De Europese drempel voor de rijksoverheid
 * geldt in 2026 en 2027; de Europese Commissie past hem elke twee jaar aan.
 */
const BC_INKOOP = [
    'geen' => [
        'label' => 'Geen inkoop',
        'niveau' => '',
        'redenen' => ['Jullie kopen niets in, dus er is geen aanbesteding nodig.'],
    ],
    'nadere' => [
        'label' => 'Onder een raamcontract dat er al is',
        'niveau' => 'l',
        'redenen' => [
            'Een nadere opdracht valt onder een raamcontract dat er al is. Een nieuwe aanbesteding is dan meestal '
                . 'niet nodig.',
            'Kijk wel of het contract deze opdracht dekt.',
        ],
    ],
    'onder' => [
        'label' => 'Onder de Europese drempel',
        'niveau' => 'l',
        'redenen' => [
            'De opdracht blijft onder 140.000 euro. Een Europese aanbesteding is dan niet nodig.',
            'Je inkoopadviseur weet welke procedure wel past.',
        ],
    ],
    'boven' => [
        'label' => 'Europese aanbesteding',
        'niveau' => 'm',
        'redenen' => [
            'De opdracht is 140.000 euro of meer. Dan moet het Rijk bijna altijd Europees aanbesteden.',
            'Zo’n aanbesteding duurt vaak maanden. Betrek je inkoopadviseur daarom nu al.',
        ],
    ],
    'onbekend' => [
        'label' => 'Nog niet bekend',
        'niveau' => 'm',
        'redenen' => [
            'Maak een schatting, want het bedrag bepaalt hoe jullie moeten inkopen.',
            'Een opdracht in stukken knippen om onder de drempel te blijven, mag niet.',
        ],
    ],
];

/** De sleutel in BC_INKOOP. regels.js spiegelt dit. */
function bc_inkoop_route(array $a): string
{
    $vorm = (string) ($a['inkoopvorm'] ?? '');

    return in_array($vorm, ['geen', 'nadere'], true) ? $vorm : (string) ($a['bc_opdrachtwaarde'] ?? '');
}

/** Wat een toets zegt, per niveau. */
const BC_TOETS_LABELS = ['l' => 'In orde', 'm' => 'Eerst uitzoeken', 'h' => 'Andere route'];

/** Het advies aan het MT, per niveau van het hoogste risico. */
const BC_ADVIES = [
    'l' => 'Alle vijf toetsen zijn in orde. Het project is nodig, en de organisatie kan het dragen. Het MT kan '
        . 'besluiten om door te gaan.',
    'm' => 'Bij een of meer toetsen is nog iets open. Het MT kan het project laten beginnen, maar dan is het risico '
        . 'groter. Het MT kan ook eerst om meer onderzoek vragen.',
    'h' => 'Bij een of meer toetsen ligt een andere route voor de hand. Het advies is: begin dit project nu niet, en '
        . 'kies de route die bij die toets staat.',
];

/**
 * Het niveau van een toets: het hoogste van zijn vragen. Voor de vragen in
 * 'of' telt alleen het laagste. Leeg zolang er iets open is. regels.js
 * spiegelt dit.
 *
 * @param array{vragen:array<string,array<string,string>>,of:list<string>} $toets
 */
function bc_toets_niveau(array $toets, array $a): string
{
    $niveau = static fn (string $key): string => $toets['vragen'][$key][$a[$key] ?? ''] ?? '';
    $niveaus = array_map($niveau, array_values(array_diff(array_keys($toets['vragen']), $toets['of'])));

    if ($toets['of'] !== []) {
        $of = array_map($niveau, $toets['of']);
        usort($of, static fn (string $x, string $y): int => cm_niveau_rang($x) <=> cm_niveau_rang($y));
        $niveaus[] = in_array('l', $of, true) ? 'l' : (in_array('', $of, true) ? '' : $of[0]);
    }

    return in_array('', $niveaus, true) ? '' : cm_niveau_max(...$niveaus);
}

/** Per toets, op de id van zijn sectie. @return array<string,array{stap:int,titel:string,niveau:string,label:string,redenen:list<string>}> */
function bc_toetsen(array $a): array
{
    $toetsen = [];

    foreach (cm_secties() as $sectie) {
        foreach ($sectie['vragen'] as $vraag) {
            if (isset($vraag['groepen']['of'])) {
                $niveau = bc_toets_niveau($vraag['groepen'], $a) ?: 'm';
                $toetsen[$sectie['id']] = [
                    'stap' => $sectie['stap'],
                    'titel' => $sectie['titel'],
                    'niveau' => $niveau,
                    'label' => BC_TOETS_LABELS[$niveau],
                    'redenen' => $vraag['groepen']['redenen'][$niveau],
                ];
            }
        }
    }

    return $toetsen;
}

/** @return list<string> */
function bc_aandachtspunten(array $a): array
{
    $inkoop = BC_INKOOP[bc_inkoop_route($a)] ?? null;

    $punten = [
        match ($a['bc_burger_merkt'] ?? '') {
            'indirect' => 'De burger merkt dit project alleen indirect. Het MT vraagt dan: waarom gaat dit geld niet '
                . 'naar iets wat de burger wel merkt? Geef daar een antwoord op.',
            'niet' => 'De burger merkt niets van dit project. Leg goed uit waarom het toch nodig is.',
            default => '',
        },
        ...($inkoop !== null && $inkoop['niveau'] === 'm' ? $inkoop['redenen'] : []),
        ($a['bc_omvang'] ?? '') === 'groot'
            ? 'Het project kost meer dan 5 miljoen euro. Dan geeft de CIO van het ministerie een oordeel, en meld je '
                . 'het project bij het Adviescollege ICT-toetsing. Reken voor het oordeel van de CIO op ongeveer drie maanden.'
            : '',
    ];

    return array_values(array_filter($punten));
}

/** Wat er na dit document gebeurt. @return list<string> */
function bc_vervolg(array $a): array
{
    $stappen = [
        'Het MT leest dit advies, en vult het besluit in op de laatste pagina.',
        bc_inkoop_route($a) === 'geen' ? '' : 'Bespreek de inkoop met je inkoopadviseur.',
        ($a['bc_omvang'] ?? '') === 'groot'
            ? 'Vraag een oordeel van de CIO aan, en meld het project bij het Adviescollege ICT-toetsing. Voor het '
                . 'oordeel van de CIO zijn dit document, een planning en een architectuurschets nodig.'
            : '',
        'Gaat het MT akkoord? Vul dan quickscan 2.0 in. Die laat zien wat de BIO2 en de Cyberbeveiligingswet vragen, '
            . 'en of er ook een DPIA of een IAMA nodig is. Laad daar het bestand van dit document in, dan staat een '
            . 'deel er al.',
    ];

    return array_values(array_filter($stappen));
}

/**
 * Alles wat pagina en document nodig hebben, in één keer uitgerekend.
 *
 * @return array<string,mixed>
 */
function bc_evalueer(array $a): array
{
    $toetsen = bc_toetsen($a);
    $risico = cm_niveau_max(...array_column($toetsen, 'niveau'));

    return [
        'versie' => trim((string) ($a['versie'] ?? '')) ?: '0.1',
        'toetsen' => $toetsen,
        'risico' => ['niveau' => $risico, 'label' => cm_niveau_label($risico), 'advies' => BC_ADVIES[$risico]],
        'inkoop' => BC_INKOOP[bc_inkoop_route($a)] ?? BC_INKOOP['onbekend'],
        'aandachtspunten' => bc_aandachtspunten($a),
        'vervolg' => bc_vervolg($a),
    ];
}
