<?php

/**
 * Afleidingen: de route door de beslishulp, de plichten die daaruit volgen,
 * en de toets aan het standpunt over generatieve AI.
 *
 * ai_route() loopt de beslisboom af en bouwt onderweg een profiel: de rollen,
 * de soort, de risicogroep en de transparantie. De plichten volgen uit dat
 * profiel, niet uit de ruim veertig conclusies van de beslishulp. regels.js
 * loopt dezelfde route tijdens het invullen; PHP rekent bij het versturen
 * alles opnieuw uit.
 */

declare(strict_types=1);

/** Waarom de AI-verordening niet geldt. */
const AI_NIET = [
    'algoritme' => 'Er zit geen algoritme in de toepassing.',
    'rol' => 'Jullie hebben geen rol die de AI verordening kent.',
    'ai' => 'De toepassing is geen AI systeem en geen AI model, zoals de AI verordening die bedoelt.',
    'uitzondering' => 'Er geldt een uitzondering uit artikel 2.',
    'opensource' => 'De toepassing is open source, zonder hoog risico en zonder plicht tot transparantie. Daarvoor '
        . 'geldt een uitzondering uit artikel 2.',
];

/** Wat dan nog geldt. */
const AI_NIET_ADVIES = [
    'algoritme' => ['Houd je aan de andere wetten, zoals de AVG als er persoonsgegevens in de toepassing komen.'],
    'rol' => ['Kijk goed na of jullie echt geen rol hebben. De AI verordening kent de aanbieder, de '
        . 'gebruiksverantwoordelijke, de importeur en de distributeur.'],
    'ai' => ['Kijk of het algoritme veel invloed heeft op mensen. Dan hoort het in het Algoritmeregister. De '
        . 'Handreiking Algoritmeregister helpt daarbij.',
        'Houd je aan de andere wetten, zoals de AVG als er persoonsgegevens in de toepassing komen.'],
    'uitzondering' => ['Zet het algoritme in het Algoritmeregister.',
        'Houd je aan de andere wetten, zoals de AVG als er persoonsgegevens in de toepassing komen.'],
    'opensource' => ['Zet het algoritme in het Algoritmeregister.',
        'Houd je aan de andere wetten, zoals de AVG als er persoonsgegevens in de toepassing komen.'],
];

const AI_SOORTEN = [
    'systeem' => 'AI systeem',
    'gpai_systeem' => 'AI systeem voor algemene doeleinden',
    'model' => 'AI model voor algemene doeleinden',
];

/** De uitkomst van de beslishulp, met de zwaarte voor de kleur op de pagina. */
const AI_KLASSEN = [
    'niet' => ['label' => 'De AI verordening geldt niet', 'niveau' => '',
        'uitleg' => 'Andere regels blijven wel gelden, zoals de AVG.'],
    'verboden' => ['label' => 'Verboden', 'niveau' => 'zh',
        'uitleg' => 'De AI verordening verbiedt deze toepassing. Hij mag niet worden gebruikt.'],
    'hoog' => ['label' => 'Hoog risico', 'niveau' => 'h',
        'uitleg' => 'Er gelden veel eisen, voor de aanbieder en voor de gebruiksverantwoordelijke.'],
    'systeemrisico' => ['label' => 'Model met een systeemrisico', 'niveau' => 'h',
        'uitleg' => 'Het model is zo groot dat het de hele EU kan raken. Daarom gelden er extra eisen.'],
    'model' => ['label' => 'Model voor algemene doeleinden', 'niveau' => '',
        'uitleg' => 'Voor de aanbieder van het model gelden eigen regels.'],
    'transparantie' => ['label' => 'Geen hoog risico, wel transparantie', 'niveau' => 'm',
        'uitleg' => 'Mensen moeten weten dat ze met AI te maken hebben.'],
    'laag' => ['label' => 'Geen hoog risico', 'niveau' => '',
        'uitleg' => 'Er gelden maar weinig regels uit de AI verordening.'],
];

/** De plichten per rol en risicogroep. De datums zet ai_plichten() erbij. */
const AI_PLICHTEN = [
    'kennis' => [
        'titel' => 'Kennis van AI (artikel 4)',
        'punten' => [
            'Help iedereen die met het systeem werkt om genoeg over AI te leren.',
            'Een gedragscode is vrijwillig (artikel 95). Jullie mogen er een opstellen, of een bestaande volgen.',
        ],
    ],
    'aanbieder' => [
        'titel' => 'Aanbieder van een systeem met een hoog risico (artikel 16)',
        'punten' => [
            'Zorg dat het systeem voldoet aan de eisen voor een hoog risico (artikel 8 tot en met 15). Die gaan over '
                . 'risicobeheer, data, documentatie, logs, uitleg, menselijk toezicht, nauwkeurigheid en beveiliging.',
            'Richt een systeem in dat de kwaliteit bewaakt (artikel 17).',
            'Bewaar de documentatie (artikel 18), en de logs die het systeem zelf maakt (artikel 19).',
            'Laat het systeem beoordelen voordat het in gebruik gaat (artikel 43). Dat heet een '
                . 'conformiteitsbeoordeling.',
            'Stel een EU conformiteitsverklaring op (artikel 47), en zet de CE markering op het systeem (artikel 48).',
            'Registreer het systeem in de databank van de EU (artikel 49).',
            'Zet de naam en het adres van de aanbieder op het systeem, of in de documentatie.',
            'Voldoet het systeem niet? Grijp dan in, en meld het (artikel 20).',
            'Laat een toezichthouder op verzoek zien dat het systeem voldoet.',
            'Zorg dat mensen met een beperking het systeem kunnen gebruiken.',
        ],
    ],
    'gebruiker' => [
        'titel' => 'Gebruiksverantwoordelijke van een systeem met een hoog risico (artikel 26 en 27)',
        'punten' => [
            'Gebruik het systeem zoals de gebruiksaanwijzing zegt.',
            'Laat mensen met genoeg kennis, training en gezag toezicht houden.',
            'Hebben jullie de invoer in handen? Zorg dan dat die past bij het doel van het systeem.',
            'Houd in de gaten hoe het systeem werkt. Meld problemen aan de aanbieder.',
            'Bewaar de logs van het systeem, minstens zes maanden.',
            'Komt het systeem op de werkvloer? Vertel dat dan vooraf aan de werknemers en hun vertegenwoordigers.',
            'Als overheid registreren jullie het gebruik in de databank van de EU (artikel 49). Staat het systeem daar '
                . 'zelf niet in? Gebruik het dan niet, en meld dat aan de aanbieder.',
            'Gebruik de informatie van de aanbieder voor de DPIA.',
            'Helpt het systeem bij besluiten over mensen? Vertel hun dan dat het wordt gebruikt.',
            'Werk mee met de toezichthouders.',
            'Beoordeel de gevolgen voor de grondrechten, als overheid of bij een publieke dienst (artikel 27). De IAMA '
                . 'in deze tool is daarvoor gemaakt.',
        ],
    ],
    'model' => [
        'titel' => 'Het model voor algemene doeleinden (artikel 53 tot en met 56)',
        'punten' => [
            'Maak technische documentatie van het model, over de training, de tests en de uitkomsten. Houd die bij.',
            'Geef partijen die het model in hun systeem bouwen genoeg informatie en documentatie.',
            'Maak beleid om het auteursrecht te respecteren.',
            'Publiceer een samenvatting van de data waarmee het model is getraind. Gebruik daarvoor het sjabloon van '
                . 'het AI Office van de Europese Commissie.',
            'Een praktijkcode van de EU helpt om te laten zien dat het model voldoet (artikel 56).',
        ],
    ],
    'systeemrisico' => [
        'titel' => 'Model met een systeemrisico (artikel 55)',
        'punten' => [
            'Evalueer het model met de nieuwste methoden. Test ook of het zwakke plekken heeft.',
            'Breng de risico’s voor de hele EU in kaart, en beperk ze.',
            'Houd ernstige incidenten bij, en meld ze meteen aan het AI Office.',
            'Beveilig het model en de techniek eronder goed.',
        ],
    ],
    'model_gebruiker' => [
        'titel' => 'Het model voor algemene doeleinden',
        'punten' => [
            'Als gebruiksverantwoordelijke hebben jullie geen plichten voor het model zelf.',
            'Bouwen jullie het model uit tot een systeem? Dan kunnen er wel plichten gelden, zoals voor transparantie '
                . 'of een hoog risico. Doe de scan dan opnieuw voor dat systeem.',
        ],
    ],
    'importeur' => [
        'titel' => 'Importeur van een systeem met een hoog risico (artikel 23)',
        'punten' => [
            'Controleer of de aanbieder alles heeft gedaan, voordat het systeem op de markt komt. Denk aan de '
                . 'conformiteitsbeoordeling, de documentatie, de CE markering, de EU conformiteitsverklaring en de '
                . 'gebruiksaanwijzing.',
            'Twijfel je of het systeem voldoet? Breng het dan niet op de markt. Is het gevaarlijk? Meld dat aan de '
                . 'aanbieder en de toezichthouder.',
            'Zet jullie naam en adres op de verpakking, of in de documentatie.',
            'Zorg dat opslag en vervoer het systeem niet schaden.',
            'Bewaar het certificaat, de gebruiksaanwijzing en de verklaring tien jaar lang.',
            'Geef toezichthouders op verzoek alle informatie, en werk met hen mee.',
        ],
    ],
    'distributeur' => [
        'titel' => 'Distributeur van een systeem met een hoog risico (artikel 24)',
        'punten' => [
            'Controleer of het systeem de CE markering heeft, met de EU conformiteitsverklaring en de '
                . 'gebruiksaanwijzing.',
            'Twijfel je of het systeem voldoet? Lever het dan niet. Is het gevaarlijk? Meld dat aan de aanbieder of de '
                . 'importeur.',
            'Zorg dat opslag en vervoer het systeem niet schaden.',
            'Blijkt later dat het systeem niet voldoet? Zorg dan dat het wordt hersteld, uit de handel gaat of wordt '
                . 'teruggeroepen. Meld het ook aan de toezichthouder.',
            'Geef toezichthouders op verzoek alle informatie, en werk met hen mee.',
        ],
    ],
    'markt' => [
        'titel' => 'Geen plichten',
        'punten' => ['Voor importeurs en distributeurs gelden alleen plichten bij een systeem met een hoog risico '
            . '(artikel 23 en 24).'],
    ],
];

/** De voorwaarden uit het standpunt over generatieve AI. */
const GAI_VOORWAARDEN = [
    'wet' => 'Het voldoet aan de wet, zoals de AVG en de AI verordening',
    'doel' => 'Het doel is duidelijk',
    'risico' => 'Er is een risicoanalyse, met een gemengde groep',
    'beleid' => 'Het past in het beleid, en de juiste mensen beslissen',
    'leverancier' => 'Er zijn afspraken met de leverancier',
    'consument' => 'Het gebruik valt niet onder voorwaarden voor consumenten',
];

const GAI_OORDELEN = [
    'ja' => ['tekst' => 'Ja, de voorwaarden uit het standpunt zijn gehaald.', 'niveau' => ''],
    'nog_niet' => ['tekst' => 'Nog niet, er ontbreekt nog iets.', 'niveau' => 'm'],
    'verkenning' => ['tekst' => 'Alleen als verkenning, zonder gevoelige gegevens.', 'niveau' => 'm'],
    'nee' => ['tekst' => 'Nee, dit mag niet volgens het standpunt.', 'niveau' => 'h'],
];

/** Tips uit de handreiking die altijd gelden. */
const GAI_TIPS = [
    'Zet de toepassing in het Algoritmeregister. Beschrijf daar het doel, en voor wie de toepassing is.',
    'Breng de beveiliging in kaart. Let ook op verstopte opdrachten in tekst die de AI leest (prompt injectie). De '
        . 'OWASP Top 10 voor taalmodellen helpt daarbij.',
    'Zorg dat medewerkers en burgers fouten en discriminatie kunnen melden.',
    'Bewaar belangrijke vragen en antwoorden. Dan kun je later nagaan hoe iets tot stand kwam.',
    'Gebruik in communicatie geen beelden of stemmen van AI die echt lijken.',
    'Kies waar het kan een kleiner of zuiniger model.',
];

/** Wat regels.js nodig heeft om de route te lopen en de uitkomst te tonen. @return array<string,mixed> */
function ai_model(): array
{
    return ['rollen' => AI_ROLLEN, 'niet' => AI_NIET, 'soorten' => AI_SOORTEN, 'klassen' => AI_KLASSEN];
}

// ---------------------------------------------------------------------------
// De route

/**
 * De route door de beslishulp: de vragen in de volgorde van de beslisboom,
 * tot de eerste open vraag of tot het einde. De aankruislijst is nooit open,
 * want niets aanvinken is ook een antwoord.
 *
 * @return array<string,mixed>
 */
function ai_route(array $a): array
{
    $r = ['pad' => [], 'klaar' => false, 'rollen' => [], 'soort' => '', 'niet' => '', 'risico' => '',
        'bijlage3' => false, 'biometrie' => false, 'derde' => false, 'transparantie' => false,
        'opensource' => false, 'systeemrisico' => false];

    $key = 'ai_algoritme';
    while ($key !== '') {
        $r['pad'][] = $key;
        $antwoord = $a[$key] ?? ($key === 'ai_toepassing' ? [] : '');
        if ($antwoord === '') {
            return $r;
        }

        [$key, $zet] = ai_stap($key, $antwoord, $r);
        $r = array_replace($r, $zet);
    }

    $r['klaar'] = true;

    // Open source zonder hoog risico en zonder transparantie valt buiten de verordening (artikel 2, lid 12).
    if ($r['opensource'] && $r['risico'] === 'laag' && !$r['transparantie'] && $r['soort'] !== 'model') {
        $r['niet'] = 'opensource';
    }

    return $r;
}

/**
 * Eén stap in de boom: de volgende vraag, leeg aan het eind, en wat het
 * antwoord aan het profiel toevoegt. Een importeur of distributeur stopt na
 * de risicogroep, want alleen die bepaalt zijn plichten.
 *
 * @param string|list<string> $w
 * @return array{0:string,1:array<string,mixed>}
 */
function ai_stap(string $key, string|array $w, array $r): array
{
    $markt = array_intersect($r['rollen'], ['aanbieder', 'gebruiksverantwoordelijke']) === [];
    $transparantie = $r['transparantie'] ? 'ai_transparantie_uitz' : '';
    $hoog = [$markt ? '' : $transparantie, ['risico' => 'hoog']];
    $laag = [$markt ? '' : 'ai_opensource', ['risico' => 'laag']];
    $niet = static fn (string $reden): array => ['', ['niet' => $reden]];

    $lijst = (array) $w;
    $begint = static fn (string $begin): bool
        => array_filter($lijst, static fn (string $k): bool => str_starts_with($k, $begin)) !== [];
    $verboden = array_values(array_filter($lijst, static fn (string $k): bool => str_starts_with($k, 'verboden_')));

    return match ($key) {
        'ai_algoritme' => $w === 'ja' ? ['ai_rol', []] : $niet('algoritme'),
        'ai_rol' => AI_ROLLEN[$w] === [] ? $niet('rol') : ['ai_status', ['rollen' => AI_ROLLEN[$w]]],
        'ai_status' => ['ai_soort', []],
        'ai_soort' => match ($w) {
            'twijfel' => ['ai_regels', []],
            'geen' => $niet('ai'),
            default => [$w === 'model' && $markt ? '' : 'ai_uitzondering', ['soort' => $w]],
        },
        'ai_regels' => $w === 'nee' ? ['ai_inferentie', []] : $niet('ai'),
        'ai_inferentie' => $w === 'ja' ? ['ai_omgeving', []] : $niet('ai'),
        'ai_omgeving' => $w === 'ja' ? ['ai_uitzondering', ['soort' => 'systeem']] : $niet('ai'),
        'ai_uitzondering' => $w !== 'nee'
            ? $niet('uitzondering')
            : [$r['soort'] === 'model' ? 'ai_systeemrisico' : 'ai_toepassing', []],
        'ai_systeemrisico' => $w === 'ja'
            ? ['', ['systeemrisico' => true]]
            : [in_array('aanbieder', $r['rollen'], true) ? 'ai_opensource' : '', []],

        // Alleen voor live biometrie in de openbare ruimte kent artikel 5 uitzonderingen.
        'ai_toepassing' => [
            match (true) {
                $verboden === ['verboden_realtime'] => 'ai_verboden_uitz',
                $verboden !== [] => '',
                default => 'ai_bijlage1',
            },
            [
                'risico' => $verboden !== [] && $verboden !== ['verboden_realtime'] ? 'verboden' : '',
                'bijlage3' => $begint('hoog_') || in_array('transparantie_emotie', $lijst, true),
                'biometrie' => in_array('hoog_biometrie', $lijst, true) || in_array('transparantie_emotie', $lijst, true),
                'transparantie' => $begint('transparantie_'),
            ],
        ],
        'ai_verboden_uitz' => match (true) {
            $w === 'nee' => ['', ['risico' => 'verboden']],
            $markt => ['', ['risico' => 'hoog']],
            default => ['ai_bijlage1', ['bijlage3' => true, 'biometrie' => true]],
        },
        'ai_bijlage1' => match ($w) {
            'nee' => $r['bijlage3'] ? ['ai_profilering', ['derde' => $r['biometrie']]] : $laag,
            default => [
                $markt ? '' : ($w === 'a' && in_array('aanbieder', $r['rollen'], true) ? 'ai_derde' : $transparantie),
                ['risico' => 'hoog'],
            ],
        },
        'ai_derde' => [$transparantie, ['derde' => $w === 'ja']],
        'ai_profilering' => $w === 'ja' ? $hoog : ['ai_taak', []],
        'ai_taak' => $w === 'nee' ? $hoog : $laag,
        'ai_opensource' => [$r['soort'] === 'model' ? '' : $transparantie, ['opensource' => $w === 'ja']],
        'ai_transparantie_uitz' => ['', ['transparantie' => $w === 'nee']],
    };
}

/** Voor toon_als_uitkomst: of een vraag op de route ligt, en of de risicogroep aan de beurt komt. */
function ai_uitkomst(string $naam, array $a): string
{
    $r = ai_route($a);

    if ($naam === 'risico') {
        return $r['klaar'] && array_intersect(['ai_toepassing', 'ai_systeemrisico'], $r['pad']) === [] ? 'nee' : 'ja';
    }

    return in_array($naam, $r['pad'], true) ? 'ja' : 'nee';
}

/** De sleutel in AI_KLASSEN; leeg zolang de route niet af is. */
function ai_klasse(array $r): string
{
    return match (true) {
        $r['niet'] !== '' => 'niet',
        !$r['klaar'] => '',
        $r['risico'] === 'verboden' => 'verboden',
        $r['risico'] === 'hoog' => 'hoog',
        $r['systeemrisico'] => 'systeemrisico',
        $r['soort'] === 'model' => 'model',
        $r['transparantie'] => 'transparantie',
        default => 'laag',
    };
}

// ---------------------------------------------------------------------------
// De plichten

/**
 * De plichten uit de AI-verordening, per blok: een titel, de punten en
 * vanaf wanneer ze gelden. De datums zijn die na de wijziging van juli 2026.
 *
 * @return list<array{titel:string,punten:list<string>,vanaf:string}>
 */
function ai_plichten(array $a, array $r): array
{
    if ($r['niet'] !== '') {
        return [['titel' => 'Andere regels blijven gelden', 'punten' => AI_NIET_ADVIES[$r['niet']], 'vanaf' => '']];
    }

    $rol = static fn (string $naam): bool => in_array($naam, $r['rollen'], true);
    $aangevinkt = static fn (string $key): bool => in_array($key, (array) ($a['ai_toepassing'] ?? []), true);
    $inGebruik = ($a['ai_status'] ?? '') === 'gebruik';

    if ($r['risico'] === 'verboden') {
        return [[
            'titel' => 'Verboden (artikel 5)',
            'punten' => array_values(array_filter([
                $rol('gebruiksverantwoordelijke') ? 'Stop met het gebruik van het systeem.' : '',
                $rol('gebruiksverantwoordelijke') && count($r['rollen']) === 1 ? '' : 'Haal het systeem van de markt.',
            ])),
            'vanaf' => 'Het verbod geldt sinds 2 februari 2025.' . ($aangevinkt('verboden_naakt')
                ? ' Voor naaktbeelden van echte mensen geldt het vanaf 2 december 2026.' : ''),
        ]];
    }

    $gebruik = $rol('aanbieder') || $rol('gebruiksverantwoordelijke');
    $hoog = $r['risico'] === 'hoog';
    $model = $r['soort'] === 'model';
    $blok = static fn (string $id, string $vanaf, array $erbij = []): array => [
        'titel' => AI_PLICHTEN[$id]['titel'],
        'punten' => [...AI_PLICHTEN[$id]['punten'], ...$erbij],
        'vanaf' => $vanaf,
    ];

    $bijlage1 = in_array($a['ai_bijlage1'] ?? '', ['a', 'b'], true);
    $vanafHoog = 'Deze plichten gelden vanaf ' . ($bijlage1 ? '2 augustus 2028' : '2 december 2027') . '.'
        . ($inGebruik ? ' Het systeem is al in gebruik, en dan gelden ze pas na een grote wijziging. Voor de overheid '
            . 'is er wel een grens: uiterlijk 2 augustus 2030 moet het systeem voldoen.' : '');
    $vanafModel = 'Deze plichten gelden sinds 2 augustus 2025.'
        . ($inGebruik ? ' Was het model al eerder op de markt? Dan moet het uiterlijk 2 augustus 2027 voldoen.' : '');

    return array_values(array_filter([
        $gebruik && !$model ? $blok('kennis', 'Dit geldt sinds 2 februari 2025.') : null,
        $hoog && $rol('aanbieder') ? $blok('aanbieder', $vanafHoog, $r['derde']
            ? ['Voor dit systeem moet een aangemelde instantie de beoordeling doen. Dat is een onafhankelijke '
                . 'keuringsinstantie.'] : []) : null,
        $hoog && $rol('gebruiksverantwoordelijke') ? $blok('gebruiker', $vanafHoog, $r['biometrie']
            ? ['Zoeken jullie met biometrie op afstand naar een verdachte? Vraag dan toestemming aan een rechter of een '
                . 'andere instantie. Dat moet vooraf, of uiterlijk binnen 48 uur.'] : []) : null,
        $r['transparantie'] && $gebruik ? ai_plicht_transparantie($r, $aangevinkt) : null,
        $rol('aanbieder') && ($model || $r['soort'] === 'gpai_systeem') ? ai_plicht_model($r, $vanafModel) : null,
        $rol('aanbieder') && $r['systeemrisico'] ? $blok('systeemrisico', $vanafModel) : null,
        $model && !$rol('aanbieder') && $rol('gebruiksverantwoordelijke') ? $blok('model_gebruiker', '') : null,
        $hoog && $rol('importeur') ? $blok('importeur', $vanafHoog) : null,
        $hoog && $rol('distributeur') ? $blok('distributeur', $vanafHoog) : null,
        !$gebruik && !$hoog ? $blok('markt', '') : null,
    ]));
}

/**
 * Transparantie: wat de aanbieder en de gebruiksverantwoordelijke moeten
 * doen, per soort toepassing die is aangevinkt.
 *
 * @param callable(string):bool $aangevinkt
 * @return array{titel:string,punten:list<string>,vanaf:string}
 */
function ai_plicht_transparantie(array $r, callable $aangevinkt): array
{
    $aanbieder = in_array('aanbieder', $r['rollen'], true);
    $gebruiker = in_array('gebruiksverantwoordelijke', $r['rollen'], true);
    $praten = $aangevinkt('transparantie_interactie');
    $maken = $aangevinkt('transparantie_generatie');

    return [
        'titel' => 'Transparantie (artikel 50)',
        'punten' => array_values(array_filter([
            $aanbieder && $praten ? 'Maak het systeem zo dat mensen weten dat ze met AI praten. Dat hoeft niet als het '
                . 'voor iedereen duidelijk is.' : '',
            $aanbieder && $maken ? 'Markeer wat het systeem maakt als gemaakt door AI. Doe dat zo dat een computer het '
                . 'kan herkennen.' : '',
            !$aanbieder && $praten ? 'De aanbieder moet zorgen dat mensen weten dat ze met AI praten. Controleer of '
                . 'dat is geregeld.' : '',
            $gebruiker && $aangevinkt('transparantie_emotie') ? 'Vertel mensen dat het systeem emoties herkent, of hen '
                . 'indeelt op basis van biometrie.' : '',
            $gebruiker && $maken ? 'Lijkt beeld, geluid of video van de AI echt? Maak dan bekend dat AI het heeft '
                . 'gemaakt of bewerkt.' : '',
            $gebruiker && $maken ? 'Publiceren jullie tekst van AI om mensen te informeren over zaken van algemeen '
                . 'belang? Zeg dan dat AI de tekst maakte. Dat hoeft niet als een mens de tekst nakijkt, en er '
                . 'verantwoordelijk voor is.' : '',
        ])),
        'vanaf' => 'Deze plichten gelden sinds 2 augustus 2026.' . ($aanbieder && $maken
            ? ' Was het systeem al eerder op de markt? Dan moet het markeren uiterlijk 2 december 2026 geregeld zijn.'
            : ''),
    ];
}

/**
 * De plichten voor het model. Bij een systeem voor algemene doeleinden
 * gelden ze voor wie het model aanbiedt; dat kan een andere partij zijn. Een
 * open model hoeft geen documentatie te maken voor anderen.
 *
 * @return array{titel:string,punten:list<string>,vanaf:string}
 */
function ai_plicht_model(array $r, string $vanaf): array
{
    $punten = AI_PLICHTEN['model']['punten'];

    if ($r['soort'] === 'gpai_systeem') {
        array_unshift($punten, 'Deze plichten gelden voor wie het model aanbiedt. Is dat een andere partij? Vraag die '
            . 'dan om de informatie die zij moet geven.');
    } elseif ($r['opensource']) {
        $punten = [...array_slice($punten, 2), 'Het model is open source. Daarom hoeven jullie geen documentatie te '
            . 'maken voor anderen.'];
    }

    return ['titel' => AI_PLICHTEN['model']['titel'], 'punten' => $punten, 'vanaf' => $vanaf];
}

// ---------------------------------------------------------------------------
// Generatieve AI

/**
 * De toets aan het standpunt over generatieve AI: per voorwaarde of die is
 * gehaald, wat nog moet, en de tips uit de handreiking.
 *
 * @return array{oordeel:string,voorwaarden:array<string,string>,acties:list<string>,tips:list<string>}
 */
function gai_standpunt(array $a, array $r): array
{
    $w = static fn (string $key): string => (string) ($a[$key] ?? '');
    $open = array_diff_key(GAI_AFWEGINGEN, array_flip((array) ($a['gai_afwegingen'] ?? [])));
    $consument = $w('gai_voorwaarden') === 'consument';
    $verkenning = $consument && $w('gai_verkenning') === 'ja';

    $gehaald = [
        'wet' => in_array($w('gai_persoonsgegevens'), ['nee', 'dpia'], true) && $r['risico'] !== 'verboden',
        'doel' => !isset($open['noodzaak']),
        'risico' => $w('gai_risicoanalyse') === 'ja' && $w('gai_team') === 'gemengd' && $open === [],
        'beleid' => $w('gai_beleid') === 'ja' && $w('gai_besluit') === 'verantwoordelijke',
        'leverancier' => match ($w('gai_voorwaarden')) {
            'eigen' => null,
            'contract' => $w('gai_afspraken') === 'alles',
            default => false,
        },
        'consument' => !$consument,
    ];

    $voorwaarden = [];
    foreach (GAI_VOORWAARDEN as $id => $naam) {
        $voorwaarden[$naam] = match (true) {
            $gehaald[$id] === null => 'Niet nodig',
            $id === 'consument' && $verkenning => 'Alleen voor een verkenning',
            default => $gehaald[$id] ? 'Ja' : 'Nee',
        };
    }

    $acties = [
        $consument && !$verkenning ? 'Stop met gebruik onder de voorwaarden voor consumenten. Dat mag niet, ook niet '
            . 'voor één medewerker. Kies een dienst met een contract, of een model in eigen beheer.' : '',
        $verkenning ? 'Gebruik de AI alleen om te verkennen, zonder gevoelige gegevens. Voor echt gebruik is een '
            . 'contract nodig, of een model in eigen beheer.' : '',
        $r['risico'] === 'verboden' ? 'De AI verordening verbiedt deze toepassing. Hij mag dus niet worden gebruikt.' : '',
        $w('gai_persoonsgegevens') === 'ja' ? 'Doe een DPIA, of eerst een prescan DPIA. Dat moet altijd als er '
            . 'persoonsgegevens in de AI komen. De DPIA staat ook in deze tool.' : '',
        $open['noodzaak']['actie'] ?? '',
        match ($w('gai_risicoanalyse')) {
            'bezig' => 'Maak de risicoanalyse af.',
            'nee' => 'Doe een risicoanalyse, bijvoorbeeld met de IAMA in deze tool.',
            default => '',
        },
        $w('gai_team') === 'gemengd' ? '' : 'Laat een gemengde groep meedenken over de risico’s. Denk aan een jurist, '
            . 'de privacy officer, de CISO en mensen die de AI gaan gebruiken.',
        ...array_column(array_diff_key($open, ['noodzaak' => true]), 'actie'),
        match ($w('gai_besluit')) {
            'zonder_advies' => 'Vraag advies aan de CISO, de CIO en de FG, voordat er een besluit valt.',
            'onbekend' => 'Spreek af wie over de AI beslist. Die persoon vraagt eerst advies aan de CISO, de CIO en de FG.',
            default => '',
        },
        match ($w('gai_beleid')) {
            'geen' => 'Maak beleid voor generatieve AI. De handreiking helpt daarbij. Laat de CISO, de CIO en de FG '
                . 'meelezen.',
            'nee' => 'Zoek uit of de AI in het beleid past. Pas zo nodig de toepassing aan.',
            default => '',
        },
        $w('gai_voorwaarden') === 'contract' && $w('gai_afspraken') !== 'alles' ? 'Maak afspraken met de leverancier '
            . 'over drie dingen: het delen van gegevens, het bewaren ervan, en het trainen met jullie invoer.' : '',
        $w('gai_besluiten') === 'zelf' ? 'Laat de AI geen besluiten over mensen nemen. Gebruik generatieve AI alleen '
            . 'als hulp, en laat een mens beslissen.' : '',
    ];

    $tips = [
        $w('gai_kennis') === 'ja' ? '' : 'Geef de gebruikers uitleg of training over AI. De AI verordening vraagt dat '
            . 'ook (artikel 4).',
        $w('gai_mens') === 'altijd' ? '' : 'Laat een mens nakijken wat de AI maakt, voordat het wordt gebruikt. '
            . 'Generatieve AI kan fouten maken, en dingen verzinnen.',
        $w('gai_burgers') === 'ja' ? 'Vertel burgers dat ze met AI praten. Geef ze ook altijd de keus om een mens te '
            . 'spreken.' : '',
        $w('gai_model') === 'open_eu' ? '' : 'Kijk of een open model uit Europa ook kan. Dan zijn jullie minder '
            . 'afhankelijk van landen buiten de EU. Test ook hoe goed het model Nederlands kan.',
        $w('gai_voorwaarden') === 'eigen' ? '' : 'Zet in de instellingen uit dat de leverancier met jullie invoer traint.',
        ...GAI_TIPS,
    ];

    return [
        'oordeel' => match (true) {
            $verkenning => 'verkenning',
            $consument => 'nee',
            !in_array(false, $gehaald, true) => 'ja',
            default => 'nog_niet',
        },
        'voorwaarden' => $voorwaarden,
        'acties' => array_values(array_filter($acties)),
        'tips' => array_values(array_filter($tips)),
    ];
}

// ---------------------------------------------------------------------------

/** @return list<string> */
function ai_vervolg(array $a, array $r, ?array $gai): array
{
    $opnieuw = 'Doe de scan opnieuw als de toepassing verandert. Ook de AI verordening en de beslishulp veranderen nog.';

    if ($r['risico'] === 'verboden') {
        return ['Stop met de toepassing, of verander hem zo dat hij niet meer verboden is.', $opnieuw];
    }

    $hoog = $r['risico'] === 'hoog';

    return array_values(array_filter([
        $hoog && in_array('gebruiksverantwoordelijke', $r['rollen'], true) ? 'Vul de IAMA in. Die beoordeelt de '
            . 'gevolgen voor de grondrechten, zoals artikel 27 vraagt. Laad daar het bestand van deze scan in.' : '',
        $hoog && in_array('aanbieder', $r['rollen'], true) ? 'Plan de conformiteitsbeoordeling. Begin op tijd met de '
            . 'documentatie.' : '',
        in_array($r['niet'], ['ai', 'uitzondering', 'opensource'], true) ? 'Kijk of het algoritme in het '
            . 'Algoritmeregister hoort.' : '',
        $gai !== null && $gai['oordeel'] !== 'ja' ? 'Werk de punten uit het standpunt over generatieve AI af, voordat '
            . 'de AI in gebruik gaat.' : '',
        ($a['gai_persoonsgegevens'] ?? '') === '' ? 'Komen er persoonsgegevens in de toepassing? Doe dan een DPIA. Ook '
            . 'die staat in deze tool.' : '',
        'Vul quickscan 2.0 in voor de beveiliging. Laad daar het bestand van deze scan in, dan staat een deel er al.',
        $opnieuw,
    ]));
}

/**
 * Alles wat pagina en rapport nodig hebben, in één keer uitgerekend.
 *
 * @return array<string,mixed>
 */
function ai_evalueer(array $a): array
{
    $r = ai_route($a);
    $gai = ($a['ai_generatief'] ?? '') === 'ja' ? gai_standpunt($a, $r) : null;

    return [
        'route' => $r,
        'klasse' => ai_klasse($r),
        'plichten' => ai_plichten($a, $r),
        'standpunt' => $gai,
        'vervolg' => ai_vervolg($a, $r, $gai),
    ];
}
