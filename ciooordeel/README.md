# CIO oordeel

Een formulier van de [cisomatic](../README.md) voor de adviseur van een CIO
office. Het bouwt het oordeel van de departementale CIO over een project met
een grote ICT-component op, en levert het rapport.

Hoe het formulier draait, bewaart en het document maakt, staat in de README van
de motor. Hier staat wat alleen dit formulier doet.

## Kaders

- **Besluit CIO-stelsel Rijksdienst 2026**, artikel 4 onder k en artikel 5 lid 2
  en 3: de CIO oordeelt over beheersing, haalbaarheid, risico's en implicaties,
  in elke fase. Voor de start is een positief oordeel nodig, of een
  beargumenteerde afwijking door de SG. Het besluit noemt geen bedrag; de grens
  van 5 miljoen euro ICT komt uit het Handboek Portfoliomanagement Rijk en het
  Rijks ICT-dashboard.
- **Toetskader projecten 2026** van het Adviescollege ICT-toetsing (juli 2026):
  de negen risicogebieden en hun toetsaspecten. Het formulier zet ze in
  eenvoudiger woorden, met dezelfde nummering.
- **Kwaliteitskader CIO-oordelen** (CIO Rijk, 10 mei 2022): voegt
  informatiebeveiliging en privacy, en duurzame toegankelijkheid (archiveren by
  design) toe. De tekst van dit kader is niet openbaar. Gebied 10 en 11 zijn
  daarom een eigen uitwerking, op de BIO2, de AVG, het cloudbeleid, de
  Archiefwet 2026, het DUTO-raamwerk en de MDTO. Die aspecten dragen `'eigen'`
  in `CO_GEBIEDEN`, net als het aspect over digitale toegankelijkheid bij
  gebied 4.
- **Werkwijze**: de opbouw van het rapport (kern, wat goed gaat, verbeterpunten,
  aanbevelingen) en de drie minimale stukken (business case, planning,
  architectuurschets) komen van de Rijksorganisatie ODI.

Komt het Kwaliteitskader beschikbaar, pas dan gebied 10 en 11 en de vier
oordelen daarop aan.

## Een aspect

Elk toetsaspect is een regel die openklapt, met de `groep` van de motor. Een
regel zonder oordeel staat open; `regels.js` houdt de samenvatting op de regel
bij. Per aspect:

| Sleutel | Vraag |
| --- | --- |
| `co_<gebied>_<n>` | Op orde, aandachtspunt, risico, niet te beoordelen, of niet van toepassing |
| `_bevinding` | Wat de adviseur zag, met de bron. Verplicht behalve bij op orde |
| `_advies` | Bij een aandachtspunt of risico. Verplicht bij een risico |
| `_voorwaarde` | Bij een risico: wordt het advies een voorwaarde? |

## Minder klikken

- **Snelle keuze per gebied** (`co_<gebied>_snel`): bij "Ja, alles is op orde"
  stelt `regels.js` bij elk aspect van dat gebied "Op orde" voor, en klapt de
  regels dicht. Een aspect dat de adviseur zelf koos, blijft staan. Bij "Nee,
  er speelt iets" wist een leeg voorstel de voorgestelde "Op orde" weer. De
  server kent de snelle keuze niet: zonder script vul je elk aspect zelf in.
- **Wat niet geldt** (`CO_TOEPASSING`): vier vragen in het onderzoek verbergen
  aspecten. Zonder uitbesteden vallen 8.2 tot en met 8.12, 10.7 en 11.8 weg,
  zonder cloud 10.5, zonder persoonsgegevens 10.4, en zonder algoritme of AI
  4.5 en 10.6. Een verborgen aspect telt niet mee en staat niet in het rapport.
  Een systeem in beheer verbergt niets, want een grote wijziging is ook een
  project.

## Rekenen

- **Gebied**: het zwaarste oordeel. Hoog bij een risico, Midden bij een
  aandachtspunt of iets wat niet te beoordelen was, anders Laag.
- **Voorstel**: negatief bij `CO_GRENS_NEGATIEF` (3) of meer gebieden op Hoog.
  Anders onder voorwaarden als er een voorwaarde is, met aanbevelingen als er
  iets Midden of Hoog is, en anders positief.

Die regel is een eigen vereenvoudiging, want een openbare regel bestaat niet.
Het rapport zegt dat ook. Kiest de adviseur een ander oordeel, dan vraagt het
formulier waarom (`co_afwijking`, via de uitkomst `afwijking`).

`afleiding.php` rekent, `regels.js` rekent hetzelfde tijdens het invullen. De
gebieden, keuzes en oordelen komen via `co_model()` als `data-groepen` op het
uitkomstblok van het oordeel. `regels.js` schrijft ook een eerste versie van de
kern (`co_kern`), zolang de adviseur die niet aanraakt.

## Overnemen

Een bestand van de business case, quickscan 2.0, de DPIA, de
soevereiniteitsscan, de AI scan of de IAMA vult de gedeelde sleutels in:
`projectnaam`, `organisatieonderdeel` en `proces_omschrijving`. `aanvrager` en
`email` blijven leeg, want het oordeel is van de adviseur en niet van wie het
andere formulier invulde.

## Het rapport

Na de titelpagina de kern: het oordeel, de tekst van de adviseur, een afwijking
van het voorstel, de voorwaarden en een tabel met het risico per gebied. Dan
wat goed gaat (aspecten op orde met een bevinding), wat beter kan, de
aanbevelingen en wat niet te beoordelen was. Daarna elk gebied met alle
aspecten, het onderzoek, de opvolging met ruimte voor de reactie van de
opdrachtgever, en hoe het voorstel tot stand komt.
