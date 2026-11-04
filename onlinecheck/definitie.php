<?php

/**
 * De Online communicatie check voor de motor: teksten, bestanden en de
 * functies die de motor aanroept. Alleen gegevens, geen code; de voordeur leest
 * dit ook. Wat elke sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

return [
    'titel' => 'Online communicatie check',
    'naam' => 'online communicatie check',
    'intro' => 'Voor de adviseur sociale media. Toets snel of één kanaal past binnen het rijksbrede kader. Past het, '
        . 'dan voldoet het kanaal. Past het niet, dan leg je uit waarom jullie het toch gebruiken.',
    'uitvoer' => 'document',
    'download_uitleg' => 'Het document opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerdere check, of van de kanalenscan. Je browser leest het zelf, dus er gaat '
        . 'niets naar de server.',
    'voet' => 'Stand van november 2026. De check is een vereenvoudiging van de kanalenscan. Hij vervangt het advies '
        . 'van je CPO, CISO en CIO niet.',
    'bronnen' => [
        'Rijksbrede platformkaders, versie 1.0, bij de notitie ‘Aanwezigheid Rijksoverheid op sociale media’ '
            . '(30 oktober 2026)',
    ],
    'stappen' => [
        1 => 'Stap 1 · het kanaal',
        2 => 'Stap 2 · de doelen',
        3 => 'Stap 3 · de voorwaarden',
        4 => 'Stap 4 · voldoet het?',
    ],
    'route' => '',

    'overnemen' => [
        'socialemedia' => ['naam' => 'de kanalenscan'],
    ],

    'vaststelling' => [['Ingevuld door', 'aanvrager'], ['Vastgesteld door', 'sm_directeur']],

    // Op de voordeur: de fase waarin je dit invult.
    'fase' => 'ontwerp',

    'bestanden' => ['../socialemedia/kaders.php', 'afleiding.php', 'vragen.php', 'document.php', 'vragenlijst.php'],
    'vragenlijst' => 'oc_vragenlijst',
    'vragenlijst_blad' => true,
    'secties' => 'oc_secties',
    'evalueer' => 'oc_evalueer',
    'document' => 'oc_document',
    'uitkomst' => 'oc_uitkomst',
];
