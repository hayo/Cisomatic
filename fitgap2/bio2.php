<?php

/**
 * De fit/gap voor de BIO2 en de Cyberbeveiligingswet: de maatregelen uit
 * ISO 27002, de overheidsmaatregelen uit de BIO2, de eisen aan de keten en
 * het cloudbeleid. De vragen, de uitkomst en het document deelt dit formulier
 * met de fit/gap voor de BIO 1, in ../fitgap/verklaring.php.
 *
 * De BIO2 kent geen basisbeveiligingsniveaus meer. Elke maatregel geldt. De
 * omschrijvingen bij de maatregelen uit ISO 27002:2022 zijn een eigen
 * samenvatting in gewone taal; de tekst van de norm zelf is niet openbaar.
 * De overheidsmaatregelen dragen het nummer uit de BIO2, versie 1.3.
 */

declare(strict_types=1);

/** De maatregelen uit ISO 27002:2022: nummer => [titel, omschrijving]. */
const FG2_MAATREGELEN = [
    '5.1' => ['Beleidsregels voor informatiebeveiliging',
        'Er is een beleid voor informatiebeveiliging, met regels per onderwerp. De directie keurt het goed. '
            . 'Medewerkers en betrokken partijen kennen het. Het beleid wordt regelmatig herzien, en bij grote '
            . 'veranderingen.'],
    '5.2' => ['Rollen en verantwoordelijkheden bij informatiebeveiliging',
        'De taken en verantwoordelijkheden voor informatiebeveiliging zijn vastgelegd en verdeeld.'],
    '5.3' => ['Functiescheiding',
        'Taken en verantwoordelijkheden die met elkaar botsen, zijn gescheiden. Zo kan niemand in zijn eentje iets '
            . 'misbruiken of ongemerkt veranderen.'],
    '5.4' => ['Managementverantwoordelijkheden',
        'Het management eist van alle medewerkers dat ze het beleid en de procedures voor informatiebeveiliging '
            . 'volgen.'],
    '5.5' => ['Contact met overheidsinstanties',
        'Er is contact met de overheidsinstanties die ertoe doen, bijvoorbeeld voor het melden van incidenten.'],
    '5.6' => ['Contact met speciale belangengroepen',
        'Er is contact met vakgroepen en fora over beveiliging, om kennis te delen en bij te blijven.'],
    '5.7' => ['Informatie en analyses over dreigingen',
        'De opdrachtnemer verzamelt en analyseert informatie over dreigingen, en gebruikt die om maatregelen te '
            . 'nemen.'],
    '5.8' => ['Informatiebeveiliging in projectmanagement',
        'Informatiebeveiliging is een vast onderdeel van elk project.'],
    '5.9' => ['Inventarisatie van informatie en andere gerelateerde bedrijfsmiddelen',
        'Er is een actueel overzicht van de informatie en de bedrijfsmiddelen, zoals systemen en apparaten. Elk '
            . 'middel heeft een eigenaar.'],
    '5.10' => ['Aanvaardbaar gebruik van informatie en andere gerelateerde bedrijfsmiddelen',
        'Er zijn regels voor het gebruik van informatie en bedrijfsmiddelen. Ze zijn vastgelegd en worden '
            . 'toegepast.'],
    '5.11' => ['Retourneren van bedrijfsmiddelen',
        'Wie vertrekt of een andere functie krijgt, levert de bedrijfsmiddelen in die hij niet meer nodig heeft.'],
    '5.12' => ['Classificeren van informatie',
        'Informatie is ingedeeld naar hoe gevoelig en belangrijk die is, met de eisen voor beschikbaarheid, '
            . 'integriteit en vertrouwelijkheid.'],
    '5.13' => ['Labelen van informatie',
        'Informatie krijgt een label dat past bij de classificatie. Daar zijn procedures voor.'],
    '5.14' => ['Overdragen van informatie',
        'Er zijn regels, procedures of afspraken om informatie veilig over te dragen, binnen de organisatie en met '
            . 'anderen. Dat geldt voor elke manier van overdragen.'],
    '5.15' => ['Toegangsbeveiliging',
        'Er zijn regels voor fysieke en logische toegang tot informatie en systemen. Ze volgen uit de eisen van het '
            . 'werk en van de beveiliging.'],
    '5.16' => ['Identiteitsbeheer',
        'Elke identiteit, van mensen en van systemen, wordt beheerd tijdens de hele levensduur.'],
    '5.17' => ['Authenticatie-informatie',
        'Wachtwoorden en andere authenticatie-informatie worden beheerst uitgegeven en beheerd. Gebruikers weten hoe '
            . 'ze er veilig mee omgaan.'],
    '5.18' => ['Toegangsrechten',
        'Toegangsrechten worden volgens het beleid toegekend, beoordeeld, aangepast en ingetrokken.'],
    '5.19' => ['Informatiebeveiliging in leveranciersrelaties',
        'Er zijn processen om de risico’s te beheersen van de producten en diensten van eigen leveranciers.'],
    '5.20' => ['Adresseren van informatiebeveiliging in leveranciersovereenkomsten',
        'Met elke leverancier zijn de eisen voor informatiebeveiliging afgesproken, passend bij het soort '
            . 'leverancier.'],
    '5.21' => ['Beheren van informatiebeveiliging in de ICT-toeleveringsketen',
        'Er zijn processen om de risico’s in de keten van ICT-producten en -diensten te beheersen.'],
    '5.22' => ['Monitoren, beoordelen en beheren van wijzigingen in leveranciersdiensten',
        'De opdrachtnemer controleert regelmatig hoe zijn leveranciers werken en beveiligen. Wijzigingen in hun '
            . 'diensten worden beheerst.'],
    '5.23' => ['Informatiebeveiliging voor het gebruik van clouddiensten',
        'Er zijn processen voor het kiezen, gebruiken, beheren en stoppen van clouddiensten, passend bij de eisen '
            . 'voor beveiliging.'],
    '5.24' => ['Plannen en voorbereiden van het beheer van informatiebeveiligingsincidenten',
        'Er is een plan voor het omgaan met incidenten, met processen, rollen en verantwoordelijkheden.'],
    '5.25' => ['Beoordelen van en besluiten over informatiebeveiligingsgebeurtenissen',
        'Gebeurtenissen worden beoordeeld. Daarna wordt besloten of het een incident is.'],
    '5.26' => ['Reageren op informatiebeveiligingsincidenten',
        'Op een incident wordt gereageerd volgens vastgelegde procedures.'],
    '5.27' => ['Leren van informatiebeveiligingsincidenten',
        'Wat een incident leert, wordt gebruikt om de maatregelen te verbeteren.'],
    '5.28' => ['Verzamelen van bewijsmateriaal',
        'Er zijn procedures om bewijs bij een incident te herkennen, te verzamelen en te bewaren.'],
    '5.29' => ['Informatiebeveiliging tijdens een verstoring',
        'Ook tijdens een verstoring of een crisis blijft de informatie goed beveiligd. Dat is van tevoren gepland.'],
    '5.30' => ['ICT-gereedheid voor bedrijfscontinuïteit',
        'De ICT is voorbereid op een verstoring. Dat is gepland, ingericht, onderhouden en getest, op basis van de '
            . 'doelen voor continuïteit.'],
    '5.31' => ['Wettelijke, statutaire, regelgevende en contractuele eisen',
        'De eisen uit wetten, regels en contracten zijn bekend, vastgelegd en actueel. Er staat ook hoe de '
            . 'opdrachtnemer eraan voldoet.'],
    '5.32' => ['Intellectuele-eigendomsrechten',
        'Er zijn procedures om intellectueel eigendom te beschermen, bijvoorbeeld bij het gebruik van licenties.'],
    '5.33' => ['Beschermen van registraties',
        'Registraties zijn beschermd tegen verlies, vernietiging, vervalsing en onbevoegde toegang.'],
    '5.34' => ['Privacy en bescherming van persoonsgegevens',
        'De opdrachtnemer kent de eisen voor privacy en de bescherming van persoonsgegevens, en voldoet eraan.'],
    '5.35' => ['Onafhankelijke beoordeling van informatiebeveiliging',
        'De aanpak van informatiebeveiliging wordt onafhankelijk beoordeeld, met vaste tussenpozen en bij grote '
            . 'veranderingen.'],
    '5.36' => ['Naleving van beleid, regels en normen voor informatiebeveiliging',
        'Er wordt regelmatig gecontroleerd of het beleid, de regels en de normen worden nageleefd.'],
    '5.37' => ['Gedocumenteerde bedieningsprocedures',
        'De procedures voor het bedienen van systemen zijn vastgelegd, en beschikbaar voor wie ze nodig heeft.'],

    '6.1' => ['Screening',
        'De achtergrond van een nieuwe medewerker wordt gecontroleerd voordat hij begint, en waar nodig daarna '
            . 'opnieuw. De screening past bij de wet, de functie en de risico’s.'],
    '6.2' => ['Arbeidsovereenkomst',
        'De arbeidsovereenkomst noemt de verantwoordelijkheden voor informatiebeveiliging.'],
    '6.3' => ['Bewustwording, opleiding en training op het gebied van informatiebeveiliging',
        'Medewerkers krijgen voorlichting en training over informatiebeveiliging die past bij hun werk, en worden '
            . 'regelmatig bijgepraat.'],
    '6.4' => ['Disciplinaire procedure',
        'Er is een vastgelegde disciplinaire procedure voor wie de regels overtreedt. Medewerkers kennen die.'],
    '6.5' => ['Verantwoordelijkheden na beëindiging of wijziging van het dienstverband',
        'Plichten die na vertrek of een andere functie blijven gelden, zijn vastgelegd en bekend bij de medewerker.'],
    '6.6' => ['Vertrouwelijkheids- of geheimhoudingsovereenkomsten',
        'Medewerkers en andere betrokkenen tekenen een geheimhoudingsverklaring. De eisen daarvoor worden '
            . 'regelmatig herzien.'],
    '6.7' => ['Werken op afstand',
        'Er zijn maatregelen om informatie te beschermen bij werken buiten kantoor.'],
    '6.8' => ['Melden van informatiebeveiligingsgebeurtenissen',
        'Medewerkers kunnen een gebeurtenis of een zwakke plek snel melden, via een vaste weg.'],

    '7.1' => ['Fysieke beveiligingszones',
        'Er zijn beveiligde zones om ruimtes met informatie en systemen te beschermen.'],
    '7.2' => ['Fysieke toegangsbeveiliging',
        'Alleen wie bevoegd is, komt in een beveiligde ruimte. Daar zijn toegangscontroles voor.'],
    '7.3' => ['Beveiligen van kantoren, ruimten en faciliteiten',
        'Kantoren, ruimtes en voorzieningen zijn fysiek beveiligd.'],
    '7.4' => ['Monitoren van de fysieke beveiliging',
        'Gebouwen en terreinen worden bewaakt tegen onbevoegde toegang.'],
    '7.5' => ['Beschermen tegen fysieke en omgevingsdreigingen',
        'Er is bescherming tegen bijvoorbeeld brand, water, stroomuitval en aanvallen van buitenaf.'],
    '7.6' => ['Werken in beveiligde gebieden',
        'Er zijn regels voor het werken in beveiligde ruimtes.'],
    '7.7' => ['Clear desk en clear screen',
        'Er zijn regels voor een leeg bureau en een vergrendeld scherm.'],
    '7.8' => ['Plaatsen en beschermen van apparatuur',
        'Apparatuur staat op een veilige plek, en is beschermd.'],
    '7.9' => ['Beveiligen van bedrijfsmiddelen buiten het terrein',
        'Bedrijfsmiddelen buiten het kantoor zijn beschermd.'],
    '7.10' => ['Opslagmedia',
        'Opslagmedia worden beheerd tijdens hun hele levensduur: gebruik, vervoer en vernietiging, passend bij de '
            . 'classificatie.'],
    '7.11' => ['Nutsvoorzieningen',
        'Systemen zijn beschermd tegen uitval van stroom en andere voorzieningen.'],
    '7.12' => ['Beveiligen van bekabeling',
        'Kabels voor stroom en data zijn beschermd tegen afluisteren, storing en schade.'],
    '7.13' => ['Onderhoud van apparatuur',
        'Apparatuur wordt goed onderhouden, zodat de informatie beschikbaar, juist en vertrouwelijk blijft.'],
    '7.14' => ['Veilig verwijderen of hergebruiken van apparatuur',
        'Voordat apparatuur weggaat of opnieuw wordt gebruikt, zijn gevoelige gegevens en software met een licentie '
            . 'veilig verwijderd of overschreven.'],

    '8.1' => ['Gebruikersapparatuur',
        'Informatie op laptops, telefoons en andere apparaten van gebruikers is beschermd.'],
    '8.2' => ['Speciale toegangsrechten',
        'Beheerrechten en andere speciale rechten zijn beperkt, en worden beheerst.'],
    '8.3' => ['Beperking toegang tot informatie',
        'De toegang tot informatie en functies is beperkt, volgens het beleid voor toegang.'],
    '8.4' => ['Toegangsbeveiliging op broncode',
        'Wie broncode, ontwikkeltools en softwarebibliotheken mag lezen of wijzigen, is goed geregeld.'],
    '8.5' => ['Beveiligde authenticatie',
        'Inloggen gebeurt op een veilige manier, passend bij de gevoeligheid van de informatie.'],
    '8.6' => ['Capaciteitsbeheer',
        'Het gebruik van middelen wordt gevolgd en bijgestuurd, nu en voor later.'],
    '8.7' => ['Bescherming tegen malware',
        'Er is bescherming tegen malware, en gebruikers weten waar ze op moeten letten.'],
    '8.8' => ['Beheer van technische kwetsbaarheden',
        'Informatie over kwetsbaarheden wordt op tijd verzameld en beoordeeld. Daarna volgen passende maatregelen.'],
    '8.9' => ['Configuratiebeheer',
        'Configuraties, ook die voor beveiliging, zijn vastgelegd, ingericht en gecontroleerd, en worden beheerd.'],
    '8.10' => ['Wissen van informatie',
        'Informatie die niet meer nodig is, wordt gewist. Ook op systemen en apparaten.'],
    '8.11' => ['Maskeren van gegevens',
        'Gegevens worden gemaskeerd waar het beleid dat vraagt. Bijvoorbeeld persoonsgegevens in een testomgeving.'],
    '8.12' => ['Voorkomen van gegevenslekken',
        'Er zijn maatregelen tegen het lekken van gevoelige informatie, op systemen, netwerken en apparaten.'],
    '8.13' => ['Back-up van informatie',
        'Er worden reservekopieën gemaakt van informatie, software en systemen. Het terugzetten wordt regelmatig '
            . 'getest.'],
    '8.14' => ['Redundantie van informatieverwerkende faciliteiten',
        'Systemen zijn dubbel uitgevoerd waar de beschikbaarheid dat vraagt.'],
    '8.15' => ['Logging',
        'Activiteiten, uitzonderingen, fouten en andere gebeurtenissen worden vastgelegd. De logbestanden zijn '
            . 'beschermd, en worden geanalyseerd.'],
    '8.16' => ['Monitoren van activiteiten',
        'Netwerken, systemen en toepassingen worden bewaakt op afwijkend gedrag. Bij een mogelijk incident volgt '
            . 'actie.'],
    '8.17' => ['Kloksynchronisatie',
        'De klokken van de systemen lopen gelijk met een vaste tijdbron.'],
    '8.18' => ['Gebruik van speciale systeemhulpmiddelen',
        'Hulpmiddelen die de beveiliging kunnen omzeilen, zijn beperkt, en worden streng gecontroleerd.'],
    '8.19' => ['Installeren van software op operationele systemen',
        'Er zijn procedures om software veilig op productiesystemen te installeren.'],
    '8.20' => ['Beveiliging van netwerken',
        'Netwerken en netwerkapparaten zijn beveiligd, en worden beheerd.'],
    '8.21' => ['Beveiliging van netwerkdiensten',
        'De beveiliging en het niveau van netwerkdiensten zijn bepaald en ingericht, en worden gevolgd.'],
    '8.22' => ['Scheiding van netwerken',
        'Groepen van diensten, gebruikers en systemen zitten in gescheiden netwerken.'],
    '8.23' => ['Toepassen van webfilters',
        'De toegang tot externe websites wordt beheerst, om schadelijke inhoud te weren.'],
    '8.24' => ['Gebruik van cryptografie',
        'Er zijn regels voor versleuteling en voor het beheer van sleutels. Die worden toegepast.'],
    '8.25' => ['Beveiligen tijdens de ontwikkelcyclus',
        'Er zijn regels voor het veilig ontwikkelen van software en systemen.'],
    '8.26' => ['Beveiligingseisen voor toepassingen',
        'Bij het ontwikkelen of kopen van een toepassing zijn de eisen voor beveiliging bepaald en vastgelegd.'],
    '8.27' => ['Veilige systeemarchitectuur en technische uitgangspunten',
        'Er zijn uitgangspunten voor het ontwerpen van veilige systemen. Ze zijn vastgelegd, worden onderhouden en '
            . 'worden toegepast.'],
    '8.28' => ['Veilig coderen',
        'Bij het schrijven van software gelden regels voor veilig programmeren.'],
    '8.29' => ['Testen van de beveiliging tijdens ontwikkeling en acceptatie',
        'De beveiliging wordt getest tijdens het ontwikkelen, en bij de acceptatie.'],
    '8.30' => ['Uitbestede systeemontwikkeling',
        'Uitbestede ontwikkeling wordt aangestuurd, gevolgd en beoordeeld.'],
    '8.31' => ['Scheiding van ontwikkel-, test- en productieomgevingen',
        'Ontwikkel-, test- en productieomgevingen zijn gescheiden en beveiligd.'],
    '8.32' => ['Wijzigingsbeheer',
        'Wijzigingen in systemen en voorzieningen volgen een vaste procedure.'],
    '8.33' => ['Testgegevens',
        'Testgegevens zijn zorgvuldig gekozen, en worden beschermd en beheerd.'],
    '8.34' => ['Bescherming van informatiesystemen tijdens audits',
        'Audits en tests op productiesystemen zijn gepland en afgesproken, zodat ze het werk zo min mogelijk '
            . 'verstoren.'],
];

/**
 * De maatregelen per deel: id => [eerste, laatste, naam, uitleg]. Zo blijft
 * een stap kort genoeg om in één keer in te vullen.
 */
const FG2_DELEN = [
    'iso_beleid' => ['5.1', '5.8', 'Beleid, rollen en dreigingen',
        'Organisatorische maatregelen: het beleid, wie waarvoor verantwoordelijk is, en zicht op dreigingen.'],
    'iso_informatie' => ['5.9', '5.14', 'Informatie en bedrijfsmiddelen',
        'Wat er is, hoe informatie is ingedeeld en gelabeld, en hoe die veilig wordt overgedragen.'],
    'iso_toegang' => ['5.15', '5.18', 'Identiteit en toegang', 'Wie waar bij kan, en hoe dat geregeld is.'],
    'iso_leveranciers' => ['5.19', '5.23', 'Leveranciers en cloud',
        'De eigen leveranciers van de opdrachtnemer, en de clouddiensten die hij gebruikt.'],
    'iso_incidenten' => ['5.24', '5.30', 'Incidenten en continuïteit',
        'Incidenten melden, oplossen en ervan leren, en de dienst laten doorlopen bij een verstoring.'],
    'iso_naleving' => ['5.31', '5.37', 'Wetten, privacy en naleving',
        'Wetten, contracten, privacy, controles op de beveiliging, en vastgelegde procedures.'],
    'iso_mensen' => ['6.1', '6.8', 'Mensen',
        'Mensgerichte maatregelen: medewerkers voor, tijdens en na het dienstverband.'],
    'iso_fysiek' => ['7.1', '7.14', 'Fysieke beveiliging', 'Gebouwen, ruimtes, apparatuur en opslagmedia.'],
    'iso_apparaten' => ['8.1', '8.8', 'Apparaten, toegang en kwetsbaarheden',
        'Technologische maatregelen: apparaten, rechten, inloggen, malware en kwetsbaarheden.'],
    'iso_gegevens' => ['8.9', '8.19', 'Configuratie, gegevens en logging',
        'Instellingen, het wissen en beschermen van gegevens, back-ups, logging en bewaking.'],
    'iso_netwerk' => ['8.20', '8.24', 'Netwerken en versleuteling', 'Netwerken, webfilters en cryptografie.'],
    'iso_ontwikkeling' => ['8.25', '8.34', 'Ontwikkelen en testen',
        'Veilig ontwikkelen, testen en wijzigen. Bij een standaardproduct zonder maatwerk mogen 8.27 tot en met 8.30 '
            . 'op ‘niet van toepassing’.'],
];

/** Deze maatregelen mogen bij een standaardproduct (COTS) zonder maatwerk niet van toepassing zijn. */
const FG2_COTS = ['8.27', '8.28', '8.29', '8.30'];

const FG2_INTERNET = 'Alleen als niets van de dienst via internet bereikbaar is.';

/**
 * De overheidsmaatregelen uit de BIO2 die de opdrachtnemer raken. Een
 * certificaat voor ISO 27001 dekt ze niet af. Per maatregel: nummer, titel,
 * tekst, eventueel wanneer hij niet van toepassing is, en eventueel de sleutel
 * van het antwoord. Die staat er bij een nummer dat is verbeterd: de sleutel
 * blijft die van het oude nummer, zodat een eerder antwoordbestand blijft
 * inladen.
 */
const FG2_OVERHEID = [
    ['5.14.01', 'Internetstandaarden', 'De dienst voldoet aan de verplichte internetstandaarden van Forum '
        . 'Standaardisatie. De opdrachtnemer meet dat met internet.nl.', FG2_INTERNET],
    ['5.14.02 en 5.14.05', 'Websites', 'Een website van de dienst gebruikt bij gevoelige gegevens ten minste een '
        . 'OV-certificaat. De opdrachtnemer helpt de opdrachtgever om de website te registreren in het Register '
        . 'Internetdomeinen Overheid, en dat elk half jaar actueel te houden.',
        'Alleen als de dienst geen website heeft.'],
    ['5.14.04', 'Overzicht van wat via internet bereikbaar is', 'De opdrachtnemer geeft de opdrachtgever een actueel '
        . 'overzicht van wat er van de dienst via internet bereikbaar is: systemen, IP-adressen en API’s.',
        FG2_INTERNET],
    ['5.17.01', 'Multifactorauthenticatie', 'Multifactorauthenticatie staat aan voor de werkomgeving, voor accounts '
        . 'die via internet te bereiken zijn, en voor beheeraccounts.', ''],
    ['5.18.01, 5.18.02 en 8.02.01', 'Rechten beoordelen', 'De rechten van beheerders worden elk kwartaal '
        . 'beoordeeld, alle andere rechten elk jaar. De opdrachtnemer houdt in de gaten wie accounts met extra '
        . 'rechten maakt of wijzigt.', '', 'fg_om_5_18'],
    ['5.24.08', 'Kwetsbaarheden melden', 'Iedereen kan een kwetsbaarheid in de dienst melden. Daarvoor is een '
        . 'procedure voor Coordinated Vulnerability Disclosure.', '', 'fg_om_5_26_01'],
    ['5.28.01', 'Bewijs bewaren', 'Alles over een beveiligingsincident, zoals logbestanden, wordt ten minste drie jaar '
        . 'bewaard.', ''],
    ['5.30.01', 'Continuïteitsplan testen', 'Het continuïteitsplan voor de dienst wordt elk jaar getest.', '',
        'fg_om_5_29_01'],
    ['6.01.01', 'Screening', 'Medewerkers worden gescreend volgens het screeningsbeleid van de opdrachtgever. Wie dat '
        . 'beleid aanwijst, heeft een VOG. Werkt iemand met staatsgeheime informatie, dan is dat een vertrouwensfunctie '
        . 'met een veiligheidsonderzoek.', ''],
    ['6.03.02', 'Training', 'Iedereen die aan de dienst werkt, volgt binnen drie maanden na de start aantoonbaar een '
        . 'training over informatiebeveiliging.', '', 'fg_om_6_03_03'],
    ['8.08.01', 'Kwetsbaarheden verhelpen', 'Een kwetsbaarheid met een hoge kans op misbruik en een hoge schade wordt '
        . 'binnen een week verholpen of afgeschermd.', ''],
    ['8.08.04 en 8.08.05', 'Pentests', 'Bij elke nieuwe versie is er een pentest, waar het kan geautomatiseerd. Een '
        . 'bevinding met een hoog risico die niet anders op te lossen is, gaat niet in productie. Daarnaast wordt de '
        . 'dienst elk jaar op kwetsbaarheden getest.', FG2_INTERNET, 'fg_om_8_08_03'],
    ['8.13.01 tot en met 8.13.04', 'Back-ups en herstel', 'Reservekopieën zijn beschermd tegen ransomware, en '
        . 'staan op een andere locatie. Het herstel wordt elk jaar getest. De hersteltijd (RTO) en het grootste '
        . 'verlies aan gegevens (RPO) zijn afgesproken met de opdrachtgever.', '', 'fg_om_8_13'],
    ['8.15.01', 'Logging', 'Per gebeurtenis legt de dienst vast wat er gebeurde, waarop, met welk resultaat, vanaf '
        . 'waar, door wie en wanneer.', ''],
    ['8.16.03', 'Detectie en respons', 'De dienst is aangesloten op detectie en respons, bijvoorbeeld een SOC met een '
        . 'SIEM.', '', 'fg_om_8_16_02'],
    ['8.21.04 en 8.24.04', 'Versleuteling', 'Versleuteling gebeurt waar het kan met middelen die een positief '
        . 'inzetadvies hebben van het NBV van de AIVD. De sterkte volgt de adviezen van het NCSC en het NBV.',
        'Alleen als er geen gerubriceerde informatie in de dienst komt, en de vertrouwelijkheid niet hoog is.'],
];

/** De eisen aan de keten: uit de BIO2, en bij de Cyberbeveiligingswet. Per eis: sleutel, nummer, titel, tekst. */
const FG2_KETEN = [
    ['fg_om_5_20_03', '5.20.03 en 5.20.04', 'Onafhankelijk bewijs', 'De opdrachtnemer toont elk jaar met een '
        . 'onafhankelijk onderzoek aan dat hij aan de eisen voldoet. Het onderzoek gaat over de hele dienst, ook over '
        . 'zijn eigen leveranciers. De opdrachtgever mag zelf een audit laten doen.'],
    ['fg_om_5_20_05', '5.20.05', 'Snel melden', 'De opdrachtnemer meldt incidenten, datalekken en kwetsbaarheden die '
        . 'de dienst raken zo snel, dat de opdrachtgever zelf binnen 24 uur kan melden. De termijn is enkele uren.'],
    ['fg_om_5_20_06', '5.20.06', 'Exit', 'Er is een uitgewerkte exitstrategie. Het contract noemt de situaties waarin '
        . 'het ontbonden kan worden.'],
    ['fg_om_5_21_02', '5.21.02 tot en met 5.21.04', 'Eigen leveranciers', 'De opdrachtnemer geeft inzicht in zijn '
        . 'eigen leveranciers voor deze dienst, en in de risico’s daarin. Hij legt hun dezelfde eisen op, en meldt '
        . 'wijzigingen in die keten.'],
];

const FG2_CBW = [
    ['fg_cbw_melding', 'Cbw 1', 'Informatie voor de melding', 'Bij een significant incident levert de opdrachtnemer '
        . 'binnen 72 uur wat de opdrachtgever nodig heeft voor de melding: een eerste beoordeling van de ernst en de '
        . 'gevolgen, en waar het kan de kenmerken van de aanval (Cbw artikel 27).'],
    ['fg_cbw_eindverslag', 'Cbw 2', 'Tussenstand en eindverslag', 'Op verzoek geeft de opdrachtnemer tussendoor de '
        . 'stand van zaken. Uiterlijk een maand na de melding levert hij wat nodig is voor het eindverslag: de '
        . 'oorzaak, de genomen maatregelen en de gevolgen (Cbw artikel 28 en 29).'],
    ['fg_cbw_keten', 'Cbw 3', 'Kwaliteit en veilig ontwikkelen', 'De opdrachtnemer geeft inzicht in de kwaliteit en de '
        . 'beveiliging van zijn producten, en in hoe hij veilig ontwikkelt. De opdrachtgever moet die risico’s in de '
        . 'keten meewegen.'],
    ['fg_cbw_toezicht', 'Cbw 4', 'Medewerking aan toezicht', 'De opdrachtnemer geeft de opdrachtgever de informatie die '
        . 'nodig is om de toezichthouder te laten zien dat de keten beveiligd is. Hij werkt mee aan een onderzoek na '
        . 'een incident.'],
];

/**
 * De zorgplicht uit artikel 21, derde lid, van de Cyberbeveiligingswet, de
 * onderdelen a tot en met j, met de maatregelen uit de BIO2 die erbij horen. De koppeling komt uit
 * tabel 4 van de generieke baselinetoets BIO2, versie 3.2.
 */
const FG2_ZORGPLICHT = [
    ['a', 'Beleid voor risicoanalyse en beveiliging', ['5.1', '5.2', '5.7']],
    ['b', 'Incidenten behandelen', ['5.24', '5.25', '5.26', '5.27', '5.28', '8.15', '8.16']],
    ['c', 'Continuïteit, back-ups, herstel en crisisbeheer', ['5.29', '5.30', '7.5', '7.11', '8.13', '8.14']],
    ['d', 'De toeleveringsketen', ['5.19', '5.20', '5.21', '5.22', '5.23', '5.31']],
    ['e', 'Verwerven, ontwikkelen en onderhouden, met kwetsbaarheden', ['5.6', '5.7', '5.14', '5.20', '7.1', '8.4', '8.8', '8.9', '8.11',
        '8.12', '8.20', '8.21', '8.25', '8.26', '8.27', '8.28', '8.29', '8.30', '8.31', '8.32', '8.33']],
    ['f', 'Beoordelen of de maatregelen werken', ['5.35', '5.36', '8.1', '8.22', '8.23']],
    ['g', 'Cyberhygiëne en opleiding', ['6.3', '6.8', '8.7']],
    ['h', 'Cryptografie en encryptie', ['8.24']],
    ['i', 'Personeel, toegang en beheer van assets', ['5.4', '5.9', '5.10', '5.11', '5.15', '5.16', '5.17', '5.18', '6.1', '6.2',
        '6.4', '6.5', '6.6', '6.7', '7.3', '7.4', '7.5', '7.6', '7.7', '7.8', '7.9', '7.10', '7.12', '7.13', '7.14',
        '8.1', '8.2', '8.3', '8.6', '8.10', '8.17', '8.18', '8.19']],
    ['j', 'Multifactorauthenticatie en beveiligde communicatie', ['5.14', '8.5']],
];

/**
 * Het rijksbrede cloudbeleid van 2026. Per eis: sleutel, nummer, titel,
 * tekst, en of hij alleen bij vertrouwelijke gegevens geldt.
 */
const FG2_CLOUDBELEID = [
    ['fg_cb_locatie', 'Cloud 1', 'Data in de EER of Zwitserland', 'Alle informatie wordt binnen de EER of Zwitserland '
        . 'opgeslagen en verwerkt. Dat geldt ook voor reservekopieën, en voor hulp op afstand.', false],
    ['fg_cb_landen', 'Cloud 2', 'Geen leveranciers uit uitgesloten landen', 'De dienst en de onderaannemers komen niet '
        . 'uit een land met een actief cyberprogramma tegen Nederlandse belangen: Rusland, China, Iran en Noord-Korea.',
        false],
    ['fg_cb_versleuteling', 'Cloud 3', 'Versleuteling', 'Data die niet openbaar is, wordt versleuteld bij opslag en bij '
        . 'verzending.', true],
    ['fg_cb_sleutels', 'Cloud 4', 'Eigen sleutels', 'De opdrachtgever kan de sleutels zelf beheren, of laten beheren '
        . 'door een andere partij die daarvoor gecertificeerd is. Niet door de cloudleverancier.', true],
    ['fg_cb_jurisdictie', 'Cloud 5', 'Recht van buiten de EU', 'De opdrachtnemer maakt bekend of een land buiten de EU '
        . 'of de EER hem iets kan opleggen, bijvoorbeeld via een moederbedrijf. Hij meldt het als dat verandert.',
        false],
];

/** @return list<array<string,mixed>> */
function fg2_secties(): array
{
    $delen = fg2_delen();
    $janee = [
        'ja' => ['label' => 'Ja'],
        'nee' => ['label' => 'Nee'],
        'onbekend' => ['label' => 'Weet ik niet'],
    ];

    return [
        fg_sectie_dienst(),
        fg_sectie_reikwijdte(
            'Wetten, persoonsgegevens en cloud',
            'De BIO2 kent geen beveiligingsniveaus meer: elke maatregel geldt. Wat er bij komt, hangt af van de '
                . 'Cyberbeveiligingswet, van persoonsgegevens en van de cloud.',
            [
                [
                    'key' => 'fg_cbw',
                    'type' => 'radio',
                    'label' => 'Valt de opdrachtgever onder de Cyberbeveiligingswet?',
                    'hint' => 'Dan gelden er extra eisen aan de keten, zoals het melden van incidenten.',
                    'verplicht' => true,
                    'opties' => [
                        'ja' => ['label' => 'Ja', 'hint' => 'Ministeries, hun diensten en agentschappen zijn essentiële '
                            . 'entiteiten.'],
                        'nee' => ['label' => 'Nee'],
                        'onbekend' => ['label' => 'Weet ik niet', 'hint' => 'Dan gelden de eisen toch. Dat is de '
                            . 'voorzichtige keuze.'],
                    ],
                ],
                [
                    'key' => 'fg_cbw_zelf',
                    'type' => 'radio',
                    'label' => 'Valt de opdrachtnemer zelf onder de Cyberbeveiligingswet of NIS2?',
                    'hint' => 'Bijvoorbeeld een clouddienst, een datacenter, of een bedrijf dat ICT voor anderen beheert. '
                        . 'Dat vervangt de eisen in deze verklaring niet.',
                    'verplicht' => true,
                    'opties' => $janee,
                ],
            ],
            []
        ),
        fg_sectie_certificaten(
            'Dan hoef je de maatregelen uit ISO 27002 niet in te vullen. De overheidsmaatregelen uit de BIO2 vul je wel '
                . 'in, want die vallen niet onder ISO 27001.',
            [
                ['tekst' => 'De maatregelen uit ISO 27002.', 'toon_als' => ['fg_iso27001' => ['nee']]],
                ['tekst' => 'Geen maatregelen uit ISO 27002. Het certificaat voor ISO 27001 dekt ze af.',
                    'toon_als' => ['fg_iso27001' => ['ja']]],
            ],
            $delen
        ),
        ...array_map('fg_sectie_deel', $delen),
        fg_sectie_verklaring(5, $delen),
    ];
}

/**
 * De delen: de maatregelen uit ISO 27002, de overheidsmaatregelen, de keten,
 * het cloudbeleid, en daarna cloud en privacy.
 *
 * @return list<array<string,mixed>>
 */
function fg2_delen(): array
{
    $delen = [];

    foreach (FG2_DELEN as $id => [$van, $tot, $naam, $intro]) {
        $normen = [];
        foreach (FG2_MAATREGELEN as $nummer => [$titel, $tekst]) {
            $nummer = (string) $nummer;
            if (version_compare($nummer, $van, '>=') && version_compare($nummer, $tot, '<=')) {
                $normen[] = [
                    'key' => fg_sleutel('fg_iso_', $nummer),
                    'nummer' => $nummer,
                    'titel' => $titel,
                    'tekst' => $tekst,
                    'nvt' => in_array($nummer, FG2_COTS, true)
                        ? 'Alleen bij een standaardproduct (COTS) zonder maatwerk. Bij maatwerk geldt de maatregel wel.'
                        : '',
                ];
            }
        }

        $delen[] = [
            'id' => $id,
            'naam' => $van . '–' . $tot . ' ' . $naam,
            'titel' => $van . '–' . $tot . ' ' . $naam,
            'stap' => 2,
            'intro' => $intro,
            'toon_als' => ['fg_iso27001' => ['nee']],
            'afgedekt_als' => ['fg_iso27001' => ['ja']],
            'certificaat' => 'fg_iso27001',
            'kader' => 'de maatregelen uit ISO 27002',
            'normen' => $normen,
        ];
    }

    $eis = static fn (string $key, string $nummer, string $titel, string $tekst, array $extra = []): array => [
        'key' => $key,
        'nummer' => $nummer,
        'kort' => fg2_kort($nummer),
        'titel' => $titel,
        'tekst' => $tekst,
    ] + $extra;

    return [
        ...$delen,
        [
            'id' => 'overheid',
            'naam' => 'Overheidsmaatregelen uit de BIO2',
            'titel' => 'Overheidsmaatregelen uit de BIO2',
            'stap' => 3,
            'intro' => 'Deze maatregelen komen uit de BIO2 zelf, bovenop ISO 27002. Een certificaat voor ISO 27001 dekt '
                . 'ze niet af. Daarom vul je ze altijd in. Het nummer is dat van de BIO2.',
            'lijst' => 'De overheidsmaatregelen uit de BIO2. Die vul je altijd in.',
            'normen' => array_map(
                static fn (array $m): array => $eis($m[4] ?? fg_sleutel('fg_om_', fg2_kort($m[0])), $m[0], $m[1],
                    $m[2], ['nvt' => $m[3]]),
                FG2_OVERHEID
            ),
        ],
        [
            'id' => 'keten',
            'naam' => 'Leveranciers en de keten',
            'titel' => 'Leveranciers en de keten',
            'stap' => 3,
            'intro' => 'De opdrachtgever blijft verantwoordelijk voor de risico’s in de keten. De BIO2 zet daarom eisen '
                . 'in het contract. Valt de opdrachtgever onder de Cyberbeveiligingswet, dan waarschuwt hij bij een '
                . 'significant incident binnen 24 uur. Dat lukt alleen als de opdrachtnemer snel en volledig meldt.',
            'lijst' => 'De eisen aan de keten. Die vul je altijd in.',
            'normen' => [
                ...array_map(static fn (array $k): array => $eis(...$k) + ['kop' => 'Uit de BIO2'], FG2_KETEN),
                ...array_map(static fn (array $k): array => $eis(...$k) + [
                    'kop' => 'Uit de Cyberbeveiligingswet',
                    'toon_als' => ['fg_cbw' => ['ja', 'onbekend']],
                ], FG2_CBW),
            ],
        ],
        [
            'id' => 'cloudbeleid',
            'naam' => 'Cloudbeleid van het Rijk',
            'titel' => 'Het cloudbeleid van het Rijk',
            'stap' => 4,
            'intro' => 'Het rijksbrede cloudbeleid van 2026 stelt grenzen aan de cloud van een leverancier. Het geldt voor '
                . 'ministeries, hun diensten en agentschappen.',
            'toon_als' => ['fg_cloud' => ['openbaar', 'vertrouwelijk', 'pg']],
            'lijst' => 'De eisen uit het cloudbeleid van het Rijk.',
            'normen' => array_map(static fn (array $c): array => $eis($c[0], $c[1], $c[2], $c[3],
                $c[4] ? ['toon_als' => ['fg_cloud' => ['vertrouwelijk', 'pg']]] : []), FG2_CLOUDBELEID),
        ],
        ...fg_delen_cloud_privacy(4),
    ];
}

/** Het eerste nummer: "5.14.02 en 5.14.05" en "5.18.01, 5.18.02" worden "5.14.02" en "5.18.01". */
function fg2_kort(string $nummer): string
{
    return preg_split('/ (en|tot) |, /', $nummer)[0];
}

/** @return array<string,mixed> */
function fg2_evalueer(array $a): array
{
    $u = fg_evalueer($a);
    $cbw = (string) ($a['fg_cbw'] ?? '');

    $u['kenmerken'] = [
        ['Cyberbeveiligingswet', match ($cbw) {
            'ja' => 'De opdrachtgever valt eronder',
            'nee' => 'De opdrachtgever valt er niet onder',
            default => 'Niet bekend; de eisen gelden wel',
        }],
        ['Opdrachtnemer onder Cbw of NIS2', cm_antwoord_tekst('fg_cbw_zelf', $a)],
    ];

    $u['uitleg'] = array_values(array_filter([
        'De BIO2 bestaat uit ISO 27001, de maatregelen uit ISO 27002 en de verplichte overheidsmaatregelen. Een '
            . 'certificaat voor ISO 27001 dekt de maatregelen uit ISO 27002 af, maar niet de overheidsmaatregelen. Die '
            . 'staan daarom altijd in deze verklaring.',
        $cbw === 'nee' ? '' : 'Bij een significant incident geeft de opdrachtgever binnen 24 uur een vroegtijdige '
            . 'waarschuwing, binnen 72 uur de melding, en uiterlijk een maand daarna een eindverslag. Dat lukt alleen als de opdrachtnemer snel en volledig '
            . 'meldt.',
        ($a['fg_cbw_zelf'] ?? '') === 'ja' ? 'De opdrachtnemer valt zelf ook onder de Cyberbeveiligingswet of NIS2. Hij '
            . 'heeft dus een eigen zorgplicht en meldplicht. Dat vervangt de eisen in deze verklaring niet.' : '',
    ]));

    array_splice($u['vervolg'], -1, 0, ['De opdrachtgever beoordeelt elk jaar of de dienst nog aan de eisen voldoet '
        . '(BIO2 5.22.01).']);

    return $u;
}

/**
 * De maatregel uit ISO 27002 waar een nummer bij hoort: "6.01.01" en "6.1" worden
 * allebei "6.1". Een eis zonder zo'n nummer, zoals "Cbw 1", geeft null.
 */
function fg2_maatregel(string $nummer): ?string
{
    return preg_match('/^(\d+)\.(\d+)/', fg2_kort($nummer), $m) ? (int) $m[1] . '.' . (int) $m[2] : null;
}

/**
 * Per onderdeel van de zorgplicht: hoe de dienst ervoor staat, uit de antwoorden
 * bij de maatregelen die erbij horen.
 *
 * @return list<list<string>>
 */
function fg2_zorgplicht(array $u): array
{
    $iso = isset($u['afgedekt']['fg_iso27001']);
    $rijen = [];

    foreach (FG2_ZORGPLICHT as [$letter, $naam, $maatregelen]) {
        $telling = ['ja' => 0, 'nee' => 0, 'nvt' => 0, '' => 0];
        foreach ($u['delen'] as $id => $deel) {
            // Alleen de BIO2 zelf: ISO 27017 en 27018 hebben eigen nummers.
            if (!isset(FG2_DELEN[$id]) && !in_array($id, ['overheid', 'keten'], true)) {
                continue;
            }
            foreach ($deel['normen'] as $norm) {
                if (in_array(fg2_maatregel((string) $norm['nummer']), $maatregelen, true)) {
                    $telling[$norm['status']]++;
                }
            }
        }

        $stand = match (true) {
            $telling['nee'] > 0 => 'Voldoet nog niet: ' . fg_aantal($telling['nee'], 'maatregel', 'maatregelen'),
            $telling[''] > 0 => 'Nog niet alles ingevuld',
            $telling['ja'] + $telling['nvt'] === 0 => $iso ? 'Valt onder ISO 27001' : 'Geen maatregelen ingevuld',
            default => $iso ? 'Voldoet, deels via ISO 27001' : 'Voldoet',
        };

        $rijen[] = [$letter . '. ' . $naam, implode(', ', $maatregelen), $stand];
    }

    return $rijen;
}

/** @return list<array<int,mixed>> */
function fg2_document(array $a, array $u): array
{
    $zorgplicht = ($a['fg_cbw'] ?? '') === 'nee' ? [] : [
        ['h2', 'De zorgplicht uit de Cyberbeveiligingswet'],
        ['alinea', 'Artikel 21, derde lid, van de Cyberbeveiligingswet noemt tien onderdelen van de zorgplicht. Hier staat per '
            . 'onderdeel welke maatregelen uit de BIO2 erbij horen, en hoe de dienst daarvoor staat. De koppeling komt '
            . 'uit de generieke baselinetoets BIO2.'],
        ['tabel', [
            'kop' => ['Onderdeel', 'Maatregelen', 'Stand'],
            'rijen' => fg2_zorgplicht($u),
            'breedtes' => [5.6, 6, 5],
            'rijkop' => true,
        ]],
    ];

    return fg_document($a, $u, 'Fit/gap BIO2 en Cbw', $zorgplicht);
}
