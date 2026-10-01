<?php

/**
 * De business case en intaketoets voor de motor: teksten, bestanden en de functies die de
 * motor aanroept. Alleen gegevens, geen code; de voordeur leest dit ook. Wat
 * elke sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

// De versie hoort bij het document waar het bestand vandaan komt.
$eigen = ['versie', 'versie_toelichting'];

return [
    'titel' => 'Business case en intaketoets',
    'naam' => 'business case',
    'intro' => 'Toets een project voordat het begint. Is het nodig, kan het zonder nieuw systeem, is er al iets, is het '
        . 'jullie taak, en kunnen jullie het dragen? Je krijgt een advies aan het MT, met het risico per toets.',
    'uitvoer' => 'document',
    'download_uitleg' => 'Het document opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerdere keer met deze business case. Je browser leest het zelf, dus er gaat '
        . 'niets naar de server.',
    'voet' => 'Voor projecten van het Rijk. De Europese drempel in dit formulier geldt in 2026 en 2027. Dit document '
        . 'vervangt het advies van je inkoopadviseur niet.',
    'stappen' => [
        1 => 'Toets 1 · nodig',
        2 => 'Toets 2 · zonder nieuw systeem',
        3 => 'Toets 3 · wat er al is',
        4 => 'Toets 4 · jullie taak',
        5 => 'Toets 5 · dragen',
        6 => 'Het voorstel',
    ],
    'route' => '',
    'hoofdstuk_per_pagina' => true,

    'overnemen' => [
        'quickscan2' => ['naam' => 'de quickscan voor de BIO2', 'overslaan' => $eigen],
        'quickscan' => ['naam' => 'de quickscan', 'overslaan' => $eigen],
        'dpia' => ['naam' => 'de DPIA', 'overslaan' => $eigen],
        'iama' => [
            'naam' => 'de IAMA',
            'overslaan' => $eigen,
            'als' => ['ia_aanleiding' => 'bc_probleem', 'ia_doel' => 'bc_doel'],
        ],
    ],

    'bestanden' => ['vragen.php', 'afleiding.php', 'rapport.php'],
    'secties' => 'bc_secties',
    'evalueer' => 'bc_evalueer',
    'document' => 'bc_rapport',
];
