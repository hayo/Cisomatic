<?php

/**
 * Wat de vragenlijst als .odt extra nodig heeft: wanneer een vraag geldt die
 * afhangt van wat het formulier uitrekent (dp_uitkomst() in afleiding.php),
 * en als bijlage de matrix waarmee je de omvang van een risico bepaalt.
 *
 * Verandert dp_uitkomst(), loop dan ook dit bestand na.
 */

declare(strict_types=1);

/**
 * @param callable(string):string $nr het nummer van een vraag, als "vraag 7"
 * @return array<string,mixed>
 */
function dp_vragenlijst(callable $nr): array
{
    $niveaus = static fn (array $waarden): string => implode(' of ', array_map(
        static fn (string $n): string => '‘' . cm_niveau_label($n) . '’',
        $waarden
    ));

    $uitkomst = static function (string $naam, array $waarden) use ($nr, $niveaus): string {
        if (str_starts_with($naam, 'omvang:')) {
            return 'de omvang van dit risico ' . $niveaus($waarden) . ' is (kans en impact samen, zie bijlage A)';
        }
        if (str_starts_with($naam, 'extra:')) {
            return $waarden === ['ja']
                ? 'er een nieuwe maatregel komt: ‘Komt er’ bij een maatregel, of iets bij de andere maatregelen die '
                    . 'komen'
                : 'er geen nieuwe maatregel komt';
        }

        return match ($naam) {
            'groepen' => $waarden === ['meerdere']
                ? 'je bij ' . $nr('betrokkenen') . ' meer dan één groep betrokkenen noemde'
                : 'je bij ' . $nr('betrokkenen') . ' één groep betrokkenen noemde',
            'verwerker' => ($waarden === ['ja'] ? '' : 'geen ') . 'een van de partijen bij ' . $nr('partijen')
                . ' een verwerker of subverwerker is',
            'doorgifte' => 'gegevens naar een land buiten de EER gaan: zie de plek bij ' . $nr('cloud_locatie')
                . ' en ' . $nr('pg_subverwerkers_locatie'),
            'toestemming' => 'de grondslag bij ' . $nr('pg_grondslag') . ', of de uitzondering bij '
                . $nr('uitzondering') . ', toestemming is',
            default => '',
        };
    };

    $kolommen = array_keys(CM_NIVEAUS);

    return [
        'uitkomsten' => $uitkomst,
        'bijlagen' => [
            ['h2', 'Bijlage A: de omvang van een risico'],
            ['alinea', 'De omvang volgt uit de kans en de impact. Zoek de kans op in de eerste kolom, en de impact in '
                . 'de bovenste rij.'],
            ['tabel', [
                'kop' => ['Kans \\ impact', ...array_values(CM_NIVEAUS)],
                'rijen' => array_map(static fn (string $kans): array => [
                    CM_NIVEAUS[$kans],
                    ...array_map(static fn (string $impact): string => cm_niveau_label(dp_omvang($kans, $impact)), $kolommen),
                ], $kolommen),
                'breedtes' => [3.1, 2.7, 2.7, 2.7, 2.7, 2.7],
                'rijkop' => true,
            ]],
        ],
    ];
}
