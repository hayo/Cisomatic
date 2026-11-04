<?php

/**
 * De vragen. Vier stappen: het kanaal, de doelen, twee voorwaarden, en de
 * uitkomst met bij een explain de uitleg. Eén check gaat over één kanaal.
 *
 * De sleutels van het platform, de voorwaarden en het vaststellen zijn die van
 * de kanalenscan (sm_). Een bestand van de kanalenscan vult ze dus in.
 */

declare(strict_types=1);

/** @return list<array<string,mixed>> */
function oc_secties(): array
{
    return [oc_sectie_kanaal(), oc_sectie_doelen(), oc_sectie_voorwaarden(), oc_sectie_besluit()];
}

function oc_sectie_kanaal(): array
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
        'intro' => 'Eén check gaat over één kanaal. Heb je een Facebookpagina en een account op Instagram? Dan doe je '
            . 'twee checks.',
        'vragen' => [
            [
                'key' => 'projectnaam',
                'type' => 'text',
                'label' => 'Om welk kanaal gaat het?',
                'hint' => 'Bijvoorbeeld "Instagram van de Belastingdienst". De naam komt terug in de bestandsnaam.',
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

function oc_sectie_doelen(): array
{
    return [
        'id' => 'doelen',
        'titel' => 'De doelen',
        'stap' => 2,
        'intro' => 'Waarvoor gebruiken jullie het kanaal? Elk doel moet binnen het kader van het platform vallen.',
        'vragen' => [
            [
                'key' => 'oc_doelen',
                'type' => 'checkbox',
                'label' => 'Welke doelen heeft het kanaal?',
                'hint' => 'Kruis alles aan wat past. Ook wat jullie er af en toe mee doen.',
                'verplicht' => true,
                'opties' => oc_doelen(),
            ],
            [
                'key' => 'oc_toets_doelen',
                'type' => 'afgeleid',
                'bron' => 'doelen',
                'label' => 'Past dit binnen het kader?',
            ],
        ],
    ];
}

function oc_sectie_voorwaarden(): array
{
    return [
        'id' => 'voorwaarden',
        'titel' => 'De voorwaarden',
        'stap' => 3,
        'intro' => 'Twee voorwaarden gelden voor elk platform.',
        'vragen' => [
            [
                'key' => 'sm_eigen_kanalen',
                'type' => 'radio',
                'label' => 'Staat alles wat je hier plaatst ook op een eigen kanaal van de overheid?',
                'hint' => 'Zoals je eigen site of social.overheid.nl. Dit kanaal komt erbij, niet in plaats daarvan.',
                'verplicht_als' => ['sm_platform' => oc_platforms_behalve('mastodon')],
                'toon_als' => ['sm_platform' => oc_platforms_behalve('mastodon')],
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
                'hint' => 'Naar een kanaal van de overheid. Dat geldt ook als je zelf geen vragen beantwoordt. Op '
                    . 'social.overheid.nl gaat het om vragen met privacygevoelige gegevens.',
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
        'titel' => 'Comply or explain',
        'stap' => 4,
        'intro' => 'Past alles binnen het kader, dan is het comply. Zo niet, dan leg je uit waarom jullie het kanaal '
            . 'toch nodig hebben.',
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
                'label' => 'Waarvan wijk je af?',
                'hint' => 'De check vult in wat buiten het kader valt. Vul aan waar nodig.',
                'motivatie_voor' => 'afwijking',
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 3,
            ],
            [
                'key' => 'oc_noodzaak',
                'type' => 'textarea',
                'label' => 'Waarom is dit kanaal nodig?',
                'hint' => 'Noem de doelgroep, en laat zien dat je die hier echt bereikt.',
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 3,
            ],
            [
                'key' => 'oc_alternatief',
                'type' => 'textarea',
                'label' => 'Waarom volstaat een minder risicovol kanaal niet?',
                'hint' => 'Zoals je eigen site, een nieuwsbrief, social.overheid.nl, of een platform dat in beginsel '
                    . 'inzetbaar is.',
                'toon_als_uitkomst' => $explain,
                'verplicht_als_uitkomst' => $explain,
                'hoogte' => 2,
            ],
            [
                'key' => 'oc_maatregelen',
                'type' => 'textarea',
                'label' => 'Welke maatregelen neem je tegen de risico’s?',
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

/** Alle platforms behalve deze, voor toon_als. @return list<string> */
function oc_platforms_behalve(string ...$uit): array
{
    return array_values(array_diff(array_keys(sm_kaders()), $uit));
}
