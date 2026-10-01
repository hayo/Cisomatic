<?php

/**
 * Afleidingen: van de antwoorden naar een label.
 *
 * Zo rekent het Cloud Sovereignty Framework, in het klein:
 *
 * - elk antwoord is een SEAL niveau waard, van 0 tot 4 (zie vragen.php);
 * - het niveau van een thema is het zwakste antwoord, want een zwak punt
 *   verlaagt volgens het framework het niveau van de hele doelstelling;
 * - de score is per thema het deel van de maximale punten, maal het gewicht
 *   van het thema, opgeteld tot een getal van 0 tot 100;
 * - het label volgt uit die score: A bij 88 of meer, E bij 12 of minder.
 *
 * Wat minimaal nodig is, hangt af van de informatie in de dienst. regels.js
 * rekent hetzelfde tijdens het invullen; PHP rekent bij het versturen opnieuw.
 */

declare(strict_types=1);

/**
 * De labels, van E tot A. De sleutel is het SEAL niveau. De naam staat zoals
 * hij midden in een zin staat, want Europees houdt altijd zijn hoofdletter.
 */
const SV_LABELS = [
    0 => [
        'letter' => 'E', 'naam' => 'niet in Europese hand', 'seal' => 'No Sovereignty',
        'betekenis' => 'Europa heeft bijna geen grip op deze dienst. Partijen en wetten van buiten de EU bepalen wat '
            . 'er gebeurt.',
    ],
    1 => [
        'letter' => 'D', 'naam' => 'Europees op papier', 'seal' => 'Jurisdictional Sovereignty',
        'betekenis' => 'Europees recht geldt, maar vooral op papier. De echte macht ligt meestal buiten de EU. Een '
            . 'land buiten de EU kan de leverancier dan dwingen om data af te geven, of om te stoppen.',
    ],
    2 => [
        'letter' => 'C', 'naam' => 'deels in Europese hand', 'seal' => 'Data Sovereignty',
        'betekenis' => 'Europa heeft op een deel van de dienst grip. Op andere punten hangt de dienst nog af van '
            . 'partijen buiten de EU.',
    ],
    3 => [
        'letter' => 'B', 'naam' => 'grotendeels in Europese hand', 'seal' => 'Digital Resilience',
        'betekenis' => 'Europa heeft op de meeste punten grip. Partijen buiten de EU spelen nog maar een kleine rol.',
    ],
    4 => [
        'letter' => 'A', 'naam' => 'volledig in Europese hand', 'seal' => 'Full Digital Sovereignty',
        'betekenis' => 'Europa heeft overal grip. Alleen Europees recht geldt, en nergens hangt de dienst echt af van '
            . 'een partij buiten de EU.',
    ],
];

/**
 * Per soort informatie het niveau dat de kern minimaal moet halen: recht,
 * data en beheer. De andere thema's mogen één niveau lager zijn, behalve
 * duurzaamheid: dat telt alleen mee in de score.
 */
const SV_BELANG = ['openbaar' => 1, 'gewoon' => 2, 'gevoelig' => 3, 'geheim' => 4];

/** "C · deels in Europese hand". */
function sv_label(int $niveau): string
{
    return SV_LABELS[$niveau]['letter'] . ' · ' . SV_LABELS[$niveau]['naam'];
}

/** Het niveau dat een thema minimaal moet halen bij deze informatie. */
function sv_eis(string $thema, array $a): int
{
    $nodig = SV_BELANG[$a['sv_belang'] ?? ''] ?? 0;

    return match (SV_THEMAS[$thema]['eis']) {
        'kern' => $nodig,
        'rest' => max(0, $nodig - 1),
        default => 0,
    };
}

/** De namen van een paar thema's, klein en op een rij. @param list<string> $ids */
function sv_themanamen(array $ids): string
{
    return cm_opsomming(array_map(static fn (string $id): string => mb_strtolower(SV_THEMAS[$id]['naam'], 'UTF-8'), $ids));
}

/** Wat de kern en de rest minimaal moeten halen, in woorden. */
function sv_eis_tekst(array $a): string
{
    $zinnen = [];

    foreach (['kern', 'rest'] as $soort) {
        $ids = array_keys(array_filter(SV_THEMAS, static fn (array $t): bool => $t['eis'] === $soort));
        $niveau = sv_eis($ids[0], $a);

        if ($niveau > 0) {
            $zinnen[] = 'Minimaal ' . SV_LABELS[$niveau]['letter'] . ' bij ' . sv_themanamen($ids) . '.';
        }
    }

    return implode(' ', $zinnen);
}

/**
 * Het model voor regels.js: per thema het gewicht, de eis en per vraag de
 * niveaus, en verder de labels en de eisen. Zo staan de getallen maar op één
 * plek. Krijgt de vragen mee, want het wordt midden in sv_secties() gebouwd.
 *
 * @param array<string,list<array<string,mixed>>> $vragen
 * @return array<string,mixed>
 */
function sv_model(array $vragen): array
{
    $themas = [];

    foreach (SV_THEMAS as $id => $thema) {
        $perVraag = [];
        foreach ($vragen[$id] as $vraag) {
            $perVraag[$vraag['key']] = [
                'kort' => $vraag['kort'],
                'niveaus' => array_map(static fn (array $optie): ?int => $optie['niveau'], $vraag['opties']),
            ];
        }

        $themas[] = ['id' => $id, 'naam' => $thema['naam'], 'gewicht' => $thema['gewicht'], 'eis' => $thema['eis'],
            'vragen' => $perVraag];
    }

    return [
        'themas' => $themas,
        'labels' => array_map(static fn (array $l): array => ['letter' => $l['letter'], 'naam' => $l['naam'],
            'betekenis' => $l['betekenis']], SV_LABELS),
        'belang' => SV_BELANG,
    ];
}

/** De vragen van een thema, zoals ze in het formulier staan. @return list<array<string,mixed>> */
function sv_vragen_van(string $thema): array
{
    foreach (cm_secties() as $sectie) {
        if (($sectie['thema'] ?? '') === $thema) {
            return array_values(array_filter($sectie['vragen'], static fn (array $v): bool => $v['type'] === 'radio'));
        }
    }

    return [];
}

/**
 * De uitslag van één thema. Een verborgen vraag is leeg, en een antwoord
 * zonder niveau telt niet mee; beide vallen weg.
 *
 * @return array{id:string,niveau:int,deel:float,eis:int,te_laag:bool,antwoorden:list<array<string,mixed>>}
 */
function sv_thema(string $id, array $a): array
{
    $antwoorden = [];

    foreach (sv_vragen_van($id) as $vraag) {
        $waarde = (string) ($a[$vraag['key']] ?? '');
        $niveau = $vraag['opties'][$waarde]['niveau'] ?? null;

        if ($niveau !== null) {
            $antwoorden[] = ['vraag' => $vraag, 'waarde' => $waarde, 'niveau' => $niveau];
        }
    }

    $niveaus = array_column($antwoorden, 'niveau');
    $niveau = $niveaus === [] ? 0 : min($niveaus);
    $eis = sv_eis($id, $a);

    return [
        'id' => $id,
        'niveau' => $niveau,
        'deel' => $niveaus === [] ? 0.0 : array_sum($niveaus) / (4 * count($niveaus)),
        'eis' => $eis,
        'te_laag' => $niveau < $eis,
        'antwoorden' => $antwoorden,
    ];
}

/**
 * Wat jullie kunnen doen: de tip bij elk antwoord dat te laag is voor de
 * eis van zijn thema. 'Weet ik niet' staat bij het uitzoekwerk.
 *
 * @param array<string,array<string,mixed>> $themas
 * @return list<string>
 */
function sv_tips(array $themas): array
{
    $tips = [];

    foreach ($themas as $thema) {
        foreach ($thema['antwoorden'] as $x) {
            if ($x['niveau'] < $thema['eis'] && $x['waarde'] !== 'onbekend') {
                $tips[] = $x['vraag']['kort'] . ': ' . $x['vraag']['tip'];
            }
        }
    }

    return $tips;
}

/** @return list<string> */
function sv_onbekend(array $themas): array
{
    $open = [];

    foreach ($themas as $thema) {
        foreach ($thema['antwoorden'] as $x) {
            if ($x['waarde'] === 'onbekend') {
                $open[] = $x['vraag']['kort'] . ': ' . $x['vraag']['label'];
            }
        }
    }

    return $open;
}

/** @return list<string> */
function sv_vervolg(array $a, bool $tips, bool $onbekend): array
{
    $stappen = [
        !$tips ? '' : 'Bespreek de verbeterpunten met de leverancier. Leg vast wat jullie afspreken, in het contract.',
        !$tips ? '' : 'Lukt dat niet? Kijk dan naar een andere leverancier, met een hoger label.',
        ($a['sv_belang'] ?? '') === 'geheim'
            ? 'Voor staatsgeheimen gelden strenge regels. Overleg daarom altijd met de beveiligingsambtenaar.'
            : '',
        $onbekend ? 'Vraag de leverancier om de antwoorden die nog ontbreken. Doe de scan daarna opnieuw.' : '',
        'Vul ook quickscan 2.0 in. Die laat zien wat de BIO2 en het cloudbeleid van het Rijk vragen. Laad daar het '
            . 'bestand van deze scan in, dan staat een deel er al.',
        'Doe de scan opnieuw als de leverancier of het contract verandert, en minstens één keer per jaar.',
    ];

    return array_values(array_filter($stappen));
}

/**
 * Alles wat pagina en rapport nodig hebben, in één keer uitgerekend.
 *
 * @return array<string,mixed>
 */
function sv_evalueer(array $a): array
{
    $themas = [];
    $score = 0.0;

    foreach (SV_THEMAS as $id => $thema) {
        $themas[$id] = sv_thema($id, $a);
        $score += $thema['gewicht'] * $themas[$id]['deel'];
    }

    $score = (int) round($score);
    $teLaag = array_keys(array_filter($themas, static fn (array $t): bool => $t['te_laag']));
    $tips = sv_tips($themas);
    $onbekend = sv_onbekend($themas);

    return [
        'themas' => $themas,
        'score' => $score,
        'label' => min(4, intdiv($score + 12, 25)),
        'eis' => sv_eis_tekst($a),
        'past' => $teLaag === [],
        'oordeel' => $teLaag === [] ? 'Ja, elk thema is hoog genoeg.' : 'Nee, te laag bij ' . sv_themanamen($teLaag) . '.',
        'te_laag' => $teLaag,
        'tips' => $tips,
        'onbekend' => $onbekend,
        'vervolg' => sv_vervolg($a, $tips !== [], $onbekend !== []),
    ];
}
