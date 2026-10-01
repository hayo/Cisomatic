<?php

/**
 * Afleidingen: wat de tool uitrekent in plaats van vraagt.
 *
 * Hier staat alleen wat de uitkomst bepaalt: welke vragen gelden, de omvang
 * van een risico en wat er in het document komt. De voorstellen die het
 * formulier alvast invult, zoals een impact of een bron, staan alleen in
 * regels.js. Die zijn een hulp bij het invullen; het antwoord telt.
 * dp_uitkomst() en dp_omvang() staan in beide, pas ze dus samen aan.
 */

declare(strict_types=1);

/** "Eén soort" of "3 soorten". */
function dp_aantal(int $aantal, string $enkelvoud, string $meervoud): string
{
    return $aantal === 1 ? 'Eén ' . $enkelvoud : $aantal . ' ' . $meervoud;
}

/** De labels van aangekruiste persoonsgegevens, als opsomming in kleine letters. */
function dp_labels(array $sleutels): string
{
    $soorten = dp_informatiesoorten();

    return cm_opsomming(array_map(
        static fn (string $s): string => mb_strtolower((string) ($soorten[$s]['label'] ?? $s), 'UTF-8'),
        $sleutels
    ));
}

/**
 * Wat de aangekruiste persoonsgegevens betekenen. Of iets gewoon of bijzonder
 * is, volgt uit de groep in de aankruislijst.
 *
 * @return array{gewoon:list<string>,identificerend:list<string>,bijzonder:list<string>,label:string}
 */
function dp_afgeleide_persoonsgegevens(array $a): array
{
    $gekozen = array_map('strval', (array) ($a['informatie_soorten'] ?? []));
    $groepen = dp_informatiegroepen();

    $gewoon = array_values(array_intersect($groepen['gewoon'], $gekozen));
    $identificerend = array_values(array_intersect($groepen['identificerend'], $gekozen));
    $bijzonder = array_values(array_intersect($groepen['bijzonder'], $gekozen));
    $totaal = count($gewoon) + count($identificerend) + count($bijzonder);

    return [
        'gewoon' => $gewoon,
        'identificerend' => $identificerend,
        'bijzonder' => $bijzonder,
        'label' => dp_aantal($totaal, 'soort', 'soorten') . ' persoonsgegevens'
            . ($bijzonder === [] ? '' : ', waarvan ' . count($bijzonder) . ' bijzonder'),
    ];
}

/**
 * De groepen betrokkenen, met elke andere groep als eigen regel.
 *
 * @return list<array{sleutel:string,label:string,omschrijving:string}>
 */
function dp_groepen(array $a): array
{
    $opties = dp_groepsopties();
    $groepen = [];

    foreach ((array) ($a['betrokkenen'] ?? []) as $sleutel) {
        if ($sleutel === 'groep_anders') {
            foreach (cm_regels((string) ($a['betrokkenen_anders'] ?? '')) as $regel) {
                $groepen[] = ['sleutel' => $sleutel, 'label' => $regel, 'omschrijving' => ''];
            }
        } elseif (isset($opties[$sleutel])) {
            $groepen[] = [
                'sleutel' => $sleutel,
                'label' => $opties[$sleutel]['label'],
                'omschrijving' => $opties[$sleutel]['omschrijving'] ?? '',
            ];
        }
    }

    return $groepen;
}

/**
 * Per groep de persoonsgegevens, of één tabel als ze voor iedereen gelijk zijn.
 * Een groep heeft alle gegevens, behalve wat maar over een deel van de groepen
 * gaat en niet over deze.
 *
 * @return list<array{titel:string,soorten:list<string>}>
 */
function dp_gegevenstabellen(array $a): array
{
    $groepen = dp_groepen($a);
    $alle = (array) ($a['informatie_soorten'] ?? []);

    $deels = (array) ($a['pg_deels'] ?? []);

    if ($deels === []) {
        return [[
            'titel' => count($groepen) === 1
                ? 'Gegevens van ' . mb_strtolower($groepen[0]['label'], 'UTF-8')
                : 'Gegevens van alle groepen',
            'soorten' => $alle,
        ]];
    }

    $tabellen = [];
    foreach (array_unique(array_column($groepen, 'sleutel')) as $sleutel) {
        $tabellen[] = [
            'titel' => $sleutel === 'groep_anders'
                ? 'Gegevens van de andere groepen'
                : 'Gegevens van ' . mb_strtolower(dp_groepsopties()[$sleutel]['label'], 'UTF-8'),
            'soorten' => array_values(array_filter($alle, static fn (string $soort): bool =>
                !in_array($soort, $deels, true)
                || in_array($sleutel, (array) ($a['pg_groepen_' . $soort] ?? []), true))),
        ];
    }

    return $tabellen;
}

/**
 * De zwaarste plek buiten de EER waar gegevens heen gaan, via de clouddienst of
 * via een subverwerker. Leeg als alles binnen de EER blijft.
 */
function dp_doorgifte(array $a): string
{
    $plekken = [];

    if (in_array($a['cloud'] ?? '', ['ja', 'mogelijk'], true)) {
        $plekken[] = (string) ($a['cloud_locatie'] ?? '');
    }
    if (($a['pg_subverwerkers'] ?? '') === 'ja') {
        $plekken[] = (string) ($a['pg_subverwerkers_locatie'] ?? '');
    }

    foreach (['buiten-eer', 'modelcontract', 'adequaatheid'] as $plek) {
        if (in_array($plek, $plekken, true)) {
            return $plek;
        }
    }

    return '';
}

/**
 * Namen om uit te kiezen bij de stromen: de groepen betrokkenen en de partijen.
 *
 * @return list<string>
 */
function dp_suggesties(array $a): array
{
    return array_values(array_unique(array_filter(array_map('trim', [
        ...array_column(dp_groepen($a), 'label'),
        ...array_column((array) ($a['partijen'] ?? []), 'organisatie'),
    ]), 'strlen')));
}

/**
 * Uitkomsten waar toon_als_uitkomst en verplicht_als_uitkomst op letten.
 * Gespiegeld in uitkomst() in regels.js.
 */
function dp_uitkomst(string $naam, array $a): string
{
    if (str_starts_with($naam, 'omvang:')) {
        $id = substr($naam, 7);

        return dp_omvang((string) ($a[$id . '_kans'] ?? ''), (string) ($a[$id . '_impact'] ?? ''));
    }

    // Of er bij een risico een nieuwe maatregel komt: gekozen, of zelf ingevuld.
    if (str_starts_with($naam, 'extra:')) {
        $id = substr($naam, 6);

        return (in_array('komt', (array) ($a[$id . '_maatregelen'] ?? []), true)
            || cm_regels((string) ($a[$id . '_extra'] ?? '')) !== []) ? 'ja' : 'nee';
    }

    return match ($naam) {
        'groepen' => count(dp_groepen($a)) >= 2 ? 'meerdere' : 'een',
        'verwerker' => array_intersect(
            array_column((array) ($a['partijen'] ?? []), 'rol'),
            ['verwerker', 'subverwerker']
        ) === [] ? 'nee' : 'ja',
        'doorgifte' => dp_doorgifte($a) === '' ? 'nee' : 'ja',
        'toestemming' => in_array('toestemming', [$a['pg_grondslag'] ?? '', $a['uitzondering'] ?? ''], true)
            ? 'ja'
            : 'nee',
        default => '',
    };
}

/**
 * Hoe de regel van een risico erbij staat: de samenvatting, de omvang, en of
 * de regel open moet. Zonder script staat hij open zolang er iets te doen is:
 * zonder antwoord, of vanaf omvang Hoog.
 *
 * @return array{samenvatting:string,niveau:string,open:bool}
 */
function dp_risico_stand(string $id, array $a): array
{
    $omvang = dp_uitkomst('omvang:' . $id, $a);

    return [
        'samenvatting' => dp_risico_samenvatting($id, $a),
        'niveau' => $omvang,
        'open' => $omvang === '' || cm_niveau_rang($omvang) >= cm_niveau_rang('h'),
    ];
}

/** "impact hoog · kans midden · omvang hoog", zoals de regel van een risico dat toont. */
function dp_risico_samenvatting(string $id, array $a): string
{
    $impact = (string) ($a[$id . '_impact'] ?? '');
    $kans = (string) ($a[$id . '_kans'] ?? '');

    if ($impact === '' || $kans === '') {
        return 'nog te beoordelen';
    }

    $klein = static fn (string $niveau): string => mb_strtolower(cm_niveau_label($niveau), 'UTF-8');
    $omvang = dp_omvang($kans, $impact);
    $kansNa = (string) ($a[$id . '_kans_na'] ?? '');
    $rest = $kansNa !== '' && dp_uitkomst('extra:' . $id, $a) === 'ja' ? dp_omvang($kansNa, $impact) : $omvang;

    return sprintf('impact %s · kans %s · omvang %s', $klein($impact), $klein($kans), $klein($omvang))
        . ($rest === $omvang ? '' : ' · met maatregelen ' . $klein($rest));
}

/**
 * De wetten en kaders die hier gelden. Het formulier toont ze met dezelfde
 * voorwaarden, zodat het script ze niet hoeft na te rekenen.
 *
 * @return list<array{tekst:string,toon_als?:array<string,list<string>>}>
 */
function dp_kaderlijst(): array
{
    return [
        ['tekst' => 'AVG: Algemene verordening gegevensbescherming'],
        ['tekst' => 'UAVG: Uitvoeringswet Algemene verordening gegevensbescherming'],
        ['tekst' => 'Archiefwet 2026, met de regels die daarop rusten'],
        ['tekst' => 'Wet open overheid'],
        ['tekst' => 'BIO: Baseline Informatiebeveiliging Overheid'],
        ['tekst' => 'VIR: Voorschrift Informatiebeveiliging Rijksdienst, binnen de Rijksdienst'],
        [
            'tekst' => 'Wet algemene bepalingen burgerservicenummer, omdat het BSN wordt gebruikt',
            'toon_als' => ['informatie_soorten' => ['bsn']],
        ],
        [
            'tekst' => 'AI verordening, omdat AI niet is uitgesloten',
            'toon_als' => ['ai' => ['mogelijk', 'onderdeel']],
        ],
    ];
}

/** @return list<string> */
function dp_kaders(array $a): array
{
    $kaders = [];

    foreach (dp_kaderlijst() as $kader) {
        if (cm_zichtbaar($kader, $a)) {
            $kaders[] = $kader['tekst'];
        }
    }

    return array_merge($kaders, cm_regels((string) ($a['aanvullende_wetgeving'] ?? '')));
}

/**
 * Elk ingevuld risico, standaard en eigen, genummerd in de volgorde van het
 * document. De omvang na maatregelen gebruikt de kans met de extra maatregel
 * erbij; zonder extra maatregel blijft de kans gelijk.
 *
 * @return list<array<string,mixed>>
 */
function dp_risico_uitkomsten(array $a): array
{
    $lijst = [];

    foreach (dp_risicos() as $risico) {
        $id = $risico['id'];

        if (!cm_zichtbaar($risico, $a) || ($a[$id . '_impact'] ?? '') === '' || ($a[$id . '_kans'] ?? '') === '') {
            continue;
        }

        $keuzes = ['al' => [], 'komt' => []];
        foreach ((array) ($a[$id . '_maatregelen'] ?? []) as $maatregel => $keuze) {
            if (isset($keuzes[$keuze])) {
                $keuzes[$keuze][] = $risico['maatregelen'][$maatregel] ?? $maatregel;
            }
        }

        $lijst[] = dp_risico_regel(
            $risico['titel'],
            $risico['tekst'],
            implode(', ', $risico['categorieen']),
            array_merge($keuzes['al'], cm_regels((string) ($a[$id . '_anders'] ?? ''))),
            (string) $a[$id . '_impact'],
            (string) $a[$id . '_kans'],
            array_merge($keuzes['komt'], cm_regels((string) ($a[$id . '_extra'] ?? ''))),
            (string) ($a[$id . '_kans_na'] ?? '')
        );
    }

    foreach ((array) ($a['eigen_risicos'] ?? []) as $rij) {
        $tekst = (string) ($rij['risico'] ?? '');

        $lijst[] = dp_risico_regel(
            $tekst,
            $tekst,
            (string) ($rij['categorieen'] ?? ''),
            cm_regels((string) ($rij['maatregelen'] ?? '')),
            (string) ($rij['impact'] ?? ''),
            (string) ($rij['kans'] ?? ''),
            cm_regels((string) ($rij['extra'] ?? '')),
            (string) ($rij['kans_na'] ?? '')
        );
    }

    foreach ($lijst as $nummer => &$regel) {
        $regel['nummer'] = $nummer + 1;
    }
    unset($regel);

    return $lijst;
}

/**
 * @param list<string> $genomen
 * @param list<string> $extra
 * @return array<string,mixed>
 */
function dp_risico_regel(
    string $titel,
    string $tekst,
    string $categorieen,
    array $genomen,
    string $impact,
    string $kans,
    array $extra,
    string $kansNa
): array {
    $kansNa = $extra === [] || $kansNa === '' ? $kans : $kansNa;

    return [
        'titel' => $titel,
        'tekst' => $tekst,
        'categorieen' => $categorieen,
        'genomen' => $genomen,
        'impact' => $impact,
        'kans' => $kans,
        'omvang' => dp_omvang($kans, $impact),
        'extra' => $extra,
        'kans_na' => $kansNa,
        'rest' => dp_omvang($kansNa, $impact),
    ];
}

/** @return list<string> */
function dp_aandachtspunten(array $a, bool $samen): array
{
    $punten = [];

    if (($a['bewaartermijn'] ?? '') === 'onbepaald') {
        $punten[] = 'Er is nog geen bewaartermijn bepaald. Zonder termijn kan de organisatie zich niet aan '
            . 'de AVG en de Archiefwet houden.';
    }

    $punten[] = match ($a['verwerkersovereenkomst'] ?? '') {
        'onderhandeling' => 'Over de verwerkersovereenkomst wordt nog onderhandeld. Die moet getekend zijn '
            . 'voordat de verwerking begint.',
        'nee' => 'Er is nog geen verwerkersovereenkomst. Die moet er zijn voordat een verwerker gegevens krijgt.',
        default => '',
    };

    if (in_array($a['ai'] ?? '', ['mogelijk', 'onderdeel'], true)) {
        $punten[] = 'AI is niet uitgesloten. Ga na of een IAMA nodig is, en of het systeem in het '
            . 'Algoritmeregister hoort.';
    }

    if (array_intersect((array) ($a['technieken'] ?? []), ['besluitvorming', 'profilering']) !== []) {
        $punten[] = 'Het systeem neemt besluiten over mensen of maakt profielen van hen. Artikel 22 van de '
            . 'AVG stelt daar eisen aan.';
    }

    if ($samen) {
        $punten[] = match ($a['informeren'] ?? '') {
            'nee' => 'Betrokkenen horen nog niet dat hun gegevens worden gebruikt. De informatieplicht moet '
                . 'nog worden geregeld.',
            'verklaring' => 'Betrokkenen horen het alleen via de privacyverklaring. Een bericht op het moment '
                . 'dat de gegevens worden verzameld, bereikt hen beter.',
            default => '',
        };

        if (($a['pg_grondslag'] ?? '') === 'gerechtvaardigd_belang') {
            $punten[] = 'De grondslag is gerechtvaardigd belang. Een overheidsorgaan kan zich daar voor zijn '
                . 'publieke taken niet op beroepen.';
        }
        if (($a['pg_proportionaliteit'] ?? '') === 'twijfel') {
            $punten[] = 'Er is twijfel of de verwerking in verhouding staat tot het doel. Leg die afweging '
                . 'voor aan de functionaris gegevensbescherming.';
        }
        if (($a['pg_subsidiariteit'] ?? '') === 'kan_minder') {
            $punten[] = 'Het doel kan ook met minder gegevens. Leg vast waarom daar niet voor is gekozen, of '
                . 'pas het ontwerp aan.';
        }
        if (($a['doelbinding'] ?? '') === 'anders') {
            $punten[] = 'De gegevens worden ook gebruikt voor een doel dat los staat van deze verwerking. '
                . 'Daar is een eigen grondslag voor nodig.';
        }
        if (in_array($a['pg_rechten'] ?? '', ['deels', 'nee'], true)) {
            $punten[] = 'Het systeem kan inzage, correctie en verwijdering niet volledig aan. Leg vast hoe een '
                . 'verzoek dan toch binnen een maand wordt afgehandeld.';
        }
    }

    return array_values(array_filter($punten));
}

/**
 * Wat geregeld moet zijn voordat de verwerking mag beginnen.
 *
 * @param list<array<string,mixed>> $risicos
 * @return list<string>
 */
function dp_blokkerend(array $a, array $risicos): array
{
    $punten = [];

    if (dp_doorgifte($a) === 'buiten-eer') {
        $punten[] = 'Gegevens gaan naar een land buiten de EER, zonder adequaatheidsbesluit en zonder '
            . 'modelcontract. Dat mag alleen met passende waarborgen uit hoofdstuk V van de AVG.';
    }

    if (($a['balans_eind'] ?? '') === 'betrokkenen') {
        $punten[] = 'Ook met de extra waarborgen weegt het belang van de betrokkenen zwaarder. Gerechtvaardigd '
            . 'belang kan dan geen grondslag zijn.';
    }

    $hoog = [];
    foreach ($risicos as $risico) {
        if (in_array($risico['rest'], ['h', 'zh'], true)) {
            $hoog[] = (string) $risico['nummer'];
        }
    }

    if ($hoog !== []) {
        $punten[] = sprintf(
            'Met de extra maatregelen blijft er een hoog risico over (risico %s). Dan moet de organisatie '
            . 'eerst de Autoriteit Persoonsgegevens raadplegen, volgens artikel 36 van de AVG.',
            cm_opsomming($hoog)
        );
    }

    return $punten;
}

/**
 * Alles wat pagina en document nodig hebben, in één keer uitgerekend.
 *
 * @return array<string,mixed>
 */
function dp_evalueer(array $a): array
{
    $samen = ($a['rol'] ?? '') === 'samen';
    $risicos = $samen ? dp_risico_uitkomsten($a) : [];

    $maatregelen = [];
    foreach ($risicos as $risico) {
        foreach ($risico['extra'] as $maatregel) {
            $maatregelen[] = $maatregel . ' (risico ' . $risico['nummer'] . ')';
        }
    }

    return [
        'samen' => $samen,
        'versie' => trim((string) ($a['versie'] ?? '')) ?: '0.1',
        'pg' => dp_afgeleide_persoonsgegevens($a),
        'groepen' => dp_groepen($a),
        'risicos' => $risicos,
        'hoogste' => cm_niveau_max(...array_column($risicos, 'omvang')),
        'hoogste_rest' => cm_niveau_max(...array_column($risicos, 'rest')),
        'maatregelen' => $maatregelen,
        'kaders' => dp_kaders($a),
        'aandachtspunten' => dp_aandachtspunten($a, $samen),
        'blokkerend' => dp_blokkerend($a, $risicos),
    ];
}
