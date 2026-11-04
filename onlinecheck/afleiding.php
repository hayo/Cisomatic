<?php

/**
 * Wat de check uitrekent. Eén regel: comply als het platform in beginsel
 * inzetbaar is, elk doel binnen het kader valt, en aan de twee voorwaarden is
 * voldaan. Anders explain.
 *
 * De kaders komen uit ../socialemedia/kaders.php, dezelfde als die van de
 * kanalenscan. regels.js rekent hetzelfde uit tijdens het invullen, met de
 * gegevens van oc_regelgegevens(). PHP blijft de bron van waarheid.
 */

declare(strict_types=1);

const OC_UITKOMSTEN = [
    'comply' => ['label' => 'Comply', 'niveau' => 'l'],
    'explain' => ['label' => 'Explain', 'niveau' => 'm'],
];

/**
 * De doelen van de check: de vijftien doelen uit de notitie, samengevoegd tot
 * elf. Alleen doelen die bij elk platform dezelfde ruimte hebben, staan samen.
 * Zo verandert geen uitkomst. Verder samenvoegen kan niet zonder verlies: dan
 * valt bijvoorbeeld informeren op LinkedIn buiten het kader.
 */
const OC_GROEPEN = [
    'informeren' => [
        'label' => 'Informeren en duiden',
        'hint' => 'Feitelijke informatie geven, en uitleggen wat iets betekent voor mensen of bedrijven.',
        'doelen' => ['informeren', 'duiden'],
    ],
    'attenderen' => ['doelen' => ['attenderen']],
    'voorlichting' => [
        'label' => 'Voorlichten en gedrag beïnvloeden',
        'hint' => 'Het brede publiek voorlichten over beleid, of bepaald gedrag stimuleren of ontmoedigen.',
        'doelen' => ['publieksvoorlichting', 'gedrag'],
    ],
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
    'explain' => '{naam} is in beginsel niet inzetbaar. Daarom vraagt het kader altijd een explain.',
    'geen_kader' => 'Voor {naam} is er geen rijksbreed kader. Dan is het altijd een explain.',
    'doelen' => 'Deze doelen vallen buiten het kader van {naam}: {lijst}.',
    'eigen_deels' => 'Niet alles wat jullie hier plaatsen, staat ook op een eigen kanaal van de overheid.',
    'eigen_nee' => 'Wat jullie hier plaatsen, staat niet op een eigen kanaal van de overheid.',
    'doorverwijzen' => 'Jullie wijzen mensen niet op de risico’s van contact via {naam}, en verwijzen niet door.',
    'doelen_past' => 'Alle doelen passen binnen het kader van {naam}.',
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
 * Hoe een doel van de check zich tot een kader verhoudt: 'ja' (binnen het
 * kader), 'betaald' (alleen met advertenties, zoals gedragsbeïnvloeding op
 * TikTok) of 'nee'. Bij samengevoegde doelen telt de krapste ruimte. Een
 * platform zonder kader geeft null: daar is niets om aan te toetsen.
 */
function oc_ruimte(array $kader, string $groep): ?string
{
    if ($kader['doelen'] === null) {
        return null;
    }

    $rang = ['nee' => 0, 'betaald' => 1, 'ja' => 2];
    $ruimtes = array_map(static fn (string $doel): string => match (true) {
        in_array($doel, [...$kader['doelen'], ...$kader['contactdoelen']], true) => 'ja',
        in_array($doel, $kader['betaald'], true) => 'betaald',
        default => 'nee',
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
