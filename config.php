<?php

/**
 * Instellingen. Er staan geen geheimen in, dus dit bestand hoort gewoon in git.
 */

declare(strict_types=1);

return [
    // De formulieren, in de volgorde van de voordeur. Elk is een map naast src/.
    'formulieren' => ['businesscase', 'quickscan2', 'socialemedia', 'quickscan', 'dpia', 'aiscan', 'iama', 'soevereiniteit', 'ciooordeel'],

    // Tijdzone voor de datum in het document en in de bestandsnaam.
    'timezone' => 'Europe/Amsterdam',

    // Langste antwoord per veld, in tekens.
    'max_veldlengte' => 20000,

    // Meeste rijen in een lijst, zoals de gegevensstromen of de eigen risico's.
    'max_rijen' => 40,

    // Map waarin elk document als bestand wordt bewaard, bijvoorbeeld
    // __DIR__ . '/bewaard'. Bij null bewaart de server niets, en downloadt de
    // invuller het document zelf.
    'bewaar_map' => null,

    // Het logo bovenaan elke pagina van het .odt-bestand, tegen de bovenrand.
    // Een PNG of JPEG, bijvoorbeeld ['bestand' => __DIR__ . '/img/logo.png',
    // 'breedte' => 5.5]. De breedte is in centimeters. 'midden' is de afstand
    // van de linkerrand van het beeld tot het midden van het lint, ook in
    // centimeters; dat punt komt op het midden van de pagina. Zonder 'midden'
    // staat het hele beeld in het midden. Bij null heeft het document geen logo.
    'logo' => null,

    // De organisatie waarvoor de business case toetst of iets haar taak is. De
    // taken staan in 'besluit'; de diensten eronder volgen daaruit. Bij null
    // vraagt het formulier alleen om een toelichting.
    'organisatie' => [
        'naam' => 'DPC',
        'besluit' => 'artikel 11 van het Organisatiebesluit Ministerie van Algemene Zaken',
        'taken' => [
            'a' => [
                'taak' => 'a. De rijksoverheid helpen om samen beter te communiceren met publiek en professionals',
                'diensten' => [
                    'academie' => 'Academie voor Overheidscommunicatie: opleiding en kennis delen',
                    'visueel' => 'Visuele communicatie: advies en richtlijnen voor beeld',
                    'campagnes' => 'Campagnemanagement',
                    'onderzoek' => 'Communicatieonderzoek',
                    'capaciteit' => 'Communicatiecapaciteit: tijdelijke communicatieprofessionals',
                ],
            ],
            'b' => [
                'taak' => 'b. Gezamenlijke voorzieningen voor de overheidscommunicatie bouwen en onderhouden',
                'diensten' => [
                    'online' => 'Online: websites, apps, nieuwsbrieven en webarchivering',
                    'redactie' => 'Redactie en vraagbeantwoording, met Informatie Rijksoverheid (1400)',
                    'rijksportaal' => 'Rijksportaal: het intranet van het Rijk',
                ],
            ],
            'c' => [
                'taak' => 'c. Voor het hele Rijk communicatie inkopen, en daarover adviseren',
                'diensten' => [
                    'inkoopcentrum' => 'Specialistisch Inkoopcentrum Communicatie: raamcontracten voor foto, video, media en inhuur',
                    'mediainkoop' => 'Mediainkoop: advertenties en reclame op radio en tv',
                ],
            ],
        ],
    ],
];
