# IAMA

Een formulier van de [cisomatic](../README.md) voor het Impact Assessment
Mensenrechten en Algoritmes, versie 2 uit 2026. Het volgt de vijf delen van het
IAMA: waarom, wat, hoe, grondrechten en afsluiting. Van de antwoorden maakt het
meteen een document, met een titelpagina en elk deel op een nieuwe pagina.

Hoe het formulier draait, bewaart en het document maakt, staat in de README van
de motor. Hier staat wat alleen de IAMA doet.

## White label

Er staat geen organisatie in. De rollen zijn die uit het IAMA: projectleider,
data scientist, jurist, gespreksleider en de verantwoordelijke die besluit.
Namen, datum en handtekening voor het besluit komen op het document zelf.

## Sessies

Het IAMA raadt aan het gesprek op te knippen. De eerste vraag is daarom welke
sessie het team nu houdt:

- **Sessie 1**: deel 1 en 2.
- **Sessie 2**: ook deel 3 en vraag 4.1.
- **Alles**: ook 4.2 tot en met 4.5, waar de jurist vooraf uitzoekwerk voor doet,
  en deel 5.

Een sectie heeft een `sessie` en de bijbehorende `toon_als`. De
delen die nog komen, zeggen dat ze in
een volgende sessie volgen. Na elke sessie download je de antwoorden, en laad je
ze de volgende keer weer in.

## Wat het anders doet dan het PDF-sjabloon

| Stond in het sjabloon | Nu |
| --- | --- |
| Lege vakken per vraag | Een tekstvak per vraag; ja/nee-vragen zijn een keuze met een toelichting eronder |
| Het icoon ‘art. 27 AI-verordening’ | Een zin in de hint, en ‘(artikel 27 AI-verordening)’ achter de kop in het document |
| 1.3.1: is het een verboden AI-systeem? | Eerst: is het een AI-systeem? Dan 1.3.1 als keuze, met een verwijzing naar de [AI scan](../aiscan/README.md). Die zoekt de risicogroep uit, dus de IAMA heeft geen eigen aankruislijst |
| 2.2A en 2.2B, sla over wat niet geldt | Het type bij 2.1.1 bepaalt welke van de twee verschijnt |
| 2.5.1 alleen bij een externe partij | Een vraag wie het algoritme ontwikkelde; 2.5.1 verschijnt alleen bij een externe partij |
| 4.3 tot en met 4.5 alleen als het nodig is | Eén vraag na 4.2 of ze nodig zijn |
| 4.3: de ernst van ‘het’ grondrecht | Een regel per aangetast grondrecht, met de namen uit 4.1.2 als suggestie |
| 4.4.2 of 4.5.4 nee: terug naar de tekentafel | De rest van deel 4 en deel 5 verdwijnen, en het document zegt waarom |
| ‘Maak hier eventueel een actiepunt van’ | De tool zet die actiepunten zelf op de lijst, naast die van het team |
| Een vak voor restrisico’s | Rijen, met in het document een lege kolom om ze te aanvaarden of af te wijzen |

Drie vragen staan niet in het sjabloon: wie het algoritme ontwikkelde, wie het
besluit neemt (bij 3.2), en wie er in het team zitten. Het IAMA noemt het team
en de rol van de mens wel in de instructie; de tool heeft ze nodig voor de
aandachtspunten.

## Artikel 27

Het icoon staat in het sjabloon bij 1.1.2, 3.1.1, 3.1.3, 3.2.1, 3.2.2, 3.4.1,
3.4.2, 3.5.1 en 3.5.2. Die vragen hebben `art27`. Is het algoritme een AI-systeem
met een hoog risico (vraag 4.2.1), dan komen er twee actiepunten bij: toetsen aan
de eisen van de AI-verordening, en de uitkomst melden bij de
markttoezichthouder. Het tweede zegt erbij dat het niet hoeft bij alleen
kritieke infrastructuur, want daarvoor geldt artikel 27 niet.

## Overnemen

Een bestand van quickscan 2.0 vult de algemene vragen in: projectnaam,
opsteller, afdeling, e-mail en het proces bij 3.1.1. Een bestand van de DPIA doet dat ook, maar laat de versie staan.
Omgekeerd leest de DPIA een bestand van de IAMA in, met als aanleiding ‘uit de
IAMA bleek dat een DPIA nodig is’.

Een bestand van de AI scan neemt de omschrijving over als doel bij 1.1.2.
Weet het team nog niet of het een AI-systeem is, of of het verboden is? Dan zet
de tool de AI scan bij de actiepunten.

## Voorstellen

Het formulier rekent weinig, want het IAMA is een gesprek. Bij 4.2.1 stelt
`regels.js` nee voor als het algoritme geen AI-systeem is. De ernst bij 4.3.3
kiest het team zelf; de hint geeft de vuistregel uit het IAMA.

De balans in deel 5 vraagt opnieuw hoe waarschijnlijk en hoe nodig het algoritme
is. Daar vult `regels.js` alvast de antwoorden van 4.4.1 en 4.5.1 in, zolang
niemand het tekstvak aanraakt.

## Het document

`rapport.php` loopt de vragenlijst zelf af: elke sectie een paragraaf, elke
vraag een kop met het antwoord. Wat alleen dit formulier gebruikt:

| Sleutel | Betekenis |
| --- | --- |
| `art27` | De vraag hoort bij artikel 27; dat staat in de hint en achter de kop |
| `bij` | Hoort bij de vraag ervoor, en krijgt geen eigen kop. Bij een kolom: de regels komen in de cel van die kolom |
| `opsomming` | Een tekstvak met één punt per regel wordt een lijst |
| `kort`, `breedte` | Bij een kolom: de kop en de breedte in centimeters in de tabel |
| `extra_kolommen` | Lege kolommen om op papier in te vullen, met hun breedte |
| `sessie` | Bij een sectie: vanaf welke sessie hij meedoet |

## Opbouw

```
definitie.php     teksten, het overnemen uit quickscan en DPIA, en de functies voor de motor
vragen.php        de vragenlijst
afleiding.php     klasse, ernst, actiepunten, aandachtspunten en wat blokkeert
rapport.php       het document als blokken
vooraf.php        "Voor je begint"
samenvatting.php  de IAMA in het kort
regels.js         het voorstel bij 4.2.1, de suggesties bij 4.3 en de teksten in de balans
```

## Beperkingen

- Het document vervangt het oordeel van een jurist niet, en ook niet het gesprek.
- De toelichting bij het IAMA, met het CODIO-waardenkader en de
  grondrechtenclusters, zit niet in de tool.
