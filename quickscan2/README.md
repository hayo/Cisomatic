# Quickscan 2.0: BIO2 en Cyberbeveiligingswet

Een formulier van de [Compliancomatic](../README.md) voor ministeries, hun
diensten en agentschappen. Het neemt de quickscan af die de BIO2 vraagt bij elk
nieuw systeem en bij elke grote wijziging, en rolt er meteen een advies uit:
of de Cyberbeveiligingswet geldt, hoe zwaar de te beschermen belangen zijn, wat
het cloudbeleid van 2026 toelaat, welke overheidsmaatregelen uit de BIO2 hier
gelden, en welke analyses nog nodig zijn.

Het is een afsplitsing van [quickscan 1.0](../quickscan/README.md). De
twee stappen, de ondergrens bij bijstellingen, "weet ik niet" met een aanname,
de privacyvragen en het hoofdstuk over de Archiefwet werken hetzelfde; dat staat
daar beschreven. Hier staat wat anders is.

## Wat anders is dan quickscan 1.0

| Quickscan 1.0 (BIO 1) | Quickscan 2.0 (BIO2) |
| --- | --- |
| De eerste vraag: valt er iets te beschermen? Zo niet, dan stopt de scan | Die vraag is er niet. Wie de quickscan invult, doet dat omdat het moet, dus er valt altijd iets te beschermen |
| Beveiligingsniveau BBN 1, 2 of 3 | Bestaat niet meer. Elk systeem krijgt de basis van de BIO2 (ISO 27002 plus de verplichte overheidsmaatregelen) en een risicoafweging. Zie [Risicoanalyse](#risicoanalyse) |
| Geen wettelijk kader voor de beveiliging zelf | De Cyberbeveiligingswet: wel of geen essentiële entiteit, afgeleid uit de soort organisatie |
| Geen te beschermen belangen | Zeven soorten schade uit bijlage 1 bij de Cyberbeveiligingsregeling sector overheid. Samen met de rubricering en de BIV-eisen geeft dat TBB 1 tot 4; bij TBB 1, 2 of 3 is het systeem cruciaal |
| Geen meldplicht | Welke incidenten bij dit systeem de drempels uit artikel 6 van de regeling halen, en de termijnen van 24 uur, 72 uur en een maand |
| Rubricering: staatsgeheim als één keuze | Staatsgeheim Confidentieel, Geheim en Zeer geheim apart, want die geven een ander TBB-niveau |
| Cloudcategorie Laag, Midden, Hoog of Zeer hoog | Het cloudbeleid van 2026: data alleen in de EER of Zwitserland, geen publieke cloud voor staatsgeheim of TBB 1 tot 3, e-mail en documenten alleen onder drie voorwaarden, sleutelbeheer, jurisdictie van buiten de EU, bijzondere persoonsgegevens en basisregistraties. Het oordeel is "toegestaan onder voorwaarden", "afgeraden" of "niet toegestaan" |
| Materieel cloudgebruik: proces, systeem en beschikbaarheid | De definitie uit het cloudbeleid: kerntaak, bedrijfskritisch, of grootschalige verwerking van persoonsgegevens. Dan volgen een risicoanalyse, een exitplan en een melding bij CISO Rijk. Bij bedrijfskritisch stelt `regels.js` ja voor bij een vitaal systeem in een (kritisch) strategisch proces |
| Politiek-bestuurlijk gevoelig: een losse vraag | Volgt uit de politieke schade bij de te beschermen belangen. Is die aangevinkt, dan kijkt de CIO mee |
| Een motivatie voor het proces, en een voor het systeem | Eén motivatie voor allebei, en in het advies één weging |
| Nauwelijks vragen over de techniek | Nieuwe sectie over bereikbaarheid, inloggen, MFA, logging en monitoring. Daaruit volgen de internetstandaarden, pentests, CVD, het Register Internetdomeinen Overheid en MFA (BIO2 5.14, 5.17, 8.08, 8.15, 8.16) |
| Leveranciers: land en subverwerkers | Nieuwe sectie over de keten: onafhankelijk bewijs voor de hele dienst, meldtermijn, overstapbaarheid, en of de leverancier zelf onder de wet valt (BIO2 5.19 tot 5.23). De vraag naar subverwerkers staat hier één keer, en telt ook voor de privacy |
| AI: een aankruislijst met de risicoklasse | Alleen of er AI in komt. Dan staat de [AI-scan](../aiscan/README.md) bij de analyses, want die bepaalt de risicogroep en de plichten |
| De CIO stelt mee vast | De proceseigenaar stelt vast, na advies van de CISO, en bij zware gevallen ook van de CIO. De BIO2 legt het risico bij de lijn |

De sleutels van de vragen die in beide quickscans staan, zijn gelijk. Een
bestand van quickscan 1.0 laadt hier dus in, behalve de rubricering. De DPIA leest de bestanden van beide quickscans.

## Risicoanalyse

Wat de teksten zeggen, en wat de tool zelf kiest:

- **Altijd.** Bij elk nieuw systeem en elke grote wijziging een risicoafweging
  met de risicomanagementmethodiek van de organisatie (BIO2 5.08.01). Die
  methode heeft een quickscan die bepaalt of de basis genoeg is (BIO2,
  paragraaf 6.2). Deze quickscan is daarvoor bedoeld.
- **Uit de tekst.** Zit de organisatie vast aan de leverancier, dan vóór het
  contract een risicoafweging of die afhankelijkheid beheersbaar is (BIO2
  5.20.06). Een cruciaal systeem komt op het overzicht van cruciale systemen
  (regeling, artikel 4).
- **Methode van deze quickscan.** Een volledige risicoanalyse bij een cruciaal
  systeem, een eis Zeer hoog, een hoog dreigingsprofiel, of een (kritisch)
  strategisch proces op een vitaal systeem. De BIO2, de Cbw en de regeling
  noemen geen drempel; ze laten die aan de organisatie. Het advies zegt dat er
  ook bij. Andere drempels van de organisatie gaan voor.

## Te beschermen belangen

Niemand kiest het TBB-niveau zelf. De invuller vinkt aan welke soorten schade
een incident kan geven (`tbb_soorten`), of kiest bewust ‘Geen van deze’. Die
twee sluiten elkaar uit: `regels.js` haalt het andere vinkje weg, en
`q2_valideer()` weigert de combinatie. Alleen bij een aangevinkte soort kiest de
invuller hoe zwaar die schade is. Elke keuze heet naar de soort, zoals
`tbb_politiek`.

`q2_tbb()` neemt daarna het zwaarste niveau uit:

- de gekozen schade;
- de rubricering: Departementaal Vertrouwelijk is TBB 4, Staatsgeheim
  Confidentieel TBB 3, Geheim TBB 2, Zeer geheim TBB 1;
- de BIV-eisen: één eis Zeer hoog is TBB 3, alle drie Zeer hoog is TBB 2.
  Zonder Zeer hoog is één eis boven Zeer laag al TBB 4. Zo staat het in
  bijlage 1: alleen Zeer laag, Zeer laag, Zeer laag haalt geen TBB.

De redenen zijn de opbouw: per bron een regel met zijn niveau, dan de uitkomst
en wat die betekent. Die opbouw staat in het uitkomstblok, als voorstel in de
motivatie (een regel per bron) en in het advies bij de Cyberbeveiligingswet.

De omschrijvingen per niveau staan in `q2_tbb_categorieen()` in `vragen.php`.
`regels.js` rekent hetzelfde uit, zodat de uitkomst al tijdens het invullen in
beeld staat.

## Opbouw

```
definitie.php     teksten en de functies voor de motor
vragen.php        de vragenlijst, en de tabel met te beschermen belangen
toelichting.php   de omschrijvingen van de BIV-niveaus en de classificaties
afleiding.php     wat we uitrekenen in plaats van vragen: BIV, TBB, Cbw, meldplicht, materieel cloudgebruik
beslisboom.php    de beslisregels: cloudbeleid, analyses, BIO2-maatregelen, leveranciers, wie kijkt mee
advies.php        het advies als blokken
vooraf.php        "Voor je begint"
samenvatting.php  de uitkomst in het kort, de analyses, de wegingen en het pad
regels.js         de afleidingen in de browser, de ondergrens en de meldingen
```

De functies heten `q2_`. `regels.js` herhaalt de afleidingen uit
`afleiding.php`; PHP blijft de bron van waarheid.

## Bronnen

- [BIO2, versie 1.3](https://www.bio-overheid.nl/bio2/bio-producten/baseline-informatiebeveiliging-overheid-2-bio2/)
  (9 januari 2026): de maatregelnummers in het advies verwijzen hiernaar.
- [Cyberbeveiligingsregeling sector overheid](https://wetten.overheid.nl/BWBR0052963)
  (Stcrt. 2026, 27679), geldend vanaf 15 augustus 2026: cruciale systemen
  (artikel 4 en bijlage 1), de BIO2 als invulling van de zorgplicht (artikel 5)
  en de drempels voor een significant incident (artikel 6).
- [Cyberbeveiligingswet](https://wetten.overheid.nl/BWBR0052872), geldend vanaf
  15 augustus 2026: essentiële entiteiten (artikel 5 en 8), de zorgplicht
  (artikel 21), de meldplicht (artikel 25 tot en met 29) en het nationale
  register (artikel 44).
- [Verplichtingen uit de Cyberbeveiligingswet](https://www.digitaleoverheid.nl/overzicht-van-alle-onderwerpen/cyberbeveiligingswet/verplichtingen-cyberbeveiligingswet/)
  en het [toezicht door de RDI](https://www.rdi.nl/onderwerpen/digitale-weerbaarheid/cyberbeveiligingswet/sectoren-onder-toezicht/overheid).
  De wet geldt sinds 15 augustus 2026.
- [Herziening rijksbreed cloudbeleid 2026](https://www.tweedekamer.nl/downloads/document?id=2026D35295)
  (3 juli 2026).
- [Generieke Baselinetoets BIO2](https://www.bio-overheid.nl/bio2/bio-producten/generieke-baselinetoets-bio2/),
  versie 3.2, met de gebruikershandleiding versie 1.2 (14 oktober 2026). Zie
  hieronder.

## Naast de baselinetoets BIO2

De baselinetoets stelt dezelfde vraag als deze quickscan: volstaat de basis van
de BIO2, of is een aanvullende risicoanalyse nodig? Hij is er voor elke
overheid. Deze quickscan is er alleen voor het Rijk, en volgt daarom bijlage 1
bij de regeling, de te beschermen belangen.

Waar ze hetzelfde zeggen:

- Staatsgeheim Confidentieel, Geheim of Zeer geheim: een volledige
  risicoanalyse. In de toets is dat het vroege afkappunt, hier TBB 3 of zwaarder.
- Een statelijke actor, of de hoogste impact: een volledige risicoanalyse. Hier
  is dat het hoge dreigingsprofiel, of een eis Zeer hoog.

In beide is dat een keuze van het instrument, geen drempel uit de BIO2.
- De overheidsmaatregelen gelden altijd, ook bij een gunstige uitkomst. Alleen
  bij eigen maatregelen mag een risico bewust geaccepteerd worden.
- De te beschermen belangen uit bijlage 1, met dezelfde niveaus. Alleen bij de
  betrouwbaarheidseisen wijkt tabblad 1b van de toets af van bijlage 1 (één eis
  Zeer hoog TBB 3, meerdere TBB 2, alle drie TBB 1). Deze quickscan volgt
  bijlage 1 van de regeling: één eis Zeer hoog TBB 3, alle drie TBB 2, en tot
  en met Hoog TBB 4.

Wat de toets anders doet:

- Hij rekent met kans maal impact, op vijf niveaus. Deze quickscan kent geen
  kans; het dreigingsprofiel neemt die plaats in.
- Hij vraagt naar twintig soorten impact uit bijlage 2, voor decentrale
  overheden. Het Rijk vult in de toets ook die in. Deze quickscan doet dat niet:
  voor het Rijk geldt bijlage 1.
- Met de werkwijze Basis is een impact Hoog op één aspect al genoeg voor een
  aanvullende risicoanalyse. Zo geeft een RPO van vier uur of minder daar altijd
  een analyse. Hier geeft de RPO alleen de eis aan de back-up. De tekst van de
  BIO2, de Cbw en de regeling is leidend, en die noemen voor het Rijk zo'n
  drempel niet. De BIO2 laat de RPO over aan een risicoafweging (8.13.02). In
  de regeling maakt een RPO van vier uur een systeem alleen cruciaal bij een
  decentrale overheid (bijlage 2, artikel 4, derde lid).
- TBB 3 is in de toets impact Midden, en dan is een analyse alleen het
  overwegen waard. De regeling zegt het anders: bij TBB 1, 2 of 3 is een
  systeem van het Rijk cruciaal (artikel 4, tweede lid). Deze quickscan volgt
  de regeling. Dat er bij een cruciaal systeem een volledige risicoanalyse
  volgt, is een keuze van de tool; de regeling vraagt alleen een overzicht van
  de cruciale systemen.
- De ISMS-volwassenheid kan in de toets het advies een niveau hoger of lager
  maken. Deze quickscan vraagt er niet naar.

## Beperkingen

- Het advies vervangt het oordeel van de CISO niet, en ook niet de volledige
  risicoanalyse die de BIO2 vraagt.
- Of een zbo onder de wet valt, hangt af van vier criteria. De tool gaat ervan
  uit dat dat zo is.
- De meldplicht noemt alleen de scenario's die uit de antwoorden volgen. Of een
  echt incident significant is, blijft een beoordeling per geval.
- De BIO2-maatregelen in het advies zijn een selectie op basis van de
  antwoorden, geen volledige verklaring van toepasselijkheid.
- De regels over materieel cloudgebruik gelden hier voor elke cloud van een
  externe leverancier. Het cloudbeleid spreekt vooral over publieke cloud; de
  tool kiest de voorzichtige lezing.
- De uitsluiting van landen werkt op de naam die de invuller typt.
