<?php

/**
 * De check op één A4, om op papier of in Word in te vullen. De motor maakt hem
 * niet uit de secties, zoals bij de andere formulieren, maar neemt dit blad
 * over ('vragenlijst_blad' in de definitie). Zo past alles op één pagina:
 * - het kanaal: platform en accountnaam
 * - wat het kader per platform eist, in een tabel
 * - de voorwaarden, als zinnen waar ja goed is. Niet op het basiskanaal
 * - de uitkomst, met de regel in één zin, en bij uitleg de vier vragen uit de
 *   notitie
 *
 * De tabel komt uit ../socialemedia/kaders.php en afleiding.php. De rekenregel
 * staat hier in woorden. Verandert oc_evalueer(), loop dan ook dit bestand na.
 */

declare(strict_types=1);

/** De volgorde van de platforms: het basiskanaal, wat in beginsel inzetbaar is, en waar uitleg altijd nodig is. */
const OC_KOLOMMEN = ['mastodon', 'instagram', 'youtube', 'linkedin', 'facebook', 'x', 'tiktok', 'anders'];

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

    return ['blad' => [
        ['h1', OC_TITEL],
        ['noot', 'Eén formulier per kanaal. Voldoet het kanaal aan het rijksbrede kader? Zo niet, dan leg je uit '
            . 'waarom jullie het toch gebruiken.'],

        ['vraagblok', [
            'nr' => '1',
            'vraag' => 'Het kanaal',
            'keuzes' => [
                ['los' => true, 'Platform en accountnaam:'],
                ['los' => true, 'Organisatie:' . str_repeat("\u{00A0}", 80) . 'Datum:'],
                ['los' => true, 'Ingevuld door:' . str_repeat("\u{00A0}", 50) . 'Vastgesteld door (directeur Communicatie):'],
            ],
        ]],

        ['vraagblok', [
            'nr' => '2',
            'vraag' => 'Wat het kader per platform eist',
            'tabel' => oc_vragenlijst_platforms($kaders),
        ]],

        ['vraagblok', [
            'nr' => '3',
            'vraag' => 'Voorwaarden',
            'bijzin' => 'kies per regel',
            'noten' => ['Sla dit over bij social.overheid.nl, het basiskanaal. Daar mag alles.'],
            'keuzes' => [
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Geen dienstverlening of veilig contact.'],
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Alles staat ook op een eigen kanaal van de overheid.'],
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Mensen worden gewezen op de risico’s van contact, en '
                    . 'doorverwezen.'],
            ],
        ]],

        ['vraagblok', [
            'nr' => '4',
            'vraag' => 'Voldoet het kanaal?' . $ruim . $hokjes(array_column(OC_UITKOMSTEN, 'label')),
            'noten' => [
                'Het kanaal voldoet op het basiskanaal, of als het platform bij 2 ‘Altijd uitleg’ nee heeft en bij 3 '
                    . 'alles ja is. Voldoet het niet, beantwoord dan a tot en met d.',
            ],
            'tabel' => [
                'kop' => ['Vraag', 'Uitleg'],
                'rijen' => array_map(static fn (string $vraag): array => [$vraag, ''], array_values(OC_UITLEG)),
                'breedtes' => [4.4, 11.0],
                'voor' => ['a', 'b', 'c', 'd'],
                'hoogte' => 1.6,
            ],
        ]],

        ['noot', OC_BESTUURSRAAD],
        ['noot', 'Bron: rijksbrede platformkaders 1.0, bij de notitie ‘Aanwezigheid Rijksoverheid op sociale media’.'],
    ]];
}

/**
 * De platforms met wat het kader eist: altijd uitleg of niet, of dienstverlening
 * en veilig contact mogen, en waarom. Ter informatie, zonder hokje: welk
 * platform het is, staat bij het kanaal.
 *
 * @param array<string,array<string,mixed>> $kaders
 * @return array<string,mixed>
 */
function oc_vragenlijst_platforms(array $kaders): array
{
    $rijen = [];
    foreach (OC_KOLOMMEN as $k) {
        $kader = $kaders[$k] + ['explain' => false, 'toestel' => false];
        $naam = $k === 'anders' ? 'Ander platform' : $kader['naam'];
        $extra = array_filter([
            $kader['toestel'] ? 'Nooit op een toestel van het Rijk.' : '',
            oc_gedrag_betaald($kader) ? 'Gedrag beïnvloeden alleen met advertenties.' : '',
        ]);
        $rijen[] = [
            $naam,
            $kader['explain'] ? 'Ja' : 'Nee',
            oc_basis($kader) ? 'Mag' : 'Mag niet',
            oc_basis($kader) ? 'Basiskanaal van de overheid: alles mag.' : trim((OC_GRONDEN_KORT[$k] ?? '') . ' ' . implode(' ', $extra)),
        ];
    }

    return [
        'kop' => ['Platform', 'Altijd uitleg', 'Dienstverlening en veilig contact', 'Waarom'],
        'rijen' => $rijen,
        'breedtes' => [3.6, 1.9, 2.9, 7.0],
        'voor' => '',
    ];
}
