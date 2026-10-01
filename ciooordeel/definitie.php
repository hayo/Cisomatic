<?php

/**
 * Het CIO oordeel voor de motor: teksten, bestanden en de functies die de
 * motor aanroept. Alleen gegevens, geen code; de voordeur leest dit ook. Wat
 * elke sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

// Het oordeel is van de adviseur, niet van wie het andere formulier invulde.
$eigen = ['aanvrager', 'email', 'versie', 'versie_toelichting'];

return [
    'titel' => 'CIO oordeel: staat het project er goed voor?',
    'naam' => 'CIO oordeel',
    'intro' => 'Voor de adviseur van het CIO office. Beoordeel een project met een grote ICT component op elf '
        . 'aandachtsgebieden, en krijg een voorstel voor het oordeel. Het rapport volgt de opbouw van een CIO oordeel.',
    'uitvoer' => 'rapport',
    'download_uitleg' => 'Het rapport opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerder oordeel, of van de business case, quickscan 2.0, de DPIA of een andere '
        . 'scan van hetzelfde project. Je browser leest het zelf, dus er gaat niets naar de server.',
    'voet' => 'Gebaseerd op het Besluit CIO-stelsel Rijksdienst 2026 en het toetskader projecten 2026 van het '
        . 'Adviescollege ICT-toetsing. Informatiebeveiliging en privacy en duurzame toegankelijkheid zijn een eigen '
        . 'uitwerking van het Kwaliteitskader CIO-oordelen. Het voorstel voor het oordeel is een hulpmiddel: het '
        . 'oordeel zelf is van de CIO.',
    'stappen' => [
        1 => 'Deel 1 · het onderzoek',
        2 => 'Deel 2 · doel en sturing',
        3 => 'Deel 3 · processen en techniek',
        4 => 'Deel 4 · bouwen, inkopen en beheren',
        5 => 'Deel 5 · informatie',
        6 => 'Deel 6 · het oordeel',
    ],
    'route' => '',
    'hoofdstuk_per_pagina' => true,

    'overnemen' => [
        'businesscase' => ['naam' => 'de business case', 'overslaan' => $eigen],
        'quickscan2' => ['naam' => 'quickscan 2.0', 'overslaan' => $eigen],
        'dpia' => ['naam' => 'de DPIA', 'overslaan' => $eigen],
        'soevereiniteit' => ['naam' => 'de soevereiniteitsscan', 'overslaan' => $eigen],
        'aiscan' => ['naam' => 'de AI scan', 'overslaan' => $eigen],
        'iama' => ['naam' => 'de IAMA', 'overslaan' => $eigen],
    ],

    'bestanden' => ['vragen.php', 'afleiding.php', 'rapport.php'],
    'secties' => 'co_secties',
    'evalueer' => 'co_evalueer',
    'document' => 'co_rapport',
    'uitkomst' => 'co_uitkomst',
];
