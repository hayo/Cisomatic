<?php

/**
 * De fit/gap voor de BIO2 en de Cyberbeveiligingswet, voor de motor: teksten,
 * bestanden en de functies die de motor aanroept. Alleen gegevens, geen code;
 * de voordeur leest dit ook. Wat elke sleutel doet, staat in ../README.md.
 *
 * De vragen, de uitkomst, het document en regels.js deelt dit formulier met
 * de fit/gap voor de BIO 1, in ../fitgap.
 */

declare(strict_types=1);

// De verklaring is van de opdrachtnemer, niet van wie het andere formulier invulde.
$eigen = ['aanvrager', 'email'];

return [
    'titel' => 'Fit/gap 2.0: BIO2 en Cyberbeveiligingswet',
    'naam' => 'fit/gap',
    'intro' => 'Voor de leverancier van een dienst. Laat per maatregel uit de BIO2 zien of de dienst voldoet, met de '
        . 'eisen aan de keten uit de Cyberbeveiligingswet en het cloudbeleid. Het document is de verklaring aan de '
        . 'opdrachtgever.',
    'uitvoer' => 'document',
    'download_uitleg' => 'Het document opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerdere keer, of van fit/gap 1.0. Je browser leest het zelf, dus er '
        . 'gaat niets naar de server.',
    'voet' => 'De omschrijvingen van de maatregelen uit ISO 27002 zijn een eigen samenvatting. De opdrachtgever '
        . 'beoordeelt de verklaring.',
    'bronnen' => [
        'Fit/gap analyse BIO, versie 1.05',
        'BIO2, versie 1.3 (9 januari 2026)',
        'Cyberbeveiligingswet en Cyberbeveiligingsregeling sector overheid (Stcrt. 2026, 27679)',
        'Herziening rijksbreed cloudbeleid 2026 (3 juli 2026)',
        'Generieke Baselinetoets BIO2, versie 3.2 (14 oktober 2026), voor de koppeling van de zorgplicht',
        'ISO 27017, ISO 27018 en ISO/IEC 27701:2019',
    ],
    'stappen' => [
        1 => 'Deel 1 · de dienst',
        2 => 'Deel 2 · de maatregelen uit ISO 27002',
        3 => 'Deel 3 · overheid en keten',
        4 => 'Deel 4 · cloud en privacy',
        5 => 'Deel 5 · de verklaring',
    ],
    'route' => 'fg_iso27001',
    'hoofdstuk_per_pagina' => true,

    'overnemen' => [
        'fitgap' => ['naam' => 'fit/gap 1.0'],
        'quickscan2' => [
            'naam' => 'quickscan 2.0',
            'overslaan' => $eigen,
            'als' => ['leverancier_cbw' => 'fg_cbw_zelf'],
        ],
        'quickscan' => ['naam' => 'quickscan 1.0', 'overslaan' => $eigen],
        'businesscase' => ['naam' => 'de business case', 'overslaan' => $eigen],
    ],

    // De verklaring heeft al alle normen en een eigen ondertekening.
    'vaststelling' => false,
    'antwoorden' => false,

    // Op de voordeur. De leverancier vult dit in, dus het hoort niet in een dossier.
    'fase' => 'toetsen',
    'normenkader' => 'bio2',
    'dossier' => false,

    'bestanden' => ['../fitgap/verklaring.php', '../fitgap/cloudprivacy.php', 'bio2.php'],
    'regels' => 'fitgap/regels.js',
    'secties' => 'fg2_secties',
    'valideer' => 'fg_valideer',
    'evalueer' => 'fg2_evalueer',
    'document' => 'fg2_document',
];
