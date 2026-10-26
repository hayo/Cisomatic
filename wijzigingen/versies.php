<?php

/**
 * De versies van de Cisomatic, de nieuwste bovenaan. De bovenste is de versie
 * die nu draait; de voettekst, de pagina met wijzigingen en de verantwoording
 * onder elk document lezen hem hier. Hoe de nummers werken, staat in
 * ../README.md, onder Versies.
 *
 * Alleen gegevens, geen code. Per versie: het nummer, de datum als JJJJ-MM-DD,
 * en in een paar korte zinnen wat er veranderd is.
 */

declare(strict_types=1);

return [
    [
        'versie' => '1.2.5',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'De teksten in alle formulieren zijn nagelezen en waar nodig herschreven.',
            'Woorden als AI-scan, AI-verordening en CIO-oordeel zijn nu goed gespeld, met een koppelteken.',
        ],
    ],
    [
        'versie' => '1.2.4',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'Quickscan 2.0 vraagt bij elk nieuw systeem om een risicoafweging. Dat staat in de BIO2, bij maatregel '
                . '5.08.01.',
            'Het advies zegt nu wanneer de quickscan om een volledige risicoanalyse vraagt. Dat is een keuze van de '
                . 'quickscan, geen eis uit de BIO2.',
            'Kan de organisatie bijna niet overstappen naar een andere leverancier? Dan vraagt de quickscan niet meer om '
                . 'een volledige risicoanalyse. Wel om een risicoafweging vóór het contract (BIO2 5.20.06).',
        ],
    ],
    [
        'versie' => '1.2.3',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'Quickscan 2.0 is gecontroleerd met de tekst van de Cyberbeveiligingsregeling sector overheid.',
            'Bij de meldplicht noemt het advies nu ook gerubriceerde informatie van de NAVO en de EU.',
            'Het advies zegt nu dat gepland onderhoud geen significant incident is.',
        ],
    ],
    [
        'versie' => '1.2.2',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'In quickscan 2.0 en fit/gap 2.0 stonden een paar verkeerde nummers van BIO2-maatregelen. Die zijn '
                . 'verbeterd. Een antwoordbestand van fit/gap 2.0 dat je eerder hebt opgeslagen, kun je gewoon weer '
                . 'inladen.',
            'De meldplicht volgt nu de termijnen en de woorden van de Cyberbeveiligingswet.',
            'Onder elk formulier staat nu op welke documenten het is gebaseerd.',
        ],
    ],
    [
        'versie' => '1.2.1',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'De quickscan en de fit/gap voor de BIO 1 heten nu quickscan 1.0 en fit/gap 1.0.',
            'Quickscan 2.0 volgt bij de te beschermen belangen nu precies de tabel uit de regeling. Daardoor krijgen '
                . 'meer systemen TBB 4.',
            'Het document van fit/gap 2.0 laat nu per onderdeel van de zorgplicht uit de Cyberbeveiligingswet zien '
                . 'of de dienst voldoet.',
            'Onderaan elke pagina staat de versie van de Cisomatic, met een link naar deze pagina.',
        ],
    ],
    [
        'versie' => '1.2',
        'datum' => '2026-10-15',
        'wijzigingen' => [
            'Nieuw: dossiers. Daarmee bundel je alle formulieren van een project. Een antwoord dat in meer formulieren '
                . 'voorkomt, vul je maar één keer in.',
        ],
    ],
    [
        'versie' => '1.1',
        'datum' => '2026-10-13',
        'wijzigingen' => [
            'Nieuw: fit/gap 1.0 en fit/gap 2.0. Daarmee laat een leverancier zien of de dienst aan de eisen voldoet.',
            'Elk document heeft nu een kenmerk en een hoofdstuk Verantwoording.',
            'De kanalenscan volgt de kaders van 1 oktober 2026.',
            'Op de voordeur heeft elk formulier een icoon.',
        ],
    ],
    [
        'versie' => '1.0',
        'datum' => '2026-10-01',
        'wijzigingen' => [
            'De eerste versie, met negen formulieren.',
        ],
    ],
];
