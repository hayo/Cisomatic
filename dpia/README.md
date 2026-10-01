# DPIA

Een formulier van de [Compliancomatic](../README.md) voor een Data Protection
Impact Assessment. Het volgt de opbouw van het Model DPIA Rijksdienst, en maakt
van de antwoorden meteen een document: colofon, inleiding, de vier
hoofdstukken, de risicotabel en de maatregelen.

Hoe het formulier draait, bewaart en het document maakt, staat in de README van
de motor. Hier staat wat alleen de DPIA doet. Het document krijgt een
titelpagina, en elk hoofdstuk begint op een nieuwe pagina
(`hoofdstuk_per_pagina`).

## White label

Er staat geen organisatie in, en geen namen. De rollen heten opsteller,
privacyfunctionaris en functionaris gegevensbescherming. Namen, datum en
handtekening komen op het document zelf. De kaders zijn bijgewerkt: de BIO in
plaats van de BIR 2017, de Archiefwet 2026, en de beleidsstukken van één
ministerie zijn eruit.

## Twee delen, dertien stappen

Het sjabloon zegt het zelf: hoofdstuk 2 vul je nooit alleen in. De eerste vraag
is daarom wat je nu gaat doen.

- **Alleen de beschrijving.** Acht stappen, hoofdstuk 1. Het document heeft dan
  lege hoofdstukken 2 tot en met 4. Download de
  antwoorden en laad ze later weer in.
- **Alles, samen met de privacyfunctionaris.** Daarna volgen rechtsgrond (met
  de bijzondere gegevens), noodzaak, rechten, risico's en conclusie.

Optionele vragen staan per stap in een uitklapblok, zodat de hoofdroute alleen
bevat wat nodig is.

## Wat het anders doet dan het Word-sjabloon

| Stond in het sjabloon | Nu |
| --- | --- |
| Per groep betrokkenen een tabel met "Normaal/Bijzonder" om weg te halen | Eén aankruislijst, gelijk aan die van de quickscan; of iets bijzonder is, volgt uit de groep |
| Dezelfde tabel voor elke groep | Eén vraag: welke gegevens heb je maar van een deel van de groepen? Alleen bij die gegevens kies je de groepen, met alle groepen alvast aangevinkt. Het document maakt er per groep een tabel van |
| Een lege kolom "Bron" | Per gegeven een keuzelijst, met een voorstel: een IP-adres komt uit het systeem, een naam van de betrokkene |
| Een scope om te schrijven | Voorgevuld met het proces en het systeem; alleen wat erbuiten valt, schrijf je zelf |
| Zinnen met `[VUL IN]` onder vastleggen, raadplegen, overdragen | Aan te kruisen handelingen; bewaren en vernietigen volgen uit de bewaartermijn |
| Een ketenplaat als plaatje | Een lijst met stromen, met de partijen en groepen als suggestie; de pagina tekent er een schema van |
| Een tabel met kruisjes per rol | Eén rij per partij en rol |
| Belangenafweging a tot en met e, altijd | Alleen bij gerechtvaardigd belang; de waarborgen alleen als de eerste afweging niet duidelijk is |
| Wet- en beleidskaders om aan te vullen | De tool zet de kaders die gelden op een rij, met het BSN en de AI-verordening alleen als ze van toepassing zijn |
| Een risicotabel met voorbeeldteksten en lege cellen | Elk risico één regel met een voorstel voor impact, kans en omvang; open vanaf Hoog |
| Genomen maatregelen en aanbevolen maatregelen apart | Per maatregel één keuze: is er al, komt er, of niet nodig |
| Een losse tabel met restrisico's, een conclusie en een maatregelenlijst | Alle drie volgen uit de keuzes per risico |

## De quickscan inladen

Waar een vraag ook in de quickscan staat, is de sleutel gelijk. Een bestand dat
je op de adviespagina van de quickscan downloadt, kun je hier inladen, en die
antwoorden staan dan klaar: projectnaam, aanvrager, e-mail, afdeling, proces,
systeem, de persoonsgegevens, doel, grondslag, aantal betrokkenen,
subverwerkers, cloud, AI, bewaartermijn en de rechten van betrokkenen.

De quickscan kent ook een rol, maar die betekent daar iets anders. Die wordt
overgeslagen. Een antwoord dat hier niet bestaat, zoals
"weet ik niet", laat de vraag leeg. De aanleiding wordt dan "uit de quickscan
bleek dat een DPIA nodig is". Dat staat onder `overnemen` in `definitie.php`.
Een bestand van de DPIA zelf heeft `"soort": "dpia"`, en laadt alles terug.

## Risico's

Er zijn dertien standaardrisico's: de elf uit het sjabloon, en twee die alleen
verschijnen als ze gelden. Doorgifte buiten de EER verschijnt bij een cloud of
subverwerker buiten de EER. Besluiten zonder mens verschijnt bij
geautomatiseerde besluitvorming of profilering. Het risico over toestemming
verschijnt alleen als toestemming de grondslag of de uitzondering is. Eigen
risico's staan in het uitklapblok onder de risico's.

Elk risico is één regel, bijvoorbeeld "impact hoog · kans midden · omvang
hoog". Zolang de omvang onder Hoog blijft, staat de regel dicht. Vanaf Hoog
klapt hij open, en daarbinnen staan:

- per standaardmaatregel de keuze: is er al, komt er, of niet nodig, met
  daaronder ruimte voor andere maatregelen;
- impact en kans, met een voorstel en de uitleg waarom;
- de omvang, uit de tolerantiematrix van het sjabloon;
- de kans met de nieuwe maatregelen, zodra er een maatregel bij komt.

Bij Hoog en Zeer hoog moet er minstens één maatregel bij komen: een keuze "komt
er", of een regel in het tekstvak met andere maatregelen die er komen. De vraag
naar de maatregelen is daarom alleen verplicht zolang dat tekstvak leeg is.
Blijft er met de maatregelen een risico van Hoog of hoger over, dan staat
bovenaan dat eerst de Autoriteit Persoonsgegevens geraadpleegd moet worden
(artikel 36 AVG).

**Wat al vaststaat, is al gekozen.** Een aantal maatregelen volgt uit eerdere
antwoorden: de privacyverklaring en het bericht bij verzamelen (risico 2 en 3),
de regeling en het systeem voor verzoeken (risico 6), het register (risico 7),
de bewaartermijn en automatisch vernietigen (risico 11), en de
verwerkersovereenkomst en het modelcontract (risico 12).

**Voorstellen, geen regels.** Het voorstel voor impact en kans is een
startwaarde die `regels.js` uitrekent: bijzondere of identificerende gegevens
geven Hoog, kinderen en grote aantallen een stap hoger, en bij risico's waar de
schade pas via een klacht of boete ontstaat een stap lager. De kans volgt uit
antwoorden zoals "betrokkenen worden niet geïnformeerd", en gaat een stap omlaag
als alle maatregelen die nodig zijn er al zijn. Het antwoord telt; de server
gebruikt de voorstellen niet.

Zonder JavaScript staat een risico open zolang er iets in te vullen is. De
voorstellen, de suggesties en de uitleg bij een risico verschijnen alleen met
script.

## Het document

Het schema van de gegevensstromen staat alleen op de pagina; in het document
staat de tabel.

## Opbouw

```
definitie.php     teksten, het overnemen uit de quickscan, en de functies voor de motor
vragen.php        de vragenlijst
risicos.php       standaardrisico's, categorieën, tolerantiematrix
afleiding.php     uitkomsten, groepen, doorgifte, de stand van een risicoregel, aandachtspunten
rapport.php       het document als blokken
vooraf.php        "Voor je begint"
samenvatting.php  de DPIA in het kort, en wat eerst geregeld moet zijn
regels.js         voorstellen, uitkomsten, risicoregels en suggesties in de browser
```

`dp_uitkomst()` en `dp_omvang()` staan ook in `regels.js`. Pas je de een aan,
pas dan ook de ander aan.

## Beperkingen

- Het document vervangt het oordeel van de functionaris gegevensbescherming niet.
- De voorstellen voor impact en kans zijn een aanname van de tool, geen regel
  uit het model.
- Het schema zet partijen in kolommen op volgorde van de stromen. Bij veel
  stromen die kriskras lopen, blijft een getekende ketenplaat duidelijker.
- De versietabel heeft één regel: de huidige versie. Eerdere versies staan in
  eerdere documenten.
