<?php

/**
 * De beslisboom.
 *
 * Deze functie zet de antwoorden om in een uitkomst: beveiligingsniveau,
 * cloudcategorie, benodigde aanvullende analyses, eisen en wie er moet
 * meekijken. De regels komen uit het processchema en uit de paragraaf
 * ‘Uitkomst en advies’ van de IB & Privacy Quickscan.
 */

declare(strict_types=1);

/**
 * @param array<string,mixed> $a antwoorden
 * @return array<string,mixed>
 */
function qs_evalueer(array $a): array
{
    $u = [
        'biv'              => [],
        'biv_afgeleid'     => [],
        'biv_ondergrens'   => [],
        'biv_redenen'      => [],
        'biv_verhoogd'     => [],
        'wegingen'         => [],
        'materieel_cloudgebruik' => ['waar' => false, 'reden' => ''],
        'abro'             => ['waar' => false, 'reden' => ''],
        'dreiging'         => ['niveau' => 'normaal', 'redenen' => []],
        'grootschalig'     => ['uitkomst' => 'nee', 'redenen' => []],
        'ai_risico'        => ['niveau' => 'geen', 'label' => '', 'redenen' => []],
        'aannames'         => [],
        'persoonsgegevens' => [],
        'archief'          => [],
        'uitzondering'     => false,
        'bbn'              => 1,
        'bbn_stappen'      => [],
        'rto'              => '',
        'rpo'              => '',
        'cloud_categorie'  => null,
        'cloud_eisen'      => [],
        'analyses'         => [],
        'blokkerend'       => [],
        'aandachtspunten'  => [],
        'wetgeving'        => [],
        'kaders'           => [],
        'eisen_leverancier' => [],
        'maatregelen'      => [],
        'collegiaal_advies' => [],
        'collegiaal_advies_rollen' => [],
        'cio_reden'        => '',
        'vaststelling'     => [],
        'risiconiveau'     => 'laag',
        'beslisboom'       => [],
    ];

    // 'Weet ik niet' wordt hier vervangen door de aanname die bij de vraag hoort.
    // Verderop rekenen we met die aannames; het advies benoemt ze apart.
    $metAannames = qs_met_aannames($a);
    $a = $metAannames['antwoorden'];
    $u['aannames'] = $metAannames['aannames'];

    // Uitzonderingsgeval: geen te beschermen informatie. Dan is de rest niet gevraagd.
    if (($a['te_beschermen_informatie'] ?? 'ja') === 'nee') {
        return qs_uitzondering_geen_bescherming($u, $a);
    }

    // De aangekruiste soorten informatie bepalen wat er over persoonsgegevens
    // vaststaat. We schrijven dat terug, zodat de regels hieronder met concrete
    // waarden kunnen werken in plaats van steeds opnieuw te tellen.
    $pg = qs_afgeleide_persoonsgegevens($a);
    $a['pg_bijzonder_soorten'] = $pg['bijzonder_soorten'];
    $a['pg_bsn'] = $pg['bsn'];
    $u['persoonsgegevens'] = $pg;

    $heeftPg = $pg['heeft'];
    $pgCats = $pg['categorieen'];
    $pgBijzonder = $pg['bijzonder'];
    $rubricering = (string) ($a['rubricering'] ?? 'openbaar');
    $procesKlasse = (string) ($a['proces_classificatie'] ?? '');
    $systeemKlasse = (string) ($a['systeem_classificatie'] ?? '');
    $dreiging = qs_afgeleid_dreigingsprofiel($a);
    $dreigingHoog = $dreiging['niveau'] === 'hoog';
    $u['dreiging'] = $dreiging;
    $u['grootschalig'] = qs_afgeleide_grootschaligheid($a);
    $u['ai_risico'] = qs_afgeleid_ai_risico($a);
    $cloud = (string) ($a['cloud'] ?? 'nee');
    $heeftCloud = $cloud !== 'nee' && $cloud !== '';
    $cloudType = $heeftCloud ? (string) ($a['cloud_type'] ?? '') : '';
    $cloudLocatie = $heeftCloud ? (string) ($a['cloud_locatie'] ?? '') : '';
    $afwijkend = (array) ($a['afwijkend'] ?? []);
    $abro = qs_abro_van_toepassing($a);

    // ------------------------------------------------------------------
    // 1. Betrouwbaarheidseisen: afgeleid uit eerdere antwoorden
    // ------------------------------------------------------------------
    $eisen = qs_effectieve_biv($a);

    $B = $eisen['b'];
    $I = $eisen['i'];
    $V = $eisen['v'];

    $u['biv'] = ['b' => $B, 'i' => $I, 'v' => $V];
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

    // ------------------------------------------------------------------
    // 2. Beveiligingsniveau (BBN) volgens het processchema
    // ------------------------------------------------------------------
    $bbnUitV = qs_bbn_uit_niveau($V);
    $bbnUitBI = max(qs_bbn_uit_niveau($B), qs_bbn_uit_niveau($I));
    $bbn = max($bbnUitV, $bbnUitBI);

    $u['bbn_stappen'][] = [
        'label' => 'Vertrouwelijkheid is ' . cm_niveau_label($V),
        'gevolg' => 'BBN ' . $bbnUitV,
    ];
    $u['bbn_stappen'][] = [
        'label' => 'Hoogste van beschikbaarheid (' . cm_niveau_label($B)
            . ') en integriteit (' . cm_niveau_label($I) . ')',
        'gevolg' => 'BBN ' . $bbnUitBI,
    ];

    $strategischVitaal = in_array($procesKlasse, ['strategisch', 'kritisch-strategisch'], true)
        && $systeemKlasse === 'vitaal';

    if ($strategischVitaal && $bbn < 3) {
        $bbn++;
        $u['bbn_stappen'][] = [
            'label' => 'Een (kritisch) strategisch proces op een vitaal systeem',
            'gevolg' => 'een niveau hoger: BBN ' . $bbn,
        ];
    }

    if (($a['bedrijfskritisch'] ?? '') === 'ja' && $bbn < 2) {
        $bbn = 2;
        $u['bbn_stappen'][] = [
            'label' => 'Bedrijfskritisch systeem',
            'gevolg' => 'het hele systeem minimaal BBN 2',
        ];
    }

    $u['bbn'] = $bbn;
    $u['ib_niveau'] = sprintf('IB niveau %d (vergelijkbaar met BBN%d)', $bbn, $bbn);

    // ------------------------------------------------------------------
    // 3. Herstel: RTO volgt uit beschikbaarheid, RPO is een keuze
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
    // 4. Cloudgebruik
    // ------------------------------------------------------------------
    if ($heeftCloud) {
        $u['cloud_categorie'] = qs_cloud_categorie(
            $rubricering,
            $pgCats,
            $pgBijzonder,
            $I,
            $V,
            $dreigingHoog,
            $bbn
        );
        $u = qs_cloud_eisen($u, $a, $cloudType, $cloudLocatie, $heeftPg, $rubricering);
    }

    // ------------------------------------------------------------------
    // 5. Aanvullende analyses en documenten
    // ------------------------------------------------------------------
    $materieel = qs_materieel_cloudgebruik($a, $B);
    $u['materieel_cloudgebruik'] = $heeftCloud
        ? $materieel
        : ['waar' => false, 'reden' => 'Er is geen sprake van clouddienstverlening.'];
    $u['abro'] = $abro;

    $u = qs_analyses($u, $a, [
        'afwijkend' => $afwijkend,
        'dreigingHoog' => $dreigingHoog,
        'strategischVitaal' => $strategischVitaal,
        'biv' => [$B, $I, $V],
        'heeftCloud' => $heeftCloud,
        'cloudLocatie' => $cloudLocatie,
        'heeftPg' => $heeftPg,
        'materieel' => $u['materieel_cloudgebruik'],
        'abro' => $abro,
        'grootschalig' => $u['grootschalig'],
        'aiRisico' => $u['ai_risico'],
    ]);

    $u = qs_privacy_checks($u, $a, $heeftPg);
    $u = qs_archiefwet($u, $a, $heeftPg, $heeftCloud);

    // ------------------------------------------------------------------
    // 6. Wetten en regels, kaders, eisen en maatregelen
    // ------------------------------------------------------------------
    $u = qs_wetgeving($u, $a, $rubricering, $heeftPg);
    $u = qs_eisen_en_maatregelen($u, $a, $heeftPg, $heeftCloud);
    $u = qs_wie_kijkt_mee($u, $a, $heeftPg, $heeftCloud, $dreigingHoog, [$B, $I, $V]);

    // ------------------------------------------------------------------
    // 7. Samenvattend risiconiveau
    // ------------------------------------------------------------------
    $u['risiconiveau'] = qs_risiconiveau($B, $I, $V, $dreigingHoog, $bbn);

    $u['wegingen'] = qs_wegingen($a, $u);

    $u['beslisboom'] = qs_beslisboom_pad($a, $u, [
        'heeftPg' => $heeftPg,
        'heeftCloud' => $heeftCloud,
        'cloudLocatie' => $cloudLocatie,
        'dreigingHoog' => $dreigingHoog,
        'strategischVitaal' => $strategischVitaal,
    ]);

    return $u;
}

function qs_bbn_uit_niveau(string $niveau): int
{
    return match ($niveau) {
        'zh' => 3,
        'm', 'h' => 2,
        default => 1,
    };
}

/** Bepaalt de cloudcategorie op basis van het type informatie. */
function qs_cloud_categorie(
    string $rubricering,
    string $pgCategorieen,
    string $pgBijzonder,
    string $I,
    string $V,
    bool $dreigingHoog,
    int $bbn = 1
): string {
    // De regels hieronder volgen de cloudtabel uit de quickscan, rij voor rij.
    if ($rubricering === 'staatsgeheim' || $dreigingHoog) {
        return 'zeer-hoog';
    }

    if ($rubricering === 'dep-vertrouwelijk'
        || $pgCategorieen === 'meerdere'
        || $pgBijzonder === 'meerdere'
        || in_array($I, ['h', 'zh'], true)
        || in_array($V, ['h', 'zh'], true)
        || $bbn >= 3) {
        return 'hoog';
    }

    if ($rubricering === 'intern' || $pgCategorieen === 'een' || $V === 'm') {
        return 'midden';
    }

    return 'laag';
}

/** Vertaalt de cloudcategorie naar concrete eisen. */
function qs_cloud_eisen(
    array $u,
    array $a,
    string $cloudType,
    string $cloudLocatie,
    bool $heeftPg,
    string $rubricering
): array {
    $categorie = $u['cloud_categorie'];

    // Per categorie één bondige kernregel; de specifieke gevallen komen hieronder,
    // zodat we niet twee keer hetzelfde opschrijven.
    $kernregel = match ($categorie) {
        'laag' => 'Categorie Laag: er mag gebruik worden gemaakt van alle clouddienstverleners, '
            . 'zolang dat niet in strijd is met wetten en regels.',
        'midden' => 'Categorie Midden: de informatie mag alleen in de EER worden opgeslagen, óf in '
            . 'een land waarvoor een adequaatheidsbesluit bestaat, óf op basis van een modelcontract '
            . 'dat voldoet aan de AVG.',
        'hoog' => 'Categorie Hoog: de data mogen alleen in de EER worden opgeslagen, óf in een land '
            . 'waarvoor een adequaatheidsbesluit bestaat.',
        'zeer-hoog' => 'Categorie Zeer hoog: er mag onder geen enkele voorwaarde gebruik worden '
            . 'gemaakt van een publieke clouddienstverlener.',
        default => '',
    };

    if ($kernregel !== '') {
        $u['cloud_eisen'][] = $kernregel;
    }

    $u['cloud_eisen'][] = sprintf(
        'Leveranciers of diensten uit landen met een actief cyberprogramma gericht tegen Nederlandse '
        . 'belangen (%s) zijn uitgesloten.',
        implode(', ', qs_uitgesloten_landen())
    );

    if ($categorie === 'zeer-hoog') {
        if ($cloudType === 'publiek' || $cloudType === 'hybride') {
            $u['blokkerend'][] = 'Bij cloudcategorie Zeer hoog mag onder geen enkele voorwaarde '
                . 'gebruik worden gemaakt van een publieke clouddienstverlener. De gekozen cloudvorm '
                . 'is daarmee niet toegestaan.';
        } else {
            $u['aandachtspunten'][] = 'Cloudcategorie Zeer hoog: publieke cloud is uitgesloten. '
                . 'Controleer of de gekozen constructie echt geen publieke componenten bevat.';
        }
    }

    if ($categorie === 'hoog') {
        if (in_array($cloudLocatie, ['modelcontract', 'buiten-eer', 'onbekend'], true)) {
            $u['cloud_eisen'][] = 'De data staat niet aantoonbaar in de EER of in een land met '
                . 'adequaatheidsbesluit. De CISO voert daarom een beoordeling uit op basis van de '
                . 'criteria van C2000, en er dient een explain met minimaal een uitgevoerde DPIA aan de '
                . 'CIO Rijk gestuurd te worden.';
        }
        $u['cloud_eisen'][] = 'Voor alle data (inclusief reservekopieën, data in transit en data at rest) '
            . 'wordt encryptie toegepast waarvan het sleutelbeheer binnen de eigen organisatie '
            . 'plaatsvindt, of bij een contractant die niet de cloudleverancier is en niet onder de '
            . 'CLOUD Act valt.';
    }

    if ($categorie === 'midden' && $cloudLocatie === 'buiten-eer') {
        $u['cloud_eisen'][] = 'Er kan niet worden voldaan aan opslag binnen de EER, een '
            . 'adequaatheidsbesluit of een modelcontract. De DPIA dient aan de CIO Rijk gestuurd te '
            . 'worden.';
    }

    if (in_array($categorie, ['midden', 'hoog'], true)) {
        $u['eisen_leverancier'][] = 'De cloudleverancier voldoet aan de controls van ISO 27001.';
        if ($rubricering !== 'openbaar') {
            $u['eisen_leverancier'][] = 'De cloudleverancier voldoet aan de controls van ISO 27017 '
                . '(er worden gegevens verwerkt die niet openbaar zijn).';
        }
        if ($heeftPg) {
            $u['eisen_leverancier'][] = 'De cloudleverancier voldoet aan de controls van ISO 27018 '
                . '(er worden persoonsgegevens verwerkt).';
        }
    }

    $land = trim((string) ($a['leverancier_land'] ?? ''));
    if ($land !== '' && qs_land_uitgesloten($land)) {
        $u['blokkerend'][] = sprintf(
            'De leverancier komt uit %s. Leveranciers of diensten uit landen met een actief '
            . 'cyberprogramma dat gericht is tegen Nederlandse belangen (%s) worden altijd '
            . 'uitgesloten.',
            $land,
            implode(', ', qs_uitgesloten_landen())
        );
    }

    return $u;
}

function qs_land_uitgesloten(string $land): bool
{
    $genormaliseerd = mb_strtolower(trim($land), 'UTF-8');

    foreach (['rusland', 'russia', 'china', 'chinese', 'iran', 'noord-korea', 'noord korea', 'north korea'] as $treffer) {
        if (str_contains($genormaliseerd, $treffer)) {
            return true;
        }
    }

    return false;
}

/** Bepaalt welke aanvullende analyses nodig zijn en wie ze beoordeelt. */
function qs_analyses(array $u, array $a, array $ctx): array
{
    $pgBijzonderNiveau = qs_afgeleide_persoonsgegevens($a)['bijzonder'];

    $voegToe = static function (array $u, string $naam, string $reden, string $beoordelaar): array {
        foreach ($u['analyses'] as $bestaand) {
            if ($bestaand['naam'] === $naam) {
                return $u;
            }
        }
        $u['analyses'][] = ['naam' => $naam, 'reden' => $reden, 'beoordelaar' => $beoordelaar];

        return $u;
    };

    $labels = [
        'productgericht' => 'er sprake is van een productgerichte uitvraag',
        'saas' => 'het om software als dienst (SaaS) gaat',
        'opensource' => 'er open source wordt gebruikt',
    ];
    $redenen = [];
    foreach ($ctx['afwijkend'] as $situatie) {
        if (isset($labels[$situatie])) {
            $redenen[] = $labels[$situatie];
        }
    }
    if ($redenen !== []) {
        $u = $voegToe(
            $u,
            'Motivatie & Marktanalyse',
            'Omdat ' . implode(' en ', $redenen) . '. Deze moet jaarlijks herhaald worden.',
            'CTO of CISO'
        );
    }

    if ($ctx['dreigingHoog']) {
        $u = $voegToe($u, 'Volledige Risicoanalyse', 'Er is sprake van een hoog dreigingsprofiel.', 'CTO of CISO');
    }
    if ($ctx['strategischVitaal']) {
        $u = $voegToe(
            $u,
            'Volledige Risicoanalyse',
            'Een (kritisch) strategisch proces in combinatie met een vitaal systeem.',
            'CTO of CISO'
        );
    }
    if (in_array('zh', $ctx['biv'], true)) {
        $u = $voegToe(
            $u,
            'Volledige Risicoanalyse',
            'Ten minste één van de betrouwbaarheidseisen is Zeer hoog.',
            'CTO of CISO'
        );
    }
    if ($ctx['materieel']['waar']) {
        $u = $voegToe(
            $u,
            'Volledige Risicoanalyse',
            $ctx['materieel']['reden'],
            'CTO of CISO'
        );
    }

    if ($ctx['heeftPg']) {
        $zwaar = array_intersect(
            (array) ($a['pg_bijzonder_soorten'] ?? []),
            qs_zware_bijzondere_gegevens()
        );
        $grootschalig = $ctx['grootschalig'];

        if ($zwaar !== []) {
            $soorten = qs_informatiesoorten();
            $labels = [];
            foreach ($zwaar as $soort) {
                $labels[] = mb_strtolower((string) ($soorten[$soort]['label'] ?? $soort), 'UTF-8');
            }
            $u = $voegToe(
                $u,
                'DPIA',
                'Er worden gegevens verwerkt over ' . implode(' en ', $labels) . '. Die categorieën '
                . 'gelden altijd als een gevoelige verwerking, ongeacht de omvang.',
                'CPO'
            );
        } elseif ($grootschalig['uitkomst'] === 'ja') {
            $u = $voegToe($u, 'DPIA', $grootschalig['redenen'][0] ?? 'Grootschalige verwerking.', 'CPO');
        } elseif ($grootschalig['uitkomst'] === 'twijfel') {
            $u = $voegToe($u, 'DPIA pretoets', $grootschalig['redenen'][0] ?? '', 'CPO');
        } elseif ($pgBijzonderNiveau !== 'nee') {
            $u = $voegToe(
                $u,
                'DPIA pretoets',
                'Er worden bijzondere persoonsgegevens verwerkt, maar niet grootschalig. De CPO '
                . 'bepaalt of een volledige DPIA nodig is.',
                'CPO'
            );
        }
    }

    if (($a['pg_bsn'] ?? '') === 'ja') {
        $u['aandachtspunten'][] = 'Er wordt een burgerservicenummer verwerkt. Leg de wettelijke '
            . 'grondslag voor het gebruik van het BSN expliciet vast.';
    }

    if ($ctx['heeftPg']) {
        $doorgifte = [];

        if (in_array($ctx['cloudLocatie'], ['modelcontract', 'buiten-eer'], true)) {
            $doorgifte[] = 'de clouddienst data buiten de EER verwerkt';
        }
        if (($a['pg_subverwerkers'] ?? '') === 'ja'
            && in_array($a['pg_subverwerkers_locatie'] ?? '', ['modelcontract', 'buiten-eer'], true)) {
            $doorgifte[] = 'de leverancier subverwerkers buiten de EER inschakelt';
        }

        if ($doorgifte !== []) {
            $u = $voegToe(
                $u,
                'DTIA',
                'Er is sprake van internationale doorgifte naar een land zonder '
                . 'adequaatheidsbesluit, omdat ' . implode(' en ', $doorgifte) . '.',
                'CTO of CISO'
            );
        }
    }

    if (($a['ai_nieuw_algoritme'] ?? '') === 'ja') {
        $u = $voegToe($u, 'IAMA', 'Het gaat om een nieuw algoritme of AI systeem.', 'CDO');
    }

    if (in_array($ctx['aiRisico']['niveau'], ['hoog', 'onaanvaardbaar'], true)) {
        $u = $voegToe(
            $u,
            'AI Impact Assessment (AIIA)',
            $ctx['aiRisico']['redenen'][0] ?? 'De inzet van AI valt in een zware risicoklasse.',
            'CDO'
        );
    }

    if ($ctx['abro']['waar']) {
        $u = $voegToe($u, 'ABRO Quickscan', $ctx['abro']['reden'], 'CISO');
    }

    return $u;
}

/** Stelt de lijst met wetten en regels en toe te passen kaders samen. */
function qs_wetgeving(array $u, array $a, string $rubricering, bool $heeftPg): array
{
    // Alleen noemen wat hier echt geldt; een vaste lijst zegt niets.
    $u['wetgeving'] = ['VIR: Voorschrift Informatiebeveiliging Rijksdienst'];

    if (in_array($rubricering, ['dep-vertrouwelijk', 'staatsgeheim'], true)) {
        $u['wetgeving'][] = 'VIR-BI: Voorschrift Informatiebeveiliging Rijksdienst Bijzondere '
            . 'Informatie, omdat er gerubriceerde informatie wordt verwerkt';
    }

    if ($heeftPg) {
        $u['wetgeving'][] = '(U)AVG: Algemene verordening gegevensbescherming en de Uitvoeringswet, '
            . 'omdat er persoonsgegevens worden verwerkt';
    }

    $u['wetgeving'][] = 'Archiefwet 2026: wat hier wordt opgemaakt of ontvangen is informatie '
        . 'waarvoor een bewaartermijn en een overbrengingsplicht gelden';

    $u['wetgeving'][] = 'Wet open overheid: de informatie kan opgevraagd worden en moet dus '
        . 'vindbaar en leverbaar zijn';

    if (($a['ai'] ?? 'uitgesloten') !== 'uitgesloten') {
        $u['wetgeving'][] = 'AI verordening, risicoklasse: '
            . mb_strtolower((string) ($u['ai_risico']['label'] ?? 'nog te bepalen'), 'UTF-8');
    }

    if (($a['gebruikers'] ?? '') === 'burgers') {
        $u['wetgeving'][] = 'Tijdelijk besluit digitale toegankelijkheid overheid: het systeem wordt '
            . 'door burgers gebruikt en moet voldoen aan de toegankelijkheidseisen (WCAG)';
    }

    $extra = trim((string) ($a['aanvullende_wetgeving'] ?? ''));
    if ($extra !== '') {
        $u['wetgeving'][] = 'Aanvullend opgegeven: ' . $extra;
    }

    if (($a['ai'] ?? 'uitgesloten') !== 'uitgesloten') {
        // Bij een zware risicoklasse staat de AIIA al als formele analyse in de lijst hierboven.
        if (!in_array($u['ai_risico']['niveau'] ?? '', ['hoog', 'onaanvaardbaar'], true)) {
            $u['kaders'][] = 'AI Impact Assessment (AIIA), om te bepalen of het idee voor AI haalbaar en '
                . 'wenselijk is.';
        }
        $u['kaders'][] = 'Inschrijving in het Algoritmeregister.';
        $u['kaders'][] = 'Controle op de eisen aan transparantie.';
        $u['kaders'][] = 'Toolbox Ethisch Verantwoorde Innovatie.';

        if (in_array($u['ai_risico']['niveau'] ?? '', ['hoog', 'onaanvaardbaar'], true)) {
            $u['kaders'][] = 'Een inschatting van het risico op basis van de beknopte opsomming van de AP en de '
                . 'Beslishulp van BZK.';
        }

        if (($u['ai_risico']['niveau'] ?? '') === 'beperkt') {
            $u['kaders'][] = 'Transparantieverplichting: maak kenbaar dat mensen met een AI systeem '
                . 'te maken hebben, en merk gegenereerde inhoud als zodanig.';
        }
    }

    if (qs_abro_van_toepassing($a)['waar']) {
        $u['kaders'][] = 'ABRO: Quickscan nationale veiligheid bij inkoop en aanbesteden.';
    }

    if ($heeftPg) {
        $u['kaders'][] = 'Verwerkingsregister bijwerken met deze verwerking.';
    }

    return $u;
}

/** Eisen aan de leverancier en maatregelen die we zelf moeten nemen. */
function qs_eisen_en_maatregelen(array $u, array $a, bool $heeftPg, bool $heeftCloud): array
{
    $inkoop = (string) ($a['inkoopvorm'] ?? 'geen');

    if ($inkoop !== 'geen') {
        array_unshift(
            $u['eisen_leverancier'],
            'De leverancier vult de Fit/Gap verklaring van de organisatie in, waaruit '
            . 'blijkt dat aan alle eisen wordt voldaan.'
        );
    }

    if ($heeftPg) {
        $u['eisen_leverancier'][] = 'De leverancier ondertekent de verwerkersovereenkomst voor de AVG van '
            . 'de organisatie.';
    }

    $cis = trim((string) ($a['cis_normen'] ?? ''));
    if ($cis !== '') {
        $u['eisen_leverancier'][] = 'De volgende CIS Benchmarks of STIGs zijn van toepassing: ' . $cis;
    }

    $externe = trim((string) ($a['externe_eisen'] ?? ''));
    if ($externe !== '') {
        $u['eisen_leverancier'][] = 'Externe eisen die zijn opgegeven: ' . $externe;
    }

    if ($heeftCloud && $u['cloud_categorie'] === 'hoog') {
        $u['eisen_leverancier'][] = 'Een rapport volgens SOC 2 Type II wordt geëist.';
    }

    if ($inkoop === 'raamcontract') {
        $u['aandachtspunten'][] = 'Dit is een raamcontract. De informatie die je hierboven hebt '
            . 'opgegeven bepaalt de grenzen van álle verwerkingen onder toekomstige nadere opdrachten. '
            . 'Wat hier niet in staat, mag daar straks niet.';
    }

    $u['maatregelen'][] = 'De basistraining informatiebeveiliging en privacy wordt opgelegd aan '
        . 'iedereen die met dit proces of systeem werkt.';
    $u['maatregelen'][] = sprintf(
        'De Recovery Time Objective is %s en de Recovery Point Objective is %s. Leg dit vast in de '
        . 'afspraken met de systeemeigenaar (SLA).',
        $u['rto'],
        $u['rpo']
    );

    if (($a['ai'] ?? 'uitgesloten') !== 'uitgesloten') {
        $u['maatregelen'][] = 'Aanvullende opleiding of training over verantwoord gebruik van AI is nodig.';

        if (in_array($u['ai_risico']['niveau'] ?? '', ['hoog', 'onaanvaardbaar'], true)) {
            $u['maatregelen'][] = 'De inzet van AI valt onder "' . $u['ai_risico']['label'] . '". De '
                . 'CDO moet hierbij betrokken worden.';
        }
        if (($u['ai_risico']['niveau'] ?? '') === 'onaanvaardbaar') {
            $u['blokkerend'][] = 'Deze toepassing van AI is verboden onder de AI verordening. '
                . ($u['ai_risico']['redenen'][0] ?? '')
                . ' Het voorstel kan in deze vorm niet doorgaan zonder herontwerp.';
        }
        if (($a['ai'] ?? '') === 'mogelijk') {
            $u['aandachtspunten'][] = 'Gebruik van AI in de toekomst wordt niet uitgesloten. We gaan er '
                . 'daarom van uit dat het ook daadwerkelijk gebeurt, en stellen de bijbehorende eisen.';
        }
    }

    if (($a['gebruikers'] ?? '') === 'burgers') {
        $u['maatregelen'][] = 'Burgers of andere externen gebruiken het systeem. Besteed expliciet '
            . 'aandacht aan toegankelijkheid, transparantie en de informatieplicht richting betrokkenen.';
    }

    return $u;
}

/** Wie er collegiaal advies geeft en wie de uitkomst vaststelt. */
function qs_wie_kijkt_mee(array $u, array $a, bool $heeftPg, bool $heeftCloud, bool $dreigingHoog, array $biv): array
{
    $u['collegiaal_advies'][] = 'CISO';

    if ($heeftPg) {
        $u['collegiaal_advies'][] = 'CPO';
    }
    if (($a['ai'] ?? 'uitgesloten') !== 'uitgesloten') {
        $u['collegiaal_advies'][] = 'CDO';
    }
    if ($heeftCloud || ($a['inkoopvorm'] ?? 'geen') !== 'geen') {
        $u['collegiaal_advies'][] = 'CTO';
    }

    $cioRedenen = [];
    if (($a['bedrijfskritisch'] ?? '') === 'ja') {
        $cioRedenen[] = 'het een bedrijfskritisch systeem is';
    }
    if (in_array('zh', $biv, true) || $dreigingHoog) {
        $cioRedenen[] = 'er sprake is van een zeer hoog risico';
    }
    if (qs_abro_van_toepassing($a)['waar']) {
        $cioRedenen[] = 'de ABRO van toepassing is';
    }
    if (($a['politiek_sensitief'] ?? '') === 'ja') {
        $cioRedenen[] = 'het onderwerp politiek gevoelig is';
    }

    if ($cioRedenen !== []) {
        $u['collegiaal_advies'][] = 'CIO';
        $u['cio_reden'] = count($cioRedenen) === 1
            ? $cioRedenen[0]
            : implode(', ', array_slice($cioRedenen, 0, -1)) . ' en ' . end($cioRedenen);
        $u['vaststelling'][] = 'CIO';
    } else {
        $u['vaststelling'][] = $heeftPg ? 'CISO of CPO' : 'CISO';
    }

    $u['collegiaal_advies'] = array_values(array_unique($u['collegiaal_advies']));
    $u['collegiaal_advies_rollen'] = $u['collegiaal_advies'];

    return $u;
}

function qs_risiconiveau(string $B, string $I, string $V, bool $dreigingHoog, int $bbn): string
{
    if ($dreigingHoog || in_array('zh', [$B, $I, $V], true)) {
        return 'zeer hoog';
    }
    if ($bbn >= 3 || in_array('h', [$B, $I, $V], true)) {
        return 'hoog';
    }
    if ($bbn === 2) {
        return 'midden';
    }

    return 'laag';
}

/**
 * Het gevolgde pad door het processchema, zodat je in beeld kunt laten zien
 * welke vragen tot welke uitkomst hebben geleid.
 *
 * @return list<array{vraag:string,antwoord:string,gevolg:string,geraakt:bool}>
 */
function qs_beslisboom_pad(array $a, array $u, array $ctx): array
{
    $heeftAnalyse = static function (array $u, string $naam): bool {
        foreach ($u['analyses'] as $analyse) {
            if ($analyse['naam'] === $naam) {
                return true;
            }
        }

        return false;
    };

    $stappen = [];

    $stappen[] = [
        'vraag' => 'Gelden er nog andere wetten of regels?',
        'antwoord' => trim((string) ($a['aanvullende_wetgeving'] ?? '')) !== '' ? 'Ja' : 'Nee',
        'gevolg' => trim((string) ($a['aanvullende_wetgeving'] ?? '')) !== ''
            ? 'Opnemen in de uitwerking'
            : 'Alleen het standaardkader',
        'geraakt' => trim((string) ($a['aanvullende_wetgeving'] ?? '')) !== '',
    ];

    $stappen[] = [
        'vraag' => 'Zijn er externe eisen van toepassing?',
        'antwoord' => trim((string) ($a['externe_eisen'] ?? '')) !== '' ? 'Ja' : 'Nee',
        'gevolg' => trim((string) ($a['externe_eisen'] ?? '')) !== '' ? 'Opnemen in de uitwerking' : 'Geen gevolg',
        'geraakt' => trim((string) ($a['externe_eisen'] ?? '')) !== '',
    ];

    $stappen[] = [
        'vraag' => 'Zijn er CIS Benchmarks of STIGs van toepassing?',
        'antwoord' => trim((string) ($a['cis_normen'] ?? '')) !== '' ? 'Ja' : 'Nee',
        'gevolg' => trim((string) ($a['cis_normen'] ?? '')) !== '' ? 'Opnemen in de uitwerking' : 'Geen gevolg',
        'geraakt' => trim((string) ($a['cis_normen'] ?? '')) !== '',
    ];

    $stappen[] = [
        'vraag' => 'Worden er persoonsgegevens verwerkt?',
        'antwoord' => $ctx['heeftPg'] ? 'Ja' : 'Nee',
        'gevolg' => $ctx['heeftPg']
            ? ($heeftAnalyse($u, 'DPIA') ? 'DPIA noodzakelijk'
                : ($heeftAnalyse($u, 'DPIA pretoets') ? 'DPIA pretoets door de CPO' : 'Geen DPIA nodig'))
            : 'Geen DPIA nodig',
        'geraakt' => $ctx['heeftPg'],
    ];

    $stappen[] = [
        'vraag' => 'Is er sprake van cloudgebruik?',
        'antwoord' => qs_antwoord_tekst('cloud', $a),
        'gevolg' => $ctx['heeftCloud']
            ? 'Cloudeisen opleggen, categorie ' . qs_cloud_label($u['cloud_categorie'])
            : 'Geen cloudeisen',
        'geraakt' => $ctx['heeftCloud'],
    ];

    if ($ctx['heeftCloud']) {
        $stappen[] = [
            'vraag' => 'Gaat data buiten de EER, naar een land zonder adequaatheidsbesluit?',
            'antwoord' => qs_antwoord_tekst('cloud_locatie', $a),
            'gevolg' => $heeftAnalyse($u, 'DTIA') ? 'DTIA noodzakelijk' : 'Geen DTIA nodig',
            'geraakt' => $heeftAnalyse($u, 'DTIA'),
        ];

        $stappen[] = [
            'vraag' => 'Is er sprake van materieel cloudgebruik?',
            'antwoord' => ($u['materieel_cloudgebruik']['waar'] ? 'Ja' : 'Nee') . ' (afgeleid)',
            'gevolg' => $u['materieel_cloudgebruik']['waar'] ? 'Volledige Risicoanalyse' : 'Geen gevolg',
            'geraakt' => $u['materieel_cloudgebruik']['waar'],
        ];
    }

    $stappen[] = [
        'vraag' => 'Is er een hoog dreigingsprofiel?',
        'antwoord' => ($ctx['dreigingHoog'] ? 'Ja' : 'Nee') . ' (afgeleid uit de kenmerken van het systeem)',
        'gevolg' => $ctx['dreigingHoog'] ? 'Volledige Risicoanalyse en cloudcategorie Zeer hoog' : 'Geen gevolg',
        'geraakt' => $ctx['dreigingHoog'],
    ];

    $stappen[] = [
        'vraag' => 'Een (kritisch) strategisch proces op een vitaal systeem?',
        'antwoord' => $ctx['strategischVitaal'] ? 'Ja' : 'Nee',
        'gevolg' => $ctx['strategischVitaal']
            ? 'Volledige Risicoanalyse en een niveau hoger in het BBN'
            : 'Geen gevolg',
        'geraakt' => $ctx['strategischVitaal'],
    ];

    if ($ctx['heeftPg']) {
        $stappen[] = [
            'vraag' => 'Is de verwerking grootschalig?',
            'antwoord' => ucfirst((string) $u['grootschalig']['uitkomst']) . ' (afgeleid uit '
                . 'aantal, omvang, duur en bereik)',
            'gevolg' => $heeftAnalyse($u, 'DPIA')
                ? 'DPIA noodzakelijk'
                : ($heeftAnalyse($u, 'DPIA pretoets') ? 'DPIA pretoets door de CPO' : 'Geen gevolg'),
            'geraakt' => $u['grootschalig']['uitkomst'] !== 'nee',
        ];

        $stappen[] = [
            'vraag' => 'Is er een geldige grondslag?',
            'antwoord' => qs_antwoord_tekst('pg_grondslag', $a),
            'gevolg' => in_array($a['pg_grondslag'] ?? '', ['gerechtvaardigd_belang', 'onbekend'], true)
                ? 'Blokkerend: de grondslag moet eerst kloppen'
                : 'Geen gevolg',
            'geraakt' => in_array($a['pg_grondslag'] ?? '', ['gerechtvaardigd_belang', 'onbekend'], true),
        ];
    }

    $stappen[] = [
        'vraag' => 'Is de bewaartermijn bepaald?',
        'antwoord' => qs_antwoord_tekst('bewaartermijn', $a),
        'gevolg' => ($a['bewaartermijn'] ?? '') === 'onbepaald'
            ? 'Blokkerend: zonder termijn voldoe je niet aan de AVG en de Archiefwet'
            : 'Termijn vastleggen in het systeem',
        'geraakt' => ($a['bewaartermijn'] ?? '') === 'onbepaald',
    ];

    $stappen[] = [
        'vraag' => 'Gaat het om een nieuw algoritme of AI systeem?',
        'antwoord' => ($a['ai'] ?? 'uitgesloten') === 'uitgesloten'
            ? 'AI is uitgesloten'
            : qs_antwoord_tekst('ai_nieuw_algoritme', $a),
        'gevolg' => $heeftAnalyse($u, 'IAMA')
            ? 'IAMA noodzakelijk · ' . $u['ai_risico']['label']
            : ($u['ai_risico']['niveau'] === 'geen' ? 'Geen gevolg' : $u['ai_risico']['label']),
        'geraakt' => $heeftAnalyse($u, 'IAMA')
            || in_array($u['ai_risico']['niveau'], ['hoog', 'onaanvaardbaar', 'beperkt'], true),
    ];

    $stappen[] = [
        'vraag' => 'Is het een maatschappelijk vitaal proces?',
        'antwoord' => ($u['abro']['waar'] ? 'Ja' : 'Nee') . ' (afgeleid uit de procesclassificatie)',
        'gevolg' => $u['abro']['waar'] ? 'ABRO quickscan noodzakelijk' : 'Geen gevolg',
        'geraakt' => $u['abro']['waar'],
    ];

    $stappen[] = [
        'vraag' => 'Wat is het beveiligingsniveau van het hele systeem?',
        'antwoord' => sprintf(
            'B %s · I %s · V %s',
            cm_niveau_label($u['biv']['b']),
            cm_niveau_label($u['biv']['i']),
            cm_niveau_label($u['biv']['v'])
        ),
        'gevolg' => $u['ib_niveau'],
        'geraakt' => true,
    ];

    return $stappen;
}

/**
 * Het uitzonderingsgeval 'géén te beschermen informatie' uit de quickscan.
 * De scan stopt dan vroeg: er is niets te classificeren, dus er volgen ook
 * geen eisen. Wel moet de beoordelaar de motivatie toetsen.
 *
 * @param array<string,mixed> $u
 * @param array<string,mixed> $a
 * @return array<string,mixed>
 */
function qs_uitzondering_geen_bescherming(array $u, array $a): array
{
    $u['uitzondering'] = true;
    $u['biv'] = ['b' => 'zl', 'i' => 'zl', 'v' => 'zl'];
    $u['biv_afgeleid'] = ['i' => 'zl', 'v' => 'zl'];
    $u['biv_redenen'] = ['i' => [], 'v' => []];
    $u['bbn'] = 1;
    $u['ib_niveau'] = 'IB niveau 1 (vergelijkbaar met BBN1)';
    $u['rto'] = 'groter dan een week (best effort)';
    $u['rpo'] = 'niet van toepassing';
    $u['risiconiveau'] = 'laag';

    $u['wetgeving'] = ['VIR: Voorschrift Informatiebeveiliging Rijksdienst'];
    $u['kaders'] = [];

    $u['maatregelen'] = [
        'Er gelden geen aanvullende beveiligingseisen zolang de uitzondering standhoudt.',
        'Komt er tóch informatie in die bescherming nodig heeft (persoonsgegevens, geheime '
            . 'informatie, of een proces dat niet meer zonder kan), vul de quickscan dan opnieuw in.',
    ];

    $u['aandachtspunten'] = [
        'Dit is het uitzonderingsgeval "géén te beschermen informatie". De beoordelaar toetst de '
            . 'opgegeven motivatie; houdt die geen stand, dan volgt alsnog de volledige scan.',
    ];

    $u['collegiaal_advies'] = ['CISO'];
    $u['collegiaal_advies_rollen'] = ['CISO'];
    $u['vaststelling'] = ['CISO'];

    $u['beslisboom'] = [[
        'vraag' => 'Wordt er informatie verwerkt die bescherming nodig heeft?',
        'antwoord' => 'Nee',
        'gevolg' => 'Uitzonderingsgeval: de quickscan stopt hier',
        'geraakt' => true,
    ]];

    return $u;
}

/**
 * Controles op de privacykant: grondslag, subsidiariteit, proportionaliteit en
 * de rechten van betrokkenen.
 *
 * @param array<string,mixed> $u
 * @param array<string,mixed> $a
 * @return array<string,mixed>
 */
function qs_privacy_checks(array $u, array $a, bool $heeftPg): array
{
    if (!$heeftPg) {
        return $u;
    }

    $grondslag = (string) ($a['pg_grondslag'] ?? '');

    if ($grondslag === 'gerechtvaardigd_belang') {
        $u['blokkerend'][] = 'Als grondslag is gerechtvaardigd belang gekozen. Een overheidsorgaan '
            . 'kan zich daar voor de uitvoering van zijn publieke taken niet op beroepen; de AVG '
            . 'sluit dat uitdrukkelijk uit. Kies een andere grondslag (meestal een wettelijke '
            . 'verplichting of een taak van algemeen belang), of onderbouw waarom deze verwerking '
            . 'aantoonbaar buiten de publieke taak valt.';
    }

    if ($grondslag === 'toestemming') {
        $u['aandachtspunten'][] = 'De grondslag is toestemming. Die moet vrij te weigeren en net zo '
            . 'makkelijk in te trekken zijn als te geven. Tussen overheid en burger is dat door de '
            . 'ongelijke verhouding lastig hard te maken: ga na of een wettelijke taak hier niet de '
            . 'juistere grondslag is, en zorg dat de dienst ook werkt als iemand weigert.';
    }

    if ($grondslag === 'onbekend') {
        $u['blokkerend'][] = 'De grondslag voor de verwerking is nog niet bepaald. Zonder grondslag '
            . 'mag er niet verwerkt worden; dit moet vaststaan voordat het project start. De CPO '
            . 'helpt bij het bepalen ervan.';
    }

    if (($a['pg_subsidiariteit'] ?? '') === 'kan_minder') {
        $u['aandachtspunten'][] = 'Het doel kan volgens de invuller ook met minder of anoniemere '
            . 'gegevens worden bereikt. Leg vast waarom daar niet voor is gekozen, of pas het '
            . 'ontwerp aan. Dit is een kernvraag in de DPIA.';
    }

    if (($a['pg_proportionaliteit'] ?? '') === 'twijfel') {
        $u['aandachtspunten'][] = 'Er is twijfel of de inbreuk op de privacy in verhouding staat tot '
            . 'het doel. Werk die afweging expliciet uit en leg hem voor aan de CPO.';
    }

    $rechten = (string) ($a['pg_rechten'] ?? '');
    if (in_array($rechten, ['deels', 'nee'], true)) {
        $u['eisen_leverancier'][] = 'Het systeem ondersteunt inzage, correctie en verwijdering van '
            . 'persoonsgegevens binnen de wettelijke termijn van een maand. Kan dat alleen handmatig, '
            . 'dan wordt de procedure daarvoor vastgelegd en belegd.';
    }

    if (($a['pg_subverwerkers'] ?? '') === 'ja') {
        $u['eisen_leverancier'][] = 'De leverancier levert een actuele lijst van subverwerkers, meldt '
            . 'wijzigingen vooraf, en legt dezelfde verplichtingen contractueel aan hen op.';
    }

    return $u;
}

/**
 * Bewaren, vernietigen en archiveren.
 *
 * Levert naast losse eisen ook een stappenlijst voor het inrichten van de
 * archiefprocessen. Wat een overheidsorgaan in zijn taak opmaakt of ontvangt
 * is archiefbescheiden, ongeacht de vorm of de plek waar het staat.
 *
 * @param array<string,mixed> $u
 * @param array<string,mixed> $a
 * @return array<string,mixed>
 */
function qs_archiefwet(array $u, array $a, bool $heeftPg, bool $heeftCloud): array
{
    $bewaartermijn = (string) ($a['bewaartermijn'] ?? '');
    $vernietiging = (string) ($a['archief_vernietiging'] ?? '');
    $export = (string) ($a['archief_export'] ?? '');
    $extern = $heeftCloud || ($a['inkoopvorm'] ?? 'geen') !== 'geen';

    $stappen = [];

    $stappen[] = [
        'titel' => 'Wijs de zorgdrager en de beheerder aan',
        'tekst' => 'Het overheidsorgaan blijft onder de Archiefwet 2026 verantwoordelijk voor de '
            . 'informatie, ook als een leverancier '
            . 'het systeem draait. Leg vast wie binnen de organisatie die rol heeft, en leg in het '
            . 'contract vast dat de leverancier het beheer uitvoert namens de zorgdrager en zelf geen '
            . 'zeggenschap over bewaren of vernietigen krijgt.',
    ];

    $stappen[] = [
        'titel' => 'Zoek de categorie in de selectielijst op',
        'tekst' => 'De vastgestelde selectielijst bepaalt per categorie informatie of die blijvend '
            . 'bewaard wordt of na een termijn vernietigd. Bepaal onder welke categorie dit proces '
            . 'valt voordat het systeem wordt ingericht: zonder die categorie kun je geen '
            . 'bewaartermijn instellen en niet aantonen dat je aan de Archiefwet voldoet.',
    ];

    $stappen[] = [
        'titel' => 'Zet de bewaartermijn in het systeem, niet in een document',
        'tekst' => 'Een termijn die alleen in beleid staat, wordt in de praktijk niet nageleefd. Leg '
            . 'per informatiesoort vast wanneer de termijn begint te lopen, en laat het systeem daar '
            . 'zelf op sturen.',
    ];

    $stappen[] = [
        'titel' => 'Richt de metagegevens in',
        'tekst' => 'Leg bij elk stuk informatie vast wat het is, wie het heeft gemaakt of ontvangen, '
            . 'wanneer, binnen welk proces, en welke bewaartermijn en waardering erbij horen. Binnen '
            . 'de Rijksoverheid is MDTO het metagegevensschema daarvoor. Zonder metagegevens is '
            . 'informatie later niet terug te vinden, niet te waarderen en niet over te brengen.',
    ];

    $stappen[] = [
        'titel' => 'Kies bestandsformaten die je over tien jaar nog kunt openen',
        'tekst' => 'Gebruik open, gedocumenteerde formaten zoals PDF/A, XML, CSV of JSON in plaats van '
            . 'formaten die alleen de leverancier kan lezen. Spreek dit af voordat het systeem in '
            . 'gebruik gaat; achteraf converteren kost meer en levert verlies op.',
    ];

    $stappen[] = [
        'titel' => 'Zorg dat vernietigen ook echt gebeurt',
        'tekst' => 'Na afloop van de bewaartermijn moet informatie daadwerkelijk worden vernietigd, '
            . 'ook uit reservekopieën binnen een redelijke termijn. Maak van elke vernietiging een '
            . 'verklaring op waarin staat wat is vernietigd, op grond van welke categorie en wanneer.',
    ];

    $stappen[] = [
        'titel' => 'Reken op de overbrengingstermijn van tien jaar',
        'tekst' => 'De Archiefwet 2026 brengt de overbrengingstermijn terug van twintig naar tien '
            . 'jaar. Informatie met de waardering "blijvend bewaren" gaat dus twee keer zo snel naar '
            . 'het Nationaal Archief als onder de oude wet. Voor een systeem dat nu wordt ingekocht '
            . 'betekent dat: de exportfunctie is geen probleem voor later meer, maar iets dat binnen de '
            . 'looptijd van dit contract werkend moet zijn. Zorg dat het systeem een export mét '
            . 'metagegevens kan leveren in een formaat dat het Nationaal Archief accepteert, en test '
            . 'dat een keer voordat je het nodig hebt.',
    ];

    $stappen[] = [
        'titel' => 'Bepaal nu al wat straks openbaar wordt',
        'tekst' => 'Bij overbrenging wordt informatie in beginsel openbaar, tenzij er een grond is om '
            . 'de openbaarheid te beperken (bijvoorbeeld de bescherming van persoonsgegevens of van '
            . 'de staat). Met een termijn van tien jaar is dat geen theoretische vraag meer. Leg per '
            . 'informatiesoort vast of er een beperking nodig is en op welke grond, en zorg dat het '
            . 'systeem die aantekening als metagegeven kan vasthouden.',
    ];

    if ($extern) {
        $stappen[] = [
            'titel' => 'Regel de exit voordat je begint',
            'tekst' => 'Neem als contracteis op dat bij beëindiging alle informatie in een bruikbaar, '
                . 'leveranciersonafhankelijk formaat wordt teruggeleverd, inclusief metagegevens, en '
                . 'dat de leverancier daarna aantoonbaar al zijn kopieën vernietigt. Zonder deze '
                . 'afspraak ben je bij een contractwissel je archief kwijt of zit je eraan vast.',
        ];
    }

    $stappen[] = [
        'titel' => 'Houd rekening met de Wet open overheid',
        'tekst' => 'Informatie die onder de Woo opvraagbaar is, moet binnen de wettelijke termijn '
            . 'gevonden en geleverd kunnen worden. Dat stelt eisen aan doorzoekbaarheid en aan de '
            . 'mogelijkheid om een selectie te exporteren, ook uit chats, samenwerkingsomgevingen en '
            . 'mail.',
    ];

    $u['archief'] = $stappen;

    // ---- concrete gevolgen van de gegeven antwoorden ----------------------

    if ($bewaartermijn === 'onbepaald') {
        $u['blokkerend'][] = 'Er is nog geen bewaartermijn bepaald. Zonder termijn kun je niet '
            . 'voldoen aan de AVG (niet langer bewaren dan nodig) en niet aan de Archiefwet 2026 '
            . '(tijdig vernietigen of overbrengen). Bepaal eerst onder welke categorie van de '
            . 'selectielijst dit proces valt.';
    } elseif ($bewaartermijn === 'eigen') {
        $u['aandachtspunten'][] = 'De bewaartermijn is zelf bepaald in plaats van uit de selectielijst '
            . 'overgenomen. Controleer of er voor dit proces niet toch een categorie in de '
            . 'selectielijst bestaat; die gaat voor.';
    }

    if ($vernietiging === 'nee') {
        $u['blokkerend'][] = 'Het systeem kan informatie na afloop van de bewaartermijn niet '
            . 'vernietigen. Daarmee kan de organisatie niet aan de bewaarplicht voldoen. Dit moet '
            . 'opgelost zijn voordat het systeem in gebruik wordt genomen.';
        $u['eisen_leverancier'][] = 'Het systeem ondersteunt het vernietigen van informatie na afloop '
            . 'van de bewaartermijn, inclusief uit reservekopieën binnen een redelijke termijn.';
    } elseif ($vernietiging === 'handmatig') {
        $u['aandachtspunten'][] = 'Vernietiging kan alleen handmatig. Leg de procedure vast, beleg wie '
            . 'hem uitvoert en met welke frequentie, en zorg dat er een vernietigingsverklaring wordt '
            . 'opgemaakt. Handmatige stappen die niemand toegewezen krijgt, gebeuren niet.';
    }

    if ($export === 'nee') {
        $u['eisen_leverancier'][] = 'De leverancier levert een export van alle informatie inclusief '
            . 'metagegevens, in een open en gedocumenteerd formaat, op elk moment tijdens en bij '
            . 'beëindiging van het contract.';
        $u['aandachtspunten'][] = 'De informatie is nu niet zonder de leverancier op te halen. Dat '
            . 'blokkeert overbrenging naar het Nationaal Archief, maakt verzoeken om informatie (Woo) moeilijker, en maakt '
            . 'een contractwissel riskant.';
    } elseif ($export === 'eigen') {
        $u['eisen_leverancier'][] = 'De export is beschikbaar in een open, gedocumenteerd formaat, '
            . 'niet alleen in een eigen formaat van de leverancier.';
    }

    if ($heeftPg && $bewaartermijn !== 'onbepaald') {
        $u['maatregelen'][] = 'Neem de bewaartermijn ook op in het verwerkingsregister, zodat de '
            . 'AVG en het archief dezelfde termijn hanteren.';
    }

    return $u;
}

/**
 * Legt per weging vast: welk niveau het is, wat dat niveau betekent, waarom het
 * zo is en wie dat heeft bepaald. Zonder dit staat er in het advies alleen
 * "Hoog", en is niet na te gaan of dat juist is ingeschaald.
 *
 * @param array<string,mixed> $a
 * @param array<string,mixed> $u
 * @return list<array<string,string>>
 */
function qs_wegingen(array $a, array $u): array
{
    $rol = ($a['rol'] ?? 'behoeftesteller') === 'beoordelaar' ? 'de beoordelaar' : 'de behoeftesteller';

    $motivatie = static function (string $veld, string $voor) use ($a): array {
        $eigen = trim((string) ($a[$veld] ?? ''));

        if ($eigen !== '') {
            return ['tekst' => $eigen, 'eigen' => true];
        }

        return ['tekst' => qs_motivatie_voorstel($voor, $a), 'eigen' => false];
    };

    $wegingen = [];

    $procesToelichting = qs_toelichting_proces();
    $wegingen[] = [
        'label' => 'Classificatie van het proces',
        'niveau' => qs_antwoord_tekst('proces_classificatie', $a),
        'omschrijving' => $procesToelichting[(string) ($a['proces_classificatie'] ?? '')] ?? '',
        'motivatie' => trim((string) ($a['proces_motivatie'] ?? '')),
        'herkomst' => 'Bepaald door ' . $rol . '.',
    ];

    $systeemToelichting = qs_toelichting_systeem();
    $wegingen[] = [
        'label' => 'Classificatie van het systeem',
        'niveau' => qs_antwoord_tekst('systeem_classificatie', $a),
        'omschrijving' => $systeemToelichting[(string) ($a['systeem_classificatie'] ?? '')] ?? '',
        'motivatie' => trim((string) ($a['systeem_motivatie'] ?? '')),
        'herkomst' => 'Bepaald door ' . $rol . '.',
    ];

    $voorstelB = qs_voorstel_beschikbaarheid($a);
    $motB = $motivatie('beschikbaarheid_motivatie', 'beschikbaarheid');
    $wegingen[] = [
        'label' => 'Beschikbaarheid',
        'niveau' => cm_niveau_label($u['biv']['b']),
        'omschrijving' => qs_toelichting_beschikbaarheid()[$u['biv']['b']] ?? '',
        'motivatie' => $motB['tekst'],
        'herkomst' => $voorstelB === $u['biv']['b']
            ? 'Voorstel van de tool, overgenomen door ' . $rol . '.'
            : sprintf(
                'De tool stelde %s voor; %s koos %s.',
                cm_niveau_label($voorstelB),
                $rol,
                cm_niveau_label($u['biv']['b'])
            ),
    ];

    foreach (['i' => 'integriteit', 'v' => 'vertrouwelijkheid'] as $letter => $naam) {
        $mot = $motivatie($naam . '_motivatie', $naam);
        $afgeleid = $u['biv_afgeleid'][$letter] ?? $u['biv'][$letter];
        $toelichting = $naam === 'integriteit'
            ? qs_toelichting_integriteit()
            : qs_toelichting_vertrouwelijkheid();

        $wegingen[] = [
            'label' => ucfirst($naam),
            'niveau' => cm_niveau_label($u['biv'][$letter]),
            'omschrijving' => $toelichting[$u['biv'][$letter]] ?? '',
            'motivatie' => $mot['tekst'],
            'herkomst' => isset($u['biv_verhoogd'][$letter])
                ? sprintf(
                    'De tool leidde %s af; %s stelde bij naar %s.',
                    cm_niveau_label($afgeleid),
                    $rol,
                    cm_niveau_label($u['biv'][$letter])
                )
                : 'Afgeleid door de tool en overgenomen door ' . $rol . '.',
        ];
    }

    $voorstelRpo = qs_voorstel_rpo($a);
    $motRpo = $motivatie('rpo_motivatie', 'rpo');
    $wegingen[] = [
        'label' => 'Recovery Point Objective',
        'niveau' => $u['rpo'],
        'omschrijving' => 'Hoeveel werk er bij een calamiteit verloren mag gaan. Dit bepaalt hoe '
            . 'vaak er een reservekopie gemaakt moet worden.',
        'motivatie' => $motRpo['tekst'],
        'herkomst' => $voorstelRpo === (string) ($a['rpo'] ?? '')
            ? 'Voorstel van de tool, overgenomen door ' . $rol . '.'
            : 'Afwijkend van het voorstel van de tool, gekozen door ' . $rol . '.',
    ];

    $motDreiging = $motivatie('dreiging_motivatie', 'dreiging');
    $wegingen[] = [
        'label' => 'Dreigingsprofiel',
        'niveau' => ucfirst((string) $u['dreiging']['niveau']),
        'omschrijving' => $u['dreiging']['niveau'] === 'hoog'
            ? 'Bescherming is nodig tegen inlichtingendiensten, terreurgroepen of georganiseerde '
                . 'criminaliteit. Dat sluit publieke cloud uit en vraagt om een Volledige Risicoanalyse.'
            : 'Deze groepen vormen geen bedreiging voor dit proces of systeem.',
        'motivatie' => $motDreiging['tekst'],
        'herkomst' => ($a['dreiging_verhoging'] ?? '') === 'hoog'
            ? 'Handmatig op hoog gezet door ' . $rol . '.'
            : 'Afgeleid uit de aangekruiste kenmerken van het systeem.',
    ];

    if ($u['cloud_categorie'] !== null) {
        $toelichting = qs_toelichting_cloud()[$u['cloud_categorie']] ?? null;
        $wegingen[] = [
            'label' => 'Cloudcategorie',
            'niveau' => qs_cloud_label($u['cloud_categorie']),
            'omschrijving' => $toelichting === null
                ? ''
                : 'Hieronder valt: ' . $toelichting['data'] . ' ' . $toelichting['eis'],
            'motivatie' => 'Volgt uit het soort informatie, de eisen aan integriteit en '
                . 'vertrouwelijkheid, en het dreigingsprofiel.',
            'herkomst' => 'Afgeleid door de tool.',
        ];
    }

    return $wegingen;
}
