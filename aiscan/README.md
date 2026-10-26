# AI-scan

Een formulier van de [cisomatic](../README.md) dat uitzoekt wat de
AI-verordening van een toepassing vraagt. Het volgt de
[Beslishulp AI-verordening](https://github.com/MinBZK/ai-verordening-beslishulp)
van het ministerie van BZK. Gebruikt de toepassing generatieve AI, dan toetst
het ook het overheidsbrede standpunt en de handreiking over generatieve AI.

Hoe het formulier draait, bewaart en het document maakt, staat in de README van
de motor. Hier staat wat alleen deze scan doet.

## Bronnen

| Bron | Versie | Waarvoor |
| --- | --- | --- |
| Beslishulp AI-verordening, `decision-tree.yaml` | 1.0.0, commit `1ec10da` van 14 september 2026 | De vragen en de route, deel 1 en 2 |
| AI-verordening (EU) 2024/1689, na de Digital Omnibus | in werking 27 juli 2026 | De datums |
| Overheidsbreed standpunt voor de inzet van generatieve AI | 30 januari 2025 | De voorwaarden in deel 3 |
| Overheidsbrede handreiking verantwoorde inzet van generatieve AI | 30 januari 2025 | De tips in deel 3 |

De beslishulp staat onder de EUPL 1.2. De vragen zijn in eigen woorden op B1
herschreven; de route volgt de beslisboom.

## De route

De beslishulp is een beslisboom: elk antwoord bepaalt de volgende vraag.
`ai_route()` loopt die boom af, tot de eerste open vraag. Elke vraag uit de
boom heeft `toon_als_uitkomst` op zijn eigen sleutel, en `ai_uitkomst()` zegt of
hij op de route ligt. `regels.js` loopt dezelfde route in de browser.

Onderweg bouwt de route een profiel: de rollen, de soort, de risicogroep, en of
er transparantie, open source of een systeemrisico is. De beslishulp heeft ruim
veertig conclusies, één per combinatie. De scan stelt de plichten in plaats
daarvan samen uit het profiel, in `ai_plichten()`.

De vragen staan in de volgorde van de boom. Zo verwijst een vraag alleen naar
eerdere vragen, en dat verwacht de motor.

## Waar de scan afwijkt van de beslishulp

| Beslishulp | Scan |
| --- | --- |
| 1.4.1 is het een AI-systeem, en bij nee 1.4.2 is het een model of systeem voor algemene doeleinden | Eén vraag met alle soorten |
| 2.1 verboden, 2.4 bijlage III en 2.9 transparantie, elk een ja of nee met een lijst | De aankruislijst `ai_toepassing`. Quickscan 2.0 en de IAMA hebben hem niet meer: die verwijzen naar deze scan |
| 2.2 uitzonderingen op elk verbod | Alleen bij live biometrie in de openbare ruimte, want alleen daarvoor kent artikel 5 die uitzonderingen. De andere uitzonderingen staan als hint bij het verbod |
| 2.7.2 derde partij bij bijlage III | Volgt uit biometrie; geen vraag |
| 2.7.1 derde partij bij bijlage I, voor aanbieder en gebruiksverantwoordelijke | Alleen voor de aanbieder, want alleen diens plichten hangen ervan af |
| 1.5 uitzondering, ja of nee | Een keuze uit de uitzonderingen, zodat het rapport ze noemt |
| Overal 2 december 2027 | De datums van de gewijzigde verordening, zie hieronder |

## Datums

Sinds de Digital Omnibus van juli 2026 gelden deze datums. De beslishulp
noemt alleen de eerste voor een hoog risico.

| Wat | Vanaf |
| --- | --- |
| Verboden praktijken, kennis van AI | 2 februari 2025 |
| Modellen voor algemene doeleinden | 2 augustus 2025; modellen van daarvoor uiterlijk 2 augustus 2027 |
| Transparantie | 2 augustus 2026; markeren bij systemen van daarvoor 2 december 2026 |
| Verbod op naaktbeelden zonder toestemming | 2 december 2026 |
| Hoog risico, bijlage III | 2 december 2027 |
| Hoog risico, bijlage I | 2 augustus 2028 |
| Hoog risico bij de overheid, al in gebruik | uiterlijk 2 augustus 2030 |

## Generatieve AI

Deel 3 verschijnt als `ai_generatief` ja is. Het heeft twee secties:

- **Het standpunt**: zes voorwaarden, elk gehaald, niet gehaald of niet nodig.
  Een account onder voorwaarden voor consumenten mag niet, behalve voor een
  verkenning in een testomgeving zonder gevoelige gegevens. De vijf vragen die
  het standpunt voor de risicoanalyse noemt, staan in `GAI_AFWEGINGEN`.
- **De handreiking**: vijf vragen over kennis, menselijke controle, besluiten,
  contact met burgers en het model. Een ontbrekend antwoord wordt een tip;
  `GAI_TIPS` gelden altijd.

Bij generatieve AI vinkt `regels.js` in de aankruislijst alvast aan dat het
systeem tekst, beeld of geluid maakt.

## Overnemen

De omschrijving `ai_omschrijving` heet in quickscan 2.0 en de DPIA hetzelfde,
dus die gaat vanzelf mee. De aankruislijst `ai_toepassing` deelt deze scan alleen
nog met quickscan 1.0. De IAMA
neemt de omschrijving ook over als doel. Quickscan 2.0 en de DPIA zetten bij een
bestand van deze scan de vraag of er AI is op ja.

## Opbouw

```
definitie.php     teksten, het overnemen, en de functies voor de motor
vragen.php        de vragenlijst, de rollen en de aankruislijst
afleiding.php     de route, de plichten, de toets aan het standpunt
rapport.php       het rapport als blokken
vooraf.php        "Voor je begint"
samenvatting.php  de scan in het kort
regels.js         de route en de uitkomst tijdens het invullen
```

## Beperkingen

- De scan vervangt het oordeel van een jurist niet.
- De beslishulp verandert nog. Loop bij een nieuwe versie van
  `decision-tree.yaml` de route en de afwijkingen hierboven na.
