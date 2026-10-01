<?php

/**
 * Het IAMA voor de motor: teksten, bestanden en de functies die de motor
 * aanroept. Alleen gegevens, geen code; de voordeur leest dit ook. Wat elke
 * sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

return [
    'titel' => 'IAMA: algoritmes en grondrechten',
    'naam' => 'IAMA',
    'intro' => 'Bespreek met een team wat een algoritme doet met grondrechten en andere belangen. Je krijgt '
        . 'meteen een document in de opbouw van het Impact Assessment Mensenrechten en Algoritmes.',
    'uitvoer' => 'document',
    'download_uitleg' => 'Het document opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerdere sessie, of van de quickscan of de DPIA. Je browser leest het zelf, '
        . 'dus er gaat niets naar de server.',
    'voet' => 'Gebaseerd op het Impact Assessment Mensenrechten en Algoritmes, versie 2 (2026), van de Universiteit '
        . 'Utrecht en het ministerie van BZK. Dit document vervangt het oordeel van een jurist niet.',
    'stappen' => [
        1 => 'Deel 1 · waarom',
        2 => 'Deel 2 · wat',
        3 => 'Deel 3 · hoe',
        4 => 'Deel 4 · grondrechten',
        5 => 'Deel 5 · afsluiting',
    ],
    'route' => 'sessie',
    'hoofdstuk_per_pagina' => true,

    // De versie hoort bij het document waar het bestand vandaan komt.
    'overnemen' => [
        'quickscan' => [
            'naam' => 'de quickscan',
            'zet' => ['ia_reden' => 'quickscan'],
        ],
        'quickscan2' => [
            'naam' => 'de quickscan voor de BIO2',
            'zet' => ['ia_reden' => 'quickscan'],
        ],
        'dpia' => [
            'naam' => 'de DPIA',
            'overslaan' => ['versie', 'versie_toelichting'],
        ],
        'businesscase' => [
            'naam' => 'de business case',
            'overslaan' => ['versie', 'versie_toelichting'],
            'als' => ['bc_probleem' => 'ia_aanleiding', 'bc_doel' => 'ia_doel'],
        ],
        'aiscan' => ['naam' => 'de AI scan', 'als' => ['ai_omschrijving' => 'ia_doel']],
    ],

    'bestanden' => ['vragen.php', 'afleiding.php', 'rapport.php'],
    'secties' => 'ia_secties',
    'evalueer' => 'ia_evalueer',
    'document' => 'ia_rapport',
];
