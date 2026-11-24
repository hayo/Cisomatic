<?php

/**
 * Maakt van een antwoordbestand het document, zoals de webapp dat doet.
 *
 *   php tools/maak.php dpia antwoorden.json uit/
 *   php tools/maak.php dpia antwoorden.json uit/ --tekst   ook het document als tekst
 *   php tools/maak.php dpia antwoorden.json uit/ --alles   idem, met de verantwoording
 *   php tools/maak.php dpia antwoorden.json uit/ --pdf     ook een PDF, met LibreOffice
 *
 * Het antwoordbestand is een object met sleutel => antwoord, of een
 * antwoordbestand uit de webapp (met "antwoorden" erin). Het script:
 *
 * 1. controleert de antwoorden: onbekende sleutels, keuzes die niet bestaan,
 *    antwoorden die wegvallen omdat de vraag niet geldt, en de invulplicht
 * 2. schrijft het .odt-bestand en het antwoordbestand (.json), met dezelfde
 *    namen, het kenmerk en de vingerafdruk als de webapp
 * 3. noemt wat in de tekst opvalt: een streepje voor een regel, een regel
 *    die eindigt op een dubbele punt, een punt achter een kort tekstveld, en
 *    een alinea die te lang is om prettig te lezen
 *
 * Bij een fout in stap 1 komt er geen document, en stopt het met code 1.
 */

declare(strict_types=1);

require __DIR__ . '/laad.php';

[$vlaggen, $rest] = tl_argumenten($argv);
if (count($rest) < 3) {
    tl_stop('Gebruik: php tools/maak.php <formulier> <antwoorden.json> <uitmap> [--tekst] [--alles] [--pdf]');
}

[$id, $bestand, $uitmap] = $rest;
$formulier = tl_formulier($id);

$json = @file_get_contents($bestand);
$ruw = is_string($json) ? json_decode($json, true) : null;
if (!is_array($ruw)) {
    tl_stop('Kan ' . $bestand . ' niet lezen als JSON' . (is_string($json) ? ': ' . json_last_error_msg() : '') . '.');
}
if (isset($ruw['antwoorden']) && is_array($ruw['antwoorden'])) {
    if (isset($ruw['soort']) && $ruw['soort'] !== $id) {
        echo 'Let op: dit antwoordbestand is van ', $ruw['soort'], ', niet van ', $id, ".\n";
    }
    $ruw = $ruw['antwoorden'];
}

$vragen = cm_alle_vragen();
$a = cm_lees_antwoorden($ruw);
$label = static fn (string $key): string => trim((string) preg_replace('/\s+/u', ' ',
    (string) ($vragen[$key]['label'] ?? $key)));

// 1. Wat de motor stil zou weggooien.
$meldingen = [];
foreach ($ruw as $key => $waarde) {
    $vraag = $vragen[$key] ?? null;
    if ($vraag === null || !cm_is_invoer($vraag)) {
        $meldingen[] = "$key: deze vraag bestaat niet" . ($vraag === null ? '' : ' als invoer') . '.';
        continue;
    }
    if ($waarde === null || $waarde === [] || (is_string($waarde) && trim($waarde) === '')) {
        continue;
    }
    $opties = array_map('strval', array_keys($vraag['opties'] ?? []));
    if (in_array($vraag['type'], ['radio', 'select'], true) && !in_array((string) $waarde, $opties, true)) {
        $meldingen[] = "$key: de keuze ‘" . (is_string($waarde) ? $waarde : json_encode($waarde))
            . "’ bestaat niet. Kies uit: " . implode(', ', array_filter($opties, static fn (string $o): bool => $o !== '')) . '.';
        continue;
    }
    if ($a[$key] === '' || $a[$key] === []) {
        $meldingen[] = "$key: valt weg, want de vraag geldt niet bij deze antwoorden.";
        continue;
    }
    if (in_array($vraag['type'], ['checkbox', 'maatregelen'], true)) {
        $over = $vraag['type'] === 'checkbox'
            ? array_diff(array_map('strval', (array) $waarde), $a[$key])
            : array_diff_key((array) $waarde, $a[$key]);
        if ($over !== []) {
            $meldingen[] = "$key: deze keuzes bestaan niet of mogen niet: " . implode(', ', $vraag['type'] === 'checkbox'
                ? $over : array_keys($over)) . '.';
        }
    }
    if ($vraag['type'] === 'rijen' && is_array($waarde)) {
        foreach (array_values($waarde) as $i => $rij) {
            foreach ($vraag['kolommen'] as $kolom => $def) {
                $cel = is_array($rij) ? (string) ($rij[$kolom] ?? '') : '';
                if ($def['type'] === 'select' && $cel !== '' && !array_key_exists($cel, $def['opties'] ?? [])) {
                    $meldingen[] = "$key, rij " . ($i + 1) . ", $kolom: de keuze ‘$cel’ bestaat niet.";
                }
            }
            foreach (array_keys(is_array($rij) ? $rij : []) as $kolom) {
                if (!isset($vraag['kolommen'][$kolom])) {
                    $meldingen[] = "$key, rij " . ($i + 1) . ": kolom $kolom bestaat niet.";
                }
            }
        }
    }
}

$fouten = cm_valideer($a);
foreach ($fouten as $key => $fout) {
    $meldingen[] = "$key ({$label($key)}): $fout";
}

if ($fouten !== []) {
    echo "Geen document. Dit moet eerst anders:\n- ", implode("\n- ", $meldingen), "\n";
    exit(1);
}

// 2. Het document en het antwoordbestand, zoals de webapp ze maakt.
$uitkomst = $formulier['evalueer']($a);
$blokken = cm_verantwoord($formulier['document']($a, $uitkomst), $a, $uitkomst);
$vingerafdruk = cm_vingerafdruk($a);
$kenmerk = cm_kenmerk($vingerafdruk);
$naam = (string) ($a['projectnaam'] ?? '');

if (!is_dir($uitmap) && !mkdir($uitmap, 0777, true)) {
    tl_stop('Kan de map ' . $uitmap . ' niet maken.');
}
$odt = rtrim($uitmap, '/') . '/' . cm_bestandsnaam($naam, 'odt');
$antwoordbestand = rtrim($uitmap, '/') . '/' . cm_bestandsnaam($naam, 'json');

file_put_contents($odt, cm_odt($blokken, (string) ($a['aanvrager'] ?? ''), $kenmerk));
file_put_contents($antwoordbestand, json_encode([
    'soort' => $formulier['id'],
    'projectnaam' => $naam,
    'opgeslagen_op' => (new DateTimeImmutable('now'))->format(DATE_ATOM),
    'kenmerk' => $kenmerk,
    'vingerafdruk' => $vingerafdruk,
    'antwoorden' => $a,
] + (isset($formulier['vervolg']) ? ['vervolgformulieren' => $formulier['vervolg']($a, $uitkomst)] : []),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");

echo "Gemaakt:\n  $odt\n  $antwoordbestand\nKenmerk: $kenmerk\n";

// 3. Wat in de tekst opvalt. Alleen bij vragen die meetellen.
$tekstMeldingen = [];
$open = 0;
$bekijk = static function (string $waar, string $tekst, bool $kort) use (&$tekstMeldingen, &$open): void {
    $open += preg_match_all('/te bevestigen/iu', $tekst);
    foreach (cm_regels($tekst) as $regel) {
        if (preg_match('/^\s*([-*•–]|\d+[.)])\s/u', $regel)) {
            $tekstMeldingen[] = "$waar: een regel begint met een streepje of nummer. Zet elk punt zonder teken op een eigen regel.";
        }
        if (preg_match('/:\s*$/u', $regel)) {
            $tekstMeldingen[] = "$waar: een regel eindigt op een dubbele punt.";
        }
        if ($kort && preg_match('/[^.]\.$/u', $regel)) {
            $tekstMeldingen[] = "$waar: een kort veld eindigt op een punt.";
        }
        if (!$kort && mb_strlen($regel) > 350) {
            $tekstMeldingen[] = "$waar: een alinea van " . mb_strlen($regel) . ' tekens. Splits hem: één gedachte per regel, '
                . 'of een opsomming met één punt per regel.';
        }
        if (str_contains($regel, '  ')) {
            $tekstMeldingen[] = "$waar: twee spaties achter elkaar.";
        }
    }
};

foreach ($vragen as $key => $vraag) {
    $waarde = $a[$key] ?? '';
    if (in_array($vraag['type'], ['text', 'textarea'], true) && is_string($waarde) && $waarde !== '') {
        $bekijk($key, $waarde, $vraag['type'] === 'text');
    }
    if ($vraag['type'] === 'rijen') {
        foreach ($waarde as $i => $rij) {
            foreach ($vraag['kolommen'] as $kolom => $def) {
                if ($def['type'] !== 'select' && ($rij[$kolom] ?? '') !== '') {
                    $bekijk("$key, rij " . ($i + 1) . ", $kolom", $rij[$kolom], $def['type'] === 'text');
                }
            }
        }
    }
}

// Het document zet soms zelf een punt of dubbele punt achter een antwoord. Dan staat er twee keer een teken.
foreach ($blokken as $blok) {
    $tekst = tl_blok_tekst($blok);
    if (preg_match_all('/.{0,40}(?<!\.)(\.\.|[:;,]\.|\.:|::)(?!\.).{0,20}/u', $tekst, $treffers)) {
        foreach ($treffers[0] as $stuk) {
            $tekstMeldingen[] = 'document: twee leestekens achter elkaar in ‘' . trim($stuk) . '’.';
        }
    }
}

$alles = array_merge($meldingen, array_values(array_unique($tekstMeldingen)));
echo $alles === [] ? "Niets opvallends in de antwoorden.\n" : "Let op:\n- " . implode("\n- ", $alles) . "\n";
if ($open > 0) {
    echo "Nog $open keer ‘te bevestigen’ in de antwoorden.\n";
}

// De titelpagina: de uitkomst in het kort.
echo "\nTitelpagina:\n";
foreach ($blokken as $blok) {
    if ($blok[0] === 'h2') {
        break;
    }
    echo '  ', tl_blok_tekst($blok), "\n";
}

if (in_array('tekst', $vlaggen, true) || in_array('alles', $vlaggen, true)) {
    echo "\n";
    foreach ($blokken as $blok) {
        if ($blok[0] === 'h2' && $blok[1] === 'Verantwoording' && !in_array('alles', $vlaggen, true)) {
            echo "## Verantwoording (weggelaten, zie --alles)\n";
            break;
        }
        echo tl_blok_tekst($blok), "\n";
    }
}

if (in_array('pdf', $vlaggen, true)) {
    exec('soffice --headless --convert-to pdf --outdir ' . escapeshellarg($uitmap) . ' ' . escapeshellarg($odt)
        . ' 2>&1', $uit, $code);
    echo $code === 0 ? "PDF gemaakt in $uitmap.\n" : "Geen PDF: LibreOffice gaf een fout.\n" . implode("\n", $uit) . "\n";
}

/** Eén documentblok als platte tekst, met # voor koppen en | tussen cellen. */
function tl_blok_tekst(array $blok): string
{
    $cel = static fn (string|array $inhoud): string => implode(' / ', (array) $inhoud);

    return match ($blok[0]) {
        'h1' => '# ' . $blok[1],
        'h2' => "\n## " . $blok[1],
        'h3' => "\n### " . $blok[1],
        'h4' => '#### ' . $blok[1],
        'term' => $blok[1] . ': ' . $blok[2],
        'lijst', 'genummerd' => implode("\n", array_map(static fn (string $i): string => '• ' . $i, $blok[1])),
        'citaat' => '> ' . $blok[1],
        'lijn' => '---',
        'tabel' => implode("\n", array_map(
            static fn (array $rij): string => '| ' . implode(' | ', array_map($cel, array_values($rij))),
            [...(empty($blok[1]['kop']) ? [] : [$blok[1]['kop']]), ...$blok[1]['rijen']]
        )),
        'schema' => '[schema: ' . count($blok[1]) . ' stromen]',
        default => (string) ($blok[1] ?? ''),
    };
}
