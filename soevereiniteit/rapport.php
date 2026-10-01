<?php

/**
 * Het rapport: de scan als blokken.
 *
 * Na de titelpagina komt een samenvatting met het label, en per thema of het
 * hoog genoeg is. Daarna de acht thema's met elke vraag en elk antwoord. Het
 * laatste hoofdstuk legt uit hoe de scan rekent, voor wie het wil nagaan. Een
 * blok is [soort, inhoud]; zie ../README.md.
 */

declare(strict_types=1);

/** @return list<array<int,mixed>> */
function sv_rapport(array $a, array $u): array
{
    $naam = trim((string) ($a['projectnaam'] ?? '')) ?: 'Naamloos project';
    $opmerkingen = cm_regels((string) ($a['sv_opmerkingen'] ?? ''));

    return [
        ...sv_rapport_inleiding($a, $naam),
        ...sv_rapport_samenvatting($u),
        ...sv_rapport_themas($a, $u),
        ...sv_rapport_rekenwijze(),
        ...($opmerkingen === [] ? [] : [
            ['h2', 'Opmerkingen'],
            ...array_map(static fn (string $regel): array => ['alinea', $regel], $opmerkingen),
        ]),
    ];
}

function sv_rapport_inleiding(array $a, string $naam): array
{
    return [
        ['h1', 'Soevereiniteitsscan ' . $naam],
        ['term', 'Clouddienst', (string) $a['cloud_leverancier']],
        ['term', 'Soort leverancier', cm_antwoord_tekst('sv_profiel', $a)],
        ['term', 'Informatie', cm_antwoord_tekst('sv_belang', $a)],
        ['term', 'Datum', cm_datum_nl(new DateTimeImmutable('now'))],
        ['term', 'Opgesteld door', (string) $a['aanvrager']],
        ...(($a['organisatieonderdeel'] ?? '') === '' ? [] : [['term', 'Organisatie', (string) $a['organisatieonderdeel']]]),
        ...(($a['email'] ?? '') === '' ? [] : [['term', 'Contact', (string) $a['email']]]),
    ];
}

function sv_rapport_samenvatting(array $u): array
{
    $label = SV_LABELS[$u['label']];
    $rijen = [];

    foreach ($u['themas'] as $id => $thema) {
        $rijen[] = [
            SV_THEMAS[$id]['naam'],
            sv_label($thema['niveau']),
            $thema['eis'] > 0 ? SV_LABELS[$thema['eis']]['letter'] : 'Geen eis',
            $thema['te_laag'] ? 'Nee' : 'Ja',
        ];
    }

    return [
        ['h2', 'Samenvatting'],
        ['alinea', 'Deze scan laat zien hoeveel grip Europa heeft op de clouddienst. Hij volgt het Cloud Sovereignty '
            . 'Framework van de Europese Commissie, in een eenvoudige vorm.'],
        ['term', 'Label', sv_label($u['label'])],
        ['term', 'Score', $u['score'] . ' van de 100'],
        ['term', 'Nodig', $u['eis']],
        ['term', 'Past het', $u['oordeel']],
        ['h3', 'Wat betekent label ' . $label['letter'] . '?'],
        ['alinea', $label['betekenis']],
        ['alinea', 'Het label is een gemiddelde. Een thema kan dus lager uitkomen dan het label. Daarom staat hieronder '
            . 'per thema of het hoog genoeg is.'],
        ['tabel', [
            'kop' => ['Thema', 'Label', 'Nodig', 'Hoog genoeg?'],
            'rijen' => $rijen,
            'breedtes' => [3.8, 6.8, 2.8, 3.2],
            'rijkop' => true,
        ]],
        ...($u['tips'] === [] ? [] : [['h3', 'Wat kan beter?'], ['lijst', $u['tips']]]),
        ...($u['onbekend'] === [] ? [] : [
            ['h3', 'Nog uitzoeken'],
            ['alinea', 'Bij deze vragen staat ‘Weet ik niet’. Dat telt als het laagste niveau, dus het label kan hoger '
                . 'uitkomen als jullie het uitzoeken.'],
            ['lijst', $u['onbekend']],
        ]),
        ['h3', 'Vervolgstappen'],
        ['genummerd', $u['vervolg']],
    ];
}

/** Per thema het label, en elke vraag met het antwoord en wat dat waard is. */
function sv_rapport_themas(array $a, array $u): array
{
    $b = [
        ['h2', 'De acht thema’s'],
        ['alinea', 'Per thema staan hier de vragen en de antwoorden. Het zwakste antwoord bepaalt het label van het '
            . 'thema. Een vraag die niet van toepassing was, staat er niet bij.'],
    ];

    foreach (SV_THEMAS as $id => $thema) {
        $rijen = [];
        foreach (sv_vragen_van($id) as $vraag) {
            $waarde = (string) ($a[$vraag['key']] ?? '');
            if ($waarde === '') {
                continue;
            }

            $niveau = $vraag['opties'][$waarde]['niveau'];
            $rijen[] = [$vraag['label'], cm_antwoord_tekst($vraag['key'], $a),
                $niveau === null ? 'Telt niet mee' : SV_LABELS[$niveau]['letter']];
        }

        array_push($b,
            ['h3', $thema['naam'] . ': ' . lcfirst($thema['titel'])],
            ['term', 'Label', sv_label($u['themas'][$id]['niveau'])],
            ['term', 'Weegt mee', $thema['gewicht'] . ' procent'],
            ['alinea', $thema['intro']],
            ['tabel', [
                'kop' => ['Vraag', 'Antwoord', 'Waard'],
                'rijen' => $rijen,
                'breedtes' => [7, 7.6, 2],
            ]],
        );
    }

    return $b;
}

/** Hoe de scan rekent, en waar dat vandaan komt. */
function sv_rapport_rekenwijze(): array
{
    $labels = [];
    foreach (array_reverse(SV_LABELS, true) as $niveau => $label) {
        $labels[] = [$label['letter'], ucfirst($label['naam']), 'SEAL ' . $niveau, $label['seal']];
    }

    $themas = [];
    foreach (SV_THEMAS as $thema) {
        $themas[] = [$thema['naam'], 'SOV ' . $thema['sov'] . ', ' . $thema['eu'], $thema['gewicht'] . '%'];
    }

    $eisen = [];
    foreach (SV_BELANG as $belang => $nodig) {
        $eisen[] = [
            (string) cm_alle_vragen()['sv_belang']['opties'][$belang]['label'],
            SV_LABELS[$nodig]['letter'],
            $nodig > 1 ? SV_LABELS[$nodig - 1]['letter'] : 'Geen eis',
        ];
    }

    return [
        ['h2', 'Zo rekent de scan'],
        ['alinea', 'De Europese Commissie beoordeelt een clouddienst op acht doelen. Per doel krijgt de dienst een '
            . 'niveau, van SEAL 0 tot SEAL 4. SEAL staat voor Sovereignty Effectiveness Assurance Level. Deze scan '
            . 'doet hetzelfde, maar met eenvoudige vragen. Elk label hoort bij een SEAL niveau.'],
        ['tabel', [
            'kop' => ['Label', 'Naam', 'Niveau', 'Naam in het framework'],
            'rijen' => $labels,
            'breedtes' => [1.8, 6.2, 2.4, 6.2],
            'rijkop' => true,
        ]],
        ['alinea', 'Elk antwoord is een niveau waard. Het zwakste antwoord bepaalt het niveau van een thema. Dat staat '
            . 'ook in het framework: een zwak punt verlaagt het niveau van het hele doel.'],
        ['alinea', 'De score telt alle antwoorden mee. Per thema telt de scan de punten op, en deelt die door het '
            . 'maximum. Dat deel gaat maal het gewicht van het thema. Samen geeft dat een score van 0 tot 100. Die '
            . 'formule en de gewichten komen uit het framework.'],
        ['tabel', [
            'kop' => ['Thema', 'Doel in het framework', 'Gewicht'],
            'rijen' => $themas,
            'breedtes' => [3.8, 10.4, 2.4],
            'rijkop' => true,
        ]],
        ['alinea', 'Het label volgt uit de score:'],
        ['lijst', ['A: 88 tot en met 100', 'B: 63 tot en met 87', 'C: 38 tot en met 62', 'D: 13 tot en met 37',
            'E: 0 tot en met 12']],
        ['alinea', 'Wat minimaal nodig is, hangt af van de informatie in de dienst. Drie thema’s vormen de kern: '
            . 'recht, data en beheer. Die moeten het niveau in de tabel halen. Voor zeggenschap, keten, techniek en '
            . 'beveiliging mag het één niveau lager. Duurzaamheid telt mee in de score, maar houdt niets tegen.'],
        ['tabel', [
            'kop' => ['Informatie', 'Kern', 'Andere thema’s'],
            'rijen' => $eisen,
            'breedtes' => [9.6, 3, 4],
            'rijkop' => true,
        ]],
        ['alinea', 'De vragen, het label en de eisen zijn een eigen vereenvoudiging. In een echte aanbesteding stelt de '
            . 'opdrachtgever zelf de vragen en de eisen vast. Gebruik deze scan daarom als eerste indruk, en als begin '
            . 'van het gesprek met de leverancier.'],
        ['alinea', 'Bron: Cloud Sovereignty Framework, versie 1.2.1, Europese Commissie, oktober 2025. De uitleg per '
            . 'niveau is gebaseerd op eucloudpatterns.eu.'],
    ];
}
