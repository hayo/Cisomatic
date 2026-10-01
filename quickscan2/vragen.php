<?php

/**
 * De vragenlijst van de quickscan voor de BIO2 en de Cyberbeveiligingswet.
 *
 * Dit is het enige bestand dat je hoeft aan te passen als je vragen wilt
 * toevoegen, herformuleren of weghalen. Het formulier, de controle en het
 * advies lezen allemaal uit deze definitie. De sleutels per vraag staan in
 * ../README.md. Deze vragenlijst gebruikt er één van zichzelf:
 *
 *   aanname   - waarde die geldt als iemand 'weet ik niet' antwoordt; de tool
 *               rekent daarmee door en het advies vermeldt dat het een aanname is
 *
 * Waar een vraag ook in de eerste quickscan staat, is de sleutel gelijk. Zo
 * laadt een bestand daarvan hier in.
 */

declare(strict_types=1);

/** @return list<array<string,mixed>> */
function q2_secties(): array
{
    return [
        q2_sectie_route(),
        q2_sectie_project(),
        q2_sectie_scope(),
        q2_sectie_informatie(),
        q2_sectie_toegang(),
        q2_sectie_privacy(),
        q2_sectie_archief(),
        q2_sectie_ai(),
        q2_sectie_leveranciers(),
        q2_sectie_classificatie(),
        q2_sectie_biv(),
        q2_sectie_belangen(),
        q2_sectie_cloud(),
        q2_sectie_dreiging(),
    ];
}

/** Alleen als er een externe partij iets levert. */
function q2_met_inkoop(): array
{
    return ['inkoopvorm' => ['enkele', 'nadere', 'raamcontract']];
}

/** Alleen als er cloud in het spel is. */
function q2_met_cloud(): array
{
    return ['cloud' => ['ja', 'mogelijk']];
}

/** Waar data of partijen zitten. $binnen is de keuze voor alles binnen de EER. */
function q2_locatie_opties(string $binnen): array
{
    return [
        'eer' => ['label' => $binnen, 'hint' => 'De EER is de EU, met Noorwegen, IJsland en Liechtenstein.'],
        'adequaatheid' => [
            'label' => 'Ook buiten de EER, in een land dat de EU veilig vindt',
            'hint' => 'De EU heeft dan besloten dat dat land persoonsgegevens goed beschermt. Dat heet een '
                . 'adequaatheidsbesluit.',
        ],
        'modelcontract' => [
            'label' => 'Ook buiten de EER, met een standaardcontract van de EU',
            'hint' => 'Zo’n contract heet ook een modelcontract.',
        ],
        'buiten-eer' => ['label' => 'Ook buiten de EER, zonder besluit van de EU en zonder standaardcontract'],
        'onbekend' => ['label' => 'Weet ik niet'],
    ];
}

/** Hulpje: BIV-opties opbouwen uit een toelichtingenlijst. */
function q2_biv_opties(array $toelichting, array $korteHints): array
{
    $opties = [];
    foreach (['zl', 'l', 'm', 'h', 'zh'] as $niveau) {
        $opties[$niveau] = [
            'label'  => cm_niveau_label($niveau),
            'hint'   => $korteHints[$niveau] ?? '',
            'uitleg' => $toelichting[$niveau] ?? '',
        ];
    }

    return $opties;
}

/** Hulpje: de vaste vorm van een bijstellingsvraag bij een afgeleide eis. */
function q2_bijstelling_opties(): array
{
    return [
        ''   => ['label' => 'Nee, laat de eis zo'],
        'zl' => ['label' => 'Ja, maak er Zeer laag van'],
        'l'  => ['label' => 'Ja, maak er Laag van'],
        'm'  => ['label' => 'Ja, maak er Midden van'],
        'h'  => ['label' => 'Ja, maak er Hoog van'],
        'zh' => ['label' => 'Ja, maak er Zeer hoog van'],
    ];
}

/**
 * De soorten schade uit bijlage 1 bij de Cyberbeveiligingsregeling sector
 * overheid, met per niveau de omschrijving, van licht naar zwaar. TBB 4 is de
 * lichtste schade die meetelt, TBB 1 de zwaarste. De rubricering en de eisen aan
 * beschikbaarheid, integriteit en vertrouwelijkheid staan ook in die tabel; die
 * leidt de tool zelf af.
 *
 * @return array<string,array{label:string,soort:string,niveaus:array<string,string>}>
 */
function q2_tbb_categorieen(): array
{
    return [
        'tbb_politiek' => [
            'label' => 'Hoe groot kan de politieke schade worden?',
            'soort' => 'Politieke schade',
            'niveaus' => [
                'tbb4' => 'Politieke schade voor een bewindspersoon',
                'tbb3' => 'Een bewindspersoon moet aftreden',
                'tbb2' => 'Het kabinet moet aftreden',
                'tbb1' => 'Een parlementaire crisis',
            ],
        ],
        'tbb_imago' => [
            'label' => 'Hoe groot kan de schade aan het imago van de overheid worden?',
            'soort' => 'Imagoschade',
            'niveaus' => [
                'tbb4' => 'Verlies aan publiek respect',
                'tbb3' => 'Publieke verontwaardiging',
                'tbb2' => 'Verlies aan vertrouwen',
                'tbb1' => 'Structureel verlies aan vertrouwen',
            ],
        ],
        'tbb_diplomatiek' => [
            'label' => 'Hoe groot kan de diplomatieke schade worden?',
            'soort' => 'Diplomatieke schade',
            'niveaus' => [
                'tbb4' => 'Te herstellen door ambtelijk op te schalen',
                'tbb3' => 'Te herstellen door politiek op te schalen',
                'tbb2' => 'Er is externe bemiddeling nodig',
                'tbb1' => 'Blijvende schade bij bondgenoten, of oorlog',
            ],
        ],
        'tbb_vitaal' => [
            'label' => 'Wat gebeurt er met vitale processen in de samenleving?',
            'soort' => 'Schade aan vitale processen',
            'niveaus' => [
                'tbb4' => 'Het is niet meer zeker dat een vitaal proces doorgaat',
                'tbb3' => 'Een vitaal proces valt tijdelijk uit',
                'tbb2' => 'Een vitaal proces valt langdurig uit',
                'tbb1' => 'Een vitaal proces valt blijvend uit',
            ],
        ],
        'tbb_letsel' => [
            'label' => 'Hoe ernstig kan het letsel worden?',
            'soort' => 'Letselschade',
            'niveaus' => [
                'tbb4' => 'Iemand raakt gewond',
                'tbb3' => 'Iemand overlijdt of raakt zeer ernstig gewond',
                'tbb2' => 'Meerdere doden of ernstig gewonden',
                'tbb1' => 'Groepen doden',
            ],
        ],
        'tbb_financieel' => [
            'label' => 'Hoe groot kan de financiële schade worden?',
            'soort' => 'Financiële schade voor de economie of de staat',
            'niveaus' => [
                'tbb4' => 'Meer dan 50 miljoen euro',
                'tbb3' => 'Meer dan 500 miljoen euro',
                'tbb2' => 'Meer dan 5 miljard euro',
                'tbb1' => 'Meer dan 50 miljard euro',
            ],
        ],
        'tbb_misbruik' => [
            'label' => 'Hoe groot kan de schade door misbruik van bedrijfsinformatie worden?',
            'soort' => 'Misbruik van bedrijfsinformatie',
            'niveaus' => [
                'tbb4' => 'Meer dan 1 miljoen euro',
                'tbb3' => 'Meer dan 5 miljoen euro',
                'tbb2' => 'Meer dan 500 miljoen euro',
                'tbb1' => 'Meer dan 5 miljard euro',
            ],
        ],
    ];
}

// ---------------------------------------------------------------------------
// Stap 1: de beschrijving

function q2_sectie_route(): array
{
    return [
        'id'    => 'route',
        'titel' => 'Wie vult dit in?',
        'stap' => 1,
        'intro' => 'De quickscan gaat in twee stappen. Eerst beschrijft de behoeftesteller wat er gaat '
            . 'gebeuren. Daarna weegt de beoordelaar dat, en stelt de eisen vast. Allebei gebruiken ze deze tool.',
        'vragen' => [
            [
                'key' => 'rol',
                'type' => 'radio',
                'label' => 'In welke rol vul je dit in?',
                'verplicht' => true,
                'opties' => [
                    'behoeftesteller' => [
                        'label' => 'Behoeftesteller',
                        'hint' => 'Je beschrijft wat je wilt gaan doen. De wegingen verderop vul je '
                            . 'alvast in als voorstel. De beoordelaar stelt ze definitief vast.',
                    ],
                    'beoordelaar' => [
                        'label' => 'Beoordelaar',
                        'hint' => 'Je beoordeelt wat de behoeftesteller heeft ingevuld, meestal namens de '
                            . 'CISO. Je mag de wegingen aanpassen, ook naar een lager niveau. Leg dan wel uit waarom.',
                    ],
                ],
            ],
            [
                'key' => 'beoordelaar_naam',
                'type' => 'text',
                'label' => 'Wie beoordeelt deze scan?',
                'hint' => 'Naam en rol, bijvoorbeeld "J. Jansen, CISO".',
                'verplicht_als' => ['rol' => ['beoordelaar']],
                'toon_als' => ['rol' => ['beoordelaar']],
            ],
        ],
    ];
}

function q2_sectie_project(): array
{
    return [
        'id'    => 'project',
        'titel' => 'Over het project',
        'stap' => 1,
        'intro' => 'Een paar gegevens, zodat je het advies later terugvindt. De beoordelaar weet dan ook wie '
            . 'het heeft ingevuld.',
        'vragen' => [
            [
                'key' => 'projectnaam',
                'type' => 'text',
                'label' => 'Hoe heet het project?',
                'hint' => 'Deze naam komt terug in de bestandsnaam van het advies.',
                'verplicht' => true,
            ],
            [
                'key' => 'aanvrager',
                'type' => 'text',
                'label' => 'Wie vult deze scan in?',
                'hint' => 'Naam en functie van de behoeftesteller.',
                'verplicht' => true,
            ],
            [
                'key' => 'email',
                'type' => 'email',
                'label' => 'Op welk mailadres ben je bereikbaar?',
                'hint' => 'De beoordelaar mailt je hierop over de risicoanalyse.',
            ],
            [
                'key' => 'organisatieonderdeel',
                'type' => 'text',
                'optioneel' => true,
                'label' => 'Voor welk team of welke afdeling is dit?',
            ],
            [
                'key' => 'organisatie_soort',
                'type' => 'radio',
                'label' => 'Voor wat voor organisatie is dit?',
                'hint' => 'Hieruit volgt of de Cyberbeveiligingswet geldt. De BIO2 geldt in alle gevallen.',
                'verplicht' => true,
                'aanname' => 'ministerie',
                'opties' => [
                    'ministerie' => ['label' => 'Een ministerie, of een dienst of agentschap daarvan'],
                    'zbo' => [
                        'label' => 'Een zelfstandig bestuursorgaan van de rijksoverheid',
                        'hint' => 'Een zbo valt onder de wet als het aan vier criteria voldoet. De tool '
                            . 'gaat daarvan uit. Je CISO weet het zeker.',
                    ],
                    'uitgezonderd' => [
                        'label' => 'Een organisatie die vooral werkt voor nationale veiligheid, openbare '
                            . 'veiligheid, defensie of rechtshandhaving',
                        'hint' => 'Zoals Defensie, het Openbaar Ministerie, de politie, de AIVD of de MIVD. '
                            . 'Daar geldt de Cyberbeveiligingswet niet.',
                    ],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
        ],
    ];
}

function q2_sectie_scope(): array
{
    return [
        'id'    => 'scope',
        'titel' => 'Wat gaan jullie doen, en waarmee?',
        'stap' => 1,
        'intro' => 'Een quickscan gaat altijd over twee dingen: een proces en een systeem. Het proces is wat '
            . 'je doet, en het systeem is waarmee je het doet. Beschrijf ze los van elkaar.',
        'vragen' => [
            [
                'key' => 'proces_omschrijving',
                'type' => 'textarea',
                'label' => 'Wat willen jullie gaan doen?',
                'hint' => 'Beschrijf het proces in gewone taal. Wat gebeurt er, van begin tot eind?',
                'verplicht' => true,
                'hoogte' => 4,
            ],
            [
                'key' => 'systeem_omschrijving',
                'type' => 'textarea',
                'label' => 'Met welk systeem, dienst of leverancier voeren jullie dat uit?',
                'hint' => 'Naam van het product of de dienst, en wie het levert.',
                'verplicht' => true,
                'hoogte' => 4,
            ],
            [
                'key' => 'inkoopvorm',
                'type' => 'radio',
                'label' => 'Wat voor inkoop is dit?',
                'verplicht' => true,
                'opties' => [
                    'geen' => ['label' => 'Geen inkoop', 'hint' => 'We bouwen of gebruiken dit zelf.'],
                    'enkele' => ['label' => 'Enkele opdracht'],
                    'nadere' => ['label' => 'Nadere opdracht', 'hint' => 'Onder een bestaand raamcontract.'],
                    'raamcontract' => [
                        'label' => 'Raamcontract',
                        'hint' => 'Deze scan geldt dan ook voor alle opdrachten die later onder dit contract komen.',
                    ],
                ],
            ],
            [
                'key' => 'gebruikers',
                'type' => 'radio',
                'label' => 'Wie gaat het gebruiken?',
                'verplicht' => true,
                'opties' => [
                    'intern' => ['label' => 'Alleen wijzelf'],
                    'rijksbreed' => ['label' => 'Ook andere overheidsorganisaties'],
                    'burgers' => ['label' => 'Ook burgers of andere externen'],
                ],
            ],
            [
                'key' => 'systemen_afhankelijk',
                'type' => 'radio',
                'label' => 'Hangen andere processen of systemen straks van dit systeem af?',
                'hint' => 'Kunnen ze niet zonder dit systeem? Dan moet dit systeem even goed beschikbaar zijn als '
                    . 'zij. De eis aan beschikbaarheid gaat dan verderop omhoog.',
                'verplicht' => true,
                'aanname' => 'enkele',
                'opties' => [
                    'nee' => ['label' => 'Nee, dit staat op zichzelf'],
                    'enkele' => ['label' => 'Ja, enkele'],
                    'veel' => ['label' => 'Ja, veel processen, of een heel belangrijk proces'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'omgeving',
                'type' => 'checkbox',
                'label' => 'Gaat het om een van deze bijzondere omgevingen?',
                'hint' => 'Niets aanvinken betekent: geen van deze.',
                'opties' => [
                    'ot' => [
                        'label' => 'Operationele technologie',
                        'hint' => 'Systemen die fysieke installaties besturen, zoals sluizen, '
                            . 'gebouwbeheer of meetapparatuur.',
                    ],
                    'zorg' => [
                        'label' => 'Zorginformatie',
                        'hint' => 'Gegevens over de gezondheid of de zorg van mensen, in een zorgproces.',
                    ],
                    'basisregistratie' => [
                        'label' => 'Een basisregistratie',
                        'hint' => 'Zoals de BRP, het Handelsregister of de BAG.',
                    ],
                ],
            ],
            [
                'key' => 'proces_systeem_relatie',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Hoe hangen proces en systeem samen?',
                'hint' => 'Welk deel van het proces gaat door het systeem heen?',
                'hoogte' => 3,
            ],
            [
                'key' => 'procesketen',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Welke processen zijn nodig om dit te kunnen starten?',
                'hoogte' => 3,
            ],
            [
                'key' => 'systeemketen',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Aan welke bestaande systemen zit dit vast?',
                'hoogte' => 3,
            ],
        ],
    ];
}

function q2_sectie_informatie(): array
{
    return [
        'id'    => 'informatie',
        'titel' => 'Welke informatie gaat erin?',
        'stap'  => 1,
        'intro' => 'Kruis aan wat er in het systeem terecht kan komen. Ga uit van het slechtste geval: alles '
            . 'wat het systeem toelaat, gebeurt ook. Bij een raamcontract telt ook mee wat er later onder dat '
            . 'contract komt.',
        'vragen' => [
            [
                'key' => 'informatie_soorten',
                'type' => 'checkbox',
                'label' => 'Welke soorten informatie worden verwerkt?',
                'hint' => 'Kruis alles aan wat erin kan komen. De tool ziet dan zelf of er persoonsgegevens bij '
                    . 'zijn, en welke soort. Die vragen hoef je dus niet apart te beantwoorden.',
                'verplicht' => true,
                'groepen' => q2_informatiegroepen(),
                'opties' => q2_informatiesoorten(),
            ],
            [
                'key' => 'informatie_anders',
                'type' => 'textarea',
                'label' => 'Staat er iets niet bij?',
                'hint' => 'Eén soort per regel. Zitten er persoonsgegevens bij? Kruis dan hierboven ook "andere '
                    . 'persoonsgegevens" aan.',
                'hoogte' => 3,
            ],
            [
                'key' => 'afgeleid_persoonsgegevens',
                'type' => 'afgeleid',
                'bron' => 'pg',
                'label' => 'Wat betekent dit voor de privacy?',
                'hint' => 'Volgt uit wat je hierboven hebt aangekruist.',
            ],
            [
                'key' => 'rubricering',
                'type' => 'radio',
                'label' => 'Hoe gevoelig is die informatie?',
                'hint' => 'Kies het gevoeligste stuk dat erin kan komen. Hieruit volgt straks hoe geheim de '
                    . 'informatie moet blijven.',
                'verplicht' => true,
                'opties' => [
                    'openbaar' => [
                        'label' => 'Openbaar',
                        'hint' => 'Mag iedereen zien.',
                        'uitleg' => q2_toelichting_vertrouwelijkheid()['zl'],
                    ],
                    'intern' => [
                        'label' => 'Intern, maar niet gerubriceerd',
                        'hint' => 'Niet openbaar, maar zonder rubricering.',
                        'uitleg' => q2_toelichting_vertrouwelijkheid()['m'],
                    ],
                    'dep-vertrouwelijk' => [
                        'label' => 'Departementaal Vertrouwelijk',
                        'hint' => 'Of NATO Restricted of EU Restricted.',
                        'uitleg' => q2_toelichting_vertrouwelijkheid()['h'],
                    ],
                    'stg-confidentieel' => [
                        'label' => 'Staatsgeheim Confidentieel',
                        'hint' => 'Of NATO Confidential of EU Confidential. Publieke cloud is dan uitgesloten.',
                        'uitleg' => q2_toelichting_vertrouwelijkheid()['zh'],
                    ],
                    'stg-geheim' => [
                        'label' => 'Staatsgeheim Geheim',
                        'hint' => 'Of NATO Secret of EU Secret.',
                    ],
                    'stg-zeer-geheim' => [
                        'label' => 'Staatsgeheim Zeer geheim',
                        'hint' => 'Of Cosmic Top Secret of EU Top Secret.',
                    ],
                ],
            ],
        ],
    ];
}

/**
 * De aankruislijst met soorten informatie.
 *
 * De groepen zijn niet alleen ordening: ze bepalen de afleiding. Alles in de
 * eerste twee groepen telt als een categorie persoonsgegevens, de derde groep
 * als bijzondere persoonsgegevens.
 *
 * @return array<string,array<string,string>>
 */
function q2_informatiesoorten(): array
{
    $groepen = [
        'Gewone persoonsgegevens' => [
            'naam' => 'Naam',
            'contact' => 'Adres, telefoonnummer of mailadres',
            'geboorte' => 'Geboortedatum of leeftijd',
            'functie' => 'Functie, afdeling of werkgever',
            'financieel' => 'Bankrekening, salaris of andere financiële gegevens',
            'ip' => 'IP adres, nummer van een apparaat of van een cookie',
            'locatie' => 'Waar iemand is, of waar iemand heen reist',
            'gebruik' => 'Wat iemand doet in een systeem of op internet',
            'beeld' => 'Foto, video of geluidsopname van personen',
            'vrije_tekst' => 'Vrije tekst die mensen zelf invullen, zoals een vraag of klacht',
            'pg_anders' => 'Andere persoonsgegevens',
        ],
        'Identificerende gegevens' => [
            'bsn' => 'Burgerservicenummer',
            'idbewijs' => 'Kopie of nummer van een identiteitsbewijs',
        ],
        'Bijzondere persoonsgegevens' => [
            'gezondheid' => 'Gezondheid',
            'strafrecht' => 'Strafrechtelijk gedrag of veroordelingen',
            'biometrie' => 'Biometrische of genetische gegevens',
            'ras' => 'Ras of etnische afkomst',
            'godsdienst' => 'Godsdienst of levensovertuiging',
            'politiek' => 'Politieke opvatting',
            'seksueel' => 'Seksueel leven of geaardheid',
            'vakbond' => 'Lidmaatschap van een vakvereniging',
        ],
        'Informatie zonder persoonsgegevens' => [
            'beleid' => 'Beleidsstukken of besluitvorming',
            'woordvoering' => 'Woordvoering of communicatie',
            'contract' => 'Contracten, offertes of stukken over een aanbesteding',
            'organisatie_financieel' => 'Financiële administratie van de organisatie',
            'techniek' => 'Broncode, configuratie of technische documentatie',
            'publicatie' => 'Publicaties die al openbaar zijn',
        ],
    ];

    $opties = [];
    foreach ($groepen as $groep => $soorten) {
        foreach ($soorten as $sleutel => $label) {
            $opties[$sleutel] = ['label' => $label, 'groep' => $groep];
        }
    }

    return $opties;
}

/** De sleutels per groep, zodat de afleiding weet wat waarbij hoort. */
function q2_informatiegroepen(): array
{
    $perGroep = [];
    foreach (q2_informatiesoorten() as $sleutel => $optie) {
        $perGroep[$optie['groep']][] = $sleutel;
    }

    return [
        'gewoon' => array_merge(
            $perGroep['Gewone persoonsgegevens'] ?? [],
            $perGroep['Identificerende gegevens'] ?? []
        ),
        'bijzonder' => $perGroep['Bijzondere persoonsgegevens'] ?? [],
        'overig' => $perGroep['Informatie zonder persoonsgegevens'] ?? [],
    ];
}

function q2_sectie_toegang(): array
{
    $internet = ['bereikbaar' => ['internet_medewerkers', 'internet_publiek', 'onbekend']];

    return [
        'id'    => 'toegang',
        'titel' => 'Hoe is het bereikbaar, en wie logt in?',
        'stap' => 1,
        'intro' => 'Wat via internet bereikbaar is, moet aan extra eisen uit de BIO2 voldoen. Denk aan de '
            . 'verplichte internetstandaarden, en een beveiligingstest (pentest) bij elke nieuwe versie. Weet je '
            . 'iets niet? Kies dan "weet ik niet". De tool rekent dan met een veilige aanname.',
        'vragen' => [
            [
                'key' => 'bereikbaar',
                'type' => 'radio',
                'label' => 'Waar is het systeem te bereiken?',
                'verplicht' => true,
                'aanname' => 'internet_publiek',
                'opties' => [
                    'intern' => ['label' => 'Alleen binnen het netwerk van de organisatie'],
                    'internet_medewerkers' => ['label' => 'Via internet, alleen voor medewerkers'],
                    'internet_publiek' => ['label' => 'Via internet, ook voor burgers, bedrijven of ketenpartners'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'internet_onderdelen',
                'type' => 'checkbox',
                'label' => 'Wat staat er aan de kant van internet?',
                'hint' => 'Niets aanvinken betekent: geen van deze.',
                'toon_als' => $internet,
                'opties' => [
                    'website' => ['label' => 'Een website of portaal'],
                    'api' => ['label' => 'Een koppeling voor andere systemen (API)'],
                    'email' => ['label' => 'Het systeem verstuurt mail namens de organisatie'],
                    'domein' => ['label' => 'Er komt een nieuwe domeinnaam bij'],
                ],
            ],
            [
                'key' => 'inloggen',
                'type' => 'checkbox',
                'label' => 'Wie loggen er in?',
                'hint' => 'Niets aanvinken betekent: niemand logt in, bijvoorbeeld bij een koppeling '
                    . 'tussen systemen.',
                'opties' => [
                    'medewerkers' => ['label' => 'Medewerkers'],
                    'beheerders' => ['label' => 'Beheerders met extra rechten'],
                    'extern' => ['label' => 'Burgers, bedrijven of ketenpartners'],
                ],
            ],
            [
                'key' => 'mfa',
                'type' => 'radio',
                'label' => 'Kan iedereen in twee stappen inloggen?',
                'hint' => 'Bijvoorbeeld met een wachtwoord en een code op de telefoon, of met een passkey. Dit '
                    . 'heet ook MFA.',
                'verplicht' => true,
                'toon_als' => ['inloggen' => ['medewerkers', 'beheerders', 'extern']],
                'aanname' => 'nee',
                'opties' => [
                    'ja' => ['label' => 'Ja, voor alle accounts'],
                    'deels' => ['label' => 'Voor een deel van de accounts'],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'logging',
                'type' => 'radio',
                'label' => 'Legt het systeem vast wie wat doet?',
                'hint' => 'De BIO2 vraagt per gebeurtenis: wat er gebeurde, waarop, met welk resultaat, '
                    . 'vanaf waar, door wie en wanneer.',
                'verplicht' => true,
                'aanname' => 'deels',
                'opties' => [
                    'volledig' => ['label' => 'Ja, al die gegevens'],
                    'deels' => ['label' => 'Een deel daarvan'],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'monitoring',
                'type' => 'radio',
                'label' => 'Worden aanvallen op het systeem opgemerkt?',
                'hint' => 'Bijvoorbeeld doordat een beveiligingsteam (SOC) de logbestanden in de gaten houdt.',
                'verplicht' => true,
                'aanname' => 'nee',
                'opties' => [
                    'ja' => ['label' => 'Ja, het systeem wordt bewaakt'],
                    'gepland' => ['label' => 'Dat wordt nog geregeld'],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
        ],
    ];
}

function q2_sectie_privacy(): array
{
    return [
        'id'    => 'privacy',
        'titel' => 'Waarvoor en op welke grond?',
        'stap' => 1,
        'intro' => 'Er komen persoonsgegevens in het systeem, dus nu volgen een paar vragen over privacy. '
            . 'Daaruit blijkt of er een DPIA nodig is. Ook zie je of je de gegevens wel mag gebruiken.',
        'toon_als' => ['informatie_soorten' => q2_persoonsgegeven_sleutels()],
        'vragen' => [
            [
                'key' => 'pg_doel',
                'type' => 'textarea',
                'label' => 'Waarvoor worden de persoonsgegevens gebruikt?',
                'hint' => 'Eén doel per regel. "Voor de dienstverlening" is te vaag. Waar gebruik je de '
                    . 'gegevens echt voor?',
                'verplicht' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'pg_grondslag',
                'type' => 'radio',
                'label' => 'Op welke grondslag gebeurt dat?',
                'hint' => 'Bij de overheid is het bijna altijd een wettelijke verplichting of een '
                    . 'taak van algemeen belang.',
                'verplicht' => true,
                'opties' => [
                    'wettelijke_verplichting' => [
                        'label' => 'Wettelijke verplichting',
                        'hint' => 'Een wet verplicht ons deze gegevens te verwerken.',
                    ],
                    'algemeen_belang' => [
                        'label' => 'Taak van algemeen belang of openbaar gezag',
                        'hint' => 'De verwerking hoort bij onze publieke taak.',
                    ],
                    'overeenkomst' => [
                        'label' => 'Uitvoering van een overeenkomst',
                        'hint' => 'Nodig om een contract met de betrokkene uit te voeren.',
                    ],
                    'toestemming' => [
                        'label' => 'Toestemming van de betrokkene',
                        'hint' => 'Mensen moeten dan vrij nee kunnen zeggen, en hun ja later weer kunnen '
                            . 'intrekken. Bij de overheid lukt dat vaak niet.',
                    ],
                    'vitaal_belang' => ['label' => 'Vitaal belang van een persoon'],
                    'gerechtvaardigd_belang' => [
                        'label' => 'Gerechtvaardigd belang',
                        'hint' => 'De overheid mag deze grond niet gebruiken voor haar eigen taken.',
                    ],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_subsidiariteit',
                'type' => 'radio',
                'label' => 'Kan hetzelfde doel ook met minder of zonder persoonsgegevens?',
                'hint' => 'Dit heet ook subsidiariteit.',
                'verplicht' => true,
                'aanname' => 'kan_minder',
                'opties' => [
                    'minst_ingrijpend' => [
                        'label' => 'Nee, het kan niet met minder',
                    ],
                    'kan_minder' => [
                        'label' => 'Ja, het kan met minder gegevens, of anoniem',
                        'hint' => 'Leg dan uit waarom jullie daar niet voor kiezen.',
                    ],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_proportionaliteit',
                'type' => 'radio',
                'label' => 'Is het doel belangrijk genoeg voor deze inbreuk op de privacy?',
                'hint' => 'Weegt wat je bereikt op tegen wat het de mensen kost? Dit heet ook proportionaliteit.',
                'verplicht' => true,
                'aanname' => 'twijfel',
                'opties' => [
                    'in_verhouding' => ['label' => 'Ja, het doel weegt zwaarder'],
                    'twijfel' => ['label' => 'Daar twijfel ik over'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_aantal_betrokkenen',
                'type' => 'radio',
                'label' => 'Over hoeveel mensen gaat het?',
                'hint' => 'Het totaal over de hele looptijd, niet per dag.',
                'verplicht' => true,
                'aanname' => 'tot100k',
                'opties' => [
                    'tot100' => ['label' => 'Minder dan 100'],
                    'tot1000' => ['label' => '100 tot 1.000'],
                    'tot100k' => ['label' => '1.000 tot 100.000'],
                    'meer' => ['label' => 'Meer dan 100.000'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_hoeveelheid',
                'type' => 'radio',
                'label' => 'Hoeveel weten we straks per persoon?',
                'verplicht' => true,
                'aanname' => 'uitgebreid',
                'opties' => [
                    'beperkt' => [
                        'label' => 'Een paar gegevens',
                        'hint' => 'Bijvoorbeeld alleen een naam en een mailadres.',
                    ],
                    'uitgebreid' => [
                        'label' => 'Een uitgebreid beeld',
                        'hint' => 'Genoeg om een profiel van iemand te vormen.',
                    ],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_duur',
                'type' => 'radio',
                'label' => 'Hoe lang loopt de verwerking?',
                'verplicht' => true,
                'aanname' => 'doorlopend',
                'opties' => [
                    'kortdurend' => ['label' => 'Eén keer, of een korte tijd'],
                    'doorlopend' => ['label' => 'Doorlopend'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_bereik',
                'type' => 'radio',
                'label' => 'Hoe groot is het gebied waar dit over gaat?',
                'verplicht' => true,
                'aanname' => 'landelijk',
                'opties' => [
                    'lokaal' => ['label' => 'Eén organisatie of regio'],
                    'landelijk' => ['label' => 'Landelijk'],
                    'internationaal' => ['label' => 'Internationaal'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_rechten',
                'type' => 'radio',
                'optioneel' => true,
                'label' => 'Kan het systeem de gegevens van één persoon tonen, verbeteren en wissen?',
                'hint' => 'Mensen hebben het recht om hun gegevens in te zien, te laten verbeteren en te laten '
                    . 'wissen.',
                'opties' => [
                    'volledig' => ['label' => 'Ja, volledig'],
                    'deels' => ['label' => 'Deels, of alleen met de hand'],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
        ],
    ];
}

function q2_sectie_archief(): array
{
    return [
        'id'    => 'archief',
        'titel' => 'Bewaren, vernietigen en archiveren',
        'stap' => 1,
        'intro' => 'Wat de overheid voor haar werk maakt of krijgt, valt onder de Archiefwet 2026. Dat geldt '
            . 'voor elke vorm, dus ook voor chats, mail en gegevens in een clouddienst. Voor alles geldt een '
            . 'bewaartermijn. Daarna moet je de informatie echt vernietigen, of naar een archief brengen. Onder '
            . 'de nieuwe wet moet dat al na tien jaar, en niet meer na twintig. Het advies legt uit hoe je dat '
            . 'regelt.',
        'vragen' => [
            [
                'key' => 'bewaartermijn',
                'type' => 'radio',
                'label' => 'Hoe lang wordt de informatie bewaard?',
                'verplicht' => true,
                'aanname' => 'onbepaald',
                'opties' => [
                    'selectielijst' => [
                        'label' => 'Volgens de selectielijst',
                        'hint' => 'Die lijst zegt per soort informatie hoe lang je die bewaart, of dat je die '
                            . 'altijd bewaart.',
                    ],
                    'wettelijk' => [
                        'label' => 'Een termijn uit een andere wet',
                        'hint' => 'Bijvoorbeeld voor de belasting, of voor personeelszaken.',
                    ],
                    'eigen' => [
                        'label' => 'Een termijn die we zelf hebben bepaald',
                        'hint' => 'Dat mag, als het doel die termijn onderbouwt.',
                    ],
                    'onbepaald' => [
                        'label' => 'Nog niet bepaald',
                        'hint' => 'Regel dit dan eerst. Zonder termijn voldoe je niet aan de AVG en niet aan de '
                            . 'Archiefwet.',
                    ],
                ],
            ],
            [
                'key' => 'bewaartermijn_toelichting',
                'type' => 'text',
                'label' => 'Welke categorie of termijn is dat?',
                'hint' => 'Bijvoorbeeld het nummer uit de selectielijst, of het aantal jaren.',
                'verplicht_als' => ['bewaartermijn' => ['selectielijst', 'wettelijk', 'eigen']],
                'toon_als' => ['bewaartermijn' => ['selectielijst', 'wettelijk', 'eigen']],
            ],
            [
                'key' => 'archief_vernietiging',
                'type' => 'radio',
                'label' => 'Kan het systeem informatie na de termijn echt vernietigen?',
                'hint' => 'Je mag informatie niet langer bewaren dan nodig. Kan het systeem niets weggooien? Dan '
                    . 'overtreed je de regels.',
                'verplicht' => true,
                'aanname' => 'nee',
                'opties' => [
                    'automatisch' => ['label' => 'Ja, automatisch op basis van de termijn'],
                    'handmatig' => ['label' => 'Ja, maar alleen met de hand'],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'archief_export',
                'type' => 'radio',
                'label' => 'Kun je de informatie eruit halen zonder de leverancier nodig te hebben?',
                'hint' => 'Dat is nodig voor het Nationaal Archief, voor een verzoek om informatie (Woo), en aan '
                    . 'het eind van het contract.',
                'verplicht' => true,
                'aanname' => 'nee',
                'opties' => [
                    'open' => [
                        'label' => 'Ja, in een open formaat dat elk systeem kan lezen',
                        'hint' => 'Bijvoorbeeld PDF/A, XML, CSV of JSON, met de gegevens over elk stuk erbij.',
                    ],
                    'eigen' => [
                        'label' => 'Ja, maar in een eigen formaat van de leverancier',
                    ],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
        ],
    ];
}

function q2_sectie_ai(): array
{
    return [
        'id'    => 'ai',
        'titel' => 'Komt er AI bij kijken?',
        'stap' => 1,
        'intro' => 'Zit er AI in het systeem? Doe dan ook de AI scan. Die zoekt uit welke regels van de AI '
            . 'verordening gelden. Ook "we sluiten het niet uit" telt hier mee.',
        'vragen' => [
            [
                'key' => 'ai',
                'type' => 'radio',
                'label' => 'Gebruikt het systeem AI?',
                'verplicht' => true,
                'opties' => [
                    'uitgesloten' => ['label' => 'Nee, en dat blijft zo'],
                    'mogelijk' => ['label' => 'Nu niet, maar het kan later komen'],
                    'onderdeel' => ['label' => 'Ja'],
                ],
            ],
            [
                'key' => 'ai_omschrijving',
                'type' => 'textarea',
                'label' => 'Waarvoor gebruiken jullie de AI, en hoe werkt die?',
                'verplicht' => true,
                'toon_als' => ['ai' => ['mogelijk', 'onderdeel']],
                'hoogte' => 3,
            ],
            [
                'key' => 'ai_nieuw_algoritme',
                'type' => 'radio',
                'label' => 'Is de AI nieuw voor de organisatie?',
                'hint' => 'Ook een bekend product is nieuw, als jullie het hier voor het eerst gebruiken.',
                'verplicht' => true,
                'toon_als' => ['ai' => ['mogelijk', 'onderdeel']],
                'aanname' => 'ja',
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'ja' => ['label' => 'Ja'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
        ],
    ];
}

function q2_sectie_leveranciers(): array
{
    $inkoop = q2_met_inkoop();

    return [
        'id'    => 'leveranciers',
        'titel' => 'Leveranciers en de keten',
        'stap' => 1,
        'intro' => 'De Cyberbeveiligingswet legt extra nadruk op de keten. De organisatie blijft zelf '
            . 'verantwoordelijk voor de risico’s, ook als een leverancier het werk doet. De BIO2 zet '
            . 'daarom eisen in het contract, en vraagt elk jaar om bewijs.',
        'vragen' => [
            [
                'key' => 'afwijkend',
                'type' => 'checkbox',
                'label' => 'Geldt een van deze dingen?',
                'hint' => 'Niets aanvinken betekent: geen van deze. Bij deze vormen kost de '
                    . 'voorbereiding meer tijd.',
                'opties' => [
                    'opensource' => ['label' => 'Open source', 'hint' => 'Iedereen mag de code zien en gebruiken.'],
                    'productgericht' => [
                        'label' => 'Jullie vragen om één bepaald product',
                        'hint' => 'In plaats van te beschrijven wat het moet kunnen.',
                    ],
                    'saas' => [
                        'label' => 'Software als dienst (SaaS)',
                        'hint' => 'Jullie gebruiken de software via internet, en de leverancier beheert die.',
                    ],
                ],
            ],
            [
                'key' => 'leverancier_land',
                'type' => 'text',
                'label' => 'Uit welk land komt de leverancier?',
                'hint' => 'Het land van het moederbedrijf, en niet alleen van de vestiging in Nederland. '
                    . 'Leveranciers uit Rusland, China, Iran en Noord-Korea vallen altijd af.',
                'toon_als' => $inkoop,
            ],
            [
                'key' => 'pg_subverwerkers',
                'type' => 'radio',
                'label' => 'Schakelt de leverancier zelf andere partijen in voor deze dienst?',
                'hint' => 'Bijvoorbeeld voor hosting, support, ontwikkeling of het versturen van mail.',
                'verplicht' => true,
                'toon_als' => $inkoop,
                'aanname' => 'ja',
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'ja' => ['label' => 'Ja'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_subverwerkers_locatie',
                'type' => 'radio',
                'label' => 'Waar zitten die partijen?',
                'hint' => 'Gaan persoonsgegevens buiten de EER? Dan gelden er extra regels.',
                'verplicht' => true,
                'toon_als' => $inkoop + [
                    'pg_subverwerkers' => ['ja', 'onbekend'],
                    'informatie_soorten' => q2_persoonsgegeven_sleutels(),
                ],
                'aanname' => 'buiten-eer',
                'opties' => q2_locatie_opties('Allemaal in de EER'),
            ],
            [
                'key' => 'leverancier_bewijs',
                'type' => 'radio',
                'label' => 'Kan de leverancier met een onafhankelijk onderzoek aantonen dat hij veilig werkt?',
                'hint' => 'Bijvoorbeeld een certificaat voor ISO 27001, of een rapport volgens ISAE 3402 of SOC 2. '
                    . 'Het onderzoek moet over de hele dienst gaan.',
                'verplicht' => true,
                'toon_als' => $inkoop,
                'aanname' => 'nee',
                'opties' => [
                    'volledig' => ['label' => 'Ja, en het onderzoek gaat over de hele dienst'],
                    'deels' => ['label' => 'Ja, maar niet over de hele dienst'],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'leverancier_melden',
                'type' => 'radio',
                'label' => 'Is afgesproken hoe snel de leverancier een incident of een kwetsbaarheid meldt?',
                'hint' => 'De organisatie moet een ernstig incident binnen 24 uur melden. Dat lukt alleen '
                    . 'als de leverancier het eerder doorgeeft.',
                'verplicht' => true,
                'toon_als' => $inkoop,
                'aanname' => 'nee',
                'opties' => [
                    'binnen_uren' => ['label' => 'Ja, binnen enkele uren'],
                    'later' => ['label' => 'Ja, maar met een langere termijn'],
                    'nee' => ['label' => 'Nee, dat is niet afgesproken'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'leverancier_overstap',
                'type' => 'radio',
                'label' => 'Hoe makkelijk stap je over naar een andere leverancier?',
                'verplicht' => true,
                'toon_als' => $inkoop,
                'aanname' => 'lastig',
                'opties' => [
                    'makkelijk' => ['label' => 'Makkelijk', 'hint' => 'Binnen een paar maanden, zonder grote kosten.'],
                    'lastig' => ['label' => 'Lastig', 'hint' => 'Het kan, maar het kost veel tijd of geld.'],
                    'vast' => ['label' => 'Bijna niet', 'hint' => 'We zitten vast aan deze leverancier.'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'leverancier_cbw',
                'type' => 'radio',
                'optioneel' => true,
                'label' => 'Valt de leverancier zelf onder de Cyberbeveiligingswet of NIS2?',
                'hint' => 'Bijvoorbeeld een clouddienst, een datacenter, of een bedrijf dat ICT voor anderen '
                    . 'beheert. Die heeft dan zelf ook een zorgplicht en een meldplicht.',
                'toon_als' => $inkoop,
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
        ],
    ];
}

// ---------------------------------------------------------------------------
// Stap 2: de weging

function q2_sectie_classificatie(): array
{
    $proces = q2_toelichting_proces();
    $systeem = q2_toelichting_systeem();

    return [
        'id'    => 'classificatie',
        'titel' => 'Hoe belangrijk zijn het proces en het systeem?',
        'stap' => 2,
        'intro' => 'Kies wat het best past. Twijfel je? Klap dan de uitleg open. Deze keuzes wegen zwaar in de '
            . 'uitkomst, bijvoorbeeld voor de eis aan beschikbaarheid en voor de regels over de cloud.',
        'vragen' => [
            [
                'key' => 'proces_classificatie',
                'type' => 'radio',
                'label' => 'Hoe belangrijk is het proces voor de organisatie?',
                'verplicht' => true,
                'opties' => [
                    'ondersteunend' => [
                        'label' => 'Ondersteunend',
                        'hint' => 'Handig om te hebben.',
                        'uitleg' => $proces['ondersteunend'],
                    ],
                    'bijdragend' => [
                        'label' => 'Bijdragend',
                        'hint' => 'Zonder dit proces gaat het minder efficiënt.',
                        'uitleg' => $proces['bijdragend'],
                    ],
                    'strategisch' => [
                        'label' => 'Strategisch',
                        'hint' => 'Hoort bij het primaire proces of een wettelijke taak.',
                        'uitleg' => $proces['strategisch'],
                    ],
                    'kritisch-strategisch' => [
                        'label' => 'Kritisch strategisch',
                        'hint' => 'Nodig voor de samenleving, of een week stilstand is al ernstig.',
                        'uitleg' => $proces['kritisch-strategisch'],
                    ],
                ],
            ],
            [
                'key' => 'systeem_classificatie',
                'type' => 'radio',
                'label' => 'Hoe belangrijk is het systeem voor dat proces?',
                'verplicht' => true,
                'opties' => [
                    'nuttig' => [
                        'label' => 'Nuttig',
                        'hint' => 'Handig om te hebben.',
                        'uitleg' => $systeem['nuttig'],
                    ],
                    'belangrijk' => [
                        'label' => 'Belangrijk',
                        'hint' => 'Zonder dit systeem kost het proces veel meer moeite.',
                        'uitleg' => $systeem['belangrijk'],
                    ],
                    'vitaal' => [
                        'label' => 'Vitaal',
                        'hint' => 'Zonder dit systeem kan het proces niet.',
                        'uitleg' => $systeem['vitaal'],
                    ],
                ],
            ],
            [
                'key' => 'proces_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom passen deze twee keuzes bij het proces en het systeem?',
                'hint' => 'Deze keuzes kan de tool niet voor je maken. Schrijf op waarom je zo kiest. Dan kan '
                    . 'later iedereen zien hoe de keuzes tot stand kwamen.',
                'verplicht' => true,
                'hoogte' => 4,
            ],
            [
                'key' => 'bedrijfskritisch',
                'type' => 'radio',
                'label' => 'Valt de organisatie stil als dit systeem uitvalt?',
                'hint' => 'Dan heet het systeem bedrijfskritisch. De tool stelt alvast iets voor, uit het belang '
                    . 'van het proces en het systeem.',
                'verplicht' => true,
                'voorstel' => true,
                'aanname' => 'ja',
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'ja' => ['label' => 'Ja'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
        ],
    ];
}

function q2_sectie_biv(): array
{
    return [
        'id'    => 'biv',
        'titel' => 'Wat mag er misgaan?',
        'stap' => 2,
        'intro' => 'Hier hoef je maar één vraag echt te beantwoorden: hoe lang mag het plat liggen? De '
            . 'andere eisen volgen uit wat je al hebt ingevuld. Vind je dat het zwaarder ligt? Dan kun je ze '
            . 'nog verhogen.',
        'vragen' => [
            [
                'key' => 'beschikbaarheid',
                'type' => 'radio',
                'label' => 'Hoe lang mag het plat liggen?',
                'hint' => 'De tool kiest alvast wat past bij het belang van het proces, en bij wat er van dit '
                    . 'systeem afhangt. Klopt dat niet? Kies dan iets anders, en leg kort uit waarom.',
                'verplicht' => true,
                'voorstel' => true,
                'opties' => q2_biv_opties(q2_toelichting_beschikbaarheid(), [
                    'zl' => 'Langer dan een week is geen probleem',
                    'l'  => 'Vier dagen tot een week',
                    'm'  => 'Eén tot drie dagen',
                    'h'  => 'Vier tot acht uur',
                    'zh' => 'Nul tot vier uur',
                ]),
            ],
            [
                'key' => 'beschikbaarheid_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom dit niveau?',
                'hint' => 'De tool schrijft alvast op waarom hij dit voorstelt. Neem je het voorstel over? '
                    . 'Laat de tekst dan staan. Kies je iets anders? Vervang de tekst dan.',
                'verplicht' => true,
                'motivatie_voor' => 'beschikbaarheid',
                'hoogte' => 3,
            ],
            [
                'key' => 'afgeleid_integriteit',
                'type' => 'afgeleid',
                'bron' => 'i',
                'label' => 'Hoe erg is het als de gegevens niet kloppen?',
                'hint' => 'Deze eis volgt uit het belang van het proces en uit de persoonsgegevens '
                    . 'die erin zitten.',
            ],
            [
                'key' => 'integriteit_bijstelling',
                'type' => 'select',
                'label' => 'Wil je die eis aanpassen?',
                'hint' => 'Als behoeftesteller kun je de eis alleen hoger maken. De laagste eis volgt uit de AVG '
                    . 'en uit het belang van het proces. De beoordelaar mag de eis ook lager maken, maar legt '
                    . 'dan uit waarom.',
                'opties' => q2_bijstelling_opties(),
            ],
            [
                'key' => 'integriteit_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom dit niveau?',
                'verplicht' => true,
                'motivatie_voor' => 'integriteit',
                'hoogte' => 3,
            ],
            [
                'key' => 'afgeleid_vertrouwelijkheid',
                'type' => 'afgeleid',
                'bron' => 'v',
                'label' => 'Hoe erg is het als de verkeerde persoon meekijkt?',
                'hint' => 'Deze eis volgt uit het soort informatie en uit de persoonsgegevens.',
            ],
            [
                'key' => 'vertrouwelijkheid_bijstelling',
                'type' => 'select',
                'label' => 'Wil je die eis aanpassen?',
                'hint' => 'Als behoeftesteller kun je de eis alleen hoger maken. De beoordelaar mag de eis ook '
                    . 'lager maken, maar legt dan uit waarom.',
                'opties' => q2_bijstelling_opties(),
            ],
            [
                'key' => 'vertrouwelijkheid_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom dit niveau?',
                'verplicht' => true,
                'motivatie_voor' => 'vertrouwelijkheid',
                'hoogte' => 3,
            ],
            [
                'key' => 'rpo',
                'type' => 'select',
                'label' => 'Hoeveel werk mag er verloren gaan bij een ramp of een zware storing?',
                'hint' => 'Hieruit volgt hoe vaak er een reservekopie moet komen. De tool stelt alvast iets voor '
                    . 'dat past bij de eis aan beschikbaarheid.',
                'verplicht' => true,
                'voorstel' => true,
                'opties' => [
                    'geen' => ['label' => 'Niets'],
                    '1-uur' => ['label' => 'Maximaal 1 uur'],
                    '4-uur' => ['label' => 'Maximaal 4 uur'],
                    '24-uur' => ['label' => 'Maximaal 24 uur'],
                    '1-week' => ['label' => 'Maximaal een week'],
                    'nvt' => ['label' => 'Niet van toepassing, want er is geen data om terug te zetten'],
                ],
            ],
            [
                'key' => 'rpo_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom dit antwoord?',
                'verplicht' => true,
                'motivatie_voor' => 'rpo',
                'hoogte' => 3,
            ],
        ],
    ];
}

/**
 * Eerst welke soorten schade kunnen optreden, dan per aangevinkte soort hoe
 * zwaar. Het niveau zelf kiest niemand: q2_tbb() rekent het uit.
 */
function q2_sectie_belangen(): array
{
    $soorten = [];
    $vragen = [];

    foreach (q2_tbb_categorieen() as $key => $categorie) {
        $soorten[$key] = [
            'label' => $categorie['soort'],
            'hint' => 'Telt vanaf: ' . mb_strtolower($categorie['niveaus']['tbb4'], 'UTF-8') . '.',
        ];

        $vragen[] = [
            'key' => $key,
            'type' => 'radio',
            'label' => $categorie['label'],
            'verplicht' => true,
            'toon_als' => ['tbb_soorten' => [$key]],
            'opties' => array_map(static fn (string $label): array => ['label' => $label], $categorie['niveaus']),
        ];
    }

    return [
        'id'    => 'belangen',
        'titel' => 'Wat staat er op het spel?',
        'stap' => 2,
        'intro' => 'Hier rekent de tool uit hoe zwaar de belangen zijn die je beschermt. Dat heet het niveau '
            . 'van de te beschermen belangen (TBB). Het weegt zwaar, want bij TBB 1, 2 of 3 is het systeem '
            . 'cruciaal. Dan volgt een volledige risicoanalyse, mag er geen publieke cloud komen, en kijkt de CIO '
            . 'mee. Jij kiest alleen welke schade een incident kan geven.',
        'vragen' => [
            [
                'key' => 'tbb_soorten',
                'type' => 'checkbox',
                'label' => 'Welke schade kan een incident in het ergste geval geven?',
                'hint' => 'Denk aan uitval, aan fouten en aan een lek. Vink een soort alleen aan als die '
                    . 'schade realistisch is. Is geen enkele soort realistisch, kies dan ‘Geen van deze’.',
                'verplicht' => true,
                'opties' => $soorten + [
                    'geen' => [
                        'label' => 'Geen van deze',
                        'hint' => 'Geen enkele soort haalt de ondergrens die erbij staat.',
                    ],
                ],
            ],
            ...$vragen,
            [
                'key' => 'afgeleid_tbb',
                'type' => 'afgeleid',
                'bron' => 'tbb',
                'label' => 'Te beschermen belangen',
                'hint' => 'Het zwaarste niveau telt. TBB 4 is het lichtst, TBB 1 het zwaarst. Naast de '
                    . 'schade tellen de rubricering en de eisen aan beschikbaarheid, integriteit en '
                    . 'vertrouwelijkheid mee.',
            ],
            [
                'key' => 'tbb_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom dit niveau?',
                'hint' => 'De tool zet de opbouw van het niveau hier alvast neer. Vul aan met wat de lezer '
                    . 'moet weten om de schade te begrijpen.',
                'verplicht' => true,
                'motivatie_voor' => 'tbb',
                'hoogte' => 7,
            ],
        ],
    ];
}

function q2_sectie_cloud(): array
{
    return [
        'id'    => 'cloud',
        'titel' => 'Staat het in de cloud?',
        'stap' => 2,
        'intro' => 'Het cloudbeleid van het Rijk uit 2026 stelt grenzen aan de cloud van een leverancier. '
            . 'Software die je via internet gebruikt (SaaS) telt ook mee. Welke regels er precies gelden, rekent '
            . 'de tool zelf uit.',
        'vragen' => [
            [
                'key' => 'cloud',
                'type' => 'radio',
                'label' => 'Gebruiken jullie een clouddienst?',
                'verplicht' => true,
                'opties' => [
                    'nee' => ['label' => 'Nee, alles draait op onze eigen servers'],
                    'mogelijk' => ['label' => 'Mogelijk, we sluiten het niet uit'],
                    'ja' => ['label' => 'Ja'],
                ],
            ],
            [
                'key' => 'cloud_type',
                'type' => 'radio',
                'label' => 'Wat voor cloud is dat?',
                'verplicht' => true,
                'toon_als' => q2_met_cloud(),
                'aanname' => 'publiek',
                'opties' => [
                    'publiek' => ['label' => 'Publieke cloud', 'hint' => 'Gedeeld met andere klanten.'],
                    'community' => ['label' => 'Community cloud', 'hint' => 'Gedeeld met andere overheden.'],
                    'prive' => ['label' => 'Private cloud', 'hint' => 'Alleen voor ons.'],
                    'hybride' => ['label' => 'Hybride'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'cloud_aanbieder',
                'type' => 'radio',
                'label' => 'Wie levert die cloud?',
                'verplicht' => true,
                'toon_als' => q2_met_cloud(),
                'aanname' => 'extern',
                'opties' => [
                    'extern' => ['label' => 'Een externe leverancier'],
                    'overheid' => [
                        'label' => 'Een overheidsorganisatie',
                        'hint' => 'Het cloudbeleid geldt dan maar voor een deel.',
                    ],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'cloud_locatie',
                'type' => 'radio',
                'label' => 'Waar staat de data, en waar wordt die verwerkt?',
                'hint' => 'Tel ook de reservekopieën mee, en de hulp op afstand vanuit het buitenland.',
                'verplicht' => true,
                'toon_als' => q2_met_cloud(),
                'aanname' => 'buiten-eer',
                'opties' => q2_locatie_opties('Alleen in de EER of Zwitserland'),
            ],
            [
                'key' => 'cloud_jurisdictie',
                'type' => 'radio',
                'label' => 'Kan een land buiten de EU of de EER de leverancier iets opleggen?',
                'hint' => 'Dat kan als de leverancier of zijn moederbedrijf daar zit. Denk aan een Amerikaans '
                    . 'bedrijf, ook als de data in Europa staat.',
                'verplicht' => true,
                'toon_als' => q2_met_cloud(),
                'aanname' => 'ja',
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'ja' => ['label' => 'Ja'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'cloud_toepassing',
                'type' => 'checkbox',
                'label' => 'Gaat het om een van deze diensten?',
                'hint' => 'Niets aanvinken betekent: geen van deze. Voor mail en documenten gelden '
                    . 'strengere regels.',
                'toon_als' => q2_met_cloud(),
                'opties' => [
                    'email' => ['label' => 'Mail'],
                    'documenten' => ['label' => 'Documenten of bestandsopslag'],
                ],
            ],
            [
                'key' => 'cloud_sleutels',
                'type' => 'radio',
                'label' => 'Wie beheert de sleutels waarmee de data wordt versleuteld?',
                'verplicht' => true,
                'toon_als' => q2_met_cloud(),
                'aanname' => 'leverancier',
                'opties' => [
                    'eigen' => ['label' => 'De organisatie zelf'],
                    'derde' => ['label' => 'Een andere partij die daarvoor gecertificeerd is'],
                    'leverancier' => ['label' => 'De cloudleverancier'],
                    'geen' => ['label' => 'De data wordt niet versleuteld'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'cloud_leverancier',
                'type' => 'text',
                'optioneel' => true,
                'label' => 'Welke clouddienstverlener is het?',
                'toon_als' => q2_met_cloud(),
            ],
        ],
    ];
}

function q2_sectie_dreiging(): array
{
    return [
        'id'    => 'dreiging',
        'titel' => 'Voor wie is dit interessant?',
        'stap' => 2,
        'intro' => 'Of spionnen of criminelen het op dit systeem gemunt hebben, is moeilijk te zeggen. Daarom '
            . 'vragen we iets anders: wat maakt dit systeem interessant voor hen? Daaruit volgt hoe groot de '
            . 'dreiging is.',
        'vragen' => [
            [
                'key' => 'dreiging_kenmerken',
                'type' => 'checkbox',
                'label' => 'Zit een van deze dingen in of achter dit systeem?',
                'hint' => 'Niets aanvinken betekent: geen van deze.',
                'opties' => [
                    'besluitvorming' => [
                        'label' => 'Politieke besluiten, standpunten of woordvoering die nog niet openbaar zijn',
                    ],
                    'onderhandeling' => [
                        'label' => 'Onderhandelingen, aanbestedingen of contracten waar veel geld of een groot '
                            . 'belang mee gemoeid is',
                    ],
                    'internationaal' => [
                        'label' => 'Informatie over internationale betrekkingen, diplomatie of defensie',
                    ],
                    'opsporing' => [
                        'label' => 'Informatie over opsporing, inlichtingen of veiligheid',
                    ],
                    'bewindspersonen' => [
                        'label' => 'Gegevens over bewindspersonen of topambtenaren, zoals hun agenda, hun '
                            . 'reizen of waar ze zijn',
                    ],
                    'vitaal' => [
                        'label' => 'Een koppeling met een vitaal proces of met vitale infrastructuur',
                    ],
                    'identiteit' => [
                        'label' => 'Gegevens waarmee je veel burgers kunt identificeren, zoals hun BSN of een '
                            . 'kopie van hun paspoort',
                    ],
                ],
            ],
            [
                'key' => 'afgeleid_dreiging',
                'type' => 'afgeleid',
                'bron' => 'dreiging',
                'label' => 'Dreigingsprofiel',
                'hint' => 'Is het dreigingsprofiel hoog? Dan moet het systeem bestand zijn tegen '
                    . 'inlichtingendiensten, terreurgroepen of georganiseerde misdaad. Dan volgen een volledige '
                    . 'risicoanalyse en advies van de AIVD of de MIVD.',
            ],
            [
                'key' => 'dreiging_verhoging',
                'type' => 'radio',
                'label' => 'Ben je het daarmee eens?',
                'opties' => [
                    '' => ['label' => 'Ja, neem deze uitkomst over'],
                    'hoog' => ['label' => 'Nee, het dreigingsprofiel is toch hoog'],
                ],
            ],
            [
                'key' => 'dreiging_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom dit dreigingsprofiel?',
                'hint' => 'De tool schrijft zijn eigen redenering alvast op. Weet je meer over wie hier belang '
                    . 'bij heeft? Vul de tekst dan aan, of vervang hem.',
                'verplicht' => true,
                'motivatie_voor' => 'dreiging',
                'hoogte' => 3,
            ],
            [
                'key' => 'aanvullende_wetgeving',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Gelden er nog andere wetten of regels?',
                'hint' => 'De gewone regels zet de tool er zelf bij. Denk aan wetten voor jullie sector, of aan '
                    . 'eigen regels van de organisatie.',
                'hoogte' => 3,
            ],
            [
                'key' => 'externe_eisen',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Stelt een partij buiten de organisatie eisen?',
                'hint' => 'Bijvoorbeeld een partner in de keten.',
                'hoogte' => 3,
            ],
            [
                'key' => 'cis_normen',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Gelden er CIS Benchmarks of STIGs?',
                'hint' => 'Dat zijn lijsten met veilige instellingen, voor besturingssystemen, databases of '
                    . 'netwerkapparatuur.',
                'hoogte' => 2,
            ],
            [
                'key' => 'opmerkingen',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Zijn er nog bijzonderheden die we moeten weten?',
                'hoogte' => 3,
            ],
        ],
    ];
}
