<?php

/**
 * De vragenlijst.
 *
 * Dit is het enige bestand dat je hoeft aan te passen als je vragen wilt
 * toevoegen, herformuleren of weghalen. Het formulier, de controle en het
 * advies lezen allemaal uit deze definitie. De sleutels per vraag staan in
 * ../README.md. Deze vragenlijst gebruikt er één van zichzelf:
 *
 *   aanname   - waarde die geldt als iemand 'weet ik niet' antwoordt; de tool
 *               rekent daarmee door en het advies vermeldt dat het een aanname is
 *
 * Type 'afgeleid' is geen invoerveld maar een uitkomstblok: de tool rekent de
 * waarde uit en laat zien waar die vandaan komt. Zie afleiding.php.
 */

declare(strict_types=1);

/** @return list<array<string,mixed>> */
function qs_secties(): array
{
    return [
        qs_sectie_route(),
        qs_sectie_project(),
        qs_sectie_scope(),
        qs_sectie_informatie(),
        qs_sectie_privacy(),
        qs_sectie_archief(),
        qs_sectie_ai(),
        qs_sectie_inkoop(),
        qs_sectie_classificatie(),
        qs_sectie_biv(),
        qs_sectie_cloud(),
        qs_sectie_dreiging(),
    ];
}

/** Alleen van toepassing als er iets te beschermen valt. */
function qs_volledige_scan(): array
{
    return ['te_beschermen_informatie' => ['ja']];
}

/** Hulpje: BIV-opties opbouwen uit een toelichtingenlijst. */
function qs_biv_opties(array $toelichting, array $korteHints): array
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
function qs_bijstelling_opties(): array
{
    return [
        ''   => ['label' => 'Nee, neem de afgeleide eis over'],
        'zl' => ['label' => 'Stel bij naar Zeer laag'],
        'l'  => ['label' => 'Stel bij naar Laag'],
        'm'  => ['label' => 'Stel bij naar Midden'],
        'h'  => ['label' => 'Stel bij naar Hoog'],
        'zh' => ['label' => 'Stel bij naar Zeer hoog'],
    ];
}

// ---------------------------------------------------------------------------

function qs_sectie_route(): array
{
    return [
        'id'    => 'route',
        'titel' => 'Wie vult dit in?',
        'stap' => 1,
        'intro' => 'De quickscan wordt in twee stappen gemaakt. De behoeftesteller beschrijft wat '
            . 'er gaat gebeuren. De beoordelaar weegt dat en stelt de eisen vast. Beiden gebruiken '
            . 'deze tool, alleen het stempel op wat je invult verschilt.',
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
                        'hint' => 'Je beoordeelt een ingevulde inventarisatie. Je mag de wegingen '
                            . 'bijstellen, ook naar beneden, mits je motiveert waarom.',
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
            [
                'key' => 'te_beschermen_informatie',
                'type' => 'radio',
                'label' => 'Wordt er informatie verwerkt die bescherming nodig heeft?',
                'verplicht' => true,
                'opties' => [
                    'ja' => [
                        'label' => 'Ja',
                        'hint' => 'Het normale geval. Je krijgt de volledige scan.',
                    ],
                    'nee' => [
                        'label' => 'Nee, er valt hier niets te beschermen',
                        'hint' => 'Kies dit alleen als alle drie kloppen: er zitten geen '
                            . 'persoonsgegevens in, alle informatie is openbaar of mag openbaar '
                            . 'worden, én uitval heeft geen gevolgen voor de organisatie, burgers '
                            . 'of gebruikers.',
                    ],
                ],
            ],
            [
                'key' => 'geen_bescherming_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom valt er niets te beschermen?',
                'hint' => 'De beoordelaar toetst deze motivatie. Wees concreet over wat er in het '
                    . 'systeem terechtkomt en over wat er gebeurt als het uitvalt.',
                'verplicht_als' => ['te_beschermen_informatie' => ['nee']],
                'toon_als' => ['te_beschermen_informatie' => ['nee']],
                'hoogte' => 4,
            ],
        ],
    ];
}

function qs_sectie_project(): array
{
    return [
        'id'    => 'project',
        'titel' => 'Over het project',
        'stap' => 1,
        'intro' => 'Een paar gegevens zodat we het advies kunnen terugvinden en weten bij wie we '
            . 'terecht kunnen.',
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
                'hint' => 'De beoordelaar neemt hierop contact op voor de risicoanalyse.',
            ],
            [
                'key' => 'organisatieonderdeel',
                'type' => 'text',
                'optioneel' => true,
                'label' => 'Voor welk team of welke afdeling is dit?',
            ],
        ],
    ];
}

function qs_sectie_scope(): array
{
    return [
        'id'    => 'scope',
        'titel' => 'Wat gaan jullie doen, en waarmee?',
        'stap' => 1,
        'intro' => 'Een quickscan gaat altijd over een combinatie van een proces (wat je doet) en '
            . 'een systeem (waarmee je het doet). Beschrijf ze los van elkaar.',
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
                        'hint' => 'Let op: dan bepaalt deze scan de grenzen van álle verwerkingen '
                            . 'onder de nadere opdrachten.',
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
                'label' => 'Worden andere processen of systemen straks van dit systeem afhankelijk?',
                'hint' => 'Als anderen niet verder kunnen zonder dit systeem, erft het hun '
                    . 'beschikbaarheidseis. Dat trekt de eis hieronder omhoog.',
                'verplicht' => true,
                'toon_als' => qs_volledige_scan(),
                'aanname' => 'enkele',
                'opties' => [
                    'nee' => ['label' => 'Nee, dit staat op zichzelf'],
                    'enkele' => ['label' => 'Ja, enkele'],
                    'veel' => ['label' => 'Ja, veel processen of juist een kritiek proces'],
                    'onbekend' => ['label' => 'Weet ik niet'],
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

function qs_sectie_informatie(): array
{
    return [
        'id'    => 'informatie',
        'titel' => 'Welke informatie gaat erin?',
        'stap'  => 1,
        'intro' => 'Kruis aan wat er in het systeem terecht kan komen. Ga uit van het slechtste '
            . 'geval: alles wat het systeem tóélaat, gebeurt ook. Bij een raamcontract tellen ook de '
            . 'verwerkingen onder toekomstige nadere opdrachten mee.',
        'toon_als' => qs_volledige_scan(),
        'vragen' => [
            [
                'key' => 'informatie_soorten',
                'type' => 'checkbox',
                'label' => 'Welke soorten informatie worden verwerkt?',
                'hint' => 'Kruis alles aan wat van toepassing kan zijn. Hieruit leiden we af of er '
                    . 'persoonsgegevens in het spel zijn, hoeveel categorieën dat zijn en of er '
                    . 'bijzondere gegevens bij zitten. Die vragen hoef je dus niet apart te '
                    . 'beantwoorden.',
                'verplicht' => true,
                'groepen' => qs_informatiegroepen(),
                'opties' => qs_informatiesoorten(),
            ],
            [
                'key' => 'informatie_anders',
                'type' => 'textarea',
                'label' => 'Staat er iets niet bij?',
                'hint' => 'Eén soort per regel. Zitten daar persoonsgegevens bij, kruis hierboven '
                    . 'dan ook "andere persoonsgegevens" aan.',
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
                'hint' => 'Kijk naar het gevoeligste stuk dat erin kán komen. Uit dit antwoord volgt '
                    . 'straks de eis aan vertrouwelijkheid.',
                'verplicht' => true,
                'opties' => [
                    'openbaar' => [
                        'label' => 'Openbaar',
                        'hint' => 'Mag iedereen zien.',
                        'uitleg' => qs_toelichting_vertrouwelijkheid()['zl'],
                    ],
                    'intern' => [
                        'label' => 'Alleen voor intern gebruik',
                        'hint' => 'Niet openbaar, maar ook niet gerubriceerd.',
                        'uitleg' => qs_toelichting_vertrouwelijkheid()['m'],
                    ],
                    'dep-vertrouwelijk' => [
                        'label' => 'Departementaal vertrouwelijk',
                        'hint' => 'Of vergelijkbaar: personeelsvertrouwelijk, commercieel '
                            . 'vertrouwelijk, medisch geheim.',
                        'uitleg' => qs_toelichting_vertrouwelijkheid()['h'],
                    ],
                    'staatsgeheim' => [
                        'label' => 'Staatsgeheim of NATO restricted en hoger',
                        'hint' => 'Let op: hierbij is publieke cloud altijd uitgesloten.',
                        'uitleg' => qs_toelichting_vertrouwelijkheid()['zh'],
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
function qs_informatiesoorten(): array
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
            'woordvoering' => 'Woordvoeringslijnen of communicatiemateriaal',
            'contract' => 'Contracten, offertes of aanbestedingsinformatie',
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
function qs_informatiegroepen(): array
{
    $perGroep = [];
    foreach (qs_informatiesoorten() as $sleutel => $optie) {
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

function qs_sectie_privacy(): array
{
    return [
        'id'    => 'privacy',
        'titel' => 'Waarvoor en op welke grond?',
        'stap' => 1,
        'intro' => 'Deze vragen bepalen of er een DPIA nodig is en of de verwerking überhaupt mag. '
            . 'Ze verschijnen alleen omdat je hebt aangegeven dat er persoonsgegevens in het systeem '
            . 'komen.',
        'toon_als' => ['informatie_soorten' => qs_persoonsgegeven_sleutels()],
        'vragen' => [
            [
                'key' => 'pg_doel',
                'type' => 'textarea',
                'label' => 'Waarvoor worden de persoonsgegevens gebruikt?',
                'hint' => 'Eén concreet doel per regel. "Voor de dienstverlening" is te vaag: '
                    . 'waar worden de gegevens feitelijk voor ingezet?',
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
                        'hint' => 'Let op: toestemming moet vrij te weigeren en in te trekken zijn. '
                            . 'Bij de overheid is dat vaak lastig hard te maken.',
                    ],
                    'vitaal_belang' => ['label' => 'Vitaal belang van een persoon'],
                    'gerechtvaardigd_belang' => [
                        'label' => 'Gerechtvaardigd belang',
                        'hint' => 'Een overheidsorgaan kan hier voor de uitvoering van zijn taken '
                            . 'géén beroep op doen.',
                    ],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_subsidiariteit',
                'type' => 'radio',
                'label' => 'Kan hetzelfde doel ook met minder of zonder persoonsgegevens?',
                'hint' => 'Subsidiariteit: is dit de minst ingrijpende manier om het doel te bereiken?',
                'verplicht' => true,
                'aanname' => 'kan_minder',
                'opties' => [
                    'minst_ingrijpend' => [
                        'label' => 'Nee, dit is de minst ingrijpende manier',
                    ],
                    'kan_minder' => [
                        'label' => 'Ja, het kan met minder gegevens of anoniemer',
                        'hint' => 'Dan moet gemotiveerd worden waarom daar niet voor is gekozen.',
                    ],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_proportionaliteit',
                'type' => 'radio',
                'label' => 'Staat de inbreuk op de privacy in verhouding tot het doel?',
                'hint' => 'Proportionaliteit: weegt wat we ermee bereiken op tegen wat het de '
                    . 'betrokkene kost?',
                'verplicht' => true,
                'aanname' => 'twijfel',
                'opties' => [
                    'in_verhouding' => ['label' => 'Ja, dat is in verhouding'],
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
                    'kortdurend' => ['label' => 'Eenmalig of kortdurend'],
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
                'key' => 'pg_subverwerkers',
                'type' => 'radio',
                'label' => 'Schakelt de leverancier zelf andere partijen in?',
                'hint' => 'Subverwerkers, bijvoorbeeld voor hosting, support of het versturen van mail. '
                    . 'Hun locatie telt mee voor internationale doorgifte.',
                'verplicht' => true,
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
                'verplicht' => true,
                'toon_als' => ['pg_subverwerkers' => ['ja', 'onbekend']],
                'aanname' => 'buiten-eer',
                'opties' => [
                    'eer' => ['label' => 'Allemaal binnen de EER'],
                    'adequaatheid' => ['label' => 'Ook buiten de EER, met adequaatheidsbesluit'],
                    'modelcontract' => ['label' => 'Ook buiten de EER, op basis van een modelcontract'],
                    'buiten-eer' => ['label' => 'Ook buiten de EER, zonder die waarborgen'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'pg_rechten',
                'type' => 'radio',
                'optioneel' => true,
                'label' => 'Kan het systeem inzage, correctie en verwijdering aan?',
                'hint' => 'Betrokkenen hebben recht op inzage in hun gegevens, op correctie en op '
                    . 'verwijdering. Het systeem moet dat praktisch mogelijk maken.',
                'opties' => [
                    'volledig' => ['label' => 'Ja, volledig'],
                    'deels' => ['label' => 'Deels, of alleen handmatig'],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
        ],
    ];
}

function qs_sectie_archief(): array
{
    return [
        'id'    => 'archief',
        'titel' => 'Bewaren, vernietigen en archiveren',
        'stap' => 1,
        'intro' => 'Wat een overheidsorgaan in zijn taak opmaakt of ontvangt, valt onder de '
            . 'Archiefwet 2026, ongeacht de vorm. Dus ook records in een clouddienst, chatlogs en '
            . 'mail. Daar hoort een bewaartermijn bij, en de plicht om na afloop daadwerkelijk te '
            . 'vernietigen of over te brengen. De overbrengingstermijn is onder deze wet tien jaar in '
            . 'plaats van twintig, dus dat komt sneller dichtbij dan je denkt. Het advies legt uit '
            . 'hoe je de archiefprocessen inricht.',
        'toon_als' => qs_volledige_scan(),
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
                        'hint' => 'De vastgestelde selectielijst bepaalt per categorie of iets '
                            . 'blijvend bewaard wordt of na een termijn vernietigd.',
                    ],
                    'wettelijk' => [
                        'label' => 'Een wettelijke termijn uit andere regelgeving',
                        'hint' => 'Bijvoorbeeld een fiscale of personeelsrechtelijke bewaartermijn.',
                    ],
                    'eigen' => [
                        'label' => 'Een termijn die we zelf hebben bepaald',
                        'hint' => 'Dat mag, maar moet onderbouwd worden vanuit het doel.',
                    ],
                    'onbepaald' => [
                        'label' => 'Nog niet bepaald',
                        'hint' => 'Dan is dit het eerste dat geregeld moet worden: zonder termijn '
                            . 'kun je niet voldoen aan de AVG en niet aan de Archiefwet.',
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
                'label' => 'Kan het systeem informatie na de termijn daadwerkelijk vernietigen?',
                'hint' => 'Bewaren mag niet langer dan nodig. Een systeem dat niets kan weggooien, '
                    . 'dwingt je tot een overtreding.',
                'verplicht' => true,
                'aanname' => 'nee',
                'opties' => [
                    'automatisch' => ['label' => 'Ja, automatisch op basis van de termijn'],
                    'handmatig' => ['label' => 'Ja, maar alleen handmatig'],
                    'nee' => ['label' => 'Nee'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'archief_export',
                'type' => 'radio',
                'label' => 'Kun je de informatie eruit halen zonder de leverancier nodig te hebben?',
                'hint' => 'Nodig voor overbrenging naar het Nationaal Archief, voor een verzoek om informatie (Woo), '
                    . 'en voor het einde van het contract.',
                'verplicht' => true,
                'aanname' => 'nee',
                'opties' => [
                    'open' => [
                        'label' => 'Ja, in een open, leveranciersonafhankelijk formaat',
                        'hint' => 'Bijvoorbeeld PDF/A, XML, CSV of JSON, mét metagegevens.',
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

function qs_sectie_ai(): array
{
    return [
        'id'    => 'ai',
        'titel' => 'Komt er AI bij kijken?',
        'stap' => 1,
        'intro' => 'Ook "we sluiten het niet uit" telt mee. Als een systeem AI toelaat, houden we er '
            . 'rekening mee dat het gebruikt wordt.',
        'toon_als' => qs_volledige_scan(),
        'vragen' => [
            [
                'key' => 'ai',
                'type' => 'radio',
                'label' => 'Wordt er gebruikgemaakt van AI?',
                'verplicht' => true,
                'opties' => [
                    'uitgesloten' => ['label' => 'Nee, inzet van AI is uitgesloten'],
                    'mogelijk' => ['label' => 'We sluiten toekomstig gebruik niet uit'],
                    'onderdeel' => ['label' => 'Ja, AI is onderdeel van de eisen'],
                ],
            ],
            [
                'key' => 'ai_omschrijving',
                'type' => 'textarea',
                'label' => 'Waarvoor wordt de AI ingezet, en hoe werkt het?',
                'verplicht' => true,
                'toon_als' => ['ai' => ['mogelijk', 'onderdeel']],
                'hoogte' => 3,
            ],
            [
                'key' => 'ai_toepassing',
                'type' => 'checkbox',
                'label' => 'Waar raakt die AI aan?',
                'hint' => 'Vink alles aan wat van toepassing kan zijn. Hieruit volgt de risicoklasse '
                    . 'onder de AI verordening. Niets aanvinken betekent: geen van deze.',
                'toon_als' => ['ai' => ['mogelijk', 'onderdeel']],
                'opties' => [
                    'verboden_scoring' => [
                        'label' => 'Mensen beoordelen of scoren op hun sociale gedrag',
                        'hint' => 'Met nadelige gevolgen op een ander terrein dan waar de gegevens '
                            . 'vandaan komen.',
                    ],
                    'verboden_emotie' => [
                        'label' => 'Emoties herkennen op de werkvloer of in het onderwijs',
                    ],
                    'verboden_biometrie' => [
                        'label' => 'Mensen op basis van biometrie indelen naar ras, geloof, '
                            . 'politieke opvatting of seksuele geaardheid',
                    ],
                    'verboden_realtime' => [
                        'label' => 'Mensen in de openbare ruimte live biometrisch identificeren',
                    ],
                    'verboden_manipulatie' => [
                        'label' => 'Gedrag onbewust sturen, of inspelen op leeftijd, beperking of '
                            . 'een kwetsbare positie',
                    ],
                    'hoog_onderwijs' => [
                        'label' => 'Toegang tot onderwijs, of het beoordelen van deelnemers',
                    ],
                    'hoog_werk' => [
                        'label' => 'Werving, selectie, beoordeling of ontslag van personeel',
                    ],
                    'hoog_diensten' => [
                        'label' => 'Toegang tot uitkeringen, subsidies of andere essentiële '
                            . 'voorzieningen',
                    ],
                    'hoog_rechtshandhaving' => [
                        'label' => 'Rechtshandhaving, opsporing of risicobeoordeling van personen',
                    ],
                    'hoog_migratie' => ['label' => 'Migratie, asiel of grenscontrole'],
                    'hoog_rechtspraak' => [
                        'label' => 'Ondersteuning van rechtspraak, of van verkiezingen en '
                            . 'democratische processen',
                    ],
                    'hoog_infrastructuur' => [
                        'label' => 'Beheer of bediening van kritieke infrastructuur',
                    ],
                    'hoog_biometrie' => [
                        'label' => 'Biometrische identificatie of categorisering in andere gevallen',
                    ],
                    'transparantie_interactie' => [
                        'label' => 'Het systeem praat rechtstreeks met mensen, bijvoorbeeld als chatbot',
                    ],
                    'transparantie_generatie' => [
                        'label' => 'Het systeem maakt of bewerkt tekst, beeld, audio of video',
                    ],
                ],
            ],
            [
                'key' => 'afgeleid_ai',
                'type' => 'afgeleid',
                'bron' => 'ai',
                'label' => 'Risicoklasse onder de AI verordening',
                'hint' => 'Volgt uit wat je hierboven hebt aangevinkt.',
                'toon_als' => ['ai' => ['mogelijk', 'onderdeel']],
            ],
            [
                'key' => 'ai_nieuw_algoritme',
                'type' => 'radio',
                'label' => 'Is dit algoritme of AI systeem nieuw voor de organisatie?',
                'hint' => 'Ook een bestaand product dat hier voor het eerst wordt ingezet is nieuw.',
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

function qs_sectie_inkoop(): array
{
    return [
        'id'    => 'inkoop',
        'titel' => 'Bijzondere situaties bij inkoop en leveranciers',
        'stap' => 1,
        'intro' => 'Bij deze vormen is de kans groter dat je niet aan de regels voldoet, en kost de '
            . 'voorbereiding meer tijd. Ze vragen om een motivatie en een jaarlijks herhaalde marktanalyse. Of de ABRO '
            . 'van toepassing is, leiden we af uit de procesclassificatie hieronder.',
        'toon_als' => qs_volledige_scan(),
        'vragen' => [
            [
                'key' => 'afwijkend',
                'type' => 'checkbox',
                'label' => 'Is hier sprake van een van deze situaties?',
                'hint' => 'Niets aanvinken betekent: geen van deze.',
                'opties' => [
                    'opensource' => ['label' => 'Open source'],
                    'productgericht' => [
                        'label' => 'Productgerichte uitvraag',
                        'hint' => 'De uitvraag noemt een specifiek product in plaats van functionaliteit.',
                    ],
                    'saas' => ['label' => 'Software als dienst (SaaS)'],
                ],
            ],
            [
                'key' => 'leverancier_land',
                'type' => 'text',
                'label' => 'Uit welk land komt de leverancier?',
                'hint' => 'Het land van de moederorganisatie, niet alleen van de Nederlandse '
                    . 'vestiging. Leveranciers uit Rusland, China, Iran en Noord-Korea worden '
                    . 'altijd uitgesloten.',
            ],
        ],
    ];
}

function qs_sectie_classificatie(): array
{
    $proces = qs_toelichting_proces();
    $systeem = qs_toelichting_systeem();

    return [
        'id'    => 'classificatie',
        'titel' => 'Hoe belangrijk zijn het proces en het systeem?',
        'stap' => 2,
        'intro' => 'Kies wat het dichtst in de buurt komt; klap de uitleg open als je twijfelt. Deze '
            . 'twee antwoorden bepalen een flink deel van de uitkomst: de beschikbaarheidseis, of de '
            . 'ABRO geldt, en of er sprake is van materieel cloudgebruik.',
        'toon_als' => qs_volledige_scan(),
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
                        'hint' => 'Maatschappelijk vitaal, of een week stilstand is al ernstig.',
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
                        'hint' => 'Zonder dit systeem kost het proces onevenredig veel moeite.',
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
                'key' => 'bedrijfskritisch',
                'type' => 'radio',
                'label' => 'Is dit een bedrijfskritisch systeem?',
                'hint' => 'Valt de organisatie stil als dit systeem uitvalt?',
                'verplicht' => true,
                'aanname' => 'ja',
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'ja' => ['label' => 'Ja'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'politiek_sensitief',
                'type' => 'radio',
                'label' => 'Ligt dit politiek gevoelig?',
                'verplicht' => true,
                'aanname' => 'ja',
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'ja' => ['label' => 'Ja'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'proces_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom die classificatie van het proces?',
                'hint' => 'Dit is een menselijke afweging die de tool niet kan maken. Leg vast '
                    . 'waarom, zodat later terug te halen is hoe deze beoordeling tot stand kwam.',
                'verplicht' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'systeem_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom die classificatie van het systeem?',
                'verplicht' => true,
                'hoogte' => 3,
            ],
        ],
    ];
}

function qs_sectie_biv(): array
{
    return [
        'id'    => 'biv',
        'titel' => 'Wat mag er misgaan?',
        'stap' => 2,
        'intro' => 'Eén vraag hoef je maar echt te beantwoorden: hoe lang mag het plat liggen. De '
            . 'eisen aan juistheid en vertrouwelijkheid volgen uit wat je hierboven hebt ingevuld. '
            . 'Je kunt ze alleen nog verhogen als je vindt dat het zwaarder ligt.',
        'toon_als' => qs_volledige_scan(),
        'vragen' => [
            [
                'key' => 'beschikbaarheid',
                'type' => 'radio',
                'label' => 'Hoe lang mag het plat liggen?',
                'hint' => 'We vullen alvast in wat past bij het belang van het proces en bij wat er '
                    . 'van dit systeem afhangt. Klopt dat niet, kies dan iets anders en leg kort uit '
                    . 'waarom.',
                'verplicht' => true,
                'voorstel' => true,
                'opties' => qs_biv_opties(qs_toelichting_beschikbaarheid(), [
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
                'hint' => 'De tool vult alvast in waaróm hij dit voorstelt. Neem je het voorstel '
                    . 'over, dan kun je dit laten staan; kies je iets anders, vervang het dan.',
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
                'label' => 'Wil je die eis bijstellen?',
                'hint' => 'Als behoeftesteller kun je alleen verhogen; de ondergrens komt uit de AVG '
                    . 'en uit het belang van het proces. De beoordelaar mag ook naar beneden '
                    . 'bijstellen, mits gemotiveerd.',
                'opties' => qs_bijstelling_opties(),
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
                'label' => 'Wil je die eis bijstellen?',
                'hint' => 'Als behoeftesteller kun je alleen verhogen. De beoordelaar mag ook naar '
                    . 'beneden bijstellen, mits gemotiveerd.',
                'opties' => qs_bijstelling_opties(),
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
                'label' => 'Hoeveel werk mag er verloren gaan bij een calamiteit?',
                'hint' => 'Dit bepaalt hoe vaak er een reservekopie gemaakt moet worden. We stellen alvast '
                    . 'iets voor dat past bij de beschikbaarheidseis.',
                'verplicht' => true,
                'voorstel' => true,
                'opties' => [
                    'geen' => ['label' => 'Niets (er mag geen enkele transactie verloren gaan)'],
                    '1-uur' => ['label' => 'Maximaal 1 uur'],
                    '4-uur' => ['label' => 'Maximaal 4 uur'],
                    '24-uur' => ['label' => 'Maximaal 24 uur'],
                    '1-week' => ['label' => 'Maximaal een week'],
                    'nvt' => ['label' => 'Niet van toepassing (er is geen data om te herstellen)'],
                ],
            ],
            [
                'key' => 'rpo_motivatie',
                'type' => 'textarea',
                'label' => 'Waarom dit terugvalpunt?',
                'verplicht' => true,
                'motivatie_voor' => 'rpo',
                'hoogte' => 3,
            ],
        ],
    ];
}

function qs_sectie_cloud(): array
{
    return [
        'id'    => 'cloud',
        'titel' => 'Staat het in de cloud?',
        'stap' => 2,
        'intro' => 'Het type informatie bepaalt welke clouddienstverlening is toegestaan. Of er sprake '
            . 'is van materieel cloudgebruik rekenen we zelf uit.',
        'toon_als' => qs_volledige_scan(),
        'vragen' => [
            [
                'key' => 'cloud',
                'type' => 'radio',
                'label' => 'Wordt er gebruikgemaakt van clouddienstverlening?',
                'verplicht' => true,
                'opties' => [
                    'nee' => ['label' => 'Nee, alles draait op eigen infrastructuur'],
                    'mogelijk' => ['label' => 'Mogelijk, we sluiten het niet uit'],
                    'ja' => ['label' => 'Ja'],
                ],
            ],
            [
                'key' => 'cloud_type',
                'type' => 'radio',
                'label' => 'Wat voor cloud is dat?',
                'verplicht' => true,
                'toon_als' => ['cloud' => ['ja', 'mogelijk']],
                'aanname' => 'publiek',
                'opties' => [
                    'publiek' => ['label' => 'Publieke cloud', 'hint' => 'Gedeeld met andere klanten.'],
                    'community' => ['label' => 'Community cloud', 'hint' => 'Bijvoorbeeld een Rijkscloud.'],
                    'prive' => ['label' => 'Private cloud', 'hint' => 'Exclusief voor ons.'],
                    'hybride' => ['label' => 'Hybride'],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'cloud_locatie',
                'type' => 'radio',
                'label' => 'Waar staat de data?',
                'hint' => 'Inclusief reservekopieën en ondersteuning vanuit het buitenland.',
                'verplicht' => true,
                'toon_als' => ['cloud' => ['ja', 'mogelijk']],
                'aanname' => 'buiten-eer',
                'opties' => [
                    'eer' => ['label' => 'Binnen de EER'],
                    'adequaatheid' => ['label' => 'Buiten de EER, in een land met adequaatheidsbesluit'],
                    'modelcontract' => ['label' => 'Buiten de EER, op basis van een modelcontract (SCC)'],
                    'buiten-eer' => [
                        'label' => 'Buiten de EER, zonder adequaatheidsbesluit of modelcontract',
                    ],
                    'onbekend' => ['label' => 'Weet ik niet'],
                ],
            ],
            [
                'key' => 'cloud_leverancier',
                'type' => 'text',
                'optioneel' => true,
                'label' => 'Welke clouddienstverlener is het?',
                'toon_als' => ['cloud' => ['ja', 'mogelijk']],
            ],
        ],
    ];
}

function qs_sectie_dreiging(): array
{
    return [
        'id'    => 'dreiging',
        'titel' => 'Voor wie is dit interessant?',
        'stap' => 2,
        'intro' => 'We vragen niet of inlichtingendiensten of criminelen een dreiging vormen, want dat is '
            . 'nauwelijks in te schatten. In plaats daarvan: wat maakt dit systeem een aantrekkelijk '
            . 'doelwit? Daaruit volgt het dreigingsprofiel.',
        'toon_als' => qs_volledige_scan(),
        'vragen' => [
            [
                'key' => 'dreiging_kenmerken',
                'type' => 'checkbox',
                'label' => 'Zit een van deze dingen in of achter dit systeem?',
                'hint' => 'Niets aanvinken betekent: geen van deze.',
                'opties' => [
                    'besluitvorming' => [
                        'label' => 'Nog niet openbare politieke besluitvorming, standpuntbepaling '
                            . 'of woordvoeringslijnen',
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
                        'label' => 'Identificerende gegevens van grote aantallen burgers, zoals BSN '
                            . 'of kopieën van identiteitsdocumenten',
                    ],
                ],
            ],
            [
                'key' => 'afgeleid_dreiging',
                'type' => 'afgeleid',
                'bron' => 'dreiging',
                'label' => 'Dreigingsprofiel',
                'hint' => 'Een hoog dreigingsprofiel betekent: bescherming nodig tegen '
                    . 'inlichtingendiensten, terreurgroepen of georganiseerde criminaliteit. Dat '
                    . 'sluit publieke cloud uit en vraagt om een Volledige Risicoanalyse.',
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
                'hint' => 'De tool vult zijn eigen redenering alvast in. Vul aan of vervang als je '
                    . 'meer weet over wie hier belangstelling voor zou hebben.',
                'verplicht' => true,
                'motivatie_voor' => 'dreiging',
                'hoogte' => 3,
            ],
            [
                'key' => 'aanvullende_wetgeving',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Gelden er nog andere wetten of regels?',
                'hint' => 'De standaardkaders vult de tool zelf aan. Denk aan sectorale wetgeving, '
                    . 'NIS2, of interne kaders van de organisatie of de systeemeigenaar.',
                'hoogte' => 3,
            ],
            [
                'key' => 'externe_eisen',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Zijn er externe eisen van bijvoorbeeld een ketenpartner?',
                'hoogte' => 3,
            ],
            [
                'key' => 'cis_normen',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Gelden er specifieke CIS Benchmarks of STIGs?',
                'hint' => 'Hardeningsnormen voor besturingssystemen, databases of netwerkapparatuur.',
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
