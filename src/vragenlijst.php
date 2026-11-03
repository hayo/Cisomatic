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
    $extra = isset($formulier['vragenlijst']) ? $formulier['vragenlijst']($nr) : [];
    $extra += ['inleiding' => [], 'vragen' => [], 'uitkomsten' => [], 'bijlagen' => []];

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
        if (!empty($sectie['toon_als']) || !empty($sectie['toon_als_uitkomst'])) {
            $blokken[] = ['citaat', 'Vul dit deel alleen in als '
                . cm_vragenlijst_voorwaarde($sectie, $nummers, $extra['uitkomsten']) . '.'];
        }
        if (!empty($sectie['intro'])) {
            $blokken[] = ['alinea', $sectie['intro']];
        }

        // Staat een vraag onder een tussenkop met dezelfde voorwaarde, dan zegt alleen de kop wanneer hij geldt.
        $kop = null;
        foreach ($sectie['vragen'] as $vraag) {
            if ($vraag['type'] === 'kop') {
                $kop = $vraag['toon_als'] ?? null;
            } elseif ($kop !== null && ($vraag['toon_als'] ?? null) === $kop) {
                unset($vraag['toon_als']);
            }
            array_push($blokken, ...cm_vragenlijst_vraag($vraag, $nummers, $extra, $delen));
        }
    }

    return [...$blokken, ...$extra['bijlagen']];
}

/**
 * Eén vraag als blokken: het nummer en de vraag, de hint, wanneer hij geldt,
 * en de keuzes of het vak.
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
    $voorwaarde = empty($vraag['toon_als']) && empty($vraag['toon_als_uitkomst'])
        ? []
        : [['noot', 'Alleen als ' . cm_vragenlijst_voorwaarde($vraag, $nummers, $extra['uitkomsten']) . '.']];

    if ($vraag['type'] === 'kop') {
        return [
            ['h3', $vraag['label']],
            ...$voorwaarde,
            ...(empty($vraag['hint']) ? [] : [['alinea', $vraag['hint']]]),
            ...(empty($vraag['lijst']) ? [] : [['lijst', $vraag['lijst']]]),
        ];
    }

    // Een uitkomstblok rekent het formulier uit. Zonder aanvulling slaat de vragenlijst het over.
    if ($vraag['type'] === 'afgeleid') {
        return isset($eigen['blokken']) ? [...$voorwaarde, ...$eigen['blokken'], ...($eigen['na'] ?? [])] : [];
    }

    $soort = match ($vraag['type']) {
        'checkbox' => 'kruis aan wat past',
        'radio', 'select' => 'kies één',
        default => '',
    };
    // "Kruis alles aan wat past" staat al achter de vraag.
    $hint = trim((string) preg_replace('/^Kruis alles aan wat past\.\s*/u', '', (string) ($vraag['hint'] ?? '')));
    $leeg = !empty($vraag['optioneel']) || (empty($vraag['verplicht']) && empty($vraag['verplicht_als'])
        && empty($vraag['verplicht_als_uitkomst']));

    // Bij de vraag die de route bepaalt, zegt elke keuze welke delen je daarna invult.
    $route = $key === (cm_formulier()['route'] ?? '') ? cm_vragenlijst_route($vraag, $delen) : [];
    $keuzes = [];
    foreach ($vraag['opties'] ?? [] as $waarde => $optie) {
        $keuzes[] = [$optie['label'], trim(($optie['hint'] ?? '') . (isset($route[$waarde]) ? ' ' . $route[$waarde] : ''))];
    }

    $blok = [
        'nr' => (string) $nummers[$key],
        'vraag' => $vraag['label'],
        'bijzin' => implode(', ', array_filter([$soort, $leeg ? 'mag leeg' : ''])),
        'noten' => array_values(array_filter([
            $voorwaarde === [] ? '' : '→ ' . $voorwaarde[0][1],
            $hint,
            ...($eigen['noten'] ?? []),
        ])),
        ...($eigen['antwoord'] ?? match ($vraag['type']) {
            'radio', 'checkbox', 'select' => ['keuzes' => $keuzes],
            'text', 'email' => ['vak' => 1],
            'textarea' => ['vak' => (int) ($vraag['hoogte'] ?? 3)],
            default => ['vak' => 4],
        }),
    ];

    return [['vraagblok', $blok], ...($eigen['na'] ?? [])];
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
function cm_vragenlijst_voorwaarde(array $item, array $nummers, array $uitkomsten): string
{
    $vragen = cm_alle_vragen();
    $delen = [];

    foreach ($item['toon_als'] ?? [] as $key => $waarden) {
        $vraag = $vragen[$key];
        $waarden = array_map('strval', (array) $waarden);
        $opties = array_map('strval', array_keys($vraag['opties'] ?? []));
        $label = static fn (string $w): string => '‘' . ($vraag['opties'][$w]['label'] ?? $w) . '’';
        $werkwoord = $vraag['type'] === 'checkbox' ? 'aankruiste' : 'koos';
        $niet = array_diff($opties, $waarden);

        $delen[] = count($niet) > 0 && count($niet) < count($waarden)
            ? 'je bij vraag ' . $nummers[$key] . ' niet ' . cm_opsomming(array_map($label, $niet)) . ' ' . $werkwoord
            : 'je bij vraag ' . $nummers[$key] . ' ' . implode(' of ', array_map($label, $waarden)) . ' ' . $werkwoord;
    }

    foreach ($item['toon_als_uitkomst'] ?? [] as $naam => $waarden) {
        foreach ((array) $waarden as $waarde) {
            $delen[] = $uitkomsten[$naam][(string) $waarde] ?? 'de uitkomst ' . $naam . ' ' . $waarde . ' is';
        }
    }

    return implode(', en ', $delen);
}

/** Stuurt de vragenlijst als .odt-download, als het formulier er een heeft. */
function cm_stuur_vragenlijst(): never
{
    cm_stuur_odt(cm_vragenlijst(), cm_bestandsnaam('vragenlijst', 'odt'));
}
