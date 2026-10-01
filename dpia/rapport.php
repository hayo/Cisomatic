<?php

/**
 * Het document: de DPIA in de hoofdstukken van het model, als blokken.
 *
 * Een blok is [soort, inhoud]. De motor maakt er HTML van voor de
 * pagina, en een .odt-bestand. Alle tekst is platte tekst. Opmaak zit
 * alleen in de soort van het blok, dus een antwoord wordt nooit per ongeluk
 * vet of een kop.
 *
 * Soorten: h1 tot en met h4, alinea, term (label en tekst), lijst, genummerd,
 * tabel, en schema (alleen op de pagina).
 */

declare(strict_types=1);

/** @return list<array<int,mixed>> */
function dp_rapport(array $a, array $u): array
{
    $naam = trim((string) ($a['projectnaam'] ?? '')) ?: 'Naamloze verwerking';

    return [
        ...dp_rapport_inleiding($a, $u, $naam),
        ...dp_rapport_beschrijving($a, $u),
        ...($u['samen'] ? dp_rapport_rechtmatigheid($a, $u, $naam) : dp_rapport_open('2 Beoordeling van de rechtmatigheid')),
        ...($u['samen'] ? dp_rapport_risicos($a, $u, $naam) : dp_rapport_open('3 Risico’s')),
        ...($u['samen'] ? dp_rapport_maatregelen($u) : dp_rapport_open('4 Maatregelen')),
    ];
}

/** Tekst uit een tekstvak als alinea's, één per regel. */
function dp_alineas(string $tekst): array
{
    return array_map(static fn (string $regel): array => ['alinea', $regel], cm_regels($tekst));
}

/** Een label met een kleine beginletter, voor midden in een zin. "IP-adres" blijft staan. */
function dp_klein(string $tekst): string
{
    $tweede = mb_substr($tekst, 1, 1, 'UTF-8');

    return $tweede !== '' && $tweede === mb_strtoupper($tweede, 'UTF-8') && $tweede !== mb_strtolower($tweede, 'UTF-8')
        ? $tekst
        : mb_strtolower(mb_substr($tekst, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($tekst, 1, null, 'UTF-8');
}

/** Het label van een keuze in een rij, zoals de rol van een partij. */
function dp_kolomlabel(string $vraag, string $kolom, string $waarde): string
{
    return (string) (cm_alle_vragen()[$vraag]['kolommen'][$kolom]['opties'][$waarde]['label'] ?? $waarde);
}

function dp_rapport_open(string $titel): array
{
    return [['h2', $titel], ['alinea', 'Dit hoofdstuk wordt nog ingevuld, samen met de privacyfunctionaris.']];
}

function dp_rapport_inleiding(array $a, array $u, string $naam): array
{
    $datum = cm_datum_nl(new DateTimeImmutable('now'));
    // Naam, datum en handtekening worden op het document zelf ingevuld.
    $ondertekening = static fn (string $functie): array => [
        'rijen' => [['Naam', ''], ['Functie', $functie], ['Datum', ''], ['Handtekening', '']],
        'breedtes' => [4, 12.6],
        'rijkop' => true,
    ];

    return [
        ['h1', 'DPIA ' . $naam],
        ['term', 'Versie', $u['versie']],
        ['term', 'Datum', $datum],
        ['term', 'Opgesteld door', (string) $a['aanvrager']],
        ['term', 'Afdeling', (string) $a['organisatieonderdeel']],
        ...(($a['email'] ?? '') === '' ? [] : [['term', 'Contact', (string) $a['email']]]),

        ['h2', 'Inleiding en vaststelling'],
        ['alinea', 'Onder de Algemene verordening gegevensbescherming (AVG) kan een organisatie verplicht zijn '
            . 'om een Data Protection Impact Assessment (DPIA) uit te voeren. Een DPIA brengt vooraf de '
            . 'privacyrisico’s van een verwerking van persoonsgegevens in kaart. Daarna volgen de maatregelen '
            . 'die deze risico’s kleiner maken.'],
        ['h3', 'Het proces achter de verwerking'],
        ...dp_alineas((string) $a['proces_omschrijving']),
        ['h3', 'Aanleiding'],
        ['alinea', cm_antwoord_tekst('aanleiding', $a) . '.'],
        ...dp_alineas((string) ($a['aanleiding_toelichting'] ?? '')),
        ['h3', 'Doel van deze DPIA'],
        ['alinea', sprintf('Deze DPIA beschrijft de verwerking %s, en werkt de privacyrisico’s ervan uit.', $naam)],
        ['h3', 'Scope'],
        ['h4', 'Wat binnen de scope valt'],
        ...dp_alineas((string) $a['scope_binnen']),
        ['h4', 'Wat buiten de scope valt'],
        ...dp_alineas((string) $a['scope_buiten']),
        ['h3', 'Aanpak'],
        ['alinea', 'Deze DPIA volgt de opbouw van het Model DPIA Rijksdienst. De opsteller heeft de verwerking '
            . 'beschreven. ' . ($u['samen']
                ? 'De rechtsgrond, de risico’s en de maatregelen zijn samen met de privacyfunctionaris beoordeeld.'
                : 'De beoordeling samen met de privacyfunctionaris volgt nog.')],
        ['alinea', 'Een DPIA is nooit helemaal af. Verandert de verwerking, bijvoorbeeld door nieuwe techniek of '
            . 'een ander gebruik van de gegevens, dan wordt de DPIA bijgewerkt. Vier jaar na de vaststelling wordt '
            . 'hij in ieder geval herzien.'],
        ['h3', 'Versiebeheer'],
        ['tabel', [
            'kop' => ['Versie', 'Datum', 'Door', 'Toelichting'],
            'rijen' => [[$u['versie'], $datum, (string) $a['aanvrager'], trim((string) ($a['versie_toelichting'] ?? '')) ?: 'Eerste opzet']],
            'breedtes' => [2, 3.5, 5, 6.1],
        ]],
        ['h3', 'Beoordeling en vaststelling'],
        ['h4', 'Beoordeling'],
        ['tabel', $ondertekening('Functionaris gegevensbescherming')],
        ['h4', 'Vaststelling en acceptatie van de risico’s'],
        ['tabel', $ondertekening('')],
    ];
}

function dp_rapport_beschrijving(array $a, array $u): array
{
    $b = [
        ['h2', '1 Beschrijving van de verwerking'],
        ['alinea', 'Dit hoofdstuk beschrijft de verwerking, de doelen en de belangen die ermee gemoeid zijn.'],
        ['h3', '1.1 Toelichting op de verwerking'],
        ...dp_alineas((string) $a['verwerking_omschrijving']),
        ['alinea', 'Het gebeurt met dit systeem of deze dienst:'],
        ...dp_alineas((string) $a['systeem_omschrijving']),
    ];

    foreach (cm_regels((string) ($a['deelverwerkingen'] ?? '')) as $i => $regel) {
        [$kop, $tekst] = array_map('trim', explode(':', $regel, 2)) + [1 => ''];
        $b[] = ['h4', sprintf('1.1.%d %s', $i + 1, $kop)];
        if ($tekst !== '') {
            $b[] = ['alinea', $tekst];
        }
    }

    $b = [
        ...$b,
        ['h3', '1.2 Betrokkenen'],
        ['alinea', sprintf(
            'De verwerking gaat over de volgende groepen. In totaal gaat het om %s mensen.',
            dp_klein(cm_antwoord_tekst('pg_aantal_betrokkenen', $a))
        )],
        ['tabel', [
            'kop' => ['Betrokkenen', 'Rol in de verwerking'],
            'rijen' => array_map(static fn (array $g): array => [$g['label'], $g['omschrijving']], $u['groepen']),
            'breedtes' => [5, 11.6],
        ]],
        ['h3', '1.3 Persoonsgegevens'],
        ['alinea', 'Hieronder staan de persoonsgegevens, of die gewoon of bijzonder zijn, en waar ze vandaan komen.'],
    ];

    foreach (dp_gegevenstabellen($a) as $i => $tabel) {
        $b[] = ['h4', sprintf('1.3.%d %s', $i + 1, $tabel['titel'])];
        $b[] = ['tabel', [
            'kop' => ['Persoonsgegeven', 'Soort', 'Bron'],
            'rijen' => dp_gegevensrijen($a, $tabel['soorten']),
            'breedtes' => [7, 3.8, 5.8],
        ]];
    }

    $b[] = ['h3', '1.4 Wat er met de gegevens gebeurt'];

    $opties = cm_alle_vragen()['verwerking_vormen']['opties'];
    $perGroep = ['Vastleggen' => [], 'Raadplegen' => [], 'Bewaren' => [dp_bewaarzin($a)], 'Overdragen' => [],
        'Na de bewaartermijn' => [dp_na_termijn_zin($a)], 'Overige handelingen' => []];

    foreach ((array) $a['verwerking_vormen'] as $vorm) {
        $perGroep[$opties[$vorm]['groep']][] = $opties[$vorm]['label'];
    }
    array_push($perGroep['Overdragen'], ...array_map(
        static fn (string $regel): string => 'Verstrekt aan: ' . $regel,
        cm_regels((string) ($a['verstrekken_aan'] ?? ''))
    ));
    array_push($perGroep['Overige handelingen'], ...cm_regels((string) ($a['verwerking_vormen_anders'] ?? '')));

    $nummer = 0;
    foreach (array_filter($perGroep) as $groep => $items) {
        $b[] = ['h4', sprintf('1.4.%d %s', ++$nummer, $groep)];
        $b[] = ['lijst', $items];
    }

    $stromen = array_values((array) $a['stromen']);
    $b = [
        ...$b,
        ['h4', sprintf('1.4.%d Schema van de gegevensstromen', ++$nummer)],
        ['schema', $stromen, 'Een doorgetrokken lijn valt binnen deze DPIA, een gestippelde lijn erbuiten. '
            . 'De nummers staan ook in de tabel.'],
        ['tabel', [
            'kop' => ['#', 'Van', 'Naar', 'Gegevens', 'Binnen de DPIA'],
            'rijen' => array_map(
                static fn (array $s, int $i): array => [(string) ($i + 1), $s['van'], $s['naar'], $s['gegevens'], $s['scope'] === 'buiten' ? 'Nee' : 'Ja'],
                $stromen,
                array_keys($stromen)
            ),
            'breedtes' => [0.9, 4, 4, 5.2, 2.5],
        ]],

        ['h3', '1.5 Doelen van de verwerking'],
        ['alinea', 'De persoonsgegevens worden voor de volgende doelen gebruikt.'],
        ['lijst', cm_regels((string) $a['pg_doel'])],

        ['h3', '1.6 Betrokken partijen'],
        ['alinea', 'Deze organisaties doen mee aan de verwerking, elk in een eigen rol. Per partij staat ook '
            . 'welke functies bij de persoonsgegevens kunnen.'],
        ['tabel', [
            'kop' => ['Organisatie', 'Rol', 'Toegang tot de gegevens'],
            'rijen' => array_map(
                static fn (array $p): array => [$p['organisatie'], dp_kolomlabel('partijen', 'rol', $p['rol']), $p['toegang']],
                (array) $a['partijen']
            ),
            'breedtes' => [5.6, 4.5, 6.5],
        ]],
    ];

    $overeenkomst = match ($a['verwerkersovereenkomst'] ?? '') {
        'getekend' => 'Met elke verwerker is een verwerkersovereenkomst getekend.',
        'onderhandeling' => 'Over de verwerkersovereenkomst wordt nog onderhandeld.',
        'nee' => 'Er is nog geen verwerkersovereenkomst.',
        default => '',
    };
    $subverwerkers = match ($a['pg_subverwerkers'] ?? '') {
        'ja' => sprintf('Een verwerker schakelt zelf ook andere partijen in. Die zitten %s.',
            dp_klein(cm_antwoord_tekst('pg_subverwerkers_locatie', $a))),
        'nee' => 'Verwerkers schakelen zelf geen andere partijen in.',
        default => '',
    };

    return [
        ...$b,
        ...array_map(static fn (string $zin): array => ['alinea', $zin], array_filter([$overeenkomst, $subverwerkers])),

        ['h3', '1.7 Belang van de verwerking'],
        ['alinea', match ($a['belang']) {
            'kritisch' => 'Zonder deze verwerking valt het proces stil.',
            'belangrijk' => 'Zonder deze verwerking gaat het proces door, maar het kost veel meer moeite.',
            default => 'De verwerking is handig, maar niet nodig voor het proces.',
        }],
        ...dp_alineas((string) $a['belang_toelichting']),

        ['h3', '1.8 Waar de verwerking plaatsvindt'],
        ...dp_locatiezinnen($a),

        ['h3', '1.9 Technieken en methoden'],
        ...dp_techniekzinnen($a),

        ['h3', '1.10 Wetten en kaders'],
        ['alinea', 'Voor deze verwerking gelden de volgende wetten en kaders.'],
        ['lijst', $u['kaders']],
        ...(cm_regels((string) ($a['eigen_beleid'] ?? '')) === [] ? [] : [
            ['alinea', 'Daarnaast telt dit beleid van de organisatie mee.'],
            ['lijst', cm_regels((string) $a['eigen_beleid'])],
        ]),

        ['h3', '1.11 Bewaartermijnen'],
        ['alinea', dp_bewaarzin($a)],
        ['alinea', dp_na_termijn_zin($a)],
    ];
}

/** @param list<string> $soorten */
function dp_gegevensrijen(array $a, array $soorten): array
{
    $alle = dp_informatiesoorten();
    $rijen = [];

    foreach ($soorten as $soort) {
        $zwaarte = match ($alle[$soort]['groep']) {
            'Bijzondere persoonsgegevens' => 'Bijzonder',
            'Identificerende gegevens' => 'Gewoon, identificerend',
            default => 'Gewoon',
        };
        $namen = $soort === 'pg_anders'
            ? (cm_regels((string) ($a['pg_anders_welke'] ?? '')) ?: [$alle[$soort]['label']])
            : [$alle[$soort]['label']];

        foreach ($namen as $naam) {
            $rijen[] = [$naam, $zwaarte, cm_antwoord_tekst('bron_' . $soort, $a)];
        }
    }

    return $rijen;
}

function dp_bewaarzin(array $a): string
{
    $toelichting = trim((string) ($a['bewaartermijn_toelichting'] ?? ''));

    return match ($a['bewaartermijn'] ?? '') {
        'selectielijst' => sprintf('De gegevens worden bewaard volgens de selectielijst (%s).', $toelichting),
        'wettelijk' => sprintf('De gegevens worden bewaard volgens een termijn uit andere wetgeving (%s).', $toelichting),
        'eigen' => sprintf('De gegevens worden bewaard volgens een termijn die de organisatie zelf heeft bepaald (%s).', $toelichting),
        default => 'De bewaartermijn is nog niet bepaald.',
    };
}

function dp_na_termijn_zin(array $a): string
{
    $zin = match ($a['na_termijn'] ?? '') {
        'overbrengen' => 'Na de termijn gaan de gegevens naar een archiefdienst.',
        'beide' => 'Na de termijn wordt een deel van de gegevens vernietigd, en gaat een deel naar een archiefdienst.',
        default => 'Na de termijn worden de gegevens vernietigd.',
    };

    return $zin . match ($a['archief_vernietiging'] ?? '') {
        'automatisch' => ' Het systeem vernietigt automatisch.',
        'handmatig' => ' Vernietigen gebeurt met de hand.',
        'nee' => ' Het systeem kan nog niet vernietigen.',
        default => '',
    };
}

function dp_locatiezinnen(array $a): array
{
    $zinnen = [];

    if (($a['cloud'] ?? '') === 'nee') {
        $zinnen[] = 'De verwerking en de opslag vinden plaats op de eigen infrastructuur van de organisatie.';
    } else {
        if ($a['cloud'] === 'mogelijk') {
            $zinnen[] = 'Of er een clouddienst wordt gebruikt, staat nog niet vast. Deze DPIA gaat daar wel van uit.';
        }

        $leverancier = trim((string) ($a['cloud_leverancier'] ?? ''));
        $zinnen[] = sprintf(
            'De verwerking en de opslag vinden plaats bij een clouddienst%s. Het gaat om een %s.',
            $leverancier === '' ? '' : ' van ' . $leverancier,
            match ($a['cloud_type'] ?? '') {
                'community' => 'community cloud',
                'prive' => 'private cloud',
                'hybride' => 'hybride cloud',
                default => 'publieke cloud',
            }
        );
        $zinnen[] = sprintf('De gegevens staan %s.', dp_klein(cm_antwoord_tekst('cloud_locatie', $a)));
    }

    if (trim((string) ($a['documentatie'] ?? '')) !== '') {
        $zinnen[] = sprintf('De documentatie staat hier: %s.', trim((string) $a['documentatie']));
    }

    return array_map(static fn (string $zin): array => ['alinea', $zin], $zinnen);
}

function dp_techniekzinnen(array $a): array
{
    $technieken = (array) ($a['technieken'] ?? []);
    $opties = cm_alle_vragen()['technieken']['opties'];

    $b = $technieken === []
        ? [['alinea', 'Er worden geen bijzondere technieken gebruikt, zoals geautomatiseerde besluitvorming, '
            . 'profilering of het combineren van grote hoeveelheden gegevens.']]
        : [
            ['alinea', 'Bij de verwerking worden deze technieken gebruikt.'],
            ['lijst', array_map(static fn (string $t): string => $opties[$t]['label'], $technieken)],
            ...dp_alineas((string) ($a['technieken_toelichting'] ?? '')),
        ];

    $b[] = ['alinea', match ($a['ai'] ?? '') {
        'mogelijk' => 'AI is bij deze verwerking niet uitgesloten.',
        'onderdeel' => 'AI is onderdeel van de verwerking.',
        default => 'AI is bij deze verwerking uitgesloten.',
    }];

    return [...$b, ...dp_alineas((string) ($a['ai_omschrijving'] ?? ''))];
}

function dp_rapport_rechtmatigheid(array $a, array $u, string $naam): array
{
    $grondslag = (string) $a['pg_grondslag'];

    $b = [
        ['h2', '2 Beoordeling van de rechtmatigheid'],
        ['alinea', 'Dit hoofdstuk beoordeelt de rechtsgrond, de noodzaak en de doelbinding van de verwerking. '
            . 'Ook de rechten van de betrokkenen komen aan bod.'],
        ['h3', '2.1 Rechtsgrond'],
        ['alinea', 'Volgens artikel 6 van de AVG is een verwerking alleen rechtmatig als een van de grondslagen '
            . 'uit artikel 6, lid 1 geldt.'],
        ['alinea', sprintf('De verwerking %s rust op deze grondslag: %s.', $naam, dp_klein(cm_antwoord_tekst('pg_grondslag', $a)))],
    ];

    if (($a['grondslag_wet'] ?? '') !== '') {
        $b[] = ['alinea', sprintf('De wettelijke basis is %s.', $a['grondslag_wet'])];
    }
    if ($grondslag === 'toestemming') {
        $b = [...$b, ['h4', 'Toestemming'], ...dp_alineas((string) $a['toestemming_wijze'])];
    }
    if (($a['bsn_wet'] ?? '') !== '') {
        $b[] = ['alinea', sprintf('Het burgerservicenummer wordt gebruikt op grond van %s.', $a['bsn_wet'])];
    }

    if ($grondslag === 'gerechtvaardigd_belang') {
        $b = [
            ...$b,
            ['h4', 'Belangenafweging'],
            ['alinea', 'Bij gerechtvaardigd belang worden de belangen van de organisatie afgewogen tegen die van '
                . 'de betrokkenen.'],
            ['alinea', 'a. Belang van de organisatie'],
            ...dp_alineas((string) $a['belang_verantwoordelijke']),
            ['alinea', 'b. Gevolgen voor de betrokkenen'],
            ...dp_alineas((string) $a['belang_betrokkene']),
            ['alinea', 'c. Voorlopige afweging'],
            ['alinea', cm_antwoord_tekst('balans_voorlopig', $a) . '.'],
        ];

        if (($a['waarborgen'] ?? '') !== '') {
            $b = [
                ...$b,
                ['alinea', 'd. Extra waarborgen'],
                ...dp_alineas((string) $a['waarborgen']),
                ['alinea', 'e. Afweging met de waarborgen'],
                ['alinea', cm_antwoord_tekst('balans_eind', $a) . '.'],
            ];
        }
    }

    $bijzonder = $u['pg']['bijzonder'];
    $b = [
        ...$b,
        ['h3', '2.2 Bijzondere persoonsgegevens'],
        ...($bijzonder === []
            ? [['alinea', 'Er worden geen bijzondere persoonsgegevens verwerkt.']]
            : [
                ['alinea', sprintf('Deze bijzondere persoonsgegevens worden verwerkt: %s.', dp_labels($bijzonder))],
                ['alinea', sprintf('Dat mag op grond van deze uitzondering: %s.', dp_klein(cm_antwoord_tekst('uitzondering', $a)))],
                ...dp_alineas((string) $a['uitzondering_toelichting']),
            ]),

        ['h3', '2.3 Noodzaak en evenredigheid'],
        ['alinea', 'Beoordeeld is of de verwerking nodig is om de doelen te bereiken. Daarbij gaat het om '
            . 'proportionaliteit en subsidiariteit.'],
        ['h4', 'Proportionaliteit'],
        ['alinea', $a['pg_proportionaliteit'] === 'twijfel'
            ? 'Er is twijfel of de inbreuk op de privacy in verhouding staat tot het doel.'
            : 'De inbreuk op de privacy staat in verhouding tot het doel.'],
        ...dp_alineas((string) $a['proportionaliteit_toelichting']),
        ['h4', 'Subsidiariteit'],
        ['alinea', $a['pg_subsidiariteit'] === 'kan_minder'
            ? 'Het doel kan ook met minder gegevens, of anoniemer, worden bereikt.'
            : 'Het doel kan niet worden bereikt op een manier die minder ingrijpt in de privacy.'],
        ...dp_alineas((string) $a['subsidiariteit_toelichting']),
        ['h4', 'Doelbinding'],
        ['alinea', match ($a['doelbinding']) {
            'verenigbaar' => 'De gegevens worden ook gebruikt voor een doel dat bij deze verwerking past.',
            'anders' => 'De gegevens worden ook gebruikt voor een doel dat los staat van deze verwerking.',
            default => 'De gegevens worden alleen gebruikt voor de doelen van deze verwerking.',
        }],
        ...dp_alineas((string) ($a['doelbinding_toelichting'] ?? '')),

        ['h3', '2.4 Rechten van de betrokkenen'],
        ['alinea', match ($a['informeren']) {
            'beide' => 'Betrokkenen horen op twee manieren dat hun gegevens worden gebruikt: via de '
                . 'privacyverklaring, en op het moment dat de gegevens worden verzameld.',
            'melding' => 'Betrokkenen horen op het moment van verzamelen dat hun gegevens worden gebruikt.',
            'verklaring' => 'Betrokkenen kunnen in de privacyverklaring lezen dat hun gegevens worden gebruikt.',
            default => 'Betrokkenen horen nog niet dat hun gegevens worden gebruikt.',
        }],
        ['alinea', match ($a['pg_rechten']) {
            'volledig' => 'Het systeem kan inzage, correctie en verwijdering volledig aan.',
            'deels' => 'Het systeem kan inzage, correctie en verwijdering maar deels aan, of alleen met de hand.',
            default => 'Het systeem kan inzage, correctie en verwijdering niet aan.',
        }],
        ['alinea', $a['verzoeken_register'] === 'ja'
            ? 'Verzoeken van betrokkenen worden bijgehouden in een register, en iemand bewaakt de termijn.'
            : 'Verzoeken van betrokkenen worden niet in een register bijgehouden.'],
    ];

    if (($a['rechten_regeling'] ?? '') !== '') {
        $b[] = ['alinea', sprintf('Hoe iemand een verzoek indient, staat hier: %s.', $a['rechten_regeling'])];
    }

    return $b;
}

function dp_rapport_risicos(array $a, array $u, string $naam): array
{
    $categorieen = [];
    foreach (dp_risicocategorieen() as $letter => $tekst) {
        $categorieen[] = $letter . '. ' . $tekst;
    }

    $matrix = [];
    foreach (dp_tolerantiematrix() as $kans => $rij) {
        $matrix[] = [cm_niveau_label($kans), ...array_map('cm_niveau_label', array_values($rij))];
    }

    $tabel = array_map(static fn (array $r): array => [
        (string) $r['nummer'],
        $r['tekst'],
        $r['categorieen'],
        $r['genomen'] ?: ['Nog geen'],
        cm_niveau_label($r['impact']),
        cm_niveau_label($r['kans']),
        cm_niveau_label($r['omvang']),
    ], $u['risicos']);

    return [
        ['h2', '3 Risico’s'],
        ['alinea', 'Dit hoofdstuk beschrijft en beoordeelt de risico’s van de verwerking voor de rechten en '
            . 'vrijheden van de betrokkenen. Daarbij tellen de aard, de omvang, de context en de doelen van de '
            . 'verwerking mee, zoals hoofdstuk 1 en 2 die beschrijven.'],
        ['h3', '3.1 Toelichting'],
        ['alinea', sprintf('De verwerking %s gebruikt persoonsgegevens. Dat brengt risico’s mee voor de privacy '
            . 'van de betrokkenen, en voor de partijen die meedoen. Deze categorieën van risico’s worden '
            . 'onderscheiden.', $naam)],
        ['lijst', $categorieen],
        ['alinea', 'Per risico zijn de kans en de impact ingeschat, op een schaal van Zeer laag tot Zeer hoog.'],
        ['lijst', [
            'Kans: hoe waarschijnlijk het is dat het risico optreedt.',
            'Impact: hoe groot het effect is als het optreedt.',
            'Omvang: kans en impact samen, volgens de tolerantiematrix.',
        ]],
        ['alinea', 'Bij een omvang van Hoog of Zeer hoog zijn extra maatregelen nodig. Bij Midden worden ze voor '
            . 'privacy ook verwacht. In de matrix staat de kans in de rij, en de impact in de kolom.'],
        ['tabel', [
            'kop' => ['Kans', ...array_values(CM_NIVEAUS)],
            'rijen' => $matrix,
            'breedtes' => [3.1, 2.7, 2.7, 2.7, 2.7, 2.7],
            'rijkop' => true,
        ]],
        ['h3', '3.2 Uitwerking van de risico’s'],
        ['alinea', 'De tabel geeft per risico de omschrijving, de categorieën en de maatregelen die er al zijn. '
            . 'Daarna volgen impact, kans en omvang. Dat is de omvang zonder de extra maatregelen uit hoofdstuk 4.'],
        ['tabel', [
            'kop' => ['#', 'Risico', 'Cat.', 'Genomen maatregelen', 'Impact', 'Kans', 'Omvang'],
            'rijen' => $tabel,
            'breedtes' => [0.8, 5.4, 1.4, 4.6, 1.5, 1.4, 1.5],
        ]],
        ['h3', '3.3 Conclusie'],
        ...dp_alineas((string) ($a['conclusie_toelichting'] ?? '')),
        ...($u['aandachtspunten'] === [] ? [] : [
            ['alinea', 'Belangrijke aandachtspunten:'],
            ['lijst', $u['aandachtspunten']],
        ]),
    ];
}

function dp_rapport_maatregelen(array $u): array
{
    $rest = array_map(static fn (array $r): array => [
        (string) $r['nummer'],
        $r['titel'],
        $r['extra'] ?: ['Geen extra maatregel'],
        cm_niveau_label($r['impact']),
        cm_niveau_label($r['kans_na']),
        cm_niveau_label($r['rest']),
    ], $u['risicos']);

    return [
        ['h2', '4 Maatregelen'],
        ['h3', '4.1 Aanbevolen maatregelen'],
        ...($u['maatregelen'] === []
            ? [['alinea', 'Er zijn geen extra maatregelen nodig. De maatregelen die er al zijn, volstaan.']]
            : [
                ['alinea', 'Deze maatregelen horen bij de risico’s uit hoofdstuk 3. Het nummer tussen haakjes '
                    . 'verwijst naar het risico.'],
                ['genummerd', $u['maatregelen']],
            ]),
        ['h4', '4.1.1 Risico’s met de aanbevolen maatregelen'],
        ['alinea', 'Met de aanbevolen maatregelen erbij ziet de inschatting er zo uit.'],
        ['tabel', [
            'kop' => ['#', 'Risico', 'Maatregelen', 'Impact', 'Kans', 'Omvang'],
            'rijen' => $rest,
            'breedtes' => [0.8, 5, 6.3, 1.5, 1.5, 1.5],
        ]],
    ];
}
