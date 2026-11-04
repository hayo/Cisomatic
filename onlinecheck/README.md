# Online communicatie check

Een formulier van de [Cisomatic](../README.md) voor de adviseur sociale media.
Het toetst snel of één kanaal binnen het rijksbrede kader past: comply, of
explain. Het is een eenvoudige versie van de [kanalenscan](../socialemedia/README.md),
voor als je veel kanalen in kaart moet brengen. De kanalenscan blijft er
naast staan, voor een grondige toets, en voor starten en stoppen op een
platform.

De check volgt de platformkaders bij de notitie "Aanwezigheid Rijksoverheid op
sociale media", versie 1.0 van 30 oktober 2026.

## De regel

Het is comply als alles klopt:

1. Het platform is in beginsel inzetbaar: Instagram, YouTube, LinkedIn of
   social.overheid.nl.
2. Elk doel van het kanaal valt binnen het kader van dat platform.
3. Alles staat ook op een eigen kanaal van de overheid (`sm_eigen_kanalen`).
   Op social.overheid.nl vraagt de check dat niet.
4. Je wijst mensen op de risico's van contact, en verwijst door
   (`sm_doorverwijzen`).

Klopt er één niet, dan is het explain. Facebook, X, TikTok en een platform
zonder kader zijn altijd explain; dat zegt de notitie. Bij een explain vraagt
de check wat de notitie voorschrijft: waarvan je afwijkt (de check vult dat
voor), waarom het kanaal nodig is, waarom een minder risicovol kanaal niet
volstaat, en welke maatregelen je neemt.

De check kent geen "niet inzetten". Dat is een keuze: de adviseur brengt in
kaart, de directeur Communicatie beslist.

## De doelen

De check vraagt alleen naar de communicatiedoelen uit de notitie. De
communicatievormen niet: die zijn nergens omschreven, en ze overlappen met de
doelen. Adverteren en contact vraagt de check ook niet apart.

De vijftien doelen uit de notitie zijn samengevoegd tot elf (`OC_GROEPEN` in
`afleiding.php`). Alleen doelen met dezelfde ruimte bij elk platform staan
samen:

- informeren en duiden
- voorlichten (publieksvoorlichting) en gedrag beïnvloeden
- activeren en werven (arbeidsmarktcommunicatie)
- dienstverlening en veilig contact

Zo verandert geen uitkomst. Eén punt verschilt: op TikTok kan
publieksvoorlichting zonder advertenties, gedragsbeïnvloeding niet. Samen
gelden ze als "alleen met advertenties". TikTok is altijd explain, dus dat
verandert alleen het punt onder "Let op".

Verder samenvoegen kan niet zonder verlies. Bij samengevoegde doelen telt de
krapste ruimte. Komt attenderen bij informeren, dan valt attenderen op X buiten
het kader. Komt publieksvoorlichting erbij, dan valt informeren op LinkedIn en
social.overheid.nl erbuiten.

Een kanaal heeft vaak meer doelen. Ze moeten allemaal passen. Valt er één
buiten het kader, dan is het een explain.

## De kaders

De check leest de kaders uit `../socialemedia/kaders.php`, dezelfde als de
kanalenscan. Bij de jaarlijkse herziening loop je dus één bestand na.

`oc_ruimte()` in `afleiding.php` zegt per platform en doel van de check:

- **ja**: het doel staat in `doelen` of `contactdoelen` van het kader
- **betaald**: het doel staat alleen in `betaald`. Op TikTok geldt dat voor
  gedragsbeïnvloeding: de notitie noemt "gedragsbeïnvloeding middels betaalde
  inzet". Het telt als binnen het kader, met een punt onder "Let op".

Bij een samengevoegd doel telt de krapste ruimte van de doelen erin.
- **nee**: anders. Wat de notitie bij een platform niet noemt, valt erbuiten.

`contactdoelen` zijn interactie, dienstverlening en veilig contact. De
kanalenscan vraagt die bij contact; de check als gewone doelen.

Waar de notitie niet eenduidig is, kiest de check net als de kanalenscan:

- Instagram noemt stakeholdermanagement bij "wel" en bij "niet". Het valt
  buiten het kader.
- TikTok noemt attenderen bij "wel" en bij "niet". Het valt binnen het kader.
- Communitymanagement staat alleen bij Facebook en Instagram. Bij de andere
  platforms valt het erbuiten.

## Sleutels

De sleutels van het platform, de voorwaarden en het vaststellen zijn die van de
kanalenscan: `sm_platform`, `sm_anders_naam`, `sm_eigen_kanalen`,
`sm_doorverwijzen` en `sm_directeur`. Een bestand van de kanalenscan vult ze
in. De doelen en de explain hebben eigen sleutels (`oc_`), omdat de kanalenscan
ze anders indeelt.

## De check op één A4

Boven het formulier staat een link naar de check als `.odt`, op één A4. Die is
voor wie op papier of in Word wil werken. De motor maakt hem niet uit de
secties: met `'vragenlijst_blad' => true` in de definitie neemt hij het blad
uit `oc_vragenlijst()` over, zonder titelpagina en inhoudsopgave.

Op het blad staat de matrix: per doel een rij, per platform een kolom, met ✓,
✓* of –. De matrix komt uit `oc_ruimte()`, dus hij loopt niet achter op de
kaders. De regel staat er in één zin bij. Verandert `oc_evalueer()`, loop dan
ook `vragenlijst.php` na.

Komt er een platform of doel bij, kijk dan of het blad nog op één pagina
past.

## regels.js

`regels.js` rekent tijdens het invullen hetzelfde uit als `oc_evalueer()`: het
kader, de toets van de doelen, de uitkomst en de voorgevulde afwijkingen. De
gegevens krijgt het van PHP, via `oc_regelgegevens()` als `data-groepen` op de
vraag `sm_platform`. PHP blijft de bron van waarheid.

## Opbouw

```
definitie.php     teksten en de functies voor de motor
afleiding.php     de doelen, de toets en de uitkomst
vragen.php        het kanaal, de doelen, de voorwaarden en de uitkomst
document.php      het document met de uitkomst, en het kader als bijlage
vooraf.php        "Voor je begint"
samenvatting.php  de uitkomst in het kort
vragenlijst.php   de check op één A4
regels.js         dezelfde afleiding in de browser
```

## Beperkingen

- De check vervangt het advies van de CPO, de CISO en de CIO niet. De directeur
  Communicatie stelt de uitkomst vast.
- De check weegt de risico's niet, en vraagt niet of je de doelgroep bereikt.
  Bij een explain leg je dat zelf uit. Voor een volledige weging is er de
  kanalenscan.
- Voor een platform zonder kader is het altijd een explain.
