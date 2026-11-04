<?php

/**
 * Wat de check uitrekent. Eén regel: het kanaal voldoet als het platform in
 * beginsel inzetbaar is, geen doel in het kader bij "niet" staat, en aan de
 * twee voorwaarden is voldaan. Anders is uitleg nodig: de explain uit de
 * notitie. Een doel dat het kader niet noemt, mag.
 *
 * De kaders komen uit ../socialemedia/kaders.php, dezelfde als die van de
 * kanalenscan. regels.js rekent hetzelfde uit tijdens het invullen, met de
 * gegevens van oc_regelgegevens(). PHP blijft de bron van waarheid.
 */

declare(strict_types=1);

const OC_UITKOMSTEN = [
    'comply' => ['label' => 'Voldoet', 'niveau' => 'l'],
    'explain' => ['label' => 'Uitleg nodig', 'niveau' => 'm'],
];

/**
 * De doelen van de check: de vijftien doelen uit de notitie, samengevoegd tot
 * twaalf. Alleen doelen die bij elk platform hetzelfde krijgen, staan samen.
 * Zo verandert geen uitkomst.
 */
const OC_GROEPEN = [
    'informeren' => [
        'label' => 'Informeren en duiden',
        'hint' => 'Feitelijke informatie geven, en uitleggen wat iets betekent voor mensen of bedrijven.',
        'doelen' => ['informeren', 'duiden'],
    ],
    'attenderen' => ['doelen' => ['attenderen']],
    'publieksvoorlichting' => ['doelen' => ['publieksvoorlichting']],
    'gedrag' => ['doelen' => ['gedrag']],
    'activeren' => [
        'label' => 'Activeren en werven',
        'hint' => 'Mensen iets laten doen, zoals zich aanmelden of meedoen. Ook medewerkers werven en binden.',
        'doelen' => ['activeren', 'arbeidsmarkt'],
    ],
    'bereik' => ['doelen' => ['bereik']],
    'communitymanagement' => ['doelen' => ['communitymanagement']],
    'stakeholdermanagement' => ['doelen' => ['stakeholdermanagement']],
    'internationaal' => ['doelen' => ['internationaal']],
    'monitoring' => ['doelen' => ['monitoring']],
    'interactie' => ['doelen' => ['interactie']],
    'contact' => [
        'label' => 'Dienstverlening en veilig contact',
        'hint' => 'Iemand helpen met een eigen vraag of zaak, via een veilige bron van de overheid.',
        'doelen' => ['dienstverlening', 'veilig'],
    ],
];

/** De zinnen van de uitkomst. {naam} is het platform, {lijst} een opsomming. */
const OC_TEKSTEN = [
    'comply' => 'Het kanaal past binnen het rijksbrede kader.',
    'explain' => '{naam} is in beginsel niet inzetbaar. Daarom moet je altijd uitleggen waarom jullie het gebruiken.',
    'geen_kader' => 'Voor {naam} is er geen rijksbreed kader. Daarom moet je altijd uitleggen waarom jullie het gebruiken.',
    'doelen' => 'Het kader van {naam} zet deze doelen bij ‘niet’: {lijst}.',
    'eigen_deels' => 'Niet alles wat jullie hier plaatsen, staat ook op een eigen kanaal van de overheid.',
    'eigen_nee' => 'Wat jullie hier plaatsen, staat niet op een eigen kanaal van de overheid.',
    'doorverwijzen' => 'Jullie wijzen mensen niet op de risico’s van contact via {naam}, en verwijzen niet door.',
    'doelen_past' => 'Geen van de doelen staat in het kader van {naam} bij ‘niet’.',
    'onbekend' => 'Het kader van {naam} noemt dit niet: {lijst}. Dat mag, maar ga na of het platform er geschikt voor is.',
    'doelen_geen_kader' => 'Er is geen kader om de doelen aan te toetsen.',
    'betaald' => '{lijst} past op {naam} alleen met advertenties.',
    'toestel' => 'De app van {naam} mag nooit op een telefoon, tablet of computer van het Rijk.',
];

function oc_tekst(string $sleutel, string $naam = '', array $lijst = []): string
{
    return strtr(OC_TEKSTEN[$sleutel], ['{naam}' => $naam, '{lijst}' => cm_opsomming($lijst)]);
}

/** De doelen van de check met label en hint. @return array<string,array{label:string,hint:string}> */
function oc_doelen(): array
{
    $alle = SM_DOELEN + SM_CONTACTDOELEN;

    return array_map(static fn (array $g): array => [
        'label' => $g['label'] ?? $alle[$g['doelen'][0]]['label'],
        'hint' => $g['hint'] ?? $alle[$g['doelen'][0]]['hint'],
    ], OC_GROEPEN);
}

/**
 * Hoe een doel van de check zich tot een kader verhoudt:
 * - 'nee': de notitie zet het bij "niet" (uitleg nodig)
 * - 'ja': de notitie zet het bij "wel"
 * - 'betaald': alleen met advertenties, zoals gedragsbeïnvloeding op TikTok
 * - 'onbekend': de notitie noemt het niet. Dat mag.
 * Bij samengevoegde doelen telt de krapste. Een platform zonder kader geeft
 * null: daar is niets om aan te toetsen.
 */
function oc_ruimte(array $kader, string $groep): ?string
{
    if ($kader['doelen'] === null) {
        return null;
    }

    $rang = ['nee' => 0, 'betaald' => 1, 'onbekend' => 2, 'ja' => 3];
    $ruimtes = array_map(static fn (string $doel): string => match (true) {
        in_array($doel, $kader['niet_doelen'], true) => 'nee',
        in_array($doel, [...$kader['doelen'], ...$kader['contactdoelen']], true) => 'ja',
        in_array($doel, $kader['betaald'], true) => 'betaald',
        default => 'onbekend',
    }, OC_GROEPEN[$groep]['doelen']);
    usort($ruimtes, static fn (string $x, string $y): int => $rang[$x] <=> $rang[$y]);

    return $ruimtes[0];
}

/** Het kader van het gekozen platform, met de naam die de invuller gaf bij een ander kanaal. */
function oc_kader(array $a): ?array
{
    $k = (string) ($a['sm_platform'] ?? '');
    $kader = sm_kaders()[$k] ?? null;
    if ($kader === null) {
        return null;
    }
    if ($k === 'anders' && trim((string) ($a['sm_anders_naam'] ?? '')) !== '') {
        $kader['naam'] = trim((string) $a['sm_anders_naam']);
    }

    return $kader + ['explain' => false, 'toestel' => false];
}

/** Of de vraag naar eigen kanalen geldt. Op social.overheid.nl zit je al op een eigen kanaal. */
function oc_eigen_geldt(array $a): bool
{
    return ($a['sm_platform'] ?? '') !== 'mastodon';
}

/** Of alles beantwoord is wat de uitkomst bepaalt. */
function oc_compleet(array $a): bool
{
    return (string) ($a['sm_platform'] ?? '') !== ''
        && (array) ($a['oc_doelen'] ?? []) !== []
        && (string) ($a['sm_doorverwijzen'] ?? '') !== ''
        && (!oc_eigen_geldt($a) || (string) ($a['sm_eigen_kanalen'] ?? '') !== '');
}

/**
 * Alles wat pagina en document nodig hebben. Werkt ook half ingevuld: dan is
 * de uitkomst leeg.
 *
 * @return array<string,mixed>
 */
function oc_evalueer(array $a): array
{
    $kader = oc_kader($a) ?? sm_kaders()['anders'] + ['explain' => true, 'toestel' => false];
    $naam = $kader['naam'];
    $doelen = oc_doelen();
    $gekozen = array_values(array_intersect(array_keys(OC_GROEPEN), (array) ($a['oc_doelen'] ?? [])));
    $label = static fn (string $k): string => $doelen[$k]['label'];
    // Midden in een zin met een kleine letter.
    $klein = static fn (string $k): string => mb_strtolower(mb_substr($label($k), 0, 1)) . mb_substr($label($k), 1);

    $buiten = array_values(array_filter($gekozen, static fn (string $d): bool => oc_ruimte($kader, $d) === 'nee'));
    $betaald = array_values(array_filter($gekozen, static fn (string $d): bool => oc_ruimte($kader, $d) === 'betaald'));
    $onbekend = array_values(array_filter($gekozen, static fn (string $d): bool => oc_ruimte($kader, $d) === 'onbekend'));
    $eigen = oc_eigen_geldt($a) ? (string) ($a['sm_eigen_kanalen'] ?? '') : '';

    $afwijkingen = array_values(array_filter([
        $kader['inzet'] === 'geen' ? oc_tekst('geen_kader', $naam) : ($kader['explain'] ? oc_tekst('explain', $naam) : ''),
        $buiten === [] ? '' : oc_tekst('doelen', $naam, array_map($klein, $buiten)),
        $eigen === 'deels' ? oc_tekst('eigen_deels') : ($eigen === 'nee' ? oc_tekst('eigen_nee') : ''),
        ($a['sm_doorverwijzen'] ?? '') === 'nee' ? oc_tekst('doorverwijzen', $naam) : '',
    ]));

    $uitkomst = match (true) {
        !oc_compleet($a) => '',
        $afwijkingen !== [] => 'explain',
        default => 'comply',
    };

    $letOp = array_values(array_filter([
        $onbekend === [] ? '' : oc_tekst('onbekend', $naam, array_map($klein, $onbekend)),
        $betaald === [] ? '' : oc_tekst('betaald', $naam, array_map($label, $betaald)),
        $kader['toestel'] ? oc_tekst('toestel', $naam) : '',
    ]));

    return [
        'kader' => $kader,
        'inzet' => SM_INZET[$kader['inzet']],
        'uitkomst' => $uitkomst,
        'label' => OC_UITKOMSTEN[$uitkomst]['label'] ?? 'Nog te bepalen',
        'niveau' => OC_UITKOMSTEN[$uitkomst]['niveau'] ?? '',
        'redenen' => match ($uitkomst) {
            'explain' => $afwijkingen,
            'comply' => [oc_tekst('comply')],
            default => [],
        },
        'afwijkingen' => $afwijkingen,
        'buiten' => array_map($klein, $buiten),
        'let_op' => $letOp,
        'herziening' => (new DateTimeImmutable('now'))->modify('+1 year'),
    ];
}

/** Voor toon_als_uitkomst en verplicht_als_uitkomst. */
function oc_uitkomst(string $naam, array $a): string
{
    return $naam === 'uitkomst' ? oc_evalueer($a)['uitkomst'] : '';
}

/**
 * Wat regels.js nodig heeft om hetzelfde uit te rekenen. De motor zet dit als
 * data-groepen op de vraag naar het platform.
 *
 * @return array<string,mixed>
 */
function oc_regelgegevens(): array
{
    $kaders = [];
    foreach (sm_kaders() as $k => $kader) {
        $kaders[$k] = [
            'naam' => $kader['naam'],
            'inzet' => $kader['inzet'],
            'explain' => !empty($kader['explain']),
            'toestel' => !empty($kader['toestel']),
            'uitleg' => $kader['uitleg'],
            'doelgroep' => $kader['doelgroep'],
            'risicos' => $kader['risicos'],
            'ruimte' => array_map(static fn (string $d): ?string => oc_ruimte($kader, $d), array_combine(array_keys(OC_GROEPEN), array_keys(OC_GROEPEN))),
        ];
    }

    return [
        'kaders' => $kaders,
        'inzet' => SM_INZET,
        'doelen' => array_map(static fn (array $d): string => $d['label'], oc_doelen()),
        'uitkomsten' => OC_UITKOMSTEN,
        'teksten' => OC_TEKSTEN,
    ];
}
