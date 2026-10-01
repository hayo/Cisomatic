<?php

/**
 * De vragenlijst. Eerst het kanaal en de situatie. Een nieuw of bestaand kanaal
 * gaat door de vier stappen van de rijksbrede werkwijze: doel en doelgroep,
 * kanaalstrategie, weging, en comply or explain. Bij toetreding tot een nieuw
 * platform komt het vooronderzoek erbij. Een vertrek heeft eigen secties, in
 * exit.php. Eén scan gaat over één kanaal.
 */

declare(strict_types=1);

/** De maatregelen, met wat er te regelen is als een maatregel ontbreekt. */
const SM_MAATREGELEN = [
    'mfa' => [
        'label' => 'Inloggen in twee stappen (MFA) voor elke beheerder',
        'regel' => 'Zet voor elke beheerder inloggen in twee stappen (MFA) aan.',
    ],
    'eigen_inlog' => [
        'label' => 'Een eigen inlog voor elke beheerder',
        'hint' => 'Geen gedeeld wachtwoord.',
        'regel' => 'Geef elke beheerder een eigen inlog. Deel nooit een wachtwoord.',
    ],
    'archief' => [
        'label' => 'Een afspraak over archiveren met de informatiebeheerder',
        'regel' => 'Maak met je informatiebeheerder een afspraak over archiveren. Berichten, reacties en '
            . 'privéberichten vallen onder de Archiefwet en de Woo.',
    ],
    'moderatie' => [
        'label' => 'Een afspraak over het modereren van reacties',
        'regel' => 'Spreek af wie de reacties leest, en wanneer je een reactie verbergt of weghaalt.',
    ],
    'vermeld' => [
        'label' => 'Het account staat op onze eigen site',
        'hint' => 'Zo zien mensen dat het account echt is.',
        'regel' => 'Zet het account op je eigen site, zodat mensen zien dat het echt is.',
    ],
    'dpia' => [
        'label' => 'Een DPIA',
        'hint' => 'Of een toets met de privacy officer of die nodig is.',
        'regel' => 'Toets met je privacy officer of een DPIA nodig is.',
    ],
];

/** Waar in de cyclus het kanaal staat. Een nieuw of bestaand kanaal gaat door de vier stappen, een vertrek niet. */
const SM_SITUATIES = [
    'nieuw' => ['label' => 'Een nieuw kanaal starten'],
    'bestaand' => [
        'label' => 'Een bestaand kanaal toetsen',
        'hint' => 'Bijvoorbeeld bij de jaarlijkse toets door de bestuursraad.',
    ],
    'exit' => ['label' => 'Van een kanaal vertrekken', 'hint' => 'Je overweegt het, of het is al besloten.'],
];

/** De vier stappen gelden voor een nieuw of bestaand kanaal. */
const SM_IN_GEBRUIK = ['sm_situatie' => ['nieuw', 'bestaand']];

/** De toetreding: een nieuw kanaal op een platform waar de organisatie nog niet zit. */
const SM_TOETREDING = ['sm_situatie' => ['nieuw'], 'sm_al_actief' => ['nee']];

/** @return list<array<string,mixed>> */
function sm_secties(): array
{
    return [
        sm_sectie_kanaal(),
        sm_sectie_doel(),
        sm_sectie_strategie(),
        sm_sectie_weging(),
        sm_sectie_toetreding(),
        sm_sectie_besluit(),
        ...sm_secties_exit(),
        sm_sectie_vaststellen(),
    ];
}

function sm_sectie_kanaal(): array
{
    $platforms = [];
    foreach (sm_kaders() as $k => $kader) {
        $platforms[$k] = [
            'label' => $kader['label'] ?? $kader['naam'],
            'hint' => trim(($kader['hint'] ?? '') . ' ' . SM_INZET[$kader['inzet']]['label'] . '.'),
        ];
    }

    return [
        'id' => 'kanaal',
        'titel' => 'Het kanaal',
        'stap' => 1,
        'intro' => 'Eén scan gaat over één kanaal. Heb je een Facebookpagina en een account op Instagram? Dan doe je '
            . 'twee scans.',
        'vragen' => [
            [
                'key' => 'sm_situatie',
                'type' => 'radio',
                'label' => 'Wat wil je doen?',
                'verplicht' => true,
                'opties' => SM_SITUATIES,
            ],
            [
                'key' => 'sm_platform',
                'type' => 'radio',
                'label' => 'Op welk platform zit het kanaal?',
                'verplicht' => true,
                'opties' => $platforms,
                'groepen' => sm_regelgegevens(),
            ],
            [
                'key' => 'sm_anders_naam',
                'type' => 'text',
                'label' => 'Welk platform is het?',
                'verplicht_als' => ['sm_platform' => ['anders']],
                'toon_als' => ['sm_platform' => ['anders']],
            ],
            [
                'key' => 'sm_anders_land',
                'type' => 'radio',
                'label' => 'Waar zit het bedrijf achter het platform?',
                'hint' => 'Kijk ook waar de servers staan.',
                'verplicht_als' => ['sm_platform' => ['anders']],
                'toon_als' => ['sm_platform' => ['anders']],
                'opties' => [
                    'eu' => ['label' => 'In de EU'],
                    'buiten' => ['label' => 'Buiten de EU'],
                    'offensief' => [
                        'label' => 'In een land met een offensief cyberprogramma tegen Nederland',
                        'hint' => 'Zoals China, Rusland of Iran. Dan gelden dezelfde regels als voor TikTok.',
                    ],
                ],
            ],
            [
                'key' => 'sm_kader',
                'type' => 'afgeleid',
                'bron' => 'kader',
                'label' => 'Het rijksbrede kader:',
            ],
            [
                'key' => 'sm_al_actief',
                'type' => 'radio',
                'label' => 'Is je organisatie al actief op dit platform?',
                'hint' => 'Met een ander account. Zo niet, dan treed je toe tot een nieuw platform, en dan geldt ook de '
                    . 'richtlijn voor toetreding.',
                'verplicht_als' => ['sm_situatie' => ['nieuw']],
                'toon_als' => ['sm_situatie' => ['nieuw']],
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nee' => ['label' => 'Nee, dit is ons eerste account op dit platform'],
                ],
            ],
            [
                'key' => 'sm_exit_scope',
                'type' => 'radio',
                'label' => 'Vertrek je van het hele platform, of sluit je alleen dit account?',
                'verplicht_als' => ['sm_situatie' => ['exit']],
                'toon_als' => ['sm_situatie' => ['exit']],
                'opties' => [
                    'platform' => [
                        'label' => 'Van het hele platform',
                        'hint' => 'Dan geldt de richtlijn voor vertrek, en stem je af met de Voorlichtingsraad.',
                    ],
                    'account' => ['label' => 'Alleen dit account'],
                ],
            ],
            [
                'key' => 'sm_accounttype',
                'type' => 'radio',
                'label' => 'Van wie is het account?',
                'verplicht' => true,
                'opties' => [
                    'organisatie' => ['label' => 'Van de organisatie'],
                    'bewindspersoon' => [
                        'label' => 'Van een bewindspersoon',
                        'hint' => 'Een corporate account met ambtelijke ondersteuning. Het departement is de eigenaar.',
                    ],
                ],
            ],
            [
                'key' => 'projectnaam',
                'type' => 'text',
                'label' => 'Om welk account gaat het?',
                'hint' => 'Bijvoorbeeld "Facebookpagina van de Belastingdienst". De naam komt terug in de '
                    . 'bestandsnaam.',
                'verplicht' => true,
            ],
            [
                'key' => 'aanvrager',
                'type' => 'text',
                'label' => 'Wie vult deze scan in?',
                'hint' => 'Naam en functie.',
                'verplicht' => true,
            ],
            [
                'key' => 'organisatieonderdeel',
                'type' => 'text',
                'label' => 'Voor welke organisatie is dit?',
                'optioneel' => true,
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

function sm_sectie_doel(): array
{
    return [
        'id' => 'doel',
        'toon_als' => SM_IN_GEBRUIK,
        'titel' => 'Doel en doelgroep',
        'stap' => 2,
        'intro' => 'Bereik je met dit kanaal je doelen, binnen de ruimte van het kader? En zit je doelgroep er echt?',
        'vragen' => [
            [
                'key' => 'sm_doelgroep',
                'type' => 'textarea',
                'label' => 'Wie wil je bereiken?',
                'hint' => 'Beschrijf de doelgroep zo precies als je kunt.',
                'verplicht' => true,
                'hoogte' => 2,
            ],
            [
                'key' => 'sm_bereik',
                'type' => 'radio',
                'label' => 'Bereik je die doelgroep aantoonbaar op dit platform?',
                'hint' => 'Kijk naar statistieken of onderzoek. Is het kanaal nieuw? Geef dan je verwachting.',
                'verplicht' => true,
                'opties' => [
                    'aantoonbaar' => ['label' => 'Ja, dat blijkt uit cijfers'],
                    'verwachting' => ['label' => 'Waarschijnlijk, maar het is nog niet gemeten'],
                    'nee' => ['label' => 'Nee', 'hint' => 'Dan dient het kanaal het doel niet.'],
                ],
            ],
            [
                'key' => 'sm_gebruik',
                'type' => 'checkbox',
                'label' => 'Hoe gebruik je het kanaal?',
                'hint' => 'Kruis alles aan wat past. Per soort gebruik geldt een eigen ruimte in het kader.',
                'verplicht' => true,
                'opties' => SM_GEBRUIK,
            ],
            ...sm_vragen_soort('organisch', 'Berichten plaatsen', [
                ['key' => 'sm_doelen', 'label' => 'Welke doelen hebben je berichten?', 'opties' => SM_DOELEN],
                ['key' => 'sm_vormen', 'label' => 'Welke vormen gebruik je?', 'opties' => SM_VORMEN],
            ]),
            ...sm_vragen_soort('betaald', 'Adverteren', [
                ['key' => 'sm_betaald', 'label' => 'Waarvoor adverteer je?', 'opties' => SM_BETAALD],
            ]),
            [
                'key' => 'sm_doelgericht',
                'type' => 'radio',
                'label' => 'Hoe bepaal je wie een advertentie ziet?',
                'verplicht_als' => ['sm_gebruik' => ['betaald']],
                'toon_als' => ['sm_gebruik' => ['betaald']],
                'opties' => [
                    'breed' => ['label' => 'Breed: op leeftijd, regio of taal'],
                    'profiel' => [
                        'label' => 'Op interesses of gedrag',
                        'hint' => 'Dan gebruik je de profielen die het platform van mensen maakt.',
                    ],
                ],
            ],
            ...sm_vragen_soort('contact', 'Vragen beantwoorden', [
                ['key' => 'sm_contactvormen', 'label' => 'Welk contact heb je met mensen?', 'opties' => SM_CONTACT],
            ]),
            [
                'key' => 'sm_toets_doel',
                'type' => 'afgeleid',
                'bron' => 'doel',
                'label' => 'Past dit binnen het kader?',
            ],
        ],
    ];
}

function sm_sectie_strategie(): array
{
    return [
        'id' => 'strategie',
        'toon_als' => SM_IN_GEBRUIK,
        'titel' => 'Kanaalstrategie',
        'stap' => 3,
        'intro' => 'Past dit platform bij het doel? En is er een minder risicovol kanaal dat hetzelfde bereikt?',
        'vragen' => [
            [
                'key' => 'sm_alternatief',
                'type' => 'radio',
                'label' => 'Is er een minder risicovol kanaal dat hetzelfde doel bereikt?',
                'hint' => 'Zoals je eigen site, een nieuwsbrief, social.overheid.nl, of een platform dat in beginsel '
                    . 'inzetbaar is.',
                'verplicht' => true,
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'deels' => ['label' => 'Deels, maar dat is niet genoeg'],
                    'ja' => ['label' => 'Ja', 'hint' => 'Dan vraagt het kader om terughoudendheid. Gebruik dat kanaal.'],
                ],
            ],
            [
                'key' => 'sm_alternatief_toelichting',
                'type' => 'textarea',
                'label' => 'Welke andere kanalen heb je bekeken? En wat bereiken ze wel of niet?',
                'verplicht' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'sm_eigen_kanalen',
                'type' => 'radio',
                'label' => 'Staat alles wat je hier plaatst eerst op je eigen openbare kanalen?',
                'hint' => 'Zoals je eigen site of social.overheid.nl. Dit kanaal komt erbij, niet in plaats daarvan.',
                'verplicht_als' => ['sm_platform' => sm_platforms_behalve('mastodon')],
                'toon_als' => ['sm_platform' => sm_platforms_behalve('mastodon')],
                'opties' => [
                    'ja' => ['label' => 'Ja, alles'],
                    'deels' => ['label' => 'Voor een deel'],
                    'nee' => ['label' => 'Nee'],
                ],
            ],
        ],
    ];
}

function sm_sectie_weging(): array
{
    return [
        'id' => 'weging',
        'toon_als' => SM_IN_GEBRUIK,
        'titel' => 'Weging',
        'stap' => 4,
        'intro' => 'Weegt de meerwaarde op tegen de risico’s uit het kader? En hoe beperk je die risico’s?',
        'vragen' => [
            [
                'key' => 'sm_risicos',
                'type' => 'afgeleid',
                'bron' => 'risicos',
                'label' => 'Risico’s volgens het kader:',
            ],
            [
                'key' => 'sm_doorverwijzen',
                'type' => 'radio',
                'label' => 'Wijs je mensen op de risico’s van contact via dit platform, en verwijs je door?',
                'hint' => 'Naar een kanaal van de overheid. Het kader vraagt dat ook als je zelf geen vragen '
                    . 'beantwoordt, want mensen sturen toch berichten. Op social.overheid.nl geldt het voor vragen met '
                    . 'privacygevoelige gegevens.',
                'verplicht' => true,
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nee' => ['label' => 'Nee'],
                ],
            ],
            [
                'key' => 'sm_toestel',
                'type' => 'radio',
                'label' => 'Vanaf welk toestel beheer je het account?',
                'verplicht_als_uitkomst' => ['toestel' => ['ja']],
                'toon_als_uitkomst' => ['toestel' => ['ja']],
                'opties' => [
                    'los' => ['label' => 'Vanaf een los toestel, alleen voor dit account'],
                    'rijk' => ['label' => 'Vanaf een toestel van het Rijk', 'hint' => 'Dat mag niet.'],
                    'prive' => ['label' => 'Vanaf een privétoestel', 'hint' => 'Dan ligt het risico bij de medewerker.'],
                ],
            ],
            [
                'key' => 'sm_maatregelen',
                'type' => 'checkbox',
                'label' => 'Welke maatregelen zijn er al?',
                'hint' => 'Wat ontbreekt, zet de tool in het document als iets om te regelen.',
                'opties' => SM_MAATREGELEN,
            ],
            [
                'key' => 'sm_weging',
                'type' => 'radio',
                'label' => 'Weegt de meerwaarde op tegen de risico’s?',
                'verplicht' => true,
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nee' => ['label' => 'Nee'],
                ],
            ],
            [
                'key' => 'sm_weging_toelichting',
                'type' => 'textarea',
                'label' => 'Waarom?',
                'hint' => 'Noem de meerwaarde, en de risico’s uit het kader die voor jullie zwaar wegen.',
                'verplicht' => true,
                'hoogte' => 3,
            ],
        ],
    ];
}

function sm_sectie_besluit(): array
{
    $explain = ['uitkomst' => ['explain']];

    return [
        'id' => 'besluit',
        'titel' => 'Comply or explain',
        'stap' => 6,
        'toon_als' => SM_IN_GEBRUIK,
        'intro' => 'Past de inzet binnen de ruimte van het kader, dan is het comply. Ga je verder, dan werk je een '
            . 'explain uit.',
        'vragen' => [
            [
                'key' => 'sm_uitkomst',
                'type' => 'afgeleid',
                'bron' => 'uitkomst',
                'label' => 'Uitkomst:',
            ],
            [
                'key' => 'sm_afwijking',
                'type' => 'textarea',
                'label' => 'Waarvan wijk je af?',
                'hint' => 'De tool vult in wat buiten het kader valt. Vul aan waar nodig.',
                'motivatie_voor' => 'afwijking',
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 4,
            ],
            [
                'key' => 'sm_noodzaak',
                'type' => 'textarea',
                'label' => 'Waarom is deze inzet noodzakelijk?',
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 3,
            ],
            [
                'key' => 'sm_beheersing',
                'type' => 'textarea',
                'label' => 'Wat doe je nog meer om de risico’s te beheersen?',
                'hint' => 'De maatregelen uit de weging staan al in het document.',
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 3,
            ],
        ],
    ];
}

/** Het vooronderzoek en het normenkader uit de richtlijn voor toetreding. */
function sm_sectie_toetreding(): array
{
    $anders = ['sm_platform' => ['anders']];

    return [
        'id' => 'toetreding',
        'titel' => 'Vooronderzoek',
        'stap' => 5,
        'toon_als' => SM_TOETREDING,
        'intro' => 'Je treedt toe tot een nieuw platform. Dan vraagt de richtlijn voor toetreding een paar dingen extra.',
        'vragen' => [
            [
                'key' => 'sm_belang_burger',
                'type' => 'textarea',
                'label' => 'Hoe dient dit kanaal het belang van de burger?',
                'verplicht' => true,
                'hoogte' => 2,
            ],
            [
                'key' => 'sm_kosten',
                'type' => 'textarea',
                'label' => 'Wat kost het kanaal, in uren en geld?',
                'hint' => 'Vergelijk het met de kosten van andere platforms.',
                'verplicht' => true,
                'hoogte' => 2,
            ],
            [
                'key' => 'sm_markt_open',
                'type' => 'radio',
                'label' => 'Hoe is het platform opgezet?',
                'verplicht_als' => $anders,
                'toon_als' => $anders,
                'opties' => [
                    'open' => [
                        'label' => 'Open en decentraal',
                        'hint' => 'Je kiest zelf een server, en het werkt samen met andere platforms. Zoals Mastodon.',
                    ],
                    'centraal' => [
                        'label' => 'Eén bedrijf beheert alles',
                        'hint' => 'Dan is het platform gevoeliger voor censuur en voor de macht van één bedrijf.',
                    ],
                ],
            ],
            [
                'key' => 'sm_markt_archief',
                'type' => 'radio',
                'label' => 'Kun je berichten van het platform archiveren?',
                'verplicht_als' => $anders,
                'toon_als' => $anders,
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nee' => ['label' => 'Nee, of dat is nog niet bekend'],
                ],
            ],
            [
                'key' => 'sm_markt_partners',
                'type' => 'textarea',
                'label' => 'Welke andere overheden en partners zitten al op het platform?',
                'optioneel' => true,
                'hoogte' => 2,
            ],
            [
                'key' => 'sm_start',
                'type' => 'radio',
                'label' => 'Hoe begin je?',
                'verplicht' => true,
                'opties' => [
                    'pilot' => [
                        'label' => 'Met een pilot van drie tot zes maanden',
                        'hint' => 'Aangeraden. Kies één of twee thema’s of campagnes.',
                    ],
                    'structureel' => ['label' => 'Meteen structureel'],
                ],
            ],
            [
                'key' => 'sm_exitstrategie',
                'type' => 'textarea',
                'label' => 'Wat is je exitstrategie?',
                'hint' => 'Wanneer stop je, en hoe? Het besluit legt dat vast, samen met de momenten van evaluatie.',
                'verplicht' => true,
                'hoogte' => 3,
            ],
        ],
    ];
}

/** Wie adviseerde, en wie het vaststelt. Voor elke situatie. */
function sm_sectie_vaststellen(): array
{
    return [
        'id' => 'vaststellen',
        'titel' => 'Vaststellen',
        'stap' => 10,
        'intro' => 'De directeur Communicatie beslist, na advies. Bij twijfel beslist die samen met de '
            . 'Voorlichtingsraad, de SG of de bewindspersoon.',
        'vragen' => [
            [
                'key' => 'sm_adviseurs',
                'type' => 'checkbox',
                'label' => 'Wie gaf advies?',
                'opties' => [
                    'cpo' => ['label' => 'De CPO of privacy officer'],
                    'ciso' => ['label' => 'De CISO', 'hint' => 'Met een risicoanalyse, als dat kan.'],
                    'cio' => ['label' => 'De CIO'],
                    'beleid' => ['label' => 'Een beleidsadviseur'],
                ],
            ],
            [
                'key' => 'sm_directeur',
                'type' => 'text',
                'label' => 'Wie stelt dit vast?',
                'hint' => 'De directeur Communicatie van je organisatie, of wie daarvoor gemandateerd is.',
            ],
        ],
    ];
}

/**
 * Een kop en de keuzevragen van één soort gebruik, alleen als die soort gekozen is.
 *
 * @param list<array{key:string,label:string,opties:array<string,mixed>}> $vragen
 * @return list<array<string,mixed>>
 */
function sm_vragen_soort(string $soort, string $kop, array $vragen): array
{
    $als = ['sm_gebruik' => [$soort]];
    $uit = [['key' => 'sm_kop_' . $soort, 'type' => 'kop', 'label' => $kop, 'toon_als' => $als]];

    foreach ($vragen as $vraag) {
        $uit[] = $vraag + [
            'type' => 'checkbox',
            'hint' => 'Kruis alles aan wat past.',
            'verplicht_als' => $als,
            'toon_als' => $als,
        ];
    }

    return $uit;
}

/** Alle platforms behalve deze, voor toon_als. @return list<string> */
function sm_platforms_behalve(string ...$uit): array
{
    return array_values(array_diff(array_keys(sm_kaders()), $uit));
}
