<?php

/**
 * Afleidingen: van de oordelen per aspect naar een risico per gebied, en een
 * voorstel voor het oordeel.
 *
 * - Een gebied is Hoog bij één risico, Midden bij een aandachtspunt of iets
 *   wat niet te beoordelen was, en anders Laag.
 * - Het voorstel is negatief als CO_GRENS_NEGATIEF gebieden of meer Hoog
 *   zijn. Anders onder voorwaarden als er een voorwaarde is, met
 *   aanbevelingen als er iets beter kan, en anders positief.
 *
 * Die regel is een eigen vereenvoudiging; de adviseur kan ervan afwijken.
 * regels.js rekent hetzelfde tijdens het invullen; PHP rekent bij het
 * versturen opnieuw.
 */

declare(strict_types=1);

/** De stukken die er minimaal moeten zijn. */
const CO_MINIMUM = ['businesscase', 'planning', 'architectuur'];

/** De regel van een aspect: het oordeel als samenvatting, en open tenzij op orde of niet van toepassing. */
function co_aspect_stand(string $key, array $a): array
{
    $waarde = (string) ($a[$key] ?? '');

    return [
        'samenvatting' => co_aspect_samenvatting($key, $a),
        'niveau' => (string) (CO_KEUZES[$waarde]['niveau'] ?? ''),
        'open' => !in_array($waarde, ['orde', 'nvt'], true),
    ];
}

/** "Risico · voorwaarde", zoals de regel van een aspect dat toont. */
function co_aspect_samenvatting(string $key, array $a): string
{
    $waarde = (string) ($a[$key] ?? '');

    if ($waarde === '') {
        return 'nog te beoordelen';
    }

    return CO_KEUZES[$waarde]['label'] . (($a[$key . '_voorwaarde'] ?? '') === 'ja' ? ' · voorwaarde' : '');
}

/**
 * De uitslag van één gebied: het risico, en per aspect het oordeel. Een
 * aspect dat niet geldt, valt weg.
 *
 * @return array{niveau:string,open:int,aspecten:list<array<string,mixed>>}
 */
function co_gebied(string $id, array $a): array
{
    $aspecten = [];
    $open = 0;
    $niveau = 'l';

    foreach (co_aspecten($id) as $key => $aspect) {
        if (!cm_zichtbaar(cm_alle_vragen()[$key], $a)) {
            continue;
        }

        $waarde = (string) ($a[$key] ?? '');
        if ($waarde === '') {
            $open++;
            continue;
        }

        $niveau = cm_niveau_max($niveau, (string) CO_KEUZES[$waarde]['niveau']);
        $aspecten[] = $aspect + [
            'key' => $key,
            'waarde' => $waarde,
            'bevinding' => trim((string) ($a[$key . '_bevinding'] ?? '')),
            'advies' => trim((string) ($a[$key . '_advies'] ?? '')),
            'voorwaarde' => ($a[$key . '_voorwaarde'] ?? '') === 'ja',
        ];
    }

    return ['niveau' => $open > 0 ? '' : $niveau, 'open' => $open, 'aspecten' => $aspecten];
}

/**
 * Het voorstel voor het oordeel; leeg zolang er nog aspecten open zijn.
 *
 * @param array<string,array{niveau:string,open:int,aspecten:list<array<string,mixed>>}> $gebieden
 */
function co_voorstel(array $gebieden): string
{
    if (array_sum(array_column($gebieden, 'open')) > 0) {
        return '';
    }

    $aspecten = array_merge(...array_column($gebieden, 'aspecten'));
    $hoog = count(array_filter($gebieden, static fn (array $g): bool => $g['niveau'] === 'h'));

    return match (true) {
        $hoog >= CO_GRENS_NEGATIEF => 'negatief',
        array_filter($aspecten, static fn (array $x): bool => $x['voorwaarde']) !== [] => 'voorwaarden',
        in_array('m', array_column($gebieden, 'niveau'), true),
        in_array('h', array_column($gebieden, 'niveau'), true) => 'aanbevelingen',
        default => 'positief',
    };
}

/** Voor toon_als_uitkomst: wijkt het oordeel af van het voorstel? */
function co_uitkomst(string $naam, array $a): string
{
    if ($naam !== 'afwijking') {
        return '';
    }

    $voorstel = co_voorstel(co_gebieden($a));
    $oordeel = (string) ($a['co_oordeel'] ?? '');

    return $voorstel === '' || $oordeel === '' ? '' : ($voorstel === $oordeel ? 'nee' : 'ja');
}

/** @return array<string,array{niveau:string,open:int,aspecten:list<array<string,mixed>>}> */
function co_gebieden(array $a): array
{
    $gebieden = [];

    foreach (array_keys(CO_GEBIEDEN) as $id) {
        $gebieden[$id] = co_gebied($id, $a);
    }

    return $gebieden;
}

/** De minimale stukken die ontbreken, als labels. @return list<string> */
function co_stukken_missen(array $a): array
{
    $gelezen = (array) ($a['co_stukken'] ?? []);

    return array_values(array_map(
        static fn (string $s): string => mb_strtolower(CO_STUKKEN[$s]['label'], 'UTF-8'),
        array_diff(CO_MINIMUM, $gelezen)
    ));
}

/**
 * Het model voor regels.js: per gebied de sleutels van de aspecten, en de
 * keuzes, oordelen en grenzen. Zo staan de getallen maar op één plek.
 *
 * @return array<string,mixed>
 */
function co_model(): array
{
    $gebieden = [];
    foreach (CO_GEBIEDEN as $id => $gebied) {
        $gebieden[] = ['id' => $id, 'naam' => $gebied['naam'], 'kort' => $gebied['kort'],
            'aspecten' => array_keys(co_aspecten($id))];
    }

    return [
        'gebieden' => $gebieden,
        'keuzes' => array_map(static fn (array $k): array => ['label' => $k['label'], 'niveau' => $k['niveau']], CO_KEUZES),
        'oordelen' => CO_OORDELEN,
        'grens' => CO_GRENS_NEGATIEF,
        'minimum' => array_combine(CO_MINIMUM, array_map(
            static fn (string $s): string => mb_strtolower(CO_STUKKEN[$s]['label'], 'UTF-8'), CO_MINIMUM)),
    ];
}

/** "1.8 Business case: de baten staan in euro’s", voor de lijsten in het rapport. */
function co_regel(string $gebied, array $x, string $tekst): string
{
    $nummer = array_search($gebied, array_keys(CO_GEBIEDEN), true) + 1;

    return $nummer . '.' . $x['nummer'] . ' ' . ucfirst(CO_GEBIEDEN[$gebied]['kort']) . ': ' . $tekst;
}

/** @return list<string> */
function co_vervolg(array $a, string $oordeel, array $u): array
{
    $aanleiding = (array) ($a['co_aanleiding'] ?? []);

    $stappen = [
        $oordeel === 'negatief'
            ? 'Leg het oordeel voor aan de SG. Wil de SG het project toch laten doorgaan, dan legt de SG vast waarom.'
            : '',
        $u['voorwaarden'] === [] ? '' : 'Spreek af wie elke voorwaarde regelt, en wanneer. Het project gaat pas '
            . 'verder als dat klaar is.',
        $u['aanbevelingen'] === [] ? '' : 'De opdrachtgever laat weten wat er met elke aanbeveling gebeurt.',
        $u['missen'] === [] ? '' : 'Vraag de stukken op die nog ontbreken: ' . cm_opsomming($u['missen']) . '.',
        $u['onbekend'] === [] ? '' : 'Zoek uit wat nu niet te beoordelen was. Pas het oordeel aan als dat nodig is.',
        in_array('bedrag', $aanleiding, true) && in_array($a['co_acict'] ?? '', ['nog_niet', 'onbekend'], true)
            ? 'Meld het project aan bij het Adviescollege ICT-toetsing.'
            : '',
        in_array('bedrag', $aanleiding, true) ? 'Zet het oordeel bij het project op het Rijks ICT-dashboard.' : '',
        'Geef opnieuw een oordeel bij een herijking, of als het project flink verandert.',
    ];

    return array_values(array_filter($stappen));
}

/**
 * Alles wat pagina en rapport nodig hebben, in één keer uitgerekend.
 *
 * @return array<string,mixed>
 */
function co_evalueer(array $a): array
{
    $gebieden = co_gebieden($a);
    $u = ['goed' => [], 'verbeter' => [], 'aanbevelingen' => [], 'voorwaarden' => [], 'onbekend' => []];

    foreach ($gebieden as $id => $gebied) {
        foreach ($gebied['aspecten'] as $x) {
            if ($x['waarde'] === 'orde' && $x['bevinding'] !== '') {
                $u['goed'][] = co_regel($id, $x, $x['bevinding']);
            }
            if (in_array($x['waarde'], ['aandacht', 'risico'], true)) {
                $u['verbeter'][$id][] = $x;
                if ($x['advies'] !== '') {
                    $u[$x['voorwaarde'] ? 'voorwaarden' : 'aanbevelingen'][] = co_regel($id, $x, $x['advies']);
                }
            }
            if ($x['waarde'] === 'onbekend') {
                $u['onbekend'][] = co_regel($id, $x, $x['tekst']);
            }
        }
    }

    $voorstel = co_voorstel($gebieden);
    $oordeel = (string) ($a['co_oordeel'] ?? '') ?: $voorstel;
    $u['missen'] = co_stukken_missen($a);

    return $u + [
        'gebieden' => $gebieden,
        'hoog' => array_keys(array_filter($gebieden, static fn (array $g): bool => $g['niveau'] === 'h')),
        'voorstel' => $voorstel,
        'oordeel' => $oordeel,
        'afwijking' => $voorstel !== '' && $oordeel !== $voorstel,
        'vervolg' => co_vervolg($a, $oordeel, $u),
    ];
}
