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
            'Alle formulieren nog eens nagelezen. Lange zinnen zijn korter, en woorden als AI-scan, AI-verordening en '
                . 'CIO-oordeel hebben nu een koppelteken.',
        ],
    ],
    [
        'versie' => '1.2.4',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'Quickscan 2.0 volgt bij de risicoanalyse de tekst van de BIO2. Elk nieuw systeem krijgt een risicoafweging '
                . '(5.08.01). Dat er soms een volledige analyse volgt, is een keuze van de quickscan zelf, en dat staat '
                . 'er nu ook bij.',
            'Zit je vast aan een leverancier? Dan vraagt de quickscan geen volledige analyse meer. Wel een afweging '
                . 'vóór het contract, zoals de BIO2 vraagt (5.20.06).',
        ],
    ],
    [
        'versie' => '1.2.3',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'Quickscan 2.0 naast de Cyberbeveiligingsregeling gelegd. De te beschermen belangen klopten al. Bij de '
                . 'meldplicht staan nu ook NATO en EU Confidential, en dat gepland onderhoud geen incident is.',
        ],
    ],
    [
        'versie' => '1.2.2',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'In quickscan 2.0 en fit/gap 2.0 klopten een paar nummers van BIO2-maatregelen niet. Het testen van het '
                . 'continuïteitsplan is bijvoorbeeld 5.30.01, niet 5.29.01. Oude antwoordbestanden laden gewoon in.',
            'De meldplicht gebruikt de woorden van de Cyberbeveiligingswet: binnen 24 uur een waarschuwing, binnen 72 '
                . 'uur de melding, en een maand later het eindverslag.',
            'Onder elk formulier staat op welke documenten het is gebaseerd.',
        ],
    ],
    [
        'versie' => '1.2.1',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'De oude quickscan en fit/gap heten nu quickscan 1.0 en fit/gap 1.0. Ze horen bij de BIO 1.',
            'Quickscan 2.0 vergeleken met de generieke baselinetoets BIO2. Een systeem met een eis boven Zeer laag '
                . 'krijgt nu TBB 4.',
            'Fit/gap 2.0 laat per onderdeel van de zorgplicht zien hoe de dienst ervoor staat.',
            'Onderaan elke pagina staat de versie, met een link naar deze lijst.',
        ],
    ],
    [
        'versie' => '1.2',
        'datum' => '2026-10-15',
        'wijzigingen' => [
            'Dossiers. Je bundelt de formulieren van een project, en een antwoord dat vaker terugkomt vul je één keer '
                . 'in.',
        ],
    ],
    [
        'versie' => '1.1',
        'datum' => '2026-10-13',
        'wijzigingen' => [
            'Nieuw: fit/gap 1.0 en fit/gap 2.0, voor de leverancier.',
            'Elk document heeft een kenmerk en een hoofdstuk Verantwoording.',
            'De kanalenscan volgt de kaders van 1 oktober 2026.',
            'Iconen op de voordeur.',
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
