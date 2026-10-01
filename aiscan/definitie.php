<?php

/**
 * De AI scan voor de motor: teksten, bestanden en de functies die de motor
 * aanroept. Alleen gegevens, geen code; de voordeur leest dit ook. Wat elke
 * sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

return [
    'titel' => 'AI scan: wat vraagt de AI verordening?',
    'naam' => 'AI scan',
    'intro' => 'Geldt de AI verordening voor jullie toepassing? En wat moeten jullie dan doen? Deze scan zoekt het '
        . 'uit, met de vragen van de beslishulp van BZK. Voor generatieve AI toetst hij ook het standpunt van de '
        . 'overheid.',
    'uitvoer' => 'rapport',
    'download_uitleg' => 'Het rapport opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerdere scan, of van quickscan 2.0, de DPIA of de IAMA. Je browser leest het '
        . 'zelf, dus er gaat niets naar de server.',
    'voet' => 'Gebaseerd op de Beslishulp AI verordening van het ministerie van BZK (versie van 14 september 2026), '
        . 'met de datums die sinds juli 2026 in de AI verordening staan. De vragen over generatieve AI komen uit het '
        . 'standpunt en de handreiking van BZK (januari 2025). Deze scan vervangt het oordeel van een jurist niet.',
    'stappen' => [
        1 => 'Deel 1 · de toepassing',
        2 => 'Deel 2 · de risicogroep',
        3 => 'Deel 3 · generatieve AI',
        4 => 'Deel 4 · de uitkomst',
    ],
    'route' => '',
    'hoofdstuk_per_pagina' => true,

    // De eerste quickscan deelt de aankruislijst ai_toepassing, en de DPIA en
    // quickscan 2.0 de omschrijving ai_omschrijving.
    'overnemen' => [
        'quickscan2' => ['naam' => 'quickscan 2.0'],
        'quickscan' => ['naam' => 'de quickscan'],
        'dpia' => ['naam' => 'de DPIA'],
        'iama' => ['naam' => 'de IAMA', 'als' => ['ia_doel' => 'ai_omschrijving']],
        'businesscase' => ['naam' => 'de business case', 'als' => ['bc_doel' => 'ai_omschrijving']],
        'soevereiniteit' => ['naam' => 'de soevereiniteitsscan'],
    ],

    'bestanden' => ['vragen.php', 'afleiding.php', 'rapport.php'],
    'secties' => 'ai_secties',
    'uitkomst' => 'ai_uitkomst',
    'evalueer' => 'ai_evalueer',
    'document' => 'ai_rapport',
];
