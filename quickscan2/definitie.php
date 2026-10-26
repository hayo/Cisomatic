<?php

/**
 * De quickscan voor de BIO2 en de Cyberbeveiligingswet, voor de motor: teksten,
 * bestanden en de functies die de motor aanroept. Alleen gegevens, geen code; de
 * voordeur leest dit ook. Wat elke sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

return [
    'titel' => 'Quickscan 2.0: BIO2 en Cyberbeveiligingswet',
    'naam' => 'quickscan',
    'intro' => 'Voor ministeries, diensten en agentschappen. Vul dit in voordat je een project start. Je ziet '
        . 'dan meteen wat de BIO2, de Cyberbeveiligingswet en het cloudbeleid vragen.',
    'uitvoer' => 'advies',
    'download_uitleg' => 'Het document opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerdere keer, of van quickscan 1.0. Je browser leest het zelf, dus '
        . 'er gaat niets naar de server.',
    'voet' => 'Dit advies vervangt het oordeel van de CISO niet.',
    'bronnen' => [
        'BIO2, versie 1.3 (9 januari 2026)',
        'Cyberbeveiligingswet (geldt sinds 15 augustus 2026)',
        'Cyberbeveiligingsregeling sector overheid (Stcrt. 2026, 27679)',
        'Herziening rijksbreed cloudbeleid 2026 (3 juli 2026)',
        'Generieke Baselinetoets BIO2, versie 3.2 (14 oktober 2026), ter vergelijking',
        'AVG, Archiefwet 2026 en AI-verordening',
    ],
    'stappen' => [
        1 => 'Stap 1 · beschrijving door de behoeftesteller',
        2 => 'Stap 2 · weging door de beoordelaar',
    ],
    'route' => 'informatie_soorten',

    // De eerste quickscan kent staatsgeheim als één keuze; die kiest de invuller hier opnieuw.
    'overnemen' => [
        'quickscan' => [
            'naam' => 'quickscan 1.0',
            'overslaan' => ['rubricering'],
        ],
        'businesscase' => ['naam' => 'de business case'],
        // De scan gaat altijd over een clouddienst.
        'soevereiniteit' => [
            'naam' => 'de soevereiniteitsscan',
            'als' => ['sv_buitenlandse_wet' => 'cloud_jurisdictie'],
            'zet' => ['cloud' => 'ja'],
        ],
        // En deze altijd over AI.
        'aiscan' => ['naam' => 'de AI-scan', 'zet' => ['ai' => 'onderdeel']],
    ],

    // Het advies zet de antwoorden zelf al in een hoofdstuk.
    'vaststelling' => 'q2_vaststelling',
    'antwoorden' => false,

    // Op de voordeur en in een dossier. 'vervolg' zegt welke andere formulieren
    // de quickscan voorstelt.
    'fase' => 'ontwerp',
    'normenkader' => 'bio2',
    'vervolg' => 'q2_vervolg',

    'bestanden' => ['toelichting.php', 'vragen.php', 'afleiding.php', 'beslisboom.php', 'advies.php'],
    'secties' => 'q2_secties',
    'valideer' => 'q2_valideer',
    'evalueer' => 'q2_evalueer',
    'document' => 'q2_document',
];
