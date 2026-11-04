<?php

/**
 * Het document: de uitkomst van de check voor één kanaal. Bovenaan de uitkomst,
 * dan de doelen en de voorwaarden, bij een explain de uitleg, en als bijlage
 * het kader van het platform.
 */

declare(strict_types=1);

/**
 * @param array<string,mixed> $a antwoorden
 * @param array<string,mixed> $u uitkomst van oc_evalueer()
 * @return list<array<int,mixed>>
 */
function oc_document(array $a, array $u): array
{
    $kader = $u['kader'];
    $t = static fn (string $key): string => cm_antwoord_tekst($key, $a);
    $vrij = static fn (string $key): string => implode(' ', cm_regels((string) ($a[$key] ?? '')));

    $kop = [
        ['h1', 'Online communicatie check: ' . ($a['projectnaam'] ?: 'naamloos')],
        ['term', 'Platform', $kader['naam']],
        ['term', 'Kader', $u['inzet']['label']],
        ['term', 'Ingevuld door', (string) ($a['aanvrager'] ?: 'onbekend')],
        ['term', 'Ingevuld op', cm_datum_nl(new DateTimeImmutable('now'))],
    ];
    foreach (['organisatieonderdeel' => 'Organisatie', 'sm_directeur' => 'Vastgesteld door'] as $key => $label) {
        if (trim((string) ($a[$key] ?? '')) !== '') {
            $kop[] = ['term', $label, (string) $a[$key]];
        }
    }
    $kop[] = ['term', 'Uitkomst', $u['label']];
    $kop[] = ['term', 'Herzien uiterlijk', cm_datum_nl($u['herziening'])];

    $toets = $kader['doelen'] === null
        ? oc_tekst('doelen_geen_kader')
        : ($u['buiten'] === [] ? oc_tekst('doelen_past', $kader['naam']) : oc_tekst('doelen', $kader['naam'], $u['buiten']));

    $uitleg = $u['uitkomst'] === 'explain' ? [
        ['h2', '3. Explain'],
        ['h3', 'Waarvan wordt afgeweken'],
        ['lijst', cm_regels((string) ($a['oc_afwijking'] ?? ''))],
        ['h3', 'Waarom dit kanaal nodig is'],
        ['alinea', $vrij('oc_noodzaak')],
        ['h3', 'Waarom een minder risicovol kanaal niet volstaat'],
        ['alinea', $vrij('oc_alternatief')],
        ['h3', 'Maatregelen tegen de risico’s'],
        ['lijst', cm_regels((string) ($a['oc_maatregelen'] ?? ''))],
    ] : [];

    return [
        ...$kop,
        ['citaat', implode(' ', $u['redenen'])],

        ['h2', '1. Doelen'],
        ['term', 'Doelen', $t('oc_doelen')],
        ['alinea', $toets],
        ...($u['let_op'] === [] ? [] : [['h3', 'Let op'], ['lijst', $u['let_op']]]),

        ['h2', '2. Voorwaarden'],
        ...(oc_eigen_geldt($a) ? [['term', 'Ook op een eigen kanaal', $t('sm_eigen_kanalen')]] : []),
        ['term', 'Wijst op risico’s en verwijst door', $t('sm_doorverwijzen')],

        ...$uitleg,

        ['h2', ($uitleg === [] ? '3' : '4') . '. Vervolg'],
        ['genummerd', [
            'De directeur Communicatie stelt dit vast, na advies van de CPO, de CISO en waar nodig de CIO.',
            'De bestuursraad toetst het elk jaar. De volgende keer is uiterlijk ' . cm_datum_nl($u['herziening']) . '.',
            'Wil je het kanaal grondiger toetsen, of starten of stoppen op een platform? Gebruik dan de kanalenscan.',
        ]],

        ['h2', 'Bijlage: het kader voor ' . $kader['naam']],
        ['term', 'Advies', $u['inzet']['label']],
        ['alinea', $kader['uitleg']],
        ['term', 'Doelgroep', $kader['doelgroep']],
        ['h3', 'Risico’s'],
        ['lijst', $kader['risicos']],
        ...($kader['doelen'] === null ? [] : [
            ['h3', 'Doelen binnen het kader'],
            ['alinea', oc_document_ruimte($kader)],
        ]),
    ];
}

/** De doelen binnen het kader als opsomming, met de doelen die alleen met advertenties passen. */
function oc_document_ruimte(array $kader): string
{
    $lijst = [];
    foreach (oc_doelen() as $k => $doel) {
        $ruimte = oc_ruimte($kader, $k);
        if ($ruimte === 'ja') {
            $lijst[] = $doel['label'];
        } elseif ($ruimte === 'betaald') {
            $lijst[] = $doel['label'] . ' (alleen met advertenties)';
        }
    }

    return $lijst === [] ? 'Geen.' : cm_opsomming($lijst) . '.';
}
