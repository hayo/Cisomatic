<?php

/**
 * De fit/gap voor de BIO 1, voor de motor: teksten, bestanden en de functies
 * die de motor aanroept. Alleen gegevens, geen code; de voordeur leest dit ook.
 * Wat elke sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

// De verklaring is van de opdrachtnemer, niet van wie het andere formulier invulde.
$eigen = ['aanvrager', 'email'];

return [
    'titel' => 'Fit/gap 1.0: BIO',
    'naam' => 'fit/gap',
    'intro' => 'Voor de leverancier van een dienst. Laat per norm uit de BIO zien of de dienst voldoet, hoe dat '
        . 'geregeld is, en wat er nog moet gebeuren. Het document is de verklaring aan de opdrachtgever.',
    'uitvoer' => 'document',
    'download_uitleg' => 'Het document opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerdere keer, of van fit/gap 2.0. Je browser leest het zelf, dus er '
        . 'gaat niets naar de server.',
    'voet' => 'De opdrachtgever beoordeelt de verklaring.',
    'bronnen' => [
        'Fit/gap analyse BIO, versie 1.05',
        'BIO, versie 1',
        'ISO 27017, ISO 27018 en ISO/IEC 27701:2019',
    ],
    'stappen' => [
        1 => 'Deel 1 · de dienst',
        2 => 'Deel 2 · de normen uit de BIO',
        3 => 'Deel 3 · cloud en privacy',
        4 => 'Deel 4 · de verklaring',
    ],
    'route' => 'fg_iso27001',
    'hoofdstuk_per_pagina' => true,

    'overnemen' => [
        'fitgap2' => ['naam' => 'fit/gap 2.0'],
        'quickscan' => ['naam' => 'quickscan 1.0', 'overslaan' => $eigen],
        'quickscan2' => ['naam' => 'quickscan 2.0', 'overslaan' => $eigen],
        'businesscase' => ['naam' => 'de business case', 'overslaan' => $eigen],
    ],

    // De verklaring heeft al alle normen en een eigen ondertekening.
    'vaststelling' => false,
    'antwoorden' => false,

    // Op de voordeur. De leverancier vult dit in, dus het hoort niet in een dossier.
    'fase' => 'toetsen',
    'normenkader' => 'bio',
    'opvolger' => 'fitgap2',
    'dossier' => false,

    'bestanden' => ['verklaring.php', 'cloudprivacy.php', 'bio.php'],
    'secties' => 'fg1_secties',
    'valideer' => 'fg_valideer',
    'evalueer' => 'fg1_evalueer',
    'document' => 'fg1_document',
];
