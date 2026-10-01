<?php

/**
 * Wegschrijven van het document naar de map uit 'bewaar_map', als die aan staat.
 */

declare(strict_types=1);

/**
 * Slaat het document op als .odt-bestand, met daarnaast een JSON-bestand met de
 * ruwe antwoorden, zodat het later opnieuw te lezen of te vergelijken is.
 *
 * @param list<array<int,mixed>> $blokken
 * @param array<string,mixed> $antwoorden
 * @param array<string,mixed> $uitkomst
 * @return array{bestandsnaam:string,pad:string}|null null als bewaren uit staat
 *
 * @throws RuntimeException als de map niet te maken of niet beschrijfbaar is
 */
function cm_bewaar(array $blokken, array $antwoorden, array $uitkomst): ?array
{
    $map = cm_config()['bewaar_map'] ?? null;

    if ($map === null) {
        return null;
    }

    $map = (string) $map;

    if (!is_dir($map) && !mkdir($map, 0775, true) && !is_dir($map)) {
        throw new RuntimeException('De map ' . $map . ' bestaat niet en kan niet worden aangemaakt.');
    }

    if (!is_writable($map)) {
        throw new RuntimeException('De map ' . $map . ' is niet beschrijfbaar.');
    }

    // Met de tijd erbij, zodat twee keer op één dag geen bestand overschrijft.
    $moment = new DateTimeImmutable('now');
    $basis = $moment->format('Y-m-d_H-i-s') . '_' . cm_formulier()['id'] . '_'
        . cm_slug((string) ($antwoorden['projectnaam'] ?? ''));
    $pad = $map . '/' . $basis . '.odt';

    if (file_put_contents($pad, cm_odt($blokken, (string) ($antwoorden['aanvrager'] ?? '')), LOCK_EX) === false) {
        throw new RuntimeException('Het document kon niet worden weggeschreven naar ' . $pad . '.');
    }

    $json = json_encode([
        'soort'         => cm_formulier()['id'],
        'opgeslagen_op' => $moment->format(DATE_ATOM),
        'projectnaam'   => $antwoorden['projectnaam'] ?? '',
        'antwoorden'    => $antwoorden,
        'uitkomst'      => $uitkomst,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($json !== false) {
        file_put_contents($map . '/' . $basis . '.json', $json, LOCK_EX);
    }

    return ['bestandsnaam' => $basis . '.odt', 'pad' => $pad];
}
