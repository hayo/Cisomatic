<?php

/**
 * De check op één A4, om op papier of in Word in te vullen. De motor maakt hem
 * niet uit de secties, zoals bij de andere formulieren, maar neemt dit blad
 * over ('vragenlijst_blad' in de definitie). Zo past alles op één pagina:
 * - het kanaal en het platform, met per platform of uitleg altijd nodig is
 * - de doelen, met per platform of ze binnen het kader vallen
 * - de twee voorwaarden
 * - de uitkomst, met de regel in één zin, en bij uitleg de vier vragen
 *   uit de notitie
 *
 * De matrix komt uit ../socialemedia/kaders.php, via oc_ruimte(). De
 * rekenregel staat hier in woorden. Verandert oc_evalueer(), loop dan ook dit
 * bestand na.
 */

declare(strict_types=1);

/** De volgorde van de kolommen: eerst wat in beginsel inzetbaar is, dan waar uitleg altijd nodig is. */
const OC_KOLOMMEN = ['instagram', 'youtube', 'linkedin', 'mastodon', 'facebook', 'x', 'tiktok'];

/**
 * @param callable(string):string $nr het nummer van een vraag; niet nodig op één blad
 * @return array<string,mixed>
 */
function oc_vragenlijst(callable $nr): array
{
    $kaders = sm_kaders();
    $ruim = "\u{00A0}\u{00A0}\u{00A0}\u{00A0}";
    $hokjes = static fn (array $labels): string => implode($ruim, array_map(static fn (string $l): string => '☐ ' . $l, $labels));
    $naam = static fn (string $k): string => $kaders[$k]['naam'];
    $inzetbaar = array_values(array_filter(OC_KOLOMMEN, static fn (string $k): bool => empty($kaders[$k]['explain'])));
    $explain = array_values(array_filter(OC_KOLOMMEN, static fn (string $k): bool => !empty($kaders[$k]['explain'])));
    $toestel = array_values(array_filter(OC_KOLOMMEN, static fn (string $k): bool => !empty($kaders[$k]['toestel'])));

    return ['blad' => [
        ['h1', 'Online communicatie check'],
        ['noot', 'Eén check per kanaal. Past alles binnen het rijksbrede kader, dan voldoet het kanaal. Zo niet, dan '
            . 'leg je uit waarom jullie het toch gebruiken (explain).'],

        ['vraagblok', [
            'nr' => '1',
            'vraag' => 'Het kanaal',
            'keuzes' => [
                ['los' => true, 'Kanaal:' . str_repeat("\u{00A0}", 58) . 'Organisatie:' . str_repeat("\u{00A0}", 42) . 'Datum:'],
                ['los' => true, 'Ingevuld door:' . str_repeat("\u{00A0}", 50) . 'Vastgesteld door (directeur Communicatie):'],
            ],
        ]],

        ['vraagblok', [
            'nr' => '2',
            'vraag' => 'Op welk platform zit het kanaal?',
            'bijzin' => 'kies één',
            'keuzes' => [
                ['los' => true, 'In beginsel inzetbaar:' . $ruim . $hokjes(array_map($naam, $inzetbaar))],
                ['los' => true, 'Altijd uitleg nodig:' . $ruim . $hokjes([...array_map($naam, $explain), 'Ander kanaal: ……………………'])],
            ],
        ]],

        ['vraagblok', [
            'nr' => '3',
            'vraag' => 'Welke doelen heeft het kanaal?',
            'bijzin' => 'kruis aan wat past',
            'noten' => [
                'Kijk bij elk aangekruist doel in de kolom van je platform. ✗: het kader zegt ‘niet’, leg uit. ~: het '
                    . 'kader noemt het niet; het mag, maar ga na of het past. *: alleen met advertenties. Leeg: mag. '
                    . 'Mastodon is social.overheid.nl.',
            ],
            'tabel' => oc_vragenlijst_matrix($kaders),
        ]],

        ['vraagblok', [
            'nr' => '4',
            'vraag' => 'Voldoe je aan de voorwaarden?',
            'bijzin' => 'kies per regel',
            'keuzes' => [
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Alles staat ook op een eigen kanaal van de overheid. '
                    . 'Niet nodig op ' . $naam('mastodon') . '.'],
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Je wijst mensen op de risico’s van contact, en '
                    . 'verwijst door naar een kanaal van de overheid.'],
            ],
        ]],

        ['vraagblok', [
            'nr' => '5',
            'vraag' => 'De uitkomst:' . $ruim . $hokjes(array_column(OC_UITKOMSTEN, 'label')),
            'noten' => [
                'Het kanaal voldoet als het platform in beginsel inzetbaar is, geen doel een ✗ heeft en beide '
                    . 'voorwaarden ja zijn. Anders is uitleg nodig: beantwoord a tot en met d.',
            ],
            'tabel' => [
                'kop' => [
                    'a. Waarvan wijk je af?',
                    'b. Waarom is dit kanaal nodig? Noem de doelgroep.',
                    'c. Waarom volstaat een minder risicovol kanaal niet?',
                    'd. Welke maatregelen neem je tegen de risico’s?',
                ],
                // Lege regels, om te schrijven. Zo groeit het vak ook in Word niet weg.
                'rijen' => [array_fill(0, 4, ['', ...array_fill(0, 8, "\u{00A0}")])],
                'breedtes' => [3.85, 3.85, 3.85, 3.85],
                'voor' => 'Uitleg',
            ],
        ]],

        ['noot', cm_opsomming(array_map($naam, $toestel)) . ' mag nooit op een toestel van het Rijk. Bron: '
            . 'platformkaders 1.0, bij de notitie ‘Aanwezigheid Rijksoverheid op sociale media’. Cisomatic '
            . cm_versie() . '.'],
    ]];
}

/**
 * De doelen met per platform ✗ (uitleg nodig), ~ (niet genoemd), * (alleen met
 * advertenties) of leeg (mag). Het hokje zet de motor ervoor.
 *
 * @param array<string,array<string,mixed>> $kaders
 * @return array<string,mixed>
 */
function oc_vragenlijst_matrix(array $kaders): array
{
    // Smalle kolommen: lange namen mogen afbreken, met een zacht afbreekstreepje.
    $kort = [
        'instagram' => 'Instagram',
        'youtube' => "You\u{00AD}Tube",
        'linkedin' => "Linked\u{00AD}In",
        'mastodon' => 'Mastodon',
        'facebook' => "Face\u{00AD}book",
        'x' => 'X',
        'tiktok' => "Tik\u{00AD}Tok",
    ];
    $teken = ['ja' => '', 'betaald' => '*', 'onbekend' => '~', 'nee' => '✗'];

    $rijen = [];
    foreach (oc_doelen() as $k => $doel) {
        $rijen[] = [
            $doel['label'],
            ...array_map(static fn (string $p): string => $teken[oc_ruimte($kaders[$p], $k)], OC_KOLOMMEN),
        ];
    }

    return [
        'kop' => ['Doel', ...array_map(static fn (string $p): string => $kort[$p], OC_KOLOMMEN)],
        'rijen' => $rijen,
        'breedtes' => [5.0, 1.6, 1.6, 1.55, 1.6, 1.6, 0.75, 1.4],
    ];
}
