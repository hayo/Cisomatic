# Fit/gap 2.0: BIO2 en Cyberbeveiligingswet

Een formulier van de [cisomatic](../README.md) voor de leverancier van een
dienst. Het is [fit/gap 1.0](../fitgap/README.md), bijgewerkt naar
de BIO2 en de Cyberbeveiligingswet. De vragen per norm, de certificaten, de
normen voor cloud en privacy, de uitkomst en het document werken hetzelfde; dat
staat daar beschreven. Hier staat wat anders is.

## Wat anders is dan fit/gap 1.0

| Fit/gap 1.0 | Fit/gap 2.0 |
| --- | --- |
| Beveiligingsniveau BBN 1, 2 of 3, met een eigen niveau voor B en I | Bestaat niet meer. Elke maatregel geldt |
| 112 normen uit ISO 27002:2013, in 14 hoofdstukken | 93 maatregelen uit ISO 27002:2022, in 4 thema's. Het formulier deelt ze op in 12 stappen |
| ISO 27001 dekt alle normen uit de BIO af | ISO 27001 dekt de maatregelen uit ISO 27002 af, maar niet de overheidsmaatregelen. Die vul je altijd in |
| COTS: 14.2.5 tot en met 14.2.9 mogen niet van toepassing zijn | COTS: 8.27 tot en met 8.30 |
| Geen eisen aan de keten | De eisen uit de BIO2 aan de keten, en bij de Cyberbeveiligingswet vier eisen voor het melden en het toezicht |
| Niveau cloudbeleid alleen in het document | De eisen uit het cloudbeleid van 2026, bij elke cloud |

## Wat er bij komt

- **Overheidsmaatregelen.** De maatregelen uit de BIO2 die de leverancier
  raken, met hun nummer uit de BIO2: internetstandaarden, websites, MFA, rechten,
  CVD, bewijs, continuïteit, screening, training, kwetsbaarheden, pentests,
  back-ups, logging, detectie en versleuteling. Een paar mogen niet van
  toepassing zijn, bijvoorbeeld als niets via internet bereikbaar is.
- **De keten.** Onafhankelijk bewijs (5.20.03 en 5.20.04), snel melden (5.20.05),
  een exit (5.20.06) en de eigen leveranciers (5.21.02 tot en met 5.21.04).
- **De Cyberbeveiligingswet.** Valt de opdrachtgever onder de wet, of weet de
  invuller dat niet, dan komen er vier eisen bij: informatie voor de melding
  binnen 72 uur, een tussenstand, en het eindverslag uiterlijk een maand na de
  melding, inzicht in de kwaliteit en het veilig ontwikkelen, en medewerking aan
  het toezicht. Ze volgen de meldtermijnen uit de wet, en de zorgplicht voor
  de keten.
- **De zorgplicht.** Valt de opdrachtgever onder de wet, of weet de invuller
  dat niet, dan zet het document de tien onderdelen van de zorgplicht uit
  artikel 21, derde lid, in een tabel. Per onderdeel staan de maatregelen uit de BIO2 die
  erbij horen, en of de dienst daaraan voldoet. Er komen geen vragen bij: de
  stand volgt uit de antwoorden per maatregel. De koppeling komt uit tabel 4 van
  de generieke baselinetoets BIO2.
- **Het cloudbeleid.** Bij elke cloud: data in de EER of Zwitserland, geen
  leveranciers uit uitgesloten landen, en openheid over recht van buiten de EU.
  Bij vertrouwelijke gegevens ook versleuteling en eigen sleutels.

Of de leverancier zelf onder de Cyberbeveiligingswet of NIS2 valt, komt in het
document. Het vervangt de eisen niet. Een bestand van quickscan 2.0 vult die
vraag in, uit `leverancier_cbw`.

## Bronnen

- [BIO2, versie 1.3](https://www.bio-overheid.nl/bio2/bio-producten/baseline-informatiebeveiliging-overheid-2-bio2/)
  (9 januari 2026): de nummers van de overheidsmaatregelen.
- [Cyberbeveiligingswet](https://wetten.overheid.nl/BWBR0052872), geldend vanaf
  15 augustus 2026: de zorgplicht (artikel 21) en de meldtermijnen (artikel 26
  tot en met 29).
- [Cyberbeveiligingsregeling sector overheid](https://wetten.overheid.nl/BWBR0052963)
  (Stcrt. 2026, 27679).
- [Herziening rijksbreed cloudbeleid 2026](https://www.tweedekamer.nl/downloads/document?id=2026D35295)
  (3 juli 2026).
- [Generieke Baselinetoets BIO2](https://www.bio-overheid.nl/bio2/bio-producten/generieke-baselinetoets-bio2/),
  versie 3.2 (14 oktober 2026): de koppeling van de zorgplicht aan de
  maatregelen.

Dezelfde bronnen als [quickscan 2.0](../quickscan2/README.md). De teksten van de
overheidsmaatregelen volgen die van de quickscan.

## Beperkingen

- De omschrijvingen bij de maatregelen uit ISO 27002 zijn een eigen
  samenvatting in gewone taal. De tekst van de norm zelf is niet openbaar.
- De overheidsmaatregelen zijn een selectie: de maatregelen die de leverancier
  raken. Het is geen volledige verklaring van toepasselijkheid.
- De tabel met de zorgplicht laat zien hoe de maatregelen ervoor staan. Of de
  opdrachtgever daarmee aan de zorgplicht voldoet, beoordeelt die zelf.
- De vier eisen bij de Cyberbeveiligingswet zijn een eigen uitwerking van wat
  de opdrachtgever van de keten nodig heeft. De wet zelf legt ze niet op aan de
  leverancier.
