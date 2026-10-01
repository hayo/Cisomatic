<?php

/**
 * De rijksbrede platformkaders: per platform het advies over inzet, de
 * doelgroep, de risico's, en de ruimte per soort gebruik. Organisch gebruik
 * toetst aan 'doelen' en 'vormen', betaald gebruik aan 'betaald', en contact
 * aan 'contact'. Wat daar niet in staat, valt erbuiten.
 *
 * De kaders worden elk jaar herzien. Dit is het bestand om dan na te lopen.
 * De vlaggen:
 * - explain: het kader vraagt voor dit platform altijd een explain
 * - toestel: de app mag niet op apparatuur van het Rijk
 * En 'bewindspersoon': mag een bewindspersoon hier een account hebben (ja, nee,
 * of verboden, dan is het niet inzetten).
 */

declare(strict_types=1);

const SM_DOELEN = [
    'informeren' => ['label' => 'Informeren', 'hint' => 'Volledige informatie over beleid of diensten.'],
    'attenderen' => ['label' => 'Attenderen', 'hint' => 'Kort wijzen op nieuws, met een link naar meer.'],
    'duiden' => ['label' => 'Duiden', 'hint' => 'Uitleggen waarom iets gebeurt.'],
    'publieksvoorlichting' => ['label' => 'Publieksvoorlichting', 'hint' => 'Het brede publiek voorlichten over regels en rechten.'],
    'activeren' => ['label' => 'Activeren', 'hint' => 'Mensen iets laten doen, zoals zich aanmelden.'],
    'gedrag' => ['label' => 'Gedragsbeïnvloeding', 'hint' => 'Mensen helpen iets anders te doen, zoals veiliger rijden.'],
    'arbeidsmarkt' => ['label' => 'Arbeidsmarktcommunicatie', 'hint' => 'Nieuwe medewerkers werven.'],
    'bereik' => ['label' => 'Bereik vergroten', 'hint' => 'Meer mensen laten zien wat de overheid doet.'],
    'communitymanagement' => ['label' => 'Communitymanagement', 'hint' => 'Een groep volgers betrekken en bij elkaar houden.'],
    'stakeholdermanagement' => ['label' => 'Stakeholdermanagement', 'hint' => 'Contact met organisaties en partners.'],
    'internationaal' => ['label' => 'Internationale betrekkingen'],
    'monitoring' => ['label' => 'Monitoring', 'hint' => 'Volgen wat er over een onderwerp gezegd wordt.'],
];

const SM_VORMEN = [
    'publiekscampagnes' => ['label' => 'Publiekscampagnes', 'hint' => 'Zonder advertenties.'],
    'arbeidsmarktcampagnes' => ['label' => 'Arbeidsmarktcampagnes', 'hint' => 'Zonder advertenties.'],
    'crisis' => ['label' => 'Crisiscommunicatie'],
    'pers' => ['label' => 'Persvoorlichting'],
    'persconferenties' => ['label' => 'Persconferenties'],
    'corporate' => ['label' => 'Corporate communicatie', 'hint' => 'Over de organisatie zelf.'],
    'stakeholder' => ['label' => 'Stakeholdercommunicatie'],
    'explainers' => ['label' => 'Explainers', 'hint' => 'Korte uitleg in beeld of video. Ook Q&A’s over veelgestelde vragen.'],
    'instructie' => ['label' => 'Instructievideo’s', 'hint' => 'Ook educatieve video’s.'],
];

/** De drie soorten gebruik. Per soort volgen eigen vragen en een eigen ruimte. */
const SM_GEBRUIK = [
    'organisch' => ['label' => 'Berichten plaatsen', 'hint' => 'Organisch gebruik: zonder te betalen voor bereik.'],
    'betaald' => ['label' => 'Adverteren', 'hint' => 'Betaald gebruik: advertenties, en berichten promoten.'],
    'contact' => ['label' => 'Vragen beantwoorden', 'hint' => 'Persoonlijk contact: reacties, privéberichten en webcare.'],
];

const SM_BETAALD = [
    'publiekscampagnes' => ['label' => 'Publiekscampagnes met advertenties'],
    'arbeidsmarktcampagnes' => ['label' => 'Wervingscampagnes met advertenties'],
    'gedrag' => ['label' => 'Gedragscampagnes met advertenties', 'hint' => 'Mensen helpen iets anders te doen.'],
    'bereik' => ['label' => 'Berichten promoten voor meer bereik'],
];

const SM_CONTACT = [
    'reacties' => ['label' => 'Reageren op reacties onder je berichten', 'hint' => 'Openbaar, in gesprek met volgers.'],
    'vragen' => ['label' => 'Algemene vragen beantwoorden', 'hint' => 'Zonder persoonlijke gegevens, openbaar of in een privébericht.'],
    'persoonlijk' => ['label' => 'Persoonlijke vragen behandelen (webcare)', 'hint' => 'Vragen over iemands eigen zaak, vaak met persoonlijke gegevens.'],
];

/** Voor een platform uit een land met een offensief cyberprogramma tegen Nederland. */
const SM_OFFENSIEF = 'Het bedrijf zit in een land met een offensief cyberprogramma tegen Nederland. Dan gelden dezelfde '
    . 'regels als voor TikTok: de app mag niet op apparatuur van het Rijk.';

const SM_BUITEN_EU = 'Het bedrijf zit buiten de EU. Gegevens gaan dan naar een land buiten de EU, vaak zonder goede '
    . 'bescherming.';

/** Het advies per soort inzet, met het niveau voor de kleur. */
const SM_INZET = [
    'ja' => ['label' => 'In beginsel inzetbaar', 'niveau' => 'm'],
    'nee' => ['label' => 'In beginsel niet inzetbaar', 'niveau' => 'h'],
    'primair' => ['label' => 'Geadviseerd als eerste kanaal', 'niveau' => 'l'],
    'geen' => ['label' => 'Geen rijksbreed kader', 'niveau' => 'h'],
];

/** @return array<string,array<string,mixed>> */
function sm_kaders(): array
{
    $metaPrivacy = 'Meta gebruikt persoonsgegevens voor eigen doelen, zoals profielen en het trainen van AI. '
        . 'Gegevens gaan naar landen buiten de EU.';
    $metaDsa = 'Meta voldoet nog niet aan de moderatieregels van de Digital Services Act (DSA). Melden van '
        . 'schadelijke berichten is niet eenvoudig.';

    return [
        'facebook' => [
            'naam' => 'Facebook',
            'inzet' => 'nee',
            'explain' => true,
            'uitleg' => 'Facebook is in beginsel niet inzetbaar. Het kan alleen als uitzondering: de doelgroep is '
                . 'aantoonbaar belangrijk, en een minder risicovol kanaal bereikt haar niet goed. Denk aan Caribisch '
                . 'Nederland of Nederlanders in het buitenland. Facebook is niet geschikt voor dienstverlening.',
            'doelgroep' => 'Vooral volwassenen en ouderen, gezinnen en lokale gemeenschappen. Ook mensen voor wie '
                . 'Facebook de eerste bron van nieuws is, vooral in Caribisch Nederland en het buitenland. Minder '
                . 'geschikt voor jongeren.',
            'risicos' => [
                'Een DPIA van het Rijk vond zeven hoge privacyrisico’s bij Facebookpagina’s. Zonder goede waarborgen '
                    . 'kan de Autoriteit Persoonsgegevens optreden.',
                'Een HRIA vond een hoge mogelijke impact op minstens negen mensenrechten.',
                $metaDsa,
                'Het algoritme stuurt op aandacht. Dat geeft een hoog risico op desinformatie en filterbubbels.',
                'Zonder account is weinig te zien, en de overheid heeft weinig invloed op het platform.',
            ],
            'doelen' => ['activeren', 'arbeidsmarkt', 'attenderen', 'communitymanagement', 'duiden', 'informeren',
                'gedrag', 'publieksvoorlichting', 'stakeholdermanagement'],
            'vormen' => ['arbeidsmarktcampagnes', 'crisis', 'publiekscampagnes', 'stakeholder'],
            'bewindspersoon' => 'nee',
            'betaald' => ['publiekscampagnes', 'arbeidsmarktcampagnes', 'gedrag'],
            'contact' => [],
        ],
        'instagram' => [
            'naam' => 'Instagram',
            'inzet' => 'ja',
            'uitleg' => 'Instagram is in beginsel inzetbaar, vooral om brede groepen met beeld te bereiken. Denk aan '
                . 'voorlichting, campagnes en werkbezoeken. Het is minder geschikt voor ingewikkelde uitleg over '
                . 'beleid en voor crisiscommunicatie.',
            'doelgroep' => 'Een grote groep die elke dag terugkomt, vooral millennials, Gen Z en jonge gezinnen. '
                . 'Minder geschikt voor specialistische of zakelijke doelgroepen.',
            'risicos' => [
                $metaPrivacy,
                'Een HRIA op de infrastructuur van Meta vond een hoog risico voor mensenrechten.',
                $metaDsa,
                'Het algoritme geeft een hoog risico op verslaving, desinformatie en filterbubbels.',
            ],
            'doelen' => ['activeren', 'arbeidsmarkt', 'attenderen', 'communitymanagement', 'duiden', 'informeren',
                'internationaal', 'gedrag', 'publieksvoorlichting', 'bereik'],
            'vormen' => ['arbeidsmarktcampagnes', 'explainers', 'publiekscampagnes'],
            'bewindspersoon' => 'ja',
            'betaald' => ['publiekscampagnes', 'arbeidsmarktcampagnes', 'gedrag', 'bereik'],
            'contact' => ['reacties', 'vragen'],
        ],
        'x' => [
            'naam' => 'X',
            'hint' => 'Vroeger Twitter.',
            'inzet' => 'nee',
            'explain' => true,
            'uitleg' => 'X is in beginsel niet inzetbaar. Het kan beperkt, om journalisten, politici, '
                . 'beleidsmakers en internationale partners te bereiken. Denk aan korte attenderingen, '
                . 'persvoorlichting en internationale communicatie. X is niet bedoeld voor brede voorlichting.',
            'doelgroep' => 'Een kleine, politiek betrokken groep: journalisten, politici, bestuurders, '
                . 'beleidsmakers, belangenorganisaties en mensen die het nieuws op de voet volgen. Weinig jongeren, '
                . 'en geen goede afspiegeling van Nederland.',
            'risicos' => [
                'De Europese Commissie beboette X, omdat het niet voldoet aan de DSA. Er lopen meer onderzoeken, '
                    . 'onder meer naar de AI tool Grok.',
                'Er is meer haat, desinformatie en ander schadelijk materiaal, ook onder berichten van de overheid.',
                'De moderatie is sinds 2022 sterk afgenomen.',
                'Het bereik onder de bevolking daalt.',
            ],
            'doelen' => ['attenderen', 'internationaal', 'monitoring'],
            'vormen' => ['crisis', 'pers'],
            'bewindspersoon' => 'ja',
            'betaald' => [],
            'contact' => [],
        ],
        'tiktok' => [
            'naam' => 'TikTok',
            'inzet' => 'nee',
            'explain' => true,
            'toestel' => true,
            'uitleg' => 'TikTok is in beginsel niet inzetbaar. Het kan beperkt, om jongeren en jongvolwassenen te '
                . 'bereiken met campagnes, werving of gedragsbeïnvloeding. Dat mag alleen als een minder risicovol '
                . 'kanaal hen niet goed bereikt. Het is niet voor bewindspersonen, crisiscommunicatie, ingewikkelde '
                . 'uitleg of dienstverlening.',
            'doelgroep' => 'Vooral jongeren en jongvolwassenen, ook groepen die de overheid op andere manieren moeilijk '
                . 'bereikt. Minder geschikt voor ouderen en professionals.',
            'risicos' => [
                'TikTok mag niet op telefoons, tablets en computers van het Rijk. Het bedrijf zit in een land met een '
                    . 'offensief cyberprogramma tegen Nederland.',
                'De Europese Commissie stelde voorlopig vast dat TikTok de DSA schendt, door een verslavend ontwerp.',
                'Er zijn zorgen over wat TikTok met persoonsgegevens doet, zeker van kinderen.',
            ],
            'doelen' => ['activeren', 'arbeidsmarkt', 'attenderen', 'gedrag', 'publieksvoorlichting'],
            'vormen' => ['arbeidsmarktcampagnes', 'explainers', 'publiekscampagnes'],
            'bewindspersoon' => 'verboden',
            'betaald' => ['publiekscampagnes', 'arbeidsmarktcampagnes', 'gedrag'],
            'contact' => [],
        ],
        'youtube' => [
            'naam' => 'YouTube',
            'inzet' => 'ja',
            'uitleg' => 'YouTube is in beginsel inzetbaar voor voorlichting en uitleg over beleid, met beeld en geluid. '
                . 'Denk aan persconferenties, campagnes, explainers, instructies en Q&A’s. Het is niet geschikt voor '
                . 'crisiscommunicatie en dienstverlening.',
            'doelgroep' => 'Bijna iedereen in Nederland, van alle leeftijden. Ook groepen die de overheid op andere '
                . 'manieren moeilijk bereikt.',
            'risicos' => [
                'Het algoritme bepaalt wie een video ziet. Dat kan leiden tot filterbubbels en radicalisering.',
                'Er zijn zorgen over wat Google met persoonsgegevens doet, zoals profielen en het trainen van AI.',
                'Voor archiveren zijn extra voorzieningen nodig.',
            ],
            'doelen' => ['activeren', 'arbeidsmarkt', 'attenderen', 'duiden', 'gedrag', 'informeren',
                'publieksvoorlichting', 'bereik'],
            'vormen' => ['arbeidsmarktcampagnes', 'explainers', 'instructie', 'persconferenties', 'publiekscampagnes'],
            'bewindspersoon' => 'nee',
            'betaald' => ['publiekscampagnes', 'arbeidsmarktcampagnes', 'gedrag', 'bereik'],
            'contact' => [],
        ],
        'linkedin' => [
            'naam' => 'LinkedIn',
            'inzet' => 'ja',
            'uitleg' => 'LinkedIn is in beginsel inzetbaar voor professionals: werving, corporate communicatie, uitleg '
                . 'over beleid en contact met stakeholders. Het is niet geschikt voor brede publieksvoorlichting en '
                . 'dienstverlening.',
            'doelgroep' => 'Werkenden, werkgevers, ondernemers, beleidsmakers, bestuurders en werkzoekenden. Minder '
                . 'geschikt om een brede afspiegeling van de bevolking te bereiken.',
            'risicos' => [
                'Er zijn zorgen over wat het platform met persoonsgegevens doet, zoals profielen en het trainen van AI.',
                'Het algoritme bepaalt wie een bericht ziet.',
                'Het netwerk maakt medewerkers een doelwit voor social engineering.',
            ],
            'doelen' => ['activeren', 'arbeidsmarkt', 'attenderen', 'duiden', 'informeren', 'stakeholdermanagement',
                'bereik'],
            'vormen' => ['arbeidsmarktcampagnes', 'corporate', 'stakeholder'],
            'bewindspersoon' => 'ja',
            'betaald' => ['arbeidsmarktcampagnes', 'bereik'],
            'contact' => ['reacties', 'vragen'],
        ],
        'mastodon' => [
            'naam' => 'social.overheid.nl',
            'label' => 'Mastodon, op social.overheid.nl',
            'hint' => 'De server van de overheid.',
            'inzet' => 'primair',
            'uitleg' => 'social.overheid.nl is geschikt als eerste, betrouwbare bron voor overheidsberichten op '
                . 'sociale media. Denk aan attenderingen en persvoorlichting. Door het kleine bereik is het nog niet '
                . 'geschikt voor brede voorlichting, campagnes of werving.',
            'doelgroep' => 'Een kleine groep die digitaal vaardig en politiek betrokken is. Vaak mensen die grote '
                . 'commerciële platforms mijden.',
            'risicos' => [
                'De risico’s zijn beperkt. De server is van de overheid, er is geen tracking en geen algoritme, en '
                    . 'berichten zijn zonder account te lezen.',
                'Andere servers in het netwerk modereren zelf. Wat daar gebeurt, bepaal je niet.',
            ],
            'doelen' => ['attenderen', 'duiden', 'informeren', 'internationaal', 'stakeholdermanagement'],
            'vormen' => ['explainers', 'pers', 'stakeholder'],
            'bewindspersoon' => 'ja',
            'betaald' => [],
            'contact' => ['reacties', 'vragen', 'persoonlijk'],
        ],
        'anders' => [
            'naam' => 'Een ander kanaal',
            'hint' => 'Zoals WhatsApp, Bluesky of Threads.',
            'inzet' => 'geen',
            'explain' => true,
            'uitleg' => 'Voor dit kanaal is er geen rijksbreed kader. Werk daarom altijd een explain uit, en vraag '
                . 'advies aan je CPO en CISO.',
            'doelgroep' => 'De tool weet niet wie dit kanaal bereikt.',
            'risicos' => ['De tool kent de risico’s van dit platform niet. Zoek ze uit met je CPO en CISO.'],
            'doelen' => null,
            'vormen' => null,
            'bewindspersoon' => null,
            'betaald' => null,
            'contact' => null,
        ],
    ];
}

/** Het kader van het gekozen platform, met de naam die de invuller gaf bij een ander kanaal. */
function sm_kader(array $a): ?array
{
    $k = (string) ($a['sm_platform'] ?? '');
    $kader = sm_kaders()[$k] ?? null;
    if ($kader === null) {
        return null;
    }

    if ($k === 'anders' && trim((string) ($a['sm_anders_naam'] ?? '')) !== '') {
        $kader['naam'] = trim((string) $a['sm_anders_naam']);
    }
    if ($k === 'anders' && ($a['sm_anders_land'] ?? '') === 'offensief') {
        $kader['toestel'] = true;
        $kader['risicos'][] = SM_OFFENSIEF;
    }
    if ($k === 'anders' && ($a['sm_anders_land'] ?? '') === 'buiten') {
        $kader['risicos'][] = SM_BUITEN_EU;
    }

    return $kader + ['explain' => false, 'toestel' => false];
}

/**
 * Wat regels.js nodig heeft om hetzelfde uit te rekenen. De motor zet dit als
 * data-groepen op de vraag naar het platform.
 *
 * @return array<string,mixed>
 */
function sm_regelgegevens(): array
{
    return [
        'kaders' => array_map(
            static fn (array $k): array => $k + ['explain' => false, 'toestel' => false],
            sm_kaders()
        ),
        'inzet' => SM_INZET,
        'doelen' => array_map(static fn (array $d): string => $d['label'], SM_DOELEN),
        'vormen' => array_map(static fn (array $v): string => $v['label'], SM_VORMEN),
        'betaald' => array_map(static fn (array $v): string => $v['label'], SM_BETAALD),
        'contact' => array_map(static fn (array $v): string => $v['label'], SM_CONTACT),
        'toetsen' => SM_TOETSEN,
        'uitkomsten' => SM_UITKOMSTEN,
        'teksten' => SM_TEKSTEN,
        'land' => ['offensief' => SM_OFFENSIEF, 'buiten' => SM_BUITEN_EU],
        'exit' => [
            'teksten' => SM_EXIT_TEKSTEN,
            'aanpak' => array_map(static fn (array $o): string => $o['kort'], SM_EXIT_AANPAK),
            'type' => array_map(static fn (array $o): string => $o['kort'], SM_EXIT_TYPE),
        ],
    ];
}
