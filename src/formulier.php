<?php

/**
 * Het formulier als HTML.
 *
 * De vragen komen uit vragen.php van het formulier. Elke vraag krijgt
 * data-attributen mee, zodat assets/app.js weet wanneer hij getoond wordt en
 * of hij ingevuld moet zijn. De server controleert dat daarna nog een keer.
 *
 * Keuzegroepen en rijen zijn een fieldset met legend, losse velden een label
 * met for. Hints en foutmeldingen hangen via aria-describedby aan het veld.
 * Geen klassen: app.css kiest op element en op de data-attributen.
 */

declare(strict_types=1);

/** De voorwaarden van een sectie, vraag of lijstitem als data-attributen. */
function cm_data_regels(array $item): string
{
    $out = '';

    foreach (['toon_als', 'toon_als_uitkomst', 'verplicht_als', 'verplicht_als_uitkomst'] as $sleutel) {
        if (!empty($item[$sleutel])) {
            $out .= ' data-' . str_replace('_', '-', $sleutel) . '="' . h((string) json_encode($item[$sleutel])) . '"';
        }
    }

    return $out;
}

/**
 * @param array<string,mixed> $sectie
 * @param array<string,mixed> $a
 * @param array<string,string> $fouten
 */
function cm_render_sectie(array $sectie, array $a, array $fouten, int $nummer): string
{
    // Vragen met dezelfde groep worden samen één regel die openklapt.
    $delen = [];
    foreach ($sectie['vragen'] as $vraag) {
        $delen[$vraag['groep'] ?? $vraag['key']][] = $vraag;
    }

    $hoofd = '';
    $aanvullend = '';
    $foutInAanvullend = false;

    foreach ($delen as $deel) {
        if (isset($deel[0]['groep'])) {
            $hoofd .= cm_render_groep($deel, $a, $fouten);
        } elseif (empty($deel[0]['optioneel'])) {
            $hoofd .= cm_render_vraag($deel[0], $a, $fouten);
        } else {
            $aanvullend .= cm_render_vraag($deel[0], $a, $fouten);
            $foutInAanvullend = $foutInAanvullend || isset($fouten[$deel[0]['key']]);
        }
    }

    // Het stempel boven de titel: wie deze stap invult.
    $stap = (int) ($sectie['stap'] ?? 0);
    $stempel = cm_formulier()['stappen'][$stap] ?? '';

    $out = '<section id="sectie-' . h($sectie['id']) . '" data-sectie="' . h($sectie['id']) . '"'
        . ($stap > 0 ? ' data-stap="' . $stap . '"' : '') . cm_data_regels($sectie) . ' tabindex="-1"><header>'
        . ($stempel === '' ? '' : '<p>' . h($stempel) . '</p>')
        . '<h2><span data-nummer>' . $nummer . '.</span> ' . h($sectie['titel']) . '</h2>';

    if (!empty($sectie['intro'])) {
        $out .= '<p>' . h($sectie['intro']) . '</p>';
    }

    return $out . '</header>' . $hoofd
        . cm_uitklapblok($aanvullend, (string) ($sectie['aanvullend'] ?? 'Aanvullende gegevens'), $foutInAanvullend)
        . '</section>';
}

/** Optionele vragen in een uitklapblok; niets als er geen zijn. */
function cm_uitklapblok(string $inhoud, string $label, bool $open): string
{
    return $inhoud === ''
        ? ''
        : '<details' . ($open ? ' open' : '') . '><summary>' . h($label) . ' <small>optioneel</small></summary>'
            . $inhoud . '</details>';
}

/**
 * Een groep vragen, zoals een risico, als regel die openklapt. De kop geeft de
 * titel, de uitleg en de voorwaarden. Met 'stand' noemt de kop een functie die
 * de samenvatting op de regel geeft, en zegt of de regel open moet. Zonder
 * script staat een regel met een fout altijd open.
 *
 * @param list<array<string,mixed>> $vragen
 */
function cm_render_groep(array $vragen, array $a, array $fouten): string
{
    $kop = array_shift($vragen);
    $id = (string) $kop['groep'];
    $stand = isset($kop['stand'])
        ? $kop['stand']($id, $a)
        : ['samenvatting' => '', 'niveau' => '', 'open' => true];
    $fout = array_intersect_key($fouten, array_flip(array_column($vragen, 'key'))) !== [];

    $out = '<details id="vraag-' . h($kop['key']) . '" data-vraag="' . h($kop['key']) . '" data-type="groep" data-groep="'
        . h($id) . '"' . cm_data_regels($kop) . ($fout || $stand['open'] ? ' open' : '') . '><summary data-niveau="'
        . h($stand['niveau']) . '"><span>' . h($kop['label']) . '</span> <output data-samenvatting>'
        . h($stand['samenvatting']) . '</output></summary>';

    if (!empty($kop['hint'])) {
        $out .= '<p>' . h($kop['hint']) . '</p>';
    }

    $label = (string) ($kop['aanvullend'] ?? 'Aanvullende gegevens');
    $los = '';
    $losOpen = false;

    foreach ($vragen as $vraag) {
        if (!empty($vraag['optioneel'])) {
            $los .= cm_render_vraag($vraag, $a, $fouten);
            $losOpen = $losOpen || ($a[$vraag['key']] ?? '') !== '' || isset($fouten[$vraag['key']]);
            continue;
        }

        $out .= cm_uitklapblok($los, $label, $losOpen) . cm_render_vraag($vraag, $a, $fouten);
        $los = '';
        $losOpen = false;
    }

    return $out . cm_uitklapblok($los, $label, $losOpen) . '</details>';
}

/**
 * @param array<string,mixed> $vraag
 * @param array<string,mixed> $a
 * @param array<string,string> $fouten
 */
function cm_render_vraag(array $vraag, array $a, array $fouten): string
{
    $key = $vraag['key'];
    $type = $vraag['type'];

    $attrs = ' id="vraag-' . h($key) . '" data-vraag="' . h($key) . '" data-type="' . h($type) . '"'
        . cm_data_regels($vraag);

    foreach (['voorstel', 'verplicht'] as $vlag) {
        if (!empty($vraag[$vlag])) {
            $attrs .= ' data-' . $vlag;
        }
    }
    foreach (['motivatie_voor', 'opties_uit'] as $sleutel) {
        if (!empty($vraag[$sleutel])) {
            $attrs .= ' data-' . str_replace('_', '-', $sleutel) . '="' . h((string) $vraag[$sleutel]) . '"';
        }
    }
    if (!empty($vraag['groepen'])) {
        $attrs .= ' data-groepen="' . h((string) json_encode($vraag['groepen'])) . '"';
    }

    if ($type === 'kop') {
        return cm_render_kop($vraag, $attrs);
    }
    if ($type === 'afgeleid') {
        return cm_render_afgeleid($vraag, $attrs, $a);
    }

    $fout = $fouten[$key] ?? null;
    $lijst = in_array($type, ['checkbox', 'rijen', 'maatregelen'], true);
    $waarde = $a[$key] ?? ($lijst ? [] : (string) ($vraag['standaard'] ?? ''));

    $beschrijving = array_filter([
        empty($vraag['hint']) ? '' : 'hint-' . $key,
        $fout === null ? '' : 'fout-' . $key,
    ]);
    $veld = ($beschrijving === [] ? '' : ' aria-describedby="' . h(implode(' ', $beschrijving)) . '"')
        . ($fout === null ? '' : ' aria-invalid="true"');

    $optioneel = empty($vraag['verplicht']) && empty($vraag['verplicht_als']) && empty($vraag['verplicht_als_uitkomst']);
    $kop = h($vraag['label']) . ($optioneel ? ' <small>optioneel</small>' : '');
    $groep = in_array($type, ['radio', 'checkbox', 'rijen', 'maatregelen'], true);

    $out = $groep
        ? '<fieldset' . $attrs . ' tabindex="-1"><legend>' . $kop . '</legend>'
        : '<div' . $attrs . ' tabindex="-1"><label for="' . h($key) . '">' . $kop . '</label>';

    if (!empty($vraag['hint'])) {
        $out .= '<p id="hint-' . h($key) . '">' . h($vraag['hint']) . '</p>';
    }

    $out .= match ($type) {
        'radio', 'checkbox' => cm_render_keuzes($vraag, $waarde, $veld),
        'maatregelen' => cm_render_maatregelen($vraag, (array) $waarde, $veld),
        'rijen' => cm_render_rijen($vraag, (array) $waarde, $veld, $a),
        'select' => cm_select($key, $key, $vraag['opties'], (string) $waarde, $veld),
        'textarea' => cm_textarea($key, $key, (string) $waarde, (int) ($vraag['hoogte'] ?? 3), $veld),
        default => cm_invoerveld($key, $key, $type === 'email' ? 'email' : 'text', (string) $waarde, $veld),
    };

    $out .= '<p id="fout-' . h($key) . '" data-fout' . ($fout === null ? ' hidden>' : '>' . h($fout)) . '</p>';

    return $out . ($groep ? '</fieldset>' : '</div>');
}

/** Een tussenkop met uitleg. */
function cm_render_kop(array $vraag, string $attrs): string
{
    $out = '<div' . $attrs . '><h3>' . h($vraag['label']) . '</h3>';

    if (!empty($vraag['hint'])) {
        $out .= '<p>' . h($vraag['hint']) . '</p>';
    }
    if (!empty($vraag['lijst'])) {
        $out .= '<ul>' . implode('', array_map(static fn (string $r): string => '<li>' . h($r) . '</li>', $vraag['lijst'])) . '</ul>';
    }

    return $out . '</div>';
}

/**
 * Een uitkomstblok. Met 'lijst' is het een vaste lijst met voorwaarden, die
 * app.js toont of verbergt zonder te rekenen. Anders vult regels.js van het
 * formulier de waarde en de redenen tijdens het invullen.
 */
function cm_render_afgeleid(array $vraag, string $attrs, array $a): string
{
    $out = '<div data-afgeleid="' . h((string) ($vraag['bron'] ?? '')) . '"' . $attrs . '><p><strong>'
        . h($vraag['label']) . '</strong>';

    if (isset($vraag['lijst'])) {
        $out .= '</p><ul>';
        foreach ($vraag['lijst'] as $item) {
            $out .= '<li' . cm_data_regels($item) . (cm_zichtbaar($item, $a) ? '' : ' hidden') . '>'
                . h($item['tekst']) . '</li>';
        }

        return $out . '</ul></div>';
    }

    $out .= ' <output data-afgeleid-waarde>nog te bepalen</output></p>';

    if (!empty($vraag['hint'])) {
        $out .= '<p>' . h($vraag['hint']) . '</p>';
    }

    return $out . '<ul data-afgeleid-redenen><li>Vul de vragen hierboven in, dan verschijnt hier de uitkomst.</li></ul></div>';
}

/**
 * Keuzes. Bij een radiovraag met een voorstel markeert app.js de voorgestelde
 * keuze. Een optie met 'uitleg' krijgt de volledige omschrijving eronder.
 */
function cm_render_keuzes(array $vraag, mixed $waarde, string $veld): string
{
    $key = $vraag['key'];
    $type = $vraag['type'];
    $naam = $type === 'checkbox' ? $key . '[]' : $key;
    $gekozen = array_map('strval', (array) $waarde);
    $merk = $type === 'radio' && !empty($vraag['voorstel']) ? ' <mark hidden>voorgesteld</mark>' : '';

    $out = '';
    $huidigeGroep = null;
    $nummer = 0;

    foreach ($vraag['opties'] as $optieWaarde => $optie) {
        $groep = $optie['groep'] ?? null;

        if ($groep !== $huidigeGroep) {
            $out .= ($huidigeGroep === null ? '' : '</fieldset>')
                . ($groep === null ? '' : '<fieldset><legend>' . h($groep) . '</legend>');
            $huidigeGroep = $groep;
        }

        $id = $key . '-' . $nummer++;

        $out .= '<label for="' . h($id) . '"><input type="' . $type . '" name="' . h($naam) . '" id="' . h($id)
            . '" value="' . h((string) $optieWaarde) . '"' . $veld
            . (in_array((string) $optieWaarde, $gekozen, true) ? ' checked' : '') . '>'
            . '<span>' . h($optie['label']) . $merk . '</span>';

        if (!empty($optie['hint'])) {
            $out .= '<small>' . h($optie['hint']) . '</small>';
        }

        $out .= '</label>';

        if (!empty($optie['uitleg'])) {
            $out .= '<details><summary>Volledige omschrijving van ‘' . h($optie['label']) . '’</summary><p>'
                . h($optie['uitleg']) . '</p></details>';
        }
    }

    return $out . ($huidigeGroep === null ? '' : '</fieldset>');
}

/**
 * Per maatregel drie keuzes: is er al, komt er, of niet nodig.
 *
 * @param array<string,string> $gekozen maatregel => keuze
 */
function cm_render_maatregelen(array $vraag, array $gekozen, string $veld): string
{
    $out = '';

    foreach ($vraag['opties'] as $sleutel => $optie) {
        $out .= '<fieldset data-maatregel><legend>' . h($optie['label']) . '</legend>';

        foreach (CM_MAATREGELKEUZES as $keuze => $label) {
            $id = $vraag['key'] . '-' . $sleutel . '-' . $keuze;
            $out .= '<label for="' . h($id) . '"><input type="radio" name="' . h($vraag['key'] . '[' . $sleutel . ']')
                . '" id="' . h($id) . '" value="' . $keuze . '"' . $veld
                . (($gekozen[$sleutel] ?? '') === $keuze ? ' checked' : '') . '><span>' . h($label) . '</span></label>';
        }

        $out .= '</fieldset>';
    }

    return $out;
}

/**
 * Een lijst met rijen. Zonder script staan er twee lege rijen extra onder. Het
 * script haalt die weg, en zet er een knop voor een nieuwe rij voor in de plaats.
 * Met 'suggesties' noemt de vraag een functie die namen geeft voor bij de velden.
 *
 * @param list<array<string,string>> $rijen
 */
function cm_render_rijen(array $vraag, array $rijen, string $veld, array $a): string
{
    $aantal = min((int) (cm_config()['max_rijen'] ?? 40), max(1, count($rijen)) + 2);
    $out = '';

    for ($i = 0; $i < $aantal; $i++) {
        $out .= cm_render_rij($vraag, $i, $rijen[$i] ?? [], $veld);
    }

    if (!empty($vraag['suggesties'])) {
        $out .= '<datalist id="' . h($vraag['key']) . '-suggesties" data-suggesties>'
            . implode('', array_map(static fn (string $naam): string => '<option>' . h($naam) . '</option>', $vraag['suggesties']($a)))
            . '</datalist>';
    }

    return $out . '<p><button type="button" data-rij-erbij hidden>' . h($vraag['erbij']) . '</button></p>';
}

/** @param array<string,string> $rij */
function cm_render_rij(array $vraag, int $index, array $rij, string $veld): string
{
    $key = $vraag['key'];
    $out = '<fieldset data-rij><legend>' . h($vraag['rij_label']) . ' ' . ($index + 1) . '</legend>';

    foreach ($vraag['kolommen'] as $kolom => $def) {
        $id = $key . '-' . $index . '-' . $kolom;
        $naam = $key . '[' . $index . '][' . $kolom . ']';
        $waarde = (string) ($rij[$kolom] ?? '');
        $attrs = $veld . (empty($def['verplicht']) ? '' : ' data-verplicht')
            . (empty($def['suggesties']) ? '' : ' list="' . h($key) . '-suggesties"');

        $out .= '<label for="' . h($id) . '">' . h($def['label']) . match ($def['type']) {
            'select' => cm_select($naam, $id, $def['opties'], $waarde, $attrs),
            'textarea' => cm_textarea($naam, $id, $waarde, 2, $attrs),
            default => cm_invoerveld($naam, $id, 'text', $waarde, $attrs),
        } . '</label>';
    }

    return $out . '<button type="button" data-rij-weg hidden>Haal deze '
        . h(mb_strtolower($vraag['rij_label'], 'UTF-8')) . ' weg</button></fieldset>';
}

/** @param array<string,array<string,string>> $opties */
function cm_select(string $naam, string $id, array $opties, string $waarde, string $veld): string
{
    $out = '<select name="' . h($naam) . '" id="' . h($id) . '"' . $veld . '>';

    foreach ($opties as $optieWaarde => $optie) {
        $out .= '<option value="' . h((string) $optieWaarde) . '"'
            . ((string) $optieWaarde === $waarde ? ' selected' : '') . '>' . h($optie['label']) . '</option>';
    }

    return $out . '</select>';
}

function cm_textarea(string $naam, string $id, string $waarde, int $hoogte, string $veld): string
{
    return '<textarea name="' . h($naam) . '" id="' . h($id) . '" rows="' . $hoogte . '"' . $veld . '>'
        . h($waarde) . '</textarea>';
}

function cm_invoerveld(string $naam, string $id, string $type, string $waarde, string $veld): string
{
    return '<input type="' . $type . '" name="' . h($naam) . '" id="' . h($id) . '" value="' . h($waarde) . '"' . $veld . '>';
}
