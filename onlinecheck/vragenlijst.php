<?php

/**
 * De check op één A4, om op papier of in Word in te vullen. De motor maakt hem
 * niet uit de secties, zoals bij de andere formulieren, maar neemt dit blad
 * over ('vragenlijst_blad' in de definitie). Zo past alles op één pagina:
 * - het kanaal: platform en accountnaam
 * - de voorwaarden, als zinnen waar ja goed is, met als eerste het platform.
 *   Niet op het basiskanaal
 * - de uitkomst, met de regel in één zin, en bij uitleg de vier vragen uit de
 *   notitie
 *
 * De platforms komen uit ../socialemedia/kaders.php. De rekenregel
 * staat hier in woorden. Verandert oc_evalueer(), loop dan ook dit bestand na.
 */

declare(strict_types=1);

/**
 * @param callable(string):string $nr het nummer van een vraag; niet nodig op één blad
 * @return array<string,mixed>
 */
function oc_vragenlijst(callable $nr): array
{
    $kaders = sm_kaders();
    // De platforms die in beginsel inzetbaar zijn, behalve het basiskanaal: daar gelden de voorwaarden niet.
    $inzetbaar = array_values(array_map(static fn (array $k): string => $k['naam'], array_filter($kaders,
        static fn (array $k): bool => empty($k['explain']) && !oc_basis($k))));
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
            'vraag' => 'Voorwaarden',
            'bijzin' => 'kies per regel',
            'noten' => ['Sla dit over bij social.overheid.nl, het basiskanaal. Daar mag alles.'],
            'keuzes' => [
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Het platform is ' . implode(', ', array_slice($inzetbaar, 0, -1)) . ' of ' . end($inzetbaar) . '. '
                    . 'Facebook, X, TikTok en andere platforms voldoen nooit.'],
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Geen dienstverlening of veilig contact.'],
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Alles staat ook op een eigen kanaal van de overheid.'],
                ['los' => true, $hokjes(['Ja', 'Nee']) . $ruim . 'Mensen worden gewezen op de risico’s van contact, en '
                    . 'doorverwezen.'],
            ],
        ]],

        ['vraagblok', [
            'nr' => '3',
            'vraag' => 'Voldoet het kanaal?' . $ruim . $hokjes(array_column(OC_UITKOMSTEN, 'label')),
            'noten' => [
                'Het kanaal voldoet op social.overheid.nl, of als bij 2 alles ja is. Voldoet het niet, beantwoord dan a '
                    . 'tot en met d.',
            ],
            'tabel' => [
                'kop' => ['Vraag', 'Uitleg'],
                'rijen' => array_map(static fn (string $vraag): array => [$vraag, ''], array_values(OC_UITLEG)),
                'breedtes' => [4.4, 11.0],
                'voor' => ['a', 'b', 'c', 'd'],
                'hoogte' => 2.7,
            ],
        ]],

        ['noot', OC_BESTUURSRAAD],
        ['noot', 'Bron: rijksbrede platformkaders 1.0, bij de notitie ‘Aanwezigheid Rijksoverheid op sociale media’.'],
    ]];
}
