<?php

/**
 * De check op één A4, om op papier of in Word in te vullen. De motor maakt hem
 * niet uit de secties, zoals bij de andere formulieren, maar neemt dit blad
 * over ('vragenlijst_blad' in de definitie). Zo past alles op één pagina:
 * - het kanaal
 * - het platform, in een tabel met wat het kader per platform eist
 * - de voorwaarden, als zinnen waar ja goed is
 * - de uitkomst, met de regel in één zin, en bij uitleg de vier vragen uit de
 *   notitie
 *
 * De tabel komt uit ../socialemedia/kaders.php en afleiding.php. De rekenregel
 * staat hier in woorden. Verandert oc_evalueer(), loop dan ook dit bestand na.
 */

declare(strict_types=1);

/** De volgorde van de platforms: eerst wat in beginsel inzetbaar is, dan waar uitleg altijd nodig is. */
const OC_KOLOMMEN = ['instagram', 'youtube', 'linkedin', 'mastodon', 'facebook', 'x', 'tiktok', 'anders'];

/** Waarom uitleg altijd nodig is, kort genoeg voor de tabel op het A4. */
const OC_GRONDEN_KORT = [
    'facebook' => 'DPIA en HRIA: hoge risico’s. Meta voldoet niet aan de DSA.',
    'x' => 'Voldoet niet aan de DSA, en daarvoor beboet.',
    'tiktok' => 'Schendt voorlopig de DSA. Risico voor de nationale veiligheid.',
    'anders' => 'Geen rijksbreed kader.',
];

/**
 * @param callable(string):string $nr het nummer van een vraag; niet nodig op één blad
 * @return array<string,mixed>
 */
function oc_vragenlijst(callable $nr): array
{
    $kaders = sm_kaders();
    $ruim = "\u{00A0}\u{00A0}\u{00A0}\u{00A0}";
    $hokjes = static fn (array $labels): string => implode($ruim, array_map(static fn (string $l): string => '☐ ' . $l, $labels));
    $mastodon = $kaders['mastodon']['naam'];

    return ['blad' => [
        ['h1', 'Online communicatie check'],
        ['noot', 'Eén check per kanaal. Voldoet het kanaal aan het rijksbrede kader? Zo niet, dan leg je uit waarom '
            . 'jullie het toch gebruiken (explain). De check toetst alleen wat het kader eist.'],

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
            'tabel' => oc_vragenlijst_platforms($kaders),
        ]],

        ['vraagblok', [
            'nr' => '3',
            'vraag' => 'Klopt dit voor het kanaal?',
            'bijzin' => 'kies per regel',
            'keuzes' => [
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Je gebruikt het kanaal niet voor dienstverlening of '
                    . 'veilig contact. Niet nodig op ' . $mastodon . '.'],
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Alles staat ook op een eigen kanaal van de overheid. '
                    . 'Niet nodig op ' . $mastodon . '.'],
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Je wijst mensen op de risico’s van contact, en '
                    . 'verwijst door naar een kanaal van de overheid.'],
            ],
        ]],

        ['vraagblok', [
            'nr' => '4',
            'vraag' => 'De uitkomst:' . $ruim . $hokjes(array_column(OC_UITKOMSTEN, 'label')),
            'noten' => [
                'Het kanaal voldoet als bij vraag 2 ‘Altijd uitleg’ nee is, en bij vraag 3 alles ja. Anders is uitleg '
                    . 'nodig: beantwoord a tot en met d.',
            ],
            'tabel' => [
                'kop' => [
                    'a. Waarvan wijk je af?',
                    'b. Waarom is dit kanaal nodig? Noem de doelgroep.',
                    'c. Waarom volstaat een minder risicovol kanaal niet?',
                    'd. Welke maatregelen neem je tegen de risico’s?',
                ],
                // Lege regels, om te schrijven. Zo groeit het vak ook in Word niet weg.
                'rijen' => [array_fill(0, 4, ['', ...array_fill(0, 13, "\u{00A0}")])],
                'breedtes' => [3.85, 3.85, 3.85, 3.85],
                'voor' => 'Uitleg',
            ],
        ]],

        ['noot', 'Bron: rijksbrede platformkaders 1.0, bij de notitie ‘Aanwezigheid Rijksoverheid op sociale media’. '
            . 'Cisomatic ' . cm_versie() . '.'],
    ]];
}

/**
 * De platforms met wat het kader eist: altijd uitleg of niet, of dienstverlening
 * en veilig contact mogen, en waarom. Het hokje zet de motor ervoor.
 *
 * @param array<string,array<string,mixed>> $kaders
 * @return array<string,mixed>
 */
function oc_vragenlijst_platforms(array $kaders): array
{
    $rijen = [];
    foreach (OC_KOLOMMEN as $k) {
        $kader = $kaders[$k] + ['explain' => false, 'toestel' => false];
        $naam = $k === 'anders' ? 'Ander kanaal: ……………' : $kader['naam'];
        $extra = array_filter([
            $kader['toestel'] ? 'Nooit op een toestel van het Rijk.' : '',
            oc_gedrag_betaald($kader) ? 'Gedrag beïnvloeden alleen met advertenties.' : '',
        ]);
        $rijen[] = [
            $naam,
            $kader['explain'] ? 'Ja' : 'Nee',
            oc_contact_mag($kader) ? 'Mag' : 'Mag niet',
            trim((OC_GRONDEN_KORT[$k] ?? '') . ' ' . implode(' ', $extra)),
        ];
    }

    return [
        'kop' => ['Platform', 'Altijd uitleg', 'Dienstverlening en veilig contact', 'Waarom'],
        'rijen' => $rijen,
        'breedtes' => [3.6, 1.9, 2.9, 7.0],
    ];
}
