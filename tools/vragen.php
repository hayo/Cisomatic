<?php

/**
 * Alle vragen van een formulier, kort: één regel per vraag. Zo hoef je de
 * PHP-bestanden niet te lezen om een antwoordbestand te schrijven.
 *
 *   php tools/vragen.php dpia              de vragen
 *   php tools/vragen.php dpia --uitleg     met de hints en de uitleg bij keuzes
 *   php tools/vragen.php dpia --sectie=risicos,conclusie   alleen deze secties
 *   php tools/vragen.php dpia --sjabloon   een leeg antwoorden.json, om in te vullen
 *
 * Per vraag: sleutel, type, * als hij altijd verplicht is, de voorwaarden, de
 * vraag zelf en de keuzes als waarde=label.
 */

declare(strict_types=1);

require __DIR__ . '/laad.php';

[$vlaggen, $rest] = tl_argumenten($argv);
if ($rest === []) {
    tl_stop("Gebruik: php tools/vragen.php <formulier> [--uitleg] [--sectie=id,id] [--sjabloon]\nFormulieren: "
        . implode(', ', cm_config()['formulieren']));
}

tl_formulier($rest[0]);
$uitleg = in_array('uitleg', $vlaggen, true);

if (in_array('sjabloon', $vlaggen, true)) {
    $sjabloon = [];
    foreach (cm_alle_vragen() as $key => $vraag) {
        if (!cm_is_invoer($vraag)) {
            continue;
        }
        $sjabloon[$key] = match ($vraag['type']) {
            'checkbox' => [],
            'maatregelen' => new stdClass(),
            'rijen' => [array_map(static fn (): string => '', $vraag['kolommen'])],
            default => (string) ($vraag['standaard'] ?? ''),
        };
    }
    echo json_encode($sjabloon, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
    exit;
}

$enkel = static fn (string $t): string => trim((string) preg_replace('/\s+/u', ' ', $t));

/** De keuzes als waarde=label, of met --uitleg elk op een eigen regel. */
$keuzes = static function (array $opties, string $inspring) use ($uitleg, $enkel): string {
    if (!$uitleg) {
        return implode(' | ', array_map(
            static fn ($w, array $o): string => ($w === '' ? '""' : $w) . '=' . $enkel((string) ($o['label'] ?? '')),
            array_keys($opties),
            array_values($opties)
        ));
    }

    $regels = [];
    foreach ($opties as $w => $o) {
        $extra = $enkel(trim(($o['hint'] ?? '') . ' ' . ($o['uitleg'] ?? '')));
        $regels[] = $inspring . '- ' . ($w === '' ? '""' : $w) . ' = ' . $enkel((string) ($o['label'] ?? ''))
            . ($extra === '' ? '' : ' — ' . $extra);
    }

    return "\n" . implode("\n", $regels);
};

/** Een lijst keuzes die al eerder stond, noemt alleen de vraag waar hij stond. */
$gezien = [];
$herhaal = static function (string $waar, string $lijst, string $los = "\n   ") use (&$gezien, $uitleg): string {
    if (isset($gezien[$lijst])) {
        return '  keuzes als ' . $gezien[$lijst];
    }
    $gezien[$lijst] = $waar;

    return ($uitleg ? '' : $los) . $lijst;
};

$formulier = cm_formulier();
echo '# ', $formulier['titel'], ' (', $formulier['id'], ")\n";
echo "Per vraag: sleutel, type, * = altijd verplicht, ?als = alleen bij, !als = verplicht bij.\n";
echo "Antwoorden: tekst als string, checkbox als lijst, maatregelen als {maatregel: al|komt|nee},\n";
echo "rijen als lijst van objecten met de kolommen. (voorstel): in de browser vult het formulier\n";
echo "die vraag voor, hier vul je hem zelf in. Opties uit X: alleen wat bij X is aangekruist.\n";

$alleen = null;
foreach ($vlaggen as $vlag) {
    if (str_starts_with($vlag, 'sectie=')) {
        $alleen = explode(',', substr($vlag, 7));
    }
}

foreach (cm_secties() as $sectie) {
    if ($alleen !== null && !in_array((string) ($sectie['id'] ?? ''), $alleen, true)) {
        continue;
    }
    $voorwaarde = tl_voorwaarde($sectie);
    echo "\n## ", $sectie['id'] ?? '', ' · ', $enkel((string) ($sectie['titel'] ?? '')),
        $voorwaarde === '' ? '' : '  ?als ' . $voorwaarde, "\n";

    foreach ($sectie['vragen'] as $vraag) {
        $label = $enkel((string) ($vraag['label'] ?? ''));

        if ($vraag['type'] === 'kop') {
            echo '-- ', $label, "\n";
            continue;
        }

        $delen = [$vraag['key'], $vraag['type'] . (!empty($vraag['verplicht']) ? '*' : '')];
        if (($v = tl_voorwaarde($vraag)) !== '') {
            $delen[] = '?als ' . $v;
        }
        if (($v = tl_voorwaarde($vraag, 'verplicht_als')) !== '') {
            $delen[] = '!als ' . $v;
        }
        if (!empty($vraag['opties_uit'])) {
            $delen[] = 'opties uit ' . $vraag['opties_uit'];
        }
        if ($vraag['type'] === 'afgeleid') {
            $delen[] = '(rekent het formulier uit, niet invullen)';
        }
        if (isset($vraag['voorstel']) || isset($vraag['motivatie_voor'])) {
            $delen[] = '(voorstel)';
        }

        echo implode('  ', $delen), '  "', $label, '"';
        if ($uitleg && !empty($vraag['hint'])) {
            echo ' — ', $enkel((string) $vraag['hint']);
        }

        if (isset($vraag['opties']) && $vraag['type'] !== 'afgeleid' && empty($vraag['opties_uit'])) {
            echo $herhaal($vraag['key'], $keuzes($vraag['opties'], '   '));
        }

        foreach ($vraag['kolommen'] ?? [] as $kolom => $def) {
            echo "\n   . ", $kolom, '  ', $def['type'], !empty($def['verplicht']) ? '*' : '', '  "',
                $enkel((string) ($def['label'] ?? '')), '"';
            if (isset($def['opties'])) {
                echo $herhaal($vraag['key'] . '.' . $kolom, $keuzes($def['opties'], '      '), '  ');
            }
        }
        echo "\n";
    }
}
