<?php

/**
 * De vragen. Drie stappen: het kanaal, de voorwaarden, en de uitkomst met de
 * uitleg als het kanaal niet voldoet. Eén check gaat over één kanaal. De check
 * vraagt alleen wat juridisch telt; doelen en doelgroep zijn advies, en die
 * horen in de kanalenscan.
 *
 * De sleutels van het platform, de voorwaarden en het vaststellen zijn die van
 * de kanalenscan (sm_). Een bestand van de kanalenscan vult ze dus in.
 */

declare(strict_types=1);

/** @return list<array<string,mixed>> */
function oc_secties(): array
{
    return [oc_sectie_kanaal(), oc_sectie_voorwaarden(), oc_sectie_besluit()];
}

function oc_sectie_kanaal(): array
{
    $platforms = [];
    foreach (sm_kaders() as $k => $kader) {
        $platforms[$k] = [
            'label' => $kader['label'] ?? $kader['naam'],
            'hint' => trim(($kader['hint'] ?? '') . ' ' . (empty($kader['explain'])
                ? 'In beginsel inzetbaar.'
                : 'Altijd uitleg nodig.')),
        ];
    }

    return [
        'id' => 'kanaal',
        'titel' => 'Het kanaal',
        'stap' => 1,
        'intro' => 'Eén check gaat over één kanaal. Heb je een Facebookpagina en een account op Instagram? Dan doe je '
            . 'twee checks.',
        'vragen' => [
            [
                'key' => 'projectnaam',
                'type' => 'text',
                'label' => 'Welk platform en welke accountnaam?',
                'hint' => 'Bijvoorbeeld "Instagram, @belastingdienst". De naam komt terug in de bestandsnaam.',
                'verplicht' => true,
            ],
            [
                'key' => 'sm_platform',
                'type' => 'radio',
                'label' => 'Op welk platform zit het kanaal?',
                'verplicht' => true,
                'opties' => $platforms,
                'groepen' => oc_regelgegevens(),
            ],
            [
                'key' => 'sm_anders_naam',
                'type' => 'text',
                'label' => 'Welk platform is het?',
                'verplicht_als' => ['sm_platform' => ['anders']],
                'toon_als' => ['sm_platform' => ['anders']],
            ],
            [
                'key' => 'oc_kader',
                'type' => 'afgeleid',
                'bron' => 'kader',
                'label' => 'Het rijksbrede kader:',
            ],
            [
                'key' => 'aanvrager',
                'type' => 'text',
                'label' => 'Wie vult deze check in?',
                'hint' => 'Naam en functie.',
                'verplicht' => true,
            ],
            [
                'key' => 'organisatieonderdeel',
                'type' => 'text',
                'label' => 'Voor welke organisatie is dit?',
                'optioneel' => true,
            ],
        ],
    ];
}

function oc_sectie_voorwaarden(): array
{
    return [
        'id' => 'voorwaarden',
        'titel' => 'De voorwaarden',
        'stap' => 2,
        'toon_als' => ['sm_platform' => oc_platforms_met_voorwaarden()],
        'intro' => 'Het kader stelt drie voorwaarden. Behalve op social.overheid.nl: dat is het basiskanaal van de '
            . 'overheid, en daar mag alles.',
        'vragen' => [
            [
                'key' => 'oc_contact',
                'type' => 'radio',
                'label' => 'Gebruik je het kanaal voor dienstverlening of veilig contact?',
                'hint' => 'Iemand helpen met een eigen vraag, aanvraag of zaak, vaak met persoonsgegevens.',
                'verplicht' => true,
                'opties' => [
                    'nee' => ['label' => 'Nee'],
                    'ja' => ['label' => 'Ja'],
                ],
            ],
            [
                'key' => 'sm_eigen_kanalen',
                'type' => 'radio',
                'label' => 'Staat alles wat je hier plaatst ook op een eigen kanaal van de overheid?',
                'hint' => 'Zoals je eigen site of social.overheid.nl. Dit kanaal komt erbij, niet in plaats daarvan.',
                'verplicht' => true,
                'opties' => [
                    'ja' => ['label' => 'Ja, alles'],
                    'deels' => ['label' => 'Voor een deel'],
                    'nee' => ['label' => 'Nee'],
                ],
            ],
            [
                'key' => 'sm_doorverwijzen',
                'type' => 'radio',
                'label' => 'Wijs je mensen op de risico’s van contact via dit platform, en verwijs je door?',
                'hint' => 'Naar een kanaal van de overheid. Dat geldt ook als je zelf geen vragen beantwoordt.',
                'verplicht' => true,
                'opties' => [
                    'ja' => ['label' => 'Ja'],
                    'nee' => ['label' => 'Nee'],
                ],
            ],
        ],
    ];
}

function oc_sectie_besluit(): array
{
    $explain = ['uitkomst' => ['explain']];

    return [
        'id' => 'besluit',
        'titel' => 'Voldoet het kanaal aan de platformkaders?',
        'stap' => 3,
        'intro' => 'Voldoet het kanaal niet, dan leg je uit waarom jullie het toch nodig hebben.',
        'vragen' => [
            [
                'key' => 'oc_uitkomst',
                'type' => 'afgeleid',
                'bron' => 'uitkomst',
                'label' => 'Uitkomst:',
            ],
            [
                'key' => 'oc_afwijking',
                'type' => 'textarea',
                'label' => OC_UITLEG['oc_afwijking'],
                'hint' => 'De check vult in wat niet voldoet. Vul aan waar nodig.',
                'motivatie_voor' => 'afwijking',
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 3,
            ],
            [
                'key' => 'oc_noodzaak',
                'type' => 'textarea',
                'label' => OC_UITLEG['oc_noodzaak'],
                'hint' => 'Laat zien dat je de doelgroep hier echt bereikt.',
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 3,
            ],
            [
                'key' => 'oc_alternatief',
                'type' => 'textarea',
                'label' => OC_UITLEG['oc_alternatief'],
                'hint' => 'Zoals je eigen site, een nieuwsbrief, social.overheid.nl, of een platform dat in beginsel '
                    . 'inzetbaar is.',
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 2,
            ],
            [
                'key' => 'oc_maatregelen',
                'type' => 'textarea',
                'label' => OC_UITLEG['oc_maatregelen'],
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 2,
            ],
            [
                'key' => 'sm_directeur',
                'type' => 'text',
                'label' => 'Wie stelt dit vast?',
                'hint' => 'De directeur, het hoofd of de manager Communicatie, of wie daarvoor gemandateerd is.',
                'optioneel' => true,
            ],
        ],
    ];
}

/** De platforms waar de voorwaarden gelden: alle behalve het basiskanaal. @return list<string> */
function oc_platforms_met_voorwaarden(): array
{
    return array_keys(array_filter(sm_kaders(), static fn (array $k): bool => !oc_basis($k)));
}
