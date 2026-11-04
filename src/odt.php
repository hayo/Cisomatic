<?php

/**
 * Het document als OpenDocument-tekst (.odt), zonder PHP-extensies.
 *
 * Een .odt is een zip met een handvol XML-bestanden. De zip schrijven we zelf,
 * met pack() en crc32(), en gzdeflate() als die er is. Word, LibreOffice en Google
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
    . 'xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" '
    // Nodig voor invulvelden: form:control-implementation verwijst naar ooo:.
    . 'xmlns:ooo="http://openoffice.org/2004/office" '
    . 'xmlns:xlink="http://www.w3.org/1999/xlink" '
    . 'xmlns:dc="http://purl.org/dc/elements/1.1/" office:version="1.2"';

const CM_ODT_MANIFEST = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.2">'
    . '<manifest:file-entry manifest:full-path="/" manifest:version="1.2" manifest:media-type="application/vnd.oasis.opendocument.text"/>'
    . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
    . '<manifest:file-entry manifest:full-path="styles.xml" manifest:media-type="text/xml"/>'
    . '<manifest:file-entry manifest:full-path="meta.xml" manifest:media-type="text/xml"/>'
    . '<manifest:file-entry manifest:full-path="settings.xml" manifest:media-type="text/xml"/>'
    . '%s</manifest:manifest>';

// LibreOffice houdt de ingesloten lettertypen dan ook na opslaan in het bestand.
const CM_ODT_INSTELLINGEN = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<office:document-settings xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" '
    . 'xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" office:version="1.2"><office:settings>'
    . '<config:config-item-set config:name="ooo:configuration-settings">'
    . '<config:config-item config:name="EmbedFonts" config:type="boolean">%s</config:config-item>'
    . '</config:config-item-set></office:settings></office:document-settings>';

// Stijlnamen volgen LibreOffice ("Heading_20_1" is "Heading 1"), zodat Word en
// LibreOffice ze als hun eigen kopjes herkennen en de navigatie ze toont.
const CM_ODT_STIJLEN = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<office:document-styles ' . CM_ODT_NS . '>{lettertypen}' . <<<'XML'
<office:styles>
<style:default-style style:family="paragraph">
 <style:paragraph-properties style:writing-mode="lr-tb"/>
 <style:text-properties style:font-name="{letter}" fo:font-size="11pt" fo:language="nl" fo:country="NL" fo:color="#1f1f1f"/>
</style:default-style>
<style:style style:name="Standard" style:family="paragraph" style:class="text"/>
<style:style style:name="Text_20_body" style:display-name="Text body" style:family="paragraph" style:parent-style-name="Standard" style:class="text">
 <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.25cm" fo:line-height="120%"/>
</style:style>
<style:style style:name="Title" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Text_20_body" style:class="chapter">
 <style:paragraph-properties fo:margin-bottom="0.4cm"/>
 <style:text-properties fo:font-size="22pt" fo:font-weight="bold" fo:color="{kleur}"/>
</style:style>
<style:style style:name="Heading" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Text_20_body" style:class="text">
 <style:paragraph-properties fo:margin-top="0.5cm" fo:margin-bottom="0.15cm" fo:keep-with-next="always"/>
 <style:text-properties fo:font-weight="bold" fo:color="{kleur}"/>
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
 <style:paragraph-properties fo:keep-with-next="always" fo:margin-top="0.1cm" fo:margin-bottom="0.35cm" fo:padding="0.2cm" fo:background-color="{vlak}" fo:border-left="2.5pt solid {kleur}" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
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
<style:style style:name="Woordmerk" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0cm" fo:line-height="100%"/>
 <style:text-properties style:font-name="{woordmerk}" fo:font-size="13pt" fo:font-style="italic" fo:color="#000000"/>
</style:style>
<style:style style:name="Noot" style:family="paragraph" style:parent-style-name="Text_20_body" style:class="text">
 <style:paragraph-properties fo:margin-bottom="0.12cm" fo:keep-with-next="always"/>
 <style:text-properties fo:font-size="9.5pt" fo:color="#4d4d4d"/>
</style:style>
<style:style style:name="Bijzin" style:family="text">
 <style:text-properties fo:font-weight="normal" fo:font-size="9.5pt" fo:color="#4d4d4d"/>
</style:style>
<style:style style:name="Vraagnummer" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties fo:text-align="center" fo:margin-top="0cm" fo:margin-bottom="0cm"/>
 <style:text-properties fo:font-size="12pt" fo:font-weight="bold" fo:color="#ffffff"/>
</style:style>
<style:style style:name="Vraagtekst" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.05cm"/>
 <style:text-properties fo:font-weight="bold"/>
</style:style>
<style:style style:name="Blokuitleg" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties fo:margin-top="0.05cm" fo:margin-bottom="0cm"/>
 <style:text-properties fo:font-size="9pt" fo:color="#3d3d3d"/>
</style:style>
<style:style style:name="Groepkop" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0cm"/>
 <style:text-properties fo:font-size="11.5pt" fo:font-weight="bold" fo:color="#ffffff"/>
</style:style>
<style:style style:name="Groepuitleg" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties fo:margin-top="0.06cm" fo:margin-bottom="0cm"/>
 <style:text-properties fo:font-size="9pt" fo:color="#ffffff"/>
</style:style>
<style:style style:name="Hokje" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties fo:text-align="center" fo:margin-top="0cm" fo:margin-bottom="0cm"/>
 <style:text-properties fo:font-size="13pt" fo:color="{kleur}"/>
</style:style>
<style:style style:name="Keuzetekst" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
 <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0cm"/>
 <style:text-properties fo:font-size="10.5pt"/>
</style:style>
<style:style style:name="Keuzeuitleg" style:family="paragraph" style:parent-style-name="Keuzetekst" style:class="extra">
 <style:text-properties fo:font-size="9pt" fo:color="#4d4d4d"/>
</style:style>
<style:style style:name="Kolomkop" style:family="paragraph" style:parent-style-name="Keuzetekst" style:class="extra">
 <style:text-properties fo:font-size="9pt" fo:font-weight="bold"/>
</style:style>
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
    . '<style:style style:name="Vraagblok" style:family="table"><style:table-properties style:width="16.6cm" '
    . 'table:align="margins" fo:margin-top="0.35cm" fo:margin-bottom="0.15cm" style:may-break-between-rows="false"/></style:style>'
    . '<style:style style:name="Vraagblok_breekbaar" style:family="table"><style:table-properties style:width="16.6cm" '
    . 'table:align="margins" fo:margin-top="0.35cm" fo:margin-bottom="0.15cm"/></style:style>'
    . '<style:style style:name="Nummercel" style:family="table-cell"><style:table-cell-properties fo:padding="0.15cm" '
    . 'fo:border="0.75pt solid {kleur}" fo:background-color="{kleur}"/></style:style>'
    . '<style:style style:name="Vraagcel" style:family="table-cell"><style:table-cell-properties fo:padding="0.15cm" '
    . 'fo:border="0.75pt solid {kleur}" fo:background-color="{vlak}"/></style:style>'
    . '<style:style style:name="Invulcel" style:family="table-cell"><style:table-cell-properties fo:padding="0.12cm" '
    . 'style:vertical-align="middle" fo:border="0.75pt solid {kleur}" fo:background-color="#ffffff"/></style:style>'
    . '<style:style style:name="Groepkopcel" style:family="table-cell"><style:table-cell-properties fo:padding="0.18cm" '
    . 'fo:border="0.75pt solid {kleur}" fo:background-color="{kleur}"/></style:style>'
    . '<style:style style:name="Groepcel" style:family="table-cell"><style:table-cell-properties fo:padding="0.1cm" '
    . 'fo:border="0.75pt solid {kleur}" fo:background-color="#f3f6f9"/></style:style>'
    . '<style:style style:name="Rij" style:family="table-row"><style:table-row-properties fo:keep-together="always"/></style:style>'
    . '<style:style style:name="Cel" style:family="table-cell"><style:table-cell-properties fo:padding="0.1cm" fo:border="0.5pt solid #bfbfbf"/></style:style>'
    . '<style:style style:name="Kopcel" style:family="table-cell"><style:table-cell-properties fo:padding="0.1cm" fo:border="0.5pt solid #bfbfbf" fo:background-color="{vlak}"/></style:style>';

// Alleen op één blad: de titel bovenaan de eerste pagina, op de gewone pagina-opmaak.
const CM_ODT_BLADSTIJLEN = '<style:style style:name="Bladtitel" style:family="paragraph" style:parent-style-name="Title" style:master-page-name="Standard">'
    . '<style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.1cm"/><style:text-properties fo:font-size="17pt"/></style:style>';

// Op één blad staan de vraagblokken dichter op elkaar, met kleinere letters en
// minder ruimte in de cellen. Dit vervangt delen van de gewone stijlen.
const CM_ODT_BLADMATEN = [
    // De ruimte boven en onder een vraagblok.
    'fo:margin-top="0.35cm" fo:margin-bottom="0.15cm"' => 'fo:margin-top="0.15cm" fo:margin-bottom="0cm"',
    // De ruimte in de cellen.
    'fo:padding="0.15cm"' => 'fo:padding="0.07cm"',
    'fo:padding="0.12cm"' => 'fo:padding="0.04cm"',
    // De ruimte onder het logo.
    'fo:margin-bottom="0.8cm"/></style:header-style>' => 'fo:margin-bottom="0.3cm"/></style:header-style>',
    // De letters van de keuzes, de vraag en de uitleg.
    '<style:text-properties fo:font-size="10.5pt"/>' => '<style:text-properties fo:font-size="9pt"/>',
    '<style:text-properties fo:font-size="12pt" fo:font-weight="bold" fo:color="#ffffff"/>'
        => '<style:text-properties fo:font-size="10.5pt" fo:font-weight="bold" fo:color="#ffffff"/>',
    '<style:text-properties fo:font-size="13pt" fo:color="{kleur}"/>' => '<style:text-properties fo:font-size="10.5pt" fo:color="{kleur}"/>',
    '<style:text-properties fo:font-size="9pt" fo:color="#3d3d3d"/>' => '<style:text-properties fo:font-size="8.5pt" fo:color="#3d3d3d"/>',
];

/**
 * Het document begint met een titelpagina: de titel en wat er vóór het eerste
 * hoofdstuk staat. Dan volgt de inhoudsopgave, en daarna de hoofdstukken. Met
 * 'hoofdstuk_per_pagina' in de definitie begint elk hoofdstuk op een nieuwe
 * pagina.
 *
 * Met $blad is het één blad, zoals een check op één A4: geen titelpagina en
 * geen inhoudsopgave, de titel bovenaan de eerste pagina, en de blokken dichter
 * op elkaar.
 *
 * @param list<array<int,mixed>> $blokken
 */
function cm_odt(array $blokken, string $auteur = '', string $kenmerk = '', bool $blad = false): string
{
    $apart = !$blad && !empty(cm_formulier()['hoofdstuk_per_pagina']);
    $titel = cm_documenttitel($blokken);
    $logo = cm_odt_logo();
    $huisstijl = cm_odt_huisstijl();
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
            'h1' => '<text:p text:style-name="' . ($blad ? 'Bladtitel' : 'Titelpagina') . '">' . cm_odt_tekst($blok[1]) . '</text:p>',
            'h2' => $kop($blok[1], 1, !$blad && (++$hoofdstukken === 1 || $apart) ? 'Hoofdstuk' : 'Heading_20_1'),
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
            'noot' => '<text:p text:style-name="Noot">' . cm_odt_tekst($blok[1]) . '</text:p>',
            'vraagblok' => cm_odt_vraagblok($blok[1], ++$tabellen, $kolomstijlen),
            default => '<text:p text:style-name="Text_20_body">' . cm_odt_tekst($blok[1]) . '</text:p>',
        };
    }

    if ($plek !== null && !$blad) {
        $body = substr_replace($body, cm_odt_inhoud($inhoud), $plek, 0);
    }

    [$body, $velden] = cm_odt_velden($body);

    $maten = $blad ? CM_ODT_BLADMATEN : [];
    $inhoudstijlen = strtr(strtr(CM_ODT_INHOUDSTIJLEN, $maten), $huisstijl['tokens']) . ($blad ? CM_ODT_BLADSTIJLEN : '');

    $maker = $auteur === '' ? '' : '<meta:initial-creator>' . cm_odt_tekst($auteur) . '</meta:initial-creator>'
        . '<dc:creator>' . cm_odt_tekst($auteur) . '</dc:creator>';

    $meta = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<office:document-meta ' . CM_ODT_NS . '><office:meta>'
        . '<dc:title>' . cm_odt_tekst($titel) . '</dc:title>' . $maker
        . '<dc:language>nl-NL</dc:language>'
        . ($kenmerk === '' ? '' : '<meta:user-defined meta:name="Kenmerk">' . cm_odt_tekst($kenmerk) . '</meta:user-defined>')
        . '<meta:creation-date>' . date('Y-m-d\TH:i:s') . '</meta:creation-date>'
        . '</office:meta></office:document-meta>';

    $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<office:document-content ' . CM_ODT_NS . '>' . $huisstijl['decls']
        . '<office:automatic-styles>' . $inhoudstijlen . $kolomstijlen . ($velden === '' ? '' : CM_ODT_VELDSTIJL)
        . '</office:automatic-styles>'
        . '<office:body><office:text>' . $velden . $body . '</office:text></office:body>'
        . '</office:document-content>';

    $extra = $huisstijl['bestanden'] + ($logo === null ? [] : [$logo['pad'] => [$logo['mime'], $logo['data']]]);
    $manifest = '';
    foreach ($extra as $pad => [$mime]) {
        $manifest .= '<manifest:file-entry manifest:full-path="' . $pad . '" manifest:media-type="' . $mime . '"/>';
    }

    // ODF eist 'mimetype' als eerste bestand, ongecomprimeerd.
    return cm_zip([
        'mimetype' => 'application/vnd.oasis.opendocument.text',
        'META-INF/manifest.xml' => sprintf(CM_ODT_MANIFEST, $manifest),
        'meta.xml' => $meta,
        'settings.xml' => sprintf(CM_ODT_INSTELLINGEN, $huisstijl['bestanden'] === [] ? 'false' : 'true'),
        'styles.xml' => strtr(strtr(CM_ODT_STIJLEN, $maten), $huisstijl['tokens'])
            . strtr(cm_odt_opmaak($kenmerk === '' ? $titel : $titel . ' · ' . $kenmerk, $logo), $maten),
        'content.xml' => $content,
        ...array_map(static fn (array $bestand): string => $bestand[1], $extra),
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
 * @return array{pad:string,mime:string,data:string,breedte:float,hoogte:float,x:float,woordmerk:string}|null
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
        'woordmerk' => (string) ($logo['woordmerk'] ?? ''),
    ];
}

/**
 * De huisstijl uit config.php: de letter, de kleuren, en de lettertypen die
 * in het bestand meegaan. Zonder huisstijl Calibri en donkerblauw, zonder
 * ingesloten lettertypen. Een lettertype dat niet te lezen is, stopt het
 * document, net als bij het logo.
 *
 * @return array{decls:string,tokens:array<string,string>,bestanden:array<string,array{0:string,1:string}>}
 */
function cm_odt_huisstijl(): array
{
    $stijl = cm_config()['huisstijl'] ?? null;
    $letter = (string) ($stijl['tekst'] ?? 'Calibri');
    $faces = '';
    $bestanden = [];
    $naam = static fn (string $n): string => cm_odt_tekst(str_contains($n, ' ') ? "'" . $n . "'" : $n);

    foreach (($stijl['lettertypen'] ?? []) as $familie => $paden) {
        $bronnen = '';

        foreach ($paden as $pad) {
            $soort = strtolower(pathinfo($pad, PATHINFO_EXTENSION));

            if (!is_file($pad) || !in_array($soort, ['ttf', 'otf'], true)) {
                throw new RuntimeException('Het lettertype ' . $pad . ' bestaat niet, of is geen .ttf of .otf.');
            }

            $binnen = 'Fonts/' . basename($pad);
            $bestanden[$binnen] = ['application/x-font-' . $soort, (string) file_get_contents($pad)];
            $bronnen .= '<svg:font-face-uri xlink:href="' . cm_odt_tekst($binnen) . '" xlink:type="simple">'
                . '<svg:font-face-format svg:string="' . ($soort === 'ttf' ? 'truetype' : 'opentype') . '"/>'
                . '</svg:font-face-uri>';
        }

        $faces .= '<style:font-face style:name="' . cm_odt_tekst((string) $familie) . '" svg:font-family="'
            . $naam((string) $familie) . '" style:font-family-generic="' . ($familie === $letter ? 'swiss' : 'roman')
            . '" style:font-pitch="variable"><svg:font-face-src>' . $bronnen . '</svg:font-face-src></style:font-face>';
    }

    $woordmerk = (string) ($stijl['woordmerk'] ?? $letter);
    foreach (array_unique([$letter, $woordmerk]) as $los) {
        if (!isset($stijl['lettertypen'][$los])) {
            $faces .= '<style:font-face style:name="' . cm_odt_tekst($los) . '" svg:font-family="' . $naam($los)
                . '" style:font-family-generic="swiss" style:font-pitch="variable"/>';
        }
    }

    $decls = '<office:font-face-decls>' . $faces . '</office:font-face-decls>';

    return [
        'decls' => $decls,
        'tokens' => [
            '{lettertypen}' => $decls,
            '{letter}' => cm_odt_tekst($letter),
            '{woordmerk}' => cm_odt_tekst($woordmerk),
            '{kleur}' => cm_odt_tekst((string) ($stijl['kleur'] ?? '#1f3864')),
            '{vlak}' => cm_odt_tekst((string) ($stijl['vlak'] ?? '#e9eef4')),
        ],
        'bestanden' => $bestanden,
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
        $kop = '<style:header><text:p text:style-name="' . ($logo['woordmerk'] === '' ? 'Header' : 'Kopregel')
            . '"><draw:frame draw:style-name="Logo" draw:name="Logo" '
            . 'text:anchor-type="paragraph" svg:x="' . $cm($logo['x']) . '" svg:y="0cm" svg:width="' . $cm($logo['breedte'])
            . '" svg:height="' . $cm($logo['hoogte']) . '" draw:z-index="0"><draw:image xlink:href="' . $logo['pad']
            . '" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>'
            . cm_odt_tekst($logo['woordmerk']) . '</text:p></style:header>';
        $kopstijl = '<style:header-style><style:header-footer-properties fo:min-height="' . $cm($logo['hoogte'])
            . '" fo:margin-bottom="0.8cm"/></style:header-style>';
        $grafisch = '<style:style style:name="Logo" style:family="graphic"><style:graphic-properties style:wrap="none" '
            . 'style:vertical-pos="from-top" style:vertical-rel="page" style:horizontal-pos="from-left" '
            . 'style:horizontal-rel="page" fo:border="none" fo:padding="0cm"/></style:style>'
            . cm_odt_woordmerkstijl($logo);
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

/**
 * De tekst naast het lint, zoals in het Rijkslogo: links uitgelijnd tegen de
 * rechterkant van het lint, op ongeveer twee derde van de hoogte. Het is de
 * gewone regel van de koptekst, met een inspringing en ruimte erboven. Het
 * lint staat los op de pagina en duwt niets opzij. Zo houdt ook Word het
 * woordmerk op zijn plek.
 *
 * @param array{breedte:float,hoogte:float,x:float,woordmerk:string} $logo
 */
function cm_odt_woordmerkstijl(array $logo): string
{
    if ($logo['woordmerk'] === '') {
        return '';
    }

    return '<style:style style:name="Kopregel" style:family="paragraph" style:parent-style-name="Woordmerk">'
        . '<style:paragraph-properties fo:margin-left="' . sprintf('%.3Fcm', $logo['x'] + $logo['breedte'] + 0.2 - 2.2)
        . '" fo:margin-top="' . sprintf('%.3Fcm', $logo['hoogte'] * 0.6) . '"/></style:style>';
}

/** Platte tekst als veilige XML. Stuurtekens uit een geplakt antwoord maken het bestand anders onleesbaar. */
function cm_odt_tekst(string $tekst): string
{
    $tekst = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $tekst);

    return htmlspecialchars($tekst, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Een invulveld in een tekst, zoals in een vraagblok: 'tekst' (één regel),
 * 'vak' (meer regels) of 'keuze' (een rondje met een label; keuzes met
 * dezelfde naam sluiten elkaar uit). Breedte en hoogte in cm. De naam is de
 * sleutel van de vraag. cm_odt() maakt er een formulierveld van, dat je in
 * LibreOffice kunt invullen.
 *
 * @param array{label?:string,waarde?:string} $opties
 */
function cm_odt_veld(string $soort, string $naam, float $breedte, float $hoogte = 0.5, array $opties = []): string
{
    return "\u{E000}" . base64_encode((string) json_encode(['soort' => $soort, 'naam' => $naam,
        'breedte' => $breedte, 'hoogte' => $hoogte] + $opties)) . "\u{E001}";
}

// De opmaak van een invulveld: in de regel, midden op de tekst.
const CM_ODT_VELDSTIJL = '<style:style style:name="Veld" style:family="graphic"><style:graphic-properties '
    . 'style:vertical-pos="middle" style:vertical-rel="text" fo:margin-left="0.05cm" fo:margin-right="0.05cm"/>'
    . '</style:style>';

/**
 * Zet de invulvelden van cm_odt_veld() om. Geeft de tekst terug met de velden
 * erin, en het formulier dat bovenaan de tekst hoort (leeg zonder velden).
 *
 * @return array{0:string,1:string}
 */
function cm_odt_velden(string $body): array
{
    $controls = '';
    $n = 0;
    $body = (string) preg_replace_callback('/\x{E000}([A-Za-z0-9+\/=]+)\x{E001}/u', static function (array $m) use (&$controls, &$n): string {
        $v = json_decode((string) base64_decode($m[1]), true);
        $id = 'veld' . ++$n;
        $naam = cm_odt_tekst((string) $v['naam']);
        $kenmerk = ' form:name="' . $naam . '" form:id="' . $id . '" xml:id="' . $id . '"';
        $controls .= match ($v['soort']) {
            'vak' => '<form:textarea' . $kenmerk . ' form:control-implementation="ooo:com.sun.star.form.component.TextField">'
                . '<form:properties><form:property form:property-name="MultiLine" office:value-type="boolean" '
                . 'office:boolean-value="true"/></form:properties></form:textarea>',
            'keuze' => '<form:radio' . $kenmerk . ' form:control-implementation="ooo:com.sun.star.form.component.RadioButton"'
                . ' form:label="' . cm_odt_tekst((string) ($v['label'] ?? '')) . '" form:value="'
                . cm_odt_tekst((string) ($v['waarde'] ?? '')) . '"/>',
            default => '<form:text' . $kenmerk . ' form:control-implementation="ooo:com.sun.star.form.component.TextField"/>',
        };

        return '<draw:control text:anchor-type="as-char" draw:z-index="' . $n . '" draw:style-name="Veld" svg:width="'
            . sprintf('%.2Fcm', (float) $v['breedte']) . '" svg:height="' . sprintf('%.2Fcm', (float) $v['hoogte'])
            . '" draw:control="' . $id . '"/>';
    }, $body);

    return [$body, $controls === '' ? '' : '<office:forms form:automatic-focus="false" form:apply-design-mode="false">'
        . '<form:form form:name="Formulier" form:control-implementation="ooo:com.sun.star.form.component.Form">'
        . $controls . '</form:form></office:forms>'];
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
 * Een vraag als blok, om in te vullen. Bovenaan de vraag in een gekleurde balk,
 * met het nummer in een eigen vak links. Wat erbij hoort, zoals wanneer de
 * vraag geldt, staat in die balk. Daaronder, in wit, wat je invult: een rij per
 * keuze met een hokje, een leeg vak, of een tabel met een hokje per rij.
 *
 * Vragen die bij elkaar horen, zoals de vragen bij één risico, staan samen in
 * één blok onder een donkere kop: 'kop' met 'titel' en 'noten', en de vragen
 * in 'delen'. Zo'n blok mag over twee pagina's lopen en herhaalt dan de kop.
 * Een losse vraag breekt niet, behalve een tabel; die herhaalt de vraag.
 *
 * De eerste kolom is 1,2 cm: het nummer, en daaronder de hokjes. Keuzes zijn
 * een lijst met eerst het label en dan regels uitleg; met 'kop' een tussenkop
 * over de hele breedte, met 'los' een regel zonder hokje. Een tabel heeft
 * 'kop', 'rijen' en 'breedtes' (de verhouding van de kolommen), en optioneel
 * 'voor' (wat in de eerste kolom staat in plaats van een hokje) en 'hoogte'
 * (van elke rij, in cm). Een cel met alleen '☐' wordt een hokje. Een tabel kan
 * alleen in een losse vraag.
 *
 * @param array<string,mixed> $v een vraag (nr, vraag, bijzin, noten, en keuzes, vak of tabel), of kop en delen
 */
function cm_odt_vraagblok(array $v, int $nummer, string &$stijlen): string
{
    $naam = 'Vraagblok' . $nummer;
    $delen = $v['delen'] ?? [$v];
    $tabel = count($delen) === 1 ? ($delen[0]['tabel'] ?? null) : null;
    $breedtes = [1.2];
    if ($tabel === null) {
        $breedtes[] = 15.4;
    } else {
        $som = array_sum($tabel['breedtes']);
        foreach ($tabel['breedtes'] as $b) {
            $breedtes[] = round($b * 15.4 / $som, 2);
        }
    }
    $breed = count($breedtes) - 1;

    $p = static fn (string $stijl, string $tekst): string => '<text:p text:style-name="' . $stijl . '">'
        . cm_odt_tekst($tekst) . '</text:p>';
    $cel = static fn (string $stijl, string $inhoud, int $span = 1): string => '<table:table-cell table:style-name="'
        . $stijl . '" office:value-type="string"' . ($span > 1 ? ' table:number-columns-spanned="' . $span . '"' : '')
        . '>' . $inhoud . '</table:table-cell>' . str_repeat('<table:covered-table-cell/>', $span - 1);
    $hokje = $cel('Invulcel', $p('Hokje', '☐'));

    // Een cel met tekst: de eerste regel gewoon, de rest kleiner. Alleen een hokje staat in het midden.
    $tekst = static function (string|array $inhoud) use ($p): string {
        $regels = array_values(array_filter((array) $inhoud, 'is_string'));
        if ($regels === ['☐']) {
            return $p('Hokje', '☐');
        }

        // Een regel met hokjes, zoals "☐ Is er al   ☐ Komt er", is om in te vullen en blijft groot.
        return $p('Keuzetekst', (string) array_shift($regels)) . implode('', array_map(
            static fn (string $r): string => $p(str_starts_with($r, '☐') ? 'Keuzetekst' : 'Keuzeuitleg', $r),
            $regels
        ));
    };

    $balk = static fn (array $d): string => '<table:table-row>' . $cel('Nummercel', $p('Vraagnummer', (string) $d['nr']))
        . $cel('Vraagcel', '<text:p text:style-name="Vraagtekst">' . cm_odt_tekst($d['vraag'])
            . (($d['bijzin'] ?? '') === '' ? '' : ' <text:span text:style-name="Bijzin">' . cm_odt_tekst($d['bijzin'])
                . '</text:span>') . '</text:p>'
            . implode('', array_map(static fn (string $n): string => $p('Blokuitleg', $n), $d['noten'] ?? [])), $breed)
        . '</table:table-row>';

    $out = '<table:table table:name="' . $naam . '" table:style-name="'
        . (isset($v['delen']) || $tabel !== null ? 'Vraagblok_breekbaar' : 'Vraagblok') . '">';
    foreach ($breedtes as $i => $breedte) {
        $stijlen .= '<style:style style:name="' . $naam . '.K' . $i . '" style:family="table-column">'
            . '<style:table-column-properties style:column-width="' . $breedte . 'cm"/></style:style>';
        $out .= '<table:table-column table:style-name="' . $naam . '.K' . $i . '"/>';
    }

    // De rijen die bovenaan een volgende pagina terugkomen: de kop van een
    // groep, of bij een losse vraag de vraag en de kop van de tabel.
    if (isset($v['kop'])) {
        $out .= '<table:table-header-rows><table:table-row>' . $cel('Groepkopcel', $p('Groepkop', $v['kop']['titel'])
            . implode('', array_map(static fn (string $n): string => $p('Groepuitleg', $n), $v['kop']['noten'] ?? [])),
            $breed + 1) . '</table:table-row></table:table-header-rows>';
    } else {
        $kop = $balk($delen[0]);
        if ($tabel !== null) {
            $kop .= '<table:table-row>' . $cel('Invulcel', '<text:p text:style-name="Kolomkop"/>')
                . implode('', array_map(static fn (string $k): string => $cel('Invulcel', $p('Kolomkop', $k)), $tabel['kop']))
                . '</table:table-row>';
        }
        $out .= '<table:table-header-rows>' . $kop . '</table:table-header-rows>';
    }

    foreach ($delen as $d => $deel) {
        if (isset($v['kop'])) {
            $out .= $balk($deel);
        }

        foreach ($deel['keuzes'] ?? [] as $keuze) {
            $out .= match (true) {
                isset($keuze['kop']) => '<table:table-row>' . $cel('Groepcel', $p('Kolomkop', $keuze['kop']), $breed + 1),
                !empty($keuze['los']) => '<table:table-row table:style-name="Rij">' . $cel('Invulcel', $tekst($keuze), $breed + 1),
                default => '<table:table-row table:style-name="Rij">' . $hokje . $cel('Invulcel', $tekst($keuze), $breed),
            } . '</table:table-row>';
        }

        if (isset($deel['vak'])) {
            $rij = $naam . '.R' . $d;
            $stijlen .= '<style:style style:name="' . $rij . '" style:family="table-row"><style:table-row-properties '
                . 'style:min-row-height="' . sprintf('%.2Fcm', 0.3 + 0.55 * max(1, (int) $deel['vak'])) . '"/></style:style>';
            $out .= '<table:table-row table:style-name="' . $rij . '">'
                . $cel('Invulcel', '<text:p text:style-name="Keuzetekst"/>', $breed + 1) . '</table:table-row>';
        }
    }

    // Voor elke rij van een tabel staat een hokje, of wat 'voor' zegt: één tekst, of een lijst per rij.
    if (isset($tabel['hoogte'])) {
        $stijlen .= '<style:style style:name="' . $naam . '.T" style:family="table-row"><style:table-row-properties '
            . 'style:min-row-height="' . sprintf('%.2Fcm', (float) $tabel['hoogte']) . '" fo:keep-together="always"/>'
            . '</style:style>';
    }
    foreach ($tabel['rijen'] ?? [] as $r => $rij) {
        $voor = $tabel['voor'] ?? '☐';
        $voor = is_array($voor) ? (string) ($voor[$r] ?? '') : $voor;
        $out .= '<table:table-row table:style-name="' . (isset($tabel['hoogte']) ? $naam . '.T' : 'Rij') . '">'
            . $cel('Invulcel', $voor === '☐' ? $p('Hokje', '☐') : $p('Kolomkop', $voor));
        foreach (array_values($rij) as $inhoud) {
            $out .= $cel('Invulcel', $tekst($inhoud));
        }
        $out .= '</table:table-row>';
    }

    return $out . '</table:table>';
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
        $out .= '<table:table-row table:style-name="Rij">';
        foreach (array_values($rij) as $i => $inhoud) {
            $out .= $cel($inhoud, $i === 0 && !empty($tabel['rijkop']));
        }
        $out .= '</table:table-row>';
    }

    return $out . '</table:table>';
}

/**
 * Een zip met de bestanden in de gegeven volgorde. Heeft PHP zlib, wat bijna
 * altijd zo is, dan wordt alles behalve 'mimetype' gecomprimeerd; dat scheelt
 * veel bij de lettertypen. Zonder zlib blijft alles ongecomprimeerd.
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
        $methode = $naam !== 'mimetype' && function_exists('gzdeflate') ? 8 : 0;
        $data = $methode === 8 ? (string) gzdeflate($inhoud, 6) : $inhoud;

        $register .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, $methode, $tijd, $datum, $crc,
            strlen($data), $grootte, strlen($naam), 0, 0, 0, 0, 0, strlen($archief)) . $naam;
        $archief .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, $methode, $tijd, $datum, $crc,
            strlen($data), $grootte, strlen($naam), 0) . $naam . $data;
    }

    return $archief . $register . pack('VvvvvVVv', 0x06054b50, 0, 0,
        count($bestanden), count($bestanden), strlen($register), strlen($archief), 0);
}

/** Stuurt het document als .odt-download en stopt het verzoek. */
function cm_stuur_odt(array $blokken, string $bestandsnaam, string $auteur = '', string $kenmerk = '', bool $blad = false): never
{
    $odt = cm_odt($blokken, $auteur, $kenmerk, $blad);

    // Een site of host kan al een outputbuffer hebben lopen; die hoort niet in het bestand.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.oasis.opendocument.text');
    header('Content-Disposition: attachment; filename="' . $bestandsnaam . '"');
    header('Content-Length: ' . strlen($odt));
    echo $odt;
    exit;
}
