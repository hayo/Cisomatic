<?php

/**
 * Het advies als blokken, waar de motor de pagina en het .odt-bestand van maakt.
 *
 * Alle tekst is platte tekst. Nadruk zit in de soort van het blok: een term,
 * een tabel of een kop. Een antwoord wordt dus nooit per ongeluk vet.
 */

declare(strict_types=1);

/**
 * @param array<string,mixed> $a antwoorden
 * @param array<string,mixed> $u uitkomst van de beslisboom
 * @return list<array<int,mixed>>
 */
function qs_document(array $a, array $u): array
{
    return [
        ...qs_advies_kop($a, $u),
        ...qs_advies_uitkomst($a, $u),
        ...qs_advies_eisen($a, $u),
        ...qs_advies_wegingen($u),
        ...qs_advies_archief($a, $u),
        ...qs_advies_kaders($u),
        ...qs_advies_onderbouwing($u),
        ...qs_advies_antwoorden($a),
        ['lijn', ''],
        ['alinea', 'Volgende stap: stuur dit advies samen met de onderliggende stukken naar de '
            . 'beoordelaar voor de risicoanalyse en de vaststelling.'],
    ];
}

/** Een tabel met een label per rij en de waarde ernaast. */
function qs_termtabel(array $rijen): array
{
    return ['tabel', ['rijen' => $rijen, 'breedtes' => [5.2, 11.4], 'rijkop' => true]];
}

/** Titel, wie en wanneer, en in welke stap. */
function qs_advies_kop(array $a, array $u): array
{
    $beoordeeld = ($a['rol'] ?? 'behoeftesteller') === 'beoordelaar';

    $b = [
        ['h1', 'IB & Privacy Quickscan: ' . ($a['projectnaam'] ?: 'naamloos project')],
        ['term', 'Ingevuld op', cm_datum_nl(new DateTimeImmutable('now'), true)],
        ['term', 'Ingevuld door', (string) ($a['aanvrager'] ?: 'onbekend')],
    ];

    foreach (['organisatieonderdeel' => 'Team of afdeling', 'email' => 'Contact'] as $key => $label) {
        if (trim((string) ($a[$key] ?? '')) !== '') {
            $b[] = ['term', $label, (string) $a[$key]];
        }
    }

    $b[] = ['term', 'Rol', qs_antwoord_tekst('rol', $a)];

    if ($beoordeeld && trim((string) ($a['beoordelaar_naam'] ?? '')) !== '') {
        $b[] = ['term', 'Beoordeeld door', (string) $a['beoordelaar_naam']];
    }

    $b[] = ['alinea', 'Dit advies is automatisch samengesteld op basis van de ingevulde antwoorden en de '
        . 'beslisregels uit de IB & Privacy Quickscan.'];
    $b[] = ['citaat', $beoordeeld
        ? 'Stap 2. De wegingen hieronder zijn vastgesteld door de beoordelaar. Daarmee zijn de eisen '
            . 'definitief, behoudens collegiaal advies en vaststelling.'
        : 'Stap 1. De beschrijving komt van de behoeftesteller. De wegingen hieronder zijn voorlopig: de '
            . 'beoordelaar stelt ze in stap 2 definitief vast. Gebruik dit advies om te zien wat er '
            . 'waarschijnlijk nodig is, niet als eindoordeel.'];

    if (!empty($u['uitzondering'])) {
        $b[] = ['citaat', 'Uitzonderingsgeval: géén te beschermen informatie. De volledige scan is niet '
            . 'ingevuld omdat er volgens de invuller niets te beschermen valt. De beoordelaar toetst die '
            . 'motivatie.'];
        $b[] = ['h3', 'Opgegeven motivatie'];
        $motivatie = cm_regels((string) ($a['geen_bescherming_motivatie'] ?? ''));
        foreach ($motivatie === [] ? ['Niet ingevuld.'] : $motivatie as $regel) {
            $b[] = ['alinea', $regel];
        }
    }

    return $b;
}

/** De uitkomst in het kort, de aannames, wat blokkeert en welke analyses nodig zijn. */
function qs_advies_uitkomst(array $a, array $u): array
{
    $kort = [
        ['Beveiligingsniveau', $u['ib_niveau']],
        ['Betrouwbaarheidseisen', sprintf(
            'Beschikbaarheid %s, integriteit %s, vertrouwelijkheid %s',
            cm_niveau_label($u['biv']['b']),
            cm_niveau_label($u['biv']['i']),
            cm_niveau_label($u['biv']['v'])
        )],
        ['Risiconiveau', ucfirst((string) $u['risiconiveau'])],
        ['Herstel', 'RTO ' . $u['rto'] . ', RPO ' . $u['rpo']],
        ['Cloudbeleid', $u['cloud_categorie'] !== null
            ? 'Categorie ' . qs_cloud_label($u['cloud_categorie'])
            : 'Niet van toepassing, er wordt geen clouddienst gebruikt'],
    ];

    if (empty($u['uitzondering'])) {
        $kort[] = ['Dreigingsprofiel', ucfirst((string) $u['dreiging']['niveau'])];

        if (($u['ai_risico']['niveau'] ?? 'geen') !== 'geen') {
            $kort[] = ['AI verordening', $u['ai_risico']['label']];
        }
    }

    $kort[] = ['Aanvullende analyses', $u['analyses'] === []
        ? 'Geen'
        : implode(', ', array_column($u['analyses'], 'naam'))];

    $b = [['h2', 'De uitkomst in het kort'], qs_termtabel($kort)];

    if ($u['aannames'] !== []) {
        $b[] = ['h2', 'Let op: hier is iets aangenomen'];
        $b[] = ['alinea', count($u['aannames']) === 1
            ? 'Op één vraag is "weet ik niet" geantwoord. De tool heeft daar een veilige aanname voor gedaan '
                . 'en het advies daarop gebaseerd. Klopt de aanname niet, dan verandert de uitkomst.'
            : sprintf(
                'Op %d vragen is "weet ik niet" geantwoord. De tool heeft daar veilige aannames voor gedaan '
                . 'en het advies daarop gebaseerd. Kloppen die aannames niet, dan verandert de uitkomst.',
                count($u['aannames'])
            )];
        $b[] = ['tabel', [
            'kop' => ['Vraag', 'Aangenomen'],
            'rijen' => array_map(static fn (array $x): array => [$x['vraag'], $x['aanname']], $u['aannames']),
            'breedtes' => [10.6, 6],
        ]];
    }

    if ($u['blokkerend'] !== []) {
        $b[] = ['h2', 'Dit blokkeert het voorstel'];
        $b[] = ['alinea', 'Zolang het volgende niet is opgelost, kan deze combinatie van proces en systeem niet '
            . 'worden goedgekeurd.'];
        $b[] = ['lijst', $u['blokkerend']];
    }

    $b[] = ['h2', 'Aanvullende analyses en documenten'];

    if ($u['analyses'] === []) {
        $b[] = ['alinea', 'Er zijn geen aanvullende analyses nodig. De standaardmaatregelen volstaan.'];
    } else {
        $b[] = ['alinea', 'Zolang deze analyses of documenten ontbreken, kunnen er nog geen definitieve eisen '
            . 'aan de leverancier en aan onszelf worden gesteld. Uitkomsten van analyses moeten eerst worden '
            . 'omgezet naar eisen; documenten moeten eerst worden goedgekeurd.'];
        $b[] = ['tabel', [
            'kop' => ['Analyse', 'Waarom', 'Beoordeling door'],
            'rijen' => array_map(
                static fn (array $x): array => [$x['naam'], $x['reden'], $x['beoordelaar']],
                $u['analyses']
            ),
            'breedtes' => [4, 9.2, 3.4],
        ]];
    }

    return $b;
}

/** De eisen aan proces en systeem, en het cloudgebruik. */
function qs_advies_eisen(array $a, array $u): array
{
    $b = [
        ['h2', 'Eisen aan het proces en het systeem'],
        ['lijst', [
            'Het toe te passen beveiligingsniveau is ' . $u['ib_niveau'] . '.',
            sprintf(
                'De Recovery Time Objective is %s; die volgt rechtstreeks uit de beschikbaarheidseis %s.',
                $u['rto'],
                cm_niveau_label($u['biv']['b'])
            ),
            'De Recovery Point Objective is ' . $u['rpo'] . '.',
            ...$u['cloud_eisen'],
        ]],
    ];

    if ($u['cloud_categorie'] === null) {
        return $b;
    }

    $b[] = ['h2', 'Cloudgebruik'];
    $b[] = ['alinea', sprintf(
        'Er wordt %s gebruikgemaakt van clouddienstverlening. Gekozen vorm: %s. Locatie van de data: %s.',
        ($a['cloud'] ?? '') === 'mogelijk' ? 'mogelijk' : 'daadwerkelijk',
        qs_antwoord_tekst('cloud_type', $a),
        qs_antwoord_tekst('cloud_locatie', $a)
    )];

    $toelichting = qs_toelichting_cloud()[$u['cloud_categorie']] ?? null;
    if ($toelichting !== null) {
        $b[] = ['term', 'Het systeem valt in ' . $toelichting['titel'], $toelichting['data']];
    }

    $b[] = ['term', 'Materieel cloudgebruik', ($u['materieel_cloudgebruik']['waar'] ? 'ja. ' : 'nee. ')
        . $u['materieel_cloudgebruik']['reden']];

    return $b;
}

/** Per weging het niveau, wat dat inhoudt, waarom, en wie het bepaalde. */
function qs_advies_wegingen(array $u): array
{
    if (empty($u['wegingen'])) {
        return [];
    }

    $b = [
        ['h2', 'De wegingen en waarom'],
        ['alinea', 'Per weging staat hier het niveau, wat dat niveau volgens de quickscan betekent, waarom het '
            . 'zo is ingeschaald en wie dat heeft bepaald. Zo is achteraf na te gaan hoe deze beoordeling tot '
            . 'stand is gekomen, en of hij klopt.'],
    ];

    foreach ($u['wegingen'] as $weging) {
        $b[] = ['h3', $weging['label'] . ': ' . $weging['niveau']];

        if ($weging['omschrijving'] !== '') {
            $b[] = ['term', 'Wat dat niveau inhoudt', $weging['omschrijving']];
        }
        if (trim($weging['motivatie']) !== '') {
            $b[] = ['term', 'Motivatie', $weging['motivatie']];
        }

        $b[] = ['term', 'Herkomst', $weging['herkomst']];
    }

    return $b;
}

/** De stappen om de archiefprocessen in te richten. */
function qs_advies_archief(array $a, array $u): array
{
    if (empty($u['archief'])) {
        return [];
    }

    $toelichting = trim((string) ($a['bewaartermijn_toelichting'] ?? ''));
    $b = [
        ['h2', 'Zo richt je de archiefprocessen in'],
        ['alinea', 'Alles wat in dit proces wordt opgemaakt of ontvangen valt onder de Archiefwet 2026, '
            . 'ongeacht waar het staat of in welke vorm. Die wet brengt de overbrengingstermijn terug van '
            . 'twintig naar tien jaar, dus wat vroeger een zorg voor later was, valt nu binnen de looptijd van '
            . 'dit systeem. De volgende stappen horen bij de inrichting, en de meeste daarvan zijn goedkoper '
            . 'als je ze vóór ingebruikname regelt dan erna.'],
        ['term', 'Opgegeven bewaartermijn', qs_antwoord_tekst('bewaartermijn', $a)
            . ($toelichting === '' ? '' : ' (' . $toelichting . ')')],
    ];

    foreach ($u['archief'] as $nummer => $stap) {
        $b[] = ['h3', sprintf('%d. %s', $nummer + 1, $stap['titel'])];
        $b[] = ['alinea', $stap['tekst']];
    }

    return $b;
}

/** Wetten en regels, eisen aan de leverancier, eigen maatregelen en wie meekijkt. */
function qs_advies_kaders(array $u): array
{
    $b = [['h2', 'Wetten en regels'], ['lijst', $u['wetgeving']]];

    if ($u['kaders'] !== []) {
        $b[] = ['h3', 'Toe te passen afwegingskaders'];
        $b[] = ['lijst', $u['kaders']];
    }

    if ($u['eisen_leverancier'] !== []) {
        $b[] = ['h2', 'Op te nemen eisen aan de leverancier'];
        $b[] = ['lijst', $u['eisen_leverancier']];
    }

    $b[] = ['h2', 'Maatregelen die we zelf nemen'];
    $b[] = ['lijst', $u['maatregelen']];

    if ($u['aandachtspunten'] !== []) {
        $b[] = ['h2', 'Aandachtspunten'];
        $b[] = ['lijst', $u['aandachtspunten']];
    }

    $b[] = ['h2', 'Wie kijkt mee en wie stelt vast'];
    $b[] = ['term', 'Collegiaal advies', implode(', ', $u['collegiaal_advies_rollen'])];

    if ($u['cio_reden'] !== '') {
        $b[] = ['alinea', 'De CIO wordt betrokken omdat ' . $u['cio_reden'] . '.'];
    }

    $b[] = ['term', 'Vaststelling', implode(', ', $u['vaststelling'])];

    return $b;
}

/** Het gevolgde pad door de beslisboom, en de redenen achter de afleidingen. */
function qs_advies_onderbouwing(array $u): array
{
    $b = [
        ['h2', 'Hoe deze uitkomst tot stand komt'],
        ['alinea', 'Het gevolgde pad door de beslisboom. Een gevolg staat er alleen bij als de vraag iets in '
            . 'gang zette.'],
        ['tabel', [
            'kop' => ['Vraag', 'Antwoord', 'Gevolg'],
            'rijen' => array_map(
                static fn (array $s): array => [$s['vraag'], $s['antwoord'], $s['geraakt'] ? $s['gevolg'] : ''],
                $u['beslisboom']
            ),
            'breedtes' => [6, 5, 5.6],
        ]],
    ];

    $redenen = [];

    if (empty($u['uitzondering'])) {
        $redenen['Waarom het dreigingsprofiel ' . $u['dreiging']['niveau'] . ' is'] = $u['dreiging']['redenen'];
        $redenen['Waarom de verwerking wel of niet grootschalig is'] = $u['grootschalig']['redenen'] ?? [];

        if (($u['ai_risico']['niveau'] ?? 'geen') !== 'geen') {
            $redenen['Waarom de inzet van AI in deze risicoklasse valt'] = $u['ai_risico']['redenen'];
        }
    }

    $redenen['Opbouw van het beveiligingsniveau'] = array_map(
        static fn (array $stap): string => $stap['label'] . ' → ' . $stap['gevolg'],
        $u['bbn_stappen']
    );

    foreach (['i' => 'integriteit', 'v' => 'vertrouwelijkheid'] as $letter => $naam) {
        $redenen['Waarom de eis aan ' . $naam . ' ' . cm_niveau_label($u['biv'][$letter]) . ' is']
            = $u['biv_redenen'][$letter] ?? [];
    }

    foreach ($redenen as $titel => $lijst) {
        if ($lijst !== []) {
            $b[] = ['h3', $titel];
            $b[] = ['lijst', $lijst];
        }
    }

    return $b;
}

/** Alle ingevulde antwoorden, per sectie een tabel. */
function qs_advies_antwoorden(array $a): array
{
    $b = [['h2', 'De ingevulde antwoorden']];

    foreach (cm_secties() as $sectie) {
        $rijen = [];

        foreach ($sectie['vragen'] as $vraag) {
            $waarde = $a[$vraag['key']] ?? '';

            if (!cm_is_invoer($vraag) || !cm_zichtbaar($vraag, $a)
                || (is_array($waarde) ? $waarde === [] : trim((string) $waarde) === '')) {
                continue;
            }

            $rijen[] = [$vraag['label'], explode("\n", qs_antwoord_tekst($vraag['key'], $a))];
        }

        if ($rijen !== []) {
            $b[] = ['h3', $sectie['titel']];
            $b[] = ['tabel', ['kop' => ['Vraag', 'Antwoord'], 'rijen' => $rijen, 'breedtes' => [7, 9.6]]];
        }
    }

    return $b;
}

/** Leesbaar antwoord voor in het advies: het label in plaats van de sleutel. */
function qs_antwoord_tekst(string $key, array $antwoorden): string
{
    $vraag = cm_alle_vragen()[$key] ?? null;
    $waarde = $antwoorden[$key] ?? '';

    if ($vraag === null) {
        return is_array($waarde) ? implode(', ', $waarde) : (string) $waarde;
    }

    if ($vraag['type'] === 'checkbox') {
        $labels = [];
        foreach ((array) $waarde as $gekozen) {
            $labels[] = $vraag['opties'][$gekozen]['label'] ?? $gekozen;
        }

        return $labels === [] ? 'Geen' : implode(', ', $labels);
    }

    if (in_array($vraag['type'], ['radio', 'select'], true)) {
        return (string) ($vraag['opties'][$waarde]['label'] ?? ($waarde === '' ? 'Niet ingevuld' : $waarde));
    }

    return trim((string) $waarde) === '' ? 'Niet ingevuld' : (string) $waarde;
}

/** Leesbaar label voor een cloudcategorie. */
function qs_cloud_label(?string $categorie): string
{
    return [
        'laag' => 'Laag',
        'midden' => 'Midden',
        'hoog' => 'Hoog',
        'zeer-hoog' => 'Zeer hoog',
    ][$categorie ?? ''] ?? 'niet van toepassing';
}
