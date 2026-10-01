<?php

/**
 * De quickscan voor de motor: teksten, bestanden en de functies die de motor
 * aanroept. Alleen gegevens, geen code; de voordeur leest dit ook. Wat elke
 * sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

return [
    'titel' => 'IB & Privacy Quickscan',
    'naam' => 'quickscan',
    'intro' => 'Vul dit in voordat je een project start. Je ziet dan meteen welke eisen er gelden en welke '
        . 'aanvullende analyses nodig zijn.',
    'uitvoer' => 'advies',
    'download_uitleg' => 'Het document opent in Word, LibreOffice en Google Docs, met kopjes en lijsten.',
    'inladen' => 'Heb je bij een vorige scan de antwoorden gedownload? Laad dat bestand dan hier in. Dat '
        . 'gebeurt in je eigen browser, dus er gaat niets naar de server.',
    'voet' => 'Gebaseerd op de IB & Privacy Quickscan. Dit advies vervangt het oordeel van de beoordelaar niet.',
    'stappen' => [
        1 => 'Stap 1 · beschrijving door de behoeftesteller',
        2 => 'Stap 2 · weging en vaststelling door de beoordelaar',
    ],
    'route' => 'te_beschermen_informatie',

    'bestanden' => ['toelichting.php', 'vragen.php', 'afleiding.php', 'beslisboom.php', 'advies.php'],
    'secties' => 'qs_secties',
    'valideer' => 'qs_valideer_bijstelling',
    'evalueer' => 'qs_evalueer',
    'document' => 'qs_document',
];
