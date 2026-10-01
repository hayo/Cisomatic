<?php

/**
 * Afleidingen: wat de tool uit de antwoorden haalt.
 *
 * Het IAMA is een gesprek, dus de tool rekent weinig. Hij vat samen wat de
 * AI verordening betekent, zet de actiepunten op een rij die uit de antwoorden
 * volgen, en noemt wat opvalt. De risicogroep zelf zoekt de AI scan uit.
 */

declare(strict_types=1);

/** De sessies, van de eerste tot en met de laatste. */
const IA_SESSIES = ['wat' => 1, 'hoe' => 2, 'alles' => 3];

/** De kernfuncties die het IAMA in elk geval in het team wil. */
const IA_KERNTEAM = ['projectleider' => 'een projectleider', 'data_scientist' => 'een data scientist', 'jurist' => 'een jurist'];

/**
 * Wat de AI verordening betekent, uit de antwoorden bij 1.3 en 4.2.1. Het
 * niveau is dat van de motor, zodat de samenvatting het kleurt.
 *
 * @return array{klasse:string,label:string,niveau:string}
 */
function ia_ai_klasse(array $a): array
{
    return match (true) {
        !in_array($a['ia_ai_systeem'] ?? '', ['ja', 'twijfel'], true)
            => ['klasse' => 'geen', 'label' => 'Geen AI systeem', 'niveau' => ''],
        ($a['ia_verboden'] ?? '') === 'ja' => ['klasse' => 'verboden', 'label' => 'Verboden', 'niveau' => 'zh'],
        ($a['ia_hoog_risico'] ?? '') === 'ja' => ['klasse' => 'hoog', 'label' => 'Hoog risico', 'niveau' => 'h'],
        ($a['ia_hoog_risico'] ?? '') === 'nee' => ['klasse' => 'laag', 'label' => 'Geen hoog risico', 'niveau' => ''],
        default => ['klasse' => 'open', 'label' => 'Nog niet bekend. De AI scan zoekt het uit.', 'niveau' => 'm'],
    };
}

/** De namen bij de grondrechten in 4.3: wat bij 4.1.2 als aangetast staat. @return list<string> */
function ia_suggesties(array $a): array
{
    return cm_regels((string) ($a['ia_grondrechten_negatief'] ?? ''));
}

/** De zwaarste ernst uit 4.3, of leeg. */
function ia_zwaarste_ernst(array $a): string
{
    $ernst = array_column((array) ($a['ia_inbreuken'] ?? []), 'ernst');

    foreach (['ernstig', 'medium', 'licht'] as $niveau) {
        if (in_array($niveau, $ernst, true)) {
            return $niveau;
        }
    }

    return '';
}

/** Bij welke vraag het team stopte, of leeg. */
function ia_gestopt(array $a): string
{
    return match (true) {
        ($a['ia_redelijk_effectief'] ?? '') === 'nee' => '4.4.2',
        ($a['ia_redelijk_noodzaak'] ?? '') === 'nee' => '4.5.4',
        default => '',
    };
}

/**
 * Wat er nog moet gebeuren volgens de antwoorden, als rijen zoals die van het
 * team. Wie en wanneer vult het team in.
 *
 * @return list<array{actie:string,wie:string,wanneer:string}>
 */
function ia_actiepunten(array $a): array
{
    $acties = [
        ($a['ia_ai_systeem'] ?? '') === 'twijfel' || ($a['ia_verboden'] ?? '') === 'onbekend'
            ? 'Doe de AI scan in deze tool. Die zoekt uit of de AI verordening geldt, en wat die van jullie vraagt.'
            : '',
        ($a['ia_grondslag_taak'] ?? '') === 'onduidelijk'
            ? 'Stel vast op welke wettelijke grondslag het algoritme rust.' : '',
        ($a['ia_exit'] ?? '') === 'nee'
            ? 'Maak een exitstrategie: een plan om met het algoritme te stoppen.' : '',
        ($a['ia_hoog_risico'] ?? '') === 'ja'
            ? 'Toets of het AI systeem voldoet aan de eisen die de AI verordening stelt bij een hoog risico.' : '',
        ($a['ia_hoog_risico'] ?? '') === 'ja'
            ? 'Meld de uitkomst van deze beoordeling bij de markttoezichthouder, zoals artikel 27 van de '
                . 'AI verordening vraagt. Dat hoeft niet als het systeem alleen kritieke infrastructuur beheert.' : '',
        ($a['ia_dpia'] ?? '') === 'nodig'
            ? 'Maak een DPIA, en gebruik daarbij de antwoorden uit deze IAMA.' : '',
        in_array($a['ia_gelijke_behandeling'] ?? '', ['ja', 'misschien'], true)
            ? 'Toets of het onderscheid te rechtvaardigen is volgens de wetten voor gelijke behandeling.' : '',
        ($a['ia_andere_wetgeving'] ?? '') === 'ja'
            ? 'Toets of het algoritme past binnen de andere wetten die gelden (vraag 4.2.4).' : '',
    ];

    return array_map(
        static fn (string $actie): array => ['actie' => $actie, 'wie' => '', 'wanneer' => ''],
        array_values(array_filter($acties))
    );
}

/** @return list<string> */
function ia_aandachtspunten(array $a, int $sessie): array
{
    $functies = array_column((array) ($a['ia_team'] ?? []), 'functie');
    $ontbreekt = array_diff_key(IA_KERNTEAM, array_flip($functies));
    $aantal = count((array) ($a['ia_team'] ?? []));
    $punten = [];

    if ($ontbreekt !== []) {
        $punten[] = 'Het IAMA vraagt in elk geval om een projectleider, een data scientist en een jurist. Nog niet in '
            . 'het team: ' . cm_opsomming($ontbreekt) . '.';
    }
    if ($aantal < 4 || $aantal > 7) {
        $punten[] = sprintf('Het team telt %d %s. Het IAMA raadt vier tot zeven mensen aan.', $aantal,
            $aantal === 1 ? 'persoon' : 'mensen');
    }
    if (in_array($a['ia_reden'] ?? '', ['impactvol', 'hoog_risico'], true) || ia_ai_klasse($a)['klasse'] === 'hoog') {
        $punten[] = 'Ga na of het algoritme in het Algoritmeregister hoort.';
    }
    if (in_array('generatief', (array) ($a['ia_type'] ?? []), true)) {
        $punten[] = 'Bij generatieve AI zijn de trainingsdata en de bias van het model vaak onbekend. Het doel is ook '
            . 'moeilijker af te bakenen. De toelichting bij het IAMA helpt daarbij.';
    }

    if ($sessie >= 2) {
        if (($a['ia_menselijke_rol'] ?? '') === 'algoritme') {
            $punten[] = 'Het algoritme neemt besluiten zonder dat een medewerker meekijkt. Gaat het om '
                . 'persoonsgegevens, dan stelt artikel 22 van de AVG daar eisen aan.';
        }
        if (($a['ia_afwijken'] ?? '') === 'nee') {
            $punten[] = 'Medewerkers kunnen niet afwijken van de uitkomst. Dan stelt de menselijke tussenkomst '
                . 'weinig voor.';
        }
    }

    if ($sessie === 3 && ($a['ia_doeltreffend'] ?? '') === 'onduidelijk') {
        $punten[] = 'Het is onduidelijk of het algoritme de doelen bereikt. Bij een ernstige inbreuk is dat '
            . 'meestal niet genoeg.';
    }

    return $punten;
}

/** Wat het gebruik van het algoritme nu tegenhoudt. @return list<string> */
function ia_blokkerend(array $a): array
{
    $punten = [];

    if (ia_ai_klasse($a)['klasse'] === 'verboden') {
        $punten[] = 'De AI verordening verbiedt deze toepassing (artikel 5). Het algoritme mag niet worden gebruikt.';
    }

    $gestopt = ia_gestopt($a);
    if ($gestopt !== '') {
        $punten[] = sprintf('Het team vindt het niet redelijk om het algoritme te gebruiken (vraag %s). Het gaat '
            . 'terug naar de tekentafel.', $gestopt);
    }

    return $punten;
}

/**
 * Alles wat pagina en document nodig hebben, in één keer uitgerekend.
 *
 * @return array<string,mixed>
 */
function ia_evalueer(array $a): array
{
    $sessie = IA_SESSIES[$a['sessie'] ?? ''] ?? 1;

    return [
        'sessie' => $sessie,
        'versie' => trim((string) ($a['versie'] ?? '')) ?: '0.1',
        'ai' => ia_ai_klasse($a),
        'ernst' => ia_zwaarste_ernst($a),
        'gestopt' => ia_gestopt($a),
        'actiepunten' => [...ia_actiepunten($a), ...(array) ($a['ia_actiepunten'] ?? [])],
        'aandachtspunten' => ia_aandachtspunten($a, $sessie),
        'blokkerend' => ia_blokkerend($a),
    ];
}
