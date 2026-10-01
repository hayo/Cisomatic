<?php

/**
 * De beslisboom van de quickscan voor de BIO2 en de Cyberbeveiligingswet.
 *
 * Zet de antwoorden om in een uitkomst: of de Cyberbeveiligingswet geldt, het
 * niveau van de te beschermen belangen, of de basis van de BIO2 volstaat, wat
 * het cloudbeleid van 2026 toelaat, welke analyses nodig zijn, welke
 * overheidsmaatregelen uit de BIO2 hier gelden, en wie meekijkt.
 *
 * Bronnen: BIO2 versie 1.3 (9 januari 2026), de Cyberbeveiligingsregeling sector
 * overheid (Stcrt. 2026, 27679) en de Herziening rijksbreed cloudbeleid 2026
 * (3 juli 2026).
 */

declare(strict_types=1);

/**
 * @param array<string,mixed> $a antwoorden
 * @return array<string,mixed>
 */
function q2_evalueer(array $a): array
{
    $u = [
        'biv'              => [],
        'biv_afgeleid'     => [],
        'biv_ondergrens'   => [],
        'biv_redenen'      => [],
        'biv_verhoogd'     => [],
        'wegingen'         => [],
        'cbw'              => ['waar' => true, 'reden' => ''],
        'tbb'              => ['niveau' => 0, 'cruciaal' => false, 'label' => 'Geen TBB', 'redenen' => []],
        'meldplicht'       => [],
        'materieel_cloudgebruik' => ['waar' => false, 'reden' => ''],
        'cloud'            => null,
        'abro'             => ['waar' => false, 'reden' => ''],
        'dreiging'         => ['niveau' => 'normaal', 'redenen' => []],
        'grootschalig'     => ['uitkomst' => 'nee', 'redenen' => []],
        'aannames'         => [],
        'persoonsgegevens' => [],
        'archief'          => [],
        'niveau'           => '',
        'niveau_stappen'   => [],
        'rto'              => '',
        'rpo'              => '',
        'analyses'         => [],
        'blokkerend'       => [],
        'aandachtspunten'  => [],
        'wetgeving'        => [],
        'kaders'           => [],
        'eisen_leverancier' => [],
        'maatregelen'      => [],
        'collegiaal_advies_rollen' => [],
        'cio_reden'        => '',
        'vaststelling'     => [],
        'risiconiveau'     => 'laag',
        'beslisboom'       => [],
    ];

    // 'Weet ik niet' wordt hier vervangen door de aanname die bij de vraag hoort.
    $metAannames = q2_met_aannames($a);
    $a = $metAannames['antwoorden'];
    $u['aannames'] = $metAannames['aannames'];
    $u['cbw'] = q2_cbw($a);

    $pg = q2_afgeleide_persoonsgegevens($a);
    $a['pg_bijzonder_soorten'] = $pg['bijzonder_soorten'];
    $a['pg_bsn'] = $pg['bsn'];
    $u['persoonsgegevens'] = $pg;
    $heeftPg = $pg['heeft'];

    $u['dreiging'] = q2_afgeleid_dreigingsprofiel($a);
    $u['grootschalig'] = q2_afgeleide_grootschaligheid($a);
    $u['abro'] = q2_abro_van_toepassing($a);

    $dreigingHoog = $u['dreiging']['niveau'] === 'hoog';
    $heeftCloud = in_array($a['cloud'] ?? 'nee', ['ja', 'mogelijk'], true);
    $strategischVitaal = in_array($a['proces_classificatie'] ?? '', ['strategisch', 'kritisch-strategisch'], true)
        && ($a['systeem_classificatie'] ?? '') === 'vitaal';

    // ------------------------------------------------------------------
    // 1. Betrouwbaarheidseisen
    // ------------------------------------------------------------------
    $eisen = q2_effectieve_biv($a);
    $u['biv'] = ['b' => $eisen['b'], 'i' => $eisen['i'], 'v' => $eisen['v']];
    $u['biv_afgeleid'] = $eisen['afgeleid'];
    $u['biv_ondergrens'] = $eisen['ondergrens'];
    $u['biv_redenen'] = $eisen['redenen'];

    foreach ($eisen['bijgesteld'] as $letter => $oorspronkelijk) {
        $naam = $letter === 'i' ? 'integriteit' : 'vertrouwelijkheid';
        $motivatie = trim((string) ($a[$naam . '_motivatie'] ?? ''));
        $omhoog = cm_niveau_rang($u['biv'][$letter]) > cm_niveau_rang($oorspronkelijk);

        $u['biv_verhoogd'][$letter] = $motivatie;
        $u['aandachtspunten'][] = sprintf(
            'De eis aan %s is handmatig %s van %s naar %s door de %s.%s',
            $naam,
            $omhoog ? 'verhoogd' : 'verlaagd',
            cm_niveau_label($oorspronkelijk),
            cm_niveau_label($u['biv'][$letter]),
            ($a['rol'] ?? '') === 'beoordelaar' ? 'beoordelaar' : 'behoeftesteller',
            $motivatie === '' ? '' : ' Motivatie: ' . $motivatie
        );
    }

    $B = $u['biv']['b'];
    $zeerHoog = in_array('zh', $u['biv'], true);

    // ------------------------------------------------------------------
    // 2. Te beschermen belangen, en welke incidenten gemeld moeten worden
    // ------------------------------------------------------------------
    $u['tbb'] = q2_tbb($a, $u['biv']);
    $u['meldplicht'] = $u['cbw']['waar'] ? q2_meldplicht($a, $u['biv']) : [];

    // ------------------------------------------------------------------
    // 3. Herstel
    // ------------------------------------------------------------------
    $u['rto'] = [
        'zl' => 'groter dan een week (best effort)',
        'l'  => '4 dagen tot een week',
        'm'  => '1 tot 3 dagen',
        'h'  => '4 tot 8 uur',
        'zh' => '0 tot 4 uur',
    ][$B] ?? 'nog te bepalen';

    $u['rpo'] = [
        'geen'   => '0 (er mag geen enkele transactie verloren gaan)',
        '1-uur'  => 'maximaal 1 uur',
        '4-uur'  => 'maximaal 4 uur',
        '24-uur' => 'maximaal 24 uur',
        '1-week' => 'maximaal een week',
        'nvt'    => 'niet van toepassing',
    ][(string) ($a['rpo'] ?? '')] ?? 'nog te bepalen';

    // ------------------------------------------------------------------
    // 4. Volstaat de basis van de BIO2?
    // ------------------------------------------------------------------
    $u['niveau_stappen'][] = [
        'label' => 'Elk netwerk en informatiesysteem',
        'gevolg' => 'de beheersmaatregelen uit ISO 27002 en de verplichte overheidsmaatregelen uit de BIO2',
    ];

    $aanvullend = [];
    if ($u['tbb']['cruciaal']) {
        $aanvullend[] = [q2_tbb_label($u['tbb']['niveau']) . ', een cruciaal systeem', 'Het systeem heeft ' . q2_tbb_label($u['tbb']['niveau']) . ' en is daarmee cruciaal.'];
    }
    if ($zeerHoog) {
        $aanvullend[] = ['Een betrouwbaarheidseis is Zeer hoog', 'Ten minste één van de betrouwbaarheidseisen is Zeer hoog.'];
    }
    if ($dreigingHoog) {
        $aanvullend[] = ['Het dreigingsprofiel is hoog', 'Er is sprake van een hoog dreigingsprofiel.'];
    }
    if ($strategischVitaal) {
        $aanvullend[] = ['Een (kritisch) strategisch proces op een vitaal systeem', 'Een (kritisch) strategisch proces in combinatie met een vitaal systeem.'];
    }
    if (($a['leverancier_overstap'] ?? '') === 'vast') {
        $aanvullend[] = ['De organisatie zit vast aan de leverancier', 'De afhankelijkheid van de leverancier is groot. De BIO2 vraagt vóór het contract te bepalen of die beheersbaar is.'];
    }

    foreach ($aanvullend as [$label, $reden]) {
        $u['niveau_stappen'][] = ['label' => $label, 'gevolg' => 'aanvullende maatregelen uit een volledige risicoanalyse'];
        $u = q2_voeg_analyse_toe($u, 'Volledige risicoanalyse', $reden, 'CISO');
    }

    $u['niveau'] = $aanvullend === []
        ? 'Basis van de BIO2'
        : 'Basis van de BIO2, aangevuld met maatregelen uit een volledige risicoanalyse';

    // ------------------------------------------------------------------
    // 5. Cloud, analyses, privacy en archief
    // ------------------------------------------------------------------
    $u['materieel_cloudgebruik'] = $heeftCloud
        ? q2_materieel_cloudgebruik($a, $u['grootschalig']['uitkomst'])
        : ['waar' => false, 'reden' => 'Er is geen sprake van clouddienstverlening.'];

    if ($heeftCloud) {
        $u = q2_cloudbeleid($u, $a, $pg);
    }

    $u = q2_analyses($u, $a, $heeftPg, $dreigingHoog);
    $u = q2_privacy_checks($u, $a, $heeftPg);
    $u = q2_archiefwet($u, $a, $heeftPg, $heeftCloud);

    // ------------------------------------------------------------------
    // 6. Wetten en regels, maatregelen en eisen
    // ------------------------------------------------------------------
    $u = q2_wetgeving($u, $a, $heeftPg, $heeftCloud);
    $u = q2_bio2_maatregelen($u, $a);
    $u = q2_leveranciers($u, $a, $heeftPg, $heeftCloud);
    $u = q2_wie_kijkt_mee($u, $a, $heeftPg, $heeftCloud, $dreigingHoog);

    $u['risiconiveau'] = q2_risiconiveau($u['biv'], $dreigingHoog, $u['tbb']['niveau']);
    $u['wegingen'] = q2_wegingen($a, $u);
    $u['beslisboom'] = q2_beslisboom_pad($a, $u, [
        'heeftPg' => $heeftPg,
        'heeftCloud' => $heeftCloud,
        'dreigingHoog' => $dreigingHoog,
        'strategischVitaal' => $strategischVitaal,
    ]);

    return $u;
}

/** Een analyse erbij, tenzij die er al staat. */
function q2_voeg_analyse_toe(array $u, string $naam, string $reden, string $beoordelaar): array
{
    foreach ($u['analyses'] as $bestaand) {
        if ($bestaand['naam'] === $naam) {
            return $u;
        }
    }

    $u['analyses'][] = ['naam' => $naam, 'reden' => $reden, 'beoordelaar' => $beoordelaar];

    return $u;
}

function q2_heeft_analyse(array $u, string $naam): bool
{
    return in_array($naam, array_column($u['analyses'], 'naam'), true);
}

/**
 * Het rijksbrede cloudbeleid van 2026, toegepast op dit systeem. Het oordeel is
 * het zwaarste dat uit de regels volgt: niet toegestaan, afgeraden, of
 * toegestaan onder voorwaarden.
 *
 * @param array<string,mixed> $pg
 */
function q2_cloudbeleid(array $u, array $a, array $pg): array
{
    $type = (string) ($a['cloud_type'] ?? '');
    $publiek = in_array($type, ['publiek', 'hybride'], true);
    $extern = ($a['cloud_aanbieder'] ?? 'extern') !== 'overheid';
    $toepassing = (array) ($a['cloud_toepassing'] ?? []);
    $rubricering = (string) ($a['rubricering'] ?? '');
    $staatsgeheim = str_starts_with($rubricering, 'stg-');
    $vertrouwelijk = $staatsgeheim || $rubricering === 'dep-vertrouwelijk' || in_array($u['biv']['v'], ['h', 'zh'], true);
    $primair = in_array($a['proces_classificatie'] ?? '', ['strategisch', 'kritisch-strategisch'], true);

    $niet = [];
    $afgeraden = [];
    $regels = [];

    if ($publiek && ($staatsgeheim || $u['tbb']['cruciaal'])) {
        $niet[] = 'Publieke cloud is niet toegestaan voor staatsgeheime informatie, en niet voor een systeem met '
            . 'TBB 1, 2 of 3. ' . ($staatsgeheim
                ? 'Hier gaat het om staatsgeheime informatie.'
                : 'Dit systeem heeft ' . q2_tbb_label($u['tbb']['niveau']) . '.');
    }

    if ($extern && ($a['cloud_locatie'] ?? '') !== 'eer') {
        $niet[] = 'Alle informatie moet binnen de EER of Zwitserland worden opgeslagen en verwerkt. Dat is hier '
            . 'niet zo, of het staat niet vast.';
    }

    if ($publiek && array_intersect($toepassing, ['email', 'documenten']) !== []) {
        $niet[] = 'Mail en documenten mogen niet in de publieke cloud, tenzij aan drie voorwaarden is voldaan. '
            . 'Het is onafhankelijk vastgesteld dat de continuïteit anders in gevaar komt. Er is een getoetste '
            . 'risicoanalyse en een exitplan. En de minister heeft het besluit genomen, samen met de '
            . 'bewindspersoon voor digitalisering. Voor een klein of tijdelijk doel kan een uitzondering gelden.';
    }

    if ($extern && ($rubricering !== 'openbaar' || $pg['heeft']) && ($a['cloud_sleutels'] ?? '') === 'geen') {
        $niet[] = 'De data wordt niet versleuteld. Het cloudbeleid vraagt versleuteling bij opslag en '
            . 'verzending, voor alle data die niet openbaar is.';
    }

    if ($extern && $primair && ($a['cloud_jurisdictie'] ?? '') === 'ja' && $u['cbw']['waar']) {
        $afgeraden[] = 'De leverancier valt (deels) onder het recht van een land buiten de EU of de EER. Voor '
            . 'een essentiële entiteit onder de Cyberbeveiligingswet is dat afgeraden voor het primaire proces.';
    }

    if ($publiek && $pg['bijzonder_soorten'] !== []) {
        $afgeraden[] = 'Er zitten bijzondere persoonsgegevens in. Die horen bij voorkeur niet in de publieke '
            . 'cloud. Moet het toch, dan zijn naast een DPIA ook privacyverhogende technieken nodig.';
    }

    if ($publiek && in_array('basisregistratie', (array) ($a['omgeving'] ?? []), true)) {
        $afgeraden[] = 'Het gaat om een basisregistratie. Die komt niet (alleen) in een publieke cloud, en de '
            . 'brondata worden daar nooit beheerd. Publieke cloud mag wel voor extra capaciteit.';
    }

    // Voorwaarden die altijd gelden bij cloud van een externe leverancier.
    $regels[] = 'Leveranciers of diensten uit landen met een actief cyberprogramma tegen Nederlandse belangen '
        . '(' . implode(', ', q2_uitgesloten_landen()) . ') zijn uitgesloten. Andere landen weeg je mee in de '
        . 'risicoanalyse.';

    if ($rubricering !== 'openbaar' || $pg['heeft']) {
        $regels[] = 'De data wordt versleuteld bij opslag en verzending.';
    }

    if ($vertrouwelijk && ($a['cloud_sleutels'] ?? '') === 'leverancier') {
        $regels[] = 'Het gaat om vertrouwelijke gegevens. Beheer de sleutels daarom bij voorkeur zelf, of laat '
            . 'dat doen door een andere partij die daarvoor gecertificeerd is, niet door de cloudleverancier.';
    }

    if ($u['materieel_cloudgebruik']['waar']) {
        $regels[] = 'Maak een integrale risicoanalyse volgens het implementatiekader risicobeheersing '
            . 'cloudgebruik. Weeg daarin ook de toegang van de leverancier tot de data, buitenlandse '
            . 'jurisdictie, inmenging door een buitenlandse overheid en misbruik van afhankelijkheid.';
        $regels[] = 'Maak een exitplan en toets het zelf. Het beschrijft een geplande overstap, en wat je doet '
            . 'als de dienst onverwacht wegvalt. Beoordeel het elk jaar opnieuw.';
        $regels[] = 'Meld het cloudgebruik vóór de implementatie bij CISO Rijk, met de risicoanalyse en het '
            . 'exitplan. Registreer het ook als materieel cloudgebruik.';
        $regels[] = 'Het resterende risico wordt vastgelegd en formeel geaccepteerd door de verantwoordelijke '
            . 'bestuurder. Maatregelen die de leverancier moet nemen, staan in het contract.';
    }

    if (!$extern) {
        $regels[] = 'De cloud komt van een overheidsorganisatie. Het cloudbeleid geldt dan voor een deel; het '
            . 'implementatiekader zegt welk deel.';
    }

    if ($u['dreiging']['niveau'] === 'hoog') {
        $regels[] = 'Het dreigingsprofiel is hoog. Vraag vooraf advies over dreiging en beveiliging aan de AIVD '
            . 'of de MIVD.';
    }

    if ($niet !== [] || $afgeraden !== []) {
        $regels[] = 'Wijkt de organisatie af van het cloudbeleid, meld dat dan vooraf bij CIO Rijk, in overleg '
            . 'met het kerndepartement.';
    }

    foreach ($niet as $punt) {
        $u['blokkerend'][] = 'Cloudbeleid: ' . lcfirst($punt);
    }
    foreach ($afgeraden as $punt) {
        $u['aandachtspunten'][] = 'Cloudbeleid: ' . lcfirst($punt);
    }

    if (($a['organisatie_soort'] ?? '') === 'uitgezonderd') {
        $u['aandachtspunten'][] = 'Defensie is uitgezonderd van het rijksbrede cloudbeleid, maar volgt het waar '
            . 'dat kan. Voor andere organisaties blijft het gelden.';
    }

    $u['cloud'] = [
        'oordeel' => $niet !== [] ? 'niet' : ($afgeraden !== [] ? 'afgeraden' : 'voorwaarden'),
        'label' => $niet !== [] ? 'Niet toegestaan in deze vorm'
            : ($afgeraden !== [] ? 'Afgeraden' : 'Toegestaan onder voorwaarden'),
        'redenen' => [...$niet, ...$afgeraden],
        'regels' => $regels,
    ];

    $land = trim((string) ($a['leverancier_land'] ?? ''));
    if ($land !== '' && q2_land_uitgesloten($land)) {
        $u['blokkerend'][] = sprintf(
            'De leverancier komt uit %s. Leveranciers of diensten uit landen met een actief cyberprogramma dat '
            . 'gericht is tegen Nederlandse belangen (%s) worden altijd uitgesloten.',
            $land,
            implode(', ', q2_uitgesloten_landen())
        );
    }

    return $u;
}

function q2_land_uitgesloten(string $land): bool
{
    $genormaliseerd = mb_strtolower(trim($land), 'UTF-8');

    foreach (['rusland', 'russia', 'china', 'chinese', 'iran', 'noord-korea', 'noord korea', 'north korea'] as $treffer) {
        if (str_contains($genormaliseerd, $treffer)) {
            return true;
        }
    }

    return false;
}

/** De analyses die bovenop de volledige risicoanalyse nodig zijn, en wie ze beoordeelt. */
function q2_analyses(array $u, array $a, bool $heeftPg, bool $dreigingHoog): array
{
    $labels = [
        'productgericht' => 'er sprake is van een productgerichte uitvraag',
        'saas' => 'het om software als dienst (SaaS) gaat',
        'opensource' => 'er open source wordt gebruikt',
    ];
    $redenen = array_values(array_intersect_key($labels, array_flip((array) ($a['afwijkend'] ?? []))));

    if ($redenen !== []) {
        $u = q2_voeg_analyse_toe(
            $u,
            'Motivatie en marktanalyse',
            'Omdat ' . cm_opsomming($redenen) . '. Deze moet jaarlijks herhaald worden.',
            'CTO of CISO'
        );
    }

    if ($u['materieel_cloudgebruik']['waar']) {
        $u = q2_voeg_analyse_toe(
            $u,
            'Risicoanalyse en exitplan cloud',
            $u['materieel_cloudgebruik']['reden'] . ' Melden bij CISO Rijk vóór de implementatie.',
            'CISO'
        );
    }

    if ($dreigingHoog) {
        $u = q2_voeg_analyse_toe(
            $u,
            'Dreigingsadvies AIVD of MIVD',
            'Het dreigingsprofiel is hoog. Vraag vooraf advies over de dreiging en de beveiliging.',
            'CISO'
        );
    }

    if ($heeftPg) {
        $zwaar = array_intersect((array) ($a['pg_bijzonder_soorten'] ?? []), q2_zware_bijzondere_gegevens());
        $grootschalig = $u['grootschalig'];

        if ($zwaar !== []) {
            $soorten = q2_informatiesoorten();
            $u = q2_voeg_analyse_toe(
                $u,
                'DPIA',
                'Er worden gegevens verwerkt over ' . cm_opsomming(array_map(
                    static fn (string $s): string => mb_strtolower((string) ($soorten[$s]['label'] ?? $s), 'UTF-8'),
                    array_values($zwaar)
                )) . '. Die categorieën gelden altijd als een gevoelige verwerking, ongeacht de omvang.',
                'CPO'
            );
        } elseif ($grootschalig['uitkomst'] === 'ja') {
            $u = q2_voeg_analyse_toe($u, 'DPIA', $grootschalig['redenen'][0] ?? 'Grootschalige verwerking.', 'CPO');
        } elseif ($grootschalig['uitkomst'] === 'twijfel' || $u['persoonsgegevens']['bijzonder'] !== 'nee'
            || $u['materieel_cloudgebruik']['waar']) {
            $u = q2_voeg_analyse_toe(
                $u,
                'DPIA pretoets',
                $u['materieel_cloudgebruik']['waar']
                    ? 'Er gaan persoonsgegevens naar de cloud, bij materieel cloudgebruik. Het cloudbeleid vraagt '
                        . 'dan een pretoets en een DPIA.'
                    : ($grootschalig['uitkomst'] === 'twijfel'
                        ? ($grootschalig['redenen'][0] ?? '')
                        : 'Er worden bijzondere persoonsgegevens verwerkt, maar niet grootschalig. De CPO bepaalt of '
                            . 'een volledige DPIA nodig is.'),
                'CPO'
            );
        }

        $doorgifte = [];
        if (in_array($a['cloud_locatie'] ?? '', ['modelcontract', 'buiten-eer'], true)
            && in_array($a['cloud'] ?? 'nee', ['ja', 'mogelijk'], true)) {
            $doorgifte[] = 'de clouddienst data buiten de EER verwerkt';
        }
        if (($a['pg_subverwerkers'] ?? '') === 'ja'
            && in_array($a['pg_subverwerkers_locatie'] ?? '', ['modelcontract', 'buiten-eer'], true)) {
            $doorgifte[] = 'de leverancier subverwerkers buiten de EER inschakelt';
        }

        if ($doorgifte !== []) {
            $u = q2_voeg_analyse_toe(
                $u,
                'DTIA',
                'Er is sprake van internationale doorgifte naar een land zonder adequaatheidsbesluit, omdat '
                . cm_opsomming($doorgifte) . '.',
                'CPO'
            );
        }
    }

    if (($a['pg_bsn'] ?? '') === 'ja') {
        $u['aandachtspunten'][] = 'Er wordt een burgerservicenummer verwerkt. Leg de wettelijke grondslag voor het '
            . 'gebruik van het BSN expliciet vast.';
    }

    if (($a['ai_nieuw_algoritme'] ?? '') === 'ja') {
        $u = q2_voeg_analyse_toe($u, 'IAMA', 'Het gaat om een nieuw algoritme of AI systeem.', 'CDO');
    }

    if (q2_met_ai($a)) {
        $u = q2_voeg_analyse_toe(
            $u,
            'AI scan',
            'Er komt AI in het systeem. De AI scan zoekt uit of de AI verordening geldt, in welke risicogroep '
                . 'de AI valt, en wat jullie dan moeten doen.',
            'CDO'
        );
    }

    if ($u['abro']['waar']) {
        $u = q2_voeg_analyse_toe($u, 'ABRO quickscan', $u['abro']['reden'], 'CISO');
    }

    return $u;
}

/** De wetten en regels die hier echt gelden, en de kaders die erbij horen. */
function q2_wetgeving(array $u, array $a, bool $heeftPg, bool $heeftCloud): array
{
    $rubricering = (string) ($a['rubricering'] ?? '');
    $omgeving = (array) ($a['omgeving'] ?? []);

    if ($u['cbw']['waar']) {
        $u['wetgeving'][] = 'Cyberbeveiligingswet: de organisatie is een essentiële entiteit, met een zorgplicht, '
            . 'een meldplicht en een registratieplicht';
        $u['wetgeving'][] = 'Cyberbeveiligingsregeling sector overheid: de BIO2 als invulling van de zorgplicht, '
            . 'de te beschermen belangen en de drempels voor de meldplicht';
        $u['wetgeving'][] = 'BIO2, versie 1.3: ISO 27001, ISO 27002 en de verplichte overheidsmaatregelen. '
            . 'Wettelijk verplicht voor netwerken en informatiesystemen, en een verplichtende afspraak voor de rest';
    } else {
        $u['wetgeving'][] = 'BIO2, versie 1.3: ISO 27001, ISO 27002 en de verplichte overheidsmaatregelen, als '
            . 'verplichtende afspraak binnen de overheid. De Cyberbeveiligingswet geldt niet';
    }

    if ($rubricering === 'dep-vertrouwelijk' || str_starts_with($rubricering, 'stg-')) {
        $u['wetgeving'][] = 'VIR-BI 2025: Voorschrift Informatiebeveiliging Rijksdienst Bijzondere Informatie, '
            . 'omdat er gerubriceerde informatie wordt verwerkt';
    }

    if ($heeftPg) {
        $u['wetgeving'][] = '(U)AVG: Algemene verordening gegevensbescherming en de Uitvoeringswet, omdat er '
            . 'persoonsgegevens worden verwerkt';
    }

    $u['wetgeving'][] = 'Archiefwet 2026: wat hier wordt opgemaakt of ontvangen is informatie waarvoor een '
        . 'bewaartermijn en een overbrengingsplicht gelden';
    $u['wetgeving'][] = 'Wet open overheid: de informatie kan opgevraagd worden en moet dus vindbaar en '
        . 'leverbaar zijn';

    if ($heeftCloud) {
        $u['wetgeving'][] = 'Herziening rijksbreed cloudbeleid 2026: grenzen aan cloud van een externe leverancier';
    }

    if (q2_met_ai($a)) {
        $u['wetgeving'][] = 'AI verordening: de AI scan bepaalt de risicogroep en de plichten';
    }

    if (($a['gebruikers'] ?? '') === 'burgers') {
        $u['wetgeving'][] = 'Tijdelijk besluit digitale toegankelijkheid overheid: het systeem wordt door burgers '
            . 'gebruikt en moet voldoen aan de toegankelijkheidseisen (WCAG)';
    }

    if (in_array('extern', (array) ($a['inloggen'] ?? []), true) && ($a['gebruikers'] ?? '') === 'burgers') {
        $u['wetgeving'][] = 'Wet digitale overheid: burgers en bedrijven loggen in met een toegelaten inlogmiddel, '
            . 'zoals DigiD of eHerkenning';
    }

    if (in_array('zorg', $omgeving, true)) {
        $u['wetgeving'][] = 'NEN 7510: informatiebeveiliging in de zorg, voor de zorginformatie';
    }

    if (in_array('ot', $omgeving, true)) {
        $u['wetgeving'][] = 'CSIR en IEC 62443: beveiliging van operationele technologie';
    }

    $extra = trim((string) ($a['aanvullende_wetgeving'] ?? ''));
    if ($extra !== '') {
        $u['wetgeving'][] = 'Aanvullend opgegeven: ' . $extra;
    }

    $u['kaders'][] = 'Verklaring van toepasselijkheid: neem de maatregelen voor dit systeem op, en de '
        . 'uitzonderingen in de bijlage.';

    if ($u['tbb']['cruciaal']) {
        $u['kaders'][] = 'Overzicht van cruciale netwerken en informatiesystemen van de organisatie.';
    }

    if (q2_via_internet($a)) {
        $u['kaders'][] = 'De lijst ‘pas toe of leg uit’ van Forum Standaardisatie, gemeten met internet.nl.';
        $u['kaders'][] = 'De leidraad Coordinated Vulnerability Disclosure van het NCSC.';
    }

    if ($u['materieel_cloudgebruik']['waar']) {
        $u['kaders'][] = 'Implementatiekader risicobeheersing cloudgebruik.';
    }

    if (q2_met_ai($a)) {
        $u['kaders'][] = 'Inschrijving in het Algoritmeregister.';
        $u['kaders'][] = 'Controle op de eisen aan transparantie.';
        $u['kaders'][] = 'Toolbox Ethisch Verantwoorde Innovatie.';
    }

    if ($u['abro']['waar']) {
        $u['kaders'][] = 'ABRO: quickscan nationale veiligheid bij inkoop en aanbesteden.';
    }

    if ($heeftPg) {
        $u['kaders'][] = 'Verwerkingsregister bijwerken met deze verwerking.';
    }

    return $u;
}

/**
 * De overheidsmaatregelen uit de BIO2 die voor dit systeem gelden, gekozen op
 * de antwoorden. Het nummer in de BIO2 staat erachter, zodat de CISO ze in de
 * verklaring van toepasselijkheid terugvindt.
 */
function q2_bio2_maatregelen(array $u, array $a): array
{
    $m = [];
    $inloggen = (array) ($a['inloggen'] ?? []);
    $onderdelen = (array) ($a['internet_onderdelen'] ?? []);
    $rubricering = (string) ($a['rubricering'] ?? '');
    $B = $u['biv']['b'];

    $m[] = 'Iedereen die met het systeem werkt, volgt binnen drie maanden na de start aantoonbaar een training '
        . 'over informatiebeveiliging (BIO2 6.03.03).';
    $m[] = sprintf(
        'De Recovery Time Objective is %s en de Recovery Point Objective is %s. Leg dit vast in de afspraken met '
        . 'de systeemeigenaar (BIO2 8.13.02).',
        $u['rto'],
        $u['rpo']
    );
    $m[] = 'Maak reservekopieën die beschermd zijn tegen ransomware, bewaar ze op een andere locatie, en test het '
        . 'herstel elk jaar (BIO2 8.13).';

    if (in_array($B, ['m', 'h', 'zh'], true) || ($a['bedrijfskritisch'] ?? '') === 'ja') {
        $m[] = 'Test het continuïteitsplan voor dit systeem elk jaar (BIO2 5.29.01).';
    }

    $m[] = 'Een kwetsbaarheid met een hoge kans op misbruik en een hoge schade wordt binnen een week verholpen of '
        . 'afgeschermd (BIO2 8.08.01).';

    // ---- toegang ------------------------------------------------------------
    if ($inloggen !== []) {
        if (($a['mfa'] ?? '') === 'ja') {
            $m[] = 'Houd multifactorauthenticatie verplicht voor alle accounts (BIO2 5.17.01).';
        } else {
            $m[] = 'Zet multifactorauthenticatie aan voor de werkomgeving, voor accounts die via internet te '
                . 'bereiken zijn en voor beheeraccounts. Lukt dat niet, neem dan andere maatregelen met de CISO, '
                . 'en laat de proceseigenaar die goedkeuren (BIO2 5.17.01).';

            if (q2_via_internet($a)) {
                $u['aandachtspunten'][] = 'Het systeem is via internet te bereiken, maar niet iedereen logt in '
                    . 'met meer dan één factor. Dat is een van de meest misbruikte zwaktes.';
            }
        }
    }

    if (in_array('beheerders', $inloggen, true)) {
        $m[] = 'Beoordeel de rechten van beheerders elk kwartaal, en alle andere rechten elk jaar. Houd in de '
            . 'gaten wie accounts met extra rechten maakt of wijzigt (BIO2 5.18, 8.02.01).';
    }

    if (($a['logging'] ?? '') !== 'volledig') {
        $m[] = 'Laat het systeem per gebeurtenis vastleggen wat er gebeurde, waarop, met welk resultaat, vanaf '
            . 'waar, door wie en wanneer (BIO2 8.15.01).';
    }

    if (($a['monitoring'] ?? '') !== 'ja') {
        $m[] = 'Sluit het systeem aan op de detectie en respons van de organisatie, bijvoorbeeld een SOC met een '
            . 'SIEM (BIO2 8.16.02).';

        if ($u['tbb']['cruciaal']) {
            $u['aandachtspunten'][] = 'Dit is een cruciaal systeem, maar aanvallen worden nog niet opgemerkt. '
                . 'Regel de bewaking voordat het systeem in productie gaat.';
        }
    }

    // ---- incidenten en de meldplicht -----------------------------------------
    if ($u['cbw']['waar']) {
        $m[] = 'Neem het systeem op in de incidentprocedure. Een significant incident meldt de organisatie via '
            . 'het NCSC: binnen 24 uur een eerste melding, binnen 72 uur een vervolgmelding, en binnen een maand '
            . 'een eindverslag (BIO2 5.24.05).';
    } else {
        $m[] = 'Neem het systeem op in de incidentprocedure van de organisatie (BIO2 5.24).';
    }

    $m[] = 'Bewaar alles over een beveiligingsincident, zoals logbestanden, ten minste drie jaar (BIO2 5.28.01).';

    if ($u['tbb']['cruciaal']) {
        $m[] = 'Zet het systeem op het overzicht van cruciale netwerken en informatiesystemen van de organisatie.';
    }

    // ---- internet -------------------------------------------------------------
    if (q2_via_internet($a)) {
        $m[] = 'Laat het systeem voldoen aan de verplichte internetstandaarden van Forum Standaardisatie, en meet '
            . 'dat met internet.nl (BIO2 5.14.01).';
        $m[] = 'Voer bij elke nieuwe versie een pentest uit, waar het kan geautomatiseerd. Een bevinding met een '
            . 'hoog risico die niet anders op te lossen is, betekent: niet in productie. Test daarnaast elk jaar '
            . 'op kwetsbaarheden (BIO2 8.08.03, 8.08.04).';
        $m[] = 'Zorg dat iedereen kwetsbaarheden kan melden, met een procedure voor Coordinated Vulnerability '
            . 'Disclosure (BIO2 5.26.01).';

        if (array_intersect($onderdelen, ['website', 'api']) !== []) {
            $m[] = 'Neem het systeem, de IP adressen en de API’s op in de registratie van alles wat via internet '
                . 'bereikbaar is (BIO2 5.14.04).';
        }
        if (in_array('website', $onderdelen, true)) {
            $m[] = 'Meld de website aan bij het Register Internetdomeinen Overheid, en houd dat elk half jaar '
                . 'actueel. Gebruik voor gevoelige gegevens ten minste een OV certificaat (BIO2 5.14.02, 5.14.05).';
        }
        if (in_array('email', $onderdelen, true)) {
            $m[] = 'Stel de standaarden voor mail in, zoals SPF, DKIM en DMARC, en controleer ze met internet.nl '
                . '(BIO2 5.14.01).';
        }
        if (in_array('domein', $onderdelen, true) && $u['cbw']['waar']) {
            $m[] = 'Registreer de nieuwe domeinnaam ook bij het NCSC. Dat hoort bij de registratieplicht uit de '
                . 'Cyberbeveiligingswet.';
        }
    }

    // ---- gevoelige informatie -----------------------------------------------------
    if (in_array($u['biv']['v'], ['h', 'zh'], true) || $rubricering === 'dep-vertrouwelijk'
        || str_starts_with($rubricering, 'stg-')) {
        $m[] = 'Versleutel waar het kan met middelen die een positief inzetadvies hebben van het NBV van de '
            . 'AIVD, en volg de adviezen van het NCSC over de sterkte (BIO2 8.21.04, 8.24).';
    }

    if (str_starts_with($rubricering, 'stg-')) {
        $m[] = 'Bepaal met het screeningsbeleid wie een VOG nodig heeft. Wie met staatsgeheime informatie werkt, '
            . 'heeft een vertrouwensfunctie en krijgt een veiligheidsonderzoek (BIO2 6.01.01).';
    } elseif ($u['biv']['v'] === 'zh') {
        $m[] = 'Bepaal met het screeningsbeleid wie een VOG nodig heeft (BIO2 6.01.01).';
    }

    if (in_array('ot', (array) ($a['omgeving'] ?? []), true)) {
        $m[] = 'Gebruik voor de operationele technologie ook de CSIR en IEC 62443. Die mogen een maatregel uit de '
            . 'BIO2 vervangen als ze minstens even sterk zijn.';
    }
    if (in_array('zorg', (array) ($a['omgeving'] ?? []), true)) {
        $m[] = 'Pas voor de zorginformatie ook NEN 7510 toe.';
    }

    $m[] = 'Leg de gekozen maatregelen vast in de verklaring van toepasselijkheid. Een risico dat tijdelijk wordt '
        . 'geaccepteerd, komt in het risicoregister met een plan om het op te lossen.';

    // ---- AI en burgers ------------------------------------------------------------
    if (q2_met_ai($a)) {
        $m[] = 'Aanvullende opleiding of training over verantwoord gebruik van AI is nodig.';

        if (($a['ai'] ?? '') === 'mogelijk') {
            $u['aandachtspunten'][] = 'Gebruik van AI in de toekomst wordt niet uitgesloten. We gaan er daarom van uit dat '
                . 'het ook daadwerkelijk gebeurt, en stellen de bijbehorende eisen.';
        }
    }

    if (($a['gebruikers'] ?? '') === 'burgers') {
        $m[] = 'Burgers of andere externen gebruiken het systeem. Besteed expliciet aandacht aan toegankelijkheid, '
            . 'transparantie en de informatieplicht richting betrokkenen.';
    }

    $u['maatregelen'] = [...$m, ...$u['maatregelen']];

    return $u;
}

/** Eisen aan de leverancier en de keten, uit de BIO2 en de antwoorden. */
function q2_leveranciers(array $u, array $a, bool $heeftPg, bool $heeftCloud): array
{
    $inkoop = (string) ($a['inkoopvorm'] ?? 'geen');
    $e = [];

    if ($inkoop !== 'geen') {
        $e[] = 'Neem de beveiligingseisen uit dit advies op in de uitvraag en in het contract. Ze zijn gebaseerd op '
            . 'deze risicoafweging (BIO2 5.19.01, 5.20.01).';
        $e[] = 'Sluit de algemene voorwaarden van de leverancier uit waar dat kan. Lukt dat niet, beoordeel dan de '
            . 'risico’s (BIO2 5.20.02).';
        $e[] = 'De leverancier toont elk jaar met een onafhankelijk onderzoek aan dat hij aan de eisen voldoet, '
            . 'voor de hele dienst en inclusief zijn eigen leveranciers. De organisatie mag zelf een audit laten '
            . 'doen (BIO2 5.20.03, 5.20.04).';
        $e[] = 'De leverancier meldt incidenten, datalekken en kwetsbaarheden die de dienst raken zo snel, dat de '
            . 'organisatie zelf binnen 24 uur kan melden. Spreek een termijn van enkele uren af (BIO2 5.20.05).';
        $e[] = 'Het contract bevat een uitgewerkte exitstrategie, en noemt de situaties waarin het ontbonden kan '
            . 'worden (BIO2 5.20.06).';

        if (($a['pg_subverwerkers'] ?? '') === 'ja') {
            $e[] = 'Vóór het contract geeft de leverancier inzicht in zijn eigen leveranciers en de risico’s daarin. '
                . 'Hij legt hun dezelfde eisen op, en meldt wijzigingen in die keten (BIO2 5.21.02 tot en met 5.21.04).';
        }

        $u['maatregelen'][] = 'Neem de leverancier en het contract op in de registratie van leveranciers, en '
            . 'beoordeel elk jaar of hij nog aan de eisen voldoet (BIO2 5.22.01).';

        if (in_array($a['leverancier_bewijs'] ?? '', ['deels', 'nee'], true)) {
            $u['aandachtspunten'][] = 'De leverancier kan nog niet met een onafhankelijk onderzoek voor de hele '
                . 'dienst aantonen dat hij veilig werkt. Regel dat vóór het contract, of beoordeel het restrisico.';
        }
        if (in_array($a['leverancier_melden'] ?? '', ['later', 'nee'], true)) {
            $u['aandachtspunten'][] = 'Er is nog geen korte meldtermijn met de leverancier afgesproken. Zonder die '
                . 'afspraak haalt de organisatie de meldtermijn van 24 uur waarschijnlijk niet.';
        }
        if (($a['leverancier_overstap'] ?? '') === 'vast') {
            $u['aandachtspunten'][] = 'De organisatie zit vast aan deze leverancier. Bepaal vóór het contract of die '
                . 'afhankelijkheid beheersbaar is (BIO2 5.20.06).';
        }
        if (($a['leverancier_cbw'] ?? '') === 'ja') {
            $u['aandachtspunten'][] = 'De leverancier valt zelf onder de Cyberbeveiligingswet of NIS2. Dat helpt, maar '
                . 'het vervangt de eisen uit de BIO2 in het contract niet.';
        }
        if ($inkoop === 'raamcontract') {
            $u['aandachtspunten'][] = 'Dit is een raamcontract. De informatie die je hierboven hebt opgegeven bepaalt '
                . 'de grenzen van álle verwerkingen onder toekomstige nadere opdrachten. Wat hier niet in staat, mag '
                . 'daar straks niet.';
        }
    }

    if ($heeftCloud && ($a['cloud_aanbieder'] ?? 'extern') !== 'overheid') {
        $e[] = 'De cloudleverancier toont met ISO 27001 aan dat hij veilig werkt. Bij niet openbare gegevens hoort '
            . 'daar ISO 27017 bij, en bij persoonsgegevens ISO 27018.';
    }

    if ($heeftPg) {
        $e[] = 'De leverancier ondertekent de verwerkersovereenkomst van de organisatie.';
    }

    $cis = trim((string) ($a['cis_normen'] ?? ''));
    if ($cis !== '') {
        $e[] = 'De volgende CIS Benchmarks of STIGs zijn van toepassing: ' . $cis;
    }

    $externe = trim((string) ($a['externe_eisen'] ?? ''));
    if ($externe !== '') {
        $e[] = 'Externe eisen die zijn opgegeven: ' . $externe;
    }

    $u['eisen_leverancier'] = [...$e, ...$u['eisen_leverancier']];

    return $u;
}

/** Wie er collegiaal advies geeft en wie de uitkomst vaststelt. */
function q2_wie_kijkt_mee(array $u, array $a, bool $heeftPg, bool $heeftCloud, bool $dreigingHoog): array
{
    $rollen = ['CISO'];

    if ($heeftPg) {
        $rollen[] = 'CPO';
    }
    if (q2_met_ai($a)) {
        $rollen[] = 'CDO';
    }
    if ($heeftCloud || ($a['inkoopvorm'] ?? 'geen') !== 'geen') {
        $rollen[] = 'CTO';
    }

    $cioRedenen = [];
    if ($u['tbb']['cruciaal']) {
        $cioRedenen[] = 'het een cruciaal systeem is';
    }
    if (($a['bedrijfskritisch'] ?? '') === 'ja') {
        $cioRedenen[] = 'het een bedrijfskritisch systeem is';
    }
    if (in_array('zh', $u['biv'], true) || $dreigingHoog) {
        $cioRedenen[] = 'er sprake is van een zeer hoog risico';
    }
    if ($u['materieel_cloudgebruik']['waar']) {
        $cioRedenen[] = 'er sprake is van materieel cloudgebruik';
    }
    if ($u['abro']['waar']) {
        $cioRedenen[] = 'de ABRO van toepassing is';
    }
    if (in_array('tbb_politiek', (array) ($a['tbb_soorten'] ?? []), true)) {
        $cioRedenen[] = 'een incident politieke schade kan geven';
    }

    // Volgens de BIO2 is de lijn eigenaar van het risico; de CISO adviseert.
    $u['vaststelling'] = ['de proceseigenaar, na advies van de CISO'];

    if ($cioRedenen !== []) {
        $rollen[] = 'CIO';
        $u['cio_reden'] = cm_opsomming($cioRedenen);
        $u['vaststelling'] = ['de proceseigenaar, na advies van de CISO en de CIO'];
    }

    $u['collegiaal_advies_rollen'] = array_values(array_unique($rollen));

    return $u;
}

/** @param array{b:string,i:string,v:string} $biv */
function q2_risiconiveau(array $biv, bool $dreigingHoog, int $tbb): string
{
    if ($dreigingHoog || in_array('zh', $biv, true) || ($tbb >= 1 && $tbb <= 2)) {
        return 'zeer hoog';
    }
    if ($tbb === 3 || in_array('h', $biv, true)) {
        return 'hoog';
    }
    if ($tbb === 4 || in_array('m', $biv, true)) {
        return 'midden';
    }

    return 'laag';
}

/**
 * Het gevolgde pad door de beslisboom, zodat te zien is welke vragen tot welke
 * uitkomst hebben geleid.
 *
 * @return list<array{vraag:string,antwoord:string,gevolg:string,geraakt:bool}>
 */
function q2_beslisboom_pad(array $a, array $u, array $ctx): array
{
    $stap = static fn (string $vraag, string $antwoord, string $gevolg, bool $geraakt): array => [
        'vraag' => $vraag,
        'antwoord' => $antwoord,
        'gevolg' => $geraakt ? $gevolg : 'Geen gevolg',
        'geraakt' => $geraakt,
    ];
    $jaNee = static fn (bool $waar): string => $waar ? 'Ja' : 'Nee';
    $s = [];

    $s[] = $stap(
        'Geldt de Cyberbeveiligingswet?',
        $jaNee($u['cbw']['waar']) . ' (afgeleid uit de soort organisatie)',
        'Zorgplicht, meldplicht en registratieplicht',
        $u['cbw']['waar']
    );

    foreach (['aanvullende_wetgeving' => 'Gelden er nog andere wetten of regels?',
        'externe_eisen' => 'Zijn er externe eisen van toepassing?',
        'cis_normen' => 'Zijn er CIS Benchmarks of STIGs van toepassing?'] as $key => $vraag) {
        $ingevuld = trim((string) ($a[$key] ?? '')) !== '';
        $s[] = $stap($vraag, $jaNee($ingevuld), 'Opnemen in de uitwerking', $ingevuld);
    }

    $s[] = $stap(
        'Worden er persoonsgegevens verwerkt?',
        $jaNee($ctx['heeftPg']),
        q2_heeft_analyse($u, 'DPIA') ? 'DPIA noodzakelijk'
            : (q2_heeft_analyse($u, 'DPIA pretoets') ? 'DPIA pretoets door de CPO' : 'Geen DPIA nodig'),
        $ctx['heeftPg']
    );

    $s[] = $stap(
        'Is het systeem via internet te bereiken?',
        q2_antwoord_tekst('bereikbaar', $a),
        'Internetstandaarden, een pentest bij elke versie en een meldpunt voor kwetsbaarheden',
        q2_via_internet($a)
    );

    if ((array) ($a['inloggen'] ?? []) !== []) {
        $s[] = $stap(
            'Logt iedereen in met meer dan één factor?',
            q2_antwoord_tekst('mfa', $a),
            'Multifactorauthenticatie regelen, of andere maatregelen met de CISO',
            ($a['mfa'] ?? '') !== 'ja'
        );
    }

    $s[] = $stap(
        'Is er sprake van cloudgebruik?',
        q2_antwoord_tekst('cloud', $a),
        'Cloudbeleid: ' . mb_strtolower((string) ($u['cloud']['label'] ?? ''), 'UTF-8'),
        $ctx['heeftCloud']
    );

    if ($ctx['heeftCloud']) {
        $buiten = ($a['cloud_locatie'] ?? '') !== 'eer';
        $s[] = $stap(
            'Blijven de data binnen de EER of Zwitserland?',
            q2_antwoord_tekst('cloud_locatie', $a),
            'Niet toegestaan' . (q2_heeft_analyse($u, 'DTIA') ? ', en een DTIA noodzakelijk' : ''),
            $buiten
        );
        $s[] = $stap(
            'Valt de leverancier onder recht van buiten de EU of de EER?',
            q2_antwoord_tekst('cloud_jurisdictie', $a),
            'Afgeraden voor het primaire proces',
            ($a['cloud_jurisdictie'] ?? '') === 'ja'
        );
        $s[] = $stap(
            'Is er sprake van materieel cloudgebruik?',
            $jaNee($u['materieel_cloudgebruik']['waar']) . ' (afgeleid)',
            'Risicoanalyse, exitplan en een melding bij CISO Rijk',
            $u['materieel_cloudgebruik']['waar']
        );
    }

    $s[] = $stap(
        'Is er een hoog dreigingsprofiel?',
        $jaNee($ctx['dreigingHoog']) . ' (afgeleid uit de kenmerken van het systeem)',
        'Volledige risicoanalyse en advies van de AIVD of de MIVD',
        $ctx['dreigingHoog']
    );

    $s[] = $stap(
        'Een (kritisch) strategisch proces op een vitaal systeem?',
        $jaNee($ctx['strategischVitaal']),
        'Volledige risicoanalyse',
        $ctx['strategischVitaal']
    );

    if ($ctx['heeftPg']) {
        $s[] = $stap(
            'Is de verwerking grootschalig?',
            ucfirst((string) $u['grootschalig']['uitkomst']) . ' (afgeleid uit aantal, omvang, duur en bereik)',
            q2_heeft_analyse($u, 'DPIA') ? 'DPIA noodzakelijk' : 'DPIA pretoets door de CPO',
            $u['grootschalig']['uitkomst'] !== 'nee'
        );

        $blokkeert = in_array($a['pg_grondslag'] ?? '', ['gerechtvaardigd_belang', 'onbekend'], true);
        $s[] = $stap(
            'Is er een geldige grondslag?',
            q2_antwoord_tekst('pg_grondslag', $a),
            'Blokkerend: de grondslag moet eerst kloppen',
            $blokkeert
        );
    }

    $s[] = $stap(
        'Is de bewaartermijn bepaald?',
        q2_antwoord_tekst('bewaartermijn', $a),
        'Blokkerend: zonder termijn voldoe je niet aan de AVG en de Archiefwet',
        ($a['bewaartermijn'] ?? '') === 'onbepaald'
    );

    $s[] = $stap(
        'Komt er AI in het systeem?',
        q2_antwoord_tekst('ai', $a),
        'AI scan noodzakelijk' . (q2_heeft_analyse($u, 'IAMA') ? ' · IAMA noodzakelijk' : ''),
        q2_met_ai($a)
    );

    $s[] = $stap(
        'Is het een maatschappelijk vitaal proces?',
        $jaNee($u['abro']['waar']) . ' (afgeleid uit de procesclassificatie)',
        'ABRO quickscan noodzakelijk',
        $u['abro']['waar']
    );

    $s[] = $stap(
        'Hoe zwaar zijn de te beschermen belangen?',
        q2_tbb_label($u['tbb']['niveau']),
        'Cruciaal systeem: op het overzicht, en een volledige risicoanalyse',
        $u['tbb']['cruciaal']
    );

    $s[] = $stap(
        'Welk beveiligingsniveau geldt?',
        sprintf(
            'B %s · I %s · V %s',
            cm_niveau_label($u['biv']['b']),
            cm_niveau_label($u['biv']['i']),
            cm_niveau_label($u['biv']['v'])
        ),
        $u['niveau'],
        true
    );

    return $s;
}

/**
 * Controles op de privacykant: grondslag, subsidiariteit, proportionaliteit en
 * de rechten van betrokkenen.
 */
function q2_privacy_checks(array $u, array $a, bool $heeftPg): array
{
    if (!$heeftPg) {
        return $u;
    }

    $grondslag = (string) ($a['pg_grondslag'] ?? '');

    if ($grondslag === 'gerechtvaardigd_belang') {
        $u['blokkerend'][] = 'Als grondslag is gerechtvaardigd belang gekozen. Een overheidsorgaan kan zich daar '
            . 'voor de uitvoering van zijn publieke taken niet op beroepen; de AVG sluit dat uitdrukkelijk uit. '
            . 'Kies een andere grondslag (meestal een wettelijke verplichting of een taak van algemeen belang), of '
            . 'onderbouw waarom deze verwerking aantoonbaar buiten de publieke taak valt.';
    }

    if ($grondslag === 'toestemming') {
        $u['aandachtspunten'][] = 'De grondslag is toestemming. Die moet vrij te weigeren en net zo makkelijk in te '
            . 'trekken zijn als te geven. Tussen overheid en burger is dat door de ongelijke verhouding lastig hard '
            . 'te maken: ga na of een wettelijke taak hier niet de juistere grondslag is, en zorg dat de dienst ook '
            . 'werkt als iemand weigert.';
    }

    if ($grondslag === 'onbekend') {
        $u['blokkerend'][] = 'De grondslag voor de verwerking is nog niet bepaald. Zonder grondslag mag er niet '
            . 'verwerkt worden; dit moet vaststaan voordat het project start. De CPO helpt bij het bepalen ervan.';
    }

    if (($a['pg_subsidiariteit'] ?? '') === 'kan_minder') {
        $u['aandachtspunten'][] = 'Het doel kan volgens de invuller ook met minder of anoniemere gegevens worden '
            . 'bereikt. Leg vast waarom daar niet voor is gekozen, of pas het ontwerp aan. Dit is een kernvraag in '
            . 'de DPIA.';
    }

    if (($a['pg_proportionaliteit'] ?? '') === 'twijfel') {
        $u['aandachtspunten'][] = 'Er is twijfel of de inbreuk op de privacy in verhouding staat tot het doel. '
            . 'Werk die afweging expliciet uit en leg hem voor aan de CPO.';
    }

    if (in_array($a['pg_rechten'] ?? '', ['deels', 'nee'], true)) {
        $u['eisen_leverancier'][] = 'Het systeem ondersteunt inzage, correctie en verwijdering van persoonsgegevens '
            . 'binnen de wettelijke termijn van een maand. Kan dat alleen handmatig, dan wordt de procedure '
            . 'daarvoor vastgelegd en belegd.';
    }

    if (($a['pg_subverwerkers'] ?? '') === 'ja') {
        $u['eisen_leverancier'][] = 'De leverancier levert een actuele lijst van subverwerkers, meldt wijzigingen '
            . 'vooraf, en legt dezelfde verplichtingen contractueel aan hen op.';
    }

    return $u;
}

/**
 * Bewaren, vernietigen en archiveren. Levert naast losse eisen ook een
 * stappenlijst voor het inrichten van de archiefprocessen.
 */
function q2_archiefwet(array $u, array $a, bool $heeftPg, bool $heeftCloud): array
{
    $bewaartermijn = (string) ($a['bewaartermijn'] ?? '');
    $vernietiging = (string) ($a['archief_vernietiging'] ?? '');
    $export = (string) ($a['archief_export'] ?? '');
    $extern = $heeftCloud || ($a['inkoopvorm'] ?? 'geen') !== 'geen';

    $stappen = [
        [
            'titel' => 'Wijs de zorgdrager en de beheerder aan',
            'tekst' => 'Het overheidsorgaan blijft onder de Archiefwet 2026 verantwoordelijk voor de informatie, '
                . 'ook als een leverancier het systeem draait. Leg vast wie binnen de organisatie die rol heeft, '
                . 'en leg in het contract vast dat de leverancier het beheer uitvoert namens de zorgdrager en zelf '
                . 'geen zeggenschap over bewaren of vernietigen krijgt.',
        ],
        [
            'titel' => 'Zoek de categorie in de selectielijst op',
            'tekst' => 'De vastgestelde selectielijst bepaalt per categorie informatie of die blijvend bewaard '
                . 'wordt of na een termijn vernietigd. Bepaal onder welke categorie dit proces valt voordat het '
                . 'systeem wordt ingericht: zonder die categorie kun je geen bewaartermijn instellen en niet '
                . 'aantonen dat je aan de Archiefwet voldoet. De BIO2 vraagt hetzelfde (5.33.01).',
        ],
        [
            'titel' => 'Zet de bewaartermijn in het systeem, niet in een document',
            'tekst' => 'Een termijn die alleen in beleid staat, wordt in de praktijk niet nageleefd. Leg per '
                . 'informatiesoort vast wanneer de termijn begint te lopen, en laat het systeem daar zelf op sturen.',
        ],
        [
            'titel' => 'Richt de metagegevens in',
            'tekst' => 'Leg bij elk stuk informatie vast wat het is, wie het heeft gemaakt of ontvangen, wanneer, '
                . 'binnen welk proces, en welke bewaartermijn en waardering erbij horen. Binnen de Rijksoverheid is '
                . 'MDTO het metagegevensschema daarvoor. Zonder metagegevens is informatie later niet terug te '
                . 'vinden, niet te waarderen en niet over te brengen.',
        ],
        [
            'titel' => 'Kies bestandsformaten die je over tien jaar nog kunt openen',
            'tekst' => 'Gebruik open, gedocumenteerde formaten zoals PDF/A, XML, CSV of JSON in plaats van formaten '
                . 'die alleen de leverancier kan lezen. Spreek dit af voordat het systeem in gebruik gaat; achteraf '
                . 'converteren kost meer en levert verlies op.',
        ],
        [
            'titel' => 'Zorg dat vernietigen ook echt gebeurt',
            'tekst' => 'Na afloop van de bewaartermijn moet informatie daadwerkelijk worden vernietigd, ook uit '
                . 'reservekopieën binnen een redelijke termijn. Maak van elke vernietiging een verklaring op waarin staat '
                . 'wat is vernietigd, op grond van welke categorie en wanneer.',
        ],
        [
            'titel' => 'Reken op de overbrengingstermijn van tien jaar',
            'tekst' => 'De Archiefwet 2026 brengt de overbrengingstermijn terug van twintig naar tien jaar. '
                . 'Informatie met de waardering "blijvend bewaren" gaat dus twee keer zo snel naar het Nationaal '
                . 'Archief als onder de oude wet. Voor een systeem dat nu wordt ingekocht betekent dat: de '
                . 'exportfunctie moet binnen de looptijd van dit contract werken. Zorg dat het systeem een export '
                . 'mét metagegevens kan leveren in een formaat dat het Nationaal Archief accepteert, en test dat '
                . 'een keer voordat je het nodig hebt.',
        ],
        [
            'titel' => 'Bepaal nu al wat straks openbaar wordt',
            'tekst' => 'Bij overbrenging wordt informatie in beginsel openbaar, tenzij er een grond is om de '
                . 'openbaarheid te beperken (bijvoorbeeld de bescherming van persoonsgegevens of van de staat). '
                . 'Leg per informatiesoort vast of er een beperking nodig is en op welke grond, en zorg dat het '
                . 'systeem die aantekening als metagegeven kan vasthouden.',
        ],
    ];

    if ($extern) {
        $stappen[] = [
            'titel' => 'Regel de exit voordat je begint',
            'tekst' => 'Neem als contracteis op dat bij beëindiging alle informatie in een bruikbaar, '
                . 'leveranciersonafhankelijk formaat wordt teruggeleverd, inclusief metagegevens, en dat de '
                . 'leverancier daarna aantoonbaar al zijn kopieën vernietigt. Zonder deze afspraak ben je bij een '
                . 'contractwissel je archief kwijt of zit je eraan vast.',
        ];
    }

    $stappen[] = [
        'titel' => 'Houd rekening met de Wet open overheid',
        'tekst' => 'Informatie die onder de Woo opvraagbaar is, moet binnen de wettelijke termijn gevonden en '
            . 'geleverd kunnen worden. Dat stelt eisen aan doorzoekbaarheid en aan de mogelijkheid om een selectie '
            . 'te exporteren, ook uit chats, samenwerkingsomgevingen en mail.',
    ];

    $u['archief'] = $stappen;

    if ($bewaartermijn === 'onbepaald') {
        $u['blokkerend'][] = 'Er is nog geen bewaartermijn bepaald. Zonder termijn kun je niet voldoen aan de AVG '
            . '(niet langer bewaren dan nodig) en niet aan de Archiefwet 2026 (tijdig vernietigen of overbrengen). '
            . 'Bepaal eerst onder welke categorie van de selectielijst dit proces valt.';
    } elseif ($bewaartermijn === 'eigen') {
        $u['aandachtspunten'][] = 'De bewaartermijn is zelf bepaald in plaats van uit de selectielijst overgenomen. '
            . 'Controleer of er voor dit proces niet toch een categorie in de selectielijst bestaat; die gaat voor.';
    }

    if ($vernietiging === 'nee') {
        $u['blokkerend'][] = 'Het systeem kan informatie na afloop van de bewaartermijn niet vernietigen. Daarmee '
            . 'kan de organisatie niet aan de bewaarplicht voldoen. Dit moet opgelost zijn voordat het systeem in '
            . 'gebruik wordt genomen.';
        $u['eisen_leverancier'][] = 'Het systeem ondersteunt het vernietigen van informatie na afloop van de '
            . 'bewaartermijn, inclusief uit reservekopieën binnen een redelijke termijn.';
    } elseif ($vernietiging === 'handmatig') {
        $u['aandachtspunten'][] = 'Vernietiging kan alleen handmatig. Leg de procedure vast, beleg wie hem uitvoert '
            . 'en met welke frequentie, en zorg dat er een vernietigingsverklaring wordt opgemaakt.';
    }

    if ($export === 'nee') {
        $u['eisen_leverancier'][] = 'De leverancier levert een export van alle informatie inclusief metagegevens, in '
            . 'een open en gedocumenteerd formaat, op elk moment tijdens en bij beëindiging van het contract.';
        $u['aandachtspunten'][] = 'De informatie is nu niet zonder de leverancier op te halen. Dat blokkeert '
            . 'overbrenging naar het Nationaal Archief, maakt verzoeken om informatie (Woo) moeilijker, en maakt een contractwissel riskant.';
    } elseif ($export === 'eigen') {
        $u['eisen_leverancier'][] = 'De export is beschikbaar in een open, gedocumenteerd formaat, niet alleen in '
            . 'een eigen formaat van de leverancier.';
    }

    if ($heeftPg && $bewaartermijn !== 'onbepaald') {
        $u['maatregelen'][] = 'Neem de bewaartermijn ook op in het verwerkingsregister, zodat de AVG en het archief '
            . 'dezelfde termijn hanteren.';
    }

    return $u;
}

/**
 * Legt per weging vast: welk niveau het is, wat dat niveau betekent, waarom het
 * zo is en wie dat heeft bepaald.
 *
 * @return list<array<string,string>>
 */
function q2_wegingen(array $a, array $u): array
{
    $rol = ($a['rol'] ?? 'behoeftesteller') === 'beoordelaar' ? 'de beoordelaar' : 'de behoeftesteller';

    $motivatie = static function (string $veld, string $voor) use ($a): string {
        $eigen = trim((string) ($a[$veld] ?? ''));

        return $eigen !== '' ? $eigen : q2_motivatie_voorstel($voor, $a);
    };

    // Proces en systeem hebben één motivatie, dus ook één weging.
    $wegingen = [[
        'label' => 'Belang van proces en systeem',
        'niveau' => sprintf(
            '%s proces, %s systeem',
            q2_antwoord_tekst('proces_classificatie', $a),
            mb_strtolower(q2_antwoord_tekst('systeem_classificatie', $a), 'UTF-8')
        ),
        'omschrijving' => implode(' ', array_filter([
            q2_toelichting_proces()[(string) ($a['proces_classificatie'] ?? '')] ?? '',
            q2_toelichting_systeem()[(string) ($a['systeem_classificatie'] ?? '')] ?? '',
        ])),
        'motivatie' => trim((string) ($a['proces_motivatie'] ?? '')),
        'herkomst' => 'Bepaald door ' . $rol . '.',
    ]];

    $voorstelB = q2_voorstel_beschikbaarheid($a);
    $wegingen[] = [
        'label' => 'Beschikbaarheid',
        'niveau' => cm_niveau_label($u['biv']['b']),
        'omschrijving' => q2_toelichting_beschikbaarheid()[$u['biv']['b']] ?? '',
        'motivatie' => $motivatie('beschikbaarheid_motivatie', 'beschikbaarheid'),
        'herkomst' => $voorstelB === $u['biv']['b']
            ? 'Voorstel van de tool, overgenomen door ' . $rol . '.'
            : sprintf('De tool stelde %s voor; %s koos %s.', cm_niveau_label($voorstelB), $rol, cm_niveau_label($u['biv']['b'])),
    ];

    foreach (['i' => 'integriteit', 'v' => 'vertrouwelijkheid'] as $letter => $naam) {
        $afgeleid = $u['biv_afgeleid'][$letter] ?? $u['biv'][$letter];
        $toelichting = $naam === 'integriteit' ? q2_toelichting_integriteit() : q2_toelichting_vertrouwelijkheid();

        $wegingen[] = [
            'label' => ucfirst($naam),
            'niveau' => cm_niveau_label($u['biv'][$letter]),
            'omschrijving' => $toelichting[$u['biv'][$letter]] ?? '',
            'motivatie' => $motivatie($naam . '_motivatie', $naam),
            'herkomst' => isset($u['biv_verhoogd'][$letter])
                ? sprintf('De tool leidde %s af; %s stelde bij naar %s.', cm_niveau_label($afgeleid), $rol, cm_niveau_label($u['biv'][$letter]))
                : 'Afgeleid door de tool en overgenomen door ' . $rol . '.',
        ];
    }

    $wegingen[] = [
        'label' => 'Recovery Point Objective',
        'niveau' => $u['rpo'],
        'omschrijving' => 'Hoeveel werk er bij een calamiteit verloren mag gaan. Dit bepaalt hoe vaak er een '
            . 'reservekopie gemaakt moet worden.',
        'motivatie' => $motivatie('rpo_motivatie', 'rpo'),
        'herkomst' => q2_voorstel_rpo($a) === (string) ($a['rpo'] ?? '')
            ? 'Voorstel van de tool, overgenomen door ' . $rol . '.'
            : 'Afwijkend van het voorstel van de tool, gekozen door ' . $rol . '.',
    ];

    $wegingen[] = [
        'label' => 'Te beschermen belangen',
        'niveau' => $u['tbb']['label'],
        'omschrijving' => 'Hoe zwaar een incident in het ergste geval kan uitpakken, volgens de tabel in de '
            . 'Cyberbeveiligingsregeling sector overheid. TBB 4 is het lichtst en TBB 1 het zwaarst. Bij TBB 1, 2 '
            . 'of 3 is het systeem cruciaal.',
        'motivatie' => $motivatie('tbb_motivatie', 'tbb'),
        'herkomst' => 'Berekend door de tool, uit de schade die ' . $rol . ' koos, de rubricering en de '
            . 'betrouwbaarheidseisen.',
    ];

    $wegingen[] = [
        'label' => 'Dreigingsprofiel',
        'niveau' => ucfirst((string) $u['dreiging']['niveau']),
        'omschrijving' => $u['dreiging']['niveau'] === 'hoog'
            ? 'Bescherming is nodig tegen inlichtingendiensten, terreurgroepen of georganiseerde criminaliteit. Dat '
                . 'vraagt om een volledige risicoanalyse en om advies van de AIVD of de MIVD.'
            : 'Deze groepen vormen geen bijzondere bedreiging voor dit proces of systeem.',
        'motivatie' => $motivatie('dreiging_motivatie', 'dreiging'),
        'herkomst' => ($a['dreiging_verhoging'] ?? '') === 'hoog'
            ? 'Handmatig op hoog gezet door ' . $rol . '.'
            : 'Afgeleid uit de aangekruiste kenmerken van het systeem.',
    ];

    if ($u['cloud'] !== null) {
        $wegingen[] = [
            'label' => 'Cloudbeleid',
            'niveau' => $u['cloud']['label'],
            'omschrijving' => 'Wat het rijksbrede cloudbeleid van 2026 over deze vorm van cloud zegt.',
            'motivatie' => $u['cloud']['redenen'] === []
                ? 'De regels uit het cloudbeleid laten deze vorm toe, onder de voorwaarden in het advies.'
                : implode(' ', $u['cloud']['redenen']),
            'herkomst' => 'Afgeleid door de tool.',
        ];
    }

    return $wegingen;
}
