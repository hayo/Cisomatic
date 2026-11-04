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

/** De kolommen van het kanaal en van de uitleg: label en veld. Zo staan ze onder elkaar. */
const OC_KOLOMBREEDTES = [4.4, 11.0];

/** De breedte van een veld in de tweede kolom, binnen de rand van de cel. */
const OC_VELDBREEDTE = 10.55;

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
            // Dezelfde kolommen als de uitleg, zodat labels en velden onder elkaar staan.
            'tabel' => [
                'kop' => [],
                'rijen' => [
                    ['Platform en accountnaam', $veld('projectnaam', OC_VELDBREEDTE)],
                    ['Organisatie', $veld('organisatieonderdeel', OC_VELDBREEDTE)],
                    ['Datum', $veld('datum', OC_VELDBREEDTE)],
                    ['Ingevuld door', $veld('aanvrager', OC_VELDBREEDTE)],
                    ['Vastgesteld door', $veld('sm_directeur', OC_VELDBREEDTE)],
                ],
                'breedtes' => OC_KOLOMBREEDTES,
                'voor' => '',
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
                    OC_VELDBREEDTE, 3.4)], array_values(OC_UITLEG), array_keys(OC_UITLEG)),
                'breedtes' => OC_KOLOMBREEDTES,
                'voor' => ['a', 'b', 'c', 'd'],
                'hoogte' => 3.6,
            ],
        ]],

        ['noot', OC_BESTUURSRAAD],
    ]];
}
