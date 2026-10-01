<?php

/**
 * Vertrek van een kanaal, volgens de platformonafhankelijke richtlijn voor
 * vertrek: de vragen, het advies over het vertrek, en het exitplan in de acht
 * stappen van de richtlijn.
 *
 * De richtlijn geldt voor het verlaten van een platform als geheel. Sluit je
 * alleen één account, dan volgt het plan dezelfde stappen, zonder de
 * afstemming met de Voorlichtingsraad.
 */

declare(strict_types=1);

const SM_EXIT = ['sm_situatie' => ['exit']];

/** Hoe goed andere kanalen het vertrek opvangen, en de impact: de gradaties uit de richtlijn. */
const SM_EXIT_OPVANG = [
    'goed' => ['label' => 'Goed of helemaal'],
    'deels' => ['label' => 'Voor een deel'],
    'moeilijk' => ['label' => 'Moeilijk, of maar voor een klein deel'],
    'niet' => ['label' => 'Niet'],
];

const SM_EXIT_IMPACT = [
    'ernstig' => ['label' => 'Zeer ernstig', 'hint' => 'Er ontstaan ernstige problemen in de communicatie.'],
    'gemiddeld' => ['label' => 'Gemiddeld', 'hint' => 'Nadelige gevolgen, maar nog te overzien.'],
    'beperkt' => ['label' => 'Beperkt', 'hint' => 'Bescheiden gevolgen, niet onoverkomelijk.'],
    'geen' => ['label' => 'Weinig of geen'],
];

const SM_EXIT_AANPAK = [
    'soft' => [
        'label' => 'Soft exit: langzaam afbouwen',
        'kort' => 'Soft exit',
        'hint' => 'Aangeraden. Je plaatst steeds minder berichten, en stuurt volgers geleidelijk naar andere kanalen.',
    ],
    'direct' => [
        'label' => 'Hard exit: direct stoppen',
        'kort' => 'Hard exit, direct',
        'hint' => 'Als het niet anders kan. Doe de voorbereiding en de impactanalyse dan achteraf.',
    ],
    'moment' => [
        'label' => 'Hard exit: op een vast moment',
        'kort' => 'Hard exit, op een vast moment',
        'hint' => 'Bijvoorbeeld zodra het nieuwe kanaal klaar is.',
    ],
];

const SM_EXIT_TYPE = [
    'deactiveren' => [
        'label' => 'Account deactiveren',
        'kort' => 'account deactiveren',
        'hint' => 'Aangeraden. Niemand ziet het account nog, maar de naam blijft van jullie. In een noodsituatie zet '
            . 'je het weer aan.',
        'uitvoering' => 'Zet een slot op het account, zodat het niet meer te zien is.',
    ],
    'hybride' => [
        'label' => 'Account blijft, alleen voor noodsituaties',
        'kort' => 'alleen voor noodsituaties',
        'hint' => 'Zoals crisiscommunicatie. Voor de dagelijkse communicatie gebruik je andere kanalen.',
        'uitvoering' => 'Zet in de bio dat het account alleen nog in noodsituaties berichten plaatst, en waar mensen '
            . 'het dagelijkse nieuws vinden.',
    ],
    'inactief' => [
        'label' => 'Account blijft zichtbaar, maar inactief',
        'kort' => 'account inactief',
        'hint' => 'Oude berichten blijven te lezen, en iedereen ziet welk account echt is.',
        'uitvoering' => 'Zet in de bio dat het account niet meer actief is, pas de profielfoto aan, en zet een laatste '
            . 'bericht vast bovenaan. Doe bij voorkeur alle drie. Zeg ook dat er geen webcare meer is.',
    ],
    'verwijderen' => [
        'label' => 'Account verwijderen',
        'kort' => 'account verwijderen',
        'hint' => 'Sterk afgeraden. Anderen kunnen de naam dan claimen.',
        'uitvoering' => 'Archiveer eerst alle berichten. Handel lopende privéberichten af en archiveer ze, want ze '
            . 'kunnen met het account verdwijnen.',
    ],
];

/** @return list<array<string,mixed>> */
function sm_secties_exit(): array
{
    return [
        [
            'id' => 'exit_waarom',
            'titel' => 'Waarom vertrekken',
            'stap' => 7,
            'toon_als' => SM_EXIT,
            'intro' => 'Evalueer eerst het kanaal. Bereikt het nog zijn doel, en wat vangt een vertrek op?',
            'vragen' => [
                [
                    'key' => 'sm_exit_besluit',
                    'type' => 'radio',
                    'label' => 'Hoe ver is het vertrek?',
                    'verplicht' => true,
                    'opties' => [
                        'overweging' => ['label' => 'We overwegen te vertrekken'],
                        'besloten' => ['label' => 'Het vertrek is besloten'],
                        'opgelegd' => [
                            'label' => 'Het vertrek is opgelegd',
                            'hint' => 'Door een toezichthouder, om juridische redenen, of door het platform zelf.',
                        ],
                    ],
                ],
                [
                    'key' => 'sm_exit_reden',
                    'type' => 'textarea',
                    'label' => 'Waarom vertrek je?',
                    'verplicht' => true,
                    'hoogte' => 3,
                ],
                [
                    'key' => 'sm_exit_doel',
                    'type' => 'radio',
                    'label' => 'Bereikt het kanaal nog het doel waarvoor het er kwam?',
                    'hint' => 'Kijk naar de statistieken. Nemen volgers en betrokkenheid af? Of groeit het aantal '
                        . 'volgers, maar horen ze niet bij de doelgroep, of reageren ze kwetsend?',
                    'verplicht' => true,
                    'opties' => [
                        'ja' => ['label' => 'Ja'],
                        'deels' => ['label' => 'Voor een deel'],
                        'nee' => ['label' => 'Nee'],
                    ],
                ],
                [
                    'key' => 'sm_exit_opvang',
                    'type' => 'radio',
                    'label' => 'Hoe goed vangen andere kanalen het vertrek op?',
                    'hint' => 'Online en offline.',
                    'verplicht' => true,
                    'opties' => SM_EXIT_OPVANG,
                ],
                [
                    'key' => 'sm_exit_alternatieven',
                    'type' => 'textarea',
                    'label' => 'Welke kanalen nemen het over?',
                    'hint' => 'Kosten ze geld? Reken dat dan uit.',
                    'verplicht_als' => ['sm_exit_opvang' => ['goed', 'deels', 'moeilijk']],
                    'hoogte' => 2,
                ],
            ],
        ],
        [
            'id' => 'exit_impact',
            'titel' => 'Impact',
            'stap' => 8,
            'toon_als' => SM_EXIT,
            'intro' => 'Wat betekent het vertrek voor je communicatie, en voor burgers, media en stakeholders?',
            'vragen' => [
                [
                    'key' => 'sm_exit_impact',
                    'type' => 'radio',
                    'label' => 'Wat is de impact op burgers, media en stakeholders?',
                    'verplicht' => true,
                    'opties' => SM_EXIT_IMPACT,
                ],
                [
                    'key' => 'sm_exit_impact_toelichting',
                    'type' => 'textarea',
                    'label' => 'Wat zijn de gevolgen voor je communicatie en je doelgroep?',
                    'hint' => 'Denk aan je doelen, je werkwijze, en burgers die via het platform een vraag stelden.',
                    'verplicht' => true,
                    'hoogte' => 3,
                ],
            ],
        ],
        [
            'id' => 'exit_vertrek',
            'titel' => 'Het vertrek',
            'stap' => 9,
            'toon_als' => SM_EXIT,
            'intro' => 'Hoe en wanneer vertrek je, en wat gebeurt er met het account?',
            'vragen' => [
                [
                    'key' => 'sm_exit_aanpak',
                    'type' => 'radio',
                    'label' => 'Hoe vertrek je?',
                    'verplicht' => true,
                    'opties' => SM_EXIT_AANPAK,
                ],
                [
                    'key' => 'sm_exit_type',
                    'type' => 'radio',
                    'label' => 'Wat gebeurt er met het account?',
                    'verplicht' => true,
                    'opties' => SM_EXIT_TYPE,
                ],
                [
                    'key' => 'sm_exit_verwijderen',
                    'type' => 'textarea',
                    'label' => 'Waarom verwijder je het account?',
                    'hint' => 'Doe dat alleen als een toezichthouder het opdraagt, of om juridische redenen.',
                    'verplicht_als' => ['sm_exit_type' => ['verwijderen']],
                    'toon_als' => ['sm_exit_type' => ['verwijderen']],
                    'hoogte' => 2,
                ],
                [
                    'key' => 'sm_exit_datum',
                    'type' => 'text',
                    'label' => 'Wanneer vertrek je?',
                    'hint' => 'Een datum, of "per direct".',
                    'verplicht' => true,
                ],
                [
                    'key' => 'sm_exit_webcare',
                    'type' => 'text',
                    'label' => 'Tot wanneer beantwoord je nog vragen op het platform?',
                    'hint' => 'Ook na het laatste bericht komen er nog reacties en vermeldingen.',
                    'verplicht' => true,
                ],
                [
                    'key' => 'sm_exit_advies',
                    'type' => 'afgeleid',
                    'bron' => 'exit',
                    'label' => 'Advies over het vertrek:',
                ],
            ],
        ],
    ];
}

/** Of alles beantwoord is wat het advies bepaalt. */
function sm_exit_compleet(array $a): bool
{
    foreach (['sm_exit_besluit', 'sm_exit_doel', 'sm_exit_opvang', 'sm_exit_impact', 'sm_exit_aanpak', 'sm_exit_type'] as $veld) {
        if ((string) ($a[$veld] ?? '') === '') {
            return false;
        }
    }

    return true;
}

/**
 * Het advies over het vertrek: de gekozen aanpak, hoe zwaar die weegt, en
 * waar de richtlijn iets anders aanraadt. regels.js spiegelt dit.
 *
 * @return array{label:string,niveau:string,redenen:list<string>}
 */
function sm_exit_advies(array $a): array
{
    if (!sm_exit_compleet($a)) {
        return ['label' => 'Nog te bepalen', 'niveau' => '', 'redenen' => []];
    }

    $aanpak = (string) $a['sm_exit_aanpak'];
    $type = (string) $a['sm_exit_type'];
    $zwaar = in_array($a['sm_exit_opvang'], ['moeilijk', 'niet'], true) || $a['sm_exit_impact'] === 'ernstig';

    $redenen = array_values(array_filter([
        ($a['sm_exit_scope'] ?? '') === 'platform' ? SM_EXIT_TEKSTEN['platform'] : '',
        $a['sm_exit_doel'] === 'ja' && $a['sm_exit_besluit'] !== 'opgelegd' ? SM_EXIT_TEKSTEN['doel'] : '',
        $zwaar && $aanpak !== 'soft' && $a['sm_exit_besluit'] !== 'opgelegd' ? SM_EXIT_TEKSTEN['zwaar_soft'] : '',
        $zwaar && $aanpak === 'soft' && $type !== 'hybride' ? SM_EXIT_TEKSTEN['zwaar_hybride'] : '',
        $type === 'verwijderen' ? SM_EXIT_TEKSTEN['verwijderen'] : '',
    ]));
    if ($redenen === [] || $redenen === [SM_EXIT_TEKSTEN['platform']]) {
        $redenen[] = $aanpak === 'soft' && $type === 'deactiveren' ? SM_EXIT_TEKSTEN['aangeraden'] : SM_EXIT_TEKSTEN['past'];
    }

    return [
        'label' => SM_EXIT_AANPAK[$aanpak]['kort'] . ', ' . SM_EXIT_TYPE[$type]['kort'],
        'niveau' => match (true) {
            $type === 'verwijderen' => 'h',
            $zwaar || $aanpak !== 'soft' || $type === 'inactief' => 'm',
            default => 'l',
        },
        'redenen' => $redenen,
    ];
}

const SM_EXIT_TEKSTEN = [
    'platform' => 'Je vertrekt van het hele platform. Informeer eerst de Voorlichtingsraad en de andere '
        . 'rijksorganisaties, en stem samen af.',
    'doel' => 'Het kanaal bereikt zijn doel nog. Weeg goed af of vertrekken nu verstandig is.',
    'zwaar_soft' => 'Andere kanalen vangen het vertrek slecht op, of de impact is groot. Kies liever een soft exit.',
    'zwaar_hybride' => 'Andere kanalen vangen het vertrek slecht op, of de impact is groot. Overweeg de hybride vorm: '
        . 'het account blijft, alleen voor noodsituaties.',
    'verwijderen' => 'Verwijderen wordt sterk afgeraden. Anderen kunnen de naam dan claimen, en privéberichten van '
        . 'burgers kunnen verdwijnen.',
    'aangeraden' => 'Dit is de aanpak die de richtlijn aanraadt.',
    'past' => 'De aanpak past bij de situatie.',
];

/**
 * Het exitplan, in de acht stappen van de richtlijn.
 *
 * @return list<array<int,mixed>>
 */
function sm_exit_document(array $a, array $u): array
{
    $t = static fn (string $key): string => cm_antwoord_tekst($key, $a);
    $vrij = static fn (string $key): string => implode(' ', cm_regels((string) ($a[$key] ?? '')));
    $platform = ($a['sm_exit_scope'] ?? '') === 'platform';
    $type = SM_EXIT_TYPE[(string) $a['sm_exit_type']];
    $naam = $u['kader']['naam'];

    $kop = [
        ['h1', 'Exitplan: ' . ($a['projectnaam'] ?: 'naamloos')],
        ['term', 'Platform', $naam],
        ['term', 'Account', $t('sm_accounttype')],
        ['term', 'Reikwijdte', $t('sm_exit_scope')],
        ['term', 'Stand', $t('sm_exit_besluit')],
        ['term', 'Ingevuld door', (string) ($a['aanvrager'] ?: 'onbekend')],
        ['term', 'Ingevuld op', cm_datum_nl(new DateTimeImmutable('now'))],
    ];
    foreach (['organisatieonderdeel' => 'Organisatie', 'email' => 'Contact', 'sm_directeur' => 'Vastgesteld door'] as $key => $label) {
        if (trim((string) ($a[$key] ?? '')) !== '') {
            $kop[] = ['term', $label, (string) $a[$key]];
        }
    }
    array_push($kop,
        ['term', 'Aanpak', $u['label']],
        ['term', 'Vertrekdatum', (string) $a['sm_exit_datum']],
        ['term', 'Webcare tot', (string) $a['sm_exit_webcare']],
        ['citaat', implode(' ', $u['redenen'])],
    );

    $ciso = in_array('ciso', (array) ($a['sm_adviseurs'] ?? []), true);

    return [
        ...$kop,

        ['h2', '1. Voorbereiding'],
        ['term', 'Reden', $vrij('sm_exit_reden')],
        ['term', 'Doel nog bereikt', $t('sm_exit_doel')],
        ['term', 'Opvang door andere kanalen', $t('sm_exit_opvang')],
        ...(trim((string) ($a['sm_exit_alternatieven'] ?? '')) === '' ? [] : [['term', 'Andere kanalen', $vrij('sm_exit_alternatieven')]]),
        ...($ciso ? [] : [['alinea', 'Vraag de CISO om een risicoanalyse, als dat kan.']]),

        ['h2', '2. Samen optrekken als Rijksoverheid'],
        $platform
            ? ['lijst', [
                'Informeer de Voorlichtingsraad via zijn secretariaat, en deel de overwegingen uit stap 1.',
                'Informeer de andere rijksorganisaties op tijd, deel ontwikkelingen, en stem acties af.',
                'Besluit de Rijksoverheid samen tot vertrek? Voer stap 3 tot en met 7 dan samen uit.',
            ]]
            : ['alinea', 'Je sluit alleen dit account. De richtlijn geldt voor het vertrek van een heel platform, dus '
                . 'afstemming met de Voorlichtingsraad is niet nodig. De stappen hieronder helpen wel.'],

        ['h2', '3. Impact'],
        ['term', 'Impact op burgers, media en stakeholders', $t('sm_exit_impact')],
        ['alinea', $vrij('sm_exit_impact_toelichting')],
        ['lijst', [
            'Bespreek de impact met de directeur Communicatie.',
            'Bekijk in de instellingen van het platform hoe je een account pauzeert, deactiveert of verwijdert. Zoek uit '
                . 'wat dat betekent voor de gegevens van burgers, zoals een vraag die iemand via het platform stelde.',
            'Spreek met je informatiebeheerder af hoe je de gegevens archiveert. Regel ook de inloggegevens: wie kan er '
                . 'later nog bij?',
        ]],

        ['h2', '4. Besluit'],
        ['alinea', 'De directeur Communicatie, of wie daarvoor gemandateerd is, neemt het besluit. Bij twijfel beslist '
            . 'die samen met de Voorlichtingsraad, de SG of de bewindspersoon.'],

        ['h2', '5. Het vertrek'],
        ['term', 'Aanpak', $t('sm_exit_aanpak')],
        ['term', 'Type exit', $t('sm_exit_type')],
        ...(($a['sm_exit_type'] ?? '') === 'verwijderen' ? [['term', 'Waarom verwijderen', $vrij('sm_exit_verwijderen')]] : []),
        ['lijst', [
            'Bepaal wie er intern meedoet, zoals de CIO voor het veiligstellen van de gegevens.',
            'Stel met hen een tijdlijn op, zodat het vertrek stap voor stap en gecontroleerd gaat.',
            'Leg een nieuwe kanaalstrategie vast voor de kanalen die overblijven.',
            'Maak een plan om volgers mee te nemen naar de andere kanalen.',
            'Pas de protocollen voor crisiscommunicatie aan.',
        ]],

        ['h2', '6. Communicatie'],
        ['lijst', [
            'Vertel medewerkers waarom je vertrekt en welke kanalen je nu kiest.',
            'Breng in kaart welke stakeholders je informeert, zoals verbonden organisaties.',
            'Plaats een bericht op ' . $naam . '. Leg uit waarom je vertrekt, noem de datum, zeg tot wanneer er '
                . 'webcare is, en noem waar mensen je voortaan vinden.',
            'Maak woordvoeringslijnen' . ($platform ? ', en stem ze af met de Voorlichtingsraad' : '') . '.',
            'Bereid vragen en antwoorden voor, voor media en voor burgers.',
            'Informeer het team voor burgervragen en webcare op tijd. Laat Informatie Rijksoverheid (1400) informeren '
                . 'via de Dienst Publiek en Communicatie.',
        ]],

        ['h2', '7. Uitvoering'],
        ['lijst', [
            'Volg de reacties op het vertrek, ook op andere platforms en in de media. Pas je communicatie aan als dat '
                . 'nodig is.',
            'Archiveer de gegevens zoals afgesproken, en regel de inloggegevens.',
            'Haal de links naar ' . $naam . ' weg van je sites, ook in de footer, op de contactpagina en op de '
                . 'calamiteitenpagina. Zoek ook naar andere plekken die ernaar verwijzen.',
            'Zet in de bio dat je stopt met publiceren en reageren, en noem het alternatief. Zet bij voorkeur ook een '
                . 'bericht vast bovenaan.',
            $type['uitvoering'],
        ]],

        ['h2', '8. Evaluatie'],
        ['alinea', 'Blijf volgen wat het vertrek betekent voor de doelen en resultaten van de organisatie. De toekomst '
            . 'van platforms is onzeker, dus kijk regelmatig opnieuw naar je kanaalstrategie.'
            . ($platform ? ' Deel je ervaringen met de Voorlichtingsraad.' : '')],
    ];
}
