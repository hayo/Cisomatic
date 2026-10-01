# Kanalenscan sociale media

Een formulier van de [Cisomatic](../README.md) voor ministeries, hun diensten en
agentschappen. Het volgt één kanaal op sociale media door de hele cyclus:
starten, de jaarlijkse toets, en vertrekken. Het levert een comply or explain,
of een exitplan, voor de directeur Communicatie.

Twee bronnen liggen eronder: de rijksbrede platformkaders (met de werkwijze in
vier stappen), en de platformonafhankelijke richtlijnen voor toetreding en
vertrek.

## Eén scan per kanaal

Een Facebookpagina is één scan, een account op Instagram een tweede. De sleutels
beginnen met `sm_`.

De eerste vraag is de situatie (`sm_situatie`), en die is ook de route:

| Situatie | Secties | Resultaat |
| --- | --- | --- |
| Nieuw kanaal | de vier stappen, en het vooronderzoek bij toetreding | comply or explain |
| Bestaand kanaal | de vier stappen | comply or explain |
| Vertrek | waarom, impact, het vertrek | exitplan |

Daarna het platform (`sm_platform`), en daaruit volgt het kader. Alle situaties
eindigen met Vaststellen: wie adviseerde, en wie het vaststelt.

## Toetreding

De richtlijn voor toetreding geldt voor een platform als geheel, niet voor een
extra account. Daarom vraagt de scan bij een nieuw kanaal of de organisatie al
op het platform zit (`sm_al_actief`). Zo niet, dan verschijnt de sectie
Vooronderzoek: het belang van de burger en de kosten uit het normenkader, pilot
of structureel, en de exitstrategie. Bij een platform zonder kader vraagt het
ook naar de opzet (open of centraal) en of archiveren kan. Het document krijgt
de checklist voor livegang.

## Vertrek

`exit.php` bevat de vragen, het advies (`sm_exit_advies()`) en het exitplan
(`sm_exit_document()`), in de acht stappen van de richtlijn voor vertrek. De
gradaties voor opvang en impact komen uit de richtlijn.

Het advies weegt de gekozen aanpak. De richtlijn raadt een soft exit en
deactiveren aan. Verwijderen wordt sterk afgeraden, en vraagt een reden. Vangen
andere kanalen het vertrek slecht op, of is de impact groot, dan raadt de tool
een soft exit of de hybride vorm aan, behalve bij een opgelegd vertrek. Bij
vertrek van een heel platform komt de afstemming met de Voorlichtingsraad in
het plan; bij één account niet.

Is de uitkomst van een bestaand kanaal "niet inzetten", dan verwijst het
document naar de exit.

## Account van een bewindspersoon

`sm_accounttype` vraagt of het account van de organisatie is of van een
bewindspersoon. Per kader staat in `bewindspersoon` of dat mag: `ja`, `nee` (een
explain) of `verboden` (niet inzetten, bij TikTok). Betaalde promotie op zo'n
account is ook een afwijking. De eisen uit de richtlijn voor toetreding komen
in het document.

## Drie soorten gebruik

Het kader geeft per soort gebruik een eigen ruimte, en de scan vraagt ze los
(`sm_gebruik`):

| Soort | Vragen | Ruimte in het kader |
| --- | --- | --- |
| Organisch: berichten plaatsen | doelen (`sm_doelen`) en vormen (`sm_vormen`) | `doelen`, `vormen` |
| Betaald: adverteren | waarvoor (`sm_betaald`), en hoe je bepaalt wie het ziet (`sm_doelgericht`) | `betaald` |
| Contact: vragen beantwoorden | reacties, algemene vragen, webcare (`sm_contactvormen`) | `contact` |

`SM_TOETSEN` in `afleiding.php` legt vast welke vraag aan welke ruimte wordt
getoetst. PHP en `regels.js` lezen allebei die tabel. Een soort die niet is
aangekruist, telt niet mee, ook niet als er nog oude antwoorden staan.

Waar de ruimte vandaan komt:

- **Betaald** volgt de campagnes die de notitie "(betaald)" noemt, en de doelen
  gedragsbeïnvloeding en bereik. X en social.overheid.nl hebben geen ruimte om
  te adverteren.
- **Contact** volgt interactie, vraagbeantwoording, dienstverlening en veilig
  contact. Reageren en algemene vragen kan op Instagram, LinkedIn en
  social.overheid.nl. Webcare met persoonlijke vragen kan alleen op
  social.overheid.nl. Facebook, X, TikTok en YouTube hebben geen ruimte voor
  contact.
- **Doorverwijzen** (`sm_doorverwijzen`) geldt altijd, ook zonder contact. Elk
  kader vraagt dat je mensen op de risico's wijst en doorverwijst. Op
  social.overheid.nl gaat het om vragen met privacygevoelige gegevens.

Bij adverteren komen twee aandachtspunten in het document: het verbod uit de
DSA op advertenties op basis van gevoelige gegevens en op profielen van
minderjarigen, en een advies om breed te richten als je op interesses richt.
Ze tellen niet mee voor de uitkomst.

## De vier stappen

1. **Doel en doelgroep.** Wat je per soort gebruik kiest, wordt getoetst aan
   de ruimte van het kader. Bereik je de doelgroep niet, dan dient het kanaal
   het doel niet.
2. **Kanaalstrategie.** Is er een minder risicovol kanaal dat hetzelfde doel
   bereikt? En staat alles eerst op eigen openbare kanalen?
3. **Weging.** De risico's uit het kader, of je doorverwijst, het toestel (bij
   TikTok), de maatregelen, en of de meerwaarde opweegt.
4. **Comply or explain.** De tool rekent de uitkomst uit.

## De uitkomst

`sm_evalueer()` in `afleiding.php` geeft een van drie uitkomsten:

- **Niet inzetten**: de doelgroep is niet te bereiken, een minder risicovol
  kanaal volstaat, de meerwaarde weegt niet op, de app staat op een toestel van
  het Rijk, of een bewindspersoon wil op een platform waar dat nooit mag.
- **Explain**: het kader vraagt altijd een explain (Facebook, X, TikTok, en elk
  platform zonder kader), of de inzet gaat verder dan de ruimte. Dat is
  organisch, betaald of contact buiten het kader, niet alles eerst op eigen
  kanalen, niet doorverwijzen, beheer vanaf een privétoestel, of een
  bewindspersoon waar het kader dat niet noemt of met betaalde promotie.
- **Comply**: anders.

Bij een explain vraagt het formulier wat de notitie voorschrijft: waarvan je
afwijkt (de tool vult dat voor), waarom het noodzakelijk is, en welke
maatregelen je neemt. Waarom een alternatief niet volstaat, staat al in stap 2.

Ontbrekende maatregelen tellen niet mee voor de uitkomst. Ze komen in het
document onder "Nog te regelen".

## De kaders

`kaders.php` houdt per platform bij wat het kader zegt: het advies, de
doelgroep, de risico's, of een bewindspersoon er mag zijn, en de ruimte per
soort gebruik. Wat daar niet in staat, valt erbuiten. De kaders worden elk jaar
herzien; loop dit bestand dan na.

Een platform zonder kader krijgt de vraag waar het bedrijf zit. Een land met
een offensief cyberprogramma tegen Nederland geeft de toestelregel van TikTok;
buiten de EU komt als risico in het kader.

Drie punten in de brontekst zijn niet eenduidig. De tool kiest zo:

- Instagram noemt stakeholdermanagement zowel bij "wel" als bij "niet". De tool
  houdt het buiten de ruimte.
- TikTok noemt attenderen zowel bij "wel" als bij "niet". De tool houdt het
  binnen de ruimte, omdat het kader TikTok geschikt noemt om jongeren te
  attenderen.
- YouTube noemt corporate communicatie en crisiscommunicatie bij de doelen. De
  tool behandelt ze als vormen.

## regels.js

`regels.js` rekent tijdens het invullen hetzelfde uit als `sm_evalueer()` en
`sm_exit_advies()`: het kader, de toets per soort gebruik, de uitkomst, de
voorgevulde afwijkingen, en het advies over het vertrek. De kaders en alle
zinnen krijgt het van PHP, via `sm_regelgegevens()` als `data-groepen` op de
vraag `sm_platform`. Alleen de rekenregel staat dus dubbel. PHP blijft de bron
van waarheid.

## Opbouw

```
definitie.php     teksten en de functies voor de motor
kaders.php        de platformkaders, en de gegevens voor regels.js
vragen.php        het kanaal, de vier stappen, de toetreding en het vaststellen
exit.php          het vertrek: vragen, advies en exitplan
afleiding.php     de toets, de afwijkingen en de uitkomst
advies.php        het comply or explain document als blokken
vooraf.php        "Voor je begint"
samenvatting.php  de uitkomst in het kort
regels.js         dezelfde afleiding in de browser
```

## Beperkingen

- De tool vervangt het advies van de CPO, de CISO en de CIO niet. De directeur
  Communicatie stelt de uitkomst vast.
- Voor een platform zonder kader, zoals WhatsApp of Bluesky, kent de tool de
  risico's niet. Dan is het altijd een explain.
- Het exitplan noemt de Voorlichtingsraad en zijn secretariaat, maar geen
  adres. Vul dat zelf in.
