<?php

/**
 * Het document als HTML, voor op de pagina.
 *
 * Een formulier levert zijn document als blokken: [soort, inhoud]. Soorten: h1
 * tot en met h4, alinea, term (label en tekst), lijst, genummerd, citaat, lijn,
 * tabel, en schema (alleen op de pagina). src/odt.php maakt van dezelfde
 * blokken het .odt-bestand, zodat pagina en document niet uit elkaar lopen.
 *
 * Alle tekst is platte tekst en gaat door h(), dus er kan geen HTML uit de
 * antwoorden in de pagina. Nadruk zit in de soort van het blok.
 */

declare(strict_types=1);

/**
 * @param list<array<int,mixed>> $blokken
 * @return array{html:string,inhoud:list<array{id:string,titel:string}>}
 */
function cm_blokken_naar_html(array $blokken): array
{
    $html = '';
    $inhoud = [];

    foreach ($blokken as $blok) {
        if ($blok[0] === 'h2') {
            $id = 'hoofdstuk-' . (count($inhoud) + 1);
            $inhoud[] = ['id' => $id, 'titel' => $blok[1]];
            $html .= '<h3 id="' . $id . '">' . h($blok[1]) . "</h3>\n";
            continue;
        }

        $html .= match ($blok[0]) {
            'h1' => '<h2>' . h($blok[1]) . '</h2>',
            'h3' => '<h4>' . h($blok[1]) . '</h4>',
            'h4' => '<h5>' . h($blok[1]) . '</h5>',
            'term' => '<p><strong>' . h($blok[1]) . ':</strong> ' . h($blok[2]) . '</p>',
            'lijst' => '<ul>' . cm_items_html($blok[1]) . '</ul>',
            'genummerd' => '<ol>' . cm_items_html($blok[1]) . '</ol>',
            'citaat' => '<blockquote>' . h($blok[1]) . '</blockquote>',
            'lijn' => '<hr>',
            'tabel' => cm_tabel_html($blok[1]),
            'schema' => cm_schema_svg($blok[1], $blok[2]),
            default => '<p>' . h($blok[1]) . '</p>',
        } . "\n";
    }

    return ['html' => $html, 'inhoud' => $inhoud];
}

/** @param list<string> $items */
function cm_items_html(array $items): string
{
    return implode('', array_map(static fn (string $item): string => '<li>' . h($item) . '</li>', $items));
}

/** De titel van het document: het eerste h1-blok. */
function cm_documenttitel(array $blokken): string
{
    foreach ($blokken as $blok) {
        if ($blok[0] === 'h1') {
            return (string) $blok[1];
        }
    }

    return (string) cm_formulier()['titel'];
}

/** Een tabel. Een cel met meer regels krijgt een regeleinde tussen de regels. */
function cm_tabel_html(array $tabel): string
{
    $cel = static fn (string|array $inhoud): string => implode('<br>', array_map('h', (array) $inhoud));
    $out = '<figure><table data-kolommen="' . count($tabel['breedtes']) . '">';

    if (!empty($tabel['kop'])) {
        $out .= '<thead><tr>' . implode('', array_map(
            static fn (string $kop): string => '<th scope="col">' . h($kop) . '</th>',
            $tabel['kop']
        )) . '</tr></thead>';
    }

    $out .= '<tbody>';
    foreach ($tabel['rijen'] as $rij) {
        $out .= '<tr>';
        foreach (array_values($rij) as $i => $inhoud) {
            $out .= $i === 0 && !empty($tabel['rijkop'])
                ? '<th scope="row">' . $cel($inhoud) . '</th>'
                : '<td>' . $cel($inhoud) . '</td>';
        }
        $out .= '</tr>';
    }

    return $out . '</tbody></table></figure>';
}

/**
 * Een eenvoudig schema van stromen tussen partijen, als SVG.
 *
 * Elke partij wordt een blok. Een blok staat een kolom rechts van de partij
 * waar zijn gegevens vandaan komen, zodat de stromen van links naar rechts
 * lopen. Een stroom terug loopt onderlangs. De nummers verwijzen naar de
 * tabel onder het schema.
 *
 * @param list<array<string,string>> $stromen van, naar en scope (binnen of buiten)
 */
function cm_schema_svg(array $stromen, string $onderschrift): string
{
    if ($stromen === []) {
        return '';
    }

    // Maat van een blok, ruimte tussen de kolommen en de rijen, en de marge.
    [$b, $h, $tussenX, $tussenY, $marge] = [200, 56, 110, 34, 24];

    // Blokken op volgorde van verschijnen. Een stroom naar een blok dat later
    // verschijnt, schuift dat blok een kolom op. Een stroom terug telt niet
    // mee, dus een kring in de stromen kan de indeling niet oprekken.
    $volgorde = [];
    foreach ($stromen as $s) {
        $volgorde[$s['van']] ??= count($volgorde);
        $volgorde[$s['naar']] ??= count($volgorde);
    }

    $kolom = array_fill_keys(array_keys($volgorde), 0);
    foreach (array_keys($volgorde) as $naam) {
        foreach ($stromen as $s) {
            if ((string) $s['naar'] === (string) $naam && $volgorde[$s['van']] < $volgorde[$naam]) {
                $kolom[$naam] = max($kolom[$naam], $kolom[$s['van']] + 1);
            }
        }
    }

    $perKolom = [];
    foreach ($kolom as $naam => $k) {
        $perKolom[$k][] = (string) $naam;
    }
    ksort($perKolom);
    $perKolom = array_values($perKolom);

    $plek = [];
    $blokken = '';
    foreach ($perKolom as $k => $namen) {
        foreach ($namen as $rij => $naam) {
            [$x, $y] = $plek[$naam] = [$marge + $k * ($b + $tussenX), $marge + $rij * ($h + $tussenY)];
            $kort = mb_strlen($naam, 'UTF-8') > 24 ? mb_substr($naam, 0, 23, 'UTF-8') . '…' : $naam;

            $blokken .= '<g><title>' . h($naam) . '</title><rect x="' . $x . '" y="' . $y . '" width="' . $b
                . '" height="' . $h . '" rx="8"/><text x="' . ($x + $b / 2) . '" y="' . ($y + $h / 2)
                . '" text-anchor="middle" dominant-baseline="central">' . h($kort) . '</text></g>';
        }
    }

    $breedte = 2 * $marge + count($perKolom) * $b + (count($perKolom) - 1) * $tussenX;
    $hoogte = 2 * $marge + max(array_map('count', $perKolom)) * ($h + $tussenY) - $tussenY;

    $lijnen = '';
    $nummers = '';
    $paren = [];
    $onderkant = $hoogte - $marge;

    foreach (array_values($stromen) as $i => $s) {
        [$x1, $y1] = $plek[$s['van']];
        [$x2, $y2] = $plek[$s['naar']];
        $paar = $s['van'] . "\n" . $s['naar'];
        $dubbel = $paren[$paar] = ($paren[$paar] ?? -1) + 1;

        if ($x2 > $x1) {
            // Vooruit: van de rechterkant van het ene blok naar de linkerkant van het andere.
            [$ax, $ay, $bx, $by] = [$x1 + $b, $y1 + $h / 2, $x2, $y2 + $h / 2];
            $mx = ($ax + $bx) / 2;
            $pad = "M$ax $ay C$mx $ay $mx $by $bx $by";
            [$nx, $ny] = [$mx, ($ay + $by) / 2 + $dubbel * 26];
        } else {
            // Terug of in dezelfde kolom: onder alle blokken langs, zodat de
            // lijn geen blok in een andere rij doorsnijdt.
            [$ax, $ay] = [$x1 + $b / 2, $y1 + $h];
            [$bx, $by] = [$x2 + $b / 2 + ($s['van'] === $s['naar'] ? 40 : 0), $y2 + $h];
            $dal = $onderkant + 40 + $dubbel * 22;
            $pad = "M$ax $ay C$ax $dal $bx $dal $bx $by";
            [$nx, $ny] = [($ax + $bx) / 2, ($ay + $by + 6 * $dal) / 8];
            $hoogte = max($hoogte, $dal + $marge);
        }

        $lijnen .= '<path d="' . $pad . '" data-scope="' . ($s['scope'] === 'buiten' ? 'buiten' : 'binnen')
            . '" marker-end="url(#schema-pijl)"/>';
        $nummers .= '<circle cx="' . $nx . '" cy="' . $ny . '" r="12"/><text x="' . $nx . '" y="' . $ny
            . '" text-anchor="middle" dominant-baseline="central">' . ($i + 1) . '</text>';
    }

    return '<figure><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $breedte . ' ' . $hoogte . '" width="'
        . round($breedte / 16, 1) . 'em" role="img" aria-labelledby="schema-titel">'
        . '<title id="schema-titel">Schema van de gegevensstromen</title>'
        . '<defs><marker id="schema-pijl" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="8" markerHeight="8" '
        . 'orient="auto-start-reverse"><path d="M0 0L10 5L0 10z"/></marker></defs>'
        . $lijnen . $blokken . $nummers . '</svg>'
        . '<figcaption>' . h($onderschrift) . '</figcaption></figure>';
}
