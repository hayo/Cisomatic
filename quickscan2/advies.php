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
function q2_document(array $a, array $u): array
{
    return [
        ...q2_advies_kop($a, $u),
        ...q2_advies_uitkomst($a, $u),
        ...q2_advies_bio2($u),
        ...q2_advies_cbw($a, $u),
        ...q2_advies_cloud($a, $u),
        ...q2_advies_wegingen($u),
        ...q2_advies_archief($a, $u),
        ...q2_advies_kaders($u),
        ...q2_advies_onderbouwing($u),
        ...q2_advies_antwoorden($a),
        ['lijn', ''],
        ['alinea', 'Volgende stap: stuur dit advies samen met de onderliggende stukken naar de '
            . 'beoordelaar. Die maakt de risicoanalyse af, en de proceseigenaar stelt de uitkomst vast.'],
    ];
}

/** Een tabel met een label per rij en de waarde ernaast. */
function q2_termtabel(array $rijen): array
{
    return ['tabel', ['rijen' => $rijen, 'breedtes' => [5.2, 11.4], 'rijkop' => true]];
}

/** Titel, wie en wanneer, en in welke stap. */
function q2_advies_kop(array $a, array $u): array
{
    $beoordeeld = ($a['rol'] ?? 'behoeftesteller') === 'beoordelaar';

    $b = [
        ['h1', 'Quickscan BIO2: ' . ($a['projectnaam'] ?: 'naamloos project')],
        ['term', 'Ingevuld op', cm_datum_nl(new DateTimeImmutable('now'), true)],
        ['term', 'Ingevuld door', (string) ($a['aanvrager'] ?: 'onbekend')],
    ];

    foreach (['organisatieonderdeel' => 'Team of afdeling', 'email' => 'Contact'] as $key => $label) {
        if (trim((string) ($a[$key] ?? '')) !== '') {
            $b[] = ['term', $label, (string) $a[$key]];
        }
    }

    $b[] = ['term', 'Organisatie', q2_antwoord_tekst('organisatie_soort', $a)];
    $b[] = ['term', 'Rol', q2_antwoord_tekst('rol', $a)];

    if ($beoordeeld && trim((string) ($a['beoordelaar_naam'] ?? '')) !== '') {
        $b[] = ['term', 'Beoordeeld door', (string) $a['beoordelaar_naam']];
    }

    $b[] = ['alinea', 'Dit advies is automatisch samengesteld uit de antwoorden. De regels komen uit de BIO2 '
        . '(versie 1.3), de Cyberbeveiligingswet met de regeling voor de sector overheid, en het rijksbrede '
        . 'cloudbeleid van 2026.'];
    $b[] = ['citaat', $beoordeeld
        ? 'Stap 2. De wegingen hieronder zijn vastgesteld door de beoordelaar. De proceseigenaar stelt de '
            . 'uitkomst vast, na het advies van de CISO.'
        : 'Stap 1. De beschrijving komt van de behoeftesteller. De wegingen hieronder zijn voorlopig: de '
            . 'beoordelaar stelt ze in stap 2 definitief vast. Gebruik dit advies om te zien wat er '
            . 'waarschijnlijk nodig is, niet als eindoordeel.'];

    return $b;
}

/** De uitkomst in het kort, de aannames, wat blokkeert en welke analyses nodig zijn. */
function q2_advies_uitkomst(array $a, array $u): array
{
    $kort = [
        ['Cyberbeveiligingswet', $u['cbw']['waar']
            ? 'Geldt: de organisatie is een essentiële entiteit'
            : 'Geldt niet, de BIO2 wel'],
        ['Beveiligingsniveau', $u['niveau']],
        ['Te beschermen belangen', $u['tbb']['label']],
    ];

    $kort[] = ['Betrouwbaarheidseisen', sprintf(
        'Beschikbaarheid %s, integriteit %s, vertrouwelijkheid %s',
        cm_niveau_label($u['biv']['b']),
        cm_niveau_label($u['biv']['i']),
        cm_niveau_label($u['biv']['v'])
    )];
    $kort[] = ['Risiconiveau', ucfirst((string) $u['risiconiveau'])];
    $kort[] = ['Herstel', 'RTO ' . $u['rto'] . ', RPO ' . $u['rpo']];
    $kort[] = ['Cloudbeleid', $u['cloud'] !== null
        ? $u['cloud']['label']
        : 'Niet van toepassing, er wordt geen clouddienst gebruikt'];
    $kort[] = ['Dreigingsprofiel', ucfirst((string) $u['dreiging']['niveau'])];

    $kort[] = ['Aanvullende analyses', $u['analyses'] === []
        ? 'Geen'
        : implode(', ', array_column($u['analyses'], 'naam'))];

    $b = [['h2', 'De uitkomst in het kort'], q2_termtabel($kort)];

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
        $b[] = ['alinea', 'Er zijn geen aanvullende analyses nodig. De basis van de BIO2 volstaat.'];
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

/** Wat de BIO2 vraagt: het niveau en de maatregelen. */
function q2_advies_bio2(array $u): array
{
    return [
        ['h2', 'Wat de BIO2 hier vraagt'],
        ['alinea', 'De BIO2 kent geen vaste beveiligingsniveaus meer. Elk systeem krijgt de maatregelen uit '
            . 'ISO 27002 en de verplichte overheidsmaatregelen. Wat een risicoanalyse daarna nog aan risico '
            . 'laat zien, vraagt om extra maatregelen. Deze quickscan is het begin van die analyse.'],
        ['term', 'Beveiligingsniveau', $u['niveau']],
        ['h3', 'Maatregelen die we zelf nemen'],
        ['lijst', $u['maatregelen']],
    ];
}

/** De Cyberbeveiligingswet: te beschermen belangen, melden en registreren. */
function q2_advies_cbw(array $a, array $u): array
{
    $b = [
        ['h2', 'De Cyberbeveiligingswet'],
        ['alinea', $u['cbw']['reden']],
        ['term', 'Te beschermen belangen', $u['tbb']['label']],
        ['alinea', 'Zo rekent de tool dit niveau uit:'],
        ['lijst', $u['tbb']['redenen']],
    ];

    if (!$u['cbw']['waar']) {
        $b[] = ['alinea', 'De te beschermen belangen komen uit de regeling bij de wet. Ze helpen ook hier om '
            . 'te bepalen hoeveel zorg het systeem vraagt.'];

        return $b;
    }

    $b[] = ['h3', 'Een significant incident melden'];
    $b[] = ['alinea', 'Een significant incident meldt de organisatie bij het NCSC. Binnen 24 uur gaat er een '
        . 'eerste melding uit, binnen 72 uur een vervolgmelding, en binnen een maand een eindverslag. Een '
        . 'incident is significant als het de dienstverlening ernstig verstoort, of grote schade geeft aan '
        . 'mensen of organisaties.'];

    if ($u['meldplicht'] === []) {
        $b[] = ['alinea', 'De tool ziet bij dit systeem geen incident dat vanzelf de drempels uit de regeling '
            . 'haalt. Een incident kan toch significant zijn. Beoordeel dat dan per geval.'];
    } else {
        $b[] = ['alinea', 'Bij dit systeem liggen deze incidenten voor de hand. Ze halen de drempels uit de '
            . 'regeling, dus ze moeten gemeld worden:'];
        $b[] = ['lijst', $u['meldplicht']];
    }

    if (in_array('domein', (array) ($a['internet_onderdelen'] ?? []), true)) {
        $b[] = ['h3', 'Registreren'];
        $b[] = ['alinea', 'De organisatie staat met haar domeinnamen geregistreerd bij het NCSC. Er komt een '
            . 'domeinnaam bij, dus werk die registratie bij.'];
    }

    return $b;
}

/** Wat het cloudbeleid van 2026 over deze vorm van cloud zegt. */
function q2_advies_cloud(array $a, array $u): array
{
    if ($u['cloud'] === null) {
        return [];
    }

    $b = [
        ['h2', 'Cloudgebruik'],
        ['alinea', sprintf(
            'Er wordt %s gebruikgemaakt van clouddienstverlening.',
            ($a['cloud'] ?? '') === 'mogelijk' ? 'mogelijk' : 'daadwerkelijk'
        )],
        q2_termtabel([
            ['Soort cloud', q2_antwoord_tekst('cloud_type', $a)],
            ['Geleverd door', q2_antwoord_tekst('cloud_aanbieder', $a)],
            ['Locatie van de data', q2_antwoord_tekst('cloud_locatie', $a)],
            ['Oordeel', $u['cloud']['label']],
            ['Materieel cloudgebruik', ($u['materieel_cloudgebruik']['waar'] ? 'Ja. ' : 'Nee. ')
                . $u['materieel_cloudgebruik']['reden']],
        ]),
    ];

    if ($u['cloud']['redenen'] !== []) {
        $b[] = ['h3', 'Waarom'];
        $b[] = ['lijst', $u['cloud']['redenen']];
    }

    $b[] = ['h3', 'Voorwaarden uit het cloudbeleid'];
    $b[] = ['lijst', $u['cloud']['regels']];

    return $b;
}

/** Per weging het niveau, wat dat inhoudt, waarom, en wie het bepaalde. */
function q2_advies_wegingen(array $u): array
{
    if (empty($u['wegingen'])) {
        return [];
    }

    $b = [
        ['h2', 'De wegingen en waarom'],
        ['alinea', 'Per weging staat hier het niveau, wat dat niveau betekent, waarom het zo is ingeschaald en '
            . 'wie dat heeft bepaald. Zo is achteraf na te gaan hoe deze beoordeling tot stand is gekomen, en '
            . 'of hij klopt.'],
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
function q2_advies_archief(array $a, array $u): array
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
        ['term', 'Opgegeven bewaartermijn', q2_antwoord_tekst('bewaartermijn', $a)
            . ($toelichting === '' ? '' : ' (' . $toelichting . ')')],
    ];

    foreach ($u['archief'] as $nummer => $stap) {
        $b[] = ['h3', sprintf('%d. %s', $nummer + 1, $stap['titel'])];
        $b[] = ['alinea', $stap['tekst']];
    }

    return $b;
}

/** Wetten en regels, eisen aan de leverancier en wie meekijkt. */
function q2_advies_kaders(array $u): array
{
    $b = [['h2', 'Wetten en regels'], ['lijst', $u['wetgeving']]];

    if ($u['kaders'] !== []) {
        $b[] = ['h3', 'Toe te passen kaders en documenten'];
        $b[] = ['lijst', $u['kaders']];
    }

    if ($u['eisen_leverancier'] !== []) {
        $b[] = ['h2', 'Op te nemen eisen aan de leverancier'];
        $b[] = ['lijst', $u['eisen_leverancier']];
    }

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
    $b[] = ['alinea', 'Volgens de BIO2 is de lijn eigenaar van het risico. De CISO adviseert, en de '
        . 'proceseigenaar accepteert wat er aan risico overblijft.'];

    return $b;
}

/** Het gevolgde pad door de beslisboom, en de redenen achter de afleidingen. */
function q2_advies_onderbouwing(array $u): array
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

    $redenen = [
        'Waarom het dreigingsprofiel ' . $u['dreiging']['niveau'] . ' is' => $u['dreiging']['redenen'],
        'Waarom de verwerking wel of niet grootschalig is' => $u['grootschalig']['redenen'] ?? [],
    ];

    $redenen['Opbouw van het beveiligingsniveau'] = array_map(
        static fn (array $stap): string => $stap['label'] . ': ' . $stap['gevolg'] . '.',
        $u['niveau_stappen']
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
function q2_advies_antwoorden(array $a): array
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

            $rijen[] = [$vraag['label'], explode("\n", q2_antwoord_tekst($vraag['key'], $a))];
        }

        if ($rijen !== []) {
            $b[] = ['h3', $sectie['titel']];
            $b[] = ['tabel', ['kop' => ['Vraag', 'Antwoord'], 'rijen' => $rijen, 'breedtes' => [7, 9.6]]];
        }
    }

    return $b;
}

/** Leesbaar antwoord voor in het advies: het label in plaats van de sleutel. */
function q2_antwoord_tekst(string $key, array $antwoorden): string
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
