<?php

/**
 * De soevereiniteitsscan voor de motor: teksten, bestanden en de functies die
 * de motor aanroept. Alleen gegevens, geen code; de voordeur leest dit ook.
 * Wat elke sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

return [
    'titel' => 'Soevereiniteitsscan: wie heeft je cloud in handen?',
    'naam' => 'soevereiniteitsscan',
    'intro' => 'Hoeveel grip heeft Europa op een clouddienst? Beantwoord een paar eenvoudige vragen, en je krijgt '
        . 'een label van A tot E. De scan volgt het Cloud Sovereignty Framework van de Europese Commissie.',
    'uitvoer' => 'rapport',
    'download_uitleg' => 'Het rapport opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerdere scan, of van quickscan 2.0. Je browser leest het zelf, dus er gaat '
        . 'niets naar de server.',
    'voet' => 'Gebaseerd op het Cloud Sovereignty Framework, versie 1.2.1 (oktober 2025), van de Europese Commissie. '
        . 'De vragen, het label en wat per soort informatie nodig is, zijn een eigen vereenvoudiging. Deze scan '
        . 'vervangt de beoordeling in een aanbesteding niet.',
    'stappen' => [
        1 => 'Deel 1 · de leverancier',
        2 => 'Deel 2 · je data',
        3 => 'Deel 3 · beheer en techniek',
        4 => 'Deel 4 · beveiliging en milieu',
        5 => 'Deel 5 · de uitkomst',
    ],
    'route' => '',
    'hoofdstuk_per_pagina' => true,

    'overnemen' => [
        'quickscan2' => [
            'naam' => 'quickscan 2.0',
            'als' => ['cloud_jurisdictie' => 'sv_buitenlandse_wet'],
        ],
        'quickscan' => ['naam' => 'de quickscan'],
        'businesscase' => ['naam' => 'de business case'],
        'dpia' => ['naam' => 'de DPIA'],
        'iama' => ['naam' => 'de IAMA'],
    ],

    'bestanden' => ['vragen.php', 'afleiding.php', 'rapport.php'],
    'secties' => 'sv_secties',
    'evalueer' => 'sv_evalueer',
    'document' => 'sv_rapport',
];
