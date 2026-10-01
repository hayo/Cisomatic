<?php

/**
 * Wat de tool uitrekent: welke inzet buiten het kader valt, per soort gebruik,
 * waar de inzet van het kader afwijkt, en de uitkomst: comply, explain of niet
 * inzetten.
 *
 * regels.js rekent hetzelfde uit tijdens het invullen, met de teksten en de
 * toetsen hieronder. PHP blijft de bron van waarheid; de rekenregel staat in
 * beide.
 */

declare(strict_types=1);

const SM_UITKOMSTEN = [
    'comply' => ['label' => 'Comply', 'niveau' => 'l'],
    'explain' => ['label' => 'Explain', 'niveau' => 'm'],
    'niet' => ['label' => 'Niet inzetten', 'niveau' => 'h'],
];

/**
 * Per soort gebruik: welke vraag aan welke ruimte van het kader wordt getoetst,
 * met welke opties, en met welke zin een afwijking in het document komt.
 */
const SM_TOETSEN = [
    'organisch' => [
        ['vraag' => 'sm_doelen', 'ruimte' => 'doelen', 'tekst' => 'doelen'],
        ['vraag' => 'sm_vormen', 'ruimte' => 'vormen', 'tekst' => 'vormen'],
    ],
    'betaald' => [['vraag' => 'sm_betaald', 'ruimte' => 'betaald', 'tekst' => 'betaald']],
    'contact' => [['vraag' => 'sm_contactvormen', 'ruimte' => 'contact', 'tekst' => 'contact']],
];

/** De zinnen van de uitkomst. {naam} is het platform, {lijst} een opsomming. */
const SM_TEKSTEN = [
    'comply' => 'De inzet past binnen de ruimte van het rijksbrede kader.',
    'explain' => 'Voor {naam} vraagt het rijksbrede kader altijd een explain.',
    'geen_kader' => 'Voor {naam} is er geen rijksbreed kader.',
    'doelen' => 'Deze doelen vallen buiten het kader: {lijst}.',
    'vormen' => 'Deze vormen vallen buiten het kader: {lijst}.',
    'betaald' => 'Deze betaalde inzet valt buiten het kader: {lijst}.',
    'contact' => 'Dit contact valt buiten het kader: {lijst}.',
    'eigen_deels' => 'Niet alles wat jullie hier plaatsen, staat eerst op je eigen openbare kanalen.',
    'eigen_nee' => 'Wat jullie hier plaatsen, staat niet eerst op je eigen openbare kanalen.',
    'doorverwijzen' => 'Jullie wijzen mensen niet op de risico’s van contact via {naam}, en verwijzen niet door naar een '
        . 'kanaal van de overheid.',
    'prive_toestel' => 'Het account wordt beheerd vanaf een privétoestel.',
    'bereik' => 'Het kanaal bereikt de doelgroep niet, dus het dient het doel niet.',
    'alternatief' => 'Een minder risicovol kanaal bereikt hetzelfde doel. Het kader vraagt dan om terughoudendheid.',
    'weging' => 'De meerwaarde weegt niet op tegen de risico’s.',
    'rijk_toestel' => 'De app van {naam} mag niet op apparatuur van het Rijk.',
    'bewindspersoon_verboden' => 'Een bewindspersoon mag geen account op {naam} hebben.',
    'bewindspersoon' => 'Een account van een bewindspersoon valt buiten het kader van {naam}.',
    'bewindspersoon_betaald' => 'Een account van een bewindspersoon heeft geen betaalde promotie.',
    'doel_past' => 'Alle gekozen inzet past binnen het kader van {naam}.',
    'doel_geen_kader' => 'Er is geen kader om deze inzet aan te toetsen.',
];

/** Vaste aandachtspunten bij adverteren. Ze tellen niet mee voor de uitkomst. */
const SM_LET_OP = [
    'dsa' => 'Richt advertenties nooit op gevoelige gegevens, zoals gezondheid, geloof of politieke voorkeur. Richt ook '
        . 'niet met profielen op minderjarigen. Dat verbiedt de Digital Services Act.',
    'profiel' => 'Jullie richten advertenties op interesses of gedrag. Dat gebruikt de profielen die het platform van '
        . 'mensen maakt. Richt liever breed, op leeftijd, regio of taal.',
];

function sm_tekst(string $sleutel, string $naam = '', array $lijst = []): string
{
    return strtr(SM_TEKSTEN[$sleutel], ['{naam}' => $naam, '{lijst}' => cm_opsomming($lijst)]);
}

/** De opties van een getoetste vraag. @return array<string,array{label:string}> */
function sm_toets_opties(string $vraag): array
{
    return match ($vraag) {
        'sm_doelen' => SM_DOELEN,
        'sm_vormen' => SM_VORMEN,
        'sm_betaald' => SM_BETAALD,
        'sm_contactvormen' => SM_CONTACT,
    };
}

/** De gekozen soorten gebruik. @return list<string> */
function sm_gebruik(array $a): array
{
    return array_values(array_intersect(array_keys(SM_GEBRUIK), (array) ($a['sm_gebruik'] ?? [])));
}

/**
 * Per getoetste vraag de gekozen opties die buiten de ruimte van het kader
 * vallen, als labels. Alleen voor de soorten gebruik die gekozen zijn. Zonder
 * kader valt niets erbuiten: dan is er niets om aan te toetsen.
 *
 * @return array<string,list<string>> tekstsleutel => labels
 */
function sm_buiten(array $a, array $kader): array
{
    $buiten = [];

    foreach (sm_gebruik($a) as $soort) {
        foreach (SM_TOETSEN[$soort] as $toets) {
            $ruimte = $kader[$toets['ruimte']];
            $opties = sm_toets_opties($toets['vraag']);
            $labels = $ruimte === null ? [] : array_values(array_map(
                static fn (string $k): string => $opties[$k]['label'],
                array_diff(array_intersect(array_keys($opties), (array) ($a[$toets['vraag']] ?? [])), $ruimte)
            ));

            if ($labels !== []) {
                $buiten[$toets['tekst']] = $labels;
            }
        }
    }

    return $buiten;
}

/** Of alles beantwoord is wat de uitkomst bepaalt. */
function sm_compleet(array $a): bool
{
    $k = (string) ($a['sm_platform'] ?? '');
    $velden = ['sm_accounttype', 'sm_bereik', 'sm_alternatief', 'sm_doorverwijzen', 'sm_weging'];
    $lijsten = [];

    if ($k !== 'mastodon') {
        $velden[] = 'sm_eigen_kanalen';
    }
    if ($k === 'anders') {
        $velden[] = 'sm_anders_land';
    }
    if ((sm_kader($a) ?? [])['toestel'] ?? false) {
        $velden[] = 'sm_toestel';
    }
    foreach (sm_gebruik($a) as $soort) {
        array_push($lijsten, ...array_column(SM_TOETSEN[$soort], 'vraag'));
        if ($soort === 'betaald') {
            $velden[] = 'sm_doelgericht';
        }
    }

    foreach ($velden as $veld) {
        if ((string) ($a[$veld] ?? '') === '') {
            return false;
        }
    }
    foreach ($lijsten as $lijst) {
        if ((array) ($a[$lijst] ?? []) === []) {
            return false;
        }
    }

    return $k !== '' && sm_gebruik($a) !== [];
}

/**
 * Alles wat pagina en document nodig hebben. Werkt ook half ingevuld: dan is
 * de uitkomst leeg.
 *
 * @return array<string,mixed>
 */
function sm_evalueer(array $a): array
{
    if (($a['sm_situatie'] ?? '') === 'exit') {
        return sm_evalueer_exit($a);
    }

    $kader = sm_kader($a) ?? sm_kaders()['anders'] + ['explain' => true, 'toestel' => false];
    $naam = $kader['naam'];
    $buiten = sm_buiten($a, $kader);
    $eigen = (string) ($a['sm_eigen_kanalen'] ?? '');
    $toestel = (string) ($a['sm_toestel'] ?? '');
    $betaald = in_array('betaald', sm_gebruik($a), true);
    $bewindspersoon = ($a['sm_accounttype'] ?? '') === 'bewindspersoon';

    $niet = array_filter([
        ($a['sm_bereik'] ?? '') === 'nee' ? sm_tekst('bereik') : '',
        ($a['sm_alternatief'] ?? '') === 'ja' ? sm_tekst('alternatief') : '',
        ($a['sm_weging'] ?? '') === 'nee' ? sm_tekst('weging') : '',
        $kader['toestel'] && $toestel === 'rijk' ? sm_tekst('rijk_toestel', $naam) : '',
        $bewindspersoon && $kader['bewindspersoon'] === 'verboden' ? sm_tekst('bewindspersoon_verboden', $naam) : '',
    ]);

    $afwijkingen = array_filter([
        $kader['inzet'] === 'geen' ? sm_tekst('geen_kader', $naam) : ($kader['explain'] ? sm_tekst('explain', $naam) : ''),
        ...array_map(static fn (string $t, array $l): string => sm_tekst($t, $naam, $l), array_keys($buiten), $buiten),
        $eigen === 'deels' ? sm_tekst('eigen_deels') : ($eigen === 'nee' ? sm_tekst('eigen_nee') : ''),
        ($a['sm_doorverwijzen'] ?? '') === 'nee' ? sm_tekst('doorverwijzen', $naam) : '',
        $kader['toestel'] && $toestel === 'prive' ? sm_tekst('prive_toestel') : '',
        $bewindspersoon && $kader['bewindspersoon'] === 'nee' ? sm_tekst('bewindspersoon', $naam) : '',
        $bewindspersoon && $betaald ? sm_tekst('bewindspersoon_betaald') : '',
    ]);

    $uitkomst = match (true) {
        !sm_compleet($a) => '',
        $niet !== [] => 'niet',
        $afwijkingen !== [] => 'explain',
        default => 'comply',
    };

    $regelen = array_values(array_map(
        static fn (array $m): string => $m['regel'],
        array_diff_key(SM_MAATREGELEN, array_flip((array) ($a['sm_maatregelen'] ?? [])))
    ));
    if (sm_toetreding($a) && $kader['inzet'] === 'geen' && ($a['sm_markt_archief'] ?? '') === 'nee') {
        $regelen[] = 'Zoek uit hoe je berichten van het platform archiveert. Het platform biedt dat zelf niet.';
    }
    if (($a['sm_bereik'] ?? '') === 'verwachting') {
        $regelen[] = 'Meet het bereik onder de doelgroep. Kijk na een halfjaar opnieuw of het kanaal het doel dient.';
    }

    $letOp = !$betaald ? [] : array_values(array_filter([
        SM_LET_OP['dsa'],
        ($a['sm_doelgericht'] ?? '') === 'profiel' ? SM_LET_OP['profiel'] : '',
    ]));

    return [
        'situatie' => (string) ($a['sm_situatie'] ?? ''),
        'toetreding' => sm_toetreding($a),
        'kader' => $kader,
        'inzet' => SM_INZET[$kader['inzet']],
        'uitkomst' => $uitkomst,
        'label' => SM_UITKOMSTEN[$uitkomst]['label'] ?? 'Nog te bepalen',
        'niveau' => SM_UITKOMSTEN[$uitkomst]['niveau'] ?? '',
        'redenen' => match ($uitkomst) {
            'niet' => array_values($niet),
            'explain' => array_values($afwijkingen),
            'comply' => [sm_tekst('comply')],
            default => [],
        },
        'afwijkingen' => array_values($afwijkingen),
        'buiten' => $buiten,
        'regelen' => $regelen,
        'let_op' => $letOp,
        'herziening' => (new DateTimeImmutable('now'))->modify('+1 year'),
    ];
}

/** Voor toon_als_uitkomst en verplicht_als_uitkomst. */
function sm_uitkomst(string $naam, array $a): string
{
    return match ($naam) {
        'uitkomst' => sm_evalueer($a)['uitkomst'],
        'toestel' => ((sm_kader($a) ?? [])['toestel'] ?? false) ? 'ja' : 'nee',
        default => '',
    };
}

/** Toetreding: een nieuw kanaal op een platform waar de organisatie nog niet zit. */
function sm_toetreding(array $a): bool
{
    return ($a['sm_situatie'] ?? '') === 'nieuw' && ($a['sm_al_actief'] ?? '') === 'nee';
}

/** Een vertrek: het advies uit exit.php, in de vorm die pagina en document verwachten. */
function sm_evalueer_exit(array $a): array
{
    $kader = sm_kader($a) ?? sm_kaders()['anders'] + ['explain' => true, 'toestel' => false];
    $advies = sm_exit_advies($a);

    return [
        'situatie' => 'exit',
        'toetreding' => false,
        'kader' => $kader,
        'inzet' => SM_INZET[$kader['inzet']],
        'uitkomst' => $advies['niveau'] === '' ? '' : 'exit',
        'label' => $advies['label'],
        'niveau' => $advies['niveau'],
        'redenen' => $advies['redenen'],
        'afwijkingen' => [],
        'buiten' => [],
        'regelen' => [],
        'let_op' => [],
        'herziening' => (new DateTimeImmutable('now'))->modify('+1 year'),
    ];
}
