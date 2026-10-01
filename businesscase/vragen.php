<?php

/**
 * De vragenlijst van de business case en intaketoets: vijf toetsen en het
 * voorstel.
 *
 * Dit is het bestand om aan te passen als je vragen wilt toevoegen,
 * herformuleren of weghalen. Formulier, controle en document lezen allemaal
 * uit deze definitie. De sleutels per vraag staan in ../README.md. De sleutel
 * 'bij' betekent: de vraag hoort bij de vraag ervoor, en krijgt in het
 * document geen eigen kop.
 *
 * Elke optie van een getoetste vraag heeft een 'niveau': l, m of h. Elke toets
 * eindigt met een uitkomstblok dat die niveaus in 'groepen' meekrijgt, zodat
 * PHP en regels.js dezelfde gegevens lezen.
 */

declare(strict_types=1);

/** De keuzes bij elke plek waar al een oplossing kan zijn. */
const BC_CHECK_OPTIES = [
    'past' => ['label' => 'Ja, dat past', 'niveau' => 'h'],
    'deels' => ['label' => 'Deels, maar het is niet genoeg', 'niveau' => 'l'],
    'nee' => ['label' => 'Nee, dat past niet', 'niveau' => 'l'],
    'niet_gekeken' => ['label' => 'Nog niet uitgezocht', 'niveau' => 'm'],
];

/** @return list<array<string,mixed>> */
function bc_secties(): array
{
    return [
        bc_sectie_over(),
        bc_sectie_nodig(),
        bc_sectie_zonder_it(),
        bc_sectie_bestaand(),
        bc_sectie_taak(),
        bc_sectie_dragen(),
        bc_sectie_voorstel(),
    ];
}

/**
 * Een open vraag: standaard een verplicht tekstvak. $extra vult aan of
 * overschrijft, bijvoorbeeld met een type en opties.
 *
 * @param array<string,mixed> $extra
 * @return array<string,mixed>
 */
function bc_vraag(string $key, string $label, string $hint = '', array $extra = []): array
{
    return $extra + ['key' => $key, 'type' => 'textarea', 'label' => $label, 'verplicht' => true, 'hoogte' => 3]
        + ($hint === '' ? [] : ['hint' => $hint]);
}

/** Een keuze uit een paar opties. @return array<string,mixed> */
function bc_keuze(string $key, string $label, array $opties, string $hint = '', array $extra = []): array
{
    return bc_vraag($key, $label, $hint, $extra + ['type' => 'radio', 'opties' => $opties]);
}

/** Een toelichting bij de vraag ervoor. @return array<string,mixed> */
function bc_uitleg(string $key, string $label, array $extra = []): array
{
    return bc_vraag($key, $label, '', $extra + ['bij' => true, 'hoogte' => 2]);
}

/**
 * Een toets: een sectie die eindigt met een uitkomstblok. Het blok krijgt de
 * niveaus van alle getoetste vragen mee. Voor de vragen in $of telt het beste
 * antwoord: één ervan is genoeg.
 *
 * @param list<array<string,mixed>> $vragen
 * @param array<string,list<string>> $redenen per niveau
 * @param list<string> $of
 * @return array<string,mixed>
 */
function bc_toets(int $stap, string $id, string $titel, string $intro, array $vragen, array $redenen, array $of = []): array
{
    $niveaus = [];
    foreach ($vragen as $vraag) {
        if (isset($vraag['opties']) && isset(reset($vraag['opties'])['niveau'])) {
            $niveaus[$vraag['key']] = array_map(static fn (array $o): string => $o['niveau'], $vraag['opties']);
        }
    }

    $vragen[] = [
        'key' => 'bc_afgeleid_' . $id,
        'type' => 'afgeleid',
        'bron' => $id,
        'label' => 'Uitkomst van toets ' . $stap . ':',
        'groepen' => ['vragen' => $niveaus, 'of' => $of, 'labels' => BC_TOETS_LABELS, 'redenen' => $redenen],
    ];

    return ['id' => $id, 'titel' => $titel, 'stap' => $stap, 'intro' => $intro, 'vragen' => $vragen];
}

/**
 * De plekken waar al een oplossing kan zijn. Toets 3 en 4 stellen ze.
 *
 * @return array<string,array{label:string,hint:string}>
 */
function bc_checks(): array
{
    return [
        'bc_check_eigen' => [
            'label' => 'Kan een systeem dat jullie al hebben dit?',
            'hint' => 'Een systeem of een licentie die er al is, kan vaak meer dan je denkt. Past het deels? Kijk dan '
                . 'of jullie de werkwijze kunnen aanpassen aan dat systeem. Kan het technisch, maar past het niet bij '
                . 'de rest? Schrijf dat dan op.',
        ],
        'bc_check_rijk' => [
            'label' => 'Is er een voorziening van het Rijk die dit doet, of komt die eraan?',
            'hint' => 'Veel regelt het Rijk één keer voor iedereen. Denk aan de generieke digitale infrastructuur '
                . '(GDI) van Logius, met onder meer DigiD en MijnOverheid, of aan een shared service organisatie.',
        ],
        'bc_check_hergebruik' => [
            'label' => 'Heeft een andere overheid hier al een oplossing voor?',
            'hint' => 'Die kun je soms hergebruiken. Past open source even goed? Dan heeft dat de voorkeur van het '
                . 'Rijk. Kijk ook op developer.overheid.nl.',
        ],
    ];
}

/** Een plek uit bc_checks() als vraag met toelichting. @return list<array<string,mixed>> */
function bc_check(string $key): array
{
    $check = bc_checks()[$key];

    return [
        bc_keuze($key, $check['label'], BC_CHECK_OPTIES, $check['hint']),
        bc_uitleg($key . '_toelichting', 'Wat heb je bekeken? En waarom past het wel of niet?'),
    ];
}

/**
 * De diensten van de organisatie uit config.php, gegroepeerd per taak.
 *
 * @param array{taken:array<string,array{taak:string,diensten:array<string,string>}>} $org
 * @return array<string,array{label:string,groep:string}>
 */
function bc_taken(array $org): array
{
    $opties = [];

    foreach ($org['taken'] as $taak) {
        foreach ($taak['diensten'] as $key => $dienst) {
            $opties[$key] = ['label' => $dienst, 'groep' => $taak['taak']];
        }
    }

    return $opties + ['geen' => ['label' => 'Bij geen van deze diensten', 'groep' => 'Anders']];
}

// ---------------------------------------------------------------------------

function bc_sectie_over(): array
{
    return [
        'id' => 'over',
        'titel' => 'Over dit project',
        'intro' => 'Dit document is een advies aan het MT. Het laat zien of het project nodig is, en hoeveel risico '
            . 'het heeft. Daarna besluit het MT of het project verder mag.',
        'vragen' => [
            bc_vraag('projectnaam', 'Hoe heet het project?', 'Deze naam komt overal in het document terug, en in de '
                . 'bestandsnaam.', ['type' => 'text']),
            bc_vraag('aanvrager', 'Wie vult dit in?', 'Naam en functie.', ['type' => 'text']),
            bc_vraag('organisatieonderdeel', 'Welke afdeling vraagt het project aan?', '', ['type' => 'text']),
            bc_vraag('email', 'Op welk mailadres ben je bereikbaar?', '', [
                'type' => 'email', 'verplicht' => false, 'optioneel' => true,
            ]),
            bc_vraag('versie', 'Welk versienummer krijgt dit document?', '', [
                'type' => 'text', 'verplicht' => false, 'optioneel' => true, 'standaard' => '0.1',
            ]),
        ],
    ];
}

// ---------------------------------------------------------------------------
// Toets 1: is het nodig, en nu?

function bc_sectie_nodig(): array
{
    return bc_toets(1, 'nodig', 'Is het nodig, en waarom nu?', 'Niets doen is altijd een keuze. Dit project moet '
        . 'dus beter zijn dan niets doen.', [
        bc_vraag('proces_omschrijving', 'Om welk werk gaat het?', 'Beschrijf het werk in gewone taal. Wat gebeurt '
            . 'er, van begin tot eind?'),
        bc_vraag('bc_probleem', 'Wat gaat er nu mis? En hoe weet je dat?', 'Beschrijf het probleem, en nog niet de '
            . 'oplossing. Noem feiten of cijfers, zoals klachten, wachttijden, fouten of een audit.', ['hoogte' => 4]),
        bc_keuze('bc_burger_merkt', 'Merkt de burger iets van dit project?', [
            'direct' => [
                'label' => 'Ja, de burger gebruikt het zelf',
                'hint' => 'Bijvoorbeeld een website, een app of een formulier.',
            ],
            'dienst' => [
                'label' => 'Ja, aan onze dienstverlening',
                'hint' => 'Bijvoorbeeld doordat een aanvraag sneller klaar is, of doordat er minder fouten in zitten.',
            ],
            'indirect' => [
                'label' => 'Alleen indirect',
                'hint' => 'Het werk van de organisatie wordt beter of goedkoper. De burger merkt dat niet meteen.',
            ],
            'niet' => ['label' => 'Nee'],
        ], 'Met de burger bedoelen we hier ook bedrijven en instellingen.'),
        bc_vraag('bc_burger_waarde', 'Hoe helpt dit de burger?', 'Schrijf het op zoals de burger het zou zeggen. '
            . 'Merkt de burger niets? Leg dan uit waarom het project toch nodig is.'),
        bc_keuze('bc_plicht', 'Is er een plicht die jullie kunnen aantonen?', [
            'ja' => ['label' => 'Ja', 'niveau' => 'l'],
            'nee' => ['label' => 'Nee', 'niveau' => 'h'],
            'niet_gekeken' => ['label' => 'Nog niet uitgezocht', 'niveau' => 'm'],
        ], 'Bijvoorbeeld een wet, de BIO2, de Cyberbeveiligingswet of een andere bindende regel. Een bevinding uit '
            . 'een audit of een risico voor de continuïteit telt ook.'),
        bc_uitleg('bc_plicht_toelichting', 'Welke plicht of welk risico? Noem het artikel of de bevinding.', [
            'toon_als' => ['bc_plicht' => ['ja']],
        ]),
        bc_keuze('bc_schade', 'Kost niets doen meer dan dit project?', [
            'ja' => ['label' => 'Ja', 'niveau' => 'l'],
            'nee' => ['label' => 'Nee', 'niveau' => 'h'],
            'niet_gekeken' => ['label' => 'Nog niet uitgezocht', 'niveau' => 'm'],
        ], 'Vergelijk met jullie eigen schatting van de kosten. Tel ook mee wat niet in geld uit te drukken is, zoals '
            . 'de rechten van de burger.'),
        bc_uitleg('bc_alt_niets', 'Wat gebeurt er als jullie niets doen? En wat kost dat, en voor wie?'),
        bc_keuze('bc_nu', 'Moet het nu beginnen?', [
            'ja' => ['label' => 'Ja, wachten maakt het erger', 'niveau' => 'l'],
            'nee' => [
                'label' => 'Nee, het kan wachten',
                'hint' => 'Bijvoorbeeld omdat een nieuwe wet of een landelijke oplossing eraan komt.',
                'niveau' => 'm',
            ],
        ]),
        bc_uitleg('bc_waarom_nu', 'Waarom?'),
    ], [
        'l' => ['Er is een plicht, of niets doen kost meer dan het project. En het moet nu.'],
        'm' => ['Het is nog niet duidelijk of het project nodig is, of het kan wachten. Zoek uit wat niets doen kost, '
            . 'en of er binnenkort iets komt wat het probleem oplost.'],
        'h' => ['Er is geen plicht, en niets doen kost minder dan het project. Het advies is: doe nu niets, en kijk '
            . 'later opnieuw.'],
    ], ['bc_plicht', 'bc_schade']);
}

// ---------------------------------------------------------------------------
// Toets 2: kan het zonder IT?

function bc_sectie_zonder_it(): array
{
    return bc_toets(2, 'zonder_it', 'Kan het zonder nieuw systeem?', 'Een systeem lost niet elk probleem op. Soms '
        . 'zit het probleem in het werk zelf.', [
        bc_keuze('bc_alt_werkwijze', 'Kan het probleem ook zonder nieuw systeem worden opgelost?', [
            'ja' => ['label' => 'Ja, en dat is genoeg', 'niveau' => 'h'],
            'deels' => ['label' => 'Deels, maar het is niet genoeg', 'niveau' => 'l'],
            'nee' => ['label' => 'Nee', 'niveau' => 'l'],
            'niet_gekeken' => ['label' => 'Nog niet uitgezocht', 'niveau' => 'm'],
        ], 'Kijk eerst naar het werk. Is het geanalyseerd? Helpen duidelijke afspraken over wie wat beslist, een '
            . 'andere volgorde, een training of betere controle bij de invoer?'),
        bc_uitleg('bc_alt_werkwijze_waarom', 'Wat hebben jullie bekeken of geprobeerd? Noem ook de analyse van het werk.'),
    ], [
        'l' => ['Een andere werkwijze lost het probleem niet op. Een systeem kan helpen.'],
        'm' => ['Het is nog niet uitgezocht of het ook zonder nieuw systeem kan. Kijk eerst naar het werk: afspraken, '
            . 'mandaten, opleiding en de kwaliteit van de invoer.'],
        'h' => ['Een andere werkwijze is genoeg. Verbeter eerst het werk. Dan is een nieuw systeem niet nodig.'],
    ]);
}

// ---------------------------------------------------------------------------
// Toets 3: wat is er al?

function bc_sectie_bestaand(): array
{
    return bc_toets(3, 'bestaand', 'Wat is er al?', 'Kijk eerst wat er al is, voor je iets nieuws koopt. Zeg bij '
        . 'elke vraag wat je hebt bekeken.', [
        ...bc_check('bc_check_eigen'),
        bc_keuze('bc_domeinarchitectuur', 'Waar staat dit in de domeinarchitectuur?', [
            'staat' => ['label' => 'Het staat erin', 'niveau' => 'l'],
            'past' => ['label' => 'Het staat er nog niet in, maar het past erin', 'niveau' => 'm'],
            'past_niet' => ['label' => 'Het past er niet in', 'niveau' => 'h'],
            'niet_gekeken' => ['label' => 'Nog niet uitgezocht', 'niveau' => 'm'],
        ], 'Zoek op in welk domein dit hoort, en welke bouwsteen het is. Past het bij de systemen, de gegevens en de '
            . 'koppelingen die er al zijn?'),
        bc_uitleg('bc_domeinarchitectuur_toelichting', 'Welk domein en welke bouwsteen? Of waarom past het niet?'),
    ], [
        'l' => ['Wat er al is, past niet. En de oplossing staat in de domeinarchitectuur.'],
        'm' => ['Nog niet alles is uitgezocht, of de domeinarchitectuur moet nog worden aangevuld. Doe dat voor het '
            . 'besluit.'],
        'h' => ['Er is al een systeem dat dit kan, of de oplossing past niet in de domeinarchitectuur. Richt eerst in '
            . 'wat er al is.'],
    ]);
}

// ---------------------------------------------------------------------------
// Toets 4: is het onze taak?

function bc_sectie_taak(): array
{
    $org = cm_config()['organisatie'] ?? null;

    return bc_toets(4, 'taak', 'Is het jullie taak?', 'Doe niet wat een ander al doet, of wat bij een ander hoort.', [
        bc_keuze('bc_mandaat', 'Hoort dit bij de taak van jullie organisatie?', [
            'ja' => ['label' => 'Ja', 'niveau' => 'l'],
            'deels' => ['label' => 'Deels, het raakt ook de taak van een ketenpartner', 'niveau' => 'm'],
            'nee' => ['label' => 'Nee', 'niveau' => 'h'],
        ], $org === null ? 'Kijk naar het besluit of de wet die jullie taak regelt.'
            : 'De taken van ' . $org['naam'] . ' staan in ' . $org['besluit'] . '.'),
        ...($org === null ? [] : [bc_keuze('bc_taken', 'Bij welke taak hoort het?', bc_taken($org), 'Kies de diensten '
            . 'waar het bij hoort. Ze volgen uit de taken in het besluit.', [
            'type' => 'checkbox', 'toon_als' => ['bc_mandaat' => ['ja', 'deels']],
        ])]),
        bc_uitleg('bc_mandaat_toelichting', $org === null ? 'Welk besluit of welke wet? En welke ketenpartner?'
            : 'Licht toe. Welke ketenpartner, als die er is?'),
        ...bc_check('bc_check_rijk'),
        ...bc_check('bc_check_hergebruik'),
    ], [
        'l' => ['Het hoort bij jullie taak. En er is geen oplossing van het Rijk of van een andere overheid.'],
        'm' => ['Het raakt ook een ketenpartner, of nog niet alles is uitgezocht. Overleg eerst met de ketenpartner, '
            . 'en kijk wat er landelijk al is.'],
        'h' => ['Het is niet jullie taak, of een andere overheid heeft er al iets voor. Sluit daarbij aan, of laat het '
            . 'over aan de partner die erover gaat.'],
    ]);
}

// ---------------------------------------------------------------------------
// Toets 5: kunnen jullie het dragen?

function bc_sectie_dragen(): array
{
    return bc_toets(5, 'dragen', 'Kunnen jullie het dragen?', 'Na de oplevering begint het pas. Een systeem heeft '
        . 'elk jaar een eigenaar, geld en beheer nodig, en op een dag ook een einde.', [
        bc_keuze('bc_eigenaar', 'Is er een eigenaar in de lijn?', [
            'ja' => ['label' => 'Ja', 'niveau' => 'l'],
            'nee' => ['label' => 'Nog niet', 'niveau' => 'm'],
        ], 'Iemand in de vaste organisatie die na de oplevering zorgt dat het wordt gebruikt, en erop stuurt.'),
        bc_uitleg('bc_eigenaar_toelichting', 'Wie? Naam en functie.', ['toon_als' => ['bc_eigenaar' => ['ja']]]),
        bc_keuze('bc_budget', 'Is er geld voor?', [
            'ja' => ['label' => 'Ja, voor de aanschaf en voor elk jaar daarna', 'niveau' => 'l'],
            'eenmalig' => ['label' => 'Alleen voor de aanschaf', 'niveau' => 'h'],
            'nee' => ['label' => 'Nog niet', 'niveau' => 'm'],
        ], 'Denk ook aan de mensen voor het beheer, elk jaar.'),
        bc_keuze('bc_beheer_akkoord', 'Kan het beheer dit dragen?', [
            'ja' => ['label' => 'Ja, dat hebben zij op papier gezet', 'niveau' => 'l'],
            'gevraagd' => ['label' => 'Gevraagd, maar nog geen antwoord', 'niveau' => 'm'],
            'nee' => ['label' => 'Nee', 'niveau' => 'h'],
            'niet_gekeken' => ['label' => 'Nog niet gevraagd', 'niveau' => 'm'],
        ], 'Vraag het aan de beheerorganisatie en aan de CISO. Kunnen zij het ondersteunen, en ook veilig houden '
            . 'volgens de BIO2?'),
        bc_uitleg('bc_beheer', 'Wie beheert het, en hoeveel mensen kost dat ongeveer?'),
        bc_keuze('bc_kennis', 'Hebben jullie genoeg kennis en mensen om dit in te voeren?', [
            'ja' => ['label' => 'Ja', 'niveau' => 'l'],
            'deels' => ['label' => 'Deels', 'niveau' => 'm'],
            'nee' => ['label' => 'Nee', 'niveau' => 'm'],
        ]),
        bc_uitleg('bc_kennis_toelichting', 'Wat ontbreekt er, en hoe lossen jullie dat op?', [
            'toon_als' => ['bc_kennis' => ['deels', 'nee']],
        ]),
        bc_keuze('bc_exit', 'Kunnen jullie later weer stoppen, of overstappen naar iets anders?', [
            'ja' => ['label' => 'Ja', 'niveau' => 'l'],
            'moeilijk' => ['label' => 'Dat wordt moeilijk', 'niveau' => 'm'],
            'weet_niet' => ['label' => 'Dat weten we nog niet', 'niveau' => 'm'],
        ], 'Ook als de oplossing over een paar jaar niet meer nodig is. Kunnen jullie de gegevens dan meenemen, in '
            . 'een open formaat? En wat kost het om te stoppen?'),
    ], [
        'l' => ['Er is een eigenaar, er is geld voor elk jaar, en het beheer kan het dragen. Stoppen kan ook.'],
        'm' => ['Nog niet alles is geregeld: de eigenaar, het geld, het beheer, de kennis of het stoppen. Regel dat '
            . 'voor de start.'],
        'h' => ['Er is alleen geld voor de aanschaf, of het beheer kan het niet dragen. Begin dan niet. Anders staat '
            . 'er straks een systeem dat niemand onderhoudt.'],
    ]);
}

// ---------------------------------------------------------------------------
// Het voorstel

function bc_sectie_voorstel(): array
{
    return [
        'id' => 'voorstel',
        'titel' => 'Wat stellen jullie voor?',
        'stap' => 6,
        'intro' => 'Een schatting is genoeg. De bedragen hoeven nog niet precies te kloppen.',
        'vragen' => [
            bc_vraag('bc_doel', 'Wat is er straks beter?', 'Noem liefst iets wat je kunt meten, zoals een kortere '
                . 'wachttijd of minder fouten.'),
            bc_vraag('bc_oplossing', 'Welke oplossing kiezen jullie?', 'Beschrijf de oplossing in gewone taal. Noem '
                . 'nog geen merk of leverancier, want wie het levert, volgt uit de inkoop.', ['hoogte' => 4]),
            bc_vraag('bc_waarom_deze', 'Waarom deze oplossing?', 'Welke andere oplossingen hebben jullie bekeken, '
                . 'zoals zelf bouwen? En waarom is deze beter?'),
            bc_vraag('bc_kosten_eenmalig', 'Wat kost het eenmalig?', 'Aanschaf, inrichten, gegevens overzetten en '
                . 'opleiden.', ['type' => 'text']),
            bc_vraag('bc_kosten_jaarlijks', 'Wat kost het per jaar, en hoeveel jaar?', 'Licenties, beheer, hosting en '
                . 'de uren van eigen mensen.', ['type' => 'text']),
            bc_keuze('bc_omvang', 'Wat kost het project in totaal, over de hele looptijd?', [
                'klein' => ['label' => 'Minder dan 1 miljoen euro'],
                'middel' => ['label' => 'Tussen 1 en 5 miljoen euro'],
                'groot' => ['label' => 'Meer dan 5 miljoen euro'],
            ], 'Tel alles mee, ook de uren van eigen mensen. Boven 5 miljoen euro gelden bij het Rijk extra regels.'),
            bc_vraag('bc_baten', 'Wat levert het op?', 'In geld, in tijd of in minder fouten. Zeg ook voor wie: de '
                . 'burger, de medewerker of de organisatie.'),
            bc_keuze('inkoopvorm', 'Wat voor inkoop wordt dit?', [
                'geen' => ['label' => 'Geen inkoop', 'hint' => 'We bouwen of gebruiken dit zelf.'],
                'enkele' => ['label' => 'Enkele opdracht'],
                'nadere' => ['label' => 'Nadere opdracht', 'hint' => 'Onder een raamcontract dat er al is.'],
                'raamcontract' => ['label' => 'Een nieuw raamcontract'],
            ], 'Het Rijk koopt veel samen in. Vraag je inkoopadviseur of er al een raamcontract is.'),
            bc_keuze('bc_opdrachtwaarde', 'Hoeveel betalen jullie de leverancier, over de hele looptijd?', [
                'onder' => ['label' => 'Minder dan 140.000 euro'],
                'boven' => ['label' => '140.000 euro of meer'],
                'onbekend' => ['label' => 'Dat weten we nog niet'],
            ], 'Zonder btw, en met alle verlengingen. Bij een nieuw raamcontract tel je alle opdrachten eronder bij '
                . 'elkaar. Dit bedrag bepaalt hoe jullie moeten inkopen.', [
                'toon_als' => ['inkoopvorm' => ['enkele', 'raamcontract']],
            ]),
            [
                'key' => 'bc_afgeleid_inkoop',
                'type' => 'afgeleid',
                'bron' => 'inkoop',
                'label' => 'Hoe kopen jullie dit in?',
                'hint' => 'Volgt uit de soort inkoop en uit het bedrag.',
                'groepen' => BC_INKOOP,
            ],
        ],
    ];
}
