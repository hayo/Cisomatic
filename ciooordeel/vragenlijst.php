<?php

/**
 * Wat de vragenlijst als .odt extra nodig heeft. Het formulier stelt een
 * oordeel voor, met co_voorstel() in afleiding.php. Op papier staat die regel
 * bij de vraag naar het oordeel, en wanneer je moet uitleggen waarom je
 * afwijkt.
 *
 * Verandert co_voorstel(), loop dan ook dit bestand na.
 */

declare(strict_types=1);

/**
 * @param callable(string):string $nr het nummer van een vraag, als "vraag 7"
 * @return array<string,mixed>
 */
function co_vragenlijst(callable $nr): array
{
    $label = static fn (string $o): string => '‘' . CO_OORDELEN[$o]['label'] . '’';

    return [
        'vragen' => [
            'co_oordeel' => ['noten' => [
                'Zo stelt het formulier een oordeel voor. Een gebied is Hoog bij één risico, Midden bij een '
                    . 'aandachtspunt of iets wat niet te beoordelen was, en anders Laag.',
                $label('negatief') . ' als ' . CO_GRENS_NEGATIEF . ' of meer gebieden Hoog zijn. Anders '
                    . $label('voorwaarden') . ' als je ergens ‘Ja, het wordt een voorwaarde’ koos. Anders '
                    . $label('aanbevelingen') . ' als er een aandachtspunt of risico is. Anders ' . $label('positief')
                    . '.',
            ]],
        ],
        'uitkomsten' => static fn (string $naam, array $waarden): string => $naam === 'afwijking'
            ? 'je bij ' . $nr('co_oordeel') . ' een ander oordeel koos dan de regel daar voorstelt'
            : '',
    ];
}
