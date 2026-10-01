<?php

/**
 * Afleidingen.
 *
 * Een aantal antwoorden in de quickscan ligt al vast zodra je een eerdere
 * vraag hebt beantwoord. Die vragen we dus niet meer, maar rekenen we uit.
 * Elke afleiding geeft naast de waarde ook de redenen terug, zodat in het
 * formulier en in het advies te zien is waar hij vandaan komt.
 *
 * Let op: de JavaScript in regels.js herhaalt deze regels, zodat het
 * formulier meteen laat zien wat eruit rolt. PHP blijft de bron van waarheid;
 * pas je hier iets aan, pas het daar dan ook aan.
 */

declare(strict_types=1);

/**
 * Ondergrens die de AVG aan integriteit en vertrouwelijkheid oplegt.
 * Staat letterlijk zo in de I- en V-tabellen van de quickscan.
 */
function qs_avg_minimum(string $pgCategorieen, string $pgBijzonder): string
{
    if ($pgBijzonder === 'meerdere') {
        return 'zh';
    }

    if ($pgBijzonder === 'een' || $pgCategorieen === 'meerdere') {
        return 'h';
    }

    if ($pgCategorieen === 'een') {
        return 'm';
    }

    return 'zl';
}

/** Het belang van het proces vertaald naar een startwaarde voor B en I. */
function qs_niveau_uit_proces(string $procesClassificatie): string
{
    return match ($procesClassificatie) {
        'kritisch-strategisch' => 'h',
        'strategisch' => 'm',
        'bijdragend' => 'l',
        default => 'zl',
    };
}

/**
 * Voorgestelde beschikbaarheidseis. Vertrekt vanuit het belang van het proces
 * en gaat een stap omhoog als er veel of kritieke processen van dit systeem
 * afhankelijk worden: die erven immers de uitval.
 */
function qs_voorstel_beschikbaarheid(array $a): string
{
    $proces = (string) ($a['proces_classificatie'] ?? '');

    if ($proces === '') {
        return '';
    }

    $niveau = qs_niveau_uit_proces($proces);

    if (($a['systemen_afhankelijk'] ?? '') === 'veel') {
        $niveau = qs_niveau_omhoog($niveau);
    }

    return $niveau;
}

/** Eén stap omhoog in de BIV-schaal, met Zeer hoog als plafond. */
function qs_niveau_omhoog(string $niveau): string
{
    return ['zl' => 'l', 'l' => 'm', 'm' => 'h', 'h' => 'zh', 'zh' => 'zh'][$niveau] ?? $niveau;
}

/** De zware categorieën bijzondere persoonsgegevens. */
function qs_zware_bijzondere_gegevens(): array
{
    return ['gezondheid', 'strafrecht', 'biometrie'];
}

/** Voorgestelde RPO, die volgt uit hoe lang het systeem plat mag liggen. */
function qs_voorstel_rpo(array $a): string
{
    return match ((string) ($a['beschikbaarheid'] ?? '')) {
        'zh' => '1-uur',
        'h' => '4-uur',
        'm' => '24-uur',
        'l' => '24-uur',
        'zl' => '1-week',
        default => '',
    };
}

/**
 * Afgeleide integriteitseis.
 *
 * @return array{niveau:string,redenen:list<string>}
 */
function qs_afgeleide_integriteit(array $a): array
{
    $pg = qs_afgeleide_persoonsgegevens($a);

    // Harde ondergrens: dit staat letterlijk in de integriteitstabel van de
    // quickscan, als gevolg van het AVG beginsel 'Juistheid'.
    $ondergrens = qs_avg_minimum($pg['categorieen'], $pg['bijzonder']);
    $ondergrensReden = $ondergrens === 'zl'
        ? ''
        : sprintf(
            'Het AVG beginsel ‘Juistheid’ legt bij deze persoonsgegevens een ondergrens van %s op.',
            cm_niveau_label($ondergrens)
        );

    // Voorstel: het belang van het proces. Dit staat níét in het brondocument —
    // het is een redelijke startwaarde, geen regel. Je mag er dus onder gaan
    // zitten, zolang je boven de ondergrens blijft.
    $voorstel = qs_niveau_uit_proces((string) ($a['proces_classificatie'] ?? ''));
    $voorstelReden = ($a['proces_classificatie'] ?? '') === ''
        ? ''
        : sprintf(
            'Het proces is %s; als startwaarde stelt de tool daar %s bij voor. Dat is een voorstel, geen '
            . 'regel uit de quickscan.',
            mb_strtolower(qs_antwoord_tekst('proces_classificatie', $a), 'UTF-8'),
            cm_niveau_label($voorstel)
        );

    $niveau = cm_niveau_rang($voorstel) > cm_niveau_rang($ondergrens) ? $voorstel : $ondergrens;

    $redenen = array_values(array_filter([$ondergrensReden, $voorstelReden]));
    if ($redenen === []) {
        $redenen[] = 'Er zijn geen persoonsgegevens en het proces is ondersteunend, dus er gelden '
            . 'geen bijzondere eisen aan de juistheid van de gegevens.';
    }

    return [
        'niveau' => $niveau,
        'ondergrens' => $ondergrens,
        'voorstel' => $voorstel,
        'redenen' => $redenen,
    ];
}

/**
 * Afgeleide vertrouwelijkheidseis. Volgt rechtstreeks uit het soort informatie
 * en uit de persoonsgegevens die erin zitten.
 *
 * @return array{niveau:string,redenen:list<string>}
 */
function qs_afgeleide_vertrouwelijkheid(array $a): array
{
    $pg = qs_afgeleide_persoonsgegevens($a);
    $rubricering = (string) ($a['rubricering'] ?? '');

    $uitRubricering = match ($rubricering) {
        'staatsgeheim' => 'zh',
        'dep-vertrouwelijk' => 'h',
        'intern' => 'm',
        'openbaar' => 'zl',
        default => 'zl',
    };

    $uitAvg = qs_avg_minimum($pg['categorieen'], $pg['bijzonder']);

    // Beide ondergrenzen staan letterlijk in de vertrouwelijkheidstabel.
    $ondergrens = cm_niveau_rang($uitAvg) > cm_niveau_rang($uitRubricering) ? $uitAvg : $uitRubricering;
    $redenen = [];

    if ($rubricering !== '') {
        $redenen[] = sprintf(
            'De informatie is aangemerkt als ‘%s’; dat komt overeen met vertrouwelijkheid %s.',
            qs_antwoord_tekst('rubricering', $a),
            cm_niveau_label($uitRubricering)
        );
    }

    if ($uitAvg !== 'zl') {
        $redenen[] = sprintf(
            'De verwerkte persoonsgegevens leggen een ondergrens van %s op.',
            cm_niveau_label($uitAvg)
        );
    }

    $niveau = $ondergrens;

    // Informatie die beschermd moet worden tegen inlichtingendiensten,
    // terreurgroepen of georganiseerde criminaliteit kan niet tegelijk laag
    // vertrouwelijk zijn. Ook dit is een afleiding van de tool, geen regel.
    $voorstel = $niveau;
    if (qs_afgeleid_dreigingsprofiel($a)['niveau'] === 'hoog'
        && cm_niveau_rang($niveau) < cm_niveau_rang('h')) {
        $voorstel = 'h';
        $niveau = 'h';
        $redenen[] = 'Het dreigingsprofiel is hoog. Informatie die bescherming nodig heeft tegen '
            . 'inlichtingendiensten, terreurgroepen of georganiseerde criminaliteit kan niet '
            . 'tegelijk laag vertrouwelijk zijn, dus stelt de tool Hoog voor.';
    }

    if ($redenen === []) {
        $redenen[] = 'Vul hierboven in hoe gevoelig de informatie is, dan verschijnt hier de eis.';
    }

    return [
        'niveau' => $niveau,
        'ondergrens' => $ondergrens,
        'voorstel' => $voorstel,
        'redenen' => $redenen,
    ];
}

/**
 * De uiteindelijke B, I en V: de afleiding, eventueel handmatig verhoogd.
 *
 * @return array{
 *     b:string, i:string, v:string,
 *     afgeleid:array{i:string,v:string},
 *     redenen:array{i:list<string>,v:list<string>},
 *     verhoogd:array<string,string>
 * }
 */
function qs_effectieve_biv(array $a): array
{
    $integriteit = qs_afgeleide_integriteit($a);
    $vertrouwelijkheid = qs_afgeleide_vertrouwelijkheid($a);

    $i = $integriteit['niveau'];
    $v = $vertrouwelijkheid['niveau'];
    $bijgesteld = [];

    foreach (['i' => 'integriteit', 'v' => 'vertrouwelijkheid'] as $letter => $naam) {
        $gekozen = (string) ($a[$naam . '_bijstelling'] ?? '');
        $bron = $letter === 'i' ? $integriteit : $vertrouwelijkheid;

        if ($gekozen === '' || !qs_bijstelling_toegestaan($a, $bron, $gekozen)) {
            continue;
        }

        $bijgesteld[$letter] = $bron['niveau'];

        if ($letter === 'i') { $i = $gekozen; } else { $v = $gekozen; }
    }

    return [
        'b' => (string) ($a['beschikbaarheid'] ?? 'zl'),
        'i' => $i,
        'v' => $v,
        'afgeleid' => ['i' => $integriteit['niveau'], 'v' => $vertrouwelijkheid['niveau']],
        'ondergrens' => ['i' => $integriteit['ondergrens'], 'v' => $vertrouwelijkheid['ondergrens']],
        'redenen' => ['i' => $integriteit['redenen'], 'v' => $vertrouwelijkheid['redenen']],
        'bijgesteld' => $bijgesteld,
    ];
}

/**
 * Of een handmatige bijstelling mag.
 *
 * Onderscheid dat het brondocument ook maakt: de ondergrens uit de AVG en uit
 * het vertrouwelijkheidsniveau van de informatie ligt vast. Wat de tool daar
 * bovenop voorstelt op basis van het procesbelang is een startwaarde, geen
 * regel — daar mag iedereen onder gaan zitten, zolang de ondergrens maar staat.
 * Alleen de beoordelaar mag in stap 2 ook onder de ondergrens, gemotiveerd.
 *
 * @param array{niveau:string,ondergrens:string,voorstel:string} $bron
 */
function qs_bijstelling_toegestaan(array $a, array $bron, string $gekozen): bool
{
    if ($gekozen === '' || $gekozen === $bron['niveau']) {
        return false;
    }

    if (($a['rol'] ?? 'behoeftesteller') === 'beoordelaar') {
        return true;
    }

    return cm_niveau_rang($gekozen) >= cm_niveau_rang($bron['ondergrens']);
}

/**
 * Controle bovenop de invulplicht: een behoeftesteller mag een afgeleide eis
 * niet onder de ondergrens bijstellen; de beoordelaar wel.
 *
 * @return array<string,string> foutmelding per vraagsleutel
 */
function qs_valideer_bijstelling(array $a): array
{
    if (($a['te_beschermen_informatie'] ?? '') !== 'ja') {
        return [];
    }

    $fouten = [];

    foreach (['i' => 'integriteit', 'v' => 'vertrouwelijkheid'] as $letter => $naam) {
        $gekozen = (string) ($a[$naam . '_bijstelling'] ?? '');

        if ($gekozen === '') {
            continue;
        }

        $bron = $letter === 'i' ? qs_afgeleide_integriteit($a) : qs_afgeleide_vertrouwelijkheid($a);

        if (qs_bijstelling_toegestaan($a, $bron, $gekozen)) {
            continue;
        }

        $fouten[$naam . '_bijstelling'] = $gekozen === $bron['niveau']
            ? sprintf('Dat is hetzelfde als de afgeleide eis (%s).', cm_niveau_label($bron['niveau']))
            : sprintf(
                'De ondergrens is %s en die ligt vast in de quickscan zelf. Daaronder bijstellen '
                . 'kan alleen de beoordelaar in stap 2, met een motivatie.',
                cm_niveau_label($bron['ondergrens'])
            );
    }

    return $fouten;
}

/**
 * De motivatie die de tool zelf al kan opschrijven. Zo staat er altijd iets
 * vast over hoe een niveau tot stand kwam, ook als niemand iets typt.
 */
function qs_motivatie_voorstel(string $voor, array $a): string
{
    if ($voor === 'beschikbaarheid') {
        $voorstel = qs_voorstel_beschikbaarheid($a);

        if ($voorstel === '') {
            return '';
        }

        $tekst = sprintf(
            'Voorgesteld op %s omdat het proces is aangemerkt als %s.',
            cm_niveau_label($voorstel),
            mb_strtolower(qs_antwoord_tekst('proces_classificatie', $a), 'UTF-8')
        );

        if (($a['systemen_afhankelijk'] ?? '') === 'veel') {
            $tekst .= ' Een niveau hoger dan het procesbelang alleen, omdat veel processen of een '
                . 'kritiek proces van dit systeem afhankelijk worden en daarmee de uitval erven.';
        }

        return $tekst;
    }

    if ($voor === 'integriteit') {
        return implode(' ', qs_afgeleide_integriteit($a)['redenen']);
    }

    if ($voor === 'vertrouwelijkheid') {
        return implode(' ', qs_afgeleide_vertrouwelijkheid($a)['redenen']);
    }

    if ($voor === 'dreiging') {
        return implode(' ', qs_afgeleid_dreigingsprofiel($a)['redenen']);
    }

    if ($voor === 'rpo') {
        $beschikbaarheid = (string) ($a['beschikbaarheid'] ?? '');
        $voorstel = qs_voorstel_rpo($a);

        // 'Niets' en 'Niet van toepassing' passen niet in deze zin.
        if ($voorstel === '' || $beschikbaarheid === ''
            || in_array($a['rpo'] ?? '', ['', 'geen', 'nvt'], true)) {
            return '';
        }

        return sprintf(
            'Past bij de beschikbaarheidseis %s: als het systeem binnen die tijd weer moet draaien, '
            . 'hoort daar een terugvalpunt van %s bij.',
            cm_niveau_label($beschikbaarheid),
            mb_strtolower(qs_antwoord_tekst('rpo', $a), 'UTF-8')
        );
    }

    return '';
}

/**
 * Materieel cloudgebruik hoeven we niet te vragen: paragraaf 4.2 van de
 * quickscan definieert het als een (kritisch) strategisch proces in combinatie
 * met een vitaal of belangrijk systeem én een (zeer) hoge beschikbaarheidseis.
 *
 * @return array{waar:bool,reden:string}
 */
function qs_materieel_cloudgebruik(array $a, string $beschikbaarheid): array
{
    $proces = (string) ($a['proces_classificatie'] ?? '');
    $systeem = (string) ($a['systeem_classificatie'] ?? '');

    $waar = in_array($proces, ['strategisch', 'kritisch-strategisch'], true)
        && in_array($systeem, ['vitaal', 'belangrijk'], true)
        && in_array($beschikbaarheid, ['h', 'zh'], true);

    return [
        'waar' => $waar,
        'reden' => $waar
            ? sprintf(
                'Een %s proces op een %s systeem met beschikbaarheidseis %s telt volgens de '
                . 'quickscan als materieel cloudgebruik.',
                mb_strtolower(qs_antwoord_tekst('proces_classificatie', $a), 'UTF-8'),
                mb_strtolower(qs_antwoord_tekst('systeem_classificatie', $a), 'UTF-8'),
                cm_niveau_label($beschikbaarheid)
            )
            : 'De combinatie van procesbelang, systeembelang en beschikbaarheidseis haalt de drempel '
                . 'voor materieel cloudgebruik niet.',
    ];
}

/**
 * Of de ABRO van toepassing is. Wie het proces als kritisch strategisch
 * aanmerkt, zegt daarmee dat het een maatschappelijk vitaal proces is.
 *
 * @return array{waar:bool,reden:string}
 */
function qs_abro_van_toepassing(array $a): array
{
    $waar = ($a['proces_classificatie'] ?? '') === 'kritisch-strategisch';

    return [
        'waar' => $waar,
        'reden' => $waar
            ? 'Het proces is aangemerkt als kritisch strategisch, oftewel een maatschappelijk vitaal '
                . 'proces. Daarmee is de ABRO quickscan nationale veiligheid bij inkoop en aanbesteden '
                . 'van toepassing.'
            : 'Het proces is niet aangemerkt als maatschappelijk vitaal.',
    ];
}

/**
 * Dreigingsprofiel.
 *
 * De quickscan vraagt of bescherming nodig is tegen inlichtingendiensten,
 * terreurgroepen of georganiseerde criminaliteit. Dat is voor een
 * behoeftesteller nauwelijks te beoordelen, dus vragen we in plaats daarvan
 * wat dit systeem een aantrekkelijk doelwit maakt en leiden we het profiel af.
 *
 * @return array{niveau:string,redenen:list<string>}
 */
function qs_afgeleid_dreigingsprofiel(array $a): array
{
    $kenmerken = [
        'besluitvorming' => 'er nog niet openbare politieke besluitvorming of standpuntbepaling in zit',
        'onderhandeling' => 'er onderhandelingen of contracten met een groot belang in zitten',
        'internationaal' => 'het om internationale betrekkingen, diplomatie of defensie gaat',
        'opsporing' => 'het om informatie over opsporing, inlichtingen of veiligheid gaat',
        'bewindspersonen' => 'er persoonsgegevens van bewindspersonen of topambtenaren in zitten',
        'vitaal' => 'er een koppeling is met een vitaal proces of vitale infrastructuur',
        'identiteit' => 'er identificerende gegevens van grote aantallen burgers in zitten',
    ];

    $gekozen = (array) ($a['dreiging_kenmerken'] ?? []);
    $redenen = [];

    foreach ($kenmerken as $sleutel => $tekst) {
        if (in_array($sleutel, $gekozen, true)) {
            $redenen[] = 'Het profiel is hoog omdat ' . $tekst . '.';
        }
    }

    if (($a['rubricering'] ?? '') === 'staatsgeheim') {
        $redenen[] = 'Het profiel is hoog omdat er gerubriceerde informatie wordt verwerkt.';
    }

    $handmatig = ($a['dreiging_verhoging'] ?? '') === 'hoog';
    if ($handmatig && $redenen === []) {
        $redenen[] = 'Het profiel is handmatig op hoog gezet.';
    }

    if ($redenen === []) {
        return [
            'niveau' => 'normaal',
            'redenen' => ['Geen van de kenmerken die een systeem interessant maken voor '
                . 'inlichtingendiensten, terreurgroepen of georganiseerde criminaliteit is van '
                . 'toepassing.'],
        ];
    }

    return ['niveau' => 'hoog', 'redenen' => $redenen];
}

/**
 * Grootschaligheid van de verwerking, langs de vier factoren die de AVG
 * daarvoor noemt: aantal betrokkenen, hoeveelheid gegevens per persoon, duur
 * en geografisch bereik.
 *
 * @return array{uitkomst:string,redenen:list<string>}
 */
function qs_afgeleide_grootschaligheid(array $a): array
{
    if (!qs_afgeleide_persoonsgegevens($a)['heeft']) {
        return ['uitkomst' => 'nee', 'redenen' => ['Er worden geen persoonsgegevens verwerkt.']];
    }

    $aantalRang = [
        'tot100' => 0,
        'tot1000' => 1,
        'tot100k' => 2,
        'meer' => 3,
    ][(string) ($a['pg_aantal_betrokkenen'] ?? '')] ?? 0;

    $factoren = [];
    if (($a['pg_hoeveelheid'] ?? '') === 'uitgebreid') {
        $factoren[] = 'er ontstaat een uitgebreid beeld per persoon';
    }
    if (($a['pg_duur'] ?? '') === 'doorlopend') {
        $factoren[] = 'de verwerking loopt door';
    }
    if (in_array($a['pg_bereik'] ?? '', ['landelijk', 'internationaal'], true)) {
        $factoren[] = 'het bereik is landelijk of internationaal';
    }

    $aantalTekst = qs_antwoord_tekst('pg_aantal_betrokkenen', $a);
    $samen = $factoren === []
        ? ''
        : ' en ' . (count($factoren) === 1
            ? $factoren[0]
            : implode(', ', array_slice($factoren, 0, -1)) . ' en ' . end($factoren));

    if ($aantalRang === 3) {
        return [
            'uitkomst' => 'ja',
            'redenen' => ['Er zijn meer dan 100.000 betrokkenen; dat is per definitie grootschalig.'],
        ];
    }

    if ($aantalRang === 2 && $factoren !== []) {
        return [
            'uitkomst' => 'ja',
            'redenen' => ['Het gaat om ' . mb_strtolower($aantalTekst, 'UTF-8') . ' betrokkenen'
                . $samen . '.'],
        ];
    }

    if ($aantalRang === 2 || ($aantalRang === 1 && count($factoren) >= 2)) {
        return [
            'uitkomst' => 'twijfel',
            'redenen' => ['Het gaat om ' . mb_strtolower($aantalTekst, 'UTF-8') . ' betrokkenen'
                . $samen . '. Dat zit tegen grootschalig aan; de CPO bepaalt of een volledige DPIA '
                . 'nodig is.'],
        ];
    }

    return [
        'uitkomst' => 'nee',
        'redenen' => ['Het gaat om ' . mb_strtolower($aantalTekst, 'UTF-8') . ' betrokkenen'
            . $samen . '. Dat haalt de drempel voor een grootschalige verwerking niet.'],
    ];
}

/**
 * Risicoklasse onder de AI verordening, afgeleid uit de aangevinkte
 * toepassingsgebieden in plaats van uit een zelfinschatting.
 *
 * @return array{niveau:string,redenen:list<string>,label:string}
 */
function qs_afgeleid_ai_risico(array $a): array
{
    if (($a['ai'] ?? 'uitgesloten') === 'uitgesloten') {
        return [
            'niveau' => 'geen',
            'label' => 'Niet van toepassing',
            'redenen' => ['Inzet van AI is uitgesloten.'],
        ];
    }

    $gekozen = (array) ($a['ai_toepassing'] ?? []);
    $vraag = cm_alle_vragen()['ai_toepassing'] ?? ['opties' => []];

    $verzamel = static function (array $gekozen, array $vraag, string $prefix): array {
        $labels = [];
        foreach ($gekozen as $sleutel) {
            if (str_starts_with((string) $sleutel, $prefix)) {
                $labels[] = mb_strtolower((string) ($vraag['opties'][$sleutel]['label'] ?? $sleutel), 'UTF-8');
            }
        }

        return $labels;
    };

    $verboden = $verzamel($gekozen, $vraag, 'verboden_');
    if ($verboden !== []) {
        return [
            'niveau' => 'onaanvaardbaar',
            'label' => 'Onaanvaardbaar risico',
            'redenen' => ['De AI verordening verbiedt deze toepassing: ' . implode('; ', $verboden) . '.'],
        ];
    }

    $hoog = $verzamel($gekozen, $vraag, 'hoog_');
    if ($hoog !== []) {
        return [
            'niveau' => 'hoog',
            'label' => 'Hoog risico',
            'redenen' => ['Dit valt onder de toepassingen met een hoog risico uit de AI verordening: '
                . implode('; ', $hoog) . '.'],
        ];
    }

    $transparant = $verzamel($gekozen, $vraag, 'transparantie_');
    if ($transparant !== []) {
        return [
            'niveau' => 'beperkt',
            'label' => 'Beperkt risico, wel transparantieplicht',
            'redenen' => ['Er geldt een transparantieverplichting omdat ' . implode('; ', $transparant) . '.'],
        ];
    }

    return [
        'niveau' => 'minimaal',
        'label' => 'Minimaal risico',
        'redenen' => ['Geen van de benoemde toepassingsgebieden van de AI verordening is aangevinkt. '
            . 'Controleer dit opnieuw zodra de inzet concreter wordt.'],
    ];
}

/**
 * Vervangt elk 'weet ik niet' door de aanname die bij die vraag hoort, en
 * houdt bij waar dat is gebeurd. De motor rekent verder met de aannames; het
 * advies laat zien welke dat waren, zodat de beoordelaar ziet waar de
 * onzekerheid zit.
 *
 * @return array{antwoorden:array<string,mixed>,aannames:list<array{vraag:string,aanname:string}>}
 */
function qs_met_aannames(array $antwoorden): array
{
    $aannames = [];

    foreach (cm_secties() as $sectie) {
        if (!cm_zichtbaar($sectie, $antwoorden)) {
            continue;
        }

        foreach ($sectie['vragen'] as $vraag) {
            $key = $vraag['key'];

            if (($antwoorden[$key] ?? '') !== 'onbekend' || empty($vraag['aanname'])) {
                continue;
            }

            if (!cm_zichtbaar($vraag, $antwoorden)) {
                continue;
            }

            $aanname = (string) $vraag['aanname'];
            $antwoorden[$key] = $aanname;

            $aannames[] = [
                'vraag' => (string) $vraag['label'],
                'aanname' => (string) ($vraag['opties'][$aanname]['label'] ?? $aanname),
            ];
        }
    }

    return ['antwoorden' => $antwoorden, 'aannames' => $aannames];
}

/** Alle sleutels uit de aankruislijst die persoonsgegevens zijn. */
function qs_persoonsgegeven_sleutels(): array
{
    $groepen = qs_informatiegroepen();

    return array_merge($groepen['gewoon'], $groepen['bijzonder']);
}

/**
 * Wat de aangekruiste soorten informatie betekenen voor de privacy.
 *
 * Dit vervangt vier abstracte vragen ("hoeveel categorieën?", "zitten er
 * bijzondere bij?") door tellen wat er is aangekruist.
 *
 * @return array{
 *     heeft:bool, categorieen:string, bijzonder:string, bsn:string,
 *     gewoon:list<string>, bijzonder_soorten:list<string>,
 *     label:string, redenen:list<string>
 * }
 */
function qs_afgeleide_persoonsgegevens(array $a): array
{
    $gekozen = array_map('strval', (array) ($a['informatie_soorten'] ?? []));
    $groepen = qs_informatiegroepen();
    $soorten = qs_informatiesoorten();

    $gewoon = array_values(array_intersect($groepen['gewoon'], $gekozen));
    $bijzonder = array_values(array_intersect($groepen['bijzonder'], $gekozen));

    $heeft = $gewoon !== [] || $bijzonder !== [];
    $bsn = in_array('bsn', $gekozen, true) ? 'ja' : 'nee';

    $categorieen = count($gewoon) >= 2 ? 'meerdere' : (count($gewoon) === 1 ? 'een' : '');
    $bijzonderNiveau = count($bijzonder) >= 2 ? 'meerdere' : (count($bijzonder) === 1 ? 'een' : 'nee');

    $redenen = [];

    if (!$heeft) {
        return [
            'heeft' => false,
            'categorieen' => '',
            'bijzonder' => 'nee',
            'bsn' => 'nee',
            'gewoon' => [],
            'bijzonder_soorten' => [],
            'label' => 'Geen persoonsgegevens',
            'redenen' => ['Er is niets aangekruist dat over een herkenbaar persoon gaat. De '
                . 'AVG is daarmee niet van toepassing en de vragen over doel en grondslag '
                . 'vervallen.'],
        ];
    }

    $noem = static function (array $sleutels) use ($soorten): string {
        $labels = [];
        foreach ($sleutels as $sleutel) {
            $labels[] = mb_strtolower((string) ($soorten[$sleutel]['label'] ?? $sleutel), 'UTF-8');
        }

        return count($labels) === 1
            ? $labels[0]
            : implode(', ', array_slice($labels, 0, -1)) . ' en ' . end($labels);
    };

    if ($gewoon !== []) {
        $redenen[] = sprintf(
            '%s %s aangekruist: %s.',
            count($gewoon) === 1 ? 'Eén categorie' : count($gewoon) . ' categorieën',
            count($gewoon) === 1 ? 'gewone persoonsgegevens is' : 'gewone persoonsgegevens zijn',
            $noem($gewoon)
        );
    }

    if ($bijzonder !== []) {
        $redenen[] = sprintf(
            '%s bijzondere persoonsgegevens: %s. Die wegen zwaarder en tillen de eisen aan '
            . 'integriteit en vertrouwelijkheid omhoog.',
            count($bijzonder) === 1 ? 'Eén categorie' : count($bijzonder) . ' categorieën',
            $noem($bijzonder)
        );
    }

    if ($bsn === 'ja') {
        $redenen[] = 'Het burgerservicenummer zit erbij. Daarvoor moet een wettelijke grondslag '
            . 'worden aangewezen.';
    }

    $minimum = qs_avg_minimum($categorieen, $bijzonderNiveau);
    $redenen[] = sprintf(
        'Daarmee geldt een ondergrens van %s voor zowel integriteit als vertrouwelijkheid.',
        cm_niveau_label($minimum)
    );

    $label = count($gewoon) + count($bijzonder) === 1
        ? 'Eén categorie persoonsgegevens'
        : (count($gewoon) + count($bijzonder)) . ' categorieën persoonsgegevens';

    if ($bijzonder !== []) {
        $label .= ', waarvan ' . count($bijzonder) . ' bijzonder';
    }

    return [
        'heeft' => true,
        'categorieen' => $categorieen,
        'bijzonder' => $bijzonderNiveau,
        'bsn' => $bsn,
        'gewoon' => $gewoon,
        'bijzonder_soorten' => $bijzonder,
        'label' => $label,
        'redenen' => $redenen,
    ];
}
