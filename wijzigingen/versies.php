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
        'versie' => '1.1.2',
        'datum' => '2026-10-26',
        'wijzigingen' => [
            'De quickscan en de fit/gap voor de BIO 1 heten nu quickscan 1.0 en fit/gap 1.0. Quickscan 2.0 en '
                . 'fit/gap 2.0 horen bij de BIO2.',
            'Quickscan 2.0 is naast de generieke baselinetoets BIO2 gelegd. Een eis boven Zeer laag geeft nu TBB 4, '
                . 'zoals bijlage 1 van de regeling zegt.',
            'Het advies van quickscan 2.0 zegt dat de overheidsmaatregelen altijd gelden, en wat er bij een volledige '
                . 'risicoanalyse moet gebeuren.',
            'Het document van fit/gap 2.0 laat per onderdeel van de zorgplicht uit de Cyberbeveiligingswet zien hoe de '
                . 'dienst ervoor staat.',
            'Elke pagina noemt de versie, met een link naar deze pagina. Het document noemt de versie ook.',
        ],
    ],
    [
        'versie' => '1.1.1',
        'datum' => '2026-10-15',
        'wijzigingen' => [
            'Dossiers: alle formulieren van een project bij elkaar. Een antwoord dat in meer formulieren terugkomt, '
                . 'vul je één keer in.',
        ],
    ],
    [
        'versie' => '1.1',
        'datum' => '2026-10-13',
        'wijzigingen' => [
            'Nieuw: fit/gap 1.0 en fit/gap 2.0. De leverancier verklaart per norm of de dienst voldoet.',
            'Elk document heeft een kenmerk, een vingerafdruk van de antwoorden en een hoofdstuk Verantwoording.',
            'De kanalenscan volgt de kaders van 1 oktober 2026.',
            'Een icoon per formulier op de voordeur. Dezelfde code draait los en binnen een site.',
        ],
    ],
    [
        'versie' => '1.0',
        'datum' => '2026-10-01',
        'wijzigingen' => [
            'De eerste versie: de formulieren, en van elk een document als .odt.',
        ],
    ],
];
