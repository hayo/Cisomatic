<?php

/**
 * De vragenlijst van de AI scan: eerst de toepassing, dan de Beslishulp
 * AI-verordening van BZK, dan het standpunt en de handreiking over
 * generatieve AI, en tot slot de uitkomst.
 *
 * De beslishulp is een beslisboom. Welke vraag te zien is, volgt uit
 * ai_route() in afleiding.php: een vraag uit de boom heeft toon_als_uitkomst
 * op zijn eigen sleutel. Waar de scan afwijkt van de beslishulp, staat in
 * README.md. Wat de andere sleutels doen, staat in ../README.md.
 */

declare(strict_types=1);

/** Per keuze bij ai_rol de rollen die de AI-verordening daaraan geeft. */
const AI_ROLLEN = [
    'maken' => ['aanbieder'],
    'maken_gebruiken' => ['aanbieder', 'gebruiksverantwoordelijke'],
    'opdracht' => ['aanbieder'],
    'gebruiken' => ['gebruiksverantwoordelijke'],
    'aanpassen' => ['aanbieder', 'gebruiksverantwoordelijke'],
    'importeren' => ['importeur'],
    'doorverkopen' => ['distributeur'],
    'geen' => [],
];

/** De vijf vragen uit het standpunt voor de risicoanalyse, met wat te doen als het antwoord ontbreekt. */
const GAI_AFWEGINGEN = [
    'noodzaak' => [
        'label' => 'Welk probleem de AI oplost, en waarom dat niet anders kan',
        'actie' => 'Leg vast welk probleem de AI oplost, en waarom dat niet anders kan.',
    ],
    'basis' => [
        'label' => 'Of de basis op orde is: kennis van AI, afspraken over wie wat doet, en het beheer van informatie',
        'actie' => 'Zorg dat de basis op orde is. Denk aan kennis van AI, afspraken over wie wat doet, en het beheer '
            . 'van informatie.',
    ],
    'alternatieven' => [
        'label' => 'Welke oplossingen er zijn met minder risico',
        'actie' => 'Bekijk of een oplossing met minder risico ook kan.',
    ],
    'fouten' => [
        'label' => 'Wat jullie doen als de AI fouten maakt, of dingen verzint',
        'actie' => 'Maak een plan voor als de AI fouten maakt of dingen verzint.',
    ],
    'stoppen' => [
        'label' => 'Hoe jullie snel kunnen stoppen als er problemen zijn',
        'actie' => 'Zorg dat jullie snel kunnen stoppen als er problemen zijn. Leg ook vast hoe het werk dan doorgaat.',
    ],
];

/** @return list<array<string,mixed>> */
function ai_secties(): array
{
    return [
        ai_sectie_over(),
        ai_sectie_toepassing(),
        ai_sectie_risico(),
        ai_sectie_standpunt(),
        ai_sectie_handreiking(),
        ai_sectie_uitkomst(),
    ];
}

/** Een ja of nee. @return array<string,array<string,string>> */
function ai_janee(): array
{
    return ['ja' => ['label' => 'Ja'], 'nee' => ['label' => 'Nee']];
}

/**
 * Een vraag uit de beslisboom. Hij is alleen te zien als hij op de route ligt.
 *
 * @param array<string,array<string,string>> $opties
 * @return array<string,mixed>
 */
function ai_boomvraag(string $key, string $label, string $hint, array $opties): array
{
    return [
        'key' => $key,
        'type' => 'radio',
        'label' => $label,
        'verplicht' => true,
        'toon_als_uitkomst' => [$key => ['ja']],
        'opties' => $opties,
    ] + ($hint === '' ? [] : ['hint' => $hint]);
}

/**
 * Een vraag over generatieve AI.
 *
 * @param array<string,array<string,string>> $opties
 * @param array<string,mixed> $extra
 * @return array<string,mixed>
 */
function gai_vraag(string $key, string $label, string $hint, array $opties, array $extra = []): array
{
    return $extra + [
        'key' => $key,
        'type' => 'radio',
        'label' => $label,
        'verplicht' => true,
        'opties' => $opties,
    ] + ($hint === '' ? [] : ['hint' => $hint]);
}

// ---------------------------------------------------------------------------

function ai_sectie_over(): array
{
    return [
        'id' => 'over',
        'titel' => 'Welke toepassing is het?',
        'stap' => 1,
        'intro' => 'Eerst een paar vragen over de toepassing zelf.',
        'vragen' => [
            [
                'key' => 'projectnaam',
                'type' => 'text',
                'label' => 'Hoe heet de toepassing?',
                'hint' => 'Deze naam komt in het rapport en in de bestandsnaam.',
                'verplicht' => true,
            ],
            [
                'key' => 'ai_omschrijving',
                'type' => 'textarea',
                'label' => 'Wat doet de toepassing, en waarvoor is hij?',
                'hint' => 'Beschrijf het in een paar zinnen. Noem ook wie hem gebruikt.',
                'verplicht' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'ai_generatief',
                'type' => 'radio',
                'label' => 'Gebruikt de toepassing generatieve AI?',
                'hint' => 'Dat is AI die tekst, beeld, geluid of code maakt. Denk aan ChatGPT, Copilot of Le Chat.',
                'verplicht' => true,
                'opties' => ai_janee(),
            ],
            [
                'key' => 'aanvrager',
                'type' => 'text',
                'label' => 'Wie vult de scan in?',
                'hint' => 'Naam en functie.',
                'verplicht' => true,
            ],
            [
                'key' => 'organisatieonderdeel',
                'type' => 'text',
                'label' => 'Voor welke organisatie of afdeling is de scan?',
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

/** Vraag 1.1 tot en met 1.6 van de beslishulp: geldt de AI-verordening? */
function ai_sectie_toepassing(): array
{
    $rollen = [
        'maken' => ['label' => 'Wij maken hem, maar gebruiken hem niet zelf'],
        'maken_gebruiken' => ['label' => 'Wij maken hem, en gebruiken hem ook zelf'],
        'opdracht' => ['label' => 'Een andere partij maakt hem, in onze opdracht',
            'hint' => 'Jullie zijn dan de aanbieder. Dat is anders als die partij hem onder haar eigen naam verkoopt.'],
        'gebruiken' => ['label' => 'Wij gebruiken een toepassing van een ander, zonder aanpassingen'],
        'aanpassen' => ['label' => 'Wij gebruiken een toepassing van een ander, en passen hem aan',
            'hint' => 'Een grote aanpassing maakt jullie ook aanbieder, bijvoorbeeld als het doel verandert.'],
        'importeren' => ['label' => 'Wij halen hem bij een leverancier buiten de EU, en brengen hem hier op de markt'],
        'doorverkopen' => ['label' => 'Wij kopen hem bij een leverancier in de EU, en verkopen hem door'],
        'geen' => ['label' => 'Geen van deze'],
    ];

    $soorten = [
        'systeem' => ['label' => 'Een AI systeem', 'hint' => 'Het leert van data, of redeneert met de kennis van '
            . 'experts. Denk aan beeldherkenning, of een model dat een risico voorspelt.'],
        'gpai_systeem' => ['label' => 'Een AI systeem voor algemene doeleinden', 'hint' => 'Het kan veel soorten '
            . 'taken aan. Denk aan ChatGPT, Copilot of Le Chat.'],
        'model' => ['label' => 'Alleen een AI model voor algemene doeleinden', 'hint' => 'Bijvoorbeeld een '
            . 'taalmodel dat jullie zelf trainen of aanbieden, zonder toepassing eromheen.'],
        'geen' => ['label' => 'Geen van deze', 'hint' => 'Bijvoorbeeld vaste rekenregels die mensen hebben bedacht, of '
            . 'een dashboard met cijfers.'],
        'twijfel' => ['label' => 'Dat weet ik niet', 'hint' => 'Dan volgen er nog hooguit drie korte vragen.'],
    ];

    return [
        'id' => 'toepassing',
        'titel' => 'Geldt de AI verordening?',
        'stap' => 1,
        'intro' => 'Deze vragen komen uit de beslishulp. Na elk antwoord verschijnt de volgende vraag die voor jullie '
            . 'geldt.',
        'vragen' => [
            ai_boomvraag('ai_algoritme', 'Zit er een algoritme in de toepassing?', 'Een algoritme is een reeks '
                . 'stappen die een computer volgt. Zo komt hij tot een uitkomst, zoals een berekening of een advies.',
                ai_janee()),
            ai_boomvraag('ai_rol', 'Wie maakt de toepassing, en wie gebruikt hem?', 'De AI verordening kent twee '
                . 'hoofdrollen. De aanbieder maakt een systeem, en de gebruiksverantwoordelijke gebruikt het.', $rollen),
            ai_boomvraag('ai_status', 'Is de toepassing al in gebruik?', 'Ook een proef telt als gebruik, als die '
                . 'echte gevolgen heeft.', [
                    'gebruik' => ['label' => 'Ja, hij is al in gebruik'],
                    'ontwikkeling' => ['label' => 'Nee, hij is nog in ontwikkeling'],
                ]),
            ai_boomvraag('ai_soort', 'Wat voor toepassing is het?', 'De AI verordening kent drie soorten. Kies wat '
                . 'het best past.', $soorten),
            ai_boomvraag('ai_regels', 'Werkt de toepassing alleen met vaste regels die mensen hebben bedacht?',
                'Bijvoorbeeld de regel: wie minder verdient dan een grens, krijgt toeslag. Zulke regels komen niet uit '
                . 'data.', ai_janee()),
            ai_boomvraag('ai_inferentie', 'Leidt de toepassing zelf iets af uit data?', 'Bijvoorbeeld een '
                . 'voorspelling, een score of een indeling in groepen. Een gemiddelde uitrekenen telt niet.',
                ai_janee()),
            ai_boomvraag('ai_omgeving', 'Heeft de uitkomst gevolgen in de echte wereld?', 'Dat is zo als er een '
                . 'besluit of een actie op volgt.', ai_janee()),
            ai_boomvraag('ai_uitzondering', 'Is de toepassing alleen voor een van deze doelen?', 'Dan geldt de AI '
                . 'verordening niet (artikel 2). Testen met echte gevolgen is geen uitzondering.', [
                    'defensie' => ['label' => 'Defensie, het leger of de nationale veiligheid'],
                    'wetenschap' => ['label' => 'Wetenschappelijk onderzoek'],
                    'test' => ['label' => 'Onderzoek of testen, voordat de toepassing in gebruik gaat'],
                    'internationaal' => ['label' => 'Samenwerking van een land buiten de EU met de EU, bij politie '
                        . 'of justitie', 'hint' => 'Een overheid buiten de EU gebruikt het systeem dan.'],
                    'nee' => ['label' => 'Nee, geen van deze'],
                ]),
            [
                'key' => 'ai_afgeleid_toepassing',
                'type' => 'afgeleid',
                'bron' => 'toepassing',
                'label' => 'Geldt de AI verordening?',
                'groepen' => ai_model(),
            ],
        ],
    ];
}

/** Vraag 2.1 tot en met 2.11 van de beslishulp: de risicogroep. */
function ai_sectie_risico(): array
{
    return [
        'id' => 'risico',
        'titel' => 'In welke risicogroep valt het?',
        'stap' => 2,
        'toon_als_uitkomst' => ['risico' => ['ja']],
        'intro' => 'De AI verordening deelt toepassingen in naar hun risico. Hoe hoger het risico, hoe meer regels '
            . 'er gelden.',
        'vragen' => [
            ai_boomvraag('ai_systeemrisico', 'Is het model een van de allergrootste?', 'Het gaat om modellen die bij '
                . 'de training meer dan 10²⁵ rekenstappen nodig hadden (FLOPs). De AI verordening spreekt dan van een '
                . 'systeemrisico.', ai_janee()),
            [
                'key' => 'ai_toepassing',
                'type' => 'checkbox',
                'label' => 'Waar raakt de toepassing aan?',
                'hint' => 'Vink alles aan wat kan gelden. Niets aanvinken betekent: geen van deze.',
                'toon_als_uitkomst' => ['ai_toepassing' => ['ja']],
                'voorstel' => true,
                'opties' => ai_toepassingen(),
            ],
            ai_boomvraag('ai_verboden_uitz', 'Geldt er een uitzondering op dit verbod?', 'Die zijn er alleen voor '
                . 'de politie en justitie.', [
                    'misdrijf' => ['label' => 'Ja, bij de opsporing van een ernstig misdrijf',
                        'hint' => 'Zoals terrorisme, mensenhandel, moord of ontvoering.'],
                    'slachtoffers' => ['label' => 'Ja, bij het zoeken naar slachtoffers of vermiste personen',
                        'hint' => 'Of om een acute dreiging af te wenden, zoals een aanslag.'],
                    'nee' => ['label' => 'Nee'],
                ]),
            ai_boomvraag('ai_bijlage1', 'Valt het systeem onder de Europese regels voor veilige producten?', 'Het '
                . 'systeem is dan zelf zo’n product, of het zorgt voor de veiligheid ervan. Die producten staan in '
                . 'bijlage I van de AI verordening.', [
                    'a' => ['label' => 'Ja, zoals machines, speelgoed, liften of medische hulpmiddelen'],
                    'b' => ['label' => 'Ja, zoals auto’s, vliegtuigen, schepen of treinen'],
                    'nee' => ['label' => 'Nee'],
                ]),
            ai_boomvraag('ai_derde', 'Moet een onafhankelijke instantie het product keuren?', 'De regels voor het '
                . 'product zeggen of dat moet. Zo’n instantie heet een aangemelde instantie.', ai_janee()),
            ai_boomvraag('ai_profilering', 'Maakt het systeem profielen van mensen?', 'Het systeem beoordeelt dan '
                . 'persoonlijke dingen, zoals werk, geld, gezondheid of gedrag. Dan is er altijd een hoog risico.',
                ai_janee()),
            ai_boomvraag('ai_taak', 'Doet het systeem alleen een kleine taak, zonder groot risico voor mensen?', 'Dan '
                . 'geldt het hoge risico niet (artikel 6, lid 3).', [
                    'procedure' => ['label' => 'Ja, een beperkte taak in een procedure'],
                    'verbeteren' => ['label' => 'Ja, het verbetert werk dat een mens al heeft afgerond'],
                    'patronen' => ['label' => 'Ja, het zoekt afwijkingen in eerdere besluiten',
                        'hint' => 'Een mens beoordeelt die afwijkingen daarna zelf.'],
                    'voorbereiden' => ['label' => 'Ja, het bereidt een beoordeling voor'],
                    'nee' => ['label' => 'Nee'],
                ]),
            ai_boomvraag('ai_opensource', 'Is de toepassing open source?', 'Iedereen mag de code en de parameters dan '
                . 'zien, gebruiken en aanpassen. De parameters zijn wat het model heeft geleerd.', ai_janee()),
            ai_boomvraag('ai_transparantie_uitz', 'Mag de toepassing volgens de wet worden gebruikt voor de opsporing '
                . 'van strafbare feiten?', 'Dan hoeven jullie niet te vertellen dat het AI is.', ai_janee()),
            [
                'key' => 'ai_afgeleid_risico',
                'type' => 'afgeleid',
                'bron' => 'risico',
                'label' => 'De risicogroep',
            ],
        ],
    ];
}

/** De voorwaarden uit het overheidsbrede standpunt over generatieve AI. */
function ai_sectie_standpunt(): array
{
    $afwegingen = array_map(static fn (array $a): array => ['label' => $a['label']], GAI_AFWEGINGEN);

    return [
        'id' => 'standpunt',
        'titel' => 'Mag het volgens het standpunt?',
        'stap' => 3,
        'toon_als' => ['ai_generatief' => ['ja']],
        'intro' => 'De overheid heeft een standpunt over generatieve AI. Daarin staat wanneer je die AI mag inkopen, '
            . 'maken of gebruiken. Deze vragen lopen de voorwaarden langs.',
        'vragen' => [
            gai_vraag('gai_voorwaarden', 'Hoe gebruiken jullie de AI?', '', [
                'consument' => ['label' => 'Met een gewoon account, zonder contract', 'hint' => 'Bijvoorbeeld een '
                    . 'account van één medewerker. Dan gelden de voorwaarden voor consumenten.'],
                'contract' => ['label' => 'Met een contract tussen de organisatie en de leverancier', 'hint' => 'Bijvoorbeeld '
                    . 'een zakelijke licentie, of AI in software die jullie al gebruiken.'],
                'eigen' => ['label' => 'In eigen beheer, zonder leverancier', 'hint' => 'Bijvoorbeeld een open model op '
                    . 'een eigen server.'],
            ]),
            gai_vraag('gai_verkenning', 'Is het alleen een verkenning, in een aparte testomgeving?', 'Het standpunt '
                . 'staat dat alleen toe zonder gevoelige of vertrouwelijke gegevens.', [
                    'ja' => ['label' => 'Ja, en zonder gevoelige of vertrouwelijke gegevens'],
                    'nee' => ['label' => 'Nee'],
                ], ['toon_als' => ['gai_voorwaarden' => ['consument']]]),
            gai_vraag('gai_afspraken', 'Wat staat er in het contract over jullie gegevens?', 'Het gaat om drie dingen. '
                . 'Deelt de leverancier jullie gegevens? Hoe lang bewaart hij ze? En traint hij de AI met wat jullie '
                . 'invoeren?', [
                    'alles' => ['label' => 'Afspraken over alle drie'],
                    'deels' => ['label' => 'Afspraken over een deel ervan'],
                    'nee' => ['label' => 'Geen afspraken, of dat weet ik niet'],
                ], ['toon_als' => ['gai_voorwaarden' => ['contract']]]),
            gai_vraag('gai_persoonsgegevens', 'Komen er persoonsgegevens in de AI?', 'Denk ook aan de vragen die mensen '
                . 'intypen, en aan de bestanden die ze toevoegen.', [
                    'nee' => ['label' => 'Nee'],
                    'dpia' => ['label' => 'Ja, en er is een DPIA of een prescan DPIA'],
                    'ja' => ['label' => 'Ja, maar er is nog geen DPIA'],
                ]),
            gai_vraag('gai_risicoanalyse', 'Is er een risicoanalyse gedaan?', 'Bijvoorbeeld een IAMA of een AI Impact '
                . 'Assessment (AIIA). Een eigen methode mag ook.', [
                    'ja' => ['label' => 'Ja'],
                    'bezig' => ['label' => 'Die loopt nog'],
                    'nee' => ['label' => 'Nee'],
                ]),
            gai_vraag('gai_team', 'Wie hebben meegedacht over de risico’s?', '', [
                'gemengd' => ['label' => 'Een gemengde groep', 'hint' => 'Bijvoorbeeld een jurist, de privacy officer, '
                    . 'de CISO en mensen die de AI gaan gebruiken.'],
                'klein' => ['label' => 'Een of twee mensen'],
                'niemand' => ['label' => 'Nog niemand'],
            ]),
            [
                'key' => 'gai_afwegingen',
                'type' => 'checkbox',
                'label' => 'Wat hebben jullie al besproken en vastgelegd?',
                'hint' => 'Het standpunt noemt deze vijf punten voor de risicoanalyse. Vink aan wat klaar is.',
                'opties' => $afwegingen,
            ],
            gai_vraag('gai_besluit', 'Wie beslist of de AI er komt?', '', [
                'verantwoordelijke' => ['label' => 'De verantwoordelijke manager of bestuurder, na advies',
                    'hint' => 'Advies van onder meer de CISO, de CIO en de functionaris gegevensbescherming (FG).'],
                'zonder_advies' => ['label' => 'De verantwoordelijke, maar zonder die adviezen'],
                'onbekend' => ['label' => 'Dat is nog niet geregeld'],
            ]),
            gai_vraag('gai_beleid', 'Past de AI in het beleid van de organisatie?', 'Het standpunt vraagt dat. Veel '
                . 'organisaties maken een eigen versie van de handreiking.', [
                    'ja' => ['label' => 'Ja'],
                    'geen' => ['label' => 'Er is nog geen beleid voor AI'],
                    'nee' => ['label' => 'Nee, of dat weten we niet'],
                ]),
        ],
    ];
}

/** De belangrijkste tips uit de overheidsbrede handreiking over generatieve AI. */
function ai_sectie_handreiking(): array
{
    return [
        'id' => 'handreiking',
        'titel' => 'Is het goed geregeld?',
        'stap' => 3,
        'toon_als' => ['ai_generatief' => ['ja']],
        'intro' => 'De handreiking van BZK geeft tips voor goed gebruik van generatieve AI. Hier gaat het om de '
            . 'belangrijkste tips.',
        'vragen' => [
            gai_vraag('gai_kennis', 'Krijgen de gebruikers uitleg of training over AI?', 'De AI verordening vraagt '
                . 'dat sinds februari 2025.', [
                    'ja' => ['label' => 'Ja'],
                    'gepland' => ['label' => 'Dat komt nog'],
                    'nee' => ['label' => 'Nee'],
                ]),
            gai_vraag('gai_mens', 'Kijkt een mens na wat de AI maakt?', 'Generatieve AI kan fouten maken, en soms '
                . 'verzint het dingen.', [
                    'altijd' => ['label' => 'Ja, altijd, voordat het wordt gebruikt'],
                    'soms' => ['label' => 'Soms'],
                    'nee' => ['label' => 'Nee, de AI werkt zelfstandig'],
                ]),
            gai_vraag('gai_besluiten', 'Helpt de AI bij besluiten over mensen?', '', [
                'nee' => ['label' => 'Nee'],
                'hulp' => ['label' => 'Ja, als hulp. Een mens neemt het besluit'],
                'zelf' => ['label' => 'Ja, de AI neemt zelf het besluit'],
            ]),
            gai_vraag('gai_burgers', 'Praat de AI met burgers?', '', [
                'nee' => ['label' => 'Nee, alleen met medewerkers'],
                'geregeld' => ['label' => 'Ja. Ze weten dat het AI is, en ze kunnen ook een mens spreken'],
                'ja' => ['label' => 'Ja, maar dat is nog niet geregeld'],
            ]),
            gai_vraag('gai_model', 'Wat voor model zit erin?', 'Open betekent: iedereen mag het model zien en '
                . 'gebruiken. De handreiking raadt een open model uit Europa aan.', [
                    'open_eu' => ['label' => 'Een open model uit Europa'],
                    'eu' => ['label' => 'Een gesloten model uit Europa'],
                    'open' => ['label' => 'Een open model van buiten Europa'],
                    'gesloten' => ['label' => 'Een gesloten model van buiten Europa', 'hint' => 'Zoals GPT of Gemini.'],
                    'onbekend' => ['label' => 'Dat weet ik niet'],
                ]),
        ],
    ];
}

function ai_sectie_uitkomst(): array
{
    return [
        'id' => 'uitkomst',
        'titel' => 'De uitkomst',
        'stap' => 4,
        'intro' => 'Hier zie je de uitkomst in het kort. Het rapport zet op een rij wat jullie moeten doen.',
        'vragen' => [
            [
                'key' => 'ai_afgeleid_uitkomst',
                'type' => 'afgeleid',
                'bron' => 'uitkomst',
                'label' => 'De uitkomst van de scan',
            ],
            [
                'key' => 'ai_opmerkingen',
                'type' => 'textarea',
                'label' => 'Wil je nog iets toelichten?',
                'hint' => 'Bijvoorbeeld een twijfel bij een antwoord, of een afspraak die nog komt.',
                'hoogte' => 3,
            ],
        ],
    ];
}

// ---------------------------------------------------------------------------

/**
 * Waar een AI systeem aan kan raken. Dezelfde sleutels als in de eerste
 * quickscan, zodat een bestand daarvan dit alvast aanvinkt. Drie verboden en de
 * emotieherkenning staan alleen hier, want de beslishulp noemt ze wel. Het
 * begin van de sleutel geeft de groep.
 *
 * @return array<string,array<string,string>>
 */
function ai_toepassingen(): array
{
    $verboden = static fn (string $label, string $hint = ''): array
        => ['groep' => 'Verboden', 'label' => $label] + ($hint === '' ? [] : ['hint' => $hint]);
    $hoog = static fn (string $label): array => ['groep' => 'Hoog risico', 'label' => $label];
    $transparantie = static fn (string $label): array => ['groep' => 'Transparantie', 'label' => $label];

    return [
        'verboden_scoring' => $verboden('Mensen beoordelen of scoren op hun sociale gedrag', 'Met nadelige gevolgen op '
            . 'een ander terrein dan waar de gegevens vandaan komen.'),
        'verboden_emotie' => $verboden('Emoties herkennen op de werkvloer of in het onderwijs', 'Voor medische redenen '
            . 'of voor de veiligheid mag het wel.'),
        'verboden_biometrie' => $verboden('Mensen op basis van biometrie indelen naar ras, geloof, politieke '
            . 'opvatting of seksuele geaardheid', 'Biometrische data ordenen die volgens de wet zijn verkregen, mag wel.'),
        'verboden_realtime' => $verboden('Mensen in de openbare ruimte live biometrisch identificeren'),
        'verboden_manipulatie' => $verboden('Gedrag onbewust sturen, of inspelen op leeftijd, beperking of een '
            . 'kwetsbare positie'),
        'verboden_voorspelling' => $verboden('Voorspellen of iemand een misdrijf pleegt, alleen op basis van een '
            . 'profiel of persoonlijke kenmerken', 'Het mag wel als het een mens helpt die oordeelt op basis van feiten '
            . 'over een misdrijf.'),
        'verboden_gezichten' => $verboden('Een databank met gezichten maken, met foto’s van internet of camera’s'),
        'verboden_naakt' => $verboden('Naaktbeelden maken van echte mensen, zonder hun toestemming'),
        'hoog_onderwijs' => $hoog('Toegang tot onderwijs, of het beoordelen van deelnemers'),
        'hoog_werk' => $hoog('Werving, selectie, beoordeling of ontslag van personeel'),
        'hoog_diensten' => $hoog('Toegang tot uitkeringen, subsidies of andere essentiële voorzieningen'),
        'hoog_rechtshandhaving' => $hoog('Rechtshandhaving, opsporing of risicobeoordeling van personen'),
        'hoog_migratie' => $hoog('Migratie, asiel of grenscontrole'),
        'hoog_rechtspraak' => $hoog('Ondersteuning van rechtspraak, of van verkiezingen en democratische processen'),
        'hoog_infrastructuur' => $hoog('Beheer of bediening van kritieke infrastructuur'),
        'hoog_biometrie' => $hoog('Biometrische identificatie of categorisering in andere gevallen'),
        'transparantie_interactie' => $transparantie('Het systeem praat rechtstreeks met mensen, bijvoorbeeld als '
            . 'chatbot'),
        'transparantie_generatie' => $transparantie('Het systeem maakt of bewerkt tekst, beeld, audio of video'),
        'transparantie_emotie' => $transparantie('Het systeem herkent emoties, of deelt mensen in op basis van '
            . 'biometrie'),
    ];
}
