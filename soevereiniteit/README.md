# Soevereiniteitsscan

Een formulier van de [cisomatic](../README.md) dat laat zien hoeveel grip
Europa heeft op een clouddienst. Het volgt het Cloud Sovereignty Framework van
de Europese Commissie (versie 1.2.1, oktober 2025), in een eenvoudige vorm, en
geeft een label van A tot E, zoals een energielabel.

Hoe het formulier draait, bewaart en het document maakt, staat in de README van
de motor. Hier staat wat alleen deze scan doet.

## Van het framework naar de scan

Het framework beoordeelt een dienst op acht doelen, SOV 1 tot en met 8. Per doel
krijgt de dienst een SEAL niveau van 0 tot 4. De scan maakt van elk doel een
thema, met twee tot vier vragen in gewone taal:

| Thema | Doel | Gewicht | Eis |
| --- | --- | --- | --- |
| Zeggenschap | SOV 1, Strategic | 15% | rest |
| Recht | SOV 2, Legal & Jurisdictional | 10% | kern |
| Data | SOV 3, Data & AI | 10% | kern |
| Beheer | SOV 4, Operational | 15% | kern |
| Keten | SOV 5, Supply Chain | 20% | rest |
| Techniek | SOV 6, Technology | 15% | rest |
| Beveiliging | SOV 7, Security & Compliance | 10% | rest |
| Duurzaamheid | SOV 8, Environmental | 5% | geen |

De gewichten komen uit het framework. De rest is een eigen vereenvoudiging.

## Rekenen

De invuller ziet geen niveaus. Elke keuze in `vragen.php` heeft een `niveau` van
0 tot 4: hoe goed dat antwoord een factor uit het framework invult. `null` telt
niet mee (bij AI: "Nee"). "Weet ik niet" staat onder elke vraag en is 0, want
wat niet is aangetoond, telt niet.

- **Thema**: het zwakste antwoord. Het framework zegt dat een zwak punt het
  niveau van het hele doel verlaagt.
- **Score**: per thema de som van de niveaus, gedeeld door het maximum, maal het
  gewicht. Opgeteld is dat 0 tot 100. Dat is de formule uit hoofdstuk 5 van het
  framework.
- **Label**: uit de score. A vanaf 88, B vanaf 63, C vanaf 38, D vanaf 13, E
  daaronder. Zo is het label het gewogen gemiddelde niveau, afgerond.

Niveau 0 is bewaard voor "geen Europese grip": geen Europees bedrijf, geen
Europees recht, geen versleuteling. Een gewone Amerikaanse cloud in een
Europees datacenter komt zo overal op 1 uit, net als in de uitleg op
eucloudpatterns.eu.

`afleiding.php` rekent, `regels.js` rekent hetzelfde tijdens het invullen. De
getallen staan alleen in PHP: `sv_model()` zet ze als `data-groepen` op het
uitkomstblok, en regels.js leest ze daar.

## Wat nodig is

Het framework laat de opdrachtgever per doel een minimum kiezen. De scan leidt
dat af uit de vraag welke informatie in de dienst komt:

| Informatie | Kern | Rest |
| --- | --- | --- |
| Openbaar | D | geen eis |
| Gewoon | C | D |
| Gevoelig of vitaal | B | C |
| Staatsgeheim, defensie | A | B |

De kern is recht, data en beheer: wie de leverancier iets kan opleggen, wie bij
de data kan, en of je zonder de leverancier verder kunt. Duurzaamheid telt mee
in de score, maar houdt niets tegen. Een Amerikaanse cloud haalt zo een C voor
gewone informatie alleen met eigen sleutels, data in de EU en een plan om over
te stappen.

Is een thema te laag, dan geeft de tip van elk te laag antwoord een
verbeterpunt. Antwoorden met "Weet ik niet" komen op een lijst om uit te
zoeken.

## Voorstellen

De eerste stap vraagt wat voor leverancier het is: een Amerikaanse cloud, een
Amerikaanse cloud met een apart Europees bedrijf, een Europees bedrijf met
Amerikaanse techniek, een Europese cloud of een overheidsdatacenter.
`SV_PROFIELEN` zet dan bij elke vraag een voorstel, met "voorgesteld" bij de
keuze. Wie een vraag zelf beantwoordt, houdt dat antwoord. Kies je een ander
soort, dan schuiven de voorstellen mee; bij "Iets anders" vult de scan niets in.

De voorstellen zijn een eerste gok voor een gewone dienst van zo'n leverancier,
geen oordeel over een bedrijf. De voorbeelden bij de keuzes komen van
eucloudpatterns.eu.

## Overnemen

Een bestand van quickscan 2.0 vult de algemene vragen in, en de vragen die
beide stellen: de clouddienst, de sleutels, de plek van de data en het
overstappen. De vraag of een ander land de leverancier kan dwingen, komt uit
`cloud_jurisdictie`. Omgekeerd zet quickscan 2.0 bij een bestand van deze scan
de cloudvragen aan.

## Het rapport

Na de titelpagina een samenvatting met het label, de score, wat nodig is en per
thema of het hoog genoeg is. Dan de acht thema's, elk met de vragen, de
antwoorden en wat elk antwoord waard is. Het laatste hoofdstuk legt de
rekenwijze uit, met de SEAL namen uit het framework.
