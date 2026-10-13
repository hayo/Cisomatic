<?php

/**
 * De fit/gap: wat beide fit/gap formulieren delen. Die voor de BIO 1 staat in
 * deze map, die voor de BIO2 en de Cyberbeveiligingswet in ../fitgap2. Elk
 * formulier geeft zijn normen als delen. Dit bestand maakt er vragen van,
 * rekent de uitkomst uit en bouwt het document.
 *
 * Een deel wordt een sectie. Per norm vraagt het formulier of de dienst
 * voldoet, en hoe dat geregeld is. Voldoet de dienst nog niet, dan vraagt het
 * wat er moet gebeuren en wanneer het klaar is. Een deel dat een certificaat
 * afdekt, valt weg. Zie README.md.
 */

declare(strict_types=1);

/**
 * Het antwoord per norm. 'niveau' kleurt de regel. 'velden' moeten bij die
 * keuze ingevuld zijn; dat zijn de achtervoegsels van de sleutel.
 */
const FG_KEUZES = [
    'ja' => ['label' => 'Ja, de dienst voldoet', 'kort' => 'Voldoet', 'niveau' => 'l', 'velden' => ['_hoe']],
    'nee' => ['label' => 'Nee, nog niet', 'kort' => 'Voldoet nog niet', 'niveau' => 'h',
        'velden' => ['_maatregel', '_datum']],
    'nvt' => ['label' => 'Niet van toepassing', 'kort' => 'Niet van toepassing', 'niveau' => '', 'velden' => ['_nvt']],
];

/** De certificaten waarmee een deel wegvalt. */
const FG_CERTIFICATEN = [
    'fg_iso27001' => 'ISO 27001',
    'fg_iso27701' => 'ISO/IEC 27701',
    'fg_iso27017' => 'ISO 27017',
    'fg_iso27018' => 'ISO 27018',
];

/**
 * Normen uit een catalogus: nummer => [titel, tekst]. De sleutel volgt uit
 * het nummer; 'koppen' zet een tussenkop boven de normen die met dat nummer
 * beginnen.
 *
 * @param array<string,array{0:string,1:string}> $rijen
 * @param array<string,string> $koppen
 * @return list<array<string,mixed>>
 */
function fg_normen(string $prefix, array $rijen, array $koppen = []): array
{
    $normen = [];

    foreach ($rijen as $nummer => [$titel, $tekst]) {
        $kop = '';
        foreach ($koppen as $begin => $label) {
            if (str_starts_with($nummer . '.', $begin . '.')) {
                $kop = $label;
            }
        }

        $normen[] = [
            'key' => fg_sleutel($prefix, (string) $nummer),
            'nummer' => (string) $nummer,
            'titel' => $titel,
            'tekst' => $tekst,
            'kop' => $kop,
        ];
    }

    return $normen;
}

/** "fg_" en "CLD.6.3.1" worden "fg_cld_6_3_1". */
function fg_sleutel(string $prefix, string $nummer): string
{
    return $prefix . trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower($nummer)), '_');
}

// ---------------------------------------------------------------------------
// De vragen

/**
 * Eén norm als groep die één regel wordt: de kop met de tekst van de norm,
 * het antwoord, hoe het geregeld is, en wat er nog moet gebeuren. Een norm
 * met 'nvt' mag niet van toepassing zijn; 'nvt' is dan de uitleg wanneer.
 *
 * @param array<string,mixed> $norm
 * @return list<array<string,mixed>>
 */
function fg_norm_vragen(array $norm, string $deel): array
{
    $key = $norm['key'];
    $nummer = $norm['kort'] ?? $norm['nummer'];
    $opties = array_map(static fn (array $k): array => ['label' => $k['label']], FG_KEUZES);

    if (empty($norm['nvt'])) {
        unset($opties['nvt']);
    } else {
        $opties['nvt']['hint'] = $norm['nvt'];
    }

    $vragen = [
        [
            'key' => $key . '_kop',
            'type' => 'kop',
            'label' => $norm['nummer'] . ' ' . $norm['titel'],
            'hint' => $norm['tekst'],
            'stand' => 'fg_norm_stand',
        ],
        [
            'key' => $key,
            'type' => 'radio',
            'label' => 'Voldoet de dienst aan ' . $nummer . '?',
            'verplicht' => true,
            'opties' => $opties,
            'norm' => ['nummer' => $norm['nummer'], 'titel' => $norm['titel']],
            'deel' => $deel,
        ],
        [
            'key' => $key . '_hoe',
            'type' => 'textarea',
            'label' => 'Hoe is ' . $nummer . ' geregeld?',
            'hint' => 'Kort en concreet. Noem ook het document, en waar het daarin staat. Voldoet de dienst nog niet? '
                . 'Schrijf dan op wat er al is.',
            'verplicht_als' => [$key => ['ja']],
            'toon_als' => [$key => ['ja', 'nee']],
            'hoogte' => 3,
        ],
        [
            'key' => $key . '_maatregel',
            'type' => 'textarea',
            'label' => 'Wat moet er voor ' . $nummer . ' nog gebeuren?',
            'hint' => 'De maatregel waarmee de dienst wel voldoet.',
            'verplicht_als' => [$key => ['nee']],
            'toon_als' => [$key => ['nee']],
            'hoogte' => 2,
        ],
        [
            'key' => $key . '_datum',
            'type' => 'text',
            'label' => 'Wanneer voldoet de dienst aan ' . $nummer . '?',
            'hint' => 'De datum waarop het klaar is. Bijvoorbeeld: 1 maart 2027.',
            'verplicht_als' => [$key => ['nee']],
            'toon_als' => [$key => ['nee']],
        ],
    ];

    if (!empty($norm['nvt'])) {
        $vragen[] = [
            'key' => $key . '_nvt',
            'type' => 'textarea',
            'label' => 'Waarom geldt ' . $nummer . ' niet?',
            'verplicht_als' => [$key => ['nvt']],
            'toon_als' => [$key => ['nvt']],
            'hoogte' => 2,
        ];
    }

    // Een norm die bij deze antwoorden niet geldt, verdwijnt met al zijn vragen.
    $als = $norm['toon_als'] ?? [];

    return array_map(static fn (array $v): array => ['toon_als' => ($v['toon_als'] ?? []) + $als] + $v
        + ['groep' => $key], $vragen);
}

/**
 * Een deel als sectie, met een tussenkop boven elke groep normen en de stand
 * van het deel eronder. Een tussenkop verdwijnt als geen van zijn normen geldt.
 *
 * @param array<string,mixed> $deel
 * @return array<string,mixed>
 */
function fg_sectie_deel(array $deel): array
{
    $vragen = [];
    $groepen = [];

    foreach ($deel['normen'] as $norm) {
        $groepen[$norm['kop'] ?? ''][] = $norm;
    }

    foreach ($groepen as $kop => $normen) {
        if ($kop !== '') {
            $vragen[] = [
                'key' => 'fg_kop_' . $deel['id'] . '_' . count($vragen),
                'type' => 'kop',
                'label' => $kop,
                'toon_als' => fg_toon_als_een_van($normen),
            ];
        }
        foreach ($normen as $norm) {
            array_push($vragen, ...fg_norm_vragen($norm, $deel['id']));
        }
    }

    $vragen[] = [
        'key' => 'fg_afgeleid_' . $deel['id'],
        'type' => 'afgeleid',
        'bron' => $deel['id'],
        'label' => 'Stand van dit deel:',
    ];

    return [
        'id' => $deel['id'],
        'titel' => $deel['titel'],
        'stap' => $deel['stap'],
        'intro' => $deel['intro'],
        'toon_als' => $deel['toon_als'] ?? [],
        'deel' => array_diff_key($deel, ['normen' => true]),
        'vragen' => $vragen,
    ];
}

/**
 * De voorwaarde waaronder minstens één van deze normen geldt. Dat kan alleen
 * als ze allemaal op dezelfde ene vraag letten; anders staat de kop er altijd.
 *
 * @param list<array<string,mixed>> $normen
 * @return array<string,list<string>>
 */
function fg_toon_als_een_van(array $normen): array
{
    $waarden = [];

    foreach ($normen as $norm) {
        $als = $norm['toon_als'] ?? [];
        if (count($als) !== 1) {
            return [];
        }
        $waarden[key($als)][] = current($als);
    }

    return count($waarden) === 1
        ? [key($waarden) => array_values(array_unique(array_merge(...current($waarden))))]
        : [];
}

/** De eerste sectie: de dienst, de opdrachtnemer en wie invult. */
function fg_sectie_dienst(): array
{
    return [
        'id' => 'dienst',
        'titel' => 'De dienst en de opdrachtnemer',
        'stap' => 1,
        'intro' => 'Eerst wie de verklaring geeft, en voor welke dienst.',
        'vragen' => [
            [
                'key' => 'projectnaam',
                'type' => 'text',
                'label' => 'Voor welk product of welke dienst geldt de verklaring?',
                'hint' => 'Bijvoorbeeld: Mediadiensten. Deze naam komt in het document en in de bestandsnaam.',
                'verplicht' => true,
            ],
            [
                'key' => 'fg_opdrachtnemer',
                'type' => 'text',
                'label' => 'Welk bedrijf levert de dienst?',
                'hint' => 'De opdrachtnemer. De verklaring is van dit bedrijf.',
                'verplicht' => true,
            ],
            [
                'key' => 'fg_opdrachtgever',
                'type' => 'text',
                'label' => 'Voor welke opdrachtgever is de verklaring?',
                'verplicht' => true,
                'standaard' => (string) (cm_config()['organisatie']['naam'] ?? ''),
            ],
            [
                'key' => 'fg_omschrijving',
                'type' => 'textarea',
                'label' => 'Welke systemen en processen horen bij de dienst?',
                'hint' => 'Alles wat de opdrachtgever afneemt, en waarin informatie van of voor de opdrachtgever wordt '
                    . 'verwerkt. Ook het beheer. Daarvoor gelden de normen.',
                'verplicht' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'aanvrager',
                'type' => 'text',
                'label' => 'Wie vult de verklaring in?',
                'hint' => 'Naam en functie van de verantwoordelijke medewerker bij de opdrachtnemer. Deze naam komt '
                    . 'onder de verklaring.',
                'verplicht' => true,
            ],
            [
                'key' => 'email',
                'type' => 'email',
                'label' => 'Op welk mailadres ben je bereikbaar?',
                'optioneel' => true,
            ],
        ],
    ];
}

/**
 * De vragen die bepalen welke delen gelden: de rol bij persoonsgegevens en
 * de cloud. Een formulier zet er eigen vragen voor en na.
 *
 * @param list<array<string,mixed>> $voor
 * @param list<array<string,mixed>> $na
 */
function fg_sectie_reikwijdte(string $titel, string $intro, array $voor, array $na): array
{
    return [
        'id' => 'reikwijdte',
        'titel' => $titel,
        'stap' => 1,
        'intro' => $intro,
        'vragen' => [
            ...$voor,
            [
                'key' => 'fg_rol',
                'type' => 'radio',
                'label' => 'Welke rol heeft de opdrachtnemer bij persoonsgegevens?',
                'hint' => 'Persoonsgegevens zijn alle gegevens over een persoon, ook namen en mailadressen.',
                'verplicht' => true,
                'opties' => [
                    'verwerker' => ['label' => 'Verwerker', 'hint' => 'De opdrachtnemer verwerkt persoonsgegevens in '
                        . 'opdracht van de opdrachtgever. Dat komt het meest voor.'],
                    'verantwoordelijke' => ['label' => 'Verwerkingsverantwoordelijke',
                        'hint' => 'De opdrachtnemer bepaalt zelf waarom en hoe hij persoonsgegevens verwerkt.'],
                    'beide' => ['label' => 'Allebei', 'hint' => 'Voor een deel verwerker, voor een ander deel '
                        . 'verantwoordelijke.'],
                    'geen' => ['label' => 'Geen van beide', 'hint' => 'De dienst verwerkt geen persoonsgegevens.'],
                ],
            ],
            [
                'key' => 'fg_cloud',
                'type' => 'radio',
                'label' => 'Verwerkt de dienst gegevens in een cloud?',
                'hint' => 'Software die je via internet gebruikt (SaaS) telt ook mee, net als hosting bij een andere '
                    . 'partij.',
                'verplicht' => true,
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'openbaar' => ['label' => 'Ja, maar alleen openbare gegevens'],
                    'vertrouwelijk' => ['label' => 'Ja, ook vertrouwelijke gegevens, maar geen persoonsgegevens'],
                    'pg' => ['label' => 'Ja, ook persoonsgegevens', 'hint' => 'Persoonsgegevens zijn ook vertrouwelijk.'],
                ],
            ],
            ...$na,
        ],
    ];
}

/**
 * De certificaten. Met een volledig certificaat valt een deel weg. Onderaan
 * staat wat de invuller daarna nog invult: de lijst van het formulier, en de
 * delen met een 'lijst'.
 *
 * @param list<array<string,mixed>> $lijst
 * @param list<array<string,mixed>> $delen
 */
function fg_sectie_certificaten(string $hint27001, array $lijst, array $delen): array
{
    $bewijs = static fn (string $key, string $naam): array => [
        'key' => $key . '_bewijs',
        'type' => 'textarea',
        'label' => 'Wie gaf het certificaat voor ' . $naam . ' af, en tot wanneer is het geldig?',
        'hint' => 'Noem ook waar de scope deze dienst beschrijft.',
        'verplicht_als' => [$key => ['ja']],
        'toon_als' => [$key => ['ja']],
        'hoogte' => 2,
    ];
    $janee = static fn (string $ja, string $nee): array => ['ja' => ['label' => $ja], 'nee' => ['label' => $nee]];

    foreach ($delen as $deel) {
        if (isset($deel['lijst'])) {
            $lijst[] = ['tekst' => $deel['lijst'], 'toon_als' => $deel['toon_als'] ?? []];
        }
    }

    return [
        'id' => 'certificaten',
        'titel' => 'Certificaten',
        'stap' => 1,
        'intro' => 'Met een volledig certificaat hoef je een deel van de normen niet in te vullen. Stuur dan het '
            . 'certificaat mee, met de auditscope en de verklaring van toepasselijkheid.',
        'vragen' => [
            [
                'key' => 'fg_iso27001',
                'type' => 'radio',
                'label' => 'Heeft de opdrachtnemer een ISO 27001 certificaat voor deze dienst?',
                'hint' => $hint27001,
                'verplicht' => true,
                'opties' => $janee('Ja, en de dienst valt binnen de scope', 'Nee, of de dienst valt er niet onder'),
            ],
            $bewijs('fg_iso27001', 'ISO 27001'),
            [
                'key' => 'fg_iso27701',
                'type' => 'radio',
                'label' => 'Heeft de opdrachtnemer een volledig ISO/IEC 27701 certificaat?',
                'hint' => 'Volledig betekent: met bijlage A en bijlage B. Dan voldoet de dienst vanzelf aan de normen '
                    . 'voor persoonsgegevens uit die norm.',
                'verplicht' => true,
                'toon_als' => ['fg_rol' => ['verwerker', 'verantwoordelijke', 'beide']],
                'opties' => $janee('Ja', 'Nee'),
            ],
            $bewijs('fg_iso27701', 'ISO/IEC 27701'),
            [
                'key' => 'fg_iso27017',
                'type' => 'radio',
                'label' => 'Heeft de opdrachtnemer een volledig ISO 27017 certificaat?',
                'hint' => 'Dan voldoet de dienst vanzelf aan de normen voor de cloud.',
                'verplicht' => true,
                'toon_als' => ['fg_cloud' => ['vertrouwelijk', 'pg']],
                'opties' => $janee('Ja', 'Nee'),
            ],
            $bewijs('fg_iso27017', 'ISO 27017'),
            [
                'key' => 'fg_iso27018',
                'type' => 'radio',
                'label' => 'Heeft de opdrachtnemer ook een volledig ISO 27018 certificaat?',
                'hint' => 'Alleen met ISO 27017 én ISO 27018 voldoet de dienst vanzelf aan de normen voor '
                    . 'persoonsgegevens in de cloud.',
                'verplicht' => true,
                'toon_als' => ['fg_cloud' => ['pg'], 'fg_iso27017' => ['ja']],
                'opties' => $janee('Ja', 'Nee'),
            ],
            $bewijs('fg_iso27018', 'ISO 27018'),
            [
                'key' => 'fg_afgeleid_lijst',
                'type' => 'afgeleid',
                'label' => 'Dit vul je hierna in:',
                'lijst' => $lijst,
            ],
        ],
    ];
}

/**
 * De laatste sectie: de uitkomst, en de verklaring zelf. Het uitkomstblok
 * draagt het model voor regels.js.
 *
 * @param list<array<string,mixed>> $delen
 */
function fg_sectie_verklaring(int $stap, array $delen): array
{
    return [
        'id' => 'verklaring',
        'titel' => 'De verklaring',
        'stap' => $stap,
        'intro' => 'Hieronder staat de uitkomst. Kijk die na, en bevestig de verklaring. Daarna maakt het formulier het '
            . 'document.',
        'aanvullend' => 'Toelichting',
        'vragen' => [
            [
                'key' => 'fg_afgeleid_uitkomst',
                'type' => 'afgeleid',
                'bron' => 'uitkomst',
                'label' => 'Uitkomst:',
                'groepen' => fg_model($delen),
            ],
            [
                'key' => 'fg_akkoord',
                'type' => 'checkbox',
                'label' => 'Klopt de verklaring?',
                'hint' => 'De verklaring is officieel. De opdrachtgever gaat ervan uit dat ze klopt.',
                'verplicht' => true,
                'opties' => ['ja' => ['label' => 'Ja. Ik verklaar namens de opdrachtnemer dat de antwoorden juist en '
                    . 'volledig zijn.']],
            ],
            [
                'key' => 'fg_toelichting',
                'type' => 'textarea',
                'label' => 'Wil je nog iets toelichten?',
                'hint' => 'Dit komt in het document, bij de uitkomst.',
                'optioneel' => true,
                'hoogte' => 3,
            ],
        ],
    ];
}

/**
 * Het model voor regels.js: per deel de sleutels en nummers van de normen,
 * en de keuzes. Zo staan ze maar op één plek.
 *
 * @param list<array<string,mixed>> $delen
 * @return array<string,mixed>
 */
function fg_model(array $delen): array
{
    return [
        'delen' => array_map(static fn (array $d): array => [
            'id' => $d['id'],
            'naam' => $d['naam'],
            'normen' => array_map(static fn (array $n): array => [$n['key'], $n['nummer']], $d['normen']),
        ], $delen),
        'keuzes' => array_map(
            static fn (array $k): array => ['kort' => $k['kort'], 'niveau' => $k['niveau'], 'velden' => $k['velden']],
            FG_KEUZES
        ),
    ];
}

// ---------------------------------------------------------------------------
// Rekenen

/** Een antwoord, met de velden die bij dat antwoord horen. */
function fg_norm_compleet(string $key, array $a): bool
{
    $keuze = FG_KEUZES[(string) ($a[$key] ?? '')] ?? null;

    return $keuze !== null && array_filter(
        $keuze['velden'],
        static fn (string $veld): bool => trim((string) ($a[$key . $veld] ?? '')) === ''
    ) === [];
}

/** "Voldoet nog niet · 1 maart 2027", zoals de regel van een norm dat toont. */
function fg_norm_samenvatting(string $key, array $a): string
{
    $waarde = (string) ($a[$key] ?? '');
    $datum = trim((string) ($a[$key . '_datum'] ?? ''));

    if ($waarde === '') {
        return 'nog in te vullen';
    }

    return FG_KEUZES[$waarde]['kort'] . ($waarde === 'nee' && $datum !== '' ? ' · ' . $datum : '');
}

/** De regel van een norm: het antwoord, en open zolang er iets ontbreekt. */
function fg_norm_stand(string $key, array $a): array
{
    return [
        'samenvatting' => fg_norm_samenvatting($key, $a),
        'niveau' => (string) (FG_KEUZES[(string) ($a[$key] ?? '')]['niveau'] ?? ''),
        'open' => !fg_norm_compleet($key, $a),
    ];
}

/**
 * Per deel dat geldt de normen met hun antwoord, en welke delen een
 * certificaat afdekt. Een norm of deel dat niet geldt, valt weg.
 *
 * @return array{0:array<string,array<string,mixed>>,1:array<string,list<string>>}
 */
function fg_uitslag(array $a): array
{
    $waardeVan = static fn (string $k): mixed => $a[$k] ?? null;
    $tekst = static fn (string $key): string => trim((string) ($a[$key] ?? ''));
    $delen = [];
    $afgedekt = [];

    foreach (cm_secties() as $sectie) {
        $deel = $sectie['deel'] ?? null;
        if ($deel === null) {
            continue;
        }

        if (!empty($deel['afgedekt_als']) && cm_matcht($deel['afgedekt_als'], $waardeVan)) {
            $afgedekt[$deel['certificaat']][] = $deel['kader'];
        }
        if (!cm_zichtbaar($sectie, $a)) {
            continue;
        }

        $normen = [];
        $telling = ['ja' => 0, 'nee' => 0, 'nvt' => 0, 'open' => 0];

        foreach ($sectie['vragen'] as $vraag) {
            if (!isset($vraag['norm']) || !cm_zichtbaar($vraag, $a)) {
                continue;
            }

            $key = $vraag['key'];
            $status = (string) ($a[$key] ?? '');
            $telling[$status === '' ? 'open' : $status]++;
            $normen[] = $vraag['norm'] + [
                'key' => $key,
                'status' => $status,
                'hoe' => $tekst($key . '_hoe'),
                'maatregel' => $tekst($key . '_maatregel'),
                'datum' => $tekst($key . '_datum'),
                'nvt' => $tekst($key . '_nvt'),
                'deel' => $deel['naam'],
            ];
        }

        $delen[$deel['id']] = $deel + ['normen' => $normen, 'telling' => $telling];
    }

    return [$delen, array_map(static fn (array $k): array => array_values(array_unique($k)), $afgedekt)];
}

/**
 * Alles wat pagina en document nodig hebben. Een formulier vult dit aan met
 * zijn eigen 'kenmerken', 'uitleg' en 'vervolg'.
 *
 * @return array<string,mixed>
 */
function fg_evalueer(array $a): array
{
    [$delen, $afgedekt] = fg_uitslag($a);
    $telling = ['ja' => 0, 'nee' => 0, 'nvt' => 0, 'open' => 0];
    $gaten = [];

    foreach ($delen as $deel) {
        foreach ($deel['telling'] as $soort => $aantal) {
            $telling[$soort] += $aantal;
        }
        foreach ($deel['normen'] as $norm) {
            if ($norm['status'] === 'nee') {
                $gaten[] = $norm;
            }
        }
    }

    $bijlagen = [];
    foreach (FG_CERTIFICATEN as $key => $naam) {
        if (($a[$key] ?? '') === 'ja') {
            $bijlagen[] = 'Het certificaat voor ' . $naam . ', met de auditscope en de verklaring van toepasselijkheid.';
        }
    }

    $voldoet = $telling['nee'] === 0 && $telling['open'] === 0;

    return [
        'delen' => $delen,
        'afgedekt' => $afgedekt,
        'telling' => $telling,
        'totaal' => array_sum($telling),
        'gaten' => $gaten,
        'bijlagen' => $bijlagen,
        'voldoet' => $voldoet,
        'oordeel' => $voldoet ? 'Voldoet' : 'Voldoet nog niet',
        'kenmerken' => [],
        'uitleg' => [],
        'vervolg' => fg_vervolg($a, $gaten, $bijlagen),
    ];
}

/**
 * @param list<array<string,mixed>> $gaten
 * @param list<string> $bijlagen
 * @return list<string>
 */
function fg_vervolg(array $a, array $gaten, array $bijlagen): array
{
    $opdrachtgever = trim((string) ($a['fg_opdrachtgever'] ?? '')) ?: 'de opdrachtgever';

    return array_values(array_filter([
        'Onderteken het document, en stuur het naar ' . $opdrachtgever . ($bijlagen === [] ? '.' : ', met de bijlagen.'),
        $gaten === [] ? '' : 'Neem de maatregelen voor de normen waaraan de dienst nog niet voldoet, op de datum die je '
            . 'noemt. Laat ' . $opdrachtgever . ' weten als de dienst wel voldoet.',
        'De dienstverlening kan pas starten als de dienst aan alle normen voldoet.',
        'Werk de verklaring bij als de dienst, de beveiliging of de certificaten veranderen.',
    ]));
}

/** "1 norm" of "3 normen". */
function fg_aantal(int $aantal, string $een = 'norm', string $meer = 'normen'): string
{
    return $aantal . ' ' . ($aantal === 1 ? $een : $meer);
}

/** "De normen uit de BIO dekt ISO 27001 af." per certificaat. @return list<string> */
function fg_afgedekt_zinnen(array $u): array
{
    $zinnen = [];

    foreach ($u['afgedekt'] as $certificaat => $kaders) {
        $zinnen[] = ucfirst(cm_opsomming($kaders)) . ' vallen onder het certificaat voor '
            . FG_CERTIFICATEN[$certificaat] . '. Die normen staan niet in deze verklaring.';
    }

    return $zinnen;
}

// ---------------------------------------------------------------------------
// Het document

/**
 * Het document: de titelpagina, de uitkomst, wat nog moet gebeuren, wat de
 * verklaring dekt, alle normen, de bijlagen en de handtekeningen.
 *
 * @return list<array<int,mixed>>
 */
function fg_document(array $a, array $u, string $titel): array
{
    $naam = trim((string) ($a['projectnaam'] ?? '')) ?: 'Naamloze dienst';

    return [
        ['h1', $titel . ': ' . $naam],
        ['term', 'Uitkomst', $u['oordeel']],
        ['term', 'Opdrachtnemer', (string) ($a['fg_opdrachtnemer'] ?? '')],
        ['term', 'Opdrachtgever', (string) ($a['fg_opdrachtgever'] ?? '')],
        ...array_map(static fn (array $k): array => ['term', $k[0], $k[1]], $u['kenmerken']),
        ['term', 'Datum', cm_datum_nl(new DateTimeImmutable('now'))],
        ['term', 'Opgesteld door', (string) ($a['aanvrager'] ?? '')],
        ...(($a['email'] ?? '') === '' ? [] : [['term', 'Contact', (string) $a['email']]]),
        ...fg_document_uitkomst($a, $u),
        ...fg_document_gaten($u),
        ...fg_document_reikwijdte($a, $u),
        ...fg_document_normen($u),
        ...fg_document_ondertekening($a, $u),
    ];
}

function fg_document_uitkomst(array $a, array $u): array
{
    $t = $u['telling'];
    $rijen = array_map(static fn (array $d): array => [
        $d['naam'],
        (string) count($d['normen']),
        (string) $d['telling']['ja'],
        (string) $d['telling']['nee'],
        (string) $d['telling']['nvt'],
    ], array_values($u['delen']));

    return [
        ['h2', 'De uitkomst'],
        ['alinea', $u['voldoet']
            ? 'De dienst voldoet aan alle ' . fg_aantal($u['totaal']) . ' die gelden.'
            : sprintf(
                'Van de %s die gelden, voldoet de dienst aan %s nog niet. De dienstverlening kan pas starten als de '
                . 'dienst aan alle normen voldoet.',
                fg_aantal($u['totaal']),
                $t['nee']
            )],
        ...array_map(static fn (string $r): array => ['alinea', $r], cm_regels((string) ($a['fg_toelichting'] ?? ''))),
        ['tabel', [
            'kop' => ['Deel', 'Normen', 'Voldoet', 'Nog niet', 'N.v.t.'],
            'rijen' => $rijen,
            'breedtes' => [8.6, 2, 2, 2, 2],
            'rijkop' => true,
        ]],
        ...array_map(static fn (string $z): array => ['alinea', $z], fg_afgedekt_zinnen($u)),
    ];
}

function fg_document_gaten(array $u): array
{
    if ($u['gaten'] === []) {
        return [];
    }

    return [
        ['h2', 'Wat nog moet gebeuren'],
        ['alinea', 'De opdrachtnemer neemt deze maatregelen. Daarna voldoet de dienst aan de norm.'],
        ['tabel', [
            'kop' => ['Norm', 'Maatregel', 'Klaar op'],
            'rijen' => array_map(static fn (array $n): array => [
                $n['nummer'] . ' ' . $n['titel'],
                cm_regels($n['maatregel']),
                $n['datum'],
            ], $u['gaten']),
            'breedtes' => [5.6, 7.6, 3.4],
        ]],
    ];
}

function fg_document_reikwijdte(array $a, array $u): array
{
    $certificaten = [];
    foreach (FG_CERTIFICATEN as $key => $naam) {
        $antwoord = (string) ($a[$key] ?? '');
        if ($antwoord !== '') {
            $certificaten[] = [$naam, $antwoord === 'ja' ? 'Ja' : 'Nee', cm_regels((string) ($a[$key . '_bewijs'] ?? ''))];
        }
    }

    $opdrachtnemer = trim((string) ($a['fg_opdrachtnemer'] ?? '')) ?: 'de opdrachtnemer';
    $opdrachtgever = trim((string) ($a['fg_opdrachtgever'] ?? '')) ?: 'de opdrachtgever';

    return [
        ['h2', 'Wat de verklaring dekt'],
        ['alinea', 'Dit document is de officiële verklaring van ' . $opdrachtnemer . ' aan ' . $opdrachtgever . '. '
            . 'Daarin staat of de dienst aan de eisen voor informatiebeveiliging en privacy voldoet.'],
        ['alinea', 'De eisen gelden voor de systemen en processen die ' . $opdrachtgever . ' afneemt, en waarin '
            . 'informatie van of voor ' . $opdrachtgever . ' wordt verwerkt. Ook het beheer valt eronder.'],
        ['h3', 'Systemen en processen'],
        ...array_map(static fn (string $r): array => ['alinea', $r], cm_regels((string) ($a['fg_omschrijving'] ?? ''))),
        ['h3', 'Welke normen gelden'],
        ...array_map(static fn (array $k): array => ['term', $k[0], $k[1]], $u['kenmerken']),
        ['term', 'Rol bij persoonsgegevens', cm_antwoord_tekst('fg_rol', $a)],
        ['term', 'Cloud', cm_antwoord_tekst('fg_cloud', $a)],
        ...array_map(static fn (string $r): array => ['alinea', $r], $u['uitleg']),
        ['h3', 'Certificaten'],
        ['tabel', [
            'kop' => ['Certificaat', 'Heeft het', 'Uitgegeven door, en geldig tot'],
            'rijen' => $certificaten,
            'breedtes' => [4, 2.4, 10.2],
            'rijkop' => true,
        ]],
    ];
}

/** Per deel een tabel met alle normen die gelden, en het antwoord. */
function fg_document_normen(array $u): array
{
    $b = [
        ['h2', 'De normen'],
        ['alinea', 'Per deel staan hier alle normen die gelden, met het antwoord van de opdrachtnemer. Bij een norm '
            . 'waaraan de dienst nog niet voldoet, staat ook wat er moet gebeuren en wanneer het klaar is.'],
    ];

    foreach ($u['delen'] as $deel) {
        $b[] = ['h3', $deel['titel']];
        $b[] = ['tabel', [
            'kop' => ['Norm', 'Voldoet', 'Toelichting'],
            'rijen' => array_map(static fn (array $n): array => [
                $n['nummer'] . ' ' . $n['titel'],
                ['ja' => 'Ja', 'nee' => 'Nog niet', 'nvt' => 'N.v.t.'][$n['status']] ?? '',
                fg_toelichting($n),
            ], $deel['normen']),
            'breedtes' => [5.6, 2.2, 8.8],
        ]];
    }

    return $b;
}

/**
 * De toelichting bij een norm, als regels: hoe het geregeld is, en bij een
 * norm die nog niet voldoet de maatregel en de datum.
 *
 * @param array<string,mixed> $n
 * @return list<string>
 */
function fg_toelichting(array $n): array
{
    return match ($n['status']) {
        'nee' => [
            ...cm_regels($n['hoe']),
            'Nog te doen: ' . implode(' ', cm_regels($n['maatregel'])),
            'Klaar op: ' . $n['datum'],
        ],
        'nvt' => cm_regels($n['nvt']),
        default => cm_regels($n['hoe']),
    };
}

function fg_document_ondertekening(array $a, array $u): array
{
    $opdrachtnemer = trim((string) ($a['fg_opdrachtnemer'] ?? '')) ?: 'de opdrachtnemer';

    return [
        ...($u['bijlagen'] === [] ? [] : [
            ['h2', 'Bijlagen'],
            ['alinea', 'Deze stukken horen bij de verklaring.'],
            ['lijst', $u['bijlagen']],
        ]),
        ['h2', 'Ondertekening'],
        ['alinea', 'Namens ' . $opdrachtnemer . ' verklaar ik dat de antwoorden in dit document juist en volledig '
            . 'zijn.'],
        ['term', 'Naam en functie', (string) ($a['aanvrager'] ?? '')],
        ['term', 'Namens', $opdrachtnemer],
        ['term', 'Datum', cm_datum_nl(new DateTimeImmutable('now'))],
        ['term', 'Handtekening', ''],
        ['h2', 'Beoordeling door de opdrachtgever'],
        ['alinea', 'Voldoet de dienst aan de eisen? En zijn de maatregelen en de data goed genoeg voor de normen waaraan '
            . 'de dienst nog niet voldoet?'],
        ['term', 'Naam', ''],
        ['term', 'Datum', ''],
        ['term', 'Oordeel', ''],
        ['term', 'Toelichting', ''],
    ];
}

/** Persoonsgegevens in de cloud, maar geen rol bij persoonsgegevens: dat kan niet allebei. */
function fg_valideer(array $a): array
{
    return ($a['fg_rol'] ?? '') === 'geen' && ($a['fg_cloud'] ?? '') === 'pg'
        ? ['fg_cloud' => 'Je gaf aan dat de dienst geen persoonsgegevens verwerkt. Kies hier dan geen persoonsgegevens, '
            . 'of pas de rol aan.']
        : [];
}
