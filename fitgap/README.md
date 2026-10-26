# Fit/gap 1.0: BIO

Een formulier van de [cisomatic](../README.md) voor de leverancier van een
dienst. De leverancier, de opdrachtnemer, verklaart per norm of de dienst
voldoet. Zo ja: hoe het geregeld is, en in welk document. Zo nee: wat er nog
moet gebeuren, en wanneer het klaar is. Het document is de officiële verklaring
aan de opdrachtgever. De dienstverlening kan pas starten als de dienst aan alle
normen voldoet.

Het formulier volgt de fit/gap-analyse BIO, versie 1.05, een spreadsheet. Er is
ook een fit/gap voor de BIO2 en de Cyberbeveiligingswet, in
[fitgap2](../fitgap2/README.md). Beide delen de code in deze map.

Hoe het formulier draait, bewaart en het document maakt, staat in de README van
de motor. Hier staat wat de fit/gap doet.

## Opbouw

```
verklaring.php    wat beide fit/gap formulieren delen: vragen per norm, rekenen, het document
cloudprivacy.php  de normen uit ISO 27017, ISO 27018 en ISO/IEC 27701, ook gedeeld
bio.php           de normen uit de BIO 1, het beveiligingsniveau, en de functies voor de motor
regels.js         de stand per norm, per deel en de uitkomst in de browser, ook gedeeld
samenvatting.php  de uitkomst in het kort, ook gedeeld
```

De gedeelde functies heten `fg_`, die van dit formulier `fg1_`. `fitgap2` laadt
`verklaring.php` en `cloudprivacy.php` uit deze map, en noemt `regels.js` met
de sleutel `regels` in zijn definitie.

## Een norm

Elke norm is een regel die openklapt, met de `groep` van de motor. In de kop
staan het nummer, de titel en de tekst van de norm. Per norm:

| Sleutel | Vraag |
| --- | --- |
| `fg_<deel>_<nummer>` | Voldoet de dienst? Ja, nee, of bij sommige normen niet van toepassing |
| `_hoe` | Hoe het geregeld is, met het document en de vindplaats. Verplicht bij ja |
| `_maatregel` | Wat er nog moet gebeuren. Verplicht bij nee |
| `_datum` | Wanneer de dienst wel voldoet. Verplicht bij nee |
| `_nvt` | Waarom de norm niet geldt. Alleen bij normen die dat mogen |

De spreadsheet vraagt de beschrijving en de vindplaats in één cel. Het
formulier doet dat ook, in `_hoe`. Zo blijft het aantal velden onder de 1000
die PHP standaard per verzoek leest (`max_input_vars`). De fit/gap voor de BIO2
heeft er ongeveer 850.

Een regel staat open zolang er iets ontbreekt. Onder elk deel staat hoe ver het
is, en bij de verklaring de uitkomst over alles. Die regels rekenen
`regels.js` en `verklaring.php` allebei; PHP rekent bij het versturen opnieuw.

## Welke normen gelden

Alle normen gelden, behalve:

- **Het beveiligingsniveau.** De opdrachtgever stelt BBN 1, 2 of 3 vast. Bij BBN
  1 en BBN 2 kan voor beschikbaarheid of integriteit een ander niveau gelden.
  Dat zijn de negen kolommen uit de spreadsheet. Per norm staat of hij al bij
  BBN 1 hoort, en op welke aspecten hij slaat (B, I en V). `fg1_niveaus()` volgt
  daarmee de formules uit de kolommen:
  - Een norm van BBN 1 geldt altijd.
  - Een norm die pas bij BBN 2 hoort, geldt bij BBN 1 als een aspect waarop hij
    slaat naar BBN 2 gaat.
  - Bij BBN 2 valt hij weg als alle aspecten waarop hij slaat naar BBN 1 gaan.
  - Bij BBN 3 gelden alle normen.
- **Persoonsgegevens.** Als verwerkingsverantwoordelijke gelden de normen uit
  bijlage A van ISO/IEC 27701, als verwerker die uit bijlage B.
- **De cloud.** Bij vertrouwelijke gegevens in de cloud gelden de normen uit ISO
  27017, bij persoonsgegevens ook die uit ISO 27018. De spreadsheet vraagt
  beide los. Het formulier telt persoonsgegevens als vertrouwelijk, dus dan
  gelden beide.
- **Een standaardproduct.** Bij een product dat de leverancier niet op maat
  maakt (COTS), mogen 14.2.5 tot en met 14.2.9 niet van toepassing zijn.

Het niveau voor het cloudbeleid (Laag tot en met Zeer hoog) komt alleen in het
document. Het verandert niets aan de normen.

## Certificaten

Met een volledig certificaat valt een deel weg:

| Certificaat | Wat wegvalt |
| --- | --- |
| ISO 27001 | De normen uit de BIO. Ze komen uit ISO 27002 |
| ISO/IEC 27701, met bijlage A en B | De normen uit bijlage A en B |
| ISO 27017 | De normen uit ISO 27017 |
| ISO 27017 én ISO 27018 | De normen uit ISO 27018 |

Per certificaat vraagt het formulier wie het afgaf, en tot wanneer het geldig
is. Het document vraagt om het certificaat, de auditscope en de verklaring van
toepasselijkheid als bijlage.

## Overnemen

De sleutels van de dienst, de certificaten en de normen uit ISO 27017, ISO
27018 en ISO/IEC 27701 zijn in beide fit/gap formulieren gelijk. Een bestand
van de een vult die in de ander in. De normen uit de BIO 1 en de BIO2 hebben
eigen sleutels, want hun indeling verschilt.

Uit een quickscan of de business case komt alleen de naam van het project mee.
`aanvrager` en `email` blijven leeg, want de verklaring is van de leverancier.
De opdrachtgever staat al ingevuld, met de `naam` van `organisatie` in
`config.php`.

## Het document

Na de titelpagina de uitkomst, met een tabel per deel. Dan wat nog moet
gebeuren, met de maatregel en de datum. Dan wat de verklaring dekt: de
systemen en processen, het niveau, de rol, de cloud en de certificaten. Daarna
alle normen per deel, de bijlagen, de ondertekening, en ruimte voor de
beoordeling door de opdrachtgever.

## Teksten

De teksten van de normen komen uit de spreadsheet, met kleine correcties in
spelling. De titels bij ISO 27017 zijn een eigen samenvatting; de spreadsheet
noemt alleen het nummer. De normen uit ISO/IEC 27701 volgen de versie van 2019,
net als de spreadsheet.
