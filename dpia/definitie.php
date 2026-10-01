<?php

/**
 * De DPIA voor de motor: teksten, bestanden en de functies die de motor
 * aanroept. Alleen gegevens, geen code; de voordeur leest dit ook. Wat elke
 * sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

return [
    'titel' => 'DPIA: privacyrisico’s in kaart',
    'naam' => 'DPIA',
    'intro' => 'Beschrijf een verwerking van persoonsgegevens en beoordeel de risico’s. Je krijgt meteen een '
        . 'document in de opbouw van het Model DPIA Rijksdienst.',
    'uitvoer' => 'document',
    'download_uitleg' => 'Het document opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en '
        . 'tabellen. Het schema van de gegevensstromen staat alleen op deze pagina. In het document staat de tabel.',
    'inladen' => 'Een bestand van de quickscan, of van een eerdere keer met deze DPIA. Je browser leest het '
        . 'zelf, dus er gaat niets naar de server.',
    'voet' => 'Gebaseerd op het Model DPIA Rijksdienst. Dit document vervangt het oordeel van de functionaris '
        . 'gegevensbescherming niet.',
    'stappen' => [
        1 => 'Stap 1 · beschrijving door de opsteller',
        2 => 'Stap 2 · samen met de privacyfunctionaris',
    ],
    'route' => 'rol',
    'hoofdstuk_per_pagina' => true,

    // De quickscan kent ook een rol, maar die betekent daar iets anders.
    'overnemen' => [
        'quickscan' => [
            'naam' => 'de quickscan',
            'overslaan' => ['rol'],
            'zet' => ['aanleiding' => 'quickscan'],
        ],
        'quickscan2' => [
            'naam' => 'de quickscan voor de BIO2',
            'overslaan' => ['rol'],
            'zet' => ['aanleiding' => 'quickscan'],
        ],
        'iama' => [
            'naam' => 'de IAMA',
            'overslaan' => ['versie', 'versie_toelichting'],
            'zet' => ['aanleiding' => 'anders', 'aanleiding_toelichting' => 'Uit de IAMA bleek dat een DPIA nodig is.'],
        ],
        'businesscase' => [
            'naam' => 'de business case',
            'overslaan' => ['versie', 'versie_toelichting'],
        ],
        'aiscan' => ['naam' => 'de AI scan', 'zet' => ['ai' => 'onderdeel']],
    ],

    'bestanden' => ['risicos.php', 'vragen.php', 'afleiding.php', 'rapport.php'],
    'secties' => 'dp_secties',
    'uitkomst' => 'dp_uitkomst',
    'evalueer' => 'dp_evalueer',
    'document' => 'dp_rapport',
];
