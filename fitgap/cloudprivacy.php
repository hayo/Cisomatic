<?php

/**
 * De normen voor cloud en privacy, die beide fit/gap formulieren gebruiken:
 * ISO 27017 voor de cloud, ISO 27018 voor persoonsgegevens in de public cloud,
 * en bijlage A en B van ISO/IEC 27701:2019 voor de verwerkingsverantwoordelijke
 * en de verwerker.
 *
 * De teksten komen uit de fit/gap analyse BIO, versie 1.05, met kleine
 * correcties in spelling. De titels bij ISO 27017 zijn een eigen
 * samenvatting. De sleutels zijn in beide formulieren gelijk, dus een bestand
 * van het ene vult deze normen in het andere in.
 */

declare(strict_types=1);

const FG_CLOUD_NORMEN = [
    'CLD.6.3.1' => ['Gedeelde rollen en verantwoordelijkheden in de cloud',
        'Verantwoordelijkheden voor gedeelde informatiebeveiliging rollen bij het gebruik van de cloudservice moeten '
            . 'worden toegewezen aan aangewezen partijen en gedocumenteerd, gecommuniceerd en geïmplementeerd door '
            . 'zowel de cloudservice klant als de cloudservice provider.'],
    'CLD.8.1.5' => ['Verwijderen en teruggeven van bedrijfsmiddelen van de klant',
        'Bedrijfsmiddelen van de klant van de cloudservice die zich bij de cloudservice provider bevinden, moeten '
            . 'worden verwijderd en tijdig te worden geretourneerd indien nodig, bij het beëindigen van de clouddienst '
            . 'overeenkomst.'],
    'CLD.9.5.1' => ['Scheiding in virtuele omgevingen',
        'De virtuele omgeving van een cloudservice klant, moet worden beveiligd tegen andere cloudservice klanten en '
            . 'onbevoegde personen.'],
    'CLD.9.5.2' => ['Hardening van virtuele machines',
        'Virtuele machines in een cloud computing-omgeving moeten worden gehard om aan zakelijke eisen te voldoen.'],
    'CLD.12.1.5' => ['Beveiliging van beheerhandelingen',
        'Procedures voor administratieve handelingen in een cloud computing-omgeving moeten worden gedefinieerd, '
            . 'gedocumenteerd en gemonitord.'],
    'CLD.12.4.5' => ['Monitoren van clouddiensten',
        'De cloudservice klant moet in staat zijn om specifieke aspecten van de werking van de cloudservices te '
            . 'controleren.'],
    'CLD.13.1.4' => ['Afstemming van virtuele en fysieke netwerken',
        'Bij configuratie van virtuele netwerken moet configuratie consistentie tussen virtuele en fysieke netwerken '
            . 'worden geverifieerd op basis van het netwerkbeveiliging beleid van de cloud serviceprovider.'],
];

const FG_CLOUD_PRIVACY_NORMEN = [
    'A.2.1' => ['Verplichting samenwerking met betrekking tot persoonsgegevens rechten',
        'De public cloud persoonsgegevens verwerker moet de cloudservice klant de middelen bieden om deze in staat '
            . 'te stellen aan zijn verplichting te voldoen om de uitoefening van de rechten van natuurlijke persoon op '
            . 'toegang tot, correctie en/of wissen van persoonsgegevens mogelijk te maken.'],
    'A.3.1' => ['Doelbinding cloud persoonsgegevens',
        'Persoonsgegevens die moeten worden verwerkt op basis van een contract, mogen niet worden verwerkt voor '
            . 'andere doelen dan conform instructies van de cloudservice klant.'],
    'A.3.2' => ['Commercieel gebruik cloud persoonsgegevens',
        'Persoonsgegevens die onder een contract verwerkt worden mogen niet worden gebruikt door de public cloud '
            . 'persoonsgegevens verwerker voor marketingdoeleinden en advertenties, zonder uitdrukkelijke toestemming. '
            . 'Een dergelijke toestemming mag geen voorwaarde zijn voor de service.'],
    'A.5.1' => ['Veilig wissen van tijdelijke bestanden',
        'Tijdelijke bestanden en documenten moeten worden gewist of vernietigd binnen een gespecificeerde, '
            . 'gedocumenteerde periode.'],
    'A.6.1' => ['Melding van onthulling persoonsgegevens',
        'Het contract tussen de public cloud persoonsgegevens verwerker en de cloudservice klant vereist dat de '
            . 'public cloud persoonsgegevens verwerker de cloudservice klant op de hoogte brengt van elk juridisch '
            . 'bindend verzoek tot openbaarmaking van persoonsgegevens (conform de voorwaarden in het contract) door '
            . 'een wetshandhaving instantie, tenzij een dergelijke onthulling anderszins is verboden.'],
    'A.6.2' => ['Registratie van onthulling persoonsgegevens',
        'Onthulling van persoonsgegevens aan derden moet worden vastgelegd, inclusief welke persoonsgegevens zijn '
            . 'vrijgegeven, aan wie en op welk tijdstip.'],
    'A.8.1' => ['Bekendmaking van uitbestede PG-verwerking',
        'Wanneer de public cloud persoonsgegevens verwerker gebruik maakt van onderaannemers voor de verwerking van '
            . 'PG, moeten deze vóór gebruik worden bekendgemaakt aan de relevante cloudservice klanten.'],
    'A.10.1' => ['Melding van een datalek met PG',
        'De public cloud persoonsgegevens verwerker moet de betrokken cloudservice klant onmiddellijk op de hoogte '
            . 'brengen van ongeoorloofde toegang tot persoonsgegevens of ongeoorloofde toegang tot '
            . 'verwerkingsapparatuur of -faciliteiten, die leiden tot verlies, openbaarmaking of wijziging van PG.'],
    'A.10.2' => ['Bewaartermijn voor beveiligingsbeleid en richtlijnen',
        'Kopieën van beveiligingsbeleid en operationele procedures moeten worden bewaard gedurende een vastgelegde '
            . 'periode voor vervanging (inclusief bijwerking).'],
    'A.10.3' => ['Teruggave, overdracht en verwijdering van persoonsgegevens',
        'De public cloud persoonsgegevens verwerker moet een beleid hebben met betrekking tot de retournering, '
            . 'overdracht en/of verwijdering van PII en moet dit beleid beschikbaar stellen aan de cloudservice klant.'],
    'A.11.1' => ['Vertrouwelijkheids- of geheimhoudingsovereenkomsten',
        'Personen die onder verantwoordelijkheid vallen van de public cloud persoonsgegevens verwerker en die '
            . 'toegang hebben tot persoonsgegevens moeten voldoen aan vertrouwelijkheid eisen.'],
    'A.11.2' => ['Beperking van het maken van hardcopy-materiaal',
        'Het maken van hardcopy-materiaal met persoonsgegevens moet worden beperkt.'],
    'A.11.3' => ['Beheer en logging van gegevensherstel',
        'Er moet een procedure zijn voor en een logboek van gegevensherstel activiteiten.'],
    'A.11.4' => ['Gegevens beschermen op mobiele opslagmedia',
        'persoonsgegevens op media die de panden van de organisatie verlaten, moet een autorisatieprocedure volgen '
            . 'en mag alleen toegankelijk zijn voor bevoegd personeel (bijvoorbeeld door de betreffende gegevens te '
            . 'versleutelen).'],
    'A.11.5' => ['Gebruik van niet-versleutelde draagbare opslagmedia en apparaten',
        'Mobiele fysieke media en draagbare apparaten zonder encryptie mogen niet worden gebruikt, behalve waar dit '
            . 'onvermijdelijk is, en elk gebruik van dergelijke draagbare media en apparaten moet worden '
            . 'geregistreerd.'],
    'A.11.6' => ['Encryptie van persoonsgegevens on the move',
        'persoonsgegevens die wordt verzonden via openbare datatransmissie netwerken moet vóór verzending worden '
            . 'gecodeerd.'],
    'A.11.7' => ['Veilige verwijdering van hardcopies',
        'Wanneer hardcopies worden vernietigd, moeten deze veilig worden vernietigd met behulp van mechanismen zoals '
            . 'horizontaal snijden, versnipperen, verbranden, vermalen, enz.'],
    'A.11.8' => ['Uniek gebruik van gebruikers-ID’s',
        'Als meer dan één persoon toegang heeft tot opgeslagen PG, moet elk persoon een afzonderlijke gebruikers-ID '
            . 'hebben voor identificatie, authenticatie en autorisatie.'],
    'A.11.9' => ['Vastlegging van geautoriseerde gebruikers',
        'Er moet een up-to-date vastlegging worden onderhouden van de gebruikers of profielen van gebruikers die '
            . 'geautoriseerde toegang hebben tot het informatiesysteem.'],
    'A.11.10' => ['Beheer van gebruikers-ID’s',
        'Gedeactiveerde of verlopen gebruikers-ID’s mogen niet aan andere personen worden verstrekt.'],
    'A.11.11' => ['Contractmaatregelen',
        'Contracten tussen de klant van de clouddienst en de persoonsgegevens verwerker voor de openbare cloud '
            . 'moeten minimale technische en organisatorische maatregelen bevatten, om ervoor te zorgen dat de '
            . 'overeengekomen beveiligingsregelingen van kracht zijn en dat de gegevens niet worden verwerkt buiten de '
            . 'instructies van de persoonsgegevens verantwoordelijke. Dergelijke maatregelen mogen niet eenzijdig '
            . 'worden afgezwakt door de public cloud persoonsgegevens verwerker.'],
    'A.11.12' => ['Uitbesteding verwerking persoonsgegevens',
        'Contracten tussen de public cloud persoonsgegevens verwerker en eventuele subcontractanten die '
            . 'persoonsgegevens verwerken, moeten minimale technische en organisatorische maatregelen omvatten die '
            . 'voldoen aan de informatiebeveiliging- en PG-beveiligingsverplichtingen van de public '
            . 'cloudpersoonsgegevens verwerker. Dergelijke maatregelen mogen niet eenzijdig worden beperkt door de '
            . 'onderaannemer.'],
    'A.11.13' => ['Toegang tot gegevens op gebruikte media',
        'De public cloud persoonsgegevens verwerker moet ervoor zorgen dat wanneer gegevensopslagruimte wordt '
            . 'toegewezen aan een klant van de cloudservice, alle gegevens die eerder in die opslagruimte aanwezig '
            . 'waren, niet zichtbaar zijn voor die klant van de cloudservice.'],
    'A.12.1' => ['Geografische locatie van persoonsgegevens',
        'De public cloud persoonsgegevens verwerker moet de landen waarin persoonsgegevens mogelijk kunnen worden '
            . 'opgeslagen specificeren en documenteren.'],
    'A.12.2' => ['Beoogde bestemming van persoonsgegevens',
        'Voor persoonsgegevens die worden verzonden via een datatransmissie netwerk moeten passende maatregelen '
            . 'worden genomen, om ervoor te zorgen dat de gegevens de beoogde bestemming bereiken.'],
];

const FG_PRIVACY_A_NORMEN = [
    'A.7.2.1' => ['Doeleinde identificeren en documenteren',
        'De organisatie moet de specifieke doeleinden waarvoor de PII wordt verwerkt, identificeren en documenteren.'],
    'A.7.2.2' => ['Rechtsgrond identificeren',
        'De organisatie moet de relevante rechtsgrond voor het verwerken van PII voor de geïdentificeerde doeleinden '
            . 'vaststellen, documenteren en eraan voldoen.'],
    'A.7.2.3' => ['Vaststellen wanneer en hoe toestemming moet worden verkregen',
        'De organisatie moet een proces vaststellen en documenteren aan de hand waarvan zij kan aantonen of, wanneer '
            . 'en hoe toestemming voor het verwerken van PII is verkregen van PII-betrokkenen.'],
    'A.7.2.4' => ['Toestemming verkrijgen en registreren',
        'De organisatie moet toestemming van PII-betrokkenen verkrijgen en registreren overeenkomstig de '
            . 'gedocumenteerde processen.'],
    'A.7.2.5' => ['Privacyeffectbeoordeling',
        'De organisatie moet beoordelen of een privacy-effectbeoordeling nodig is en deze indien van toepassing '
            . 'implementeren, telkens als er nieuwe verwerkingen van PII of veranderingen aan de bestaande verwerking '
            . 'van PII gepland zijn.'],
    'A.7.2.6' => ['Overeenkomsten met PII-verwerkers',
        'De organisatie moet een schriftelijke overeenkomst hebben met elke PII-verwerker waarvan zij gebruikmaakt '
            . 'en moet bewerkstelligen dat de overeenkomsten met PII-verwerkers ingaan op de implementatie van de '
            . 'passende beheersmaatregelen in bijlage B.'],
    'A.7.2.7' => ['Gezamenlijke PII-verwerkingsverantwoordelijke',
        'De organisatie moet respectievelijke rollen en verantwoordelijkheden vaststellen voor het verwerken van PII '
            . '(met inbegrip van eisen voor de bescherming en beveiliging van PII) met een '
            . 'mede-PII-verwerkingsverantwoordelijke.'],
    'A.7.2.8' => ['Registraties met betrekking tot het verwerken van PII',
        'De organisatie moet de noodzakelijke registraties ter ondersteuning van haar verplichtingen voor het '
            . 'verwerken van PII vaststellen en op beveiligde wijze bijhouden.'],
    'A.7.3.1' => ['Verplichtingen naar PII-betrokkenen vaststellen en nakomen',
        'De organisatie moet haar verplichtingen vanwege wet- en regelgeving en haar zakelijke verplichtingen naar '
            . 'PII-betrokkenen in verband met het verwerken van hun PII vaststellen en documenteren en voorzien in de '
            . 'middelen om aan deze verplichtingen te voldoen.'],
    'A.7.3.2' => ['Informatie voor PII-betrokkenen vaststellen',
        'De organisatie moet de aan PII-betrokkenen te verstrekken informatie met betrekking tot de verwerking van '
            . 'hun PII en het moment waarop die informatie verstrekt wordt, vaststellen en documenteren.'],
    'A.7.3.3' => ['Informatie geven aan PII-betrokkenen',
        'De organisatie moet PII-betrokkenen voorzien van duidelijke en gemakkelijk toegankelijke informatie waarin '
            . 'de PII-verwerkingsverantwoordelijke wordt geïdentificeerd en de verwerking van hun PII wordt '
            . 'beschreven.'],
    'A.7.3.4' => ['In een mechanisme voorzien om toestemming aan te passen of in te trekken',
        'De organisatie moet voorzien in een mechanisme waarmee PII-betrokkenen hun toestemming kunnen aanpassen of '
            . 'intrekken.'],
    'A.7.3.5' => ['In een mechanisme voorzien om bezwaar te maken tegen verwerking',
        'De organisatie moet voorzien in een mechanisme waarmee PII-betrokkenen bezwaar kunnen maken tegen de '
            . 'verwerking van hun PII.'],
    'A.7.3.6' => ['Toegang, correctie en/of wissen',
        'De organisatie moet beleidsregels, procedures en/of mechanismen implementeren om te voldoen aan haar '
            . 'verplichtingen naar PII-betrokkenen met betrekking tot toegang tot en het corrigeren en/of wissen van '
            . 'hun PII.'],
    'A.7.3.7' => ['Verplichtingen van PII-verwerkingsverantwoordelijken om derden te informeren',
        'De organisatie moet derden met wie PII is gedeeld, in kennis stellen van alle aanpassingen, intrekkingen of '
            . 'bezwaren met betrekking tot de gedeelde PII, en hiervoor passende beleidsregels, procedures en/of '
            . 'mechanismen implementeren.'],
    'A.7.3.8' => ['Een kopie verstrekken van verwerkte PII',
        'De organisatie moet in staat zijn een kopie van de verwerkte PII te verstrekken wanneer de PII-betrokkene '
            . 'daarom verzoekt.'],
    'A.7.3.9' => ['Omgaan met verzoeken',
        'De organisatie moet beleidsregels en procedures definiëren en documenteren voor het omgaan met en reageren '
            . 'op legitieme verzoeken van PII-betrokkenen.'],
    'A.7.3.10' => ['Geautomatiseerde besluitvorming',
        'De organisatie moet verplichtingen, met inbegrip van juridische verplichtingen, naar de PII-betrokkenen '
            . 'identificeren en oppakken die voortkomen uit door de organisatie genomen besluiten die verband houden '
            . 'met de PII-betrokkene, uitsluitend gebaseerd op geautomatiseerde verwerking van PII.'],
    'A.7.4.1' => ['Het verzamelen beperken',
        'De organisatie moet het verzamelen van PII beperken tot het minimum dat relevant, proportioneel en nodig is '
            . 'voor de geïdentificeerde doeleinden.'],
    'A.7.4.2' => ['Het verwerken beperken',
        'De organisatie moet het verwerken van PII beperken tot wat toereikend, relevant en nodig is voor de '
            . 'geïdentificeerde doeleinden.'],
    'A.7.4.3' => ['Juistheid en kwaliteit',
        'De organisatie moet bewerkstelligen en documenteren dat PII gedurende de volledige levenscyclus van de PII '
            . 'zo juist, volledig en actueel is als nodig is voor de doeleinden waarvoor de PII wordt verwerkt.'],
    'A.7.4.4' => ['Doelstellingen voor minimale PII-verwerking',
        'De organisatie moet doelstellingen voor minimale gegevensverwerking en de mechanismen (zoals het '
            . 'niet-identificeerbaar maken) die worden gebruikt om die doelstellingen te bereiken, definiëren en '
            . 'documenteren.'],
    'A.7.4.5' => ['PII aan het einde van de verwerking niet-identificeerbaar maken en wissen',
        'De organisatie moet PII wissen of omzetten in een vorm waardoor PII-betrokkenen niet langer geïdentificeerd '
            . 'of geheridentificeerd kunnen worden, zodra de originele PII niet meer nodig is voor het (de) '
            . 'geïdentificeerde doeleinde(n).'],
    'A.7.4.6' => ['Tijdelijke bestanden',
        'De organisatie moet bewerkstelligen dat tijdelijke bestanden die als gevolg van het verwerken van PII '
            . 'worden aangemaakt, binnen een gespecificeerde, gedocumenteerde periode volgens gedocumenteerde '
            . 'procedures worden verwijderd (bijv. gewist of vernietigd).'],
    'A.7.4.7' => ['Bewaring',
        'De organisatie mag PII niet langer bewaren dan nodig is voor de doeleinden waarvoor de PII wordt verwerkt.'],
    'A.7.4.8' => ['Verwijdering',
        'De organisatie moet beschikken over gedocumenteerde beleidsregels, procedures en/of mechanismen voor de '
            . 'verwijdering van PII.'],
    'A.7.4.9' => ['Beheersmaatregelen voor de overdracht van PII',
        'De organisatie moet via een netwerk voor gegevensoverdracht verzonden PII (bijv. aan een andere '
            . 'organisatie) onderwerpen aan passende beheersmaatregelen die ervoor zijn ontworpen om te '
            . 'bewerkstelligen dat de gegevens hun beoogde bestemming bereiken.'],
    'A.7.5.1' => ['De grondslag voor doorgifte van PII tussen rechtsgebieden identificeren',
        'De organisatie moet de relevante grondslag voor de doorgifte van PII tussen rechtsgebieden identificeren en '
            . 'documenteren'],
    'A.7.5.2' => ['Landen en internationale organisaties waaraan PII kan worden doorgegeven',
        'De organisatie moet de landen en internationale organisaties specificeren en documenteren waaraan PII '
            . 'mogelijk kan worden doorgegeven.'],
    'A.7.5.3' => ['Registraties van doorgifte van PII',
        'De organisatie moet doorgiften van PII aan of vanuit derden registreren en samenwerking bewerkstelligen met '
            . 'die derden ter ondersteuning van toekomstige verzoeken in verband met verplichtingen naar de '
            . 'PII-betrokkenen.'],
    'A.7.5.4' => ['Registraties van de verstrekking van PII aan derden',
        'De organisatie moet de verstrekking van PII aan derden registreren, met inbegrip van welke PII is '
            . 'verstrekt, aan wie en wanneer.'],
];

const FG_PRIVACY_B_NORMEN = [
    'B.8.2.1' => ['Overeenkomst met de klant',
        'De organisatie moet waar relevant bewerkstelligen dat de overeenkomst voor het verwerken van PII ingaat op '
            . 'de rol van de organisatie bij het ondersteunen van de verplichtingen van de klant (rekening houdend met '
            . 'de aard van de verwerking en de informatie die beschikbaar is voor de organisatie).'],
    'B.8.2.2' => ['Doeleinden van de organisatie',
        'De organisatie moet bewerkstelligen dat namens een klant verwerkte PII uitsluitend wordt verwerkt voor de '
            . 'doeleinden die worden aangegeven in de schriftelijke opdracht van de klant.'],
    'B.8.2.3' => ['Gebruik voor marketing en reclame',
        'De organisatie mag krachtens een overeenkomst verwerkte PII niet voor marketing- en reclamedoeleinden '
            . 'gebruiken zonder vast te stellen dat daarvoor vooraf toestemming is verkregen van de desbetreffende '
            . 'PII-betrokkene. De organisatie mag het geven van die toestemming niet als voorwaarde stellen voor het '
            . 'ontvangen van de dienst.'],
    'B.8.2.4' => ['Opdracht die inbreuk oplevert',
        'De organisatie moet de klant in kennis stellen indien, naar haar mening, een verwerkingsopdracht inbreuk '
            . 'oplevert op toepasselijke wet- en/of regelgeving.'],
    'B.8.2.5' => ['Verplichtingen van klanten',
        'De organisatie moet de klant voorzien van de passende informatie zodat de klant kan aantonen dat hij aan '
            . 'zijn verplichtingen voldoet.'],
    'B.8.2.6' => ['Registraties met betrekking tot het verwerken van PII',
        'De organisatie moet de benodigde registraties vaststellen en onderhouden ter ondersteuning van het aantonen '
            . 'van het voldoen aan haar verplichtingen (zoals gespecificeerd in de desbetreffende overeenkomst) voor '
            . 'het verwerken van PII namens een klant.'],
    'B.8.3.1' => ['Verplichtingen naar PII-betrokkenen',
        'De organisatie moet de klant van de middelen voorzien om te voldoen aan zijn verplichtingen met betrekking '
            . 'tot PII-betrokkenen.'],
    'B.8.4.1' => ['Tijdelijke bestanden',
        'De organisatie moet bewerkstelligen dat tijdelijke bestanden die als gevolg van het verwerken van PII '
            . 'worden aangemaakt, binnen een gespecificeerde, gedocumenteerde periode volgens gedocumenteerde '
            . 'procedures worden verwijderd (bijv. gewist of vernietigd).'],
    'B.8.4.2' => ['Retournering, doorgifte of verwijdering van PII',
        'De organisatie moet de mogelijkheid bieden om PII op beveiligde wijze te retourneren, over te dragen en/of '
            . 'te verwijderen. Zij moet haar beleid ook aan de klant ter beschikking stellen.'],
    'B.8.4.3' => ['Beheersmaatregelen voor de overdracht van PII',
        'De organisatie moet via een netwerk voor gegevensoverdracht verzonden PII onderwerpen aan passende '
            . 'beheersmaatregelen die ervoor zijn ontworpen om te bewerkstelligen dat de gegevens hun beoogde '
            . 'bestemming bereiken.'],
    'B.8.5.1' => ['Grondslag voor doorgifte van PII tussen rechtsgebieden',
        'De organisatie moet de klant tijdig informeren over de grondslag voor de doorgiften van PII tussen '
            . 'rechtsgebieden en over eventuele voorgenomen veranderingen in dit opzicht, zodat de klant bezwaar kan '
            . 'maken tegen dergelijke veranderingen of de overeenkomst kan beëindigen.'],
    'B.8.5.2' => ['Landen en internationale organisaties waaraan PII kan worden doorgegeven',
        'De organisatie moet de landen en internationale organisaties specificeren en documenteren waaraan PII '
            . 'mogelijk kan worden doorgegeven.'],
    'B.8.5.3' => ['Registraties van de verstrekking van PII aan derden',
        'De organisatie moet de verstrekking van PII aan derden registreren, met inbegrip van welke PII is '
            . 'verstrekt, aan wie en wanneer.'],
    'B.8.5.4' => ['Kennisgeving van verzoeken om verstrekking van PII',
        'De organisatie moet de klant kennis geven van eventuele juridisch bindende verzoeken om verstrekking van '
            . 'PII.'],
    'B.8.5.5' => ['Juridisch bindende verstrekking van PII',
        'De organisatie moet alle verzoeken om de verstrekking van PII afwijzen die niet juridisch bindend zijn, de '
            . 'desbetreffende klant raadplegen alvorens PII te verstrekken en contractueel overeengekomen verzoeken om '
            . 'het verstrekken van PII waarvoor de desbetreffende klant autorisatie heeft gegeven, accepteren.'],
    'B.8.5.6' => ['Bekendmaking van onderaannemers die worden ingezet voor het verwerken van PII',
        'De organisatie moet de klant op de hoogte stellen over de eventuele inzet van onderaannemers voor het '
            . 'verwerken van PII, voordat zij worden ingezet.'],
    'B.8.5.7' => ['Betrokkenheid van een onderaannemer voor het verwerken van PII',
        'De organisatie mag alleen een onderaannemer voor het verwerken van PII inschakelen overeenkomstig de '
            . 'overeenkomst met de klant.'],
    'B.8.5.8' => ['Verandering van onderaannemer voor het verwerken van PII',
        'De organisatie moet, indien zij algemene schriftelijke autorisatie heeft, de klant in kennis stellen van '
            . 'voorgenomen veranderingen met betrekking tot het toevoegen of vervangen van onderaannemers voor het '
            . 'verwerken van PII, en de klant daarmee de gelegenheid bieden bezwaar te maken tegen die veranderingen.'],
];

const FG_CLOUD_PRIVACY_KOPPEN = [
    'A.2' => 'A.2 Instemming en keuze',
    'A.3' => 'A.3 Doelbinding en -specificatie',
    'A.5' => 'A.5 Gegevensminimalisatie',
    'A.6' => 'A.6 Beperkingen voor gebruik, bewaren en openbaarmaking',
    'A.8' => 'A.8 Openheid, transparantie en kennisgeving',
    'A.10' => 'A.10 Verantwoording',
    'A.11' => 'A.11 Informatiebeveiliging',
    'A.12' => 'A.12 Naleving van privacy',
];

const FG_PRIVACY_A_KOPPEN = [
    'A.7.2' => 'A.7.2 Voorwaarden voor verzamelen en verwerken',
    'A.7.3' => 'A.7.3 Verplichtingen naar PII-betrokkenen',
    'A.7.4' => 'A.7.4 Privacy by design en privacy by default',
    'A.7.5' => 'A.7.5 PII delen, doorgeven en verstrekken',
];

const FG_PRIVACY_B_KOPPEN = [
    'B.8.2' => 'B.8.2 Voorwaarden voor verzamelen en verwerken',
    'B.8.3' => 'B.8.3 Verplichtingen naar PII-betrokkenen',
    'B.8.4' => 'B.8.4 Privacy by design en privacy by default',
    'B.8.5' => 'B.8.5 PII delen, doorgeven en verstrekken',
];

/**
 * De vier delen voor cloud en privacy. Een deel staat er alleen als het
 * geldt, en als geen certificaat het afdekt.
 *
 * @return list<array<string,mixed>>
 */
function fg_delen_cloud_privacy(int $stap): array
{
    $verantwoordelijke = ['verantwoordelijke', 'beide'];
    $verwerker = ['verwerker', 'beide'];

    return [
        [
            'id' => 'cld',
            'naam' => 'ISO 27017, cloud',
            'titel' => 'ISO 27017: de cloud',
            'stap' => $stap,
            'intro' => 'Deze normen gelden als de dienst vertrouwelijke gegevens in een cloud verwerkt. Ze gaan over '
                . 'de taken van de opdrachtnemer en van de klant, en over virtuele omgevingen.',
            'toon_als' => ['fg_cloud' => ['vertrouwelijk', 'pg'], 'fg_iso27017' => ['nee']],
            'afgedekt_als' => ['fg_cloud' => ['vertrouwelijk', 'pg'], 'fg_iso27017' => ['ja']],
            'certificaat' => 'fg_iso27017',
            'kader' => 'de normen uit ISO 27017',
            'lijst' => 'De normen uit ISO 27017, voor de cloud.',
            'normen' => fg_normen('fg_', FG_CLOUD_NORMEN),
        ],
        [
            'id' => 'pc',
            'naam' => 'ISO 27018, persoonsgegevens in de cloud',
            'titel' => 'ISO 27018: persoonsgegevens in de cloud',
            'stap' => $stap,
            'intro' => 'Deze normen gelden als de dienst persoonsgegevens in een cloud verwerkt. De opdrachtnemer is '
                . 'dan een verwerker in de public cloud.',
            // Zonder ISO 27017 wordt de vraag naar ISO 27018 niet gesteld, en is het antwoord leeg.
            'toon_als' => ['fg_cloud' => ['pg'], 'fg_iso27018' => ['nee', '']],
            'afgedekt_als' => ['fg_cloud' => ['pg'], 'fg_iso27018' => ['ja']],
            'certificaat' => 'fg_iso27018',
            'kader' => 'de normen uit ISO 27018',
            'lijst' => 'De normen uit ISO 27018, voor persoonsgegevens in de cloud.',
            'normen' => fg_normen('fg_pc_', FG_CLOUD_PRIVACY_NORMEN, FG_CLOUD_PRIVACY_KOPPEN),
        ],
        [
            'id' => 'pva',
            'naam' => 'ISO/IEC 27701 bijlage A, verantwoordelijke',
            'titel' => 'ISO/IEC 27701: de verwerkingsverantwoordelijke',
            'stap' => $stap,
            'intro' => 'Deze normen uit bijlage A gelden als de opdrachtnemer zelf bepaalt waarom en hoe hij '
                . 'persoonsgegevens verwerkt. PII is hetzelfde als persoonsgegevens.',
            'toon_als' => ['fg_rol' => $verantwoordelijke, 'fg_iso27701' => ['nee']],
            'afgedekt_als' => ['fg_rol' => $verantwoordelijke, 'fg_iso27701' => ['ja']],
            'certificaat' => 'fg_iso27701',
            'kader' => 'de normen uit bijlage A',
            'lijst' => 'De normen uit bijlage A van ISO/IEC 27701, voor de verwerkingsverantwoordelijke.',
            'normen' => fg_normen('fg_pa_', FG_PRIVACY_A_NORMEN, FG_PRIVACY_A_KOPPEN),
        ],
        [
            'id' => 'pvb',
            'naam' => 'ISO/IEC 27701 bijlage B, verwerker',
            'titel' => 'ISO/IEC 27701: de verwerker',
            'stap' => $stap,
            'intro' => 'Deze normen uit bijlage B gelden als de opdrachtnemer persoonsgegevens verwerkt in opdracht '
                . 'van de opdrachtgever. De klant is de opdrachtgever. PII is hetzelfde als persoonsgegevens.',
            'toon_als' => ['fg_rol' => $verwerker, 'fg_iso27701' => ['nee']],
            'afgedekt_als' => ['fg_rol' => $verwerker, 'fg_iso27701' => ['ja']],
            'certificaat' => 'fg_iso27701',
            'kader' => 'de normen uit bijlage B',
            'lijst' => 'De normen uit bijlage B van ISO/IEC 27701, voor de verwerker.',
            'normen' => fg_normen('fg_pb_', FG_PRIVACY_B_NORMEN, FG_PRIVACY_B_KOPPEN),
        ],
    ];
}
