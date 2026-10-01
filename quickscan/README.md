# IB & Privacy Quickscan

Een formulier van de [Compliancomatic](../README.md) dat de IB & Privacy
Quickscan afneemt en er meteen een volledig advies uit rolt: welk
beveiligingsniveau geldt, welke eisen aan leverancier en organisatie, en welke
aanvullende analyses nodig zijn.

Hoe het formulier draait, bewaart en het document maakt, staat in de README van
de motor. Hier staat wat alleen de quickscan doet.

## White label

Er staat geen organisatie in. De twee rollen heten behoeftesteller en
beoordelaar; wie de beoordelaar is, verschilt per organisatie. Waar het
brondocument een eigen team of formulier noemde, staat nu "de organisatie".

Wat wél blijft, zijn de kaders waar de beslisregels op rusten: VIR, BBN, ABRO,
de CIO Rijk, de Archiefwet 2026 en de AI-verordening. Zonder die kaders is er
geen beslisboom.

## Twee stappen, twee rollen

De quickscan wordt in twee stappen gemaakt: de behoeftesteller beschrijft wat er
gaat gebeuren, de beoordelaar weegt dat en stelt de eisen vast. De tool vraagt
vooraf in welke rol je invult en zet bij elke sectie welk stempel erbij hoort.

Dat bepaalt ook wat je mag. Een behoeftesteller kan een afgeleide eis bijstellen
tot aan de **ondergrens**. Dat is de eis die letterlijk uit de quickscan volgt,
bijvoorbeeld via het AVG-beginsel "Juistheid" of het vertrouwelijkheidsniveau
van de informatie. De beoordelaar mag in stap 2 ook onder die ondergrens, met
een motivatie. De server controleert dat in `qs_valideer_bijstelling()`. Een
advies uit stap 1 draagt in beeld en in de tekst dat de wegingen voorlopig zijn.

## Onderscheid tussen regel en voorstel

Niet alles wat de tool afleidt staat in het brondocument.

- **Ondergrens**: komt uit de quickscan zelf. Eén categorie persoonsgegevens
  betekent integriteit en vertrouwelijkheid minimaal Midden; meerdere
  categorieën of één bijzondere categorie betekent minimaal Hoog. Dat ligt vast.
- **Voorstel**: bedacht door de tool als redelijke startwaarde, bijvoorbeeld de
  koppeling van procesbelang aan beschikbaarheid en integriteit. Daar mag je
  onder gaan zitten.

Een eerdere versie behandelde het voorstel als ondergrens. Een kritisch
strategisch proces zette de integriteitseis dan vast op Hoog, ook als het om
trendcijfers ging waar fouten niet toe doen. Het formulier laat nu bij elke
afgeleide eis zien wat het vaste minimum is en wat alleen een voorstel is.

## Wat het anders doet dan het Word-document

**Niet meer vragen wat al vastligt.**

| Stond in het document | Nu |
| --- | --- |
| Eén tekstvak voor "welke informatie wordt verwerkt" | Een aankruislijst met gangbare soorten, gegroepeerd; daaruit volgt of er persoonsgegevens zijn, hoeveel categorieën en of er bijzondere bij zitten |
| Vertrouwelijkheid, twee keer: als informatieniveau én als BIV-eis | Eén vraag ("hoe gevoelig is de informatie?"); de eis volgt eruit |
| Integriteit als losse keuze | Afgeleid uit de persoonsgegevens en het procesbelang |
| "Is er sprake van materieel cloudgebruik?" | Afgeleid: (kritisch) strategisch proces + vitaal of belangrijk systeem + beschikbaarheid (zeer) hoog |
| "Staat dit proces in het Overzicht vitale processen?" (ABRO) | Afgeleid uit de procesclassificatie |
| Recovery Time Objective als aparte invulling | Afgeleid uit de beschikbaarheidseis |
| Zeven lege argumentatievelden | Bij elke weging een motivatieveld dat de tool zelf alvast invult met zijn redenering |

**Niet vragen wat de invuller niet kan weten.**

| Was | Nu |
| --- | --- |
| "Zijn inlichtingendiensten, terreurgroepen of georganiseerde criminaliteit een dreiging?" | Een checklist van wat dit systeem een aantrekkelijk doelwit maakt; het dreigingsprofiel volgt eruit |
| "Is dit een grootschalige verwerking?" | De vier AVG-factoren: aantal betrokkenen, hoeveelheid per persoon, duur en geografisch bereik |
| "Hoe schat je het AI-risico in: beperkt, hoog of onaanvaardbaar?" | De toepassingsgebieden uit de AI-verordening; de risicoklasse volgt eruit |

**Een snelle route.** Valt er niets te beschermen, dan blijven er zes vragen
over in plaats van het hele formulier. Zolang die eerste vraag open staat, staat
er "Stap 1" zonder totaal: de `route` in `definitie.php`.

**"Weet ik niet" mag.** De tool neemt dan een veilige aanname, rekent daarmee
door, en zet bovenaan het advies een lijst van alles wat is aangenomen. De
aanname staat bij de vraag, onder `aanname`.

**Meer privacy.** Er zijn vragen over doel, grondslag, subverwerkers en de
rechten van betrokkenen. Kiest een overheidsorgaan "gerechtvaardigd belang" als
grondslag, dan blokkeert de tool.

**Gevolgen verderop.** Verandert een eis door een antwoord, dan meldt een korte
melding onderin beeld dat, want de eis zelf staat vaak een paar secties verder.

## Archiefwet 2026

Het advies bevat een hoofdstuk over het inrichten van de archiefprocessen:
zorgdragerschap bij een externe leverancier, de categorie in de selectielijst,
metagegevens (MDTO), duurzame bestandsformaten, vernietigingsverklaringen,
overbrenging en de exit bij een SaaS-contract.

De overbrengingstermijn gaat onder deze wet van twintig naar tien jaar. Voor een
systeem dat je nu inkoopt valt de eerste overbrenging dus binnen de looptijd van
het contract. Zonder bewaartermijn of zonder export blokkeert de tool.

## Het advies

De adviespagina begint met de uitkomst in het kort, de benodigde analyses, de
wegingen met hun herkomst en het gevolgde pad door de beslisboom. Daaronder
staat het volledige advies met een inhoudsopgave.

`advies.php` bouwt het advies als blokken voor de motor. De uitkomst in het
kort, de analyses, het pad door de beslisboom en de ingevulde antwoorden staan
in tabellen.

## Opbouw

```
definitie.php     teksten en de functies voor de motor
vragen.php        de vragenlijst
toelichting.php   de volledige omschrijvingen uit het brondocument
afleiding.php     wat we uitrekenen in plaats van vragen; de controle op bijstellingen
beslisboom.php    de beslisregels
advies.php        het advies als blokken
vooraf.php        "Voor je begint"
samenvatting.php  de uitkomst in het kort, de analyses, de wegingen en het pad
regels.js         de afleidingen in de browser, de ondergrens en de meldingen
```

`regels.js` herhaalt de afleidingsregels uit `afleiding.php`, zodat het
formulier tijdens het invullen al laat zien wat eruit rolt. PHP blijft de bron
van waarheid. Pas je de een aan, pas dan ook de ander aan.

## Beperkingen

- Het advies vervangt het oordeel van de beoordelaar niet.
- De koppeling tussen procesbelang en de startwaarden voor beschikbaarheid en
  integriteit is een aanname, geen regel uit het brondocument.
- De checklist voor het dreigingsprofiel is een uitwerking van "hoog
  dreigingsprofiel". Elk aangevinkt kenmerk leidt tot een hoog profiel.
- De drempels voor grootschaligheid (1.000 en 100.000 betrokkenen) zijn een
  keuze, geen wettelijk getal. Ze staan in `qs_afgeleide_grootschaligheid()`.
- De AI-verordening is verwerkt via de toepassingsgebieden, niet via een
  volledige conformiteitstoets.
- De bijlagen waar het brondocument naar verwijst zitten er niet in.
