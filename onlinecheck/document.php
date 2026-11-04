<?php

/**
 * Het document: de uitkomst van de check voor één kanaal. Bovenaan de uitkomst,
 * dan de voorwaarden, de uitleg als het kanaal niet voldoet, en als bijlage de
 * risico's uit het kader van het platform.
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

    $uitleg = $u['uitkomst'] === 'explain' ? [
        ['h2', '2. Uitleg (explain)'],
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

        ['h2', '1. Voorwaarden'],
        ['term', 'Platform', oc_platform_tekst($kader)],
        ...(oc_voorwaarden_gelden($a) ? [
            ['term', 'Dienstverlening of veilig contact', $t('oc_contact')],
            ['term', 'Ook op een eigen kanaal', $t('sm_eigen_kanalen')],
            ['term', 'Wijst op risico’s en verwijst door', $t('sm_doorverwijzen')],
        ] : []),
        ...($u['let_op'] === [] ? [] : [['h3', 'Let op'], ['lijst', $u['let_op']]]),

        ...$uitleg,

        ['h2', ($uitleg === [] ? '2' : '3') . '. Vervolg'],
        ['genummerd', [
            'De directeur Communicatie stelt dit vast, na advies van de CPO, de CISO en waar nodig de CIO.',
            'De bestuursraad toetst het elk jaar. De volgende keer is uiterlijk ' . cm_datum_nl($u['herziening']) . '.',
        ]],

        ['h2', 'Bijlage: de risico’s van ' . $kader['naam']],
        ['alinea', 'Uit het rijksbrede kader. Gebruik ze bij de maatregelen.'],
        ['lijst', $kader['risicos']],
    ];
}
