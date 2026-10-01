<?php

/**
 * De vragenlijst van de soevereiniteitsscan: eerst wat je beoordeelt, dan de
 * acht thema's van het Cloud Sovereignty Framework, dan de uitkomst.
 *
 * Elke keuze in een thema heeft een 'niveau' van 0 tot 4: het SEAL niveau dat
 * dat antwoord waard is. Null telt niet mee. De invuller ziet die niveaus
 * niet; afleiding.php rekent ermee. 'kort' noemt de vraag in de uitkomst, en
 * 'tip' zegt bij een te laag antwoord hoe het beter kan. Wat de andere
 * sleutels doen, staat in ../README.md.
 */

declare(strict_types=1);

/**
 * De acht thema's: de doelstellingen van het framework, SOV 1 tot en met 8,
 * met hun gewicht in de score. 'eis' zegt hoe zwaar het thema telt voor de
 * vraag of de dienst past: de kern moet het volle niveau halen, de rest één
 * lager, en duurzaamheid houdt niets tegen.
 */
const SV_THEMAS = [
    'zeggenschap' => [
        'sov' => 1, 'naam' => 'Zeggenschap', 'eu' => 'Strategic Sovereignty', 'gewicht' => 15, 'eis' => 'rest', 'stap' => 1,
        'titel' => 'Wie is de baas?',
        'intro' => 'Dit thema gaat over de eigenaar van de leverancier. Wie de baas is, beslist wat er gebeurt als '
            . 'het spannend wordt.',
    ],
    'recht' => [
        'sov' => 2, 'naam' => 'Recht', 'eu' => 'Legal & Jurisdictional Sovereignty', 'gewicht' => 10, 'eis' => 'kern',
        'stap' => 1,
        'titel' => 'Welk recht geldt?',
        'intro' => 'Data in Europa valt nog niet vanzelf onder Europees recht. Het gaat erom welke regering de '
            . 'leverancier iets kan opleggen.',
    ],
    'data' => [
        'sov' => 3, 'naam' => 'Data', 'eu' => 'Data & AI Sovereignty', 'gewicht' => 10, 'eis' => 'kern', 'stap' => 2,
        'titel' => 'Wie kan bij je data?',
        'intro' => 'Hier gaat het om de sleutels, de plek van de data en wie er meekijkt. Ook AI telt mee.',
    ],
    'beheer' => [
        'sov' => 4, 'naam' => 'Beheer', 'eu' => 'Operational Sovereignty', 'gewicht' => 15, 'eis' => 'kern', 'stap' => 3,
        'titel' => 'Wie houdt het draaiende?',
        'intro' => 'Een clouddienst heeft elke dag mensen nodig. Waar zitten die mensen? En kunnen jullie verder '
            . 'zonder deze leverancier?',
    ],
    'keten' => [
        'sov' => 5, 'naam' => 'Keten', 'eu' => 'Supply Chain Sovereignty', 'gewicht' => 20, 'eis' => 'rest', 'stap' => 3,
        'titel' => 'Waar komen de onderdelen vandaan?',
        'intro' => 'Servers, chips en software komen vaak van ver. Dat hoeft geen probleem te zijn, als je maar weet '
            . 'waar ze vandaan komen. Dit thema weegt het zwaarst.',
    ],
    'techniek' => [
        'sov' => 6, 'naam' => 'Techniek', 'eu' => 'Technology Sovereignty', 'gewicht' => 15, 'eis' => 'rest', 'stap' => 3,
        'titel' => 'Hoe open is de techniek?',
        'intro' => 'Open techniek kun je controleren en meenemen naar een andere leverancier. Gesloten techniek maakt '
            . 'je afhankelijk van één bedrijf.',
    ],
    'beveiliging' => [
        'sov' => 7, 'naam' => 'Beveiliging', 'eu' => 'Security & Compliance Sovereignty', 'gewicht' => 10,
        'eis' => 'rest', 'stap' => 4,
        'titel' => 'Wie bewaakt de beveiliging?',
        'intro' => 'Een keurmerk laat zien dat de beveiliging goed is. Maar het gaat ook om wie er meekijkt, en of '
            . 'jullie zelf mogen controleren.',
    ],
    'duurzaam' => [
        'sov' => 8, 'naam' => 'Duurzaamheid', 'eu' => 'Environmental Sustainability', 'gewicht' => 5, 'eis' => 'geen',
        'stap' => 4,
        'titel' => 'Hoe zuinig is de dienst?',
        'intro' => 'Dit thema telt het minst mee. Een zuinige dienst hangt minder af van dure energie, en van '
            . 'grondstoffen die opraken.',
    ],
];

/**
 * Wat bij elk soort leverancier gebruikelijk is. regels.js zet dit als
 * voorstel in het formulier, zolang de invuller een vraag niet zelf beantwoordt.
 * Het is een eerste gok voor een gewone dienst van zo'n leverancier, geen
 * oordeel over een bepaald bedrijf. De voorbeelden bij de keuzes komen van
 * eucloudpatterns.eu.
 */
const SV_PROFIELEN = [
    'vs' => [
        'sv_eigenaar' => 'buiten', 'sv_stopzetten' => 'stopt',
        'sv_contract_recht' => 'eu', 'sv_buitenlandse_wet' => 'ja',
        'cloud_sleutels' => 'leverancier', 'cloud_locatie' => 'adequaatheid', 'sv_toegang_zien' => 'deels',
        'leverancier_overstap' => 'lastig', 'sv_support' => 'gemengd', 'sv_kennis' => 'nee', 'sv_onderaannemers' => 'buiten',
        'sv_hardware' => 'geheim', 'sv_software' => 'buiten', 'sv_afhankelijk' => 'ja',
        'sv_standaarden' => 'naast', 'sv_opensource' => 'nee',
        'sv_certificaat' => 'c5', 'sv_soc' => 'buiten', 'sv_audit' => 'rapport',
        'sv_energie' => 'per_datacenter', 'sv_stroom' => 'deels_eigen',
    ],
    'vs_eu' => [
        'sv_eigenaar' => 'buiten', 'sv_stopzetten' => 'stopt',
        'sv_contract_recht' => 'eu', 'sv_buitenlandse_wet' => 'ja',
        'cloud_sleutels' => 'leverancier', 'cloud_locatie' => 'eu', 'sv_toegang_zien' => 'deels',
        'leverancier_overstap' => 'lastig', 'sv_support' => 'eu_papier', 'sv_kennis' => 'nee', 'sv_onderaannemers' => 'buiten',
        'sv_hardware' => 'geheim', 'sv_software' => 'buiten', 'sv_afhankelijk' => 'ja',
        'sv_standaarden' => 'naast', 'sv_opensource' => 'nee',
        'sv_certificaat' => 'c5', 'sv_soc' => 'eu', 'sv_audit' => 'rapport',
        'sv_energie' => 'per_datacenter', 'sv_stroom' => 'deels_eigen',
    ],
    'licentie' => [
        'sv_eigenaar' => 'samen', 'sv_overname' => 'beschermd', 'sv_stopzetten' => 'maanden',
        'sv_contract_recht' => 'eu', 'sv_buitenlandse_wet' => 'deels',
        'cloud_sleutels' => 'byok', 'cloud_locatie' => 'eu', 'sv_toegang_zien' => 'deels',
        'leverancier_overstap' => 'lastig', 'sv_support' => 'eu', 'sv_kennis' => 'deels', 'sv_onderaannemers' => 'eu',
        'sv_hardware' => 'merk', 'sv_software' => 'buiten', 'sv_afhankelijk' => 'licentie',
        'sv_standaarden' => 'naast', 'sv_opensource' => 'nee',
        'sv_certificaat' => 'secnum', 'sv_soc' => 'eu', 'sv_audit' => 'contract',
        'sv_energie' => 'jaarverslag', 'sv_stroom' => 'certificaten',
    ],
    'eu' => [
        'sv_eigenaar' => 'eu', 'sv_overname' => 'geen', 'sv_stopzetten' => 'zelfstandig',
        'sv_contract_recht' => 'eu', 'sv_buitenlandse_wet' => 'nee',
        'cloud_sleutels' => 'byok', 'cloud_locatie' => 'eu', 'sv_toegang_zien' => 'deels',
        'leverancier_overstap' => 'makkelijk', 'sv_support' => 'eu_papier', 'sv_kennis' => 'ja', 'sv_onderaannemers' => 'eu',
        'sv_hardware' => 'merk', 'sv_software' => 'eu', 'sv_afhankelijk' => 'vervangbaar',
        'sv_standaarden' => 'vooral', 'sv_opensource' => 'grotendeels',
        'sv_certificaat' => 'c5', 'sv_soc' => 'eu', 'sv_audit' => 'contract',
        'sv_energie' => 'per_datacenter', 'sv_stroom' => 'deels_eigen',
    ],
    'overheid' => [
        'sv_eigenaar' => 'overheid', 'sv_stopzetten' => 'maanden',
        'sv_contract_recht' => 'eu', 'sv_buitenlandse_wet' => 'nee',
        'cloud_sleutels' => 'eigen', 'cloud_locatie' => 'eu', 'sv_toegang_zien' => 'volledig',
        'leverancier_overstap' => 'makkelijk', 'sv_support' => 'eu_screening', 'sv_kennis' => 'ja', 'sv_onderaannemers' => 'eu',
        'sv_hardware' => 'merk', 'sv_software' => 'buiten', 'sv_afhankelijk' => 'vervangbaar',
        'sv_standaarden' => 'naast', 'sv_opensource' => 'deels',
        'sv_certificaat' => 'iso', 'sv_soc' => 'eigen', 'sv_audit' => 'gedaan',
        'sv_energie' => 'jaarverslag', 'sv_stroom' => 'certificaten',
    ],
    'anders' => [],
];

/** @return list<array<string,mixed>> */
function sv_secties(): array
{
    $vragen = sv_themavragen();
    $secties = [sv_sectie_over()];

    foreach (SV_THEMAS as $id => $thema) {
        $secties[] = [
            'id' => $id,
            'titel' => $thema['titel'],
            'stap' => $thema['stap'],
            'intro' => $thema['intro'],
            'thema' => $id,
            'vragen' => [...$vragen[$id], [
                'key' => 'sv_afgeleid_' . $id,
                'type' => 'afgeleid',
                'bron' => $id,
                'label' => 'Label voor ' . mb_strtolower($thema['naam'], 'UTF-8'),
            ]],
        ];
    }

    return [...$secties, sv_sectie_uitkomst(sv_model($vragen))];
}

/** Een keuze in een thema: het niveau, de tekst en een uitleg eronder. @return array<string,mixed> */
function sv_optie(?int $niveau, string $label, string $hint = ''): array
{
    return ['label' => $label, 'niveau' => $niveau] + ($hint === '' ? [] : ['hint' => $hint]);
}

/**
 * Een vraag in een thema. 'Weet ik niet' staat er altijd onder, en telt als
 * het laagste niveau.
 *
 * @param array<string,array<string,mixed>> $opties
 * @param array<string,mixed> $extra
 * @return array<string,mixed>
 */
function sv_vraag(string $key, string $label, string $kort, string $hint, array $opties, string $tip, array $extra = []): array
{
    return $extra + [
        'key' => $key,
        'type' => 'radio',
        'label' => $label,
        'kort' => $kort,
        'verplicht' => true,
        'voorstel' => true,
        'opties' => $opties + ['onbekend' => sv_optie(0, 'Weet ik niet')],
        'tip' => $tip,
    ] + ($hint === '' ? [] : ['hint' => $hint]);
}

// ---------------------------------------------------------------------------

function sv_sectie_over(): array
{
    $profielen = [
        'vs' => ['label' => 'Een grote Amerikaanse cloud, in een Europees datacenter',
            'hint' => 'Bijvoorbeeld Microsoft Azure, Amazon Web Services of Google Cloud.'],
        'vs_eu' => ['label' => 'Een Amerikaanse cloud, met een apart Europees bedrijf ervoor',
            'hint' => 'Bijvoorbeeld de AWS European Sovereign Cloud.'],
        'licentie' => ['label' => 'Een Europees bedrijf dat Amerikaanse techniek gebruikt, onder licentie',
            'hint' => 'Bijvoorbeeld Bleu, S3NS of Delos Cloud.'],
        'eu' => ['label' => 'Een Europese cloudleverancier',
            'hint' => 'Bijvoorbeeld OVHcloud, Scaleway, STACKIT of IONOS.'],
        'overheid' => ['label' => 'Een datacenter van de overheid zelf'],
        'anders' => ['label' => 'Iets anders, of dat weet ik niet', 'hint' => 'Dan vult de scan niets voor je in.'],
    ];

    return [
        'id' => 'over',
        'titel' => 'Wat beoordeel je?',
        'intro' => 'Eerst een paar vragen over de dienst, en over de informatie die erin komt.',
        'vragen' => [
            [
                'key' => 'projectnaam',
                'type' => 'text',
                'label' => 'Voor welk project of welke toepassing is de dienst?',
                'hint' => 'Deze naam komt in het rapport en in de bestandsnaam.',
                'verplicht' => true,
            ],
            [
                'key' => 'cloud_leverancier',
                'type' => 'text',
                'label' => 'Welke clouddienst beoordeel je?',
                'hint' => 'De naam van de dienst, en van de leverancier.',
                'verplicht' => true,
            ],
            [
                'key' => 'aanvrager',
                'type' => 'text',
                'label' => 'Wie vult de scan in?',
                'hint' => 'Naam en functie.',
                'verplicht' => true,
            ],
            [
                'key' => 'sv_profiel',
                'type' => 'radio',
                'label' => 'Wat voor leverancier is het?',
                'hint' => 'Kies wat het best past. De scan vult dan alvast in wat bij zo’n leverancier gebruikelijk is. '
                    . 'Klopt een antwoord niet? Pas het dan aan.',
                'verplicht' => true,
                'opties' => $profielen,
                'groepen' => SV_PROFIELEN,
            ],
            [
                'key' => 'sv_belang',
                'type' => 'radio',
                'label' => 'Wat voor informatie komt er in de dienst?',
                'hint' => 'Dit bepaalt welk label minimaal nodig is. Kies de meest gevoelige informatie.',
                'verplicht' => true,
                'opties' => [
                    'openbaar' => ['label' => 'Openbare informatie', 'hint' => 'Informatie die iedereen mag zien, zoals '
                        . 'een website.'],
                    'gewoon' => ['label' => 'Gewone informatie van de organisatie', 'hint' => 'Ook gewone '
                        . 'persoonsgegevens, zoals namen en adressen.'],
                    'gevoelig' => ['label' => 'Gevoelige informatie, of een dienst die niet mag uitvallen',
                        'hint' => 'Bijvoorbeeld medische gegevens, gegevens over veel burgers, of een vitaal proces.'],
                    'geheim' => ['label' => 'Staatsgeheimen, of informatie over defensie en nationale veiligheid'],
                ],
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

/** @param array<string,mixed> $model wat regels.js nodig heeft, uit sv_model() */
function sv_sectie_uitkomst(array $model): array
{
    return [
        'id' => 'uitkomst',
        'titel' => 'De uitkomst',
        'stap' => 5,
        'intro' => 'Hier zie je het label van de dienst. Het rapport legt uit wat het betekent, en wat jullie kunnen '
            . 'doen.',
        'vragen' => [
            [
                'key' => 'sv_afgeleid_label',
                'type' => 'afgeleid',
                'bron' => 'label',
                'label' => 'Het label van deze dienst',
                'hint' => 'Het label is het gemiddelde van de acht thema’s. Niet elk thema telt even zwaar mee.',
                'groepen' => $model,
            ],
            [
                'key' => 'sv_opmerkingen',
                'type' => 'textarea',
                'label' => 'Wil je nog iets toelichten?',
                'hint' => 'Bijvoorbeeld een afspraak die nog in het contract komt.',
                'hoogte' => 3,
            ],
        ],
    ];
}

// ---------------------------------------------------------------------------

/** De vragen per thema. @return array<string,list<array<string,mixed>>> */
function sv_themavragen(): array
{
    return [
        'zeggenschap' => [
            sv_vraag('sv_eigenaar', 'Wie is uiteindelijk de eigenaar van de leverancier?', 'Eigenaar',
                'Kijk naar het moederbedrijf, en niet alleen naar de vestiging in Nederland.', [
                    'buiten_zonder' => sv_optie(0, 'Een bedrijf buiten de EU, zonder eigen bedrijf in de EU'),
                    'buiten' => sv_optie(1, 'Een bedrijf buiten de EU, met een dochterbedrijf in de EU',
                        'Bijvoorbeeld een Amerikaans bedrijf met een vestiging in Ierland.'),
                    'buiten_afspraken' => sv_optie(2, 'Een bedrijf buiten de EU, maar het Europese deel beslist zelf',
                        'Dat staat vast in afspraken, bijvoorbeeld over wie er in het bestuur zit.'),
                    'samen' => sv_optie(3, 'Een Europees bedrijf, dat samenwerkt met een bedrijf van buiten de EU',
                        'Het Europese bedrijf heeft de meeste zeggenschap. De ander levert bijvoorbeeld de techniek.'),
                    'eu' => sv_optie(4, 'Een Europees bedrijf, zonder eigenaar van buiten de EU'),
                    'overheid' => sv_optie(4, 'Een Nederlandse of andere Europese overheid'),
                ],
                'Een eigenaar buiten de EU kan onder druk komen van zijn eigen regering. Kijk of een Europese '
                    . 'leverancier hetzelfde kan.'),
            sv_vraag('sv_overname', 'Wat gebeurt er als een bedrijf van buiten de EU de leverancier koopt?',
                'Overname', 'Zo’n overname kan de zeggenschap in één keer veranderen.', [
                    'beschermd' => sv_optie(4, 'Dat kan niet, of wij mogen dan zonder kosten stoppen',
                        'De wet of het contract regelt dat.'),
                    'geen' => sv_optie(3, 'Daar is niets over geregeld'),
                ],
                'Spreek in het contract af dat jullie zonder kosten mogen stoppen als een bedrijf van buiten de EU '
                    . 'de leverancier koopt.',
                ['toon_als' => ['sv_eigenaar' => ['samen', 'eu']]]),
            sv_vraag('sv_stopzetten', 'Stel: een land buiten de EU verbiedt de leverancier om jullie te helpen. '
                . 'Blijft de dienst dan werken?', 'Doorwerken', 'Zo’n verbod kan komen door een sanctie, of door '
                . 'ruzie tussen landen.', [
                    'stopt' => sv_optie(1, 'Nee, de dienst stopt dan, of werkt zonder updates en hulp nog even door'),
                    'maanden' => sv_optie(2, 'Hij werkt maanden door, en er is een plan om over te stappen'),
                    'zelfstandig' => sv_optie(3, 'Ja, een Europese partij kan hem zelf blijven onderhouden'),
                    'geen_risico' => sv_optie(4, 'Ja, want er is geen partij van buiten de EU bij betrokken'),
                ],
                'Maak een plan voor als de leverancier moet stoppen. Hoe lang kunnen jullie door, en waar stappen '
                    . 'jullie dan naartoe?'),
        ],

        'recht' => [
            sv_vraag('sv_contract_recht', 'Onder welk recht valt het contract?', 'Contract', 'Dat staat meestal '
                . 'achter in het contract, of in de algemene voorwaarden.', [
                    'eu' => sv_optie(4, 'Nederlands recht, of het recht van een ander land in de EU'),
                    'buiten' => sv_optie(0, 'Het recht van een land buiten de EU'),
                ],
                'Zorg dat het contract onder Nederlands of Europees recht valt, met een rechter in de EU.'),
            sv_vraag('sv_buitenlandse_wet', 'Kan een land buiten de EU de leverancier dwingen om jullie data af te '
                . 'geven?', 'Buitenlandse wetten', 'Dat kan als de leverancier of zijn moederbedrijf onder zo’n wet '
                . 'valt, zoals de Amerikaanse CLOUD Act. Dat geldt ook als de data in Europa staat.', [
                    'ja' => sv_optie(1, 'Ja, en de leverancier kan de data dan ook lezen'),
                    'ja_versleuteld' => sv_optie(2, 'Ja, maar de leverancier kan de data niet lezen',
                        'Alleen jullie hebben de sleutels. Een bevel levert dan alleen onleesbare data op.'),
                    'deels' => sv_optie(3, 'Alleen via een partner van buiten de EU, en die heeft weinig invloed'),
                    'nee' => sv_optie(4, 'Nee, er is geen band met een land buiten de EU'),
                ],
                'Versleutel de data met sleutels die alleen jullie hebben. Dan levert een bevel van een ander land '
                    . 'alleen onleesbare data op.'),
        ],

        'data' => [
            sv_vraag('cloud_sleutels', 'Wie beheert de sleutels waarmee de data wordt versleuteld?', 'Sleutels',
                'Met die sleutels maak je de data weer leesbaar. Wie de sleutels heeft, kan dus bij de data.', [
                    'eigen' => sv_optie(4, 'Wij zelf, buiten de cloud van de leverancier',
                        'Bijvoorbeeld in een eigen kluis voor sleutels. De leverancier ziet de sleutels nooit.'),
                    'byok' => sv_optie(2, 'Wij zelf, maar de sleutels staan bij de leverancier',
                        'Jullie maken de sleutels, en zetten ze in de cloud van de leverancier.'),
                    'derde' => sv_optie(3, 'Een andere Europese partij die daarvoor gecertificeerd is'),
                    'leverancier' => sv_optie(1, 'De leverancier'),
                    'geen' => sv_optie(0, 'De data wordt niet versleuteld'),
                ],
                'Beheer de sleutels zelf, buiten de cloud van de leverancier. Dan kan niemand anders de data lezen.'),
            sv_vraag('cloud_locatie', 'Waar staat de data, en waar wordt die verwerkt?', 'Plek van de data',
                'Tel ook de reservekopieën mee, en de hulp op afstand. Een beheerder buiten de EU die kan meekijken, '
                . 'telt ook.', [
                    'eu' => sv_optie(4, 'Alleen in de EU'),
                    'eer' => sv_optie(3, 'In Europa, maar niet alleen in de EU',
                        'Ook in Noorwegen, IJsland, Liechtenstein of Zwitserland.'),
                    'adequaatheid' => sv_optie(1, 'Ook buiten Europa, in een land dat de EU veilig vindt',
                        'De EU heeft dan besloten dat dat land persoonsgegevens goed beschermt. Een voorbeeld is de '
                        . 'VS, via het Data Privacy Framework.'),
                    'modelcontract' => sv_optie(1, 'Ook buiten Europa, met een standaardcontract van de EU'),
                    'buiten-eer' => sv_optie(0, 'Ook buiten Europa, zonder zo’n besluit of contract'),
                ],
                'Spreek af dat de data, de reservekopieën en het beheer in de EU blijven. Laat de leverancier dat '
                    . 'technisch regelen, en niet alleen op papier.'),
            sv_vraag('sv_toegang_zien', 'Kun je zien wie er bij jullie data is geweest?', 'Toegang zien',
                'Een logboek laat zien wie de data heeft geopend, en wanneer.', [
                    'nee' => sv_optie(1, 'Nee, dat ziet alleen de leverancier'),
                    'deels' => sv_optie(2, 'Ja, maar niet als de leverancier zelf bij de data komt'),
                    'volledig' => sv_optie(4, 'Ja, alles, ook als de leverancier zelf bij de data komt'),
                ],
                'Vraag om een logboek waarin jullie zelf zien wie bij de data komt. Ook als het een medewerker van de '
                    . 'leverancier is.'),
            sv_vraag('sv_ai', 'Gebruikt de dienst AI?', 'AI', 'Bijvoorbeeld een assistent die teksten schrijft of '
                . 'samenvat.', [
                    'nee' => sv_optie(null, 'Nee', 'Deze vraag telt dan niet mee.'),
                    'buiten' => sv_optie(1, 'Ja, AI van een bedrijf buiten de EU, en die draait ook buiten de EU'),
                    'eu_hosting' => sv_optie(2, 'Ja, AI van een bedrijf buiten de EU, maar die draait in de EU'),
                    'open_eu' => sv_optie(3, 'Ja, AI met een openbaar model, en die draait in de EU',
                        'Iedereen mag zo’n model gebruiken en controleren. Een voorbeeld is Mistral.'),
                    'eu' => sv_optie(4, 'Ja, AI die helemaal in de EU is gemaakt en getraind, en daar ook draait'),
                ],
                'Gebruik AI die in de EU draait, met een openbaar model. Spreek af dat de leverancier jullie data '
                    . 'niet gebruikt om de AI te trainen.',
                ['voorstel' => false]),
        ],

        'beheer' => [
            sv_vraag('leverancier_overstap', 'Hoe makkelijk stappen jullie over naar een andere leverancier?',
                'Overstappen', '', [
                    'zelfstandig' => sv_optie(4, 'Makkelijk, en we hebben het al eens gedaan, zonder hulp',
                        'Jullie eigen mensen hebben de dienst toen bij een andere Europese leverancier gedraaid.'),
                    'geoefend' => sv_optie(3, 'Makkelijk, en we oefenen het elk jaar',
                        'Bijvoorbeeld door een reservekopie terug te zetten bij een andere leverancier.'),
                    'makkelijk' => sv_optie(2, 'Makkelijk, maar we hebben het nooit geoefend',
                        'Het kan binnen een paar maanden, zonder grote kosten.'),
                    'lastig' => sv_optie(1, 'Lastig', 'Het kan, maar het kost veel tijd of geld.'),
                    'vast' => sv_optie(0, 'Bijna niet', 'We zitten vast aan deze leverancier.'),
                ],
                'Maak een plan om over te stappen, en oefen het. Kies techniek die ook bij andere leveranciers werkt.'),
            sv_vraag('sv_support', 'Waar zitten de mensen die de dienst beheren?', 'Beheerders', 'Denk aan de '
                . 'beheerders en de helpdesk. Ook de specialisten achter de helpdesk tellen mee.', [
                    'buiten' => sv_optie(0, 'Vooral buiten de EU'),
                    'gemengd' => sv_optie(1, 'In de EU, maar moeilijke problemen gaan naar een team buiten de EU'),
                    'eu_papier' => sv_optie(2, 'Alleen in de EU, dat staat in het contract, maar niemand controleert het'),
                    'eu' => sv_optie(3, 'Alleen in de EU, en een onafhankelijke partij controleert dat'),
                    'eu_screening' => sv_optie(4, 'Alleen in de EU, gescreend, en alleen onder Europees recht',
                        'Bijvoorbeeld met een veiligheidsonderzoek.'),
                ],
                'Spreek af dat alleen mensen in de EU de dienst beheren, ook bij moeilijke problemen. Laat een '
                    . 'onafhankelijke partij dat controleren.'),
            sv_vraag('sv_kennis', 'Kunnen jullie de dienst draaiende houden zonder deze leverancier?', 'Eigen kennis',
                'Dat mag zelf zijn, of met een andere Europese partij.', [
                    'nee' => sv_optie(1, 'Nee, alleen de leverancier weet hoe het werkt'),
                    'deels' => sv_optie(2, 'Deels, maar dan hebben we de leverancier nog wel nodig'),
                    'ja' => sv_optie(3, 'Ja, er is genoeg kennis en uitleg in Europa'),
                    'volledig' => sv_optie(4, 'Ja, en er ligt een kopie van de broncode voor ons klaar',
                        'Bij een notaris of een andere partij. Stopt de leverancier, dan krijgen jullie de code.'),
                ],
                'Zorg voor eigen kennis, of voor een Europese partner die de dienst kan overnemen. Vraag om een kopie '
                    . 'van de broncode bij een notaris.'),
            sv_vraag('sv_onderaannemers', 'Werkt de leverancier met andere bedrijven voor deze dienst?',
                'Andere bedrijven', 'Bijvoorbeeld voor de datacenters, de helpdesk of de software.', [
                    'onbekende' => sv_optie(1, 'Ja, maar we weten niet welke'),
                    'buiten' => sv_optie(1, 'Ja, ook met bedrijven buiten de EU'),
                    'eu' => sv_optie(3, 'Ja, alleen met bedrijven in de EU, en we krijgen een lijst'),
                    'geen' => sv_optie(4, 'Nee, de leverancier doet alles zelf'),
                ],
                'Vraag om een lijst van alle bedrijven die aan de dienst werken, en waar ze zitten. Spreek af dat de '
                    . 'leverancier een wijziging eerst meldt.'),
        ],

        'keten' => [
            sv_vraag('sv_hardware', 'Weet je waar de servers en de chips vandaan komen?', 'Servers en chips',
                'Bijna alle chips komen van buiten de EU. Het gaat erom of de leverancier dat laat zien, en of hij '
                . 'van één merk afhangt.', [
                    'geheim' => sv_optie(1, 'Nee, de leverancier zegt er weinig of niets over'),
                    'merk' => sv_optie(2, 'Ja, per onderdeel het merk en het land'),
                    'gespreid' => sv_optie(3, 'Ja, en de leverancier gebruikt verschillende merken'),
                    'eu' => sv_optie(4, 'Ja, en ze komen vooral uit Europese fabrieken'),
                ],
                'Vraag de leverancier welke merken hij gebruikt, en uit welke landen ze komen. Met meer merken is hij '
                    . 'minder afhankelijk.'),
            sv_vraag('sv_software', 'Waar is de software gemaakt, en waar komen de updates vandaan?', 'Software', '', [
                    'buiten' => sv_optie(1, 'Buiten de EU'),
                    'lijst' => sv_optie(2, 'Buiten de EU, maar we krijgen een lijst van alle onderdelen',
                        'Zo’n lijst heet een SBOM. Daarmee zie je snel of er een lek in zit.'),
                    'eu' => sv_optie(3, 'In de EU, en de updates komen ook uit de EU'),
                    'eu_controle' => sv_optie(4, 'Helemaal in de EU, en we kunnen elke update controleren'),
                ],
                'Vraag om een lijst van alle onderdelen van de software. Dan zien jullie snel of er een lek in zit, '
                    . 'en waar het vandaan komt.'),
            sv_vraag('sv_afhankelijk', 'Hangt de dienst af van één bedrijf buiten de EU?', 'Afhankelijkheid',
                'Bijvoorbeeld voor het besturingssysteem, de database of de techniek onder de cloud.', [
                    'ja' => sv_optie(1, 'Ja, zonder dat bedrijf werkt het niet'),
                    'vervangbaar' => sv_optie(2, 'Ja, maar dat bedrijf is te vervangen'),
                    'licentie' => sv_optie(3, 'Ja, maar een Europese partij heeft een licentie en kan zelf verder'),
                    'nee' => sv_optie(4, 'Nee, er zijn altijd Europese of open alternatieven'),
                ],
                'Kies waar het kan voor onderdelen met een Europees of open alternatief. Dan hangt de dienst niet af '
                    . 'van één bedrijf.'),
        ],

        'techniek' => [
            sv_vraag('sv_standaarden', 'Werkt de dienst met open standaarden?', 'Open standaarden', 'Bijvoorbeeld '
                . 'Kubernetes, PostgreSQL of S3. Die werken bij veel leveranciers op dezelfde manier.', [
                    'nee' => sv_optie(0, 'Nee, alleen met eigen techniek van de leverancier'),
                    'naast' => sv_optie(1, 'Deels, naast eigen techniek van de leverancier'),
                    'vooral' => sv_optie(3, 'Ja, vooral met open standaarden'),
                    'alles' => sv_optie(4, 'Ja, alles werkt met open standaarden'),
                ],
                'Kies waar het kan voor open standaarden. Dan kunnen jullie het werk meenemen naar een andere '
                    . 'leverancier.'),
            sv_vraag('sv_opensource', 'Is de software open source?', 'Open source', 'Open source betekent: je mag '
                . 'de code bekijken, aanpassen en delen. Code die je alleen mag bekijken, telt niet mee.', [
                    'nee' => sv_optie(1, 'Nee, de code is geheim'),
                    'deels' => sv_optie(2, 'Deels'),
                    'grotendeels' => sv_optie(3, 'Grotendeels, en er is een lijst van alle licenties'),
                    'ja' => sv_optie(4, 'Ja, alles'),
                ],
                'Vraag welke onderdelen open source zijn, en onder welke licentie. Open source kun je zelf '
                    . 'controleren, en een ander kan het overnemen.'),
        ],

        'beveiliging' => [
            sv_vraag('sv_certificaat', 'Welk keurmerk voor beveiliging heeft de dienst?', 'Keurmerk', 'Een '
                . 'onafhankelijke partij heeft dan gecontroleerd of de beveiliging goed is. Kies het hoogste '
                . 'keurmerk.', [
                    'geen' => sv_optie(0, 'Geen'),
                    'iso' => sv_optie(1, 'Een internationaal keurmerk, zoals ISO 27001 of SOC 2'),
                    'c5' => sv_optie(2, 'Een Europees keurmerk, zoals het Duitse BSI C5'),
                    'secnum' => sv_optie(3, 'Een keurmerk dat ook eist dat de leverancier Europees is',
                        'Een voorbeeld is het Franse SecNumCloud.'),
                    'nbv' => sv_optie(4, 'Goedgekeurd door de Nederlandse overheid voor geheime informatie',
                        'Bijvoorbeeld door het Nationaal Bureau voor Verbindingsbeveiliging van de AIVD.'),
                ],
                'Vraag om een Europees keurmerk dat ook kijkt naar wie de baas is, zoals SecNumCloud. Een certificaat '
                    . 'voor ISO 27001 zegt daar niets over.'),
            sv_vraag('sv_soc', 'Wie houdt de beveiliging dag en nacht in de gaten?', 'Bewaking', 'Dat doet een team '
                . 'dat meldingen van aanvallen bekijkt. Zo’n team heet een SOC.', [
                    'buiten' => sv_optie(1, 'Een team ergens in de wereld'),
                    'overdracht' => sv_optie(2, 'Een team in de EU, maar ’s nachts neemt een team buiten de EU het over'),
                    'eu' => sv_optie(3, 'Alleen een team in de EU, dag en nacht'),
                    'eigen' => sv_optie(4, 'Wij zelf, of een Europese partij die wij kiezen, en we zien alle meldingen'),
                ],
                'Spreek af dat alleen een team in de EU de beveiliging bewaakt, ook ’s nachts. Of laat een Europese '
                    . 'partij meekijken die jullie zelf kiezen.'),
            sv_vraag('sv_audit', 'Mogen jullie de beveiliging zelf laten controleren?', 'Zelf controleren',
                'Bijvoorbeeld door een eigen auditor, die ook in het datacenter mag kijken.', [
                    'rapport' => sv_optie(1, 'Nee, we krijgen alleen de rapporten van de leverancier'),
                    'contract' => sv_optie(2, 'Ja, dat staat in het contract'),
                    'gedaan' => sv_optie(4, 'Ja, en dat is ook al gebeurd, met toegang tot alles'),
                ],
                'Leg in het contract vast dat jullie de beveiliging zelf mogen laten controleren, ook in het '
                    . 'datacenter. En doe dat dan ook.'),
        ],

        'duurzaam' => [
            sv_vraag('sv_energie', 'Laat de leverancier zien hoeveel energie en water de dienst gebruikt?',
                'Energie en water', '', [
                    'nee' => sv_optie(0, 'Nee'),
                    'jaarverslag' => sv_optie(1, 'Alleen in een jaarverslag, voor het hele bedrijf'),
                    'per_datacenter' => sv_optie(2, 'Ja, per datacenter, met de uitstoot van CO2',
                        'Vaak ook met de PUE: een getal dat laat zien hoe zuinig het datacenter is.'),
                    'per_dienst' => sv_optie(3, 'Ja, zelfs per dienst en ook het water, en een accountant controleert het'),
                ],
                'Vraag per datacenter om cijfers over energie, water en uitstoot. Dan kunnen jullie diensten met '
                    . 'elkaar vergelijken.'),
            sv_vraag('sv_stroom', 'Waar komt de stroom vandaan?', 'Stroom', '', [
                    'grijs' => sv_optie(0, 'Gewone stroom'),
                    'certificaten' => sv_optie(1, 'Groene stroom, via certificaten',
                        'De leverancier koopt dan papieren bewijzen van groene stroom. De stroom zelf kan grijs zijn.'),
                    'deels_eigen' => sv_optie(2, 'Deels uit eigen contracten met windparken of zonneparken'),
                    'eigen' => sv_optie(4, 'Helemaal uit eigen contracten met nieuwe windparken of zonneparken'),
                ],
                'Vraag hoeveel stroom echt uit nieuwe windparken of zonneparken komt. Certificaten alleen zeggen '
                    . 'weinig.'),
        ],
    ];
}
