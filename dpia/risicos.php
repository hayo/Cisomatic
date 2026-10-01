<?php

/**
 * De standaardrisico's, de categorieën en de tolerantiematrix.
 *
 * Elk risico wordt in het formulier één regel die openklapt: per maatregel of
 * die er al is of er komt, impact, kans, en de kans met de nieuwe maatregelen.
 * De voorstellen voor impact en kans staan alleen in regels.js; de omvang
 * rekent de server zelf uit.
 */

declare(strict_types=1);

/** @return array<string,string> letter => omschrijving */
function dp_risicocategorieen(): array
{
    return [
        'A' => 'Inbreuk op de persoonlijke levenssfeer, ook door meer gegevens te gebruiken dan nodig',
        'B' => 'Flink economisch of maatschappelijk nadeel',
        'C' => 'Oneerlijke of ongelijke behandeling',
        'D' => 'Minder vrijheid om zelf te kiezen',
        'E' => 'Schade aan de reputatie',
        'F' => 'Gevaar voor de veiligheid van de betrokkene of van familieleden',
        'G' => 'Aantasting van de menselijke waardigheid',
        'H' => 'Aantasting van andere grondrechten',
        'I' => 'De rechten van betrokkenen worden niet nageleefd',
        'J' => 'De meldplicht voor datalekken wordt niet nageleefd',
        'K' => 'Er is geen geldige toestemming',
        'L' => 'Bewaartermijnen worden niet nageleefd',
        'M' => 'Overige risico’s, zoals te weinig beveiliging of de inzet van verwerkers',
    ];
}

/**
 * Omvang = kans x impact. De matrix is symmetrisch, dus de volgorde van kans
 * en impact maakt niet uit.
 *
 * @return array<string,array<string,string>>
 */
function dp_tolerantiematrix(): array
{
    return [
        'zl' => ['zl' => 'zl', 'l' => 'l', 'm' => 'l', 'h' => 'm', 'zh' => 'm'],
        'l'  => ['zl' => 'l', 'l' => 'l', 'm' => 'm', 'h' => 'm', 'zh' => 'h'],
        'm'  => ['zl' => 'l', 'l' => 'm', 'm' => 'm', 'h' => 'h', 'zh' => 'h'],
        'h'  => ['zl' => 'm', 'l' => 'm', 'm' => 'h', 'h' => 'h', 'zh' => 'zh'],
        'zh' => ['zl' => 'm', 'l' => 'h', 'm' => 'h', 'h' => 'zh', 'zh' => 'zh'],
    ];
}

function dp_omvang(string $kans, string $impact): string
{
    return dp_tolerantiematrix()[$kans][$impact] ?? '';
}

/**
 * De standaardrisico's, in de volgorde van het document.
 *
 * @return list<array<string,mixed>>
 */
function dp_risicos(): array
{
    static $risicos = null;

    return $risicos ??= [
        [
            'id' => 'r1',
            'titel' => 'Misbruik van de gegevens',
            'tekst' => 'Iemand die de gegevens niet mag zien, krijgt ze toch in handen en misbruikt ze. '
                . 'Dat is een inbreuk op de privacy van de betrokkenen. Het kan leiden tot schade die de '
                . 'organisatie moet vergoeden, een boete van de toezichthouder of reputatieschade.',
            'categorieen' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
            'maatregelen' => [
                'autorisatie' => 'Alleen wie de gegevens voor het werk nodig heeft, kan erbij',
                'geheimhouding' => 'Wie erbij kan, heeft een geheimhoudingsplicht of heeft de eed of belofte afgelegd',
                'screening' => 'Wie erbij kan, is gescreend, bijvoorbeeld met een VOG of een veiligheidsonderzoek',
                'logging' => 'Het systeem legt vast wie de gegevens inziet of wijzigt',
                'versleuteling' => 'De gegevens zijn versleuteld, bij opslag en bij verzending',
            ],
        ],
        [
            'id' => 'r2',
            'titel' => 'Rechtsgrond of doel is niet duidelijk',
            'tekst' => 'De rechtsgrond en de doelen van de verwerking zijn niet duidelijk vastgelegd, of '
                . 'de betrokkenen kennen ze niet. Dat is in strijd met artikel 5, 6 en 12 tot en met 14 '
                . 'van de AVG. Betrokkenen begrijpen dan niet wat er met hun gegevens gebeurt. Ze kunnen '
                . 'vragen de gegevens te wissen, een klacht indienen of naar de pers stappen. Dat kan '
                . 'leiden tot een boete of reputatieschade.',
            'categorieen' => ['A', 'I'],
            'maatregelen' => [
                'register' => 'De rechtsgrond en de doelen staan in het verwerkingsregister',
                'verklaring' => 'De rechtsgrond en de doelen staan in de privacyverklaring',
            ],
        ],
        [
            'id' => 'r3',
            'titel' => 'Betrokkenen weten het niet',
            'tekst' => 'Betrokkenen weten niet dat hun gegevens voor deze verwerking worden gebruikt. Dat '
                . 'is in strijd met artikel 12 tot en met 14 van de AVG. Zo ontstaat onbegrip. '
                . 'Betrokkenen kunnen dan vragen hun gegevens te wissen, een klacht indienen of naar de '
                . 'pers stappen. Dat kan leiden tot een boete of reputatieschade.',
            'categorieen' => ['A', 'I'],
            'maatregelen' => [
                'verklaring' => 'De privacyverklaring noemt deze verwerking',
                'melding' => 'Betrokkenen krijgen bericht op het moment dat hun gegevens worden verzameld',
            ],
        ],
        [
            'id' => 'r4',
            'titel' => 'Meer gegevens dan nodig',
            'tekst' => 'De verwerking verzamelt meer gegevens dan strikt nodig is. Dat is in strijd met '
                . 'artikel 5, lid 1, onder c van de AVG. Het kan leiden tot onbegrip bij betrokkenen, '
                . 'een boete of reputatieschade.',
            'categorieen' => ['A'],
            'maatregelen' => [
                'per_gegeven' => 'Per gegeven is nagegaan of het echt nodig is',
                'velden' => 'Het systeem vraagt alleen de velden die nodig zijn',
                'pseudonimisering' => 'Waar het kan, worden gegevens gepseudonimiseerd of weggelaten',
            ],
        ],
        [
            'id' => 'r5',
            'titel' => 'Gegevens gedeeld zonder grond',
            'tekst' => 'De gegevens worden gedeeld met andere partijen. Daar is geen wettelijke plicht '
                . 'voor, en het hoort ook niet bij de doelen van de verwerking. Dat kan leiden tot een '
                . 'boete of reputatieschade.',
            'categorieen' => ['A'],
            'maatregelen' => [
                'afspraken' => 'Met elke ontvanger zijn schriftelijke afspraken gemaakt',
                'toets' => 'Gegevens gaan alleen naar buiten als een wet of een doel dat toestaat',
                'vastleggen' => 'Elke verstrekking wordt vastgelegd',
            ],
        ],
        [
            'id' => 'r6',
            'titel' => 'Rechten zijn niet te gebruiken',
            'tekst' => 'Betrokkenen mogen hun gegevens inzien, laten verbeteren en laten wissen. Dat staat '
                . 'in hoofdstuk III van de AVG. Kunnen ze dat niet, dan houdt de organisatie zich niet '
                . 'aan de wet. Betrokkenen kunnen dat melden bij de toezichthouder of naar de pers '
                . 'stappen. Dat kan leiden tot een boete of reputatieschade.',
            'categorieen' => ['I'],
            'maatregelen' => [
                'uitleg' => 'De privacyverklaring legt uit hoe iemand een verzoek indient',
                'systeem' => 'Het systeem kan de gegevens van één persoon opzoeken, verbeteren en wissen',
            ],
        ],
        [
            'id' => 'r7',
            'titel' => 'Verzoek niet op tijd afgehandeld',
            'tekst' => 'Een verzoek van een betrokkene wordt niet of niet op tijd afgehandeld. De termijn '
                . 'is een maand, volgens artikel 12, lid 3 van de AVG. Dat kan leiden tot een boete of '
                . 'reputatieschade.',
            'categorieen' => ['I'],
            'maatregelen' => [
                'register' => 'Verzoeken worden bijgehouden in een register',
                'bewaking' => 'Iemand bewaakt de termijn van een maand',
            ],
        ],
        [
            'id' => 'r8',
            'titel' => 'Datalek wordt niet opgemerkt',
            'tekst' => 'Een datalek in de verwerking wordt niet opgemerkt. Dat is in strijd met artikel 33 '
                . 'van de AVG. Het lek blijft dan open, en niemand beperkt de gevolgen. Dat kan leiden '
                . 'tot schade die vergoed moet worden, een boete of reputatieschade.',
            'categorieen' => ['J'],
            'maatregelen' => [
                'detectie' => 'Beveiligingsincidenten worden automatisch gesignaleerd',
                'kennis' => 'Medewerkers weten wat een datalek is en hoe ze het melden',
                'bewustzijn' => 'Medewerkers worden regelmatig gewezen op privacy en beveiliging',
            ],
        ],
        [
            'id' => 'r9',
            'titel' => 'Datalek wordt niet goed gemeld',
            'tekst' => 'Een datalek wordt niet gemeld, of de melding wordt niet goed afgehandeld. Dat is in '
                . 'strijd met artikel 33 van de AVG. Het kan leiden tot schade die vergoed moet worden, '
                . 'een boete of reputatieschade.',
            'categorieen' => ['J'],
            'maatregelen' => [
                'procedure' => 'Er is een vaste procedure voor het afhandelen van datalekken',
                'samen' => 'De privacyfunctionaris en de beveiligingsfunctionaris handelen meldingen samen af',
                'register' => 'Datalekken worden bijgehouden in een register',
            ],
        ],
        [
            'id' => 'r10',
            'titel' => 'Geen geldige toestemming',
            'tekst' => 'De verwerking rust op toestemming, maar die ontbreekt of is niet geldig. Dat is in '
                . 'strijd met artikel 7 van de AVG. Het kan leiden tot een boete of reputatieschade.',
            'categorieen' => ['K'],
            'toon_als_uitkomst' => ['toestemming' => ['ja']],
            'maatregelen' => [
                'vastleggen' => 'Elke toestemming wordt vastgelegd',
                'intrekken' => 'Intrekken is net zo makkelijk als toestemming geven',
                'weigeren' => 'Wie weigert, kan de dienst toch gebruiken',
            ],
        ],
        [
            'id' => 'r11',
            'titel' => 'Bewaartermijn niet nageleefd',
            'tekst' => 'De gegevens worden langer bewaard dan de bewaartermijn toestaat. Dat is in strijd '
                . 'met artikel 5, lid 1, onder e van de AVG. Het kan leiden tot een boete of '
                . 'reputatieschade.',
            'categorieen' => ['L'],
            'maatregelen' => [
                'vastgesteld' => 'De bewaartermijnen zijn vastgesteld',
                'automatisch' => 'Het systeem vernietigt de gegevens zodra de termijn voorbij is',
                'verklaring' => 'Van elke vernietiging wordt een verklaring gemaakt',
            ],
        ],
        [
            'id' => 'r12',
            'titel' => 'Gegevens gaan buiten de EER',
            'tekst' => 'De gegevens gaan naar een land buiten de Europese Economische Ruimte. Daar gelden '
                . 'niet altijd dezelfde regels. Een buitenlandse overheid kan de gegevens soms opvragen. '
                . 'Hoofdstuk V van de AVG stelt daarom eisen aan zo’n doorgifte. Het kan leiden tot '
                . 'schade voor betrokkenen, een boete of reputatieschade.',
            'categorieen' => ['A', 'H', 'M'],
            'toon_als_uitkomst' => ['doorgifte' => ['ja']],
            'maatregelen' => [
                'overeenkomst' => 'Een verwerkersovereenkomst regelt de doorgifte',
                'scc' => 'Er zijn modelcontractbepalingen van de Europese Commissie afgesloten',
                'dtia' => 'Er is een doorgiftetoets gedaan (DTIA)',
                'sleutels' => 'De gegevens zijn versleuteld met sleutels die bij de organisatie zelf blijven',
            ],
        ],
        [
            'id' => 'r13',
            'titel' => 'Besluiten zonder mens',
            'tekst' => 'Het systeem neemt besluiten over mensen, bereidt ze voor, of maakt profielen van '
                . 'mensen. Daarbij kunnen fouten ontstaan, of onterechte verschillen tussen groepen. '
                . 'Artikel 22 van de AVG beperkt zulke besluiten. Het kan leiden tot ongelijke '
                . 'behandeling, schade voor betrokkenen of reputatieschade.',
            'categorieen' => ['C', 'D', 'G'],
            'toon_als' => ['technieken' => ['besluitvorming', 'profilering']],
            'maatregelen' => [
                'mens' => 'Een mens beoordeelt elk besluit voordat het wordt genomen',
                'uitleg' => 'Betrokkenen horen dat er een automatische stap in het besluit zit, en hoe die werkt',
                'toets' => 'Het systeem wordt getoetst op onterechte verschillen tussen groepen',
            ],
        ],
    ];
}

/** @return array<string,mixed>|null */
function dp_risico(string $id): ?array
{
    foreach (dp_risicos() as $risico) {
        if ($risico['id'] === $id) {
            return $risico;
        }
    }

    return null;
}
