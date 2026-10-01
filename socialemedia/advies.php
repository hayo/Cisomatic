<?php

/**
 * Het document. Bij een nieuw of bestaand kanaal het comply or explain
 * formulier, in de vier stappen van de rijksbrede werkwijze, met het
 * platformkader als bijlage. Bij toetreding komen het vooronderzoek en de
 * checklist voor livegang erbij. Bij een vertrek het exitplan uit exit.php.
 */

declare(strict_types=1);

/** De eisen aan een corporate account van een bewindspersoon, uit de richtlijn voor toetreding. */
const SM_BEWINDSPERSOON = [
    'Het departement is de eigenaar van het account.',
    'De naam is de functie, zoals @ministerBZK. De naam van de bewindspersoon staat er ook bij.',
    'De bio zegt dat het account ambtelijk wordt ondersteund, bijvoorbeeld met "Redactie door".',
    'Het account voert het Rijkslogo en is geverifieerd.',
    'Er staan nooit berichten over partijpolitiek, en nooit verwijzingen naar de partij. In de bio mag een link naar '
        . 'een persoonlijk account.',
    'Informele berichten gaan altijd over de functie en de rol in het ambt.',
    'Er is webcare, maar geen betaalde promotie.',
    'Het account gaat verplicht over naar de opvolger.',
];

/** De checklist voor livegang, uit de richtlijn voor toetreding. */
const SM_LIVEGANG = [
    'Informeer de Voorlichtingsraad via zijn secretariaat, en deel dit vooronderzoek. Informeer ook de andere '
        . 'rijksorganisaties.',
    'Leg het besluit vast, met de reikwijdte, de kaders, de momenten van evaluatie en de exitstrategie.',
    'Vertel collega’s waarom je toetreedt, en hoe publicatie, webcare en monitoring gaan. Alleen de directie '
        . 'Communicatie maakt accounts aan.',
    'Reserveer de naam, gelijk aan je andere kanalen. Richt het account in volgens de Rijkshuisstijl.',
    'Maak een contentkalender voor de eerste drie maanden, en zet vier tot zes berichten klaar.',
    'Stel doelen voor bereik en betrokkenheid vast, en richt de monitoring in.',
    'Maak huisregels voor reacties. Blokkeer volgers alleen bij discriminatie, schelden of bedreiging. Bij een '
        . 'bedreiging bel je altijd een beveiligingsambtenaar.',
    'Zet belangrijke informatie ook op je eigen site, en noem het nieuwe account daar.',
    'Maak de toetreding openbaar, bijvoorbeeld met een blog of een persbericht. Vertel waarom, en waarover je het '
        . 'gesprek aangaat.',
    'Evalueer na drie maanden wat werkt, en deel dat bij voorkeur met de Voorlichtingsraad.',
];

/**
 * @param array<string,mixed> $a antwoorden
 * @param array<string,mixed> $u uitkomst van sm_evalueer()
 * @return list<array<int,mixed>>
 */
function sm_document(array $a, array $u): array
{
    if ($u['situatie'] === 'exit') {
        return sm_exit_document($a, $u);
    }

    $kader = $u['kader'];
    $t = static fn (string $key): string => cm_antwoord_tekst($key, $a);
    $vrij = static fn (string $key): string => implode(' ', cm_regels((string) ($a[$key] ?? '')));

    $kop = [
        ['h1', 'Comply or explain: ' . ($a['projectnaam'] ?: 'naamloos')],
        ['term', 'Platform', $kader['naam']],
        ['term', 'Situatie', $t('sm_situatie') . ($u['toetreding'] ? ', op een nieuw platform' : '')],
        ['term', 'Account', $t('sm_accounttype')],
        ['term', 'Ingevuld door', (string) ($a['aanvrager'] ?: 'onbekend')],
        ['term', 'Ingevuld op', cm_datum_nl(new DateTimeImmutable('now'))],
    ];
    foreach (['organisatieonderdeel' => 'Organisatie', 'email' => 'Contact', 'sm_directeur' => 'Vastgesteld door'] as $key => $label) {
        if (trim((string) ($a[$key] ?? '')) !== '') {
            $kop[] = ['term', $label, (string) $a[$key]];
        }
    }
    $kop[] = ['term', 'Uitkomst', $u['label']];
    $kop[] = ['term', 'Herzien uiterlijk', cm_datum_nl($u['herziening'])];

    $toets = array_map(static fn (string $s, array $l): string => sm_tekst($s, '', $l), array_keys($u['buiten']), $u['buiten'])
        ?: [$kader['doelen'] === null ? sm_tekst('doel_geen_kader') : sm_tekst('doel_past', $kader['naam'])];

    $perSoort = [
        'organisch' => [['term', 'Doelen', $t('sm_doelen')], ['term', 'Vormen', $t('sm_vormen')]],
        'betaald' => [['term', 'Adverteren', $t('sm_betaald')], ['term', 'Wie ziet de advertenties', $t('sm_doelgericht')]],
        'contact' => [['term', 'Contact', $t('sm_contactvormen')]],
    ];

    return [
        ...$kop,
        ['citaat', implode(' ', $u['redenen'])],

        ['h2', '1. Doel en doelgroep'],
        ['term', 'Doelgroep', $vrij('sm_doelgroep')],
        ['term', 'Bereik', $t('sm_bereik')],
        ['term', 'Gebruik', $t('sm_gebruik')],
        ...array_merge(...array_values(array_intersect_key($perSoort, array_flip(sm_gebruik($a))))),
        ['lijst', $toets],

        ['h2', '2. Kanaalstrategie'],
        ['term', 'Minder risicovol alternatief', $t('sm_alternatief')],
        ['alinea', $vrij('sm_alternatief_toelichting')],
        ...($kader['inzet'] === 'primair' ? [] : [['term', 'Eerst op eigen kanalen', $t('sm_eigen_kanalen')]]),

        ['h2', '3. Weging'],
        ['term', 'Wijst op risico’s en verwijst door', $t('sm_doorverwijzen')],
        ...($kader['toestel'] ? [['term', 'Beheer', $t('sm_toestel')]] : []),
        ['term', 'Maatregelen', ($a['sm_maatregelen'] ?? []) === [] ? 'Nog geen' : $t('sm_maatregelen')],
        ['term', 'Meerwaarde weegt op', $t('sm_weging')],
        ['alinea', $vrij('sm_weging_toelichting')],
        ...($u['regelen'] === [] ? [] : [['h3', 'Nog te regelen'], ['lijst', $u['regelen']]]),
        ...($u['let_op'] === [] ? [] : [['h3', 'Let op bij adverteren'], ['lijst', $u['let_op']]]),
        ...(($a['sm_accounttype'] ?? '') === 'bewindspersoon'
            ? [['h3', 'Eisen aan een account van een bewindspersoon'], ['lijst', SM_BEWINDSPERSOON]]
            : []),

        ...($u['toetreding'] ? sm_document_vooronderzoek($a, $kader) : []),

        ['h2', '4. ' . $u['label']],
        ...sm_document_besluit($a, $u),

        ['h2', 'Bijlage: het kader voor ' . $kader['naam']],
        ['term', 'Advies', $u['inzet']['label']],
        ['alinea', $kader['uitleg']],
        ['term', 'Doelgroep', $kader['doelgroep']],
        ['h3', 'Risico’s'],
        ['lijst', $kader['risicos']],
        ...($kader['doelen'] === null ? [] : [
            ['h3', 'Ruimte binnen het kader'],
            ['term', 'Doelen van berichten', sm_ruimte($kader['doelen'], SM_DOELEN)],
            ['term', 'Vormen van berichten', sm_ruimte($kader['vormen'], SM_VORMEN)],
            ['term', 'Adverteren', sm_ruimte($kader['betaald'], SM_BETAALD)],
            ['term', 'Contact', sm_ruimte($kader['contact'], SM_CONTACT)],
            ['term', 'Account van een bewindspersoon', ['ja' => 'Ja', 'nee' => 'Nee', 'verboden' => 'Nee, nooit'][$kader['bewindspersoon']]],
        ]),
    ];
}

/** Het vooronderzoek bij toetreding tot een nieuw platform: het normenkader en de start. */
function sm_document_vooronderzoek(array $a, array $kader): array
{
    $t = static fn (string $key): string => cm_antwoord_tekst($key, $a);
    $vrij = static fn (string $key): string => implode(' ', cm_regels((string) ($a[$key] ?? '')));

    return [
        ['h2', 'Vooronderzoek bij toetreding'],
        ['alinea', 'Jullie organisatie zit nog niet op ' . $kader['naam'] . '. Dan geldt ook de richtlijn voor '
            . 'toetreding.'],
        ['term', 'Belang van de burger', $vrij('sm_belang_burger')],
        ['term', 'Kosten', $vrij('sm_kosten')],
        ...($kader['inzet'] === 'geen' ? [
            ['term', 'Opzet van het platform', $t('sm_markt_open')],
            ['term', 'Archiveren mogelijk', $t('sm_markt_archief')],
        ] : []),
        ...(trim((string) ($a['sm_markt_partners'] ?? '')) === '' ? [] : [['term', 'Andere overheden en partners', $vrij('sm_markt_partners')]]),
        ['term', 'Start', $t('sm_start')],
        ['term', 'Exitstrategie', $vrij('sm_exitstrategie')],
    ];
}

/** De ruimte van het kader als opsomming, of "Geen". */
function sm_ruimte(array $ruimte, array $opties): string
{
    return $ruimte === [] ? 'Geen' : cm_opsomming(array_map(static fn (string $k): string => $opties[$k]['label'], $ruimte));
}

/** Stap 4: wat comply, explain of niet inzetten hier betekent, en wat er daarna gebeurt. */
function sm_document_besluit(array $a, array $u): array
{
    $adviseurs = (array) ($a['sm_adviseurs'] ?? []);
    $bestaand = $u['situatie'] === 'bestaand';
    $vervolg = array_filter([
        array_diff(['cpo', 'ciso'], $adviseurs) === []
            ? ''
            : 'Vraag advies aan je CPO en CISO, en waar nodig aan je CIO.',
        'De directeur Communicatie stelt dit vast.',
        $u['uitkomst'] === 'niet' && $bestaand
            ? 'Bouw het kanaal af. Vul daarvoor deze scan opnieuw in, en kies vertrekken. Dan krijg je een exitplan.'
            : '',
        $u['uitkomst'] === 'niet'
            ? ''
            : 'De bestuursraad toetst het elk jaar. De volgende keer is uiterlijk ' . cm_datum_nl($u['herziening']) . '.',
    ]);

    $b = match ($u['uitkomst']) {
        'explain' => [
            ['alinea', 'De inzet gaat verder dan de ruimte van het rijksbrede kader. Daarom is dit een explain.'],
            ['h3', 'Waarvan wordt afgeweken'],
            ['lijst', cm_regels((string) ($a['sm_afwijking'] ?? ''))],
            ['h3', 'Waarom dit noodzakelijk is'],
            ['alinea', implode(' ', cm_regels((string) ($a['sm_noodzaak'] ?? '')))],
            ['h3', 'Waarom een minder risicovol alternatief niet volstaat'],
            ['alinea', implode(' ', cm_regels((string) ($a['sm_alternatief_toelichting'] ?? '')))],
            ['h3', 'Maatregelen om de risico’s te beheersen'],
            ['lijst', array_values(array_filter([
                ...array_map(static fn (string $m): string => SM_MAATREGELEN[$m]['label'], (array) ($a['sm_maatregelen'] ?? [])),
                ...cm_regels((string) ($a['sm_beheersing'] ?? '')),
            ]))],
        ],
        'niet' => [
            ['alinea', $bestaand
                ? 'Het advies is: zet dit kanaal niet langer in, en bouw het af.'
                : 'Het advies is: start dit kanaal niet.'],
            ['lijst', $u['redenen']],
        ],
        default => [
            ['alinea', 'De inzet past binnen de ruimte van het rijksbrede kader, zonder afwijkingen. Daarom is dit '
                . 'comply.'],
        ],
    };

    $livegang = $u['toetreding'] && $u['uitkomst'] !== 'niet'
        ? [['h3', 'Checklist voor livegang'], ['genummerd', SM_LIVEGANG]]
        : [];

    return [...$b, ...$livegang, ['h3', 'Vervolg'], ['genummerd', array_values($vervolg)]];
}
