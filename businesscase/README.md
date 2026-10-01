# Business case en intaketoets

Een formulier van de [cisomatic](../README.md) voor het begin van een project
bij het Rijk. Het komt vóór alle andere formulieren: eerst "moeten we dit wel
doen?", pas daarna "hoe doen we het veilig?". Van de antwoorden maakt het een
advies aan het MT: hoeveel risico heeft dit project? Het MT besluit daarna of
het project verder mag.

De toets beoordeelt niet of de oplossing goed is. Hij beoordeelt of het
probleem belangrijk genoeg is, en of een nieuw systeem nu de juiste stap is.

Hoe het formulier draait, bewaart en het document maakt, staat in de README van
de motor. Hier staat wat alleen dit formulier doet.

## Opbouw

| Stap | Secties |
| --- | --- |
| Over dit project | project, opsteller, afdeling |
| Toets 1 Nodig | het werk, het probleem, de burger, een plicht, wat niets doen kost, waarom nu |
| Toets 2 Zonder nieuw systeem | kan een andere werkwijze het oplossen? |
| Toets 3 Wat er al is | een eigen systeem, de plek in de domeinarchitectuur |
| Toets 4 Jullie taak | het mandaat en de taak uit het besluit, een voorziening van het Rijk, een oplossing van een andere overheid |
| Toets 5 Dragen | eigenaar, geld per jaar, akkoord van beheer en CISO, kennis, stoppen |
| Het voorstel | doel, oplossing, kosten, baten, inkoop |

## Toetsen

De intaketoets heeft vijf toetsen. Elke toets is een sectie die eindigt met een
uitkomstblok (`bc_toets()` in `vragen.php`). Elke optie van een getoetste vraag heeft een `niveau`:

- **l**, in orde;
- **m**, eerst uitzoeken;
- **h**, een andere route: niets doen, het werk verbeteren, inrichten wat er
  al is, aansluiten bij een ander, of eerst geld en beheer regelen.

Een toets krijgt het hoogste niveau van zijn vragen. Bij toets 1 is één reden
genoeg: een plicht, of schade die groter is dan de kosten. Daarom telt voor de
vragen in `of` alleen het laagste niveau. Het risico voor het MT is het hoogste
niveau van alle toetsen.

Niveaus, labels en teksten gaan via `groepen` als `data-groepen` op het
uitkomstblok. `regels.js` spiegelt alleen de rekenregel van
`bc_toets_niveau()`.

Er is geen ja of nee. "Nog niet uitgezocht" telt als eerst uitzoeken, zodat
wie het niet weet geen stille nee invult. Het formulier stopt nergens, want het
document is een advies. Het MT beslist.

## De taak van de organisatie

Toets 4 vraagt of het project bij de taak van de organisatie hoort. Staat er
`organisatie` in `../config.php`, dan kiest de invuller daarna de diensten waar
het bij hoort (`bc_taken`), gegroepeerd per taak uit het besluit.

Nu staat daar DPC. De taken komen uit artikel 11 van het Organisatiebesluit
Ministerie van Algemene Zaken (BWBR0040546, geldend sinds 27 januari 2018).
Het instellingsbesluit van DPC uit 2004 noemt geen taken; het maakt DPC alleen
een baten-lastendienst. De diensten onder de taken komen van de pagina van DPC
op rijksoverheid.nl. Die lijst is dus afgeleid, en verandert sneller dan het
besluit.

## Inkoop

`afleiding.php` bepaalt de inkoop uit de soort inkoop en het bedrag voor de
leverancier. De teksten staan in `BC_INKOOP`.

De Europese drempel voor leveringen en diensten van de rijksoverheid is
140.000 euro, van 1 januari 2026 tot en met 2027. De Europese Commissie past hem
elke twee jaar aan. Hij staat in `BC_INKOOP`, in de opties van
`bc_opdrachtwaarde`, en in de voetregel in `definitie.php`.

Boven 5 miljoen euro geeft de CIO van het ministerie een oordeel, en meldt het
ministerie het project bij het Adviescollege ICT-toetsing. Voor het CIO-oordeel
zijn een business case, een planning en een architectuurschets nodig.

## Overnemen

Het formulier deelt sleutels met quickscan 2.0: `projectnaam`, `aanvrager`,
`organisatieonderdeel`, `email`, `proces_omschrijving` en `inkoopvorm`. Na het
besluit laadt het team hetzelfde bestand in de quickscan. De IAMA neemt ook het
probleem en het doel over, via `als`: `bc_probleem` wordt `ia_aanleiding`, en
`bc_doel` wordt `ia_doel`. De AI scan neemt `bc_doel` over als
`ai_omschrijving`. De versie blijft overal staan.

## Het document

Na de titelpagina volgt het advies aan het MT: het risico, een tabel met de
uitkomst per toets, het probleem, het voorstel, de aandachtspunten en de
vervolgstappen. Daarna volgt elke toets als hoofdstuk, met zijn uitkomst. Aan
het eind staat het besluit van het MT, met drie vakjes: doorgaan, eerst meer
uitzoeken, of stoppen en een andere route kiezen.

## Herkomst

De toetsen komen uit de 5-poortentoets van een CIO-office, met de opmerkingen
van een collega verwerkt. Wat daaruit veranderde:

- Geen vergelijking met de gemiddelde kosten van een IT-traject, maar met de
  eigen schatting. Publieke waarde telt ook.
- De plicht is breder dan de BIO2: ook een wet, de Cyberbeveiligingswet of een
  andere bindende regel.
- De 80/20-regel is weg. De vraag naar een eigen systeem vraagt nu of de
  werkwijze zich aan dat systeem kan aanpassen.
- "Geen enkel systeem" werd "past het?": een systeem kan technisch passen,
  maar niet bij de rest. Toets 3 vraagt daarom waar het in de
  domeinarchitectuur staat.
- Toets 5 vraagt ook naar stoppen: wat als de oplossing niet meer nodig is?
- De naam poortwachterstoets verviel, omdat die ook bij ziekteverzuim hoort. Het
  woord poort verviel mee.
