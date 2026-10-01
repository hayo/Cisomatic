<?php

/**
 * De volledige toelichtingen uit de IB & Privacy Quickscan.
 *
 * In het formulier staat bij elke vraag een korte, heldere formulering.
 * Deze teksten komen daaronder te staan in een uitklapblok, zodat de
 * oorspronkelijke omschrijving altijd na te lezen is zonder dat het
 * formulier onleesbaar wordt.
 */

declare(strict_types=1);

/** @return array<string,string> */
function qs_toelichting_proces(): array
{
    return [
        'ondersteunend' => 'Voorwaardenscheppend. Activiteiten waaraan de typering '
            . '‘handig om te hebben’ kan worden toegekend. Deze activiteiten hebben geen directe '
            . 'relatie met het voortbrengen van de producten of diensten waaraan de instelling haar '
            . 'bestaansrecht ontleent. Meestal is sprake van een ondersteunende rol naar de lijn. '
            . 'De activiteiten vormen een waardevolle support van het primaire proces.',

        'bijdragend' => 'Subtaak. Er is slechts sprake van een indirecte relatie met de '
            . 'hoofdactiviteiten van het ministerie, kerndepartement of de uitvoeringsorganisatie. '
            . 'Het ontbreken van het bijdragende proces leidt wel tot verlies van effectiviteit en '
            . 'efficiency binnen het primaire proces.',

        'strategisch' => 'Afgeleide kerntaak. Het proces heeft een directe relatie met het '
            . 'uitvoeren van de doelstellingen van het ministerie, kerndepartement of de '
            . 'uitvoeringsorganisatie. Het betreft het primaire proces van de directie, agentschap '
            . 'of raad, en daarmee de uitvoering van wettelijke taken.',

        'kritisch-strategisch' => 'Kerntaak. Er is sprake van een kritisch strategisch proces als '
            . 'één of meer van deze aspecten van toepassing zijn. Het betreft een maatschappelijk '
            . 'vitaal proces doordat het proces raakt aan: territoriale veiligheid (het ongestoord '
            . 'functioneren van Nederland als onafhankelijke staat, en in het bijzonder de '
            . 'territoriale integriteit van het grondgebied en de internationale positie); fysieke '
            . 'veiligheid (het ongestoord functioneren van de mens in Nederland en zijn omgeving); '
            . 'economische veiligheid (het ongestoord functioneren van Nederland als een effectieve '
            . 'en efficiënte economie); ecologische veiligheid (voldoende zelfherstellend vermogen '
            . 'van de leefomgeving bij aantasting); sociale en politieke stabiliteit (het ongestoorde '
            . 'voortbestaan van een maatschappelijk klimaat waarin groepen mensen goed met elkaar '
            . 'kunnen samenleven binnen de kaders van de democratische rechtsstaat en gedeelde '
            . 'kernwaarden). Óf: als de activiteit langer dan één week stilvalt of niet goed verloopt, '
            . 'heeft dit ernstige gevolgen voor het voortbestaan van de organisatie, c.q. het brengt '
            . 'het ministerie, kerndepartement of de uitvoeringsorganisatie in een hachelijke positie.',
    ];
}

/** @return array<string,string> */
function qs_toelichting_systeem(): array
{
    return [
        'nuttig' => 'Het informatiesysteem geeft support bij de uitvoering van het proces of de '
            . 'processen en is ‘handig om te hebben’.',

        'belangrijk' => 'Het informatiesysteem levert een belangrijke bijdrage aan de uitvoering van '
            . 'het proces of de processen. Slechts met grote, onevenredige inspanning is voortzetting '
            . 'van het proces mogelijk. Inzet van het informatiesysteem heeft een positief effect op '
            . 'de doeltreffendheid en doelmatigheid van de organisatie. Het informatiesysteem wordt '
            . 'door veel (interne of externe) medewerkers of burgers gebruikt.',

        'vitaal' => 'Het uitvoeren van het proces of de processen is (nagenoeg) onmogelijk zonder de '
            . 'inzet van het informatiesysteem. Inzet van het informatiesysteem is essentieel voor een '
            . 'goede uitvoering van het proces of de processen.',
    ];
}

/** @return array<string,string> */
function qs_toelichting_beschikbaarheid(): array
{
    return [
        'zl' => 'Uitval van het proces met ondersteunende systemen voor een langere periode dan een '
            . 'week heeft geen gevolgen voor de organisatie, burgers of gebruikers. Uitval kan maximaal '
            . 'leiden tot: geen of nauwelijks financiële gevolgen; geen of minimaal verlies van '
            . 'management control en een week vertraging van nieuwe ontwikkelingen; geen of minimale '
            . 'negatieve publiciteit; geen of minimaal verlies van motivatie van medewerkers.',

        'l' => 'Uitval van vier dagen tot een week (ook in piekperiodes) heeft nauwelijks of geen '
            . 'gevolgen. Meer uitval is niet acceptabel omdat dit kan leiden tot: melding in de pers en '
            . 'sociale media, lokaal protest, diplomatieke schade te herstellen door ambtelijke '
            . 'opschaling; minimale financiële gevolgen, zonder problemen op te vangen binnen het '
            . 'beschikbare departementale budget; matig verlies van management control en een maand '
            . 'vertraging van nieuwe ontwikkelingen; departementale negatieve publiciteit; matig '
            . 'verlies van motivatie van medewerkers.',

        'm' => 'Een enkele keer uitval is aanvaardbaar. Eén tot drie dagen uitval is toegestaan. Meer '
            . 'uitval is niet acceptabel omdat dit kan leiden tot: het in de Tweede Kamer ter '
            . 'verantwoording roepen van een bewindspersoon, diplomatieke schade te herstellen door '
            . 'politieke opschaling; substantiële financiële gevolgen, met moeite op te vangen binnen '
            . 'het departementale budget; belangrijk verlies van management control en een kwartaal '
            . 'vertraging van nieuwe ontwikkelingen; Rijksbrede negatieve publiciteit; significant '
            . 'verlies van motivatie van medewerkers.',

        'h' => 'Nauwelijks uitval gedurende openingstijd is toegestaan. Vier tot acht uur uitval is '
            . 'toegestaan. Meer uitval is niet acceptabel omdat dit kan leiden tot: politieke schade aan '
            . 'een bewindspersoon, stakingen of demonstraties van redelijke omvang en duur, diplomatieke '
            . 'protesten en sancties; grote financiële gevolgen, alleen op te vangen ten koste van al '
            . 'gealloceerd budget; ernstig verlies van management control en een jaar vertraging van '
            . 'nieuwe ontwikkelingen; negatieve publiciteit op landelijk niveau; ernstig verlies van '
            . 'motivatie van medewerkers.',

        'zh' => 'Slechts in uitzonderlijke gevallen mag het proces of het ondersteunende systeem niet '
            . 'operationeel zijn. Nul tot vier uur uitval is toegestaan. Meer uitval is niet acceptabel '
            . 'omdat dit kan leiden tot: aftreden van een bewindspersoon, parlementair onderzoek, '
            . 'stakingen of demonstraties van landelijke omvang en langer dan een week, externe '
            . 'diplomatieke bemiddeling; zeer grote financiële gevolgen die niet binnen het departementale '
            . 'budget op te vangen zijn; extreem verlies van management control en jaren vertraging van '
            . 'nieuwe ontwikkelingen; negatieve publiciteit minstens op het niveau van de Europese Unie; '
            . 'volledig verlies van motivatie van medewerkers.',
    ];
}

/** @return array<string,string> */
function qs_toelichting_integriteit(): array
{
    return [
        'zl' => 'Bij de verwerking weet men dat de informatie niet correct, volledig of actueel zou '
            . 'kunnen en hoeven zijn. Deze informatie wordt bijvoorbeeld gebruikt om trends waar te '
            . 'nemen. Onjuiste informatie leidt tot geen of nauwelijks financiële gevolgen, geen of '
            . 'minimaal verlies van management control en geen of minimale negatieve publiciteit.',

        'l' => 'Aan correctheid, volledigheid, actualiteit, authenticiteit, controleerbaarheid en '
            . 'consistentie worden lage eisen gesteld. Onjuiste informatie kan leiden tot melding in '
            . 'de pers en sociale media, lokaal protest, minimale financiële gevolgen, matig verlies van '
            . 'management control en departementale negatieve publiciteit.',

        'm' => 'Aan correctheid, volledigheid, actualiteit, authenticiteit, controleerbaarheid en '
            . 'consistentie worden geen hoge eisen gesteld. De informatie hoeft niet altijd foutloos te '
            . 'zijn: integriteit is gewenst, maar het niet integer zijn leidt niet tot grote schade. Het '
            . 'kan leiden tot Kamervragen, substantiële financiële gevolgen, belangrijk verlies van '
            . 'management control en Rijksbrede negatieve publiciteit. Deze eis is ook van toepassing als '
            . 'sprake is van verwerking van één categorie gewone persoonsgegevens '
            . '(AVG beginsel ‘Juistheid’).',

        'h' => 'Aan correctheid, volledigheid, actualiteit, authenticiteit, controleerbaarheid en '
            . 'consistentie worden hoge eisen gesteld. De informatie dient nagenoeg foutloos te zijn; bij '
            . 'niet integer zijn ontstaan negatieve gevolgen voor de organisatie. Het kan leiden tot '
            . 'politieke schade aan een bewindspersoon, stakingen of demonstraties, diplomatieke protesten '
            . 'en sancties, grote financiële gevolgen en negatieve publiciteit op landelijk niveau. Deze '
            . 'eis is van toepassing als sprake is van verwerking van meerdere categorieën '
            . 'persoonsgegevens en/of van één categorie bijzondere persoonsgegevens (AVG beginsel '
            . '‘Juistheid’).',

        'zh' => 'Het bedrijfsproces eist foutloze informatie. Het risico is dusdanig hoog dat de hoogste '
            . 'eisen aan integriteit worden gesteld. Het gaat om gegevensverwerkingen waarbij fouten grote '
            . 'consequenties hebben, onder andere informatie waarop besluitvorming en verantwoording is '
            . 'gebaseerd of die in een gerechtelijk proces wordt gebruikt. Het kan leiden tot aftreden van '
            . 'een bewindspersoon, parlementair onderzoek, zeer grote financiële gevolgen en negatieve '
            . 'publiciteit op Europees niveau. Deze eis is ook van toepassing als sprake is van verwerking van '
            . 'meerdere categorieën bijzondere persoonsgegevens (AVG beginsel ‘Juistheid’).',
    ];
}

/** @return array<string,string> */
function qs_toelichting_vertrouwelijkheid(): array
{
    return [
        'zl' => 'Het gaat om openbare informatie. De informatie hoeft niet afgeschermd te worden; geen '
            . 'enkele beveiliging op de informatie is noodzakelijk. Onbedoeld bekend worden leidt tot geen '
            . 'of nauwelijks financiële gevolgen en geen of minimale negatieve publiciteit.',

        'l' => 'Het gaat om half openbare informatie. De informatie vormt bij normaal gebruik geen risico. '
            . 'Openbaar worden kan leiden tot melding in de pers en sociale media, lokaal protest, minimale '
            . 'financiële gevolgen, matig verlies van management control en departementale negatieve '
            . 'publiciteit.',

        'm' => 'De informatie mag alleen ter inzage zijn voor een bepaalde groep en kan gemerkt zijn als '
            . '‘Alleen voor intern gebruik’ of ‘Persoonlijk’. Organisatiebelangen of personen worden niet '
            . 'ernstig geschaad als ongeautoriseerden toegang krijgen. Openbaar worden kan leiden tot '
            . 'Kamervragen, substantiële financiële gevolgen, belangrijk verlies van management control en '
            . 'Rijksbrede negatieve publiciteit. Deze eis is ook van toepassing als sprake is van '
            . 'verwerking van één categorie gewone persoonsgegevens.',

        'h' => 'De informatie mag alleen toegankelijk zijn voor direct betrokkenen. Toegang door '
            . 'ongeautoriseerden heeft een negatieve impact op de organisatie, bijvoorbeeld op het imago. '
            . 'Het kan gaan om departementaal vertrouwelijke informatie of informatie gemerkt als '
            . '‘Personeelsvertrouwelijk’, ‘Commercieel vertrouwelijk’ of ‘Medisch geheim’. Openbaar worden '
            . 'kan leiden tot politieke schade aan een bewindspersoon, stakingen of demonstraties, '
            . 'diplomatieke protesten en sancties, grote financiële gevolgen en negatieve publiciteit op '
            . 'landelijk niveau. Deze eis is van toepassing als sprake is van verwerking van meerdere '
            . 'categorieën persoonsgegevens en/of van één categorie bijzondere persoonsgegevens.',

        'zh' => 'Kennisname door ongeautoriseerden kan grote politieke, maatschappelijke of financiële '
            . 'gevolgen voor het ministerie hebben. Het kan gaan om staatsgeheime informatie. '
            . 'Ongeautoriseerd openbaar worden kan leiden tot aftreden van een bewindspersoon, parlementair '
            . 'onderzoek, stakingen of demonstraties van landelijke omvang, externe diplomatieke bemiddeling, '
            . 'zeer grote financiële gevolgen en negatieve publiciteit op Europees niveau. Deze eis is van '
            . 'toepassing als sprake is van verwerking van meerdere categorieën bijzondere persoonsgegevens.',
    ];
}

/** @return array<string,array{titel:string,data:string,eis:string}> */
function qs_toelichting_cloud(): array
{
    return [
        'laag' => [
            'titel' => 'Categorie Laag',
            'data'  => 'Openbare data.',
            'eis'   => 'Er mag gebruik worden gemaakt van alle clouddienstverleners zolang deze niet in '
                . 'strijd zijn met wetten en regels. Leveranciers of diensten uit landen met een actief '
                . 'cyberprogramma dat gericht is tegen Nederlandse belangen worden altijd uitgesloten.',
        ],
        'midden' => [
            'titel' => 'Categorie Midden',
            'data'  => 'Overheidsdata zonder rubricering; informatie waarin maximaal één categorie '
                . 'persoonsgegevens is verwerkt; of wanneer de eis voor vertrouwelijkheid ‘Midden’ is.',
            'eis'   => 'Informatie mag alleen in de EER worden opgeslagen, óf in landen waarvoor een '
                . 'adequaatheidsbesluit bestaat, óf op basis van een modelcontract dat voldoet aan de AVG. '
                . 'Kan hier niet aan worden voldaan, dan dient de DPIA aan de CIO Rijk gestuurd te worden. '
                . 'Cloudleveranciers moeten voldoen aan ISO 27001, ISO 27017 en ISO 27018.',
        ],
        'hoog' => [
            'titel' => 'Categorie Hoog',
            'data'  => 'Departementaal vertrouwelijke informatie; informatie waarin meerdere categorieën '
                . 'persoonsgegevens en/of meerdere categorieën bijzondere persoonsgegevens zijn verwerkt; '
                . 'wanneer de eis voor integriteit of vertrouwelijkheid ‘Hoog’ of ‘Zeer hoog’ is; of als er '
                . 'sprake is van een hoog risicoprofiel.',
            'eis'   => 'De data mogen alleen in de EER worden opgeslagen, óf in landen waarvoor een '
                . 'adequaatheidsbesluit bestaat. Als dat niet het geval is, of er is alleen sprake van een '
                . 'modelcontract, dan wordt door de CISO een beoordeling uitgevoerd op basis van de '
                . 'criteria van C2000 en dient een explain (met minimaal een uitgevoerde DPIA) aan de CIO Rijk '
                . 'gestuurd te worden. De cloudleverancier moet voldoen aan de controls van ISO 27001, van '
                . 'ISO 27017 (indien er gegevens worden verwerkt die niet openbaar zijn) en van ISO 27018 (indien '
                . 'persoonsgegevens worden verwerkt).',
        ],
        'zeer-hoog' => [
            'titel' => 'Categorie Zeer hoog',
            'data'  => 'Staatsgeheime informatie, NATO restricted of hoger, of een hoog dreigingsprofiel.',
            'eis'   => 'Er mag onder geen enkele voorwaarde gebruik worden gemaakt van een publieke '
                . 'clouddienstverlener.',
        ],
    ];
}

/** Landen met een actief cyberprogramma gericht tegen Nederlandse belangen. */
function qs_uitgesloten_landen(): array
{
    return ['Rusland', 'China', 'Iran', 'Noord-Korea'];
}
