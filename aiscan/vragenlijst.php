<?php

/**
 * Wat de vragenlijst als .odt extra nodig heeft. Deel 2 en 3 zijn een boom:
 * het formulier toont na elk antwoord de volgende vraag die geldt, met
 * ai_route() en ai_stap() in afleiding.php. Op papier staat bij elke vraag
 * wanneer hij geldt, en bij een antwoord dat de scan beëindigt een pijl.
 *
 * De boom staat zo ook hier, in woorden. Verandert ai_stap(), loop dan ook dit
 * bestand na.
 */

declare(strict_types=1);

/**
 * @param callable(string):string $nr het nummer van een vraag, als "vraag 7"
 * @return array<string,mixed>
 */
function ai_vragenlijst(callable $nr): array
{
    $optie = static fn (string $key, string $waarde): string => '‘' . (cm_alle_vragen()[$key]['opties'][$waarde]['label']
        ?? $waarde) . '’';
    $stop = '→ De AI-verordening geldt niet. Sla de rest van dit deel en het deel over de risicogroep over.';
    $markt = 'behalve als jullie hem alleen importeren of doorverkopen (' . $nr('ai_rol') . ')';

    $wanneer = [
        'ai_algoritme' => '',
        'ai_rol' => 'je bij ' . $nr('ai_algoritme') . ' ‘Ja’ koos',
        'ai_status' => 'je bij ' . $nr('ai_rol') . ' niet ' . $optie('ai_rol', 'geen') . ' koos',
        'ai_soort' => 'je bij ' . $nr('ai_rol') . ' niet ' . $optie('ai_rol', 'geen') . ' koos',
        'ai_regels' => 'je bij ' . $nr('ai_soort') . ' ' . $optie('ai_soort', 'twijfel') . ' koos',
        'ai_inferentie' => 'je bij ' . $nr('ai_regels') . ' ‘Nee’ koos',
        'ai_omgeving' => 'je bij ' . $nr('ai_inferentie') . ' ‘Ja’ koos',
        'ai_uitzondering' => 'het een AI-toepassing is: je koos bij ' . $nr('ai_soort') . ' een van de eerste drie, of '
            . 'bij ' . $nr('ai_omgeving') . ' ‘Ja’. Bij alleen een model geldt dit niet als jullie het alleen '
            . 'importeren of doorverkopen',
        'ai_systeemrisico' => 'je bij ' . $nr('ai_soort') . ' ' . $optie('ai_soort', 'model') . ' koos, en bij '
            . $nr('ai_uitzondering') . ' ‘Nee, geen van deze’',
        'ai_toepassing' => 'het geen model is (' . $nr('ai_soort') . '), en je bij ' . $nr('ai_uitzondering')
            . ' ‘Nee, geen van deze’ koos',
        'ai_verboden_uitz' => 'je bij ' . $nr('ai_toepassing') . ' onder Verboden alleen '
            . $optie('ai_toepassing', 'verboden_realtime') . ' aankruiste',
        'ai_bijlage1' => 'je bij ' . $nr('ai_toepassing') . ' niets onder Verboden aankruiste, of bij '
            . $nr('ai_verboden_uitz') . ' een uitzondering koos, ' . $markt,
        'ai_derde' => 'je bij ' . $nr('ai_bijlage1') . ' ' . $optie('ai_bijlage1', 'a') . ' koos, en jullie de '
            . 'toepassing maken of laten maken (' . $nr('ai_rol') . ')',
        'ai_profilering' => 'je bij ' . $nr('ai_bijlage1') . ' ‘Nee’ koos, en bij ' . $nr('ai_toepassing')
            . ' iets onder Hoog risico of de herkenning van emoties aankruiste',
        'ai_taak' => 'je bij ' . $nr('ai_profilering') . ' ‘Nee’ koos',
        'ai_opensource' => 'het risico niet hoog is, ' . $markt . '. Bij alleen een model: als jullie het maken of '
            . 'laten maken, en het niet een van de allergrootste is (' . $nr('ai_systeemrisico') . ')',
        'ai_transparantie_uitz' => 'je bij ' . $nr('ai_toepassing') . ' iets onder Transparantie aankruiste, en het '
            . 'niet verboden is, ' . $markt,
        'risico' => 'de AI-verordening geldt: je kwam in het vorige deel bij ' . $nr('ai_uitzondering')
            . ' uit op ‘Nee, geen van deze’',
    ];

    $stops = [
        'ai_algoritme' => ['nee' => $stop],
        'ai_rol' => ['geen' => $stop],
        'ai_soort' => ['geen' => $stop],
        'ai_regels' => ['ja' => $stop],
        'ai_inferentie' => ['nee' => $stop],
        'ai_omgeving' => ['nee' => $stop],
        'ai_uitzondering' => array_fill_keys(['defensie', 'wetenschap', 'test', 'internationaal'], $stop),
        'ai_verboden_uitz' => ['nee' => '→ De toepassing is verboden. Sla de rest van dit deel over.'],
    ];

    return [
        'inleiding' => [
            ['h3', 'Voor je begint'],
            ['alinea', 'In de delen over de AI-verordening en de risicogroep hangt elke vraag af van je vorige '
                . 'antwoorden. Bij elke vraag staat wanneer hij geldt. Leidt een antwoord naar het einde, dan staat '
                . 'er een pijl bij.'],
            ['alinea', 'Het formulier op de site rekent uit of de AI-verordening geldt, en in welke risicogroep de '
                . 'toepassing valt. Dat staat dan in het rapport.'],
        ],
        'vragen' => array_map(static fn (array $s): array => ['volgende' => $s], $stops),
        'uitkomsten' => static fn (string $naam, array $waarden): string => $wanneer[$naam] ?? '',
    ];
}
