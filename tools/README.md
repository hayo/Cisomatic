# Tools

Scripts om een document te maken zonder de webapp, op de opdrachtregel. Ze
gebruiken de motor zelf, dus ze lopen niet achter op de formulieren. Via een
webserver doen ze niets: ze geven dan een 404.

```
php tools/vragen.php <formulier>                     alle vragen, één regel per vraag
php tools/vragen.php <formulier> --uitleg            met de hints en de uitleg bij keuzes
php tools/vragen.php <formulier> --sectie=id,id      alleen deze secties
php tools/vragen.php <formulier> --sjabloon          een leeg antwoorden.json

php tools/maak.php <formulier> <antwoorden.json> <uitmap>           .odt en antwoordbestand
php tools/maak.php <formulier> <antwoorden.json> <uitmap> --tekst   ook het document als tekst
php tools/maak.php <formulier> <antwoorden.json> <uitmap> --alles   idem, met de verantwoording
php tools/maak.php <formulier> <antwoorden.json> <uitmap> --pdf     ook een PDF, met LibreOffice
```

`maak.php` controleert eerst de antwoorden: sleutels die niet bestaan, keuzes die
niet bestaan, antwoorden die wegvallen omdat de vraag niet geldt, en de
invulplicht. Bij een fout komt er geen document en stopt het met code 1. Daarna
schrijft het het `.odt`-bestand en het antwoordbestand, met dezelfde naam,
hetzelfde kenmerk en dezelfde vingerafdruk als de webapp. Het noemt ook wat in de
tekst opvalt: een streepje voor een regel, een regel die eindigt op een dubbele
punt, een punt achter een kort veld, een alinea van meer dan 350 tekens, en twee
leestekens achter elkaar in het document. Het antwoordbestand laad je in de
webapp weer in.

`skill/SKILL.md` is de werkwijze voor een AI-assistent die met deze scripts
documenten maakt, met de regels voor de antwoorden.
