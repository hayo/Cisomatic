# Online communicatie check

Een formulier van de [Cisomatic](../README.md) voor de adviseur sociale media.
Het toetst snel of één kanaal voldoet aan wat het rijksbrede kader eist. Zo
niet, dan leg je uit waarom jullie het toch gebruiken. In de notitie heet dat
comply or explain; de check zegt het in het Nederlands. Het document en het A4
heten "Pas toe of leg uit - social media" (`OC_TITEL`).

De check gaat over compliance, niet over advies. Doelen en doelgroep vraagt hij
niet: of een platform geschikt is voor een doel, is een keuze van de
organisatie. Daarvoor, en voor starten en stoppen op een platform, is de
[kanalenscan](../socialemedia/README.md).

De check volgt de platformkaders bij de notitie "Aanwezigheid Rijksoverheid op
sociale media", versie 1.0 van 30 oktober 2026.

## Wat het kader eist

De "niet"-lijsten in de notitie mengen twee dingen: wat niet mag, en wat minder
geschikt is. Het meeste gaat over bereik en doelgroep, zoals activeren en
werven op social.overheid.nl ("door het beperkte bereik (nog) niet geschikt").
De check houdt alleen over wat juridisch of om veiligheid niet mag, of alleen
onder voorwaarden:

social.overheid.nl is het basiskanaal van de overheid. Daar mag alles, dus de
check stelt daar geen voorwaarden. Voor de andere platforms:

| Eis | Grond in de notitie |
| --- | --- |
| Facebook, X en TikTok vragen altijd uitleg | DPIA en HRIA (Facebook), de DSA (Meta, X, TikTok), nationale veiligheid (TikTok) |
| Een platform zonder kader vraagt altijd uitleg | Geen kader, dus de risico's zijn niet bekend |
| Geen dienstverlening of veilig contact | "Ongeschikt voor veilig contact met de overheid", "niet geschikt voor dienstverlening": persoonsgegevens |
| Alles staat ook op een eigen kanaal van de overheid | "Inzet is altijd aanvullend op eigen overheidskanalen" |
| Je wijst mensen op de risico's, en verwijst door | "Gebruikers actief attenderen op de risico's en doorverwijzen" |
| TikTok nooit op een toestel van het Rijk | Verboden. Staat onder "Let op", want uitleg maakt het niet goed |
| Gedragsbeïnvloeding op TikTok alleen met advertenties | "Gedragsbeïnvloeding middels betaalde inzet". Staat onder "Let op" |

De uitkomst is "Voldoet" of "Voldoet niet". Voldoet het kanaal niet, dan vraagt
de check wat de notitie voorschrijft (`OC_UITLEG`): waarop je afwijkt (de check
vult dat voor), waarom het kanaal nodig is, waarom een minder risicovol kanaal
niet voldoet, en welke maatregelen je neemt.

Onderaan het document en het A4 staat dat de directeur Communicatie het
formulier vaststelt, en dat het daarna naar de bestuursraad gaat
(`OC_BESTUURSRAAD`).

De check kent geen "niet inzetten". De directeur Communicatie beslist. Intern
heten de uitkomsten `comply` en `explain`.

## De kaders

De check leest de kaders uit `../socialemedia/kaders.php`, dezelfde als de
kanalenscan. Hij gebruikt:

- `explain`: altijd uitleg
- `contact`: waar `persoonlijk` erin staat, is het basiskanaal (`oc_basis()`).
  Dat is alleen social.overheid.nl. Daar gelden de voorwaarden niet.
- `toestel`: de app mag niet op een toestel van het Rijk
- `betaald` en `doelen`: staat `gedrag` alleen bij `betaald`, dan mag
  gedragsbeïnvloeding alleen met advertenties
- `risicos`: als bijlage in het document

Waarom een platform altijd uitleg vraagt, staat kort in `OC_GRONDEN` in
`afleiding.php`, en nog korter voor het A4 in `OC_GRONDEN_KORT` in
`vragenlijst.php`. Loop die na bij de jaarlijkse herziening.

## Sleutels

De sleutels van het platform, de voorwaarden en het vaststellen zijn die van de
kanalenscan: `sm_platform`, `sm_anders_naam`, `sm_eigen_kanalen`,
`sm_doorverwijzen` en `sm_directeur`. Een bestand van de kanalenscan vult ze
in. Dienstverlening en veilig contact (`oc_contact`) en de uitleg hebben eigen
sleutels.

## De check op één A4

Boven het formulier staat een link naar de check als `.odt`, op één A4. Die is
voor wie op papier of in Word wil werken. De motor maakt hem niet uit de
secties: met `'vragenlijst_blad' => true` in de definitie neemt hij het blad
uit `oc_vragenlijst()` over, zonder titelpagina en inhoudsopgave.

Op het blad staat eerst het kanaal: platform en accountnaam als tekst. Dan een
tabel met per platform of uitleg altijd nodig is, of dienstverlening en veilig
contact mogen, en waarom; het basiskanaal staat bovenaan. Daaronder de
voorwaarden als korte zinnen waar ja goed is, de uitkomst met de regel in één
zin, en de vier vragen van de uitleg onder elkaar. Verandert `oc_evalueer()`, loop dan ook `vragenlijst.php` na. Komt er
een platform bij, kijk dan of het blad nog op één pagina past.

## regels.js

`regels.js` rekent tijdens het invullen hetzelfde uit als `oc_evalueer()`: wat
het kader eist, de uitkomst en de voorgevulde afwijkingen. De gegevens krijgt
het van PHP, via `oc_regelgegevens()` als `data-groepen` op de vraag
`sm_platform`. PHP blijft de bron van waarheid.

## Opbouw

```
definitie.php     teksten en de functies voor de motor
afleiding.php     de eisen, de gronden en de uitkomst
vragen.php        het kanaal, de voorwaarden en de uitkomst
document.php      het document met de uitkomst, en de risico's als bijlage
vooraf.php        "Voor je begint"
samenvatting.php  de uitkomst in het kort
vragenlijst.php   de check op één A4
regels.js         dezelfde afleiding in de browser
```

## Beperkingen

- De check vervangt het advies van de CPO, de CISO en de CIO niet. De directeur
  Communicatie stelt de uitkomst vast.
- De check weegt de risico's niet. Bij uitleg doe je dat zelf, met de risico's
  uit de bijlage.
- Voor een platform zonder kader is altijd uitleg nodig.
