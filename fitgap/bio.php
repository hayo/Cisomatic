<?php

/**
 * De fit/gap voor de BIO 1: het beveiligingsniveau, en de normen uit de BIO
 * per hoofdstuk. De vragen, de uitkomst en het document deelt dit formulier
 * met de fit/gap voor de BIO2, in verklaring.php.
 *
 * De normen komen uit de fit/gap analyse BIO, versie 1.05: de
 * beheersmaatregelen uit ISO 27002:2013, zoals de BIO 1 ze noemt. Per norm
 * staat bij welk niveau hij hoort, en op welke aspecten hij slaat:
 * beschikbaarheid (B), integriteit (I) en vertrouwelijkheid (V). Daaruit volgt
 * bij welk vastgesteld niveau de norm geldt; zie fg1_niveaus().
 */

declare(strict_types=1);

/**
 * De niveaus die de opdrachtgever kan vaststellen. Naast BBN 1, 2 en 3 kan
 * voor beschikbaarheid of integriteit een ander niveau gelden dan voor de
 * rest. Dat zijn de kolommen uit de fit/gap analyse.
 */
const FG1_NIVEAUS = [
    'bbn1' => ['label' => 'BBN 1', 'groep' => 'Basisbeveiligingsniveau 1'],
    'bbn1_b' => ['label' => 'BBN 1, met beschikbaarheid op BBN 2', 'groep' => 'Basisbeveiligingsniveau 1'],
    'bbn1_i' => ['label' => 'BBN 1, met integriteit op BBN 2', 'groep' => 'Basisbeveiligingsniveau 1'],
    'bbn1_bi' => ['label' => 'BBN 1, met beschikbaarheid en integriteit op BBN 2',
        'groep' => 'Basisbeveiligingsniveau 1'],
    'bbn2' => ['label' => 'BBN 2', 'groep' => 'Basisbeveiligingsniveau 2'],
    'bbn2_b' => ['label' => 'BBN 2, met beschikbaarheid op BBN 1', 'groep' => 'Basisbeveiligingsniveau 2'],
    'bbn2_i' => ['label' => 'BBN 2, met integriteit op BBN 1', 'groep' => 'Basisbeveiligingsniveau 2'],
    'bbn2_bi' => ['label' => 'BBN 2, met beschikbaarheid en integriteit op BBN 1',
        'groep' => 'Basisbeveiligingsniveau 2'],
    'bbn3' => ['label' => 'BBN 3', 'groep' => 'Basisbeveiligingsniveau 3',
        'hint' => 'Alle normen gelden. De opdrachtgever kan er eisen bij stellen, uit een risicoanalyse.'],
];

/** De hoofdstukken van de BIO, met een korte uitleg. */
const FG1_HOOFDSTUKKEN = [
    '5' => ['Informatiebeveiligingsbeleid', 'Is er beleid voor informatiebeveiliging, en wordt het herzien?'],
    '6' => ['Organiseren van informatiebeveiliging', 'Hoe de opdrachtnemer informatiebeveiliging organiseert, ook '
        . 'bij mobiel werken en telewerken.'],
    '7' => ['Veilig personeel', 'Medewerkers: voor, tijdens en na het dienstverband.'],
    '8' => ['Beheer van bedrijfsmiddelen', 'Wat er is, van wie het is, hoe informatie is ingedeeld, en hoe media '
        . 'worden behandeld.'],
    '9' => ['Toegangsbeveiliging', 'Wie waar bij kan, en hoe dat geregeld is.'],
    '10' => ['Cryptografie', 'Versleuteling, en het beheer van sleutels.'],
    '11' => ['Fysieke beveiliging en beveiliging van de omgeving', 'Gebouwen, ruimtes en apparatuur.'],
    '12' => ['Beveiliging bedrijfsvoering', 'Het dagelijkse werk: procedures, malware, back-ups, logging, software en '
        . 'kwetsbaarheden.'],
    '13' => ['Communicatiebeveiliging', 'Netwerken, en het veilig versturen van informatie.'],
    '14' => ['Acquisitie, ontwikkeling en onderhoud van informatiesystemen', 'Systemen kopen, bouwen en onderhouden. '
        . 'Bij een standaardproduct zonder maatwerk mogen 14.2.5 tot en met 14.2.9 op ‘niet van toepassing’.'],
    '15' => ['Leveranciersrelaties', 'De eigen leveranciers van de opdrachtnemer.'],
    '16' => ['Beheer van informatiebeveiligingsincidenten', 'Incidenten melden, beoordelen, oplossen en ervan leren.'],
    '17' => ['Informatiebeveiligingsaspecten van bedrijfscontinuïteitsbeheer', 'Beveiliging tijdens een crisis of een '
        . 'ramp, en reservecapaciteit.'],
    '18' => ['Naleving', 'Wetten, contracten, privacy, en controles op de beveiliging.'],
];

/** De categorieën binnen een hoofdstuk, als tussenkop. */
const FG1_CATEGORIEEN = [
    '5.1' => 'Aansturing door de directie van de informatiebeveiliging',
    '6.1' => 'Interne organisatie',
    '6.2' => 'Mobiele apparatuur en telewerken',
    '7.1' => 'Voorafgaand aan het dienstverband',
    '7.2' => 'Tijdens het dienstverband',
    '7.3' => 'Beëindiging en wijziging van dienstverband',
    '8.1' => 'Verantwoordelijkheid voor bedrijfsmiddelen',
    '8.2' => 'Informatieclassificatie',
    '8.3' => 'Behandelen van media',
    '9.1' => 'Bedrijfseisen voor toegangsbeveiliging',
    '9.2' => 'Beheer van toegangsrechten van gebruikers',
    '9.3' => 'Verantwoordelijkheden van gebruikers',
    '9.4' => 'Toegangsbeveiliging van systeem en toepassing',
    '10.1' => 'Cryptografische beheersmaatregelen',
    '11.1' => 'Beveiligde gebieden',
    '11.2' => 'Apparatuur',
    '12.1' => 'Bedieningsprocedures en verantwoordelijkheden',
    '12.2' => 'Bescherming tegen malware',
    '12.3' => 'Back-up',
    '12.4' => 'Verslaglegging en monitoren',
    '12.5' => 'Beheersing van operationele software',
    '12.6' => 'Beheer van technische kwetsbaarheden',
    '12.7' => 'Overwegingen betreffende audits van informatiesystemen',
    '13.1' => 'Beheer van netwerkbeveiliging',
    '13.2' => 'Informatietransport',
    '14.1' => 'Beveiligingseisen voor informatiesystemen',
    '14.2' => 'Beveiliging in ontwikkelings- en ondersteunende processen',
    '14.3' => 'Testgegevens',
    '15.1' => 'Informatiebeveiliging in leveranciersrelaties',
    '15.2' => 'Beheer van dienstverlening van leveranciers',
    '16.1' => 'Beheer van informatiebeveiligingsincidenten en -verbeteringen',
    '17.1' => 'Informatiebeveiligings-continuïteit',
    '17.2' => 'Redundante componenten',
    '18.1' => 'Naleving van wettelijke en contractuele eisen',
    '18.2' => 'Informatiebeveiligings-beoordelingen',
];

/** Deze normen mogen bij een standaardproduct (COTS) zonder maatwerk niet van toepassing zijn. */
const FG1_COTS = ['14.2.5', '14.2.6', '14.2.7', '14.2.8', '14.2.9'];

/**
 * De normen: nummer => [niveau, aspecten, titel, tekst]. Het niveau is BBN1
 * als de norm al bij BBN 1 hoort, en anders BBN2. Bij BBN 2 en BBN 3 horen
 * alle normen.
 */
const FG1_NORMEN = [
    '5.1.1' => ['BBN1', 'BIV', 'Beleidsregels voor informatiebeveiliging',
        'Ten behoeve van informatiebeveiliging behoort een reeks beleidsregels te worden gedefinieerd, goedgekeurd '
            . 'door de directie, gepubliceerd en gecommuniceerd aan medewerkers en relevante externe partijen.'],
    '5.1.2' => ['BBN1', 'BIV', 'Beoordeling van het informatiebeveiligingsbeleid',
        'Het beleid voor informatiebeveiliging behoort met geplande tussenpozen of als zich significante '
            . 'veranderingen voordoen, te worden beoordeeld om te waarborgen dat het voortdurend passend, adequaat en '
            . 'doeltreffend is.'],

    '6.1.1' => ['BBN1', 'BIV', 'Rollen en verantwoordelijkheden bij informatiebeveiliging',
        'Alle verantwoordelijkheden bij informatiebeveiliging behoren te worden gedefinieerd en toegewezen.'],
    '6.1.2' => ['BBN1', 'V', 'Scheiding van taken',
        'Conflicterende taken en verantwoordelijkheden behoren te worden gescheiden om de kans op onbevoegd of '
            . 'onbedoeld wijzigen of misbruik van de bedrijfsmiddelen van de organisatie te verminderen.'],
    '6.1.3' => ['BBN2', 'BIV', 'Contact met overheidsinstanties',
        'Er behoren passende contacten met relevante overheidsinstanties te worden onderhouden.'],
    '6.1.5' => ['BBN1', 'BIV', 'Informatiebeveiliging in projectbeheer',
        'Informatiebeveiliging behoort aan de orde te komen in projectbeheer, ongeacht het soort project.'],
    '6.2.1' => ['BBN1', 'BIV', 'Beleid voor mobiele apparatuur',
        'Beleid en ondersteunende beveiligingsmaatregelen behoren te worden vastgesteld om de risico’s die het '
            . 'gebruik van mobiele apparatuur met zich meebrengt te beheren.'],
    '6.2.2' => ['BBN2', 'V', 'Telewerken',
        'Beleid en ondersteunende beveiligingsmaatregelen behoren te worden geïmplementeerd ter beveiliging van '
            . 'informatie die vanaf telewerklocaties wordt benaderd, verwerkt of opgeslagen.'],

    '7.1.1' => ['BBN1', 'V', 'Screening',
        'Verificatie van de achtergrond van alle kandidaten voor een dienstverband behoort te worden uitgevoerd in '
            . 'overeenstemming met relevante wet- en regelgeving en ethische overwegingen en behoort in verhouding te '
            . 'staan tot de bedrijfseisen, de classificatie van de informatie waartoe toegang wordt verleend en de '
            . 'vastgestelde risico’s te zijn.'],
    '7.1.2' => ['BBN1', 'V', 'Arbeidsvoorwaarden',
        'De contractuele overeenkomst met medewerkers en contractanten behoort hun verantwoordelijkheden voor '
            . 'informatiebeveiliging en die van de organisatie te vermelden.'],
    '7.2.1' => ['BBN1', 'BIV', 'Directieverantwoordelijkheden',
        'De directie behoort van alle medewerkers en contractanten te eisen dat ze informatiebeveiliging toepassen '
            . 'in overeenstemming met de vastgestelde beleidsregels en procedures van de organisatie.'],
    '7.2.2' => ['BBN1', 'BIV', 'Bewustzijn, opleiding en training ten aanzien van informatiebeveiliging',
        'Alle medewerkers van de organisatie en, voor zover relevant, contractanten behoren een passende '
            . 'bewustzijnsopleiding en -training te krijgen en regelmatige bijscholing van beleidsregels en procedures '
            . 'van de organisatie, voor zover relevant voor hun functie.'],
    '7.2.3' => ['BBN1', 'BIV', 'Disciplinaire procedure',
        'Er behoort een formele en gecommuniceerde disciplinaire procedure te zijn om actie te ondernemen tegen '
            . 'medewerkers die een inbreuk hebben gepleegd op de informatiebeveiliging.'],
    '7.3.1' => ['BBN1', 'BIV', 'Beëindiging of wijziging van verantwoordelijkheden van het dienstverband',
        'Verantwoordelijkheden en taken met betrekking tot informatiebeveiliging die van kracht blijven na '
            . 'beëindiging of wijziging van het dienstverband behoren te worden gedefinieerd, gecommuniceerd aan de '
            . 'medewerker of contractant, en ten uitvoer gebracht.'],

    '8.1.1' => ['BBN1', 'BIV', 'Inventariseren van bedrijfsmiddelen',
        'Informatie, andere bedrijfsmiddelen die samenhangen met informatie en informatieverwerkende faciliteiten '
            . 'behoren te worden geïdentificeerd, en van deze bedrijfsmiddelen behoort een inventaris te worden '
            . 'opgesteld en onderhouden.'],
    '8.1.2' => ['BBN1', 'BIV', 'Eigendom van bedrijfsmiddelen',
        'Bedrijfsmiddelen die in het inventarisoverzicht worden bijgehouden, behoren een eigenaar te hebben.'],
    '8.1.3' => ['BBN1', 'BIV', 'Aanvaardbaar gebruik van bedrijfsmiddelen',
        'Voor het aanvaardbaar gebruik van informatie en van bedrijfsmiddelen die samenhangen met informatie en '
            . 'informatieverwerkende faciliteiten behoren regels te worden geïdentificeerd, gedocumenteerd en '
            . 'geïmplementeerd.'],
    '8.1.4' => ['BBN1', 'BIV', 'Teruggeven van bedrijfsmiddelen',
        'Alle medewerkers en externe gebruikers behoren alle bedrijfsmiddelen van de organisatie die ze in hun bezit '
            . 'hebben bij beëindiging van hun dienstverband, contract of overeenkomst terug te geven.'],
    '8.2.1' => ['BBN1', 'IV', 'Classificatie van informatie',
        'Informatie behoort te worden geclassificeerd met betrekking tot wettelijke eisen, waarde, belang en '
            . 'gevoeligheid voor onbevoegde bekendmaking of wijziging.'],
    '8.2.2' => ['BBN1', 'V', 'Informatie labelen',
        'Om informatie te labelen behoort een passende reeks procedures te worden ontwikkeld en geïmplementeerd in '
            . 'overeenstemming met het informatieclassificatieschema dat is vastgesteld door de organisatie.'],
    '8.2.3' => ['BBN1', 'V', 'Behandelen van bedrijfsmiddelen',
        'Procedures voor het behandelen van bedrijfsmiddelen behoren te worden ontwikkeld en geïmplementeerd in '
            . 'overeenstemming met het informatieclassificatieschema dat is vastgesteld door de organisatie.'],
    '8.3.1' => ['BBN1', 'V', 'Beheer van verwijderbare media',
        'Voor het beheren van verwijderbare media behoren procedures te worden geïmplementeerd in overeenstemming '
            . 'met het classificatieschema dat door de organisatie is vastgesteld.'],
    '8.3.2' => ['BBN2', 'V', 'Verwijderen van media',
        'Media behoren op een veilige en beveiligde manier te worden verwijderd als ze niet langer nodig zijn, '
            . 'overeenkomstig formele procedures.'],
    '8.3.3' => ['BBN2', 'V', 'Media fysiek overdragen',
        'Media die informatie bevatten, behoren te worden beschermd tegen onbevoegde toegang, misbruik of corruptie '
            . 'tijdens transport.'],

    '9.1.1' => ['BBN1', 'V', 'Beleid voor toegangsbeveiliging',
        'Een beleid voor toegangsbeveiliging behoort te worden vastgesteld, gedocumenteerd en beoordeeld op basis '
            . 'van bedrijfs- en informatiebeveiligingseisen.'],
    '9.1.2' => ['BBN1', 'V', 'Toegang tot netwerken en netwerkdiensten',
        'Gebruikers behoren alleen toegang te krijgen tot het netwerk en de netwerkdiensten waarvoor zij specifiek '
            . 'bevoegd zijn.'],
    '9.2.1' => ['BBN1', 'V', 'Registratie en afmelden van gebruikers',
        'Een formele registratie- en afmeldingsprocedure behoort te worden geïmplementeerd om toewijzing van '
            . 'toegangsrechten mogelijk te maken.'],
    '9.2.2' => ['BBN1', 'V', 'Gebruikers toegang verlenen',
        'Een formele gebruikerstoegangsverleningsprocedure behoort te worden geïmplementeerd om toegangsrechten voor '
            . 'alle typen gebruikers en voor alle systemen en diensten toe te wijzen of in te trekken.'],
    '9.2.3' => ['BBN1', 'V', 'Beheren van speciale toegangsrechten',
        'Het toewijzen en gebruik van speciale toegangsrechten behoren te worden beperkt en beheerst.'],
    '9.2.4' => ['BBN1', 'BIV', 'Beheer van geheime authenticatie-informatie van gebruikers',
        'Het toewijzen van geheime authenticatie-informatie behoort te worden beheerst via een formeel '
            . 'beheersproces.'],
    '9.2.5' => ['BBN1', 'BIV', 'Beoordeling van toegangsrechten van gebruikers',
        'Eigenaren van bedrijfsmiddelen behoren toegangsrechten van gebruikers regelmatig te beoordelen.'],
    '9.2.6' => ['BBN1', 'V', 'Toegangsrechten intrekken of aanpassen',
        'De toegangsrechten van alle medewerkers en externe gebruikers voor informatie en informatieverwerkende '
            . 'faciliteiten behoren bij beëindiging van hun dienstverband, contract of overeenkomst te worden '
            . 'verwijderd, en bij wijzigingen behoren ze te worden aangepast.'],
    '9.3.1' => ['BBN1', 'V', 'Geheime authenticatie-informatie gebruiken',
        'Van gebruikers behoort te worden verlangd dat zij zich bij het gebruiken van geheime '
            . 'authenticatie-informatie houden aan de praktijk van de organisatie.'],
    '9.4.1' => ['BBN1', 'V', 'Beperking toegang tot informatie',
        'Toegang tot informatie en systeemfuncties van toepassingen behoort te worden beperkt in overeenstemming met '
            . 'het beleid voor toegangsbeveiliging.'],
    '9.4.2' => ['BBN1', 'V', 'Beveiligde inlogprocedures',
        'Indien het beleid voor toegangsbeveiliging dit vereist, behoort toegang tot systemen en toepassingen te '
            . 'worden beheerst door een beveiligde inlogprocedure.'],
    '9.4.3' => ['BBN1', 'V', 'Systeem voor wachtwoordbeheer',
        'Systemen voor wachtwoordbeheer behoren interactief te zijn en sterke wachtwoorden te waarborgen.'],
    '9.4.4' => ['BBN1', 'V', 'Speciale systeemhulpmiddelen gebruiken',
        'Het gebruik van systeemhulpmiddelen die in staat zijn om beheersmaatregelen voor systemen en toepassingen '
            . 'te omzeilen behoort te worden beperkt en nauwkeurig te worden gecontroleerd.'],
    '9.4.5' => ['BBN1', 'V', 'Toegangsbeveiliging op programmabroncode',
        'Toegang tot de programmabroncode behoort te worden beperkt.'],

    '10.1.1' => ['BBN2', 'V', 'Beleid inzake het gebruik van cryptografische beheersmaatregelen',
        'Ter bescherming van informatie behoort een beleid voor het gebruik van cryptografische beheersmaatregelen '
            . 'te worden ontwikkeld en geïmplementeerd.'],
    '10.1.2' => ['BBN1', 'V', 'Sleutelbeheer',
        'Met betrekking tot het gebruik, de bescherming en de levensduur van cryptografische sleutels behoort '
            . 'tijdens hun gehele levenscyclus een beleid te worden ontwikkeld en geïmplementeerd.'],

    '11.1.1' => ['BBN1', 'V', 'Fysieke beveiligingszone',
        'Beveiligingszones behoren te worden gedefinieerd en gebruikt om gebieden te beschermen die gevoelige of '
            . 'essentiële informatie en informatieverwerkende faciliteiten bevatten.'],
    '11.1.2' => ['BBN1', 'BIV', 'Fysieke toegangsbeveiliging',
        'Beveiligde gebieden behoren te worden beschermd door passende toegangsbeveiliging om ervoor te zorgen dat '
            . 'alleen bevoegd personeel toegang krijgt.'],
    '11.1.3' => ['BBN1', 'BIV', 'Kantoren, ruimten en faciliteiten beveiligen',
        'Voor kantoren, ruimten en faciliteiten behoort fysieke beveiliging te worden ontworpen en toegepast.'],
    '11.1.4' => ['BBN1', 'B', 'Beschermen tegen bedreigingen van buitenaf',
        'Tegen natuurrampen, kwaadwillige aanvallen of ongelukken behoort fysieke bescherming te worden ontworpen en '
            . 'toegepast.'],
    '11.1.5' => ['BBN2', 'V', 'Werken in beveiligde gebieden',
        'Voor het werken in beveiligde gebieden behoren procedures te worden ontwikkeld en toegepast.'],
    '11.1.6' => ['BBN1', 'V', 'Laad- en loslocatie',
        'Toegangspunten zoals laad- en loslocaties en andere punten waar onbevoegde personen het terrein kunnen '
            . 'betreden, behoren te worden beheerst, en zo mogelijk te worden afgeschermd van informatieverwerkende '
            . 'faciliteiten om onbevoegde toegang te vermijden.'],
    '11.2.1' => ['BBN1', 'V', 'Plaatsing en bescherming van apparatuur',
        'Apparatuur behoort zo te worden geplaatst en beschermd dat risico’s van bedreigingen en gevaren van '
            . 'buitenaf, alsook de kans op onbevoegde toegang worden verkleind.'],
    '11.2.2' => ['BBN1', 'B', 'Nutsvoorzieningen',
        'Apparatuur behoort te worden beschermd tegen stroomuitval en andere verstoringen die worden veroorzaakt '
            . 'door ontregelingen in nutsvoorzieningen.'],
    '11.2.3' => ['BBN1', 'B', 'Beveiliging van bekabeling',
        'Voedings- en telecommunicatiekabels voor het versturen van gegevens of die informatiediensten ondersteunen, '
            . 'behoren te worden beschermd tegen interceptie, verstoring of schade.'],
    '11.2.4' => ['BBN1', 'B', 'Onderhoud van apparatuur',
        'Apparatuur behoort correct te worden onderhouden om de continue beschikbaarheid en integriteit ervan te '
            . 'waarborgen.'],
    '11.2.5' => ['BBN1', 'B', 'Verwijdering van bedrijfsmiddelen',
        'Apparatuur, informatie en software behoren niet van de locatie te worden meegenomen zonder voorafgaande '
            . 'goedkeuring.'],
    '11.2.6' => ['BBN1', 'V', 'Beveiliging van apparatuur en bedrijfsmiddelen buiten het terrein',
        'Bedrijfsmiddelen die zich buiten het terrein bevinden, behoren te worden beveiligd, waarbij rekening '
            . 'behoort te worden gehouden met de verschillende risico’s van werken buiten het terrein van de '
            . 'organisatie.'],
    '11.2.7' => ['BBN1', 'V', 'Veilig verwijderen of hergebruiken van apparatuur',
        'Alle onderdelen van de apparatuur die opslagmedia bevatten, behoren te worden geverifieerd om te waarborgen '
            . 'dat gevoelige gegevens en in licentie gegeven software voorafgaand aan verwijdering of hergebruik zijn '
            . 'verwijderd of betrouwbaar veilig zijn overschreven.'],
    '11.2.8' => ['BBN1', 'V', 'Onbeheerde gebruikersapparatuur',
        'Gebruikers moeten ervoor zorgen dat onbeheerde apparatuur voldoende beschermd is.'],
    '11.2.9' => ['BBN1', 'V', '‘Clear desk’- en ‘clear screen’-beleid',
        'Er behoort een ‘clear desk’-beleid voor papieren documenten en verwijderbare opslagmedia en een ‘clear '
            . 'screen’-beleid voor informatieverwerkende faciliteiten te worden ingesteld.'],

    '12.1.1' => ['BBN1', 'V', 'Gedocumenteerde bedieningsprocedures',
        'Bedieningsprocedures behoren te worden gedocumenteerd en beschikbaar te worden gesteld aan alle gebruikers '
            . 'die ze nodig hebben.'],
    '12.1.2' => ['BBN1', 'B', 'Wijzigingsbeheer',
        'Veranderingen in de organisatie, bedrijfsprocessen, informatieverwerkende faciliteiten en systemen die van '
            . 'invloed zijn op de informatiebeveiliging behoren te worden beheerst.'],
    '12.1.3' => ['BBN1', 'BIV', 'Capaciteitsbeheer',
        'Het gebruik van middelen behoort te worden gemonitord en afgestemd, en er behoren verwachtingen te worden '
            . 'opgesteld voor toekomstige capaciteitseisen om de vereiste systeemprestaties te waarborgen.'],
    '12.1.4' => ['BBN1', 'B', 'Scheiding van ontwikkel-, test- en productieomgevingen',
        'Ontwikkel-, test- en productieomgevingen behoren te worden gescheiden om het risico van onbevoegde toegang '
            . 'tot of veranderingen aan de productieomgeving te verlagen.'],
    '12.2.1' => ['BBN1', 'B', 'Beheersmaatregelen tegen malware',
        'Ter bescherming tegen malware behoren beheersmaatregelen voor detectie, preventie en herstel te worden '
            . 'geïmplementeerd, in combinatie met een passend bewustzijn van gebruikers.'],
    '12.3.1' => ['BBN1', 'B', 'Back-up van informatie',
        'Regelmatig behoren back-upkopieën van informatie, software en systeemafbeeldingen te worden gemaakt en '
            . 'getest in overeenstemming met een overeengekomen back-upbeleid.'],
    '12.4.1' => ['BBN1', 'BIV', 'Gebeurtenissen registreren',
        'Logbestanden van gebeurtenissen die gebruikersactiviteiten, uitzonderingen en '
            . 'informatiebeveiligingsgebeurtenissen registreren, behoren te worden gemaakt, bewaard en regelmatig te '
            . 'worden beoordeeld.'],
    '12.4.2' => ['BBN1', 'V', 'Beschermen van informatie in logbestanden',
        'Logfaciliteiten en informatie in logbestanden behoren te worden beschermd tegen vervalsing en onbevoegde '
            . 'toegang.'],
    '12.4.3' => ['BBN1', 'B', 'Logbestanden van beheerders en operators',
        'Activiteiten van systeembeheerders en -operators behoren te worden vastgelegd en de logbestanden behoren te '
            . 'worden beschermd en regelmatig te worden beoordeeld.'],
    '12.4.4' => ['BBN1', 'I', 'Kloksynchronisatie',
        'De klokken van alle relevante informatieverwerkende systemen binnen een organisatie of beveiligingsdomein '
            . 'behoren te worden gesynchroniseerd met één referentietijdbron.'],
    '12.5.1' => ['BBN1', 'B', 'Software installeren op operationele systemen',
        'Om het op operationele systemen installeren van software te beheersen behoren procedures te worden '
            . 'geïmplementeerd.'],
    '12.6.1' => ['BBN1', 'B', 'Beheer van technische kwetsbaarheden',
        'Informatie over technische kwetsbaarheden van informatiesystemen die worden gebruikt behoort tijdig te '
            . 'worden verkregen, de blootstelling van de organisatie aan dergelijke kwetsbaarheden te worden '
            . 'geëvalueerd en passende maatregelen te worden genomen om het risico dat ermee samenhangt aan te pakken.'],
    '12.6.2' => ['BBN1', 'B', 'Beperkingen voor het installeren van software',
        'Voor het door gebruikers installeren van software behoren regels te worden vastgesteld en te worden '
            . 'geïmplementeerd.'],
    '12.7.1' => ['BBN1', 'B', 'Beheersmaatregelen betreffende audits van informatiesystemen',
        'Auditeisen en -activiteiten die verificatie van uitvoeringssystemen met zich meebrengen, behoren zorgvuldig '
            . 'te worden gepland en afgestemd om bedrijfsprocessen zo min mogelijk te verstoren.'],

    '13.1.1' => ['BBN1', 'B', 'Beheersmaatregelen voor netwerken',
        'Netwerken behoren te worden beheerd en beheerst om informatie in systemen en toepassingen te beschermen.'],
    '13.1.2' => ['BBN1', 'B', 'Beveiliging van netwerkdiensten',
        'Beveiligingsmechanismen, dienstverleningsniveaus en beheerseisen voor alle netwerkdiensten behoren te '
            . 'worden geïdentificeerd en opgenomen in overeenkomsten betreffende netwerkdiensten. Dit geldt zowel voor '
            . 'diensten die intern worden geleverd als voor uitbestede diensten.'],
    '13.1.3' => ['BBN1', 'V', 'Scheiding in netwerken',
        'Groepen van informatiediensten, -gebruikers en -systemen behoren in netwerken te worden gescheiden.'],
    '13.2.1' => ['BBN1', 'V', 'Beleid en procedures voor informatietransport',
        'Ter bescherming van het informatietransport, dat via alle soorten communicatiefaciliteiten verloopt, '
            . 'behoren formele beleidsregels, procedures en beheersmaatregelen voor transport van kracht te zijn.'],
    '13.2.2' => ['BBN1', 'V', 'Overeenkomsten over informatietransport',
        'Overeenkomsten behoren betrekking te hebben op het beveiligd transporteren van bedrijfsinformatie tussen de '
            . 'organisatie en externe partijen.'],
    '13.2.3' => ['BBN1', 'V', 'Elektronische berichten',
        'Informatie die is opgenomen in elektronische berichten behoort passend te zijn beschermd.'],
    '13.2.4' => ['BBN1', 'V', 'Vertrouwelijkheids- of geheimhoudingsovereenkomst',
        'Eisen voor vertrouwelijkheids- of geheimhoudingsovereenkomsten die de behoeften van de organisatie '
            . 'betreffende het beschermen van informatie weerspiegelen, behoren te worden vastgesteld, regelmatig te '
            . 'worden beoordeeld en gedocumenteerd.'],

    '14.1.1' => ['BBN1', 'BIV', 'Analyse en specificatie van informatiebeveiligingseisen',
        'De eisen die verband houden met informatiebeveiliging behoren te worden opgenomen in de eisen voor nieuwe '
            . 'informatiesystemen of voor uitbreidingen van bestaande informatiesystemen.'],
    '14.1.2' => ['BBN1', 'V', 'Toepassingen op openbare netwerken beveiligen',
        'Informatie die deel uitmaakt van uitvoeringsdiensten en die via openbare netwerken wordt uitgewisseld, '
            . 'behoort te worden beschermd tegen frauduleuze activiteiten, geschillen over contracten en onbevoegde '
            . 'openbaarmaking en wijziging.'],
    '14.1.3' => ['BBN1', 'V', 'Transacties van toepassingen beschermen',
        'Informatie die deel uitmaakt van transacties van toepassingen behoort te worden beschermd ter voorkoming '
            . 'van onvolledige overdracht, foutieve routering, onbevoegd wijzigen van berichten, onbevoegd openbaar '
            . 'maken, onbevoegd vermenigvuldigen of afspelen.'],
    '14.2.1' => ['BBN1', 'BIV', 'Beleid voor beveiligd ontwikkelen',
        'Voor het ontwikkelen van software en systemen behoren regels te worden vastgesteld en op '
            . 'ontwikkelactiviteiten binnen de organisatie te worden toegepast.'],
    '14.2.2' => ['BBN1', 'B', 'Procedures voor wijzigingsbeheer met betrekking tot systemen',
        'Wijzigingen aan systemen binnen de levenscyclus van de ontwikkeling behoren te worden beheerst door het '
            . 'gebruik van formele procedures voor wijzigingsbeheer.'],
    '14.2.3' => ['BBN2', 'B', 'Technische beoordeling van toepassingen na wijzigingen besturingsplatform',
        'Als besturingsplatforms zijn veranderd, behoren bedrijfskritische toepassingen te worden beoordeeld en '
            . 'getest om te waarborgen dat er geen nadelige impact is op de activiteiten of de beveiliging van de '
            . 'organisatie.'],
    '14.2.5' => ['BBN1', 'BIV', 'Principes voor engineering van beveiligde systemen',
        'Principes voor de engineering van beveiligde systemen behoren te worden vastgesteld, gedocumenteerd, '
            . 'onderhouden en toegepast voor alle verrichtingen betreffende het implementeren van informatiesystemen.'],
    '14.2.6' => ['BBN1', 'BIV', 'Beveiligde ontwikkelomgeving',
        'Organisaties behoren beveiligde ontwikkelomgevingen vast te stellen en passend te beveiligen voor '
            . 'verrichtingen op het gebied van systeemontwikkeling en integratie, die betrekking hebben op de gehele '
            . 'levenscyclus van de systeemontwikkeling.'],
    '14.2.7' => ['BBN1', 'BIV', 'Uitbestede softwareontwikkeling',
        'Uitbestede systeemontwikkeling behoort onder supervisie te staan van en te worden gemonitord door de '
            . 'organisatie.'],
    '14.2.8' => ['BBN1', 'BIV', 'Testen van systeembeveiliging',
        'Tijdens ontwikkelactiviteiten behoort de beveiligingsfunctionaliteit te worden getest.'],
    '14.2.9' => ['BBN1', 'I', 'Systeemacceptatietests',
        'Voor nieuwe informatiesystemen, upgrades en nieuwe versies behoren programma’s voor het uitvoeren van '
            . 'acceptatietests en gerelateerde criteria te worden vastgesteld.'],
    '14.3.1' => ['BBN2', 'B', 'Bescherming van testgegevens',
        'Testgegevens behoren zorgvuldig te worden gekozen, beschermd en gecontroleerd.'],

    '15.1.1' => ['BBN1', 'BIV', 'Informatiebeveiligingsbeleid voor leveranciersrelaties',
        'Met de leverancier behoren de informatiebeveiligingseisen om risico’s te verlagen die verband houden met de '
            . 'toegang van de leverancier tot de bedrijfsmiddelen van de organisatie, te worden overeengekomen en '
            . 'gedocumenteerd.'],
    '15.1.2' => ['BBN1', 'BIV', 'Opnemen van beveiligingsaspecten in leveranciersovereenkomsten',
        'Alle relevante informatiebeveiligingseisen behoren te worden vastgesteld en overeengekomen met elke '
            . 'leverancier die toegang heeft tot IT-infrastructuurelementen ten behoeve van de informatie van de '
            . 'organisatie, of deze verwerkt, opslaat, communiceert of biedt.'],
    '15.1.3' => ['BBN1', 'BIV', 'Toeleveringsketen van informatie- en communicatietechnologie',
        'Overeenkomsten met leveranciers behoren eisen te bevatten die betrekking hebben op de '
            . 'informatiebeveiligingsrisico’s in verband met de toeleveringsketen van de diensten en producten op het '
            . 'gebied van informatie- en communicatietechnologie.'],
    '15.2.1' => ['BBN1', 'BIV', 'Monitoring en beoordeling van dienstverlening van leveranciers',
        'Organisaties behoren regelmatig de dienstverlening van leveranciers te monitoren, te beoordelen en te '
            . 'auditen.'],
    '15.2.2' => ['BBN2', 'BIV', 'Beheer van veranderingen in dienstverlening van leveranciers',
        'Veranderingen in de dienstverlening van leveranciers, met inbegrip van handhaving en verbetering van '
            . 'bestaande beleidslijnen, procedures en beheersmaatregelen voor informatiebeveiliging, behoren te '
            . 'worden, beheerd, rekening houdend met de kritikaliteit van bedrijfsinformatie, betrokken systemen en '
            . 'processen en herbeoordeling van risico’s.'],

    '16.1.1' => ['BBN1', 'BIV', 'Verantwoordelijkheden en procedures',
        'Directieverantwoordelijkheden en -procedures behoren te worden vastgesteld om een snelle, doeltreffende en '
            . 'ordelijke respons op informatiebeveiligingsincidenten te bewerkstelligen.'],
    '16.1.2' => ['BBN1', 'BIV', 'Rapportage van informatiebeveiligingsgebeurtenissen',
        'Informatiebeveiligingsgebeurtenissen behoren zo snel mogelijk via de juiste leidinggevende niveaus te '
            . 'worden gerapporteerd.'],
    '16.1.3' => ['BBN1', 'BIV', 'Rapportage van zwakke plekken in de informatiebeveiliging',
        'Van medewerkers en contractanten die gebruikmaken van de informatiesystemen en -diensten van de organisatie '
            . 'behoort te worden geëist dat zij de in systemen of diensten waargenomen of vermeende zwakke plekken in '
            . 'de informatiebeveiliging registreren en rapporteren.'],
    '16.1.4' => ['BBN1', 'BIV', 'Beoordeling van en besluitvorming over informatiebeveiligingsgebeurtenissen',
        'Informatiebeveiligingsgebeurtenissen behoren te worden beoordeeld en er behoort te worden geoordeeld of zij '
            . 'moeten worden geclassificeerd als informatiebeveiligingsincidenten.'],
    '16.1.5' => ['BBN1', 'BIV', 'Respons op informatiebeveiligingsincidenten',
        'Op informatiebeveiligingsincidenten behoort te worden gereageerd in overeenstemming met de gedocumenteerde '
            . 'procedures.'],
    '16.1.6' => ['BBN2', 'BIV', 'Lering uit informatiebeveiligingsincidenten',
        'Kennis die is verkregen door informatiebeveiligingsincidenten te analyseren en op te lossen behoort te '
            . 'worden gebruikt om de waarschijnlijkheid of impact van toekomstige incidenten te verkleinen.'],
    '16.1.7' => ['BBN2', 'BIV', 'Verzamelen van bewijsmateriaal',
        'De organisatie behoort procedures te definiëren en toe te passen voor het identificeren, verzamelen, '
            . 'verkrijgen en bewaren van informatie die als bewijs kan dienen.'],

    '17.1.1' => ['BBN1', 'B', 'Informatiebeveiligingscontinuïteit plannen',
        'De organisatie behoort haar eisen voor informatiebeveiliging en voor de continuïteit van het '
            . 'informatiebeveiligingsbeheer in ongunstige situaties, bijv. een crisis of een ramp, vast te stellen.'],
    '17.1.2' => ['BBN1', 'B', 'Informatiebeveiligingscontinuïteit implementeren',
        'De organisatie behoort processen, procedures en beheersmaatregelen vast te stellen, te documenteren, te '
            . 'implementeren en te handhaven om het vereiste niveau van continuïteit voor informatiebeveiliging '
            . 'tijdens een ongunstige situatie te waarborgen.'],
    '17.1.3' => ['BBN1', 'B', 'Informatiebeveiligingscontinuïteit verifiëren, beoordelen en evalueren',
        'De organisatie behoort de ten behoeve van informatiebeveiligingscontinuïteit vastgestelde en '
            . 'geïmplementeerde beheersmaatregelen regelmatig te verifiëren om te waarborgen dat ze deugdelijk en '
            . 'doeltreffend zijn tijdens ongunstige situaties.'],
    '17.2.1' => ['BBN1', 'B', 'Beschikbaarheid van informatieverwerkende faciliteiten',
        'Informatieverwerkende faciliteiten behoren met voldoende redundantie te worden geïmplementeerd om aan '
            . 'beschikbaarheidseisen te voldoen.'],

    '18.1.1' => ['BBN1', 'B', 'Vaststellen van toepasselijke wetgeving en contractuele eisen',
        'Alle relevante wettelijke statutaire, regelgevende, contractuele eisen en de aanpak van de organisatie om '
            . 'aan deze eisen te voldoen behoren voor elk informatiesysteem en de organisatie expliciet te worden '
            . 'vastgesteld, gedocumenteerd en actueel gehouden.'],
    '18.1.2' => ['BBN1', 'BIV', 'Intellectuele-eigendomsrechten',
        'Om de naleving van wettelijke, regelgevende en contractuele eisen in verband met '
            . 'intellectuele-eigendomsrechten en het gebruik van eigendomssoftwareproducten te waarborgen behoren '
            . 'passende procedures te worden geïmplementeerd.'],
    '18.1.3' => ['BBN2', 'BIV', 'Beschermen van registraties',
        'Registraties behoren in overeenstemming met wettelijke, regelgevende, contractuele en bedrijfseisen te '
            . 'worden beschermd tegen verlies, vernietiging, vervalsing, onbevoegde toegang en onbevoegde vrijgave.'],
    '18.1.4' => ['BBN1', 'V', 'Privacy en bescherming van persoonsgegevens',
        'Privacy en bescherming van persoonsgegevens behoren, voor zover van toepassing, te worden gewaarborgd in '
            . 'overeenstemming met relevante wet- en regelgeving.'],
    '18.1.5' => ['BBN1', 'V', 'Voorschriften voor het gebruik van cryptografische beheersmaatregelen',
        'Cryptografische beheersmaatregelen behoren te worden toegepast in overeenstemming met alle relevante '
            . 'overeenkomsten, wet- en regelgeving.'],
    '18.2.1' => ['BBN1', 'BIV', 'Onafhankelijke beoordeling van informatiebeveiliging',
        'De aanpak van de organisatie ten aanzien van het beheer van informatiebeveiliging en de implementatie ervan '
            . '(bijv. beheerdoelstellingen, beheersmaatregelen, beleidsregels, processen en procedures voor '
            . 'informatiebeveiliging), behoren onafhankelijk en met geplande tussenpozen of zodra zich belangrijke '
            . 'veranderingen voordoen te worden beoordeeld.'],
    '18.2.2' => ['BBN1', 'BIV', 'Naleving van beveiligingsbeleid en -normen',
        'De directie behoort regelmatig de naleving van de informatieverwerking en -procedures binnen haar '
            . 'verantwoordelijkheidsgebied te beoordelen aan de hand van de desbetreffende beleidsregels, normen en '
            . 'andere eisen betreffende beveiliging.'],
    '18.2.3' => ['BBN1', 'BIV', 'Beoordeling van technische naleving',
        'Informatiesystemen behoren regelmatig te worden beoordeeld op naleving van de beleidsregels en normen van '
            . 'de organisatie voor informatiebeveiliging.'],
];

/** @return list<array<string,mixed>> */
function fg1_secties(): array
{
    $delen = fg1_delen();

    return [
        fg_sectie_dienst(),
        fg_sectie_reikwijdte(
            'Niveau, persoonsgegevens en cloud',
            'Welke normen gelden, hangt af van het beveiligingsniveau, van persoonsgegevens en van de cloud. Het niveau '
                . 'stelt de opdrachtgever vast. Weet je het niet? Vraag het dan na.',
            [[
                'key' => 'fg_niveau',
                'type' => 'radio',
                'label' => 'Welk beveiligingsniveau heeft de opdrachtgever vastgesteld?',
                'hint' => 'Het basisbeveiligingsniveau (BBN) uit de BIO. Soms geldt voor beschikbaarheid of integriteit '
                    . 'een ander niveau dan voor de rest.',
                'verplicht' => true,
                'opties' => FG1_NIVEAUS,
            ]],
            [[
                'key' => 'fg_cloudniveau',
                'type' => 'radio',
                'label' => 'Welk niveau heeft de opdrachtgever vastgesteld voor het cloudbeleid?',
                'hint' => 'Dit komt in het document. Het verandert niets aan de normen.',
                'verplicht' => true,
                'toon_als' => ['fg_cloud' => ['openbaar', 'vertrouwelijk', 'pg']],
                'opties' => [
                    'laag' => ['label' => 'Laag'],
                    'midden' => ['label' => 'Midden'],
                    'hoog' => ['label' => 'Hoog'],
                    'zeer_hoog' => ['label' => 'Zeer hoog'],
                ],
            ]]
        ),
        fg_sectie_certificaten(
            'Dan hoef je de normen uit de BIO niet in te vullen. Ze komen uit ISO 27002, en vallen onder ISO 27001.',
            [
                ['tekst' => 'De normen uit de BIO die bij het beveiligingsniveau horen.',
                    'toon_als' => ['fg_iso27001' => ['nee']]],
                ['tekst' => 'Geen normen uit de BIO. Het certificaat voor ISO 27001 dekt ze af.',
                    'toon_als' => ['fg_iso27001' => ['ja']]],
            ],
            $delen
        ),
        ...array_map('fg_sectie_deel', $delen),
        fg_sectie_verklaring(4, $delen),
    ];
}

/**
 * De delen: elk hoofdstuk van de BIO, en daarna cloud en privacy.
 *
 * @return list<array<string,mixed>>
 */
function fg1_delen(): array
{
    $hoofdstukken = [];

    foreach (FG1_NORMEN as $nummer => [$niveau, $aspecten, $titel, $tekst]) {
        $categorie = implode('.', array_slice(explode('.', $nummer), 0, 2));
        $niveaus = fg1_niveaus($niveau, $aspecten);

        $hoofdstukken[explode('.', $nummer)[0]][] = [
            'key' => fg_sleutel('fg_bio_', $nummer),
            'nummer' => $nummer,
            'titel' => $titel,
            'tekst' => $tekst,
            'kop' => $categorie . ' ' . FG1_CATEGORIEEN[$categorie],
            'toon_als' => $niveaus === [] ? [] : ['fg_niveau' => $niveaus],
            'nvt' => in_array($nummer, FG1_COTS, true)
                ? 'Alleen bij een standaardproduct (COTS) zonder maatwerk. Bij maatwerk geldt de norm wel.'
                : '',
        ];
    }

    $delen = [];
    foreach ($hoofdstukken as $nummer => $normen) {
        [$naam, $intro] = FG1_HOOFDSTUKKEN[$nummer];
        $delen[] = [
            'id' => 'bio_' . $nummer,
            'naam' => $nummer . ' ' . $naam,
            'titel' => $nummer . ' ' . $naam,
            'stap' => 2,
            'intro' => $intro,
            'toon_als' => ['fg_iso27001' => ['nee']],
            'afgedekt_als' => ['fg_iso27001' => ['ja']],
            'certificaat' => 'fg_iso27001',
            'kader' => 'de normen uit de BIO',
            'normen' => $normen,
        ];
    }

    return [...$delen, ...fg_delen_cloud_privacy(3)];
}

/**
 * Bij welke vastgestelde niveaus een norm geldt. Leeg betekent: altijd, want
 * de norm hoort al bij BBN 1. Een norm die pas bij BBN 2 hoort, geldt bij BBN 1
 * alleen als een aspect waarop hij slaat naar BBN 2 gaat. Bij BBN 2 valt hij
 * weg als alle aspecten waarop hij slaat naar BBN 1 gaan. Dat volgt de
 * formules in de kolommen van de fit/gap analyse.
 *
 * @return list<string>
 */
function fg1_niveaus(string $niveau, string $aspecten): array
{
    if ($niveau === 'BBN1') {
        return [];
    }

    $b = str_contains($aspecten, 'B');
    $i = str_contains($aspecten, 'I');

    return array_keys(array_filter([
        'bbn1' => false,
        'bbn1_b' => $b,
        'bbn1_i' => $i,
        'bbn1_bi' => $b || $i,
        'bbn2' => true,
        'bbn2_b' => $aspecten !== 'B',
        'bbn2_i' => $aspecten !== 'I',
        'bbn2_bi' => str_contains($aspecten, 'V'),
        'bbn3' => true,
    ]));
}

/** @return array<string,mixed> */
function fg1_evalueer(array $a): array
{
    $u = fg_evalueer($a);

    $u['kenmerken'] = [
        ['Beveiligingsniveau', cm_antwoord_tekst('fg_niveau', $a)],
        ...(($a['fg_cloudniveau'] ?? '') === '' ? [] : [['Niveau cloudbeleid', cm_antwoord_tekst('fg_cloudniveau', $a)]]),
    ];
    $u['uitleg'] = [
        'De BIO kent drie basisbeveiligingsniveaus. Het niveau bepaalt welke normen uit de BIO gelden. Geldt voor '
            . 'beschikbaarheid of integriteit een ander niveau, dan gelden er normen meer of minder.',
    ];

    return $u;
}

/** @return list<array<int,mixed>> */
function fg1_document(array $a, array $u): array
{
    return fg_document($a, $u, 'Fit/gap BIO');
}
