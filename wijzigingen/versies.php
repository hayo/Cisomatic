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
        'versie' => '1.4',
        'datum' => '2026-11-04',
        'wijzigingen' => [
            'Nieuw: de Online communicatie check. Daarmee toets je snel of een kanaal op sociale media voldoet aan het '
                . 'rijksbrede kader. Zo niet, dan leg je uit waarom jullie het toch gebruiken.',
            'De check toetst alleen wat het kader eist: het platform, dienstverlening en veilig contact, en twee '
                . 'voorwaarden. Advies over doelen en doelgroep geeft hij niet.',
            'De check is er ook als .odt op één A4, met per platform wat het kader eist.',
            'De kanalenscan blijft, voor een grondige toets en voor starten en stoppen op een platform.',
        ],
    ],
    [
        'versie' => '1.3.1',
        'datum' => '2026-11-03',
        'wijzigingen' => [
            'Elk formulier heeft nu een vragenlijst als .odt, om op papier of in Word in te vullen. De link staat '
                . 'boven het formulier.',
            'Vragen die bij elkaar horen, zoals de vragen bij één risico, staan in de vragenlijst samen in één blok.',
            'In de AI-scan staat bij elke vraag wanneer hij geldt, en bij welk antwoord de scan stopt.',
            'In het CIO-oordeel staat bij de vraag naar het oordeel hoe het formulier een oordeel voorstelt.',
        ],
    ],
    [
        'versie' => '1.3',
        'datum' => '2026-11-03',
        'wijzigingen' => [
            'Alle documenten hebben nu de Rijkshuisstijl: het lint met het woordmerk, de letter Rijks Sans en de '
                . 'kleuren van het Rijk.',
            'De letters zitten in het document. LibreOffice toont ze ook als ze niet op je computer staan.',
            'De kanalenscan sociale media heeft een vragenlijst als .odt, om op papier of in Word in te vullen. De '
                . 'link staat boven het formulier.',
            'In de vragenlijst is elke vraag een blok met een nummer. Bovenaan staat de vraag, en wanneer hij geldt. '
                . 'Daaronder vul je in.',
            'Per platform zie je wat binnen het kader valt. Met de punten in stap 4 bepaal je zelf de uitkomst.',
        ],
    ],
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
