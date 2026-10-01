<?php

/**
 * De vragenlijst van het CIO oordeel: eerst het onderzoek, dan elf
 * aandachtsgebieden, dan het oordeel.
 *
 * De gebieden 1 tot en met 9 en hun toetsaspecten komen uit het toetskader
 * projecten 2026 van het Adviescollege ICT-toetsing, in eenvoudiger woorden.
 * Gebied 10 en 11 zijn een eigen uitwerking van de twee gebieden die het
 * Kwaliteitskader CIO-oordelen toevoegt; een derde element 'eigen' markeert
 * die aspecten, en het losse aspect over digitale toegankelijkheid. Elk
 * aspect wordt een regel die openklapt, met een oordeel, een bevinding en een
 * advies. Een snelle keuze per gebied stelt voor om elk aspect op orde te
 * zetten; CO_TOEPASSING verbergt wat niet geldt.
 */

declare(strict_types=1);

/**
 * De keuzes per toetsaspect. 'niveau' kleurt de regel en telt voor het
 * risico van het gebied; null telt niet mee.
 */
const CO_KEUZES = [
    'orde' => ['label' => 'Op orde', 'niveau' => 'l'],
    'aandacht' => ['label' => 'Aandachtspunt', 'niveau' => 'm', 'hint' => 'Het kan beter, maar het project kan door.'],
    'risico' => ['label' => 'Risico', 'niveau' => 'h', 'hint' => 'Dit bedreigt het succes van het project.'],
    'onbekend' => ['label' => 'Niet te beoordelen', 'niveau' => 'm',
        'hint' => 'De stukken en de gesprekken geven geen antwoord. Dat telt als aandachtspunt.'],
    'nvt' => ['label' => 'Niet van toepassing', 'niveau' => null],
];

/**
 * De elf aandachtsgebieden. 'aspecten' staat per deel van het gebied; een
 * aspect is [tekst, hint] of [tekst, hint, 'eigen'].
 */
const CO_GEBIEDEN = [
    'businesscase' => [
        'naam' => 'Business case, baten en financiering', 'kort' => 'business case', 'stap' => 2,
        'intro' => 'Waarom is het project nodig, en wat levert het op? Een goede business case laat zien dat het '
            . 'project de investering waard is. Zo kan de opdrachtgever er ook later op sturen.',
        'aspecten' => [
            'Business case' => [
                ['De aanleiding, het probleem en het doel van het project zijn duidelijk.', ''],
                ['De business case laat zien wat het project oplevert voor de organisatie en voor wie ermee werkt.', ''],
                ['Er zijn andere oplossingen uitgewerkt, onderbouwd en tegen elkaar afgewogen.', ''],
                ['Het project heeft onderzocht wat de oplossing betekent voor uitvoeringsorganisaties, burgers en '
                    . 'bedrijven.', ''],
                ['Het project heeft de gevolgen voor de strategische digitale veiligheid meegewogen.',
                    'Bijvoorbeeld: van welke landen en leveranciers wordt het Rijk afhankelijk?'],
                ['De business case is actueel, en wordt gebruikt bij besluiten.', ''],
                ['De business case houdt aantoonbaar rekening met wetten en regels.', ''],
            ],
            'Baten' => [
                ['Baten die te tellen zijn, staan in euro’s.', ''],
                ['De andere baten zijn zo beschreven dat je later kunt nagaan of ze er zijn.', ''],
                ['Het is duidelijk wie verantwoordelijk is voor het halen van de baten.', ''],
                ['Die persoon kan ook sturen op het halen van de baten.', ''],
            ],
            'Financiering' => [
                ['Er is geld voor de hele looptijd van het project, en voor het beheer daarna.', ''],
                ['Er is genoeg geld over voor wijzigingen tijdens het project.', ''],
            ],
        ],
    ],
    'opdrachtgever' => [
        'naam' => 'Opdrachtgever en projectorganisatie', 'kort' => 'opdrachtgever', 'stap' => 2,
        'intro' => 'Een project heeft één opdrachtgever nodig die beslist en die weet wat er speelt. Het team moet de '
            . 'kennis en de ruimte hebben om het werk goed te doen.',
        'aspecten' => [
            'Opdrachtgever' => [
                ['De opdrachtgever is verantwoordelijk voor het budget en de business case.', ''],
                ['De opdrachtgever denkt inhoudelijk mee over de grote keuzes.', ''],
                ['De opdrachtgever draagt de gevolgen van grote wijzigingen in het project.', ''],
                ['De opdrachtgever weet inhoudelijk hoe het project ervoor staat.', ''],
            ],
            'Organisatie' => [
                ['De rollen, taken, verantwoordelijkheden en bevoegdheden in het project staan op papier.', ''],
                ['Het projectteam heeft genoeg kennis en ervaring.', ''],
                ['Mensen in het project praten open met elkaar, en werken goed samen.', ''],
                ['Er is structureel ruimte voor tegenspraak en een kritische blik.', ''],
                ['Het project heeft de bewaking van de kwaliteit geregeld.', ''],
                ['Het project heeft bepaald wanneer interne en externe toetsen plaatsvinden.',
                    'Bij belangrijke mijlpalen en producten.'],
            ],
        ],
    ],
    'risico' => [
        'naam' => 'Risico’s en afhankelijkheden', 'kort' => 'risico’s', 'stap' => 2,
        'intro' => 'Elk project loopt risico’s. Het gaat erom dat het project ze kent, er iets aan doet, en blijft '
            . 'kijken of dat werkt. Ook andere partijen kunnen het project vertragen.',
        'aspecten' => [
            'Risico’s' => [
                ['Het project heeft risicobeheer vast in zijn werk opgenomen.', ''],
                ['Het project neemt de actuele dreigingen mee in zijn risicoanalyses.',
                    'Bijvoorbeeld het dreigingsbeeld van de NCTV of de AIVD.'],
                ['Het project controleert of de maatregelen tegen risico’s ook werken.', ''],
                ['Mensen in het project zijn open over risico’s.', ''],
            ],
            'Afhankelijkheden' => [
                ['Het project heeft grip op de belangrijkste afhankelijkheden.', ''],
                ['Het project weet welke partijen betrokken zijn, en wat hun belangen zijn.', ''],
            ],
        ],
    ],
    'processen' => [
        'naam' => 'Werkprocessen en ICT', 'kort' => 'werkprocessen', 'stap' => 3,
        'intro' => 'Een systeem werkt alleen goed als het werk er ook bij past. Daarom horen het werk en het systeem '
            . 'samen ontworpen en getest te worden, vanuit de burger.',
        'aspecten' => [
            '' => [
                ['De werkprocessen en de ICT oplossing passen bij elkaar.', ''],
                ['Werkprocessen en ICT oplossing worden samen uitgewerkt en getest.', ''],
                ['De werkprocessen zijn ontworpen vanuit de burger.', ''],
                ['Burgers en bedrijven kunnen volgen hoe een besluit tot stand komt.', ''],
                ['Mensen kunnen ingrijpen als het systeem zelf een besluit neemt.',
                    'Zo is maatwerk mogelijk, als de regel niet past bij iemands situatie.'],
                ['Bij het ontwerpen of wijzigen van een werkproces kijkt het project ook naar mogelijke aanvallen.', ''],
                ['Het project heeft aandacht voor de continuïteit van het werk en de ICT samen.', ''],
                ['Het systeem voldoet aan de eisen voor digitale toegankelijkheid.',
                    'Voor de overheid is dat de norm EN 301 549, met de WCAG. Dit aspect staat niet in het toetskader.',
                    'eigen'],
            ],
        ],
    ],
    'scope' => [
        'naam' => 'Scope', 'kort' => 'scope', 'stap' => 3,
        'intro' => 'De scope zegt wat het project wel en niet doet. Een grote of groeiende scope maakt een project '
            . 'riskant. Daarom begint een project klein, en verandert de scope alleen met een bewust besluit.',
        'aspecten' => [
            'Bij de start' => [
                ['De scope beschrijft welke resultaten het project oplevert.', ''],
                ['Wordt het project te groot of te ingewikkeld, dan wordt het opgedeeld.', ''],
            ],
            'Tijdens het project' => [
                ['De opdrachtgever mag de scope na de start wijzigen.', ''],
                ['Elke wijziging in de scope is terug te vinden. Een wijziging gaat pas door als alle gevolgen bekend '
                    . 'zijn.', ''],
            ],
        ],
    ],
    'architectuur' => [
        'naam' => 'Architectuur en techniek', 'kort' => 'architectuur',
        'stap' => 3,
        'intro' => 'Kan het systeem wat het moet kunnen, en is het te bouwen? De architectuur moet passen bij de rest '
            . 'van het ICT landschap. De techniek moet bewezen zijn, of eerst klein getest worden.',
        'aspecten' => [
            'Eisen' => [
                ['De belangrijkste eisen zijn getoetst. Dat geldt voor de functies en voor eisen als snelheid en '
                    . 'beschikbaarheid.', ''],
                ['Elke eis is terug te vinden in wat daarna is ontworpen en gebouwd.', ''],
                ['Privacy, informatiebeveiliging en veiligheid krijgen vanaf het begin structureel aandacht.', ''],
                ['Het project heeft getoetst of de functies haalbaar zijn en passen bij het werk.', ''],
            ],
            'Architectuur' => [
                ['Het project hergebruikt eerst wat er al is. Pas daarna koopt of bouwt het iets.', ''],
                ['Het project gebruikt aantoonbaar de standaarden die ervoor gelden.',
                    'Zoals de open standaarden van het Forum Standaardisatie: pas toe of leg uit.'],
                ['De architectuur past bij de oplossing, en bij de rest van het ICT landschap.',
                    'Bijvoorbeeld de NORA en de architectuur van het ministerie.'],
                ['Het project heeft de voordelen en nadelen van generieke onderdelen afgewogen.',
                    'Zoals voorzieningen van Logius of van een shared service organisatie.'],
                ['De oplossing bestaat uit delen die apart op te leveren en te testen zijn.', ''],
                ['De koppelingen met andere systemen zijn beschreven.', ''],
                ['De oplossing maakt een rustige overgang in stappen naar de nieuwe situatie mogelijk.', ''],
                ['De omvang van de oplossing is ongeveer bekend. Bij een wijziging wordt die opnieuw bepaald.', ''],
            ],
            'Techniek' => [
                ['De oplossing gebruikt waar het kan gangbare en bewezen techniek.', ''],
                ['De betrokken partijen hebben genoeg ervaring met de gekozen techniek.', ''],
                ['Nieuwe techniek is eerst klein en gecontroleerd getest, voordat die breed wordt ingezet.', ''],
            ],
        ],
    ],
    'realisatie' => [
        'naam' => 'Realisatie en planning', 'kort' => 'planning', 'stap' => 4,
        'intro' => 'Hoe bouwt het project, en klopt de planning? Korte rondes met eindgebruikers geven snel '
            . 'zicht op wat werkt. Een planning is pas betrouwbaar als ze onderbouwd en afgestemd is.',
        'aspecten' => [
            'Realisatie' => [
                ['Een ronde van ontwerpen, bouwen en testen duurt zo kort mogelijk.', ''],
                ['Eindgebruikers zijn nauw betrokken bij het bouwen.', ''],
                ['De manier van ontwikkelen past bij het project, en het team heeft er genoeg ervaring mee.', ''],
                ['Voor alle producten en delen daarvan zijn de eisen aan de kwaliteit bekend.', ''],
                ['Het testen volgt uit een risicoanalyse.', ''],
            ],
            'Planning' => [
                ['De planning onderbouwt hoeveel tijd elk deel van het werk kost.', ''],
                ['De planning is afgestemd met de betrokken partijen.', ''],
            ],
        ],
    ],
    'sourcing' => [
        'naam' => 'Sourcing', 'kort' => 'sourcing', 'stap' => 4,
        'intro' => 'Doet het Rijk het werk zelf, of doet een marktpartij het? Die keuze bepaalt risico’s, kosten en '
            . 'afhankelijkheid. Ook de aanbesteding en het contract horen daarbij.',
        'aspecten' => [
            '' => [
                ['De keuze om het zelf te doen, uit te besteden of allebei is te volgen, en objectief onderbouwd.', ''],
                ['Bij uitbesteden zijn de risico’s goed verdeeld tussen opdrachtgever en marktpartij.', ''],
                ['Bij uitbesteden houdt het project expliciet rekening met risico’s voor de strategische digitale '
                    . 'veiligheid.', 'De soevereiniteitsscan helpt daarbij.'],
                ['De aanbesteding past bij de sourcingstrategie, en bij de regels.', ''],
                ['De aanbesteding verkleint het risico dat het Rijk te afhankelijk wordt van een marktpartij.', ''],
                ['Er is een exitplan dat past, en dat uitvoerbaar is. Het geldt als de uitbesteding stopt of verandert.',
                    ''],
                ['De aanbesteding past bij de omvang en de complexiteit van de opdracht.', ''],
                ['De aanbesteding geeft duidelijke kaders voor de overgang en voor het beheer.', ''],
                ['De aanbesteding is vooraf in de markt getoetst.', 'Bijvoorbeeld met een marktconsultatie.'],
                ['De gunningscriteria zijn duidelijk, en wegen kwaliteit, prijs en tijd tegen elkaar af.', ''],
                ['Het contract geeft een helder kader voor de samenwerking, en helpt om afspraken na te komen.', ''],
                ['De aanbesteding past bij hoeveel regie de organisatie kan voeren, en bij hoe ervaren ze is.', ''],
            ],
        ],
    ],
    'beheer' => [
        'naam' => 'Acceptatie, invoering en beheer', 'kort' => 'beheer', 'stap' => 4,
        'intro' => 'Een project is pas klaar als de lijn het systeem gebruikt en beheert. Daarvoor zijn afspraken '
            . 'nodig over acceptatie, over wie het beheer overneemt, en over wanneer het project stopt.',
        'aspecten' => [
            '' => [
                ['Het project regelt dat de lijnorganisatie de oplossing gaat gebruiken.', ''],
                ['Het is duidelijk afgesproken wie de oplossing gaat beheren.', ''],
                ['De eisen voor acceptatie en de manier van accepteren zijn beschreven.', ''],
                ['Na de oplevering volgt een periode van nazorg.', ''],
                ['Het is beschreven wanneer het project klaar is en kan stoppen.',
                    'Dat heet ook wel de decharge van het project.'],
            ],
        ],
    ],
    'beveiliging' => [
        'naam' => 'Informatiebeveiliging en privacy', 'kort' => 'beveiliging en privacy', 'stap' => 5,
        'intro' => 'Het Kwaliteitskader CIO-oordelen voegt dit gebied toe aan het toetskader. De aspecten hieronder '
            . 'zijn een eigen uitwerking, op de BIO2, de AVG en het cloudbeleid van het Rijk.',
        'aspecten' => [
            '' => [
                ['Het project heeft bepaald hoe goed de informatie beveiligd moet worden.',
                    'Bijvoorbeeld met een BIA, of met quickscan 2.0 op de BIO2.', 'eigen'],
                ['De maatregelen uit de BIO2 zijn bepaald, en staan in de plannen en in de planning.', '', 'eigen'],
                ['De CISO heeft geadviseerd, en de lijn heeft de risico’s aanvaard die overblijven.', '', 'eigen'],
                ['Het project heeft getoetst of een DPIA nodig is. Waar dat nodig is, is die er, met advies van de FG.',
                    '', 'eigen'],
                ['Het gebruik van de cloud past bij het cloudbeleid van het Rijk, en een afwijking is gemeld.', '',
                    'eigen'],
                ['Bij een algoritme of AI zijn de gevolgen voor mensen getoetst.',
                    'Bijvoorbeeld met een IAMA of de AI scan. Het algoritmeregister hoort erbij.', 'eigen'],
                ['Afspraken over beveiliging en privacy staan in het contract met de leverancier.',
                    'Zoals een verwerkersovereenkomst, en de inkoopvoorwaarden voor beveiliging van het Rijk.', 'eigen'],
            ],
        ],
    ],
    'archief' => [
        'naam' => 'Duurzame toegankelijkheid', 'kort' => 'archivering', 'stap' => 5,
        'intro' => 'Informatie van de overheid moet vindbaar en leesbaar blijven, ook als het systeem al lang weg is. '
            . 'Dat heet archiveren by design, en de Archiefwet 2026 eist het. Het Kwaliteitskader CIO-oordelen noemt '
            . 'dit gebied. De aspecten hieronder zijn een eigen uitwerking.',
        'aspecten' => [
            '' => [
                ['Het project weet welke informatie het systeem maakt, en welke daarvan bewaard moet blijven.',
                    'Dat volgt uit de selectielijst van de organisatie.', 'eigen'],
                ['Bewaren en vernietigen volgens de bewaartermijnen is in het systeem geregeld.', '', 'eigen'],
                ['De informatie krijgt metadata volgens de MDTO.',
                    'MDTO is de standaard van het Nationaal Archief voor gegevens over informatie.', 'eigen'],
                ['De informatie is te vinden, en openbaar te maken, bijvoorbeeld voor een verzoek op grond van de Woo.',
                    '', 'eigen'],
                ['De informatie kan in een open formaat uit het systeem als het systeem stopt of wordt vervangen.', '',
                    'eigen'],
                ['Een specialist in informatiebeheer heeft meegekeken bij het ontwerp.', '', 'eigen'],
                ['Informatie die bewaard blijft, kan op tijd naar het Nationaal Archief.',
                    'De Archiefwet 2026 brengt die termijn terug van twintig naar tien jaar.', 'eigen'],
                ['Het contract regelt hoe het ministerie aan de Archiefwet voldoet, ook als de informatie bij een '
                    . 'leverancier staat.', 'Het ministerie blijft verantwoordelijk, ook in een clouddienst.', 'eigen'],
                ['Het project weet welke informatie na de overbrenging beperkt openbaar moet blijven, en waarom.',
                    'Na de overbrenging is informatie in principe openbaar.', 'eigen'],
                ['De informatie blijft vindbaar, beschikbaar, leesbaar, te begrijpen en betrouwbaar.',
                    'Dat zijn de eisen voor duurzame toegankelijkheid. Het DUTO raamwerk van het Nationaal Archief '
                    . 'werkt ze uit.', 'eigen'],
            ],
        ],
    ],
];

/** De vier oordelen, van goed naar slecht. */
const CO_OORDELEN = [
    'positief' => [
        'label' => 'Positief',
        'betekenis' => 'Het project kan starten of doorgaan.',
    ],
    'aanbevelingen' => [
        'label' => 'Positief, met aanbevelingen',
        'betekenis' => 'Het project kan starten of doorgaan. De aanbevelingen maken de kans op succes groter.',
    ],
    'voorwaarden' => [
        'label' => 'Positief, onder voorwaarden',
        'betekenis' => 'Het project kan starten of doorgaan, maar eerst moeten de voorwaarden geregeld zijn.',
    ],
    'negatief' => [
        'label' => 'Negatief',
        'betekenis' => 'Het project kan zo niet starten of doorgaan. Alleen de SG kan daar met redenen van '
            . 'afwijken.',
    ],
];

/** De stukken die het CIO office voor een oordeel vraagt. De eerste drie zijn het minimum. */
const CO_STUKKEN = [
    'businesscase' => ['label' => 'Business case'],
    'planning' => ['label' => 'Planning'],
    'architectuur' => ['label' => 'Architectuurschets'],
    'risico' => ['label' => 'Risicoanalyse van het project'],
    'bia' => ['label' => 'BIA, of quickscan 2.0'],
    'dpia' => ['label' => 'DPIA'],
    'sourcing' => ['label' => 'Sourcingstrategie of aanbestedingsstrategie'],
    'beheer' => ['label' => 'Beheerplan of exitplan'],
    'anders' => ['label' => 'Andere stukken'],
];

/** Hoeveel gebieden met een hoog risico het voorstel negatief maken. */
const CO_GRENS_NEGATIEF = 3;

/**
 * Aspecten die alleen gelden bij een bepaald antwoord in het onderzoek. Anders
 * zijn ze verborgen, en tellen ze niet mee.
 */
const CO_TOEPASSING = [
    'co_processen_5' => ['co_algoritme' => ['ja']],
    'co_sourcing_2' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_3' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_4' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_5' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_6' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_7' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_8' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_9' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_10' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_11' => ['co_uitbesteden' => ['ja']],
    'co_sourcing_12' => ['co_uitbesteden' => ['ja']],
    'co_beveiliging_4' => ['co_persoonsgegevens' => ['ja']],
    'co_beveiliging_5' => ['co_cloud' => ['ja']],
    'co_beveiliging_6' => ['co_algoritme' => ['ja']],
    'co_beveiliging_7' => ['co_uitbesteden' => ['ja']],
    'co_archief_8' => ['co_uitbesteden' => ['ja']],
];

/** @return list<array<string,mixed>> */
function co_secties(): array
{
    $secties = [co_sectie_onderzoek()];

    foreach (CO_GEBIEDEN as $id => $gebied) {
        $secties[] = co_sectie_gebied($id, $gebied);
    }

    return [...$secties, co_sectie_oordeel()];
}

/**
 * De toetsaspecten van een gebied, met hun sleutel: co_<gebied>_<nummer>.
 *
 * @return array<string,array{tekst:string,hint:string,deel:string,eigen:bool,nummer:int}>
 */
function co_aspecten(string $gebied): array
{
    $uit = [];
    $nummer = 0;

    foreach (CO_GEBIEDEN[$gebied]['aspecten'] as $deel => $aspecten) {
        foreach ($aspecten as $aspect) {
            $nummer++;
            $uit['co_' . $gebied . '_' . $nummer] = [
                'tekst' => $aspect[0],
                'hint' => $aspect[1],
                'deel' => (string) $deel,
                'eigen' => ($aspect[2] ?? '') === 'eigen',
                'nummer' => $nummer,
            ];
        }
    }

    return $uit;
}

/**
 * Eén toetsaspect als groep die één regel wordt: de kop, het oordeel, wat de
 * adviseur zag, en bij een aandachtspunt of risico wat hij aanraadt.
 *
 * @param array{tekst:string,hint:string,nummer:int} $aspect
 * @return list<array<string,mixed>>
 */
function co_aspectvragen(string $key, int $gebiedNummer, array $aspect): array
{
    $vragen = [
        [
            'key' => $key . '_kop',
            'type' => 'kop',
            'label' => $gebiedNummer . '.' . $aspect['nummer'] . ' ' . $aspect['tekst'],
            'hint' => $aspect['hint'],
            'stand' => 'co_aspect_stand',
        ],
        [
            'key' => $key,
            'type' => 'radio',
            'label' => 'Hoe staat dit ervoor?',
            'verplicht' => true,
            'voorstel' => true,
            'opties' => CO_KEUZES,
        ],
        [
            'key' => $key . '_bevinding',
            'type' => 'textarea',
            'label' => 'Wat zag je?',
            'hint' => 'Noem ook de bron: een stuk, of een gesprek. Bij ‘Op orde’ komt dit bij wat goed gaat.',
            'verplicht_als' => [$key => ['aandacht', 'risico', 'onbekend']],
            'toon_als' => [$key => ['orde', 'aandacht', 'risico', 'onbekend']],
            'hoogte' => 2,
        ],
        [
            'key' => $key . '_advies',
            'type' => 'textarea',
            'label' => 'Wat raad je aan?',
            'verplicht_als' => [$key => ['risico']],
            'toon_als' => [$key => ['aandacht', 'risico']],
            'hoogte' => 2,
        ],
        [
            'key' => $key . '_voorwaarde',
            'type' => 'radio',
            'label' => 'Moet dit geregeld zijn voordat het project verder mag?',
            'hint' => 'Dan wordt het advies een voorwaarde bij het oordeel.',
            'verplicht' => true,
            'toon_als' => [$key => ['risico']],
            'opties' => [
                'ja' => ['label' => 'Ja, het wordt een voorwaarde'],
                'nee' => ['label' => 'Nee, het blijft een aanbeveling'],
            ],
        ],
    ];

    // Een aspect dat niet geldt, verdwijnt met al zijn vragen.
    $als = CO_TOEPASSING[$key] ?? [];

    return array_map(static fn (array $v): array => ['toon_als' => ($v['toon_als'] ?? []) + $als] + $v
        + ['groep' => $key], $vragen);
}

// ---------------------------------------------------------------------------

function co_sectie_onderzoek(): array
{
    return [
        'id' => 'onderzoek',
        'titel' => 'Het project en het onderzoek',
        'stap' => 1,
        'intro' => 'Eerst de gegevens van het project, en hoe je het onderzoek hebt gedaan. Laad je de business case in, '
            . 'dan staat een deel er al.',
        'vragen' => [
            [
                'key' => 'projectnaam',
                'type' => 'text',
                'label' => 'Hoe heet het project?',
                'hint' => 'Deze naam komt in het rapport en in de bestandsnaam.',
                'verplicht' => true,
            ],
            [
                'key' => 'organisatieonderdeel',
                'type' => 'text',
                'label' => 'Welk onderdeel voert het project uit?',
                'verplicht' => true,
            ],
            [
                'key' => 'co_opdrachtgever',
                'type' => 'text',
                'label' => 'Wie is de opdrachtgever?',
                'hint' => 'Naam en functie.',
                'verplicht' => true,
            ],
            [
                'key' => 'proces_omschrijving',
                'type' => 'textarea',
                'label' => 'Waar gaat het project over?',
                'hint' => 'Het werk dat verandert, en wat het project oplevert. In gewone taal.',
                'verplicht' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'co_moment',
                'type' => 'radio',
                'label' => 'Op welk moment geef je het oordeel?',
                'verplicht' => true,
                'opties' => [
                    'start' => ['label' => 'Voor de start van het project'],
                    'herijking' => ['label' => 'Bij een herijking',
                        'hint' => 'Het doel, het geld of de tijd verandert flink.'],
                    'uitvoering' => ['label' => 'Tijdens de uitvoering'],
                    'beheer' => ['label' => 'Bij een grote wijziging van een systeem in beheer'],
                ],
            ],
            [
                'key' => 'co_aanleiding',
                'type' => 'checkbox',
                'label' => 'Waarom is er een CIO oordeel nodig?',
                'hint' => 'Kies alles wat geldt.',
                'verplicht' => true,
                'opties' => [
                    'bedrag' => ['label' => 'Het ICT deel kost meer dan 5 miljoen euro',
                        'hint' => 'Over de hele looptijd. Het project komt dan ook op het Rijks ICT-dashboard.'],
                    'risico' => ['label' => 'Het project kan veel schade doen als het mislukt',
                        'hint' => 'Ook onder 5 miljoen euro. Bijvoorbeeld bij een vitaal proces, of veel burgers.'],
                    'verzoek' => ['label' => 'De opdrachtgever, de SG of de CIO vraagt erom'],
                ],
            ],
            [
                'key' => 'co_kosten',
                'type' => 'text',
                'label' => 'Wat kost het project, en hoeveel daarvan is ICT?',
                'hint' => 'Over de hele looptijd, met het beheer. Bijvoorbeeld: 12 miljoen euro, waarvan 8 miljoen ICT.',
                'verplicht' => true,
            ],
            [
                'key' => 'co_uitbesteden',
                'type' => 'radio',
                'label' => 'Besteedt het project werk uit aan een marktpartij?',
                'hint' => 'Ook een clouddienst of software die jullie kopen, telt mee. Werk van een andere overheid '
                    . 'niet.',
                'verplicht' => true,
                'opties' => ['ja' => ['label' => 'Ja'], 'nee' => ['label' => 'Nee, de overheid doet alles zelf']],
            ],
            [
                'key' => 'co_cloud',
                'type' => 'radio',
                'label' => 'Komt er iets in de cloud?',
                'hint' => 'Ook de cloud van een andere overheid telt mee.',
                'verplicht' => true,
                'opties' => ['ja' => ['label' => 'Ja'], 'nee' => ['label' => 'Nee']],
            ],
            [
                'key' => 'co_persoonsgegevens',
                'type' => 'radio',
                'label' => 'Verwerkt het systeem persoonsgegevens?',
                'hint' => 'Ook namen en mailadressen van medewerkers tellen mee.',
                'verplicht' => true,
                'opties' => ['ja' => ['label' => 'Ja'], 'nee' => ['label' => 'Nee']],
            ],
            [
                'key' => 'co_algoritme',
                'type' => 'radio',
                'label' => 'Gebruikt het systeem een algoritme of AI, of neemt het zelf besluiten?',
                'verplicht' => true,
                'opties' => ['ja' => ['label' => 'Ja'], 'nee' => ['label' => 'Nee']],
            ],
            [
                'key' => 'co_acict',
                'type' => 'radio',
                'label' => 'Is het project aangemeld bij het Adviescollege ICT-toetsing?',
                'hint' => 'Boven 5 miljoen euro meldt de CIO het project aan. Het AcICT kiest zelf welke projecten het '
                    . 'onderzoekt.',
                'verplicht' => true,
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nog_niet' => ['label' => 'Nog niet, dat gebeurt nu'],
                    'nee' => ['label' => 'Nee, dat is niet nodig'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'co_stukken',
                'type' => 'checkbox',
                'label' => 'Welke stukken heb je gelezen?',
                'hint' => 'Een business case, een planning en een architectuurschets zijn het minimum.',
                'verplicht' => true,
                'opties' => CO_STUKKEN,
            ],
            [
                'key' => 'co_afgeleid_stukken',
                'type' => 'afgeleid',
                'bron' => 'stukken',
                'label' => 'Genoeg stukken?',
            ],
            [
                'key' => 'aanvrager',
                'type' => 'text',
                'label' => 'Wie stelt het oordeel op?',
                'hint' => 'Naam en functie. Deze naam komt als auteur in het rapport.',
                'verplicht' => true,
            ],
            [
                'key' => 'co_stukken_lijst',
                'type' => 'textarea',
                'label' => 'Welke stukken precies?',
                'hint' => 'Met versie en datum. Eén stuk per regel.',
                'optioneel' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'co_gesprekken',
                'type' => 'textarea',
                'label' => 'Met wie heb je gesproken?',
                'hint' => 'Rollen zijn genoeg, want de gesprekken zijn vertrouwelijk. Eén gesprek per regel.',
                'optioneel' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'co_team',
                'type' => 'textarea',
                'label' => 'Wie zaten er verder in het team?',
                'hint' => 'Bijvoorbeeld een architect of een programmamanager. Eén persoon per regel.',
                'optioneel' => true,
                'hoogte' => 2,
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

/** @param array<string,mixed> $gebied */
function co_sectie_gebied(string $id, array $gebied): array
{
    $nummer = array_search($id, array_keys(CO_GEBIEDEN), true) + 1;
    $vragen = [[
        'key' => 'co_' . $id . '_snel',
        'type' => 'radio',
        'label' => 'Is dit hele gebied op orde?',
        'hint' => 'Kies ja, dan zet het formulier elk aspect alvast op ‘Op orde’. Klopt een aspect niet? Open het, en '
            . 'pas het aan.',
        'opties' => [
            'orde' => ['label' => 'Ja, alles is op orde'],
            'nee' => ['label' => 'Nee, er speelt iets'],
        ],
    ]];
    $deel = null;

    foreach (co_aspecten($id) as $key => $aspect) {
        if ($aspect['deel'] !== '' && $aspect['deel'] !== $deel) {
            $vragen[] = ['key' => 'co_' . $id . '_deel_' . $aspect['nummer'], 'type' => 'kop', 'label' => $aspect['deel']];
        }
        $deel = $aspect['deel'];
        array_push($vragen, ...co_aspectvragen($key, $nummer, $aspect));
    }

    $vragen[] = [
        'key' => 'co_afgeleid_' . $id,
        'type' => 'afgeleid',
        'bron' => $id,
        'label' => 'Risico voor ' . mb_strtolower($gebied['naam'], 'UTF-8') . ':',
    ];
    $vragen[] = [
        'key' => 'co_' . $id . '_toelichting',
        'type' => 'textarea',
        'label' => 'Wil je iets toelichten over dit gebied als geheel?',
        'hint' => 'Dit komt in het rapport, boven de aspecten van dit gebied.',
        'optioneel' => true,
        'hoogte' => 3,
    ];

    // Het nummer van het toetskader, niet dat van de sectie: app.css verbergt dat.
    return [
        'id' => $id,
        'titel' => $nummer . '. ' . $gebied['naam'],
        'stap' => $gebied['stap'],
        'intro' => $gebied['intro'],
        'aanvullend' => 'Toelichting bij dit gebied',
        'vragen' => $vragen,
    ];
}

function co_sectie_oordeel(): array
{
    return [
        'id' => 'oordeel',
        'titel' => 'Het oordeel',
        'stap' => 6,
        'intro' => 'Uit de aspecten volgt een voorstel voor het oordeel. Het oordeel zelf is van de CIO. Wijk je af van '
            . 'het voorstel? Leg dan uit waarom.',
        'vragen' => [
            [
                'key' => 'co_afgeleid_oordeel',
                'type' => 'afgeleid',
                'bron' => 'oordeel',
                'label' => 'Voorstel:',
                'groepen' => co_model(),
            ],
            [
                'key' => 'co_oordeel',
                'type' => 'radio',
                'label' => 'Wat is het oordeel?',
                'verplicht' => true,
                'voorstel' => true,
                'opties' => array_map(static fn (array $o): array => ['label' => $o['label'], 'hint' => $o['betekenis']],
                    CO_OORDELEN),
            ],
            [
                'key' => 'co_afwijking',
                'type' => 'textarea',
                'label' => 'Waarom wijk je af van het voorstel?',
                'verplicht' => true,
                'toon_als_uitkomst' => ['afwijking' => ['ja']],
                'hoogte' => 3,
            ],
            [
                'key' => 'co_kern',
                'type' => 'textarea',
                'label' => 'Wat is de kern van het oordeel?',
                'hint' => 'Dit staat bovenaan het rapport. Het formulier schrijft een eerste versie. Maak er een '
                    . 'boodschap van die de opdrachtgever in één keer begrijpt.',
                'verplicht' => true,
                'motivatie_voor' => 'kern',
                'hoogte' => 6,
            ],
            [
                'key' => 'co_opvolging',
                'type' => 'textarea',
                'label' => 'Welke afspraken zijn er over de opvolging?',
                'hint' => 'Bijvoorbeeld: de opdrachtgever reageert binnen 30 dagen, en het CIO office kijkt over een '
                    . 'half jaar opnieuw.',
                'optioneel' => true,
                'hoogte' => 3,
            ],
        ],
    ];
}
