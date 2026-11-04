<?php

/**
 * De hele vragenlijst als .odt, om buiten de webapp in te vullen: op papier,
 * of in Word of LibreOffice, bijvoorbeeld samen met collega's.
 *
 * De vragenlijst komt uit dezelfde secties als het formulier, dus hij loopt
 * nooit achter. Elke vraag is een blok met een nummer: bovenaan de vraag, met
 * wat erbij hoort, en daaronder wat je invult. Waar het formulier een vraag
 * verbergt, zegt het blok wanneer hij geldt. Keuzes krijgen een hokje, open
 * vragen een vak dat meegroeit.
 *
 * Wat het formulier uitrekent, kan de vragenlijst niet. Daarom kan een
 * formulier met 'vragenlijst' in zijn definitie een functie noemen die
 * aanvult. Die krijgt een functie mee die het nummer van een vraag geeft, en
 * levert een array met:
 * - inleiding: blokken voor de titelpagina
 * - vragen: per sleutel 'antwoord' in plaats van de keuzes of het vak (met
 *   'keuzes' of 'tabel', zie cm_odt_vraagblok()), 'noten' voor extra uitleg in
 *   het blok, 'blokken' in plaats van een uitkomstblok, en 'na' voor blokken
 *   na de vraag
 * - uitkomsten: per naam en waarde van toon_als_uitkomst een tekst, die na
 *   "Alleen als" komt
 * - bijlagen: blokken aan het eind
 * - blad: met 'vragenlijst_blad' in de definitie het hele document, op één
 *   blad. De vragenlijst maakt dan zelf niets, en de rest telt niet mee.
 */

declare(strict_types=1);

/**
 * De vragenlijst als blokken voor cm_odt().
 *
 * @return list<array<int,mixed>>
 */
function cm_vragenlijst(): array
{
    $formulier = cm_formulier();
    $secties = cm_secties();

    // Eerst de nummers, want een voorwaarde of een aanvulling kan naar een latere vraag wijzen.
    $nummers = [];
    $delen = [];
    foreach ($secties as $i => $sectie) {
        $delen[$sectie['id']] = $i + 1;
        foreach ($sectie['vragen'] as $vraag) {
            if (cm_is_invoer($vraag)) {
                $nummers[$vraag['key']] = count($nummers) + 1;
            }
        }
    }

    $nr = static fn (string $key): string => 'vraag ' . ($nummers[$key] ?? '?');
    $haak = $formulier['vragenlijst'] ?? null;
    $extra = is_string($haak) ? $haak($nr) : [];
    $extra += ['inleiding' => [], 'vragen' => [], 'uitkomsten' => [], 'bijlagen' => []];

    // Een check op één blad maakt het formulier helemaal zelf.
    if (!empty($formulier['vragenlijst_blad'])) {
        return $extra['blad'];
    }

    $blokken = [
        ['h1', 'Vragenlijst: ' . $formulier['titel']],
        ['alinea', $formulier['intro']],
        ['term', 'Versie', 'Cisomatic ' . cm_versie() . ', ' . cm_datum_nl(new DateTimeImmutable('now'))],
        ['h3', 'Zo vul je dit in'],
        ['lijst', [
            'Elke vraag is een blok met een nummer. In de blauwe balk staat de vraag, met wat erbij hoort. Wat je '
                . 'invult, staat eronder in wit.',
            'Kies je een antwoord, zet dan een kruis in het hokje. In Word of LibreOffice zet je een X in de plaats '
                . 'van het hokje.',
            'Schrijf een open antwoord in het witte vak. Het vak groeit mee als je typt.',
            'Bij sommige vragen staat wanneer ze gelden. Geldt een vraag niet voor jou, sla hem dan over. Hetzelfde '
                . 'geldt voor hele delen.',
            'Bij "mag leeg" hoef je niets in te vullen.',
            'Wil je het document? Neem je antwoorden dan over in het formulier op de site. Dat maakt er het '
                . 'document van.',
        ]],
        ...$extra['inleiding'],
    ];

    foreach ($secties as $i => $sectie) {
        $blokken[] = ['h2', ($i + 1) . '. ' . $sectie['titel']];

        $stap = $formulier['stappen'][(int) ($sectie['stap'] ?? 0)] ?? '';
        if ($stap !== '' && $stap !== $sectie['titel']) {
            $blokken[] = ['noot', $stap];
        }
        $wanneer = cm_vragenlijst_voorwaarde($sectie, $nummers, $extra['uitkomsten']);
        if ($wanneer !== '') {
            $blokken[] = ['citaat', 'Vul dit deel alleen in als ' . $wanneer . '.'];
        }
        if (!empty($sectie['intro'])) {
            $blokken[] = ['alinea', $sectie['intro']];
        }

        // Staat een vraag onder een tussenkop met dezelfde voorwaarde, dan zegt alleen de kop wanneer hij geldt.
        // Vragen met dezelfde 'groep' komen samen in één blok, onder hun kop.
        $kop = null;
        $groep = null;
        $sluit = static function () use (&$groep, &$blokken): void {
            if ($groep !== null) {
                $blokken[] = ['vraagblok', ['kop' => $groep['kop'], 'delen' => $groep['delen']]];
                array_push($blokken, ...$groep['na']);
                $groep = null;
            }
        };

        foreach ($sectie['vragen'] as $vraag) {
            $regels = [$vraag['toon_als'] ?? [], $vraag['toon_als_uitkomst'] ?? []];
            if ($vraag['type'] === 'kop') {
                $kop = $regels === [[], []] ? null : $regels;
            } elseif ($kop !== null && $regels === $kop) {
                unset($vraag['toon_als'], $vraag['toon_als_uitkomst']);
            } elseif ($kop !== null && ($vraag['toon_als'] ?? []) === $kop[0]
                && array_diff_key($vraag['toon_als_uitkomst'] ?? [], $kop[1]) !== []
                && array_intersect_key($vraag['toon_als_uitkomst'] ?? [], $kop[1]) == $kop[1]) {
                // Alleen wat de vraag extra vraagt, bovenop de kop.
                $vraag['toon_als_uitkomst'] = array_diff_key($vraag['toon_als_uitkomst'], $kop[1]);
                unset($vraag['toon_als']);
            }

            if ($groep !== null && ($vraag['groep'] ?? null) !== $groep['id']) {
                $sluit();
            }

            if ($vraag['type'] === 'kop' && isset($vraag['groep'])) {
                $sluit();
                $wanneer = cm_vragenlijst_voorwaarde($vraag, $nummers, $extra['uitkomsten']);
                $groep = ['id' => $vraag['groep'], 'delen' => [], 'na' => [], 'kop' => [
                    'titel' => $vraag['label'],
                    'noten' => array_values(array_filter([
                        $wanneer === '' ? '' : '→ Alleen als ' . $wanneer . '.',
                        (string) ($vraag['hint'] ?? ''),
                    ])),
                ]];
                continue;
            }

            $uit = cm_vragenlijst_vraag($vraag, $nummers, $extra, $delen);
            if ($groep === null) {
                array_push($blokken, ...$uit);
                continue;
            }
            foreach ($uit as $blok) {
                if ($blok[0] === 'vraagblok') {
                    $groep['delen'][] = cm_vragenlijst_in_groep($blok[1]);
                } else {
                    $groep['na'][] = $blok;
                }
            }
        }
        $sluit();
    }

    return [...$blokken, ...$extra['bijlagen']];
}

/**
 * Eén vraag als blokken. Een tussenkop wordt een kop, een uitkomstblok met een
 * vaste lijst een opsomming, en elke vraag een blok om in te vullen.
 *
 * @param array<string,int> $nummers
 * @param array<string,array<mixed>> $extra
 * @param array<string,int> $delen
 * @return list<array<int,mixed>>
 */
function cm_vragenlijst_vraag(array $vraag, array $nummers, array $extra, array $delen): array
{
    $key = $vraag['key'];
    $eigen = $extra['vragen'][$key] ?? [];
    $wanneer = cm_vragenlijst_voorwaarde($vraag, $nummers, $extra['uitkomsten']);
    $voorwaarde = $wanneer === '' ? [] : [['noot', 'Alleen als ' . $wanneer . '.']];

    if ($vraag['type'] === 'kop') {
        return [
            ['h3', $vraag['label']],
            ...$voorwaarde,
            ...(empty($vraag['hint']) ? [] : [['alinea', $vraag['hint']]]),
            ...(empty($vraag['lijst']) ? [] : [['lijst', $vraag['lijst']]]),
        ];
    }

    // Een uitkomstblok rekent het formulier uit. Een vaste lijst kan op papier
    // ook, met bij elk punt wanneer het geldt. Anders slaat de vragenlijst het
    // over, tenzij het formulier het aanvult.
    if ($vraag['type'] === 'afgeleid') {
        if (isset($eigen['blokken'])) {
            return [...$voorwaarde, ...$eigen['blokken'], ...($eigen['na'] ?? [])];
        }
        if (!isset($vraag['lijst'])) {
            return [];
        }

        return [['h4', rtrim($vraag['label'], ':')], ...$voorwaarde, ['lijst', array_map(static function (array $item) use ($nummers, $extra): string {
            $als = cm_vragenlijst_voorwaarde($item, $nummers, $extra['uitkomsten']);

            return $item['tekst'] . ($als === '' ? '' : ' (alleen als ' . $als . ')');
        }, $vraag['lijst'])]];
    }

    $soort = match ($vraag['type']) {
        'checkbox' => 'kruis aan wat past',
        'radio', 'select' => 'kies één',
        'maatregelen' => 'kies per regel één',
        default => '',
    };
    // "Kruis alles aan wat past" staat al achter de vraag.
    $hint = trim((string) preg_replace('/^(Kruis|Vink) alles aan wat (past|kan gelden)\.\s*/u', '', (string) ($vraag['hint'] ?? '')));
    $leeg = !empty($vraag['optioneel']) || (empty($vraag['verplicht']) && empty($vraag['verplicht_als'])
        && empty($vraag['verplicht_als_uitkomst']));

    // Achter een keuze kan staan waar je daarna heen gaat: bij de vraag die de
    // route bepaalt de delen, en wat het formulier zelf meegeeft in 'volgende'.
    $route = $key === (cm_formulier()['route'] ?? '') && $vraag['type'] === 'radio'
        ? cm_vragenlijst_route($vraag, $delen)
        : [];
    $volgende = ($eigen['volgende'] ?? []) + $route;

    $blok = [
        'nr' => (string) $nummers[$key],
        'vraag' => $vraag['label'],
        'bijzin' => implode(', ', array_filter([$soort, $leeg ? 'mag leeg' : ''])),
        'noten' => array_values(array_filter([
            $voorwaarde === [] ? '' : '→ ' . $voorwaarde[0][1],
            $hint,
            empty($vraag['opties_uit']) ? '' : 'Kies alleen uit wat je bij vraag ' . ($nummers[$vraag['opties_uit']] ?? '?')
                . ' aankruiste.',
            ...cm_vragenlijst_kolomkeuzes($vraag),
            ...($eigen['noten'] ?? []),
        ])),
        ...($eigen['antwoord'] ?? cm_vragenlijst_antwoord($vraag, $volgende)),
    ];

    return [['vraagblok', $blok], ...($eigen['na'] ?? [])];
}

/**
 * Een vraag in een groep heeft maar twee kolommen, dus geen tabel. Een rij
 * van een tabel wordt een regel, met de hokjes als tekst erachter, zoals
 * "☐ Is er al   ☐ Komt er   ☐ Niet nodig".
 *
 * @param array<string,mixed> $blok
 * @return array<string,mixed>
 */
function cm_vragenlijst_in_groep(array $blok): array
{
    if (!isset($blok['tabel'])) {
        return $blok;
    }

    $tabel = $blok['tabel'];
    unset($blok['tabel']);

    if (isset($tabel['hoogte'])) {
        return $blok + ['vak' => count($tabel['rijen'])];
    }

    $blok['keuzes'] = array_map(static function (array $rij) use ($tabel): array {
        $rij = array_values($rij);
        $label = (array) array_shift($rij);
        $hokjes = [];
        foreach ($rij as $i => $cel) {
            $hokjes[] = $cel === '☐' ? '☐ ' . $tabel['kop'][$i + 1] : $tabel['kop'][$i + 1] . ': ' . implode(' ', (array) $cel);
        }

        // Harde spaties, want gewone spaties na elkaar worden er in het document één.
        return ['los' => true, ...$label, implode(str_repeat("\u{00A0}", 5), $hokjes)];
    }, $tabel['rijen']);

    return $blok;
}

/**
 * Wat je invult, voor cm_odt_vraagblok(): keuzes met een hokje, een vak, of
 * een tabel.
 *
 * @param array<string,string> $volgende waarde => waar je daarna heen gaat
 * @return array<string,mixed>
 */
function cm_vragenlijst_antwoord(array $vraag, array $volgende): array
{
    switch ($vraag['type']) {
        case 'radio':
        case 'checkbox':
        case 'select':
            $keuzes = [];
            $groep = null;
            foreach ($vraag['opties'] as $waarde => $optie) {
                // Bij een keuzelijst is de lege keuze alleen een aanwijzing, zoals "Kies de kans".
                if ((string) $waarde === '') {
                    continue;
                }
                if (($optie['groep'] ?? null) !== $groep && ($optie['groep'] ?? null) !== null) {
                    $keuzes[] = ['kop' => $optie['groep']];
                }
                $groep = $optie['groep'] ?? null;
                $keuzes[] = array_values(array_filter([
                    $optie['label'],
                    (string) ($optie['hint'] ?? ''),
                    (string) ($optie['uitleg'] ?? ''),
                    $volgende[(string) $waarde] ?? '',
                ], 'strlen'));
            }

            return ['keuzes' => $keuzes];

        case 'maatregelen':
            return ['tabel' => [
                'kop' => ['Maatregel', ...array_values(CM_MAATREGELKEUZES)],
                'rijen' => array_map(
                    static fn (array $o): array => [$o['label'], ...array_fill(0, count(CM_MAATREGELKEUZES), '☐')],
                    array_values($vraag['opties'])
                ),
                'breedtes' => [9.6, 2.0, 2.0, 2.0],
                'voor' => '',
            ]];

        case 'rijen':
            // Een vaste tabel met lege rijen: een tekstvak telt dubbel zo breed.
            $gewichten = array_map(static fn (array $k): int => ($k['type'] ?? '') === 'textarea' ? 2 : 1, $vraag['kolommen']);
            $eenheid = 15.6 / array_sum($gewichten);

            return ['tabel' => [
                'kop' => array_values(array_map(static fn (array $k): string => $k['label'], $vraag['kolommen'])),
                'rijen' => array_fill(0, 5, array_fill(0, count($vraag['kolommen']), '')),
                'breedtes' => array_values(array_map(static fn (int $g): float => round($g * $eenheid, 2), $gewichten)),
                'voor' => array_map('strval', range(1, 5)),
                'hoogte' => 1.0,
            ]];

        case 'textarea':
            return ['vak' => (int) ($vraag['hoogte'] ?? 3)];

        default:
            return ['vak' => 1];
    }
}

/**
 * Bij een lijst met rijen: de keuzes van een kolom met een keuzelijst, want
 * die passen niet in de tabel.
 *
 * @return list<string>
 */
function cm_vragenlijst_kolomkeuzes(array $vraag): array
{
    $zinnen = [];

    foreach ($vraag['kolommen'] ?? [] as $kolom) {
        if (($kolom['type'] ?? '') !== 'select') {
            continue;
        }
        $labels = array_values(array_map(
            static fn (array $o): string => '‘' . $o['label'] . '’',
            array_filter($kolom['opties'], static fn (string|int $w): bool => (string) $w !== '', ARRAY_FILTER_USE_KEY)
        ));
        $zinnen[] = rtrim($kolom['label'], ':?') . ': kies uit ' . implode(', ', array_slice($labels, 0, -1))
            . (count($labels) > 1 ? ' of ' : '') . end($labels) . '.';
    }

    return $zinnen;
}

/**
 * Bij de vraag die de route bepaalt: per keuze een zin met de delen die je
 * daarna invult. Een deel dat ook van een andere vraag afhangt, krijgt "soms".
 *
 * @param array<string,int> $delen
 * @return array<string,string> waarde => zin
 */
function cm_vragenlijst_route(array $vraag, array $delen): array
{
    $zinnen = [];
    $secties = array_column(cm_secties(), null, 'id');

    foreach ($vraag['opties'] as $waarde => $optie) {
        $wel = [];
        foreach ($delen as $id => $deel) {
            $regels = $secties[$id]['toon_als'] ?? [];
            $route = $regels[$vraag['key']] ?? null;

            if ($route !== null && !in_array((string) $waarde, array_map('strval', (array) $route), true)) {
                continue;
            }

            $soms = count($regels) > ($route === null ? 0 : 1) || !empty($secties[$id]['toon_als_uitkomst']);
            $wel[] = $deel . ($soms ? ' (soms)' : '');
        }

        $zinnen[(string) $waarde] = '→ Je vult de delen ' . cm_opsomming($wel) . ' in.';
    }

    return $zinnen;
}

/**
 * Wanneer een vraag of deel geldt, als bijzin na "Alleen als". Kies je bij een
 * vraag bijna alles, dan zegt de zin welke keuze níet past.
 *
 * @param array<string,int> $nummers
 * @param array<string,array<string,string>> $uitkomsten
 */
function cm_vragenlijst_voorwaarde(array $item, array $nummers, array|callable $uitkomsten): string
{
    $vragen = cm_alle_vragen();
    $delen = [];

    foreach ($item['toon_als'] ?? [] as $key => $waarden) {
        $vraag = $vragen[$key] ?? ['type' => '', 'opties' => []];
        $waarden = array_map('strval', (array) $waarden);
        $opties = array_values(array_filter(array_map('strval', array_keys($vraag['opties'] ?? [])), 'strlen'));
        $label = static fn (string $w): string => '‘' . ($vraag['opties'][$w]['label'] ?? $w) . '’';
        $werkwoord = $vraag['type'] === 'checkbox' ? 'aankruiste' : 'koos';
        $niet = array_diff($opties, $waarden);

        $delen[] = count($niet) > 0 && count($niet) < count($waarden)
            ? 'je bij vraag ' . ($nummers[$key] ?? '?') . ' niet ' . cm_opsomming(array_map($label, $niet)) . ' ' . $werkwoord
            : 'je bij vraag ' . ($nummers[$key] ?? '?') . ' ' . implode(' of ', array_map($label, $waarden)) . ' ' . $werkwoord;
    }

    // Wat het formulier uitrekent, zegt het formulier zelf: als lijst per naam
    // en waarde, of als functie. Een lege tekst betekent: zeg hier niets over.
    foreach ($item['toon_als_uitkomst'] ?? [] as $naam => $waarden) {
        $waarden = array_map('strval', (array) $waarden);
        $tekst = is_callable($uitkomsten)
            ? (string) $uitkomsten((string) $naam, $waarden)
            : implode(' of ', array_map(
                static fn (string $w): string => $uitkomsten[$naam][$w] ?? 'het formulier dat zo uitrekent',
                $waarden
            ));
        if ($tekst !== '') {
            $delen[] = $tekst;
        }
    }

    return implode(', en ', $delen);
}

/** Stuurt de vragenlijst als .odt-download, als het formulier er een heeft. */
function cm_stuur_vragenlijst(): never
{
    $blad = !empty(cm_formulier()['vragenlijst_blad']);
    cm_stuur_odt(cm_vragenlijst(), cm_bestandsnaam($blad ? 'check' : 'vragenlijst', 'odt'), '', '', $blad);
}
