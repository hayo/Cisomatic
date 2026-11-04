<?php

/**
 * De check op één A4, om op papier of in Word in te vullen. De motor maakt hem
 * niet uit de secties, zoals bij de andere formulieren, maar neemt dit blad
 * over ('vragenlijst_blad' in de definitie). Zo past alles op één pagina:
 * - het kanaal: platform en accountnaam
 * - voldoet het kanaal: ja of nee
 * - de vier vragen van de uitleg uit de notitie
 * Alles is in te vullen in LibreOffice: de velden hebben de sleutels van het
 * formulier als naam.
 */

declare(strict_types=1);

/**
 * @param callable(string):string $nr het nummer van een vraag; niet nodig op één blad
 * @return array<string,mixed>
 */
function oc_vragenlijst(callable $nr): array
{
    $ruim = "\u{00A0}\u{00A0}\u{00A0}\u{00A0}";
    $veld = static fn (string $naam, float $breedte): string => cm_odt_veld('tekst', $naam, $breedte);
    $keuze = static fn (string $waarde, string $label): string => cm_odt_veld('keuze', 'oc_voldoet', 1.6, 0.5,
        ['label' => $label, 'waarde' => $waarde]);

    return ['blad' => [
        ['h1', OC_TITEL],
        ['noot', 'Eén formulier per kanaal. Voldoet het kanaal aan het rijksbrede kader? Zo niet, dan leg je uit '
            . 'waarom jullie het toch gebruiken.'],

        ['vraagblok', [
            'nr' => '1',
            'vraag' => 'Het kanaal',
            'keuzes' => [
                ['los' => true, 'Platform en accountnaam: ' . $veld('projectnaam', 11.8)],
                ['los' => true, 'Organisatie: ' . $veld('organisatieonderdeel', 8.4) . $ruim . 'Datum: ' . $veld('datum', 4.2)],
                ['los' => true, 'Ingevuld door: ' . $veld('aanvrager', 5.2) . $ruim . 'Vastgesteld door: '
                    . $veld('sm_directeur', 5.2)],
            ],
        ]],

        ['vraagblok', [
            'nr' => '2',
            'vraag' => 'Voldoet het kanaal aan de ' . cm_odt_link('platformkaders', OC_KADERS_URL) . '?' . $ruim
                . $keuze('ja', 'Ja') . $keuze('nee', 'Nee'),
            'noten' => ['Voldoet het niet, beantwoord dan a tot en met d.'],
            'tabel' => [
                'kop' => ['Vraag', 'Uitleg'],
                'rijen' => array_map(static fn (string $vraag, string $key): array => [$vraag, cm_odt_veld('vak', $key,
                    10.7, 3.4)], array_values(OC_UITLEG), array_keys(OC_UITLEG)),
                'breedtes' => [4.4, 11.0],
                'voor' => ['a', 'b', 'c', 'd'],
                'hoogte' => 3.6,
            ],
        ]],

        ['noot', OC_BESTUURSRAAD],
        ['noot', 'Bron: rijksbrede platformkaders 1.0, bij de notitie ‘Aanwezigheid Rijksoverheid op sociale media’.'],
    ]];
}
