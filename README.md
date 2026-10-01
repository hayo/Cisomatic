# Cisomatic

Webformulieren voor het papierwerk rond een IT-project. Je kiest een formulier,
beantwoordt de vragen stap voor stap, en krijgt meteen een document:
op de pagina, en als OpenDocument-tekst (`.odt`).

Demo: https://hayobethlehem.nl/forge/cisomatic

Er zijn nu negen formulieren:

- **[businesscase](businesscase/README.md)**: de business case en intaketoets,
  vóór alles. Vijf toetsen: is het nodig, kan het zonder nieuw systeem, is er al
  iets, is het jullie taak, en kunnen jullie het dragen? Met een advies aan het
  MT over het risico.
- **[quickscan2](quickscan2/README.md)**: de quickscan voor de BIO2 en de
  Cyberbeveiligingswet, voor ministeries, diensten en agentschappen. Te
  beschermen belangen, cloudbeleid 2026, BIO2-maatregelen, analyses.
- **[socialemedia](socialemedia/README.md)**: de kanalenscan voor sociale media,
  voor het Rijk. Volgt één kanaal door de hele cyclus: starten, de jaarlijkse
  toets, en vertrekken. Levert een comply or explain, of een exitplan.
- **[quickscan](quickscan/README.md)**: de IB & Privacy Quickscan, op de BIO 1.
  Welk beveiligingsniveau geldt, welke eisen, welke analyses.
- **[dpia](dpia/README.md)**: een Data Protection Impact Assessment in de opbouw
  van het Model DPIA Rijksdienst.
- **[aiscan](aiscan/README.md)**: de AI scan, op de Beslishulp AI-verordening van
  BZK. Geldt de verordening, welke rol, welke risicogroep en welke plichten. Bij
  generatieve AI ook de toets aan het standpunt en de handreiking van BZK.
- **[iama](iama/README.md)**: een Impact Assessment Mensenrechten en Algoritmes,
  versie 2 uit 2026. In sessies, met een advies over het gebruik van het algoritme.
- **[soevereiniteit](soevereiniteit/README.md)**: de soevereiniteitsscan voor een
  clouddienst, op het Cloud Sovereignty Framework van de Europese Commissie. Een
  label van A tot E, en per thema of het hoog genoeg is voor de informatie.
- **[ciooordeel](ciooordeel/README.md)**: het CIO oordeel, voor de adviseur van
  het CIO office. Elf aandachtsgebieden uit het toetskader van het AcICT en het
  Kwaliteitskader CIO-oordelen, een voorstel voor het oordeel, en het rapport.

Plain PHP, geen Composer, geen build, geen afhankelijkheden.

## Opbouw

Eén motor, en per formulier een map.

```
index.php                 de voordeur: een lijst van de formulieren
config.php                welke formulieren, tijdzone, limieten, bewaren
assets/app.css            vormgeving van voordeur en formulieren, op tokens
assets/standalone.css     de huisstijl: de tokens en een basis voor de pagina
assets/app.js             de motor in de browser: stappen, zichtbaarheid, rijen, bewaren, inladen
src/bootstrap.php         laadt alles; cm_config(), cm_asset(), cm_formulier(), cm_werkplek(), cm_verwerk()
src/helpers.php           escapen, niveaus, datum, opsommingen, bestandsnaam
src/antwoorden.php        inlezen en controleren
src/formulier.php         formulier naar HTML
src/opmaak.php            documentblokken naar HTML, en het schema als SVG
src/odt.php               documentblokken naar .odt; de zip zonder extensies
src/opslag.php            wegschrijven naar 'bewaar_map', als dat aan staat
src/pagina.php            de inhoud: formulier of resultaat, als <article>
src/resultaat.php         de pagina na het versturen
src/kiezer.php            de voordeur als <article>
src/document.php          html, head en body eromheen, met app.css en standalone.css
bewaard/                  opgeslagen documenten, als bewaren aan staat

<formulier>/index.php         roept de motor aan met de naam van de map
<formulier>/definitie.php     teksten, bestanden en de functies die de motor aanroept
<formulier>/vragen.php        de vragenlijst
<formulier>/vooraf.php        "Voor je begint", boven het formulier
<formulier>/samenvatting.php  de uitkomst in het kort, boven het document
<formulier>/regels.js         wat het formulier in de browser uitrekent
```

De motorfuncties heten `cm_`, die van de quickscan `qs_`, die van quickscan 2.0
`q2_`, die van de DPIA `dp_`, die van de IAMA `ia_`, die van de business case
`bc_`, die van de kanalenscan `sm_`, die van de soevereiniteitsscan `sv_`, die
van de AI scan `ai_`, `gai_` voor generatieve AI, en die van het CIO oordeel
`co_`. Een formulier gebruikt de motor; de motor kent geen formulier bij naam.

## Draaien

Kopieer de map naar een server met PHP 8.1 of nieuer, en open `index.php`. De
map moet onder de documentroot staan, want daaruit leidt `cm_asset()` de URL's
af. Elke pagina is een hele pagina met de neutrale opmaak van
`assets/standalone.css`.

Lokaal: `php -S localhost:8000` in de map, en dan `http://localhost:8000/`.

## Een formulier toevoegen

1. Maak een map naast `src/`, met een `index.php` gelijk aan die van de andere
   formulieren.
2. Zet de naam van de map in `config.php`, onder `formulieren`.
3. Schrijf `definitie.php`, `vragen.php`, `vooraf.php`, `samenvatting.php` en
   `regels.js`. De voordeur toont het formulier vanzelf.

De motor verwacht een vraag met de sleutel `projectnaam`: die geeft het
document en het bestand een naam.

### definitie.php

Alleen gegevens, geen code. De voordeur leest dit bestand ook.

| Sleutel | Betekenis |
| --- | --- |
| `titel` | De kop van het formulier, en de naam op de voordeur |
| `naam` | Kort, midden in een zin: "Een nieuwe *quickscan* invullen" |
| `intro` | De alinea onder de kop, en op de voordeur |
| `uitvoer` | Wat eruit komt: *advies* of *document*. Staat op de knoppen |
| `download_uitleg` | De alinea onder de downloadknop |
| `inladen` | Uitleg bij het inladen van een bestand |
| `voet` | De voetregel |
| `stappen` | Per stap het stempel boven een sectie, zoals "Stap 1 · beschrijving" |
| `route` | De vraag die bepaalt hoeveel stappen er zijn. Tot die beantwoord is, noemt de voortgang geen totaal |
| `hoofdstuk_per_pagina` | In het `.odt`-bestand begint elk hoofdstuk op een nieuwe pagina |
| `overnemen` | Per ander formulier: `naam`, welke sleutels over te `slaan`, welke waarden te `zet`ten, en welke antwoorden het `als` een andere sleutel overneemt (`van => naar`) |
| `bestanden` | De PHP-bestanden van het formulier, in laadvolgorde |
| `secties` | Functie die de secties geeft |
| `evalueer` | Functie die van de antwoorden de uitkomst maakt |
| `document` | Functie die van antwoorden en uitkomst de documentblokken maakt |
| `uitkomst` | Optioneel: functie voor `toon_als_uitkomst` en `verplicht_als_uitkomst` |
| `valideer` | Optioneel: functie met controles bovenop de invulplicht |

### vragen.php

| Sleutel | Betekenis |
| --- | --- |
| `key` | Unieke sleutel. Gelijke sleutels in twee formulieren laten een bestand van het een het ander invullen |
| `type` | `text`, `email`, `textarea`, `radio`, `checkbox`, `select`, `rijen`, `maatregelen`, `afgeleid` of `kop` |
| `label`, `hint` | De vraag, en een korte verduidelijking |
| `opties` | Bij keuzes: waarde => `label`, `hint`, `groep`, en `uitleg` voor een volledige omschrijving onder de keuze |
| `verplicht` | Moet altijd ingevuld zijn |
| `verplicht_als`, `toon_als` | Alleen verplicht of zichtbaar bij bepaalde antwoorden elders |
| `verplicht_als_uitkomst`, `toon_als_uitkomst` | Hetzelfde, op een uitkomst van het formulier |
| `optioneel` | Staat in het uitklapblok van de sectie of de groep |
| `aanvullend` | Bij een sectie of groepskop: de tekst op het uitklapblok |
| `hoogte`, `standaard` | Bij een tekstvak: het aantal regels, en de beginwaarde |
| `voorstel` | `regels.js` vult een waarde voor. Bij een radiovraag staat "voorgesteld" bij die keuze |
| `motivatie_voor` | `regels.js` vult een tekst voor zolang niemand het veld aanraakt |
| `opties_uit` | Alleen de opties die in die andere vraag zijn aangekruist |
| `groepen` | Gegevens voor `regels.js`, als `data-groepen` op de vraag |
| `groep` | Vragen met dezelfde groep worden één regel die openklapt; de eerste is een kop. Met `stand` noemt die kop een functie die de samenvatting en de open stand geeft |
| `kolommen`, `rij_label`, `erbij`, `suggesties` | Bij rijen: de velden, het woord voor één rij, de knoptekst, en een functie met namen voor bij de velden |
| `bron`, `lijst` | Bij `afgeleid`: de naam voor `regels.js`, of een vaste lijst met voorwaarden |

Een hele sectie kan ook `toon_als` hebben. Vragen in een verborgen sectie worden
niet gesteld en tellen niet mee.

Type `maatregelen` geeft per maatregel drie keuzes: is er al, komt er, of niet
nodig. Hij telt als ingevuld zodra er minstens één maatregel bij komt.

### regels.js

Het bestand meldt zich aan met `cisomatic.regels({...})`. Alle functies
zijn optioneel.

| Functie | Wat de motor ermee doet |
| --- | --- |
| `voorstel(key, a)` | De waarde voor een vraag met `voorstel` |
| `motivatie(voor, a)` | De tekst voor een vraag met `motivatie_voor` |
| `uitkomst(naam, a)` | Voor `toon_als_uitkomst` en `verplicht_als_uitkomst`; spiegelt de PHP-functie |
| `afgeleid(bron, a)` | `{waarde, niveau, redenen}` voor een uitkomstblok |
| `ververs(form, a, opnieuw)` | Wat het formulier verder bijwerkt na elke invoer |

`cisomatic` levert ook hulpjes: `LABEL`, `elk`, `heeft`, `rang`,
`tekstregels`, `opsomming`, `optieLabel` en `toast`.

PHP blijft de bron van waarheid en rekent bij het versturen alles opnieuw uit.
Wat in beide staat, pas je in beide aan.

### Het document

`document` levert blokken: `[soort, inhoud]`. Soorten: `h1` tot en met `h4`,
`alinea`, `term` (label en tekst), `lijst`, `genummerd`, `citaat`, `lijn`,
`tabel` (met `kop`, `rijen`, `breedtes` in centimeters en `rijkop`) en `schema`
(stromen en een onderschrift, alleen op de pagina). `src/opmaak.php` maakt er
HTML van, `src/odt.php` het `.odt`-bestand, zodat die twee niet uit elkaar lopen.

Het `.odt`-bestand krijgt een vaste opbouw. De `h1` en alles tot de eerste `h2`
vormen de titelpagina, zonder voettekst. Daarna volgt een inhoudsopgave met de
`h2` en `h3`, en dan de hoofdstukken. De inhoudsopgave is een echte: elke kop
krijgt een bladwijzer, en het paginanummer is een verwijzing daarnaar.
LibreOffice vult de nummers bij het openen in. Word toont ze pas na "Veld
bijwerken". De voettekst toont de titel en "Pagina x van y". De opsteller
(`aanvrager`) staat als auteur in de eigenschappen van het bestand.

Alle tekst is platte tekst. Nadruk zit in de soort van het blok, dus een
antwoord wordt nooit per ongeluk vet of een kop.

## White label

De formulieren noemen geen organisatie. De rollen en de kaders staan per
formulier in zijn eigen README. Alleen `organisatie` in `config.php` is van één
organisatie: de taken uit haar besluit, en de diensten die daaruit volgen. De
business case biedt ze aan als keuzes. Zet de sleutel op `null` voor een
organisatie zonder lijst; dan vraagt het formulier om een toelichting.

## Opmaak

`assets/app.css` gebruikt alleen tokens: `--ink`, `--ink-hover`, `--pill-bg`,
`--pill-bg-hover`, `--line`, `--line-soft`, `--panel`, `--page`, `--text`,
`--muted`, `--muted-soft`, `--link`, `--on-ink`, `--pill-ink`, `--radius`,
`--font-ui` en `--ui-lg`, `--ui-md`, `--ui-sm`. `standalone.css` definieert ze,
voor licht en donker, en geeft de pagina een basis. Een eigen huisstijl is dus
één bestand aanpassen: `standalone.css`.

Het formulier heeft geen klassen. `app.css` kiest op element en op de
data-attributen die `app.js` toch al nodig heeft. Alles hangt onder
`#cisomatic`; wat bij één formulier hoort, kiest ook op
`data-formulier`.

`src/document.php` stuurt een Content Security Policy mee die geen inline
script of stijl toestaat. Daarom staat er geen `onclick` of `style` in de opmaak.

## Invullen

Het formulier gaat stap voor stap, met een voortgangsbalk en een knop "toon
alles". Die keuze wordt onthouden. Vanaf stap 2 verdwijnt de inleiding boven het
formulier. Per sectie wordt gecontroleerd of alles is ingevuld voordat je door
mag.

Zonder JavaScript staat het hele formulier onder elkaar, lijsten met rijen
hebben twee lege rijen extra, en de server controleert alles bij het versturen.
Knoppen die alleen met script werken, verschijnen pas als het script draait.

## Je werk raakt niet kwijt

Zodra je iets invult, wordt alles in `localStorage` bewaard, per formulier. Kom
je terug, dan vraagt het formulier of je wilt verdergaan. Op de resultaatpagina
download je de antwoorden als JSON, met de `soort` erin. Dat bestand laad je
later weer in, ook in een ander formulier. Beide gebeuren in de browser; er is
bewust geen server-endpoint dat opgeslagen antwoorden kan openen.

## Werkplek

Een host, bijvoorbeeld achter een login, kan het concept op de server bewaren,
met een lijst om verder te gaan en een archief. De host
roept `cm_werkplek()` aan vóór `cm_verwerk()`, met de antwoorden waarmee het
formulier opent, verborgen velden zoals het CSRF-token, en het adres van de
lijst. Het formulier krijgt dan `data-werkplek` en een knop "Bewaren".
`app.js` stuurt het concept dan niet naar `localStorage`, maar met `bewaar=auto`
naar de server, die met 204 antwoordt. Een concept houdt ook de antwoorden van
vragen die nu verborgen zijn: `cm_lees_antwoorden($post, true)`. Bij elke keer
bewaren rekent `cm_voortgang()` uit hoeveel verplichte vragen van toepassing
zijn, hoeveel daarvan ingevuld zijn, en of het document al gemaakt kan worden.
De lijst toont dat als een balk.

## Het document

De downloadknop stuurt de antwoorden als JSON terug naar de server. Die rekent
alles opnieuw uit en levert een `.odt` op, dus er wordt tussen twee verzoeken
niets bewaard. Dat werkt ook zonder JavaScript. "Kopieer" zet het document op
het klembord als opgemaakte tekst.

## Instellingen

Met `logo` staat er een beeld bovenaan elke pagina van het `.odt`-bestand,
tegen de bovenrand. `midden` zet het lint van een rijkslogo op het midden van
de pagina. Het logo staat standaard uit. Op een openbare installatie laat wie
het Rijkslogo aanzet elke bezoeker documenten met dat logo maken. Zet het dus
alleen aan op een eigen installatie binnen het Rijk.

`config.php` heeft geen geheimen. `bewaar_map` staat op `null`: de server
bewaart niets. Zet je hem op een map,
bijvoorbeeld `__DIR__ . '/bewaard'`, dan komen er per document twee bestanden
in: het `.odt`-bestand en de antwoorden als JSON, met datum, tijd, formulier en
projectnaam in de naam. De projectnaam wordt teruggebracht tot kleine letters,
cijfers en koppeltekens, dus er kan geen pad in de bestandsnaam komen.
`bewaard/.htaccess` houdt de bestanden op Apache buiten bereik van een URL.

## Toegankelijkheid

Keuzegroepen zijn een `fieldset` met `legend`, losse velden hebben een `label`
met `for`. Hints en foutmeldingen hangen via `aria-describedby` aan het veld,
ongeldige velden krijgen `aria-invalid`, en de foutsamenvatting krijgt focus
zodra die verschijnt. De stapaanduiding is een `role="status"`.
