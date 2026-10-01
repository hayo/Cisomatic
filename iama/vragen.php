<?php

/**
 * De vragenlijst, in de vijf delen van het IAMA.
 *
 * Dit is het bestand om aan te passen als je vragen wilt toevoegen,
 * herformuleren of weghalen. Formulier, controle en document lezen allemaal
 * uit deze definitie. De sleutels per vraag staan in ../README.md.
 *
 * Drie sleutels gebruikt alleen dit formulier, voor het document:
 * - 'art27': de vraag hoort bij artikel 27 van de AI verordening
 * - 'bij': de vraag hoort bij de vraag ervoor en krijgt geen eigen kop
 * - 'opsomming': een tekstvak met één punt per regel wordt een lijst
 * Een sectie heeft een 'sessie': vanaf welke sessie hij meedoet.
 */

declare(strict_types=1);

const IA_ART27 = 'Deze vraag hoort bij artikel 27 van de AI verordening.';

/** @return list<array<string,mixed>> */
function ia_secties(): array
{
    return [
        ia_sectie_over(),
        ia_sectie_doel(),
        ia_sectie_waarden(),
        ia_sectie_grondslag(),
        ia_sectie_verantwoordelijk(),
        ia_sectie_type(),
        ia_sectie_totstandkoming(),
        ia_sectie_invoer(),
        ia_sectie_kwaliteit(),
        ia_sectie_datagovernance(),
        ia_sectie_context(),
        ia_sectie_medewerker(),
        ia_sectie_communicatie(),
        ia_sectie_monitoring(),
        ia_sectie_impact(),
        ia_sectie_grondrechten(),
        ia_sectie_wetgeving(),
        ia_sectie_ernst(),
        ia_sectie_doeltreffend(),
        ia_sectie_noodzaak(),
        ia_sectie_balans(),
        ia_sectie_advies(),
        ia_sectie_restrisicos(),
    ];
}

/**
 * Een open vraag: standaard een verplicht tekstvak. $extra vult aan of
 * overschrijft, bijvoorbeeld met een type en opties.
 *
 * @param array<string,mixed> $extra
 * @return array<string,mixed>
 */
function ia_vraag(string $key, string $label, string $hint = '', array $extra = []): array
{
    $hint = trim($hint . (empty($extra['art27']) ? '' : ' ' . IA_ART27));

    return $extra + ['key' => $key, 'type' => 'textarea', 'label' => $label, 'verplicht' => true, 'hoogte' => 3]
        + ($hint === '' ? [] : ['hint' => $hint]);
}

/** @return array<string,mixed> */
function ia_janee(string $key, string $label, string $hint = '', array $extra = []): array
{
    return ia_vraag($key, $label, $hint, $extra + [
        'type' => 'radio',
        'opties' => ['ja' => ['label' => 'Ja'], 'nee' => ['label' => 'Nee']],
    ]);
}

/** Een toelichting bij de vraag ervoor. @return array<string,mixed> */
function ia_uitleg(string $key, string $label = 'Leg uit', array $extra = []): array
{
    return ia_vraag($key, $label, '', $extra + ['bij' => true, 'hoogte' => 2]);
}

/**
 * Welke sessie welke delen bespreekt, zoals het IAMA aanraadt: eerst deel 1
 * en 2, dan deel 3 en vraag 4.1, en na het juridische uitzoekwerk de rest.
 *
 * @return array<string,list<string>>
 */
function ia_vanaf_sessie(int $sessie): array
{
    return match ($sessie) {
        2 => ['sessie' => ['hoe', 'alles']],
        3 => ['sessie' => ['alles']],
        default => [],
    };
}

// ---------------------------------------------------------------------------
// Over deze IAMA

function ia_sectie_over(): array
{
    return [
        'id' => 'over',
        'titel' => 'Over deze IAMA',
        'intro' => 'Het IAMA is geen afvinklijst. Het team bespreekt de vragen samen, en legt de antwoorden '
            . 'en de belangrijkste afwegingen vast. Dat gaat het best in een paar sessies.',
        'vragen' => [
            [
                'key' => 'sessie',
                'type' => 'radio',
                'label' => 'Wat gaan jullie nu invullen?',
                'hint' => 'Na elke sessie download je de antwoorden. Laad ze de volgende keer weer in.',
                'verplicht' => true,
                'opties' => [
                    'wat' => [
                        'label' => 'Sessie 1: deel 1 en 2',
                        'hint' => 'Waarom het algoritme er komt, en wat het is.',
                    ],
                    'hoe' => [
                        'label' => 'Sessie 2: tot en met vraag 4.1',
                        'hint' => 'Ook hoe het algoritme wordt gebruikt, en welke grondrechten het raakt.',
                    ],
                    'alles' => [
                        'label' => 'Alles',
                        'hint' => 'Ook het juridische uitzoekwerk, de afweging en het advies.',
                    ],
                ],
            ],
            ia_vraag('projectnaam', 'Hoe heet het algoritme?', 'Deze naam komt overal in het document terug, en in '
                . 'de bestandsnaam.', ['type' => 'text']),
            ia_vraag('aanvrager', 'Wie stelt dit document op?', 'Naam en functie.', ['type' => 'text']),
            ia_vraag('organisatieonderdeel', 'Welke afdeling is verantwoordelijk voor het algoritme?', '', ['type' => 'text']),
            ia_vraag('ia_reden', 'Waarom maken jullie deze IAMA?', '', [
                'type' => 'radio',
                'opties' => [
                    'impactvol' => [
                        'label' => 'Het algoritme heeft direct gevolgen voor mensen of organisaties',
                        'hint' => 'Of het bepaalt hoe de overheid iemand indeelt. Dan heet het een impactvol algoritme.',
                    ],
                    'hoog_risico' => ['label' => 'Het is een AI systeem met een hoog risico'],
                    'quickscan' => ['label' => 'Uit de quickscan bleek dat een IAMA nodig is'],
                    'anders' => ['label' => 'Een andere reden'],
                ],
            ]),
            ia_uitleg('ia_reden_toelichting', 'Welke reden?', ['toon_als' => ['ia_reden' => ['anders']]]),
            [
                'key' => 'ia_team',
                'type' => 'rijen',
                'label' => 'Wie zitten er in het team?',
                'hint' => 'Het IAMA raadt vier tot zeven mensen aan, met verschillende achtergronden. In elk geval '
                    . 'een projectleider, een data scientist en een jurist.',
                'verplicht' => true,
                'rij_label' => 'Teamlid',
                'erbij' => 'Nog een teamlid',
                'kolommen' => [
                    'naam' => ['type' => 'text', 'label' => 'Naam', 'verplicht' => true, 'breedte' => 6],
                    'functie' => [
                        'type' => 'select',
                        'label' => 'Functie',
                        'verplicht' => true,
                        'breedte' => 10.6,
                        'opties' => [
                            '' => ['label' => 'Kies een functie'],
                            'projectleider' => ['label' => 'Projectleider'],
                            'data_scientist' => ['label' => 'Data scientist'],
                            'jurist' => ['label' => 'Jurist'],
                            'gespreksleider' => ['label' => 'Gespreksleider'],
                            'proceseigenaar' => ['label' => 'Proceseigenaar'],
                            'uitvoering' => ['label' => 'Medewerker uit de uitvoering'],
                            'privacy' => ['label' => 'Privacyadviseur of functionaris gegevensbescherming'],
                            'beveiliging' => ['label' => 'Informatiebeveiliger'],
                            'communicatie' => ['label' => 'Communicatieadviseur'],
                            'ethiek' => ['label' => 'Ethicus'],
                            'anders' => ['label' => 'Een andere functie'],
                        ],
                    ],
                    'toelichting' => ['type' => 'text', 'label' => 'Toelichting op de functie', 'bij' => 'functie'],
                ],
            ],
            ia_vraag('email', 'Op welk mailadres ben je bereikbaar?', '', [
                'type' => 'email', 'verplicht' => false, 'optioneel' => true,
            ]),
            ia_vraag('versie', 'Welk versienummer krijgt dit document?', '', [
                'type' => 'text', 'verplicht' => false, 'optioneel' => true, 'standaard' => '0.1',
            ]),
            ia_vraag('versie_toelichting', 'Wat is er veranderd in deze versie?', '', [
                'type' => 'text', 'verplicht' => false, 'optioneel' => true, 'standaard' => 'Eerste opzet',
            ]),
        ],
    ];
}

// ---------------------------------------------------------------------------
// Deel 1: waarom?

function ia_sectie_doel(): array
{
    return [
        'id' => 'doel',
        'titel' => '1.1 Aanleiding en doel',
        'stap' => 1,
        'sessie' => 1,
        'intro' => 'Deel 1 gaat over de vraag waarom het algoritme er komt. Die antwoorden heb je later steeds '
            . 'weer nodig, dus begin hier.',
        'vragen' => [
            ia_vraag('ia_aanleiding', '1.1.1 Wat was de aanleiding voor het algoritme?', 'Welk probleem moet het '
                . 'oplossen?'),
            ia_vraag('ia_doel', '1.1.2 Wat moet het algoritme bereiken?', 'Noem het hoofddoel, en de subdoelen.', [
                'art27' => true,
            ]),
        ],
    ];
}

function ia_sectie_waarden(): array
{
    $hint = 'Noteer er hooguit zes, één per regel. Het CODIO waardenkader in de toelichting helpt daarbij.';

    return [
        'id' => 'waarden',
        'titel' => '1.2 Publieke waarden',
        'stap' => 1,
        'sessie' => 1,
        'vragen' => [
            ia_vraag('ia_waarden_positief', '1.2.1 Welke publieke waarden kan het algoritme versterken?', $hint, [
                'opsomming' => true,
            ]),
            ia_vraag('ia_waarden_negatief', '1.2.2 Welke publieke waarden kan het algoritme schaden?', $hint, [
                'opsomming' => true,
            ]),
        ],
    ];
}

function ia_sectie_grondslag(): array
{
    $ai = ['ia_ai_systeem' => ['ja', 'twijfel']];

    return [
        'id' => 'grondslag',
        'titel' => '1.3 Wettelijke grondslag',
        'stap' => 1,
        'sessie' => 1,
        'vragen' => [
            ia_vraag('ia_ai_systeem', 'Is het algoritme een AI systeem?', 'Een AI systeem bepaalt zelf hoe het van '
                . 'de invoer tot een uitkomst komt, bijvoorbeeld met machine learning. Vaste regels die mensen '
                . 'hebben opgesteld, vallen daar meestal niet onder.', [
                'type' => 'radio',
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nee' => ['label' => 'Nee'],
                    'twijfel' => ['label' => 'Dat weten we nog niet zeker'],
                ],
            ]),
            ia_vraag('ia_verboden', '1.3.1 Is het een verboden AI systeem?', 'Artikel 5 van de AI verordening noemt '
                . 'wat verboden is. Een voorbeeld is mensen een score geven op hun gedrag. De AI scan in deze tool '
                . 'zoekt het voor je uit.', [
                'type' => 'radio',
                'toon_als' => $ai,
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'ja' => ['label' => 'Ja', 'hint' => 'Dan mag het algoritme niet worden gebruikt.'],
                    'onbekend' => ['label' => 'Dat weten we nog niet'],
                ],
            ]),
            ia_vraag('ia_grondslag_taak', '1.3.2 Voert het algoritme een wettelijke taak uit?', '', [
                'type' => 'radio',
                'opties' => [
                    'taak' => ['label' => 'Ja'],
                    'intern' => [
                        'label' => 'Nee, het is alleen voor interne processen',
                        'hint' => 'Dan is geen wettelijke grondslag nodig.',
                    ],
                    'onduidelijk' => ['label' => 'Dat is nog niet duidelijk'],
                ],
            ]),
            ia_uitleg('ia_grondslag', 'Wat is de wettelijke grondslag?', [
                'hint' => 'Noem de wet en het artikel.',
                'toon_als' => ['ia_grondslag_taak' => ['taak']],
            ]),
        ],
    ];
}

function ia_sectie_verantwoordelijk(): array
{
    return [
        'id' => 'verantwoordelijk',
        'titel' => '1.4 Verantwoordelijkheden',
        'stap' => 1,
        'sessie' => 1,
        'vragen' => [
            [
                'key' => 'ia_partijen',
                'type' => 'rijen',
                'label' => '1.4.1 Welke partijen en functies zijn erbij betrokken? Waarvoor zijn ze verantwoordelijk?',
                'hint' => 'Denk aan de ontwikkeling, het gebruik en het beheer. Staat een partij in meer fases, '
                    . 'geef dan elke fase een eigen regel.',
                'verplicht' => true,
                'rij_label' => 'Partij',
                'erbij' => 'Nog een partij',
                'kolommen' => [
                    'fase' => [
                        'type' => 'select',
                        'label' => 'Wanneer',
                        'breedte' => 3.6,
                        'opties' => [
                            'ontwikkeling' => ['label' => 'Bij de ontwikkeling'],
                            'inzet' => ['label' => 'Bij het gebruik'],
                            'beheer' => ['label' => 'Bij het beheer'],
                        ],
                    ],
                    'partij' => ['type' => 'text', 'label' => 'Partij of functie', 'verplicht' => true, 'breedte' => 5],
                    'taak' => ['type' => 'textarea', 'label' => 'Waarvoor verantwoordelijk', 'breedte' => 8],
                ],
            ],
            ia_vraag('ia_eindverantwoordelijke', '1.4.2 Wie is eindverantwoordelijk voor het algoritme?', '', [
                'type' => 'text',
            ]),
            ia_janee('ia_exit', '1.4.3 Is er een exitstrategie?', 'Een plan om te stoppen, als het algoritme later '
                . 'niet meer nodig of gewenst is.'),
            ia_uitleg('ia_exit_omschrijving', 'Hoe ziet die exitstrategie eruit?', [
                'toon_als' => ['ia_exit' => ['ja']],
            ]),
        ],
    ];
}

// ---------------------------------------------------------------------------
// Deel 2: wat?

function ia_sectie_type(): array
{
    return [
        'id' => 'type',
        'titel' => '2.1 Type algoritme',
        'stap' => 2,
        'sessie' => 1,
        'intro' => 'Deel 2 gaat over het algoritme en de data. Bestaat de toepassing uit meer algoritmes? Bespreek '
            . 'deel 2 dan per algoritme, en begin bij het laatste in de keten.',
        'vragen' => [
            ia_vraag('ia_type', '2.1.1 Wat voor algoritme is het?', 'Kies alles wat geldt. Hiervan hangt af welke '
                . 'vragen er bij 2.2 komen.', [
                'type' => 'checkbox',
                'opties' => [
                    'regels' => ['label' => 'Beslisregels, niet zelflerend', 'hint' => 'Regels die mensen hebben opgesteld.'],
                    'model' => ['label' => 'Een zelflerend model', 'hint' => 'Een statistisch model, of machine learning.'],
                    'generatief' => [
                        'label' => 'Generatieve AI of een taalmodel',
                        'hint' => 'Bijvoorbeeld een chatbot. Meestal is het model van een externe leverancier.',
                    ],
                ],
            ]),
            ia_uitleg('ia_type_omschrijving', 'Beschrijf het algoritme zo precies als je kunt', [
                'hint' => 'Hoe preciezer je bent, hoe makkelijker de vragen hierna worden.',
                'hoogte' => 3,
            ]),
            ia_vraag('ia_ontwikkelaar', 'Wie heeft het algoritme ontwikkeld?', '', [
                'type' => 'radio',
                'opties' => [
                    'eigen' => ['label' => 'De eigen organisatie'],
                    'extern' => ['label' => 'Een externe partij'],
                    'samen' => ['label' => 'De eigen organisatie, samen met een externe partij'],
                ],
            ]),
            ia_vraag('ia_alternatieven', '2.1.2 Welke andere oplossingen zijn er? Waarom past dit type algoritme het '
                . 'best bij het doel?', 'Het doel staat bij vraag 1.1.2.'),
        ],
    ];
}

function ia_sectie_totstandkoming(): array
{
    $regels = ['toon_als' => ['ia_type' => ['regels']]];
    $model = ['toon_als' => ['ia_type' => ['model', 'generatief']]];

    return [
        'id' => 'totstandkoming',
        'titel' => '2.2 Hoe het algoritme tot stand komt',
        'stap' => 2,
        'sessie' => 1,
        'intro' => 'Voor deze vragen heb je soms documentatie van de leverancier of de ontwikkelaar nodig.',
        'vragen' => [
            ['key' => 'ia_regels_kop', 'type' => 'kop', 'label' => '2.2A Beslisregels'] + $regels,
            ia_vraag('ia_regels_herkomst', '2.2A.1 Hoe zijn de beslisregels tot stand gekomen?', '', $regels),
            ia_vraag('ia_regels_bias', '2.2A.2 Welke aannames en welke bias zitten in deze beslisregels?', '', $regels),
            ia_vraag('ia_regels_minimalisatie', '2.2A.3 Hoe blijft het bij zo weinig mogelijk gegevens? Zijn alle '
                . 'indicatoren nodig voor een goed resultaat?', '', $regels),
            ['key' => 'ia_model_kop', 'type' => 'kop', 'label' => '2.2B Zelflerend model'] + $model,
            ia_vraag('ia_model_bronnen', '2.2B.1 Met welke databronnen is het model getraind en getest?', '', $model),
            ia_vraag('ia_model_kwaliteit', '2.2B.2 Zijn de trainingsdata en testdata goed en betrouwbaar genoeg voor '
                . 'dit gebruik? Leg uit.', '', $model),
            ia_vraag('ia_model_representatief', '2.2B.3 Passen de trainingsdata en testdata bij de situatie waarin '
                . 'het model wordt gebruikt? Hoe verschillen ze van de data die straks de invoer zijn?', '', $model),
            ia_vraag('ia_model_minimalisatie', '2.2B.4 Hoe blijft het bij zo weinig mogelijk gegevens? Zijn alle data '
                . 'nodig voor een goed resultaat?', '', $model),
        ],
    ];
}

function ia_sectie_invoer(): array
{
    return [
        'id' => 'invoer',
        'titel' => '2.3 Het algoritme in gebruik',
        'stap' => 2,
        'sessie' => 1,
        'intro' => 'Is het algoritme ingekocht, en is de ontwikkelaar er niet bij? Kijk dan in de instructies voor '
            . 'gebruik.',
        'vragen' => [
            ia_vraag('ia_invoer_data', '2.3.1 Welke data krijgt het algoritme als invoer, als het in gebruik is? Waar '
                . 'komen die data vandaan?'),
            ia_vraag('ia_invoer_kwaliteit', '2.3.2 Zijn die data goed en betrouwbaar genoeg voor dit gebruik? Leg uit.'),
            ia_vraag('ia_invoer_bias', '2.3.3 Welke aannames en welke bias zitten in die data?'),
        ],
    ];
}

function ia_sectie_kwaliteit(): array
{
    return [
        'id' => 'kwaliteit',
        'titel' => '2.4 Kwaliteit en nauwkeurigheid',
        'stap' => 2,
        'sessie' => 1,
        'vragen' => [
            ia_vraag('ia_kwaliteit_meting', '2.4.1 Hoe meet je de kwaliteit van het algoritme? Welke maatstaven tellen '
                . 'het zwaarst, en waarom?', 'Zo’n maatstaf heet ook een metric.'),
            ia_vraag('ia_kwaliteit_grens', '2.4.2 Wanneer is de kwaliteit goed genoeg? Welke scores horen daarbij?'),
            ia_vraag('ia_fouten', '2.4.3 Tot welke ongewenste uitkomsten kan een fout van het algoritme leiden? Wat '
                . 'zijn daarvan de gevolgen?', 'De toelichting geeft eenvoudige manieren om dat uit te zoeken.'),
        ],
    ];
}

function ia_sectie_datagovernance(): array
{
    return [
        'id' => 'datagovernance',
        'titel' => '2.5 Data governance en beveiliging',
        'stap' => 2,
        'sessie' => 1,
        'vragen' => [
            ia_vraag('ia_afspraken', '2.5.1 Zijn er duidelijke afspraken met de externe partij over wie eigenaar is '
                . 'van het algoritme, en wie het beheert? Wat zijn die afspraken?', '', [
                'toon_als' => ['ia_ontwikkelaar' => ['extern', 'samen']],
            ]),
            ia_vraag('ia_beveiliging', '2.5.2 Zijn de data goed genoeg beveiligd? Leg uit, apart voor de invoer en '
                . 'voor de uitkomsten.'),
            ia_vraag('ia_toegang', '2.5.3 Wordt gecontroleerd wie bij de data kan? Leg uit, apart voor de invoer en '
                . 'voor de uitkomsten.'),
        ],
    ];
}

// ---------------------------------------------------------------------------
// Deel 3: hoe?

function ia_sectie_context(): array
{
    return [
        'id' => 'context',
        'titel' => '3.1 Gebruikscontext',
        'stap' => 3,
        'sessie' => 2,
        'toon_als' => ia_vanaf_sessie(2),
        'intro' => 'Een algoritme doet op zichzelf geen kwaad. Dat hangt af van hoe het wordt gebruikt, en van de '
            . 'besluiten die op de uitkomst volgen. Deel 3 gaat daarover.',
        'vragen' => [
            ia_vraag('proces_omschrijving', '3.1.1 Hoe ziet het proces eruit waar het algoritme deel van is? Wat '
                . 'gebeurt er met de uitkomsten, en welke besluiten volgen daaruit?', '', ['art27' => true]),
            ia_vraag('ia_periode', '3.1.2 Wanneer wordt het algoritme gebruikt, en hoe lang?', '', ['hoogte' => 2]),
            ia_vraag('ia_frequentie', '3.1.3 Hoe vaak wordt het algoritme gebruikt?', '', ['art27' => true, 'hoogte' => 2]),
            ia_vraag('ia_plaats', '3.1.4 Waar wordt het algoritme gebruikt?', 'Bijvoorbeeld in een bepaald gebied, '
                . 'of bij een bepaalde groep mensen of dossiers.', ['hoogte' => 2]),
            ia_vraag('ia_neveneffecten', '3.1.5 Kan het algoritme onbedoelde effecten hebben op andere algoritmes of '
                . 'processen? Is daar genoeg rekening mee gehouden? Leg uit.'),
        ],
    ];
}

function ia_sectie_medewerker(): array
{
    return [
        'id' => 'medewerker',
        'titel' => '3.2 De rol van de medewerker',
        'stap' => 3,
        'sessie' => 2,
        'toon_als' => ia_vanaf_sessie(2),
        'vragen' => [
            ia_vraag('ia_menselijke_rol', 'Wie neemt het besluit?', '', [
                'type' => 'radio',
                'opties' => [
                    'medewerker' => ['label' => 'Een medewerker, en het algoritme helpt daarbij'],
                    'controle' => ['label' => 'Het algoritme, en een medewerker controleert dat'],
                    'algoritme' => ['label' => 'Het algoritme, zonder dat een medewerker meekijkt'],
                ],
            ]),
            ia_vraag('ia_rol_medewerker', '3.2.1 Welke rol spelen medewerkers in het proces?', 'Dit heet ook '
                . 'menselijke tussenkomst.', ['art27' => true]),
            ia_vraag('ia_afwijken', '3.2.2 Kan een medewerker afwijken van de uitkomst van het algoritme, als dat nodig '
                . 'is?', '', [
                'type' => 'radio',
                'art27' => true,
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'deels' => ['label' => 'Soms, of alleen in bepaalde gevallen'],
                    'nee' => ['label' => 'Nee'],
                ],
            ]),
            ia_uitleg('ia_afwijken_toelichting'),
            ia_vraag('ia_geletterdheid', '3.2.3 Weten de medewerkers genoeg van AI om met het algoritme te werken? '
                . 'Welke kennis en vaardigheden hebben ze nog nodig, en hoe krijgen ze die?', 'Dit heet ook '
                . 'AI geletterdheid.'),
            ia_vraag('ia_kennisverlies', '3.2.4 Kunnen medewerkers door het algoritme kennis of vaardigheden '
                . 'kwijtraken? Leg uit.'),
        ],
    ];
}

function ia_sectie_communicatie(): array
{
    return [
        'id' => 'communicatie',
        'titel' => '3.3 Communicatie',
        'stap' => 3,
        'sessie' => 2,
        'toon_als' => ia_vanaf_sessie(2),
        'vragen' => [
            ia_vraag('ia_communicatie_intern', '3.3.1 Hoe hoort de eigen organisatie dat het algoritme wordt gebruikt?'),
            ia_vraag('ia_communicatie_extern', '3.3.2 Hoe vertelt de organisatie buiten de deur over het algoritme en '
                . 'het proces eromheen?'),
            ia_vraag('ia_communicatie_wie', '3.3.3 Wie is verantwoordelijk voor die communicatie?', '', ['type' => 'text']),
            ia_vraag('ia_uitlegbaar', '3.3.4 Hoe goed is het algoritme uit te leggen aan de mensen die het raakt?'),
        ],
    ];
}

function ia_sectie_monitoring(): array
{
    return [
        'id' => 'monitoring',
        'titel' => '3.4 Monitoring en evaluatie',
        'stap' => 3,
        'sessie' => 2,
        'toon_als' => ia_vanaf_sessie(2),
        'vragen' => [
            ia_vraag('ia_monitoring', '3.4.1 Hoe houdt de organisatie het algoritme in de gaten, zolang het bestaat? Hoe '
                . 'kan ze bijsturen als een risico echt optreedt?', '', ['art27' => true]),
            ia_vraag('ia_melden_medewerkers', '3.4.2 Kunnen medewerkers makkelijk melden dat er iets mis lijkt met het '
                . 'algoritme? Hoe worden ze dan gehoord?', '', ['art27' => true]),
            ia_vraag('ia_melden_betrokkenen', '3.4.3 Kunnen betrokkenen makkelijk melden dat er iets mis lijkt met het '
                . 'algoritme? Hoe worden ze dan gehoord?'),
            ia_vraag('ia_ander_doel', '3.4.4 Kan het algoritme later voor een ander doel worden gebruikt? Zo ja, hoe '
                . 'wordt dat voorkomen?', 'Dit heet ook function creep.'),
            ia_vraag('ia_succes', '3.4.5 Wanneer is het algoritme een succes? Hoe en wanneer stel je dat vast?'),
        ],
    ];
}

function ia_sectie_impact(): array
{
    return [
        'id' => 'impact',
        'titel' => '3.5 Gevolgen voor mensen en de maatschappij',
        'stap' => 3,
        'sessie' => 2,
        'toon_als' => ia_vanaf_sessie(2),
        'intro' => 'Kijk hierbij terug naar de publieke waarden bij 1.2.',
        'vragen' => [
            ia_vraag('ia_groepen', '3.5.1 Welke mensen of groepen kan het algoritme raken?', 'Eén groep per regel.', [
                'art27' => true,
                'opsomming' => true,
            ]),
            ia_vraag('ia_nadelen', '3.5.2 Welke risico’s of nadelen brengt het algoritme voor deze mensen of groepen?',
                'Denk ook aan de publieke waarden die het kan schaden, bij 1.2.2.', ['art27' => true]),
            ia_vraag('ia_voordelen', '3.5.3 Welke voordelen brengt het algoritme voor deze mensen of groepen?', 'Denk '
                . 'ook aan de publieke waarden die het versterkt, bij 1.2.1.'),
            ia_vraag('ia_inspraak', '3.5.4 Hoe kunnen betrokkenen meepraten? Worden ze actief betrokken?'),
            ia_vraag('ia_opt_out', '3.5.5 Kunnen betrokkenen kiezen dat het algoritme niet voor hen wordt gebruikt? Hoe '
                . 'is dat geregeld?'),
            ia_vraag('ia_maatschappij', '3.5.6 Kan dit soort algoritme ongewenste gevolgen hebben voor de maatschappij? '
                . 'Hoe erg zijn die? Leg uit.', 'Bijvoorbeeld: te afhankelijk worden van techniek, minder vertrouwen '
                . 'in de overheid, andere normen, een te simpel beeld van de werkelijkheid, of schade aan het milieu.'),
            ia_vraag('ia_zorgen', '3.5.7 Zijn er zorgen over het algoritme die nog niet genoeg aan bod kwamen? Zo ja, '
                . 'welke, en hoe neem je ze weg?'),
        ],
    ];
}

// ---------------------------------------------------------------------------
// Deel 4: grondrechten

function ia_sectie_grondrechten(): array
{
    $hint = 'Eén grondrecht per regel, of een aspect ervan.';

    return [
        'id' => 'grondrechten',
        'titel' => '4.1 Welke grondrechten spelen mee?',
        'stap' => 4,
        'sessie' => 2,
        'toon_als' => ia_vanaf_sessie(2),
        'intro' => 'Deel 4 heeft twee doelen. Eerst zie je welke grondrechten het algoritme raakt, goed of slecht. '
            . 'Is de impact negatief, dan bespreek je daarna of die toch aanvaardbaar is. Een overzicht van '
            . 'grondrechten staat bij de grondrechtenclusters in de toelichting.',
        'vragen' => [
            ia_vraag('ia_brainstorm_positief', '4.1.1 Brainstorm: welke grondrechten kan het algoritme beschermen?',
                $hint . ' Dit mag met het hele team.', ['verplicht' => false, 'opsomming' => true]),
            ia_vraag('ia_brainstorm_negatief', '4.1.1 Brainstorm: welke grondrechten kan het algoritme aantasten?',
                $hint, ['verplicht' => false, 'opsomming' => true]),
            ia_vraag('ia_grondrechten_positief', '4.1.2 Welke grondrechten beschermt het algoritme echt?', 'Kies uit '
                . 'de brainstorm wat realistisch is. ' . $hint, ['verplicht' => false, 'opsomming' => true]),
            ia_vraag('ia_grondrechten_negatief', '4.1.2 Welke grondrechten tast het algoritme echt aan?', 'Kies uit '
                . 'de brainstorm wat realistisch is. ' . $hint, ['verplicht' => false, 'opsomming' => true]),
        ],
    ];
}

function ia_sectie_wetgeving(): array
{
    return [
        'id' => 'wetgeving',
        'titel' => '4.2 Specifieke wetgeving',
        'stap' => 4,
        'sessie' => 3,
        'toon_als' => ia_vanaf_sessie(3),
        'intro' => 'Voor deze vragen is juridisch uitzoekwerk nodig. Een jurist zoekt dit uit voor de sessie.',
        'vragen' => [
            ia_janee('ia_hoog_risico', '4.2.1 Is het algoritme een AI systeem met een hoog risico?', 'De AI scan in '
                . 'deze tool zoekt dat voor je uit. Is het antwoord ja? Dan zet de tool bij de actiepunten dat je het '
                . 'systeem toetst aan de eisen van de AI verordening.', [
                'voorstel' => true,
            ]),
            ia_vraag('ia_dpia', '4.2.2 Gebruikt het algoritme persoonsgegevens? Is een DPIA verplicht?', 'De '
                . 'quickscan in deze tool zoekt dat voor je uit.', [
                'type' => 'radio',
                'opties' => [
                    'geen' => ['label' => 'Het algoritme gebruikt geen persoonsgegevens'],
                    'niet_nodig' => ['label' => 'Het gebruikt persoonsgegevens, maar een DPIA is niet verplicht'],
                    'nodig' => [
                        'label' => 'Het gebruikt persoonsgegevens, en een DPIA is verplicht',
                        'hint' => 'Gebruik de antwoorden uit deze IAMA voor de DPIA. Het formulier voor de DPIA leest '
                            . 'dit bestand in.',
                    ],
                    'gedaan' => ['label' => 'Er is al een DPIA gemaakt'],
                ],
            ]),
            ia_vraag('ia_gelijke_behandeling', '4.2.3 Maakt het algoritme onderscheid tussen mensen, zoals de wetten '
                . 'voor gelijke behandeling dat bedoelen?', 'Direct of indirect onderscheid, of een profiel dat kan '
                . 'discrimineren. Denk aan de Algemene wet gelijke behandeling.', [
                'type' => 'radio',
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'misschien' => ['label' => 'Dat kan, maar het is nog niet zeker'],
                    'nee' => ['label' => 'Nee'],
                ],
            ]),
            ia_uitleg('ia_gelijke_behandeling_toelichting'),
            ia_janee('ia_andere_wetgeving', '4.2.4 Gelden er andere wetten voor deze inbreuk op grondrechten?',
                'Bijvoorbeeld de Algemene wet bestuursrecht, het procesrecht, de DSA, consumentenrecht of '
                . 'milieuwetgeving.'),
            ia_uitleg('ia_andere_wetgeving_welke', 'Welke wetten? Past het algoritme daarbinnen?', [
                'toon_als' => ['ia_andere_wetgeving' => ['ja']],
            ]),
            ia_janee('ia_rechtspraak', '4.2.5 Is er Nederlandse of Europese rechtspraak met duidelijke criteria om de '
                . 'inbreuk te beoordelen?', 'Zo ja, gebruik dan eerst die criteria.'),
            ia_uitleg('ia_rechtspraak_toelichting', 'Welke uitspraken? Wat zeggen de criteria over dit algoritme?', [
                'toon_als' => ['ia_rechtspraak' => ['ja']],
            ]),
            ia_vraag('ia_toetsing', 'Zijn vraag 4.3 tot en met 4.5 nodig?', 'Die vragen zijn alleen nodig als het '
                . 'algoritme grondrechten aantast, en de wetten en rechtspraak hierboven geen volledig antwoord geven.', [
                'type' => 'radio',
                'opties' => [
                    'geen' => ['label' => 'Nee, het algoritme tast geen grondrechten aan'],
                    'beantwoord' => ['label' => 'Nee, de wetten en rechtspraak hierboven geven een volledig antwoord'],
                    'nodig' => ['label' => 'Ja'],
                ],
            ]),
            ia_uitleg('ia_toetsing_toelichting', 'Wat is dat antwoord? Is de inbreuk te rechtvaardigen?', [
                'toon_als' => ['ia_toetsing' => ['beantwoord']],
            ]),
        ],
    ];
}

function ia_sectie_ernst(): array
{
    return [
        'id' => 'ernst',
        'titel' => '4.3 Hoe ernstig is de inbreuk?',
        'stap' => 4,
        'sessie' => 3,
        'toon_als' => ia_vanaf_sessie(3) + ['ia_toetsing' => ['nodig']],
        'intro' => 'Hoe ernstiger de inbreuk, hoe beter het algoritme moet werken. Er mogen dan ook minder andere '
            . 'oplossingen zijn. Bespreek per grondrecht hoe ernstig de inbreuk is.',
        'vragen' => [
            [
                'key' => 'ia_inbreuken',
                'type' => 'rijen',
                'label' => 'Welke grondrechten tast het algoritme aan, en hoe ernstig?',
                'hint' => 'Raakt het de kern van een grondrecht, en gaat de inbreuk ver? Dan is die ernstig. Is het '
                    . 'grondrecht minder belangrijk, en de inbreuk beperkt? Dan is die licht. Alles daartussen is '
                    . 'meestal medium.',
                'verplicht' => true,
                'rij_label' => 'Grondrecht',
                'erbij' => 'Nog een grondrecht',
                'suggesties' => 'ia_suggesties',
                'kolommen' => [
                    'grondrecht' => [
                        'type' => 'text',
                        'label' => 'Grondrecht, of het aspect ervan',
                        'kort' => 'Grondrecht',
                        'verplicht' => true,
                        'suggesties' => true,
                        'breedte' => 3.6,
                    ],
                    'belang' => [
                        'type' => 'select',
                        'label' => '4.3.1 Hoe belangrijk is het grondrecht hier?',
                        'kort' => 'Hoe belangrijk',
                        'verplicht' => true,
                        'breedte' => 5.4,
                        'opties' => [
                            '' => ['label' => 'Kies'],
                            'kern' => ['label' => 'Heel belangrijk: het raakt de kern'],
                            'tussen' => ['label' => 'Iets ertussenin'],
                            'minder' => ['label' => 'Niet zo belangrijk'],
                        ],
                    ],
                    'belang_uitleg' => ['type' => 'textarea', 'label' => 'Waarom?', 'bij' => 'belang'],
                    'impact' => [
                        'type' => 'select',
                        'label' => '4.3.2 Hoe groot is de negatieve impact?',
                        'kort' => 'Hoe groot de impact',
                        'verplicht' => true,
                        'breedte' => 5.4,
                        'opties' => [
                            '' => ['label' => 'Kies'],
                            'groot' => ['label' => 'Heel groot: het grondrecht is bijna niet meer te gebruiken'],
                            'tussen' => ['label' => 'Iets ertussenin'],
                            'beperkt' => ['label' => 'Beperkt: de gevolgen zijn makkelijk te herstellen'],
                        ],
                    ],
                    'impact_uitleg' => ['type' => 'textarea', 'label' => 'Waarom?', 'bij' => 'impact'],
                    'ernst' => [
                        'type' => 'select',
                        'label' => '4.3.3 Hoe ernstig is de inbreuk, als je beide samen bekijkt?',
                        'kort' => 'Ernst',
                        'verplicht' => true,
                        'breedte' => 2.2,
                        'opties' => [
                            '' => ['label' => 'Kies'],
                            'ernstig' => ['label' => 'Ernstig'],
                            'medium' => ['label' => 'Medium'],
                            'licht' => ['label' => 'Licht'],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function ia_sectie_doeltreffend(): array
{
    return [
        'id' => 'doeltreffend',
        'titel' => '4.4 Werkt het algoritme?',
        'stap' => 4,
        'sessie' => 3,
        'toon_als' => ia_vanaf_sessie(3) + ['ia_toetsing' => ['nodig']],
        'vragen' => [
            ia_vraag('ia_doeltreffend', '4.4.1 Bereikt het algoritme de doelen?', 'Kijk nog een keer naar de doelen '
                . 'bij vraag 1.1.2.', [
                'type' => 'radio',
                'opties' => [
                    'zeker' => ['label' => 'Vrijwel zeker'],
                    'waarschijnlijk' => ['label' => 'Waarschijnlijk'],
                    'onduidelijk' => ['label' => 'Dat is onduidelijk, of onwaarschijnlijk'],
                ],
            ]),
            ia_uitleg('ia_doeltreffend_toelichting'),
            ia_vraag('ia_redelijk_effectief', '4.4.2 Is het redelijk om het algoritme te gebruiken?', 'Leg twee dingen '
                . 'naast elkaar: hoe goed het algoritme werkt, en hoe ernstig de inbreuk is.', [
                'type' => 'radio',
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nee' => ['label' => 'Nee', 'hint' => 'Dan stopt deel 4, en gaat het algoritme terug naar de tekentafel.'],
                ],
            ]),
            ia_uitleg('ia_redelijk_effectief_toelichting'),
        ],
    ];
}

function ia_sectie_noodzaak(): array
{
    return [
        'id' => 'noodzaak',
        'titel' => '4.5 Is het algoritme nodig?',
        'stap' => 4,
        'sessie' => 3,
        'toon_als' => ia_vanaf_sessie(3) + ['ia_toetsing' => ['nodig'], 'ia_redelijk_effectief' => ['ja']],
        'vragen' => [
            ia_vraag('ia_subsidiariteit', '4.5.1 Zijn de doelen ook te bereiken zonder dit algoritme? Zijn er andere '
                . 'middelen die grondrechten minder aantasten?', 'Dit heet ook subsidiariteit.'),
            ia_vraag('ia_aanpassingen', '4.5.2 Kan het algoritme, of de manier van gebruiken, zo veranderen dat de '
                . 'inbreuk minder ernstig wordt? Zo ja, hoe?'),
            ia_vraag('ia_restrisico_grondrecht', '4.5.3 Welke risico’s voor het grondrecht blijven er over, als die '
                . 'aanpassingen er zijn?'),
            ia_vraag('ia_redelijk_noodzaak', '4.5.4 Is het redelijk om dit algoritme te gebruiken?', 'Leg twee dingen '
                . 'naast elkaar. Aan de ene kant: hoe nodig het algoritme is, welke andere oplossingen er zijn, en '
                . 'welke aanpassingen. Aan de andere kant: hoe ernstig de inbreuk is.', [
                'type' => 'radio',
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nee' => ['label' => 'Nee', 'hint' => 'Dan stopt deel 4, en gaat het algoritme terug naar de tekentafel.'],
                ],
            ]),
            ia_uitleg('ia_redelijk_noodzaak_toelichting'),
        ],
    ];
}

// ---------------------------------------------------------------------------
// Deel 5: afsluiting. Alleen als het team niet bij 4.4.2 of 4.5.4 is gestopt.
// Een verborgen vraag telt als leeg, dus leeg of ja betekent: niet gestopt.

/** @return array<string,list<string>> */
function ia_niet_gestopt(): array
{
    return ia_vanaf_sessie(3) + ['ia_redelijk_effectief' => ['', 'ja'], 'ia_redelijk_noodzaak' => ['', 'ja']];
}

function ia_sectie_balans(): array
{
    return [
        'id' => 'balans',
        'titel' => 'A. De balans',
        'stap' => 5,
        'sessie' => 3,
        'toon_als' => ia_niet_gestopt(),
        'intro' => 'Leg nu alles naast elkaar, als op een weegschaal. Wegen de belangen die het algoritme dient '
            . 'zwaarder dan de belangen die het schaadt? Helemaal objectief wordt dat nooit, maar een overzicht helpt.',
        'vragen' => [
            ['key' => 'ia_gediend_kop', 'type' => 'kop', 'label' => 'Belangen die het algoritme dient'],
            ia_vraag('ia_gediend', 'Welke belangen dient het algoritme?', 'Kijk naar de doelen (1.1.2), de publieke '
                . 'waarden die het versterkt (1.2.1) en de grondrechten die het beschermt (4.1.2). Denk ook aan de '
                . 'eigen organisatie, zoals de kosten.', ['opsomming' => true]),
            ia_vraag('ia_gediend_gewicht', 'Hoe concreet zijn deze belangen, en hoe zwaar wegen ze?', 'De toelichting '
                . 'bij vraag 4.3 helpt daarbij.'),
            ia_vraag('ia_gediend_kans', 'Hoe waarschijnlijk is het dat het algoritme deze belangen dient?', 'De tool '
                . 'neemt je antwoord bij 4.4.1 alvast over. Vul het aan waar dat nodig is.', ['motivatie_voor' => 'kans']),
            ia_vraag('ia_gediend_noodzaak', 'Hoe nodig is dit algoritme om deze belangen te dienen?', 'De tool neemt je '
                . 'antwoord bij 4.5.1 alvast over. Vul het aan waar dat nodig is.', ['motivatie_voor' => 'noodzaak']),
            ['key' => 'ia_geschaad_kop', 'type' => 'kop', 'label' => 'Belangen die het algoritme schaadt'],
            ia_vraag('ia_geschaad', 'Welke belangen schaadt het algoritme?', 'Kijk naar de gevolgen voor mensen en '
                . 'groepen (3.5.2), voor de maatschappij (3.5.6) en de ernst van de inbreuk (4.3.3). Denk ook aan de '
                . 'eigen organisatie.', ['opsomming' => true]),
            ia_vraag('ia_geschaad_gewicht', 'Hoe concreet zijn deze belangen, en hoe zwaar wegen ze?', 'De toelichting '
                . 'bij vraag 4.3 helpt daarbij.'),
        ],
    ];
}

function ia_sectie_advies(): array
{
    return [
        'id' => 'advies',
        'titel' => 'B. Het advies',
        'stap' => 5,
        'sessie' => 3,
        'toon_als' => ia_niet_gestopt(),
        'intro' => 'Schrijf op basis van de balans een advies. De verantwoordelijke neemt daarna het besluit.',
        'vragen' => [
            ia_vraag('ia_advies', 'Wat adviseert het team?', '', [
                'type' => 'radio',
                'opties' => [
                    'inzetten' => ['label' => 'Het algoritme gebruiken'],
                    'voorwaarden' => ['label' => 'Het algoritme gebruiken, onder voorwaarden'],
                    'niet' => ['label' => 'Het algoritme niet gebruiken'],
                ],
            ]),
            ia_uitleg('ia_voorwaarden', 'Onder welke voorwaarden?', [
                'hint' => 'Eén voorwaarde per regel.',
                'opsomming' => true,
                'toon_als' => ['ia_advies' => ['voorwaarden']],
            ]),
            ia_vraag('ia_evenwicht', 'Zijn de belangen met dit advies redelijk in evenwicht? Waarom wel of niet?'),
            [
                'key' => 'ia_actiepunten',
                'type' => 'rijen',
                'label' => 'Welke actiepunten zijn er?',
                'hint' => 'Actiepunten die uit je antwoorden volgen, zet de tool er zelf bij. Hier vul je de rest in.',
                'rij_label' => 'Actiepunt',
                'erbij' => 'Nog een actiepunt',
                'kolommen' => [
                    'actie' => ['type' => 'textarea', 'label' => 'Wat moet er gebeuren?', 'kort' => 'Actie', 'verplicht' => true, 'breedte' => 9.6],
                    'wie' => ['type' => 'text', 'label' => 'Wie doet het?', 'kort' => 'Wie', 'breedte' => 4],
                    'wanneer' => ['type' => 'text', 'label' => 'Wanneer is het klaar?', 'kort' => 'Wanneer', 'breedte' => 3],
                ],
            ],
        ],
    ];
}

function ia_sectie_restrisicos(): array
{
    return [
        'id' => 'restrisicos',
        'titel' => 'C. Restrisico’s',
        'stap' => 5,
        'sessie' => 3,
        'toon_als' => ia_niet_gestopt(),
        'intro' => 'Soms blijft er een risico over, ook als alle actiepunten en maatregelen klaar zijn. Zet die '
            . 'risico’s op een rij. De verantwoordelijke leest ze, en aanvaardt ze bewust of wijst ze af.',
        'vragen' => [
            [
                'key' => 'ia_restrisicos',
                'type' => 'rijen',
                'label' => 'Welke risico’s blijven er over?',
                'hint' => 'Neem ook de restrisico’s uit vraag 4.5.3 mee. Blijft er niets over, laat dit dan leeg.',
                'rij_label' => 'Restrisico',
                'erbij' => 'Nog een restrisico',
                // Voor de verantwoordelijke, die het document afdrukt en per risico beslist.
                'extra_kolommen' => ['Aanvaard of afgewezen' => 3.6],
                'kolommen' => [
                    'risico' => ['type' => 'textarea', 'label' => 'Wat is het risico?', 'kort' => 'Restrisico', 'verplicht' => true, 'breedte' => 8],
                    'belang' => ['type' => 'text', 'label' => 'Voor welk grondrecht of belang?', 'kort' => 'Grondrecht of belang', 'breedte' => 5],
                ],
            ],
        ],
    ];
}
