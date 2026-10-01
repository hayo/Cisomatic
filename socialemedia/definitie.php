<?php

/**
 * De kanalenscan voor sociale media, voor de motor: teksten, bestanden en de
 * functies die de motor aanroept. Alleen gegevens, geen code; de voordeur leest
 * dit ook. Wat elke sleutel doet, staat in ../README.md.
 */

declare(strict_types=1);

return [
    'titel' => 'Kanalenscan sociale media',
    'naam' => 'kanalenscan',
    'intro' => 'Voor ministeries, diensten en agentschappen. Toets één kanaal op sociale media aan het rijksbrede '
        . 'kader, van starten en de jaarlijkse toets tot vertrekken. Je krijgt een comply or explain, of een '
        . 'exitplan, klaar voor de directeur Communicatie.',
    'uitvoer' => 'document',
    'download_uitleg' => 'Het document opent in Word, LibreOffice en Google Docs, met kopjes, lijsten en tabellen.',
    'inladen' => 'Een bestand van een eerdere keer. Je browser leest het zelf, dus er gaat niets naar de server.',
    'voet' => 'Gebaseerd op de rijksbrede platformkaders en de richtlijnen voor toetreding en vertrek. Stand van '
        . 'september 2026. Dit document vervangt het advies van je CPO, CISO en CIO niet.',
    'stappen' => [
        1 => 'Het kanaal',
        2 => 'Stap 1 · doel en doelgroep',
        3 => 'Stap 2 · kanaalstrategie',
        4 => 'Stap 3 · weging',
        5 => 'Toetreding · vooronderzoek',
        6 => 'Stap 4 · comply or explain',
        7 => 'Vertrek · waarom',
        8 => 'Vertrek · impact',
        9 => 'Vertrek · het vertrek',
        10 => 'Vaststellen',
    ],
    'route' => 'sm_situatie',

    'bestanden' => ['kaders.php', 'vragen.php', 'exit.php', 'afleiding.php', 'advies.php'],
    'secties' => 'sm_secties',
    'evalueer' => 'sm_evalueer',
    'document' => 'sm_document',
    'uitkomst' => 'sm_uitkomst',
];
