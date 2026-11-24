---
name: cisomatic-document
description: Maak een document met de Cisomatic (DPIA, quickscan, IAMA, AI-scan, business case, fit/gap, soevereiniteitsscan, kanalenscan, CIO-oordeel) zonder de webapp. Gebruik dit als iemand vraagt een van die formulieren in te vullen of er een .odt van te maken, op basis van bronnen of een gesprek.
---

# Een document maken met de Cisomatic

De Cisomatic maakt van antwoorden een document. Twee scripts in de repo doen het
werk. Lees de motor niet en schrijf geen eigen scripts: alles wat je nodig hebt,
geven de scripts.

## Werkwijze

1. **Repo.** Werk in een checkout van `hayo/cisomatic`. Nodig: PHP 8.1 of
   nieuwer. LibreOffice alleen voor een PDF.
2. **Vragen.** `php tools/vragen.php <formulier>` geeft alle vragen, één regel per
   vraag. De formulieren: `businesscase`, `quickscan2`, `quickscan`, `dpia`,
   `iama`, `aiscan`, `soevereiniteit`, `socialemedia`, `fitgap2`, `fitgap`,
   `ciooordeel`. Bij een groot formulier: `--sectie=id,id` voor een deel, en
   `--uitleg` alleen als je de hints echt nodig hebt.
3. **Sjabloon.** `php tools/vragen.php <formulier> --sjabloon > antwoorden.json`
   geeft alle sleutels, leeg. Vul in wat geldt. Laat leeg wat niet geldt: de
   motor gooit antwoorden op vragen die niet gelden toch weg.
4. **Onderzoek.** Zoek de bronnen en schrijf de antwoorden. Dit is het echte werk.
5. **Maken.** `php tools/maak.php <formulier> antwoorden.json <uitmap>`. Dat
   controleert de antwoorden, maakt het .odt-bestand en het antwoordbestand
   (.json), en noemt wat opvalt. Een fout? Dan komt er geen document, en staat
   er precies wat ontbreekt of niet bestaat. Herstel en draai opnieuw.
6. **Nakijken.** Lees de meldingen onder "Let op" en herstel ze. Met `--tekst`
   zie je het hele document als tekst; lees alleen de delen die je wilt
   controleren. Een PDF (`--pdf`) is alleen nodig voor een laatste blik op de
   opmaak, en dan een paar pagina's, niet allemaal.
7. **Opleveren.** Lever het .odt-bestand en het antwoordbestand (.json) op. Met
   het antwoordbestand kan de gebruiker in de webapp verder: inladen, aanpassen,
   en het document opnieuw maken.

Een antwoordbestand uit de webapp (met `"antwoorden"` erin) mag ook als invoer.
Antwoorden van het ene formulier vullen het andere in waar de sleutel gelijk is,
zoals van de quickscan naar de DPIA.

## Hoe je antwoorden schrijft

- **B1-Nederlands.** Korte zinnen, gewone woorden, actief.
- **Wat nog niet vaststaat:** schrijf het op, met "(te bevestigen)" erachter.
  Verzin niets. `maak.php` telt hoe vaak het er nog staat.
- **Kies uit de keuzes** die `vragen.php` noemt. Gebruik de waarde voor het `=`,
  niet het label.
- **Een kort veld** (`text`) is een naam of een paar woorden, zonder punt aan het
  eind.
- **Een tekstvak** (`textarea`) met meer punten: elk punt op een eigen regel
  (`\n`), zonder streepje, sterretje of nummer ervoor. Het document maakt er zelf
  een lijst van.
- **Geen dubbele punt aan het eind van een regel.** Het document zet soms zelf
  een leesteken achter een antwoord.
- **Keuzes** bij `checkbox` als lijst, bij `maatregelen` als
  `{"maatregel": "al" | "komt" | "nee"}`, bij `rijen` als lijst van objecten met
  de kolommen.
- **(voorstel)** bij een vraag betekent: in de browser vult het formulier die
  vraag voor. Hier vul je hem zelf in.

## Wijzigingen in de repo

Pas je iets aan in de repo, lees dan eerst de projectregels in de hoofdmap en
volg ze: geen verwijzing naar de assistent in code, commits of pull requests, de
vaste auteur van commits, en een nieuwe versie bovenaan `wijzigingen/versies.php`.
