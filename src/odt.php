<?php

/**
 * Het document als OpenDocument-tekst (.odt), zonder PHP-extensies.
 *
 * Een .odt is een zip met een handvol XML-bestanden. De zip schrijven we zelf
 * en ongecomprimeerd, met alleen pack() en crc32(). Word, LibreOffice en Google
 * Docs openen het met echte kopjes, lijsten en tabellen.
 */

declare(strict_types=1);

const CM_ODT_NS = 'xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" '
    . 'xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" '
    . 'xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" '
    . 'xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" '
    . 'xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" '
    . 'xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" '
    . 'xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" '
    . 'xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" '
    . 'xmlns:xlink="http://www.w3.org/1999/xlink" '
    . 'xmlns:dc="http://purl.org/dc/elements/1.1/" office:version="1.2"';

const CM_ODT_FONTS = '<office:font-face-decls>'
    . '<style:font-face style:name="Calibri" svg:font-family="Calibri" style:font-family-generic="swiss" style:font-pitch="variable"/>'
    . '</office:font-face-decls>';

const CM_ODT_MANIFEST = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.2">'
    . '<manifest:file-entry manifest:full-path="/" manifest:version="1.2" manifest:media-type="application/vnd.oasis.opendocument.text"/>'
    . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
    . '<manifest:file-entry manifest:full-path="styles.xml" manifest:media-type="text/xml"/>'
    . '<manifest:file-entry manifest:full-path="meta.xml" manifest:media-type="text/xml"/>'
    . '%s</manifest:manifest>';

// Stijlnamen volgen LibreOffice ("Heading_20_1" is "Heading 1"), zodat Word en
// LibreOffice ze als hun eigen kopjes herkennen en de navigatie ze toont.
const CM_ODT_STIJLEN = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<office:document-styles ' . CM_ODT_NS . '>' . CM_ODT_FONTS . <<<'XML'
<office:styles>
<style:default-style style:family="paragraph">
 <style:paragraph-properties style:writing-mode="lr-tb"/>
 <style:text-properties style:font-name="Calibri" fo:font-size="11pt" fo:language="nl" fo:country="NL" fo:color="#1f1f1f"/>
</style:default-style>
<style:style style:name="Standard" style:family="paragraph" style:class="text"/>
<style:style style:name="Text_20_body" style:display-name="Text body" style:family="paragraph" style:parent-style-name="Standard" style:class="text">
 <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.25cm" fo:line-height="120%"/>
</style:style>
<style:style style:name="Title" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Text_20_body" style:class="chapter">
 <style:paragraph-properties fo:margin-bottom="0.4cm"/>
 <style:text-properties fo:font-size="22pt" fo:font-weight="bold" fo:color="#1f3864"/>
</style:style>
<style:style style:name="Heading" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Text_20_body" style:class="text">
 <style:paragraph-properties fo:margin-top="0.5cm" fo:margin-bottom="0.15cm" fo:keep-with-next="always"/>
 <style:text-properties fo:font-weight="bold" fo:color="#1f3864"/>
</style:style>
<style:style style:name="Heading_20_1" style:display-name="Heading 1" style:family="paragraph" style:parent-style-name="Heading" style:next-style-name="Text_20_body" style:default-outline-level="1" style:class="text">
 <style:paragraph-properties fo:margin-top="0.7cm"/>
 <style:text-properties fo:font-size="15pt"/>
</style:style>
<style:style style:name="Heading_20_2" style:display-name="Heading 2" style:family="paragraph" style:parent-style-name="Heading" style:next-style-name="Text_20_body" style:default-outline-level="2" style:class="text">
 <style:paragraph-properties fo:margin-top="0.6cm"/>
 <style:text-properties fo:font-size="13pt"/>
</style:style>
<style:style style:name="Heading_20_3" style:display-name="Heading 3" style:family="paragraph" style:parent-style-name="Heading" style:next-style-name="Text_20_body" style:default-outline-level="3" style:class="text">
 <style:text-properties fo:font-size="11pt"/>
</style:style>
<style:style style:name="List_20_Paragraph" style:display-name="List Paragraph" style:family="paragraph" style:parent-style-name="Text_20_body" style:class="list">
 <style:paragraph-properties fo:margin-bottom="0.12cm"/>
</style:style>
<style:style style:name="Quotations" style:family="paragraph" style:parent-style-name="Text_20_body" style:class="html">
 <style:paragraph-properties fo:margin-top="0.1cm" fo:margin-bottom="0.35cm" fo:padding="0.2cm" fo:background-color="#eef2f6" fo:border-left="2.5pt solid #1f3864" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
</style:style>
<style:style style:name="Horizontal_20_Line" style:display-name="Horizontal Line" style:family="paragraph" style:parent-style-name="Standard" style:class="html">
 <style:paragraph-properties fo:margin-top="0.1cm" fo:margin-bottom="0.35cm" fo:padding="0cm" fo:border-bottom="0.5pt solid #bfbfbf" fo:border-left="none" fo:border-right="none" fo:border-top="none"/>
 <style:text-properties fo:font-size="4pt"/>
</style:style>
<style:style style:name="Table_20_Contents" style:display-name="Table Contents" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.05cm"/>
 <style:text-properties fo:font-size="9.5pt"/>
</style:style>
<style:style style:name="Table_20_Heading" style:display-name="Table Heading" style:family="paragraph" style:parent-style-name="Table_20_Contents" style:class="extra">
 <style:text-properties fo:font-weight="bold"/>
</style:style>
<style:style style:name="Header" style:family="paragraph" style:parent-style-name="Standard" style:class="extra"/>
<style:style style:name="Footer" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties><style:tab-stops><style:tab-stop style:position="16.6cm" style:type="right"/></style:tab-stops></style:paragraph-properties>
 <style:text-properties fo:font-size="8.5pt" fo:color="#7f7f7f"/>
</style:style>
<style:style style:name="Contents_20_Heading" style:display-name="Contents Heading" style:family="paragraph" style:parent-style-name="Heading" style:class="index">
 <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.5cm"/>
 <style:text-properties fo:font-size="16pt"/>
</style:style>
<style:style style:name="Contents_20_1" style:display-name="Contents 1" style:family="paragraph" style:parent-style-name="Standard" style:class="index">
 <style:paragraph-properties fo:margin-top="0.25cm" fo:margin-bottom="0.05cm">
  <style:tab-stops><style:tab-stop style:position="16.6cm" style:type="right" style:leader-style="dotted" style:leader-text="."/></style:tab-stops>
 </style:paragraph-properties>
 <style:text-properties fo:font-weight="bold"/>
</style:style>
<style:style style:name="Contents_20_2" style:display-name="Contents 2" style:family="paragraph" style:parent-style-name="Standard" style:class="index">
 <style:paragraph-properties fo:margin-left="0.6cm" fo:margin-bottom="0.05cm">
  <style:tab-stops><style:tab-stop style:position="16cm" style:type="right" style:leader-style="dotted" style:leader-text="."/></style:tab-stops>
 </style:paragraph-properties>
</style:style>
<style:style style:name="Titelgegevens" style:family="paragraph" style:parent-style-name="Text_20_body" style:class="text">
 <style:text-properties fo:color="#404040"/>
</style:style>
<style:style style:name="Strong_20_Emphasis" style:display-name="Strong Emphasis" style:family="text">
 <style:text-properties fo:font-weight="bold"/>
</style:style>
<text:list-style style:name="Opsomming">
 <text:list-level-style-bullet text:level="1" text:bullet-char="•">
  <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
   <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.64cm" fo:text-indent="-0.64cm" fo:margin-left="0.64cm"/>
  </style:list-level-properties>
 </text:list-level-style-bullet>
</text:list-style>
<text:list-style style:name="Nummering">
 <text:list-level-style-number text:level="1" style:num-suffix="." style:num-format="1">
  <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
   <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="0.64cm" fo:text-indent="-0.64cm" fo:margin-left="0.64cm"/>
  </style:list-level-properties>
 </text:list-level-style-number>
</text:list-style>
</office:styles>
XML;

// Stijlen die alleen de inhoud gebruikt. De titelpagina heeft een eigen
// pagina-opmaak zonder voettekst; de inhoudsopgave schakelt terug naar de
// gewone, en begint dus op een nieuwe pagina. Verder elk hoofdstuk op een
// nieuwe pagina, en de randen van de tabellen. De kolombreedtes komen er per
// tabel bij.
const CM_ODT_INHOUDSTIJLEN = '<style:style style:name="Titelpagina" style:family="paragraph" style:parent-style-name="Title" style:master-page-name="Titel">'
    . '<style:paragraph-properties fo:margin-top="5cm" fo:margin-bottom="1cm"/><style:text-properties fo:font-size="24pt"/></style:style>'
    . '<style:style style:name="Inhoudkop" style:family="paragraph" style:parent-style-name="Contents_20_Heading" style:master-page-name="Standard"/>'
    . '<style:style style:name="Hoofdstuk" style:family="paragraph" style:parent-style-name="Heading_20_1">'
    . '<style:paragraph-properties fo:break-before="page" fo:margin-top="0cm"/><style:text-properties fo:font-size="16pt"/></style:style>'
    . '<style:style style:name="Tabel" style:family="table"><style:table-properties style:width="16.6cm" table:align="margins" fo:margin-top="0.1cm" fo:margin-bottom="0.4cm"/></style:style>'
    . '<style:style style:name="Cel" style:family="table-cell"><style:table-cell-properties fo:padding="0.1cm" fo:border="0.5pt solid #bfbfbf"/></style:style>'
    . '<style:style style:name="Kopcel" style:family="table-cell"><style:table-cell-properties fo:padding="0.1cm" fo:border="0.5pt solid #bfbfbf" fo:background-color="#e9eef4"/></style:style>';

/**
 * Het document begint met een titelpagina: de titel en wat er vóór het eerste
 * hoofdstuk staat. Dan volgt de inhoudsopgave, en daarna de hoofdstukken. Met
 * 'hoofdstuk_per_pagina' in de definitie begint elk hoofdstuk op een nieuwe
 * pagina.
 *
 * @param list<array<int,mixed>> $blokken
 */
function cm_odt(array $blokken, string $auteur = ''): string
{
    $apart = !empty(cm_formulier()['hoofdstuk_per_pagina']);
    $titel = cm_documenttitel($blokken);
    $logo = cm_odt_logo();
    $body = '';
    $kolomstijlen = '';
    $tabellen = 0;
    $inhoud = [];
    $plek = null;
    $hoofdstukken = 0;

    // Kop 1 en 2 krijgen een bladwijzer, zodat de inhoudsopgave hun paginanummer kan tonen.
    $kop = static function (string $tekst, int $niveau, string $stijl) use (&$inhoud): string {
        $naam = '_Toc' . (count($inhoud) + 1);
        $inhoud[] = [$niveau, $tekst, $naam];

        return '<text:h text:style-name="' . $stijl . '" text:outline-level="' . $niveau . '">'
            . '<text:bookmark text:name="' . $naam . '"/>' . cm_odt_tekst($tekst) . '</text:h>';
    };

    foreach ($blokken as $blok) {
        if ($plek === null && $blok[0] === 'h2') {
            $plek = strlen($body);
        }

        $body .= match ($blok[0]) {
            'h1' => '<text:p text:style-name="Titelpagina">' . cm_odt_tekst($blok[1]) . '</text:p>',
            'h2' => $kop($blok[1], 1, ++$hoofdstukken === 1 || $apart ? 'Hoofdstuk' : 'Heading_20_1'),
            'h3' => $plek === null
                ? '<text:h text:style-name="Heading_20_2" text:outline-level="2">' . cm_odt_tekst($blok[1]) . '</text:h>'
                : $kop($blok[1], 2, 'Heading_20_2'),
            'h4' => '<text:h text:style-name="Heading_20_3" text:outline-level="3">' . cm_odt_tekst($blok[1]) . '</text:h>',
            'term' => '<text:p text:style-name="' . ($plek === null ? 'Titelgegevens' : 'Text_20_body') . '">'
                . '<text:span text:style-name="Strong_20_Emphasis">'
                . cm_odt_tekst($blok[1]) . ':</text:span> ' . cm_odt_tekst($blok[2]) . '</text:p>',
            'lijst' => cm_odt_lijst($blok[1], 'Opsomming'),
            'genummerd' => cm_odt_lijst($blok[1], 'Nummering'),
            'citaat' => '<text:p text:style-name="Quotations">' . cm_odt_tekst($blok[1]) . '</text:p>',
            'lijn' => '<text:p text:style-name="Horizontal_20_Line"/>',
            'tabel' => cm_odt_tabel($blok[1], ++$tabellen, $kolomstijlen),
            'schema' => '',
            default => '<text:p text:style-name="Text_20_body">' . cm_odt_tekst($blok[1]) . '</text:p>',
        };
    }

    if ($plek !== null) {
        $body = substr_replace($body, cm_odt_inhoud($inhoud), $plek, 0);
    }

    $maker = $auteur === '' ? '' : '<meta:initial-creator>' . cm_odt_tekst($auteur) . '</meta:initial-creator>'
        . '<dc:creator>' . cm_odt_tekst($auteur) . '</dc:creator>';

    $meta = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<office:document-meta ' . CM_ODT_NS . '><office:meta>'
        . '<dc:title>' . cm_odt_tekst($titel) . '</dc:title>' . $maker
        . '<dc:language>nl-NL</dc:language>'
        . '<meta:creation-date>' . date('Y-m-d\TH:i:s') . '</meta:creation-date>'
        . '</office:meta></office:document-meta>';

    $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<office:document-content ' . CM_ODT_NS . '>' . CM_ODT_FONTS
        . '<office:automatic-styles>' . CM_ODT_INHOUDSTIJLEN . $kolomstijlen . '</office:automatic-styles>'
        . '<office:body><office:text>' . $body . '</office:text></office:body>'
        . '</office:document-content>';

    // ODF eist 'mimetype' als eerste bestand, ongecomprimeerd.
    return cm_zip([
        'mimetype' => 'application/vnd.oasis.opendocument.text',
        'META-INF/manifest.xml' => sprintf(CM_ODT_MANIFEST, $logo === null ? '' : '<manifest:file-entry manifest:full-path="'
            . $logo['pad'] . '" manifest:media-type="' . $logo['mime'] . '"/>'),
        'meta.xml' => $meta,
        'styles.xml' => CM_ODT_STIJLEN . cm_odt_opmaak($titel, $logo),
        'content.xml' => $content,
        ...($logo === null ? [] : [$logo['pad'] => $logo['data']]),
    ]);
}

/**
 * Een echte inhoudsopgave, die Word en LibreOffice zelf kunnen bijwerken. De
 * regels staan er al in. Het paginanummer is een verwijzing naar de
 * bladwijzer in de kop, dus de tekstverwerker vult het in.
 *
 * @param list<array{0:int,1:string,2:string}> $inhoud niveau, tekst, bladwijzer
 */
function cm_odt_inhoud(array $inhoud): string
{
    $sjabloon = static fn (int $n): string => '<text:table-of-content-entry-template text:outline-level="' . $n
        . '" text:style-name="Contents_20_' . $n . '"><text:index-entry-link-start/><text:index-entry-text/>'
        . '<text:index-entry-tab-stop style:type="right" style:leader-char="."/><text:index-entry-page-number/>'
        . '<text:index-entry-link-end/></text:table-of-content-entry-template>';

    $regels = '';
    foreach ($inhoud as [$niveau, $tekst, $naam]) {
        $regels .= '<text:p text:style-name="Contents_20_' . $niveau . '"><text:a xlink:type="simple" xlink:href="#'
            . $naam . '">' . cm_odt_tekst($tekst) . '<text:tab/><text:bookmark-ref text:reference-format="page" '
            . 'text:ref-name="' . $naam . '"/></text:a></text:p>';
    }

    return '<text:table-of-content text:name="Inhoudsopgave"><text:table-of-content-source text:outline-level="2">'
        . '<text:index-title-template text:style-name="Contents_20_Heading">Inhoud</text:index-title-template>'
        . $sjabloon(1) . $sjabloon(2) . '</text:table-of-content-source><text:index-body>'
        . '<text:index-title text:name="Inhoudsopgave_kop"><text:p text:style-name="Inhoudkop">Inhoud</text:p></text:index-title>'
        . $regels . '</text:index-body></text:table-of-content>';
}

/**
 * Het logo uit config.php, of null. Een logo dat niet te lezen is, stopt het
 * document: een stil ontbrekend logo valt pas op als het document al weg is.
 *
 * @return array{pad:string,mime:string,data:string,breedte:float,hoogte:float,x:float}|null
 */
function cm_odt_logo(): ?array
{
    $logo = cm_config()['logo'] ?? null;

    if ($logo === null) {
        return null;
    }

    $info = is_file($logo['bestand']) ? getimagesize($logo['bestand']) : false;
    $soort = ['image/png' => 'png', 'image/jpeg' => 'jpg'][$info['mime'] ?? ''] ?? null;

    if ($soort === null) {
        throw new RuntimeException('Het logo ' . $logo['bestand'] . ' bestaat niet, of is geen PNG of JPEG.');
    }

    $breedte = (float) $logo['breedte'];

    return [
        'pad' => 'Pictures/logo.' . $soort,
        'mime' => $info['mime'],
        'data' => (string) file_get_contents($logo['bestand']),
        'breedte' => $breedte,
        'hoogte' => $breedte * $info[1] / $info[0],
        // Het midden van het lint komt op het midden van de pagina.
        'x' => 10.5 - (float) ($logo['midden'] ?? $breedte / 2),
    ];
}

/**
 * De pagina's: A4, met het logo tegen de bovenrand als dat er is. De
 * titelpagina heeft geen voettekst; de andere pagina's tonen de titel en
 * "pagina x van y".
 *
 * @param array{pad:string,breedte:float,hoogte:float,x:float}|null $logo
 */
function cm_odt_opmaak(string $titel, ?array $logo): string
{
    $cm = static fn (float $maat): string => sprintf('%.3Fcm', $maat);
    $kop = '';
    $kopstijl = '';
    $grafisch = '';

    if ($logo !== null) {
        $kop = '<style:header><text:p text:style-name="Header"><draw:frame draw:style-name="Logo" draw:name="Logo" '
            . 'text:anchor-type="paragraph" svg:x="' . $cm($logo['x']) . '" svg:y="0cm" svg:width="' . $cm($logo['breedte'])
            . '" svg:height="' . $cm($logo['hoogte']) . '" draw:z-index="0"><draw:image xlink:href="' . $logo['pad']
            . '" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame></text:p></style:header>';
        $kopstijl = '<style:header-style><style:header-footer-properties fo:min-height="' . $cm($logo['hoogte'])
            . '" fo:margin-bottom="0.8cm"/></style:header-style>';
        $grafisch = '<style:style style:name="Logo" style:family="graphic"><style:graphic-properties style:wrap="none" '
            . 'style:vertical-pos="from-top" style:vertical-rel="page" style:horizontal-pos="from-left" '
            . 'style:horizontal-rel="page" fo:border="none" fo:padding="0cm"/></style:style>';
    }

    $voet = '<style:footer><text:p text:style-name="Footer">' . cm_odt_tekst($titel) . '<text:tab/>Pagina '
        . '<text:page-number text:select-page="current">1</text:page-number> van <text:page-count>1</text:page-count>'
        . '</text:p></style:footer>';

    return '<office:automatic-styles>' . $grafisch . '<style:page-layout style:name="pm1">'
        . '<style:page-layout-properties fo:page-width="21cm" fo:page-height="29.7cm" style:print-orientation="portrait" '
        . 'fo:margin-top="' . ($logo === null ? '2cm' : '0cm') . '" fo:margin-bottom="1.5cm" fo:margin-left="2.2cm" '
        . 'fo:margin-right="2.2cm"/>' . $kopstijl
        . '<style:footer-style><style:header-footer-properties fo:min-height="0.6cm" fo:margin-top="0.4cm"/></style:footer-style>'
        . '</style:page-layout></office:automatic-styles><office:master-styles>'
        . '<style:master-page style:name="Standard" style:page-layout-name="pm1">' . $kop . $voet . '</style:master-page>'
        . '<style:master-page style:name="Titel" style:display-name="Titelpagina" style:page-layout-name="pm1" '
        . 'style:next-style-name="Standard">' . $kop . '</style:master-page>'
        . '</office:master-styles></office:document-styles>';
}

/** Platte tekst als veilige XML. Stuurtekens uit een geplakt antwoord maken het bestand anders onleesbaar. */
function cm_odt_tekst(string $tekst): string
{
    $tekst = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $tekst);

    return htmlspecialchars($tekst, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** @param list<string> $items */
function cm_odt_lijst(array $items, string $stijl): string
{
    $out = '<text:list text:style-name="' . $stijl . '">';

    foreach ($items as $item) {
        $out .= '<text:list-item><text:p text:style-name="List_20_Paragraph">' . cm_odt_tekst($item) . '</text:p></text:list-item>';
    }

    return $out . '</text:list>';
}

/**
 * Een tabel met vaste kolombreedtes in centimeters. De kopregel herhaalt zich
 * bovenaan elke pagina waarover de tabel doorloopt.
 */
function cm_odt_tabel(array $tabel, int $nummer, string &$kolomstijlen): string
{
    $naam = 'Tabel' . $nummer;
    $out = '<table:table table:name="' . $naam . '" table:style-name="Tabel">';

    foreach ($tabel['breedtes'] as $i => $breedte) {
        $kolomstijlen .= '<style:style style:name="' . $naam . '.K' . $i . '" style:family="table-column">'
            . '<style:table-column-properties style:column-width="' . $breedte . 'cm"/></style:style>';
        $out .= '<table:table-column table:style-name="' . $naam . '.K' . $i . '"/>';
    }

    $cel = static function (string|array $inhoud, bool $kop): string {
        $alineas = '';
        foreach ((array) $inhoud as $regel) {
            $alineas .= '<text:p text:style-name="' . ($kop ? 'Table_20_Heading' : 'Table_20_Contents') . '">'
                . cm_odt_tekst($regel) . '</text:p>';
        }

        return '<table:table-cell table:style-name="' . ($kop ? 'Kopcel' : 'Cel') . '" office:value-type="string">'
            . ($alineas === '' ? '<text:p text:style-name="Table_20_Contents"/>' : $alineas) . '</table:table-cell>';
    };

    if (!empty($tabel['kop'])) {
        $out .= '<table:table-header-rows><table:table-row>'
            . implode('', array_map(static fn (string $k): string => $cel($k, true), $tabel['kop']))
            . '</table:table-row></table:table-header-rows>';
    }

    foreach ($tabel['rijen'] as $rij) {
        $out .= '<table:table-row>';
        foreach (array_values($rij) as $i => $inhoud) {
            $out .= $cel($inhoud, $i === 0 && !empty($tabel['rijkop']));
        }
        $out .= '</table:table-row>';
    }

    return $out . '</table:table>';
}

/**
 * Een zip met alle bestanden ongecomprimeerd, in de gegeven volgorde.
 *
 * @param array<string,string> $bestanden naam => inhoud
 */
function cm_zip(array $bestanden): string
{
    $nu = getdate();
    $tijd = ($nu['hours'] << 11) | ($nu['minutes'] << 5) | intdiv($nu['seconds'], 2);
    $datum = (($nu['year'] - 1980) << 9) | ($nu['mon'] << 5) | $nu['mday'];
    $archief = '';
    $register = '';

    foreach ($bestanden as $naam => $inhoud) {
        $crc = crc32($inhoud);
        $grootte = strlen($inhoud);

        $register .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, $tijd, $datum, $crc,
            $grootte, $grootte, strlen($naam), 0, 0, 0, 0, 0, strlen($archief)) . $naam;
        $archief .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, $tijd, $datum, $crc,
            $grootte, $grootte, strlen($naam), 0) . $naam . $inhoud;
    }

    return $archief . $register . pack('VvvvvVVv', 0x06054b50, 0, 0,
        count($bestanden), count($bestanden), strlen($register), strlen($archief), 0);
}

/** Stuurt het document als .odt-download en stopt het verzoek. */
function cm_stuur_odt(array $blokken, string $bestandsnaam, string $auteur = ''): never
{
    $odt = cm_odt($blokken, $auteur);

    // Via de parser loopt er al een outputbuffer; die hoort niet in het bestand.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.oasis.opendocument.text');
    header('Content-Disposition: attachment; filename="' . $bestandsnaam . '"');
    header('Content-Length: ' . strlen($odt));
    echo $odt;
    exit;
}
