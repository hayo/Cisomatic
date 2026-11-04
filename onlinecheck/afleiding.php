<?php

/**
 * Wat de check uitrekent. Alleen wat juridisch telt, geen advies:
 * - Facebook, X, TikTok en een platform zonder kader vragen altijd uitleg
 * - de voorwaarden: geen dienstverlening of veilig contact, alles ook op een
 *   eigen kanaal van de overheid, en mensen wijzen op de risico's van contact
 *   en doorverwijzen
 * social.overheid.nl is het basiskanaal van de overheid. Daar mag alles, dus
 * daar gelden de voorwaarden niet. Klopt alles, dan voldoet het kanaal. Anders is uitleg nodig: de explain uit
 * de notitie.
 *
 * De kaders komen uit ../socialemedia/kaders.php, dezelfde als die van de
 * kanalenscan. Het basiskanaal is het kanaal waar het kader 'persoonlijk' bij
 * 'contact' heeft: daar mogen ook dienstverlening en veilig contact. regels.js rekent hetzelfde uit tijdens het
 * invullen, met de gegevens van oc_regelgegevens(). PHP blijft de bron van
 * waarheid.
 */

declare(strict_types=1);

const OC_UITKOMSTEN = [
    'comply' => ['label' => 'Voldoet', 'niveau' => 'l'],
    'explain' => ['label' => 'Voldoet niet', 'niveau' => 'm'],
];

/** De titel van het document en van de check op één A4. */
const OC_TITEL = 'Pas toe of leg uit - social media';

/** De vier vragen van de uitleg, uit de notitie. Voor het formulier, het document en het A4. */
const OC_UITLEG = [
    'oc_afwijking' => 'Waarop wijk je af?',
    'oc_noodzaak' => 'Waarom is dit kanaal nodig (doelgroep)?',
    'oc_alternatief' => 'Waarom voldoet een minder risicovol kanaal niet?',
    'oc_maatregelen' => 'Welke maatregelen neem je tegen de risico’s?',
];

/** Wat er daarna met het formulier gebeurt. Onderaan het document en het A4. */
const OC_BESTUURSRAAD = 'De directeur Communicatie stelt dit formulier vast, na advies van de CPO, de CISO en waar '
    . 'nodig de CIO. Daarna gaat het naar de bestuursraad van de eigen organisatie. De bestuursraad toetst het elk '
    . 'jaar, en herijkt het waar nodig.';

/**
 * Waarom een platform altijd uitleg vraagt, in het kort. Uit de notitie; de
 * volledige risico's staan in het kader.
 */
const OC_GRONDEN = [
    'facebook' => 'Een DPIA vond zeven hoge privacyrisico’s en een HRIA een hoog risico voor mensenrechten. Meta '
        . 'voldoet niet aan de DSA.',
    'x' => 'X voldoet niet aan de DSA. De Europese Commissie heeft X daarvoor beboet.',
    'tiktok' => 'TikTok schendt volgens de Europese Commissie voorlopig de DSA. De app is een risico voor de nationale '
        . 'veiligheid.',
    'anders' => 'Er is geen rijksbreed kader, dus de risico’s zijn niet bekend.',
];

/** De zinnen van de uitkomst. {naam} is het platform, {grond} de reden uit OC_GRONDEN. */
const OC_TEKSTEN = [
    'comply' => 'Het kanaal voldoet aan het rijksbrede kader.',
    'inzetbaar' => '{naam} is in beginsel inzetbaar. Uitleg is alleen nodig als het kanaal niet aan de voorwaarden '
        . 'voldoet.',
    'basis' => '{naam} is het basiskanaal van de overheid. Daar mag alles.',
    'explain' => '{naam} is in beginsel niet inzetbaar. Daarom moet je altijd uitleggen waarom jullie het gebruiken. '
        . '{grond}',
    'geen_kader' => 'Voor {naam} is er geen rijksbreed kader. Daarom moet je altijd uitleggen waarom jullie het '
        . 'gebruiken.',
    'contact' => 'Jullie gebruiken {naam} voor dienstverlening of veilig contact. Dat mag niet, omdat het om '
        . 'persoonsgegevens gaat.',
    'eigen_deels' => 'Niet alles wat jullie hier plaatsen, staat ook op een eigen kanaal van de overheid.',
    'eigen_nee' => 'Wat jullie hier plaatsen, staat niet op een eigen kanaal van de overheid.',
    'doorverwijzen' => 'Jullie wijzen mensen niet op de risico’s van contact via {naam}, en verwijzen niet door.',
    'toestel' => 'De app van {naam} mag nooit op een telefoon, tablet of computer van het Rijk.',
    'gedrag' => 'Gedragsbeïnvloeding mag op {naam} alleen met advertenties.',
];

function oc_tekst(string $sleutel, string $naam = '', string $grond = ''): string
{
    return trim(strtr(OC_TEKSTEN[$sleutel], ['{naam}' => $naam, '{grond}' => $grond]));
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

    return $kader + ['explain' => false, 'toestel' => false, 'id' => $k];
}

/** Of dit het basiskanaal is, waar alles mag: ook dienstverlening en veilig contact. */
function oc_basis(array $kader): bool
{
    return in_array('persoonlijk', (array) ($kader['contact'] ?? []), true);
}

/** Of gedragsbeïnvloeding alleen met advertenties mag: in 'betaald', niet in 'doelen'. Zo is het bij TikTok. */
function oc_gedrag_betaald(array $kader): bool
{
    return $kader['doelen'] !== null && in_array('gedrag', $kader['betaald'], true)
        && !in_array('gedrag', $kader['doelen'], true);
}

/** Of de voorwaarden gelden. Niet op het basiskanaal. */
function oc_voorwaarden_gelden(array $a): bool
{
    $kader = oc_kader($a);

    return $kader === null || !oc_basis($kader);
}

/** Of alles beantwoord is wat de uitkomst bepaalt. */
function oc_compleet(array $a): bool
{
    if ((string) ($a['sm_platform'] ?? '') === '') {
        return false;
    }

    return !oc_voorwaarden_gelden($a) || array_reduce(
        ['oc_contact', 'sm_eigen_kanalen', 'sm_doorverwijzen'],
        static fn (bool $ok, string $key): bool => $ok && (string) ($a[$key] ?? '') !== '',
        true
    );
}

/**
 * Alles wat pagina en document nodig hebben. Werkt ook half ingevuld: dan is
 * de uitkomst leeg.
 *
 * @return array<string,mixed>
 */
function oc_evalueer(array $a): array
{
    $kader = oc_kader($a) ?? sm_kaders()['anders'] + ['explain' => true, 'toestel' => false, 'id' => 'anders'];
    $naam = $kader['naam'];
    $gelden = oc_voorwaarden_gelden($a);
    $eigen = $gelden ? (string) ($a['sm_eigen_kanalen'] ?? '') : '';
    $contact = $gelden && ($a['oc_contact'] ?? '') === 'ja';

    $afwijkingen = array_values(array_filter([
        $kader['explain'] ? oc_platform_tekst($kader) : '',
        $contact ? oc_tekst('contact', $naam) : '',
        $eigen === 'deels' ? oc_tekst('eigen_deels') : ($eigen === 'nee' ? oc_tekst('eigen_nee') : ''),
        $gelden && ($a['sm_doorverwijzen'] ?? '') === 'nee' ? oc_tekst('doorverwijzen', $naam) : '',
    ]));

    $uitkomst = match (true) {
        !oc_compleet($a) => '',
        $afwijkingen !== [] => 'explain',
        default => 'comply',
    };

    $letOp = array_values(array_filter([
        $kader['toestel'] ? oc_tekst('toestel', $naam) : '',
        oc_gedrag_betaald($kader) ? oc_tekst('gedrag', $naam) : '',
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
        'let_op' => $letOp,
        'herziening' => (new DateTimeImmutable('now'))->modify('+1 year'),
    ];
}

/** Wat het kader over het platform zegt, in één zin. */
function oc_platform_tekst(array $kader): string
{
    return match (true) {
        $kader['inzet'] === 'geen' => oc_tekst('geen_kader', $kader['naam']),
        (bool) $kader['explain'] => oc_tekst('explain', $kader['naam'], OC_GRONDEN[$kader['id']] ?? ''),
        oc_basis($kader) => oc_tekst('basis', $kader['naam']),
        default => oc_tekst('inzetbaar', $kader['naam']),
    };
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
            'grond' => OC_GRONDEN[$k] ?? '',
            'basis' => oc_basis($kader),
            'gedrag' => oc_gedrag_betaald($kader),
        ];
    }

    return [
        'kaders' => $kaders,
        'inzet' => SM_INZET,
        'uitkomsten' => OC_UITKOMSTEN,
        'teksten' => OC_TEKSTEN,
    ];
}
