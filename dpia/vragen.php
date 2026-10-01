<?php

/**
 * De vragenlijst.
 *
 * Dit is het bestand om aan te passen als je vragen wilt toevoegen,
 * herformuleren of weghalen. Formulier, controle en document lezen allemaal
 * uit deze definitie. De sleutels per vraag staan in ../README.md.
 *
 * Staat een vraag ook in de quickscan, dan is de sleutel gelijk. Zo vult een
 * bestand van de quickscan de DPIA vooraf in.
 *
 * Bij een risico is het tekstvak {risico}_extra ook een maatregel die er komt.
 * De maatregelen zijn daarom alleen verplicht zolang dat tekstvak leeg is.
 */

declare(strict_types=1);

/** @return list<array<string,mixed>> */
function dp_secties(): array
{
    return [
        dp_sectie_over(),
        dp_sectie_verwerking(),
        dp_sectie_betrokkenen(),
        dp_sectie_gegevens(),
        dp_sectie_handelingen(),
        dp_sectie_partijen(),
        dp_sectie_techniek(),
        dp_sectie_bewaren(),
        dp_sectie_rechtsgrond(),
        dp_sectie_noodzaak(),
        dp_sectie_rechten(),
        dp_sectie_risicos(),
        dp_sectie_conclusie(),
    ];
}

/** Stap 2 verschijnt alleen als de privacyfunctionaris meedoet. */
function dp_samen(): array
{
    return ['rol' => ['samen']];
}

/** @return array<string,array<string,string>> */
function dp_niveau_opties(string $leeg): array
{
    $opties = ['' => ['label' => $leeg]];
    foreach (CM_NIVEAUS as $niveau => $label) {
        $opties[$niveau] = ['label' => $label];
    }

    return $opties;
}

/** @return array<string,array<string,string>> */
function dp_locatie_opties(): array
{
    return [
        'eer' => ['label' => 'Binnen de EER', 'hint' => 'De EER is de EU, met Noorwegen, IJsland en Liechtenstein.'],
        'adequaatheid' => [
            'label' => 'Buiten de EER, in een land dat de EU veilig vindt',
            'hint' => 'De EU heeft dan besloten dat dat land persoonsgegevens goed beschermt. Dat heet een '
                . 'adequaatheidsbesluit.',
        ],
        'modelcontract' => [
            'label' => 'Buiten de EER, met een standaardcontract van de EU',
            'hint' => 'Zo’n contract heet ook een modelcontract.',
        ],
        'buiten-eer' => ['label' => 'Buiten de EER, zonder besluit van de EU en zonder standaardcontract'],
    ];
}

// ---------------------------------------------------------------------------
// Stap 1: de beschrijving

function dp_sectie_over(): array
{
    return [
        'id' => 'over',
        'titel' => 'Over deze DPIA',
        'stap' => 1,
        'intro' => 'Eerst beschrijf je de verwerking. De rechtsgrond en de risico’s beoordeel je '
            . 'daarna samen met de privacyfunctionaris. Dat mag in één keer, of in twee keer.',
        'vragen' => [
            [
                'key' => 'rol',
                'type' => 'radio',
                'label' => 'Wat ga je nu invullen?',
                'verplicht' => true,
                'opties' => [
                    'opsteller' => [
                        'label' => 'Alleen de beschrijving van de verwerking',
                        'hint' => 'Hoofdstuk 1. De rest vul je later in, samen met de privacyfunctionaris.',
                    ],
                    'samen' => [
                        'label' => 'Alles, samen met de privacyfunctionaris',
                        'hint' => 'Ook de rechtsgrond, de risico’s en de maatregelen.',
                    ],
                ],
            ],
            [
                'key' => 'projectnaam',
                'type' => 'text',
                'label' => 'Hoe heet de verwerking?',
                'hint' => 'Deze naam komt overal in het document terug, en in de bestandsnaam.',
                'verplicht' => true,
            ],
            [
                'key' => 'aanvrager',
                'type' => 'text',
                'label' => 'Wie stelt deze DPIA op?',
                'hint' => 'Naam en functie.',
                'verplicht' => true,
            ],
            [
                'key' => 'organisatieonderdeel',
                'type' => 'text',
                'label' => 'Welke afdeling is verantwoordelijk voor de verwerking?',
                'verplicht' => true,
            ],
            [
                'key' => 'aanleiding',
                'type' => 'radio',
                'label' => 'Waarom wordt deze DPIA gemaakt?',
                'verplicht' => true,
                'opties' => [
                    'quickscan' => ['label' => 'Uit de quickscan bleek dat een DPIA nodig is'],
                    'advies' => ['label' => 'De privacyfunctionaris of de FG heeft een DPIA aangeraden'],
                    'wijziging' => ['label' => 'Een bestaande verwerking verandert flink'],
                    'herziening' => [
                        'label' => 'De vorige DPIA moet worden herzien',
                        'hint' => 'Uiterlijk vier jaar na de vaststelling.',
                    ],
                    'anders' => ['label' => 'Een andere reden'],
                ],
            ],
            [
                'key' => 'aanleiding_toelichting',
                'type' => 'textarea',
                'label' => 'Wat is de aanleiding precies?',
                'hint' => 'Noem ook waar het stuk staat dat de aanleiding vormt, bijvoorbeeld met een '
                    . 'kenmerk uit het documentsysteem.',
                'verplicht_als' => ['aanleiding' => ['anders']],
                'hoogte' => 2,
            ],
            [
                'key' => 'email',
                'type' => 'email',
                'optioneel' => true,
                'label' => 'Op welk mailadres ben je bereikbaar?',
            ],
            [
                'key' => 'versie',
                'type' => 'text',
                'optioneel' => true,
                'label' => 'Welk versienummer krijgt dit document?',
                'standaard' => '0.1',
            ],
            [
                'key' => 'versie_toelichting',
                'type' => 'text',
                'optioneel' => true,
                'label' => 'Wat is er veranderd in deze versie?',
                'standaard' => 'Eerste opzet',
            ],
        ],
    ];
}

function dp_sectie_verwerking(): array
{
    return [
        'id' => 'verwerking',
        'titel' => 'Wat gebeurt er, en waarvoor?',
        'stap' => 1,
        'intro' => 'Beschrijf de verwerking zo dat iemand zonder technische kennis het begrijpt. De '
            . 'functionaris gegevensbescherming moet het kunnen volgen zonder dat jij erbij bent.',
        'vragen' => [
            [
                'key' => 'proces_omschrijving',
                'type' => 'textarea',
                'label' => 'Welk proces ondersteunt de verwerking?',
                'hint' => 'Persoonsgegevens worden bijna altijd verwerkt voor een proces. Beschrijf dat '
                    . 'proces kort, in gewone woorden.',
                'verplicht' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'systeem_omschrijving',
                'type' => 'textarea',
                'label' => 'Met welk systeem, welke dienst of welke leverancier gebeurt het?',
                'hint' => 'Naam van het product of de dienst, en wie het levert.',
                'verplicht' => true,
                'hoogte' => 2,
            ],
            [
                'key' => 'verwerking_omschrijving',
                'type' => 'textarea',
                'label' => 'Wat gebeurt er met de gegevens, van begin tot eind?',
                'hint' => 'Wie doet wat, wanneer en waarmee?',
                'verplicht' => true,
                'hoogte' => 5,
            ],
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
                'key' => 'belang',
                'type' => 'radio',
                'label' => 'Wat gebeurt er als deze verwerking niet doorgaat?',
                'verplicht' => true,
                'opties' => [
                    'kritisch' => ['label' => 'Het proces valt stil', 'hint' => 'Bijvoorbeeld bij een wettelijke taak.'],
                    'belangrijk' => ['label' => 'Het proces gaat door, maar kost veel meer moeite'],
                    'handig' => ['label' => 'Er verandert weinig, want het is handig maar niet nodig'],
                ],
            ],
            [
                'key' => 'belang_toelichting',
                'type' => 'textarea',
                'label' => 'Wat gaat er dan precies mis?',
                'verplicht' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'scope_binnen',
                'type' => 'textarea',
                'label' => 'Wat valt binnen deze DPIA?',
                'hint' => 'De tool vult dit alvast in met het proces en het systeem. Maak de grens zo '
                    . 'scherp als je kunt.',
                'verplicht' => true,
                'motivatie_voor' => 'scope',
                'hoogte' => 3,
            ],
            [
                'key' => 'scope_buiten',
                'type' => 'textarea',
                'label' => 'Wat valt er bewust buiten?',
                'hint' => 'Bijvoorbeeld het netwerk, de werkplek of een koppeling met een eigen DPIA.',
                'verplicht' => true,
                'hoogte' => 3,
            ],
            [
                'key' => 'deelverwerkingen',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Bestaat de verwerking uit losse delen?',
                'hint' => 'Eén deel per regel. Zet achter een dubbele punt wat er gebeurt. Bijvoorbeeld: '
                    . 'Aanmelden: een medewerker meldt een bezoeker aan.',
                'hoogte' => 3,
            ],
        ],
    ];
}

function dp_sectie_betrokkenen(): array
{
    return [
        'id' => 'betrokkenen',
        'titel' => 'Over wie gaat het?',
        'stap' => 1,
        'intro' => 'Betrokkenen zijn de mensen van wie gegevens worden verwerkt. Kruis elke groep aan '
            . 'die in de verwerking voorkomt.',
        'vragen' => [
            [
                'key' => 'betrokkenen',
                'type' => 'checkbox',
                'label' => 'Van welke groepen worden gegevens verwerkt?',
                'verplicht' => true,
                'opties' => dp_groepsopties(),
            ],
            [
                'key' => 'betrokkenen_anders',
                'type' => 'textarea',
                'label' => 'Welke andere groepen zijn dat?',
                'hint' => 'Eén groep per regel.',
                'verplicht' => true,
                'toon_als' => ['betrokkenen' => ['groep_anders']],
                'hoogte' => 2,
            ],
            [
                'key' => 'pg_aantal_betrokkenen',
                'type' => 'radio',
                'label' => 'Over hoeveel mensen gaat het?',
                'hint' => 'Het totaal over de hele looptijd. Een schatting is goed genoeg.',
                'verplicht' => true,
                'opties' => [
                    'tot100' => ['label' => 'Minder dan 100'],
                    'tot1000' => ['label' => '100 tot 1.000'],
                    'tot100k' => ['label' => '1.000 tot 100.000'],
                    'meer' => ['label' => 'Meer dan 100.000'],
                ],
            ],
        ],
    ];
}

/**
 * De groepen betrokkenen. De omschrijving komt in de tabel van het document.
 *
 * @return array<string,array<string,string>>
 */
function dp_groepsopties(): array
{
    return [
        'medewerkers' => [
            'label' => 'Eigen medewerkers',
            'omschrijving' => 'Medewerkers in dienst van de organisatie die met de verwerking te maken hebben.',
        ],
        'externen' => [
            'label' => 'Externe medewerkers',
            'hint' => 'Ingehuurde krachten, stagiairs en gedetacheerden.',
            'omschrijving' => 'Ingehuurde krachten, stagiairs en gedetacheerden die voor de organisatie werken.',
        ],
        'sollicitanten' => [
            'label' => 'Sollicitanten',
            'omschrijving' => 'Mensen die solliciteren op een functie bij de organisatie.',
        ],
        'bezoekers' => [
            'label' => 'Bezoekers',
            'omschrijving' => 'Mensen die voor een bezoek toegang krijgen tot een gebouw of een ruimte van '
                . 'de organisatie.',
        ],
        'burgers' => [
            'label' => 'Burgers',
            'omschrijving' => 'Burgers die een dienst gebruiken of op een andere manier met de organisatie te '
                . 'maken hebben.',
        ],
        'contactpersonen' => [
            'label' => 'Contactpersonen van andere organisaties',
            'hint' => 'Bijvoorbeeld van leveranciers of ketenpartners.',
            'omschrijving' => 'Medewerkers van leveranciers, ketenpartners of andere organisaties waar de '
                . 'organisatie contact mee heeft.',
        ],
        'beheerders' => [
            'label' => 'Beheerders van het systeem',
            'omschrijving' => 'Medewerkers of leveranciers die het systeem beheren en daarbij bij de '
                . 'gegevens kunnen.',
        ],
        'kinderen' => [
            'label' => 'Kinderen jonger dan 16 jaar',
            'omschrijving' => 'Kinderen jonger dan 16 jaar. Zij krijgen extra bescherming.',
        ],
        'kwetsbaar' => [
            'label' => 'Mensen in een kwetsbare positie',
            'hint' => 'Bijvoorbeeld patiënten of mensen met schulden.',
            'omschrijving' => 'Mensen in een kwetsbare positie. Zij krijgen extra bescherming.',
        ],
        'groep_anders' => ['label' => 'Een andere groep'],
    ];
}

function dp_sectie_gegevens(): array
{
    $vragen = [
        [
            'key' => 'informatie_soorten',
            'type' => 'checkbox',
            'label' => 'Welke persoonsgegevens worden verwerkt?',
            'hint' => 'Of een gegeven gewoon of bijzonder is, hoef je niet te weten. De tool leest dat af '
                . 'uit de groep.',
            'verplicht' => true,
            'groepen' => dp_informatiegroepen(),
            'opties' => dp_informatiesoorten(),
        ],
        [
            'key' => 'pg_anders_welke',
            'type' => 'textarea',
            'label' => 'Welke andere persoonsgegevens zijn dat?',
            'hint' => 'Eén gegeven per regel.',
            'verplicht' => true,
            'toon_als' => ['informatie_soorten' => ['pg_anders']],
            'hoogte' => 2,
        ],
        [
            'key' => 'afgeleid_persoonsgegevens',
            'type' => 'afgeleid',
            'bron' => 'pg',
            'label' => 'Wat betekent dit?',
            'hint' => 'Volgt uit wat je hierboven hebt aangekruist.',
        ],
        [
            'key' => 'pg_deels',
            'type' => 'checkbox',
            'label' => 'Welke gegevens heb je maar van een deel van de groepen?',
            'hint' => 'Niets aanvinken betekent: je hebt van elke groep alle gegevens.',
            'toon_als_uitkomst' => ['groepen' => ['meerdere']],
            'opties_uit' => 'informatie_soorten',
            'opties' => array_map(static fn (array $optie): array => ['label' => $optie['label']], dp_informatiesoorten()),
        ],
    ];

    // Alleen voor de gegevens die niet over elke groep gaan: van welke groepen dan wel.
    $groepen = array_map(static fn (array $optie): array => ['label' => $optie['label']], dp_groepsopties());

    foreach (dp_informatiesoorten() as $soort => $optie) {
        $vragen[] = [
            'key' => 'pg_groepen_' . $soort,
            'type' => 'checkbox',
            'label' => $optie['label'] . ': van welke groepen?',
            'hint' => 'Alle groepen staan al aangevinkt. Haal het vinkje weg bij de groepen waar het niet over gaat.',
            'verplicht' => true,
            'toon_als' => ['pg_deels' => [$soort]],
            'opties_uit' => 'betrokkenen',
            'voorstel' => true,
            'opties' => $groepen,
        ];
    }

    $vragen[] = [
        'key' => 'bronnen_kop',
        'type' => 'kop',
        'label' => 'Waar komen de gegevens vandaan?',
        'hint' => 'De tool kiest per gegeven alvast de bron die het meest voor de hand ligt. Klopt dat '
            . 'niet, kies dan iets anders.',
    ];

    foreach (dp_informatiesoorten() as $soort => $optie) {
        $vragen[] = [
            'key' => 'bron_' . $soort,
            'type' => 'select',
            'label' => $optie['label'],
            'verplicht' => true,
            'toon_als' => ['informatie_soorten' => [$soort]],
            'voorstel' => true,
            'opties' => [
                '' => ['label' => 'Kies een bron'],
                'betrokkene' => ['label' => 'De betrokkene zelf'],
                'aanmelder' => ['label' => 'Iemand die de betrokkene aanmeldt'],
                'eigen' => ['label' => 'Een eigen systeem of registratie'],
                'extern' => ['label' => 'Een andere organisatie'],
                'openbaar' => ['label' => 'Een openbare bron'],
                'systeem' => ['label' => 'Het systeem legt het zelf vast'],
            ],
        ];
    }

    return [
        'id' => 'gegevens',
        'titel' => 'Welke persoonsgegevens, en waar komen ze vandaan?',
        'stap' => 1,
        'intro' => 'Kruis aan wat er in de verwerking terecht kan komen. Ga uit van alles wat het '
            . 'systeem toelaat, niet alleen van wat de bedoeling is.',
        'vragen' => $vragen,
    ];
}

/**
 * De aankruislijst met persoonsgegevens, gelijk aan die van de quickscan.
 * De groep bepaalt of een gegeven gewoon of bijzonder is.
 *
 * @return array<string,array<string,string>>
 */
function dp_informatiesoorten(): array
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
    ];

    $opties = [];
    foreach ($groepen as $groep => $soorten) {
        foreach ($soorten as $sleutel => $label) {
            $opties[$sleutel] = ['label' => $label, 'groep' => $groep];
        }
    }

    return $opties;
}

/** @return array{gewoon:list<string>,identificerend:list<string>,bijzonder:list<string>} */
function dp_informatiegroepen(): array
{
    $perGroep = [];
    foreach (dp_informatiesoorten() as $sleutel => $optie) {
        $perGroep[$optie['groep']][] = $sleutel;
    }

    return [
        'gewoon' => $perGroep['Gewone persoonsgegevens'],
        'identificerend' => $perGroep['Identificerende gegevens'],
        'bijzonder' => $perGroep['Bijzondere persoonsgegevens'],
    ];
}

function dp_sectie_handelingen(): array
{
    $vormen = [
        'Vastleggen' => [
            'vastleggen' => 'Gegevens vastleggen bij een aanmelding of registratie',
            'wijzigen' => 'Gegevens wijzigen, ook na een verzoek van de betrokkene',
            'loggen' => 'Het systeem legt zelf gegevens vast, zoals in logbestanden',
        ],
        'Raadplegen' => [
            'inzien' => 'Gegevens inzien',
            'controleren' => 'Gegevens controleren, bijvoorbeeld bij de ingang van een gebouw',
            'opvragen' => 'Gegevens opvragen bij een andere bron',
            'rapporteren' => 'Overzichten of rapportages maken',
        ],
        'Overdragen' => [
            'ontvangen' => 'Gegevens ontvangen van een andere organisatie',
            'verstrekken' => 'Gegevens verstrekken aan een andere organisatie',
        ],
        'Overige handelingen' => [
            'koppelen' => 'Gegevens koppelen aan andere bestanden',
            'publiceren' => 'Gegevens openbaar maken',
        ],
    ];

    $opties = [];
    foreach ($vormen as $groep => $lijst) {
        foreach ($lijst as $sleutel => $label) {
            $opties[$sleutel] = ['label' => $label, 'groep' => $groep];
        }
    }

    return [
        'id' => 'handelingen',
        'titel' => 'Wat wordt er met de gegevens gedaan?',
        'stap' => 1,
        'intro' => 'Kruis aan wat er met de gegevens gebeurt. Bewaren en vernietigen komen later, bij de '
            . 'bewaartermijnen.',
        'vragen' => [
            [
                'key' => 'verwerking_vormen',
                'type' => 'checkbox',
                'label' => 'Wat gebeurt er met de gegevens?',
                'verplicht' => true,
                'opties' => $opties,
            ],
            [
                'key' => 'verstrekken_aan',
                'type' => 'textarea',
                'label' => 'Aan wie worden gegevens verstrekt, en waarom?',
                'verplicht' => true,
                'toon_als' => ['verwerking_vormen' => ['verstrekken']],
                'hoogte' => 2,
            ],
            [
                'key' => 'verwerking_vormen_anders',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Gebeurt er nog iets anders met de gegevens?',
                'hint' => 'Eén handeling per regel.',
                'hoogte' => 2,
            ],
        ],
    ];
}

function dp_sectie_partijen(): array
{
    return [
        'id' => 'partijen',
        'titel' => 'Wie doen er mee, en hoe lopen de gegevens?',
        'stap' => 1,
        'intro' => 'Elke partij heeft een rol. De verwerkingsverantwoordelijke bepaalt waarom en hoe de '
            . 'gegevens worden verwerkt. Een verwerker doet dat in opdracht, zoals de leverancier van een '
            . 'clouddienst. Een verstrekker levert gegevens aan, en een ontvanger krijgt ze.',
        'vragen' => [
            [
                'key' => 'partijen',
                'type' => 'rijen',
                'label' => 'Welke partijen doen mee?',
                'hint' => 'Eén rij per partij. Heeft een partij twee rollen, gebruik dan twee rijen. De '
                    . 'betrokkenen zelf horen hier niet bij.',
                'verplicht' => true,
                'rij_label' => 'Partij',
                'erbij' => 'Nog een partij',
                'kolommen' => [
                    'organisatie' => ['type' => 'text', 'label' => 'Organisatie of afdeling', 'verplicht' => true],
                    'rol' => [
                        'type' => 'select',
                        'label' => 'Rol',
                        'verplicht' => true,
                        'opties' => [
                            '' => ['label' => 'Kies een rol'],
                            'verantwoordelijke' => ['label' => 'Verwerkingsverantwoordelijke'],
                            'gezamenlijk' => ['label' => 'Gezamenlijk verantwoordelijke'],
                            'verwerker' => ['label' => 'Verwerker'],
                            'subverwerker' => ['label' => 'Subverwerker'],
                            'verstrekker' => ['label' => 'Verstrekker'],
                            'ontvanger' => ['label' => 'Ontvanger'],
                        ],
                    ],
                    'toegang' => ['type' => 'text', 'label' => 'Welke functies kunnen bij de gegevens?'],
                ],
            ],
            [
                'key' => 'verwerkersovereenkomst',
                'type' => 'radio',
                'label' => 'Is er met elke verwerker een verwerkersovereenkomst?',
                'verplicht' => true,
                'toon_als_uitkomst' => ['verwerker' => ['ja']],
                'opties' => [
                    'getekend' => ['label' => 'Ja, die is getekend'],
                    'onderhandeling' => ['label' => 'Daar wordt nog over onderhandeld'],
                    'nee' => ['label' => 'Nee, nog niet'],
                ],
            ],
            [
                'key' => 'pg_subverwerkers',
                'type' => 'radio',
                'label' => 'Schakelt een verwerker zelf weer andere partijen in?',
                'hint' => 'Subverwerkers, bijvoorbeeld voor hosting of ondersteuning.',
                'verplicht' => true,
                'toon_als_uitkomst' => ['verwerker' => ['ja']],
                'opties' => ['nee' => ['label' => 'Nee'], 'ja' => ['label' => 'Ja']],
            ],
            [
                'key' => 'pg_subverwerkers_locatie',
                'type' => 'radio',
                'label' => 'Waar zitten die partijen?',
                'verplicht' => true,
                'toon_als' => ['pg_subverwerkers' => ['ja']],
                'opties' => dp_locatie_opties(),
            ],
            [
                'key' => 'stromen',
                'type' => 'rijen',
                'label' => 'Hoe lopen de gegevens?',
                'hint' => 'Het model vraagt om een ketenplaat: een overzicht van alle stromen van gegevens. '
                    . 'Zet elke stroom op een eigen rij, dan tekent de tool er een schema van. Bij Van en '
                    . 'Naar kun je kiezen uit de partijen en de groepen betrokkenen.',
                'verplicht' => true,
                'rij_label' => 'Stroom',
                'erbij' => 'Nog een stroom',
                'suggesties' => 'dp_suggesties',
                'kolommen' => [
                    'van' => ['type' => 'text', 'label' => 'Van', 'verplicht' => true, 'suggesties' => true],
                    'naar' => ['type' => 'text', 'label' => 'Naar', 'verplicht' => true, 'suggesties' => true],
                    'gegevens' => ['type' => 'text', 'label' => 'Welke gegevens?'],
                    'scope' => [
                        'type' => 'select',
                        'label' => 'Valt het binnen deze DPIA?',
                        'opties' => ['binnen' => ['label' => 'Ja'], 'buiten' => ['label' => 'Nee']],
                    ],
                ],
            ],
        ],
    ];
}

function dp_sectie_techniek(): array
{
    return [
        'id' => 'techniek',
        'titel' => 'Waar, hoe en onder welke regels?',
        'stap' => 1,
        'intro' => 'De plek bepaalt welke regels gelden als gegevens de EER verlaten. De techniek bepaalt '
            . 'of er extra risico’s zijn. Onderaan zet de tool de wetten en kaders op een rij.',
        'vragen' => [
            [
                'key' => 'cloud',
                'type' => 'radio',
                'label' => 'Waar worden de gegevens verwerkt en opgeslagen?',
                'verplicht' => true,
                'opties' => [
                    'nee' => ['label' => 'Alleen op de eigen infrastructuur van de organisatie'],
                    'ja' => ['label' => 'Bij een clouddienst'],
                    'mogelijk' => [
                        'label' => 'Dat staat nog niet vast',
                        'hint' => 'De tool gaat dan uit van een clouddienst.',
                    ],
                ],
            ],
            [
                'key' => 'cloud_type',
                'type' => 'radio',
                'label' => 'Wat voor cloud is dat?',
                'verplicht' => true,
                'toon_als' => ['cloud' => ['ja', 'mogelijk']],
                'opties' => [
                    'publiek' => ['label' => 'Publieke cloud', 'hint' => 'Gedeeld met andere klanten.'],
                    'community' => ['label' => 'Community cloud', 'hint' => 'Bijvoorbeeld een cloud voor de overheid.'],
                    'prive' => ['label' => 'Private cloud', 'hint' => 'Alleen voor de eigen organisatie.'],
                    'hybride' => ['label' => 'Hybride'],
                ],
            ],
            [
                'key' => 'cloud_locatie',
                'type' => 'radio',
                'label' => 'Waar staan de gegevens?',
                'hint' => 'Tel ook de reservekopieën mee, en de hulp op afstand vanuit het buitenland.',
                'verplicht' => true,
                'toon_als' => ['cloud' => ['ja', 'mogelijk']],
                'opties' => dp_locatie_opties(),
            ],
            [
                'key' => 'technieken',
                'type' => 'checkbox',
                'label' => 'Wordt een van deze technieken gebruikt?',
                'hint' => 'Niets aankruisen betekent: geen van deze.',
                'opties' => [
                    'besluitvorming' => [
                        'label' => 'Het systeem neemt besluiten over mensen, of selecteert mensen voor een besluit',
                        'hint' => 'Ook als een mens daarna beslist, telt automatisch selecteren al mee.',
                    ],
                    'profilering' => [
                        'label' => 'Het systeem maakt profielen van mensen',
                        'hint' => 'Het voorspelt of beoordeelt gedrag of kenmerken.',
                    ],
                    'bigdata' => ['label' => 'Grote hoeveelheden gegevens worden gecombineerd om patronen te vinden'],
                    'nieuw' => ['label' => 'Een techniek die nieuw is voor de organisatie'],
                    'volgen' => ['label' => 'Camera’s, biometrie of het volgen van locaties'],
                ],
            ],
            [
                'key' => 'technieken_toelichting',
                'type' => 'textarea',
                'label' => 'Hoe werkt dat, en blijft er een mens bij betrokken?',
                'verplicht' => true,
                'toon_als' => ['technieken' => ['besluitvorming', 'profilering', 'bigdata', 'nieuw', 'volgen']],
                'hoogte' => 3,
            ],
            [
                'key' => 'ai',
                'type' => 'radio',
                'label' => 'Wordt er AI gebruikt?',
                'verplicht' => true,
                'opties' => [
                    'uitgesloten' => ['label' => 'Nee, AI is uitgesloten'],
                    'mogelijk' => ['label' => 'We sluiten het niet uit'],
                    'onderdeel' => ['label' => 'Ja, AI is onderdeel van de verwerking'],
                ],
            ],
            [
                'key' => 'ai_omschrijving',
                'type' => 'textarea',
                'label' => 'Waarvoor wordt de AI gebruikt, en hoe werkt het?',
                'verplicht' => true,
                'toon_als' => ['ai' => ['mogelijk', 'onderdeel']],
                'hoogte' => 3,
            ],
            [
                'key' => 'afgeleid_kaders',
                'type' => 'afgeleid',
                'bron' => 'kaders',
                'label' => 'Deze wetten en kaders gelden in ieder geval',
                'lijst' => dp_kaderlijst(),
            ],
            [
                'key' => 'cloud_leverancier',
                'type' => 'text',
                'optioneel' => true,
                'label' => 'Welke clouddienstverlener is het?',
                'toon_als' => ['cloud' => ['ja', 'mogelijk']],
            ],
            [
                'key' => 'documentatie',
                'type' => 'text',
                'optioneel' => true,
                'label' => 'Waar staat de documentatie van het systeem of de dienst?',
                'hint' => 'Een link of een kenmerk. Het document verwijst ernaar.',
            ],
            [
                'key' => 'aanvullende_wetgeving',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Geldt er ook wetgeving voor deze sector?',
                'hint' => 'Noem alleen wat echt bij deze verwerking hoort. Eén wet per regel.',
                'hoogte' => 3,
            ],
            [
                'key' => 'eigen_beleid',
                'type' => 'textarea',
                'optioneel' => true,
                'label' => 'Telt er ook eigen beleid van de organisatie mee?',
                'hint' => 'Bijvoorbeeld een privacybeleid of een eigen baseline. Eén stuk per regel.',
                'hoogte' => 3,
            ],
        ],
    ];
}

function dp_sectie_bewaren(): array
{
    return [
        'id' => 'bewaren',
        'titel' => 'Hoe lang worden de gegevens bewaard?',
        'stap' => 1,
        'intro' => 'Persoonsgegevens mogen niet langer worden bewaard dan nodig. Voor een overheidsorgaan '
            . 'geldt ook de Archiefwet 2026. Daarom hoort bij elke verwerking een termijn, en een plan voor '
            . 'wat er daarna gebeurt.',
        'vragen' => [
            [
                'key' => 'bewaartermijn',
                'type' => 'radio',
                'label' => 'Hoe lang worden de gegevens bewaard?',
                'verplicht' => true,
                'opties' => [
                    'selectielijst' => ['label' => 'Volgens de selectielijst'],
                    'wettelijk' => [
                        'label' => 'Een termijn uit andere wetgeving',
                        'hint' => 'Bijvoorbeeld een fiscale bewaartermijn.',
                    ],
                    'eigen' => [
                        'label' => 'Een termijn die de organisatie zelf heeft bepaald',
                        'hint' => 'Dat mag, als het doel die termijn onderbouwt.',
                    ],
                    'onbepaald' => ['label' => 'Dat is nog niet bepaald', 'hint' => 'Dat moet dan eerst geregeld worden.'],
                ],
            ],
            [
                'key' => 'bewaartermijn_toelichting',
                'type' => 'text',
                'label' => 'Welke categorie of termijn is dat?',
                'hint' => 'Bijvoorbeeld het nummer uit de selectielijst, of het aantal jaren.',
                'verplicht' => true,
                'toon_als' => ['bewaartermijn' => ['selectielijst', 'wettelijk', 'eigen']],
            ],
            [
                'key' => 'na_termijn',
                'type' => 'radio',
                'label' => 'Wat gebeurt er met de gegevens na de termijn?',
                'verplicht' => true,
                'opties' => [
                    'vernietigen' => ['label' => 'Ze worden vernietigd'],
                    'overbrengen' => [
                        'label' => 'Ze gaan naar een archiefdienst',
                        'hint' => 'Bijvoorbeeld het Nationaal Archief.',
                    ],
                    'beide' => ['label' => 'Een deel wordt vernietigd, en een deel gaat naar een archiefdienst'],
                ],
            ],
            [
                'key' => 'archief_vernietiging',
                'type' => 'radio',
                'label' => 'Kan het systeem de gegevens na de termijn echt vernietigen?',
                'verplicht' => true,
                'toon_als' => ['na_termijn' => ['vernietigen', 'beide']],
                'opties' => [
                    'automatisch' => ['label' => 'Ja, automatisch op basis van de termijn'],
                    'handmatig' => ['label' => 'Ja, maar alleen met de hand'],
                    'nee' => ['label' => 'Nee'],
                ],
            ],
        ],
    ];
}

// ---------------------------------------------------------------------------
// Stap 2: samen met de privacyfunctionaris

function dp_sectie_rechtsgrond(): array
{
    $gerechtvaardigd = ['pg_grondslag' => ['gerechtvaardigd_belang']];
    $balans = ['balans_voorlopig' => ['twijfel', 'betrokkenen']];
    $bijzonder = ['informatie_soorten' => dp_informatiegroepen()['bijzonder']];

    return [
        'id' => 'rechtsgrond',
        'titel' => 'Op welke grond mag het?',
        'stap' => 2,
        'toon_als' => dp_samen(),
        'intro' => 'Een verwerking mag alleen als er een grondslag uit artikel 6 van de AVG voor is. Voor '
            . 'bijzondere persoonsgegevens is ook een uitzondering nodig. Vul dit deel altijd samen met een '
            . 'privacyfunctionaris in.',
        'vragen' => [
            [
                'key' => 'pg_grondslag',
                'type' => 'radio',
                'label' => 'Op welke grondslag rust de verwerking?',
                'hint' => 'Bij de overheid is het bijna altijd een wettelijke verplichting of een taak van '
                    . 'algemeen belang.',
                'verplicht' => true,
                'opties' => [
                    'wettelijke_verplichting' => ['label' => 'Wettelijke verplichting'],
                    'algemeen_belang' => [
                        'label' => 'Taak van algemeen belang of openbaar gezag',
                        'hint' => 'De verwerking hoort bij de publieke taak.',
                    ],
                    'overeenkomst' => ['label' => 'Uitvoering van een overeenkomst'],
                    'toestemming' => [
                        'label' => 'Toestemming van de betrokkene',
                        'hint' => 'Mensen moeten dan vrij nee kunnen zeggen, en hun ja later weer kunnen '
                            . 'intrekken. Bij de overheid lukt dat vaak niet.',
                    ],
                    'vitaal_belang' => ['label' => 'Vitaal belang van een persoon'],
                    'gerechtvaardigd_belang' => [
                        'label' => 'Gerechtvaardigd belang',
                        'hint' => 'De overheid mag deze grond niet gebruiken voor haar publieke taken.',
                    ],
                ],
            ],
            [
                'key' => 'grondslag_wet',
                'type' => 'text',
                'label' => 'Op welke wet en welk artikel rust dat?',
                'hint' => 'Met de wet en het artikel erbij is de grondslag te controleren.',
                'verplicht' => true,
                'toon_als' => ['pg_grondslag' => ['wettelijke_verplichting', 'algemeen_belang']],
            ],
            [
                'key' => 'toestemming_wijze',
                'type' => 'textarea',
                'label' => 'Hoe wordt toestemming gevraagd, vastgelegd en weer ingetrokken?',
                'verplicht' => true,
                'toon_als' => ['pg_grondslag' => ['toestemming']],
                'hoogte' => 3,
            ],
            [
                'key' => 'bsn_wet',
                'type' => 'text',
                'label' => 'Welke wet staat het gebruik van het burgerservicenummer toe?',
                'hint' => 'Het BSN mag alleen worden gebruikt als een wet dat regelt.',
                'verplicht' => true,
                'toon_als' => ['informatie_soorten' => ['bsn']],
            ],
            [
                'key' => 'belangen_kop',
                'type' => 'kop',
                'label' => 'Belangenafweging',
                'hint' => 'Bij gerechtvaardigd belang weeg je het belang van de organisatie af tegen dat van '
                    . 'de betrokkenen.',
                'toon_als' => $gerechtvaardigd,
            ],
            [
                'key' => 'belang_verantwoordelijke',
                'type' => 'textarea',
                'label' => 'Welk belang heeft de organisatie bij de verwerking?',
                'verplicht' => true,
                'toon_als' => $gerechtvaardigd,
                'hoogte' => 3,
            ],
            [
                'key' => 'belang_betrokkene',
                'type' => 'textarea',
                'label' => 'Wat merken de betrokkenen van de verwerking?',
                'hint' => 'Welke gevolgen heeft het voor hun privacy, nu en later?',
                'verplicht' => true,
                'toon_als' => $gerechtvaardigd,
                'hoogte' => 3,
            ],
            [
                'key' => 'balans_voorlopig',
                'type' => 'radio',
                'label' => 'Hoe valt de afweging voorlopig uit?',
                'verplicht' => true,
                'toon_als' => $gerechtvaardigd,
                'opties' => [
                    'organisatie' => ['label' => 'Het belang van de organisatie weegt duidelijk zwaarder'],
                    'twijfel' => ['label' => 'Dat is niet duidelijk'],
                    'betrokkenen' => ['label' => 'Het belang van de betrokkenen weegt zwaarder'],
                ],
            ],
            [
                'key' => 'waarborgen',
                'type' => 'textarea',
                'label' => 'Welke extra waarborgen neemt de organisatie?',
                'hint' => 'Maatregelen die ongewenste gevolgen voor de betrokkenen voorkomen.',
                'verplicht' => true,
                'toon_als' => $balans,
                'hoogte' => 3,
            ],
            [
                'key' => 'balans_eind',
                'type' => 'radio',
                'label' => 'Hoe valt de afweging uit met die waarborgen?',
                'verplicht' => true,
                'toon_als' => $balans,
                'opties' => [
                    'organisatie' => ['label' => 'Het belang van de organisatie weegt nu zwaarder'],
                    'betrokkenen' => ['label' => 'Het belang van de betrokkenen blijft zwaarder wegen'],
                ],
            ],
            [
                'key' => 'bijzonder_kop',
                'type' => 'kop',
                'label' => 'Bijzondere persoonsgegevens',
                'hint' => 'Deze gegevens zijn extra beschermd. Ze verwerken is verboden, tenzij er een '
                    . 'uitzondering uit de AVG of de UAVG geldt.',
                'toon_als' => $bijzonder,
            ],
            [
                'key' => 'afgeleid_bijzonder',
                'type' => 'afgeleid',
                'bron' => 'bijzonder',
                'label' => 'Deze bijzondere persoonsgegevens worden verwerkt',
                'toon_als' => $bijzonder,
                'lijst' => array_map(
                    static fn (string $soort): array => [
                        'tekst' => dp_informatiesoorten()[$soort]['label'],
                        'toon_als' => ['informatie_soorten' => [$soort]],
                    ],
                    dp_informatiegroepen()['bijzonder']
                ),
            ],
            [
                'key' => 'uitzondering',
                'type' => 'radio',
                'label' => 'Welke uitzondering maakt het verwerken mogelijk?',
                'verplicht' => true,
                'toon_als' => $bijzonder,
                'opties' => [
                    'toestemming' => ['label' => 'Uitdrukkelijke toestemming van de betrokkene'],
                    'arbeid' => ['label' => 'Nodig voor plichten uit het arbeidsrecht of de sociale zekerheid'],
                    'vitaal' => ['label' => 'Nodig om iemands leven of gezondheid te beschermen'],
                    'openbaar' => ['label' => 'De betrokkene heeft de gegevens zelf openbaar gemaakt'],
                    'rechtsvordering' => ['label' => 'Nodig voor een rechtszaak'],
                    'algemeen_belang' => ['label' => 'Een zwaarwegend algemeen belang dat in een wet is geregeld'],
                    'zorg' => ['label' => 'Nodig voor gezondheidszorg of sociale zorg'],
                    'onderzoek' => ['label' => 'Nodig voor archief, wetenschappelijk onderzoek of statistiek'],
                    'uavg' => [
                        'label' => 'Een uitzondering uit de UAVG',
                        'hint' => 'Voor strafrechtelijke gegevens staan de uitzonderingen in de UAVG.',
                    ],
                ],
            ],
            [
                'key' => 'uitzondering_toelichting',
                'type' => 'textarea',
                'label' => 'Waarom geldt die uitzondering hier?',
                'verplicht' => true,
                'toon_als' => $bijzonder,
                'hoogte' => 3,
            ],
        ],
    ];
}

function dp_sectie_noodzaak(): array
{
    return [
        'id' => 'noodzaak',
        'titel' => 'Is het nodig en in verhouding?',
        'stap' => 2,
        'toon_als' => dp_samen(),
        'intro' => 'Hier gaat het om twee vragen. Is het doel belangrijk genoeg voor de inbreuk op de '
            . 'privacy? Dat heet proportionaliteit. En kan het ook met minder gegevens? Dat heet subsidiariteit. '
            . 'Waar het kan, schrijft de tool alvast een tekst. Pas die aan als dat nodig is.',
        'vragen' => [
            [
                'key' => 'pg_proportionaliteit',
                'type' => 'radio',
                'label' => 'Is het doel belangrijk genoeg voor deze inbreuk op de privacy?',
                'verplicht' => true,
                'opties' => [
                    'in_verhouding' => ['label' => 'Ja, het doel weegt zwaarder'],
                    'twijfel' => ['label' => 'Daar is twijfel over'],
                ],
            ],
            [
                'key' => 'proportionaliteit_toelichting',
                'type' => 'textarea',
                'label' => 'Waarom weegt het doel wel of niet zwaarder?',
                'verplicht' => true,
                'motivatie_voor' => 'proportionaliteit',
                'hoogte' => 4,
            ],
            [
                'key' => 'pg_subsidiariteit',
                'type' => 'radio',
                'label' => 'Kan het doel ook met minder of zonder persoonsgegevens?',
                'verplicht' => true,
                'opties' => [
                    'minst_ingrijpend' => ['label' => 'Nee, het kan niet met minder'],
                    'kan_minder' => [
                        'label' => 'Ja, het kan met minder gegevens, of anoniem',
                        'hint' => 'Leg dan uit waarom jullie daar niet voor kiezen.',
                    ],
                ],
            ],
            [
                'key' => 'subsidiariteit_toelichting',
                'type' => 'textarea',
                'label' => 'Waarom kan het wel of niet met minder?',
                'verplicht' => true,
                'motivatie_voor' => 'subsidiariteit',
                'hoogte' => 4,
            ],
            [
                'key' => 'doelbinding',
                'type' => 'radio',
                'label' => 'Worden de gegevens ook voor een ander doel gebruikt?',
                'verplicht' => true,
                'opties' => [
                    'nee' => ['label' => 'Nee, alleen voor de doelen van deze verwerking'],
                    'verenigbaar' => ['label' => 'Ja, voor een doel dat daarbij past'],
                    'anders' => [
                        'label' => 'Ja, voor een doel dat er los van staat',
                        'hint' => 'Daar is een eigen grondslag voor nodig.',
                    ],
                ],
            ],
            [
                'key' => 'doelbinding_toelichting',
                'type' => 'textarea',
                'label' => 'Voor welk doel, en waarom mag dat?',
                'verplicht' => true,
                'toon_als' => ['doelbinding' => ['verenigbaar', 'anders']],
                'hoogte' => 3,
            ],
        ],
    ];
}

function dp_sectie_rechten(): array
{
    return [
        'id' => 'rechten',
        'titel' => 'Kunnen betrokkenen hun rechten gebruiken?',
        'stap' => 2,
        'toon_als' => dp_samen(),
        'intro' => 'Betrokkenen hebben recht op informatie, inzage, correctie en verwijdering. Dat moet in '
            . 'de praktijk ook echt kunnen.',
        'vragen' => [
            [
                'key' => 'informeren',
                'type' => 'radio',
                'label' => 'Hoe horen betrokkenen dat hun gegevens worden gebruikt?',
                'verplicht' => true,
                'opties' => [
                    'beide' => ['label' => 'In de privacyverklaring, en ook op het moment dat de gegevens worden verzameld'],
                    'melding' => ['label' => 'Op het moment dat de gegevens worden verzameld'],
                    'verklaring' => ['label' => 'Alleen in de privacyverklaring'],
                    'nee' => ['label' => 'Dat is nog niet geregeld'],
                ],
            ],
            [
                'key' => 'pg_rechten',
                'type' => 'radio',
                'label' => 'Kan het systeem inzage, correctie en verwijdering aan?',
                'hint' => 'Het systeem moet de gegevens van één persoon kunnen tonen, verbeteren en wissen.',
                'verplicht' => true,
                'opties' => [
                    'volledig' => ['label' => 'Ja, volledig'],
                    'deels' => ['label' => 'Deels, of alleen met de hand'],
                    'nee' => ['label' => 'Nee'],
                ],
            ],
            [
                'key' => 'verzoeken_register',
                'type' => 'radio',
                'label' => 'Worden verzoeken van betrokkenen bijgehouden en bewaakt?',
                'verplicht' => true,
                'opties' => [
                    'ja' => ['label' => 'Ja, in een register, en iemand bewaakt de termijn'],
                    'nee' => ['label' => 'Nee'],
                ],
            ],
            [
                'key' => 'rechten_regeling',
                'type' => 'text',
                'optioneel' => true,
                'label' => 'Waar staat hoe iemand een verzoek indient?',
                'hint' => 'Bijvoorbeeld het adres van de privacyverklaring.',
            ],
        ],
    ];
}

function dp_sectie_risicos(): array
{
    $vragen = [];
    foreach (dp_risicos() as $risico) {
        array_push($vragen, ...dp_risicovragen($risico));
    }

    $categorieen = [];
    foreach (dp_risicocategorieen() as $letter => $omschrijving) {
        $categorieen[] = $letter . ': ' . $omschrijving;
    }

    return [
        'id' => 'risicos',
        'titel' => 'Wat zijn de risico’s?',
        'stap' => 2,
        'toon_als' => dp_samen(),
        'aanvullend' => 'Nog andere risico’s',
        'intro' => 'Elk risico staat op één regel, met het voorstel van de tool voor impact, kans en '
            . 'omvang. Vanaf Hoog klapt een risico open, want dan moet er minstens één maatregel bij komen. '
            . 'Kies dan welke maatregelen er al zijn, en welke er komen. Een dichte regel kun je altijd '
            . 'openen om het voorstel aan te passen.',
        'vragen' => [
            ...$vragen,
            [
                'key' => 'categorieen_kop',
                'type' => 'kop',
                'optioneel' => true,
                'label' => 'Categorieën van risico’s',
                'hint' => 'Elk risico valt in een of meer van deze categorieën. Gebruik de letters.',
                'lijst' => $categorieen,
            ],
            [
                'key' => 'eigen_risicos',
                'type' => 'rijen',
                'optioneel' => true,
                'label' => 'Welke risico’s komen erbij?',
                'rij_label' => 'Risico',
                'erbij' => 'Nog een risico',
                'kolommen' => [
                    'risico' => ['type' => 'textarea', 'label' => 'Wat kan er misgaan, en wat is het gevolg?', 'verplicht' => true],
                    'categorieen' => ['type' => 'text', 'label' => 'Categorieën'],
                    'maatregelen' => ['type' => 'textarea', 'label' => 'Welke maatregelen zijn er al?'],
                    'impact' => ['type' => 'select', 'label' => 'Impact', 'verplicht' => true, 'opties' => dp_niveau_opties('Kies de impact')],
                    'kans' => ['type' => 'select', 'label' => 'Kans', 'verplicht' => true, 'opties' => dp_niveau_opties('Kies de kans')],
                    'extra' => ['type' => 'textarea', 'label' => 'Welke maatregelen komen er?'],
                    'kans_na' => ['type' => 'select', 'label' => 'Kans met die maatregelen', 'opties' => dp_niveau_opties('Gelijk aan de kans')],
                ],
            ],
        ],
    ];
}

/**
 * De vragen bij één standaardrisico, als groep die één regel wordt. Het
 * voorwaardelijke risico geeft zijn voorwaarde door aan elke vraag.
 *
 * @param array<string,mixed> $risico
 * @return list<array<string,mixed>>
 */
function dp_risicovragen(array $risico): array
{
    $id = $risico['id'];
    $groep = [
        'groep' => $id,
        'toon_als' => $risico['toon_als'] ?? [],
        'toon_als_uitkomst' => $risico['toon_als_uitkomst'] ?? [],
    ];

    $vragen = [
        [
            'key' => $id . '_kop',
            'type' => 'kop',
            'label' => $risico['titel'],
            'hint' => $risico['tekst'],
            'stand' => 'dp_risico_stand',
            'aanvullend' => 'Andere maatregelen',
        ],
        [
            'key' => $id . '_maatregelen',
            'type' => 'maatregelen',
            'label' => 'Welke maatregelen zijn er?',
            'hint' => 'Wat al vaststaat uit eerdere antwoorden, is alvast gekozen.',
            'verplicht_als_uitkomst' => ['omvang:' . $id => ['h', 'zh'], 'extra:' . $id => ['nee']],
            'voorstel' => true,
            'opties' => array_map(static fn (string $label): array => ['label' => $label], $risico['maatregelen']),
        ],
        [
            'key' => $id . '_anders',
            'type' => 'textarea',
            'optioneel' => true,
            'label' => 'Welke andere maatregelen zijn er al?',
            'hint' => 'Eén maatregel per regel.',
            'hoogte' => 2,
        ],
        [
            'key' => $id . '_extra',
            'type' => 'textarea',
            'optioneel' => true,
            'label' => 'Welke andere maatregelen komen er?',
            'hint' => 'Eén maatregel per regel.',
            'hoogte' => 2,
        ],
        [
            'key' => $id . '_impact',
            'type' => 'select',
            'label' => 'Impact',
            'verplicht' => true,
            'voorstel' => true,
            'opties' => dp_niveau_opties('Kies de impact'),
        ],
        [
            'key' => $id . '_kans',
            'type' => 'select',
            'label' => 'Kans',
            'verplicht' => true,
            'voorstel' => true,
            'opties' => dp_niveau_opties('Kies de kans'),
        ],
        [
            'key' => $id . '_omvang',
            'type' => 'afgeleid',
            'bron' => $id,
            'label' => 'Omvang van het risico',
        ],
        [
            'key' => $id . '_kans_na',
            'type' => 'select',
            'label' => 'Hoe groot is de kans met de nieuwe maatregelen?',
            'hint' => 'Een maatregel verlaagt meestal de kans. De impact blijft gelijk.',
            'toon_als_uitkomst' => $groep['toon_als_uitkomst']
                + ['omvang:' . $id => ['m', 'h', 'zh'], 'extra:' . $id => ['ja']],
            'voorstel' => true,
            'opties' => dp_niveau_opties('Gelijk aan de kans'),
        ],
    ];

    return array_map(static fn (array $vraag): array => $vraag + $groep, $vragen);
}

function dp_sectie_conclusie(): array
{
    return [
        'id' => 'conclusie',
        'titel' => 'Conclusie',
        'stap' => 2,
        'toon_als' => dp_samen(),
        'intro' => 'De tool zet de uitkomst van de risico’s op een rij en schrijft een eerste conclusie. '
            . 'Pas die aan waar dat nodig is.',
        'vragen' => [
            [
                'key' => 'afgeleid_conclusie',
                'type' => 'afgeleid',
                'bron' => 'conclusie',
                'label' => 'Het grootste risico met de nieuwe maatregelen',
            ],
            [
                'key' => 'conclusie_toelichting',
                'type' => 'textarea',
                'label' => 'Conclusie',
                'hint' => 'Vul aan met wat de lezer vooral moet onthouden. Namen, datum en handtekening komen '
                    . 'op het document zelf.',
                'verplicht' => true,
                'motivatie_voor' => 'conclusie',
                'hoogte' => 5,
            ],
        ],
    ];
}
