<?php

/**
 * Wat de vragenlijst als .odt extra nodig heeft. Het formulier rekent het kader,
 * de toets en de uitkomst zelf uit; op papier doet de invuller dat. Daarom:
 * - bij de vragen over het gebruik een tabel met per platform wat binnen het
 *   kader valt
 * - in stap 4 de punten waarmee je zelf de uitkomst bepaalt, met de zinnen uit
 *   afleiding.php
 * - bij het vertrek de punten van het advies, met de zinnen uit exit.php
 * - als bijlage de kaders per platform, en de eisen aan een account van een
 *   bewindspersoon
 *
 * De rekenregel staat dus ook hier, in woorden. Verandert sm_evalueer() of
 * sm_exit_advies(), loop dan ook dit bestand na.
 */

declare(strict_types=1);

/**
 * @param callable(string):string $nr het nummer van een vraag, als "vraag 7"
 * @return array<string,mixed>
 */
function sm_vragenlijst(callable $nr): array
{
    $kaders = sm_kaders();
    $naam = static fn (string $k): string => $k === 'anders' ? 'een ander kanaal' : $kaders[$k]['naam'];
    // Een opsomming met "of": het gaat om het platform dat je koos.
    $namen = static function (callable $filter) use ($kaders, $naam): string {
        $lijst = array_map($naam, array_keys(array_filter($kaders, $filter)));

        return count($lijst) < 2 ? implode('', $lijst) : implode(', ', array_slice($lijst, 0, -1)) . ' of ' . end($lijst);
    };
    $vraag = static fn (string $key): string => ucfirst($nr($key));

    $tabel = static fn (string $key, string $ruimte): array => ['blokken' => [sm_vragenlijst_tabel(
        sm_toets_opties($key),
        $ruimte
    )]];

    $niet = [
        $vraag('sm_bereik') . ' is ‘Nee’. ' . sm_tekst('bereik'),
        $vraag('sm_alternatief') . ' is ‘Ja’. ' . sm_tekst('alternatief'),
        $vraag('sm_weging') . ' is ‘Nee’. ' . sm_tekst('weging'),
        $vraag('sm_toestel') . ' is een toestel van het Rijk. ' . sm_tekst('rijk_toestel', 'dit platform'),
        'Het account is van een bewindspersoon (' . $nr('sm_accounttype') . '), op '
            . $namen(static fn (array $k): bool => $k['bewindspersoon'] === 'verboden') . '. '
            . sm_tekst('bewindspersoon_verboden', 'dit platform'),
    ];

    $explain = [
        'Het platform is ' . $namen(static fn (array $k): bool => !empty($k['explain'])) . ' (' . $nr('sm_platform')
            . '). Daar vraagt het kader altijd een explain.',
        'Je kruiste bij ' . $nr('sm_doelen') . ', ' . $nr('sm_vormen') . ', ' . $nr('sm_betaald') . ' of '
            . $nr('sm_contactvormen') . ' iets aan zonder ‘ja’ bij je platform. Dat valt buiten het kader.',
        $vraag('sm_eigen_kanalen') . ' is ‘Voor een deel’ of ‘Nee’. ' . sm_tekst('eigen_deels'),
        $vraag('sm_doorverwijzen') . ' is ‘Nee’. ' . sm_tekst('doorverwijzen', 'dit platform'),
        $vraag('sm_toestel') . ' is een privétoestel. ' . sm_tekst('prive_toestel'),
        'Het account is van een bewindspersoon, op '
            . $namen(static fn (array $k): bool => $k['bewindspersoon'] === 'nee') . '. '
            . sm_tekst('bewindspersoon', 'dat platform'),
        'Het account is van een bewindspersoon, en je adverteert (' . $nr('sm_gebruik') . '). '
            . sm_tekst('bewindspersoon_betaald'),
    ];

    $explainvraag = 'de uitkomst hierboven ‘Explain’ is';

    return [
        'inleiding' => [
            ['h3', 'Voor je begint'],
            ['alinea', 'Eén vragenlijst gaat over één kanaal. Vraag 1 bepaalt welke delen je invult: bij een nieuw '
                . 'of bestaand kanaal de vier stappen, bij een vertrek de delen over het vertrek.'],
            ['alinea', 'Het formulier op de site rekent uit wat binnen het kader valt, en wat de uitkomst is. Hier doe '
                . 'je dat zelf. De tabellen in deel 2 laten per platform zien wat past. In deel 6 staan de punten '
                . 'voor de uitkomst. Wat het kader per platform zegt, staat in bijlage A.'],
        ],
        'vragen' => [
            'sm_platform' => ['na' => [['noot', 'Bij elk platform staat het advies uit het kader in het kort. Wat '
                . 'het kader verder zegt, staat in bijlage A.']]],
            'sm_accounttype' => ['na' => [['noot', 'Een bewindspersoon mag niet op elk platform. De eisen aan zo’n '
                . 'account staan in bijlage B.']]],
            'sm_doelen' => $tabel('sm_doelen', 'doelen'),
            'sm_vormen' => $tabel('sm_vormen', 'vormen'),
            'sm_betaald' => $tabel('sm_betaald', 'betaald'),
            'sm_contactvormen' => $tabel('sm_contactvormen', 'contact'),
            'sm_toets_doel' => ['blokken' => [
                ['h3', 'Past dit binnen het kader?'],
                ['alinea', 'Kijk in de tabellen hierboven, in de kolom van je platform. Staat er geen ‘ja’ bij iets '
                    . 'wat je aankruiste? Dan valt dat buiten het kader, en wordt het een explain. Schrijf het op bij '
                    . $nr('sm_afwijking') . '.'],
                ['alinea', 'Een ander kanaal heeft geen kader. Daar is het altijd een explain.'],
            ]],
            'sm_risicos' => ['blokken' => [['alinea', 'De risico’s van je platform staan in bijlage A. Lees ze '
                . 'voordat je de vragen hieronder beantwoordt.']]],
            'sm_uitkomst' => ['blokken' => [
                ['h3', 'Bepaal de uitkomst'],
                ['alinea', 'Loop de punten hieronder na, van boven naar beneden. Kruis aan wat geldt.'],
                ['h4', 'Niet inzetten, als een van deze punten geldt'],
                ['keuzes', array_map(static fn (string $t): array => [$t], $niet)],
                ['h4', 'Anders een explain, als een van deze punten geldt'],
                ['keuzes', array_map(static fn (string $t): array => [$t], $explain)],
                ['h4', 'Geldt geen van de punten?'],
                ['alinea', 'Dan is het comply. ' . sm_tekst('comply')],
                ['vraag', '', 'De uitkomst', 'kies één'],
                ['keuzes', array_map(static fn (array $u): array => [$u['label']], array_values(SM_UITKOMSTEN))],
            ]],
            'sm_exit_advies' => ['blokken' => [
                ['h3', 'Het advies over je vertrek'],
                ['alinea', 'Het formulier geeft een advies over de aanpak die je kiest. Hier zie je wanneer.'],
                ['lijst', [
                    'Vertrek je van het hele platform (' . $nr('sm_exit_scope') . ')? ' . SM_EXIT_TEKSTEN['platform'],
                    'Bereikt het kanaal zijn doel nog (' . $nr('sm_exit_doel') . '), en is het vertrek niet opgelegd? '
                        . SM_EXIT_TEKSTEN['doel'],
                    'Kies je geen soft exit, terwijl andere kanalen het vertrek moeilijk of niet opvangen ('
                        . $nr('sm_exit_opvang') . '), of de impact zeer ernstig is (' . $nr('sm_exit_impact') . ')? '
                        . 'En is het vertrek niet opgelegd? ' . SM_EXIT_TEKSTEN['zwaar_soft'],
                    'Kies je in die situatie wel een soft exit, maar niet de hybride vorm? ' . SM_EXIT_TEKSTEN['zwaar_hybride'],
                    'Verwijder je het account (' . $nr('sm_exit_type') . ')? ' . SM_EXIT_TEKSTEN['verwijderen'],
                    'Kies je een soft exit en deactiveer je het account? ' . SM_EXIT_TEKSTEN['aangeraden'],
                ]],
            ]],
        ],
        'uitkomsten' => [
            'toestel' => ['ja' => 'je platform ' . $namen(static fn (array $k): bool => !empty($k['toestel']))
                . ' is, of als het bedrijf in een land met een offensief cyberprogramma zit (' . $nr('sm_anders_land')
                . ')'],
            'uitkomst' => ['explain' => $explainvraag],
        ],
        'bijlagen' => [
            ['h2', 'Bijlage A: de kaders per platform'],
            ['alinea', 'Uit de rijksbrede platformkaders. De kaders worden elk jaar herzien.'],
            ...array_merge(...array_map(static fn (array $k): array => [
                ['h3', $k['naam']],
                ['term', 'Advies', SM_INZET[$k['inzet']]['label']],
                ['alinea', $k['uitleg']],
                ['term', 'Doelgroep', $k['doelgroep']],
                ['h4', 'Risico’s'],
                ['lijst', $k['risicos']],
                ...($k['bewindspersoon'] === null ? [] : [['term', 'Account van een bewindspersoon',
                    ['ja' => 'Mag', 'nee' => 'Valt buiten het kader', 'verboden' => 'Nooit'][$k['bewindspersoon']]]]),
            ], array_values($kaders))),
            ['h2', 'Bijlage B: een account van een bewindspersoon'],
            ['alinea', 'Uit de richtlijn voor toetreding.'],
            ['lijst', SM_BEWINDSPERSOON],
        ],
    ];
}

/**
 * Keuzes met per platform of ze binnen het kader vallen. Een hokje voor de
 * keuze, en een kolom per platform met een kader.
 *
 * @param array<string,array{label:string,hint?:string}> $opties
 * @return array<int,mixed>
 */
function sm_vragenlijst_tabel(array $opties, string $ruimte): array
{
    $kaders = array_filter(sm_kaders(), static fn (array $k): bool => $k[$ruimte] !== null);
    $kort = static fn (string $k, array $kader): string => $k === 'mastodon' ? 'Mastodon' : $kader['naam'];

    $rijen = [];
    foreach ($opties as $waarde => $optie) {
        $rijen[] = [
            array_values(array_filter(['☐ ' . $optie['label'], (string) ($optie['hint'] ?? '')])),
            ...array_map(static fn (array $k): string => in_array($waarde, $k[$ruimte], true) ? 'ja' : '–', $kaders),
        ];
    }

    $breedte = round(12.1 / count($kaders), 2);

    return ['tabel', [
        'kop' => ['Kruis aan', ...array_map($kort, array_keys($kaders), $kaders)],
        'rijen' => $rijen,
        'breedtes' => [16.6 - $breedte * count($kaders), ...array_fill(0, count($kaders), $breedte)],
    ]];
}
