<?php

/**
 * Inlezen, schoonmaken en controleren van de antwoorden.
 */

declare(strict_types=1);

/**
 * De secties van het formulier. Eén keer opgebouwd per verzoek.
 *
 * @return list<array<string,mixed>>
 */
function cm_secties(): array
{
    static $secties = null;

    return $secties ??= cm_formulier()['secties']();
}

/** Alle vragen als platte lijst, gesleuteld op key. @return array<string,array<string,mixed>> */
function cm_alle_vragen(): array
{
    static $vragen = null;

    if ($vragen === null) {
        $vragen = [];
        foreach (cm_secties() as $sectie) {
            foreach ($sectie['vragen'] as $vraag) {
                $vragen[$vraag['key']] = $vraag;
            }
        }
    }

    return $vragen;
}

/**
 * Iets wat het formulier uitrekent, zoals de omvang van een risico. Daar
 * letten toon_als_uitkomst en verplicht_als_uitkomst op.
 */
function cm_uitkomst(string $naam, array $a): string
{
    $uitkomst = cm_formulier()['uitkomst'] ?? null;

    return $uitkomst === null ? '' : (string) $uitkomst($naam, $a);
}

/**
 * Of antwoorden aan een set voorwaarden voldoen. Elke sleutel moet kloppen;
 * bij een lijst antwoorden is één passende waarde genoeg.
 *
 * @param array<string,mixed> $regels sleutel => toegestane waarden
 * @param callable(string):mixed $waardeVan
 */
function cm_matcht(array $regels, callable $waardeVan): bool
{
    foreach ($regels as $key => $toegestaan) {
        $toegestaan = array_map('strval', (array) $toegestaan);
        $waarde = $waardeVan((string) $key);

        $past = is_array($waarde)
            ? array_intersect(array_map('strval', $waarde), $toegestaan) !== []
            : in_array((string) $waarde, $toegestaan, true);

        if (!$past) {
            return false;
        }
    }

    return true;
}

/**
 * Of een sectie, vraag of lijstitem van toepassing is. Voorwaarden verwijzen
 * altijd naar eerdere vragen, dus één doorloop op volgorde volstaat.
 *
 * @param array<string,mixed> $item
 */
function cm_zichtbaar(array $item, array $a): bool
{
    return cm_matcht($item['toon_als'] ?? [], static fn (string $k): mixed => $a[$k] ?? null)
        && cm_matcht($item['toon_als_uitkomst'] ?? [], static fn (string $k): string => cm_uitkomst($k, $a));
}

function cm_vraag_verplicht(array $vraag, array $a): bool
{
    return !empty($vraag['verplicht'])
        || (!empty($vraag['verplicht_als'])
            && cm_matcht($vraag['verplicht_als'], static fn (string $k): mixed => $a[$k] ?? null))
        || (!empty($vraag['verplicht_als_uitkomst'])
            && cm_matcht($vraag['verplicht_als_uitkomst'], static fn (string $k): string => cm_uitkomst($k, $a)));
}

/** Geen invoer: een kop of een uitkomstblok. */
function cm_is_invoer(array $vraag): bool
{
    return !in_array($vraag['type'], ['afgeleid', 'kop'], true);
}

/**
 * Zet de ruwe invoer om in een schone antwoordenset. Vragen die niet van
 * toepassing zijn, worden leeggemaakt, zodat een verborgen antwoord nooit in
 * het document komt. Een concept houdt ze wel, zodat heen en weer klikken
 * niets wist.
 *
 * @param array<string,mixed> $post
 * @return array<string,mixed>
 */
function cm_lees_antwoorden(array $post, bool $concept = false): array
{
    $a = [];

    foreach (cm_secties() as $sectie) {
        $sectieZichtbaar = cm_zichtbaar($sectie, $a);

        foreach ($sectie['vragen'] as $vraag) {
            if (!cm_is_invoer($vraag)) {
                continue;
            }

            $key = $vraag['key'];

            if (!$concept && (!$sectieZichtbaar || !cm_zichtbaar($vraag, $a))) {
                $a[$key] = in_array($vraag['type'], ['checkbox', 'rijen', 'maatregelen'], true) ? [] : '';
                continue;
            }

            $ruw = $post[$key] ?? null;

            $a[$key] = match ($vraag['type']) {
                'checkbox' => cm_lees_keuzes($vraag, $ruw, $a),
                'maatregelen' => cm_lees_maatregelen($vraag, $ruw),
                'rijen' => cm_lees_rijen($vraag, $ruw),
                default => cm_lees_waarde($vraag, $ruw),
            };
        }
    }

    return $a;
}

/** Eén waarde: ingekort, met nette regeleinden, en bij een keuze alleen wat bestaat. */
function cm_lees_waarde(array $vraag, mixed $ruw): string
{
    $max = (int) (cm_config()['max_veldlengte'] ?? 20000);
    $waarde = str_replace(["\r\n", "\r"], "\n", trim(is_string($ruw) ? $ruw : ''));
    $waarde = mb_substr($waarde, 0, $max, 'UTF-8');

    if (in_array($vraag['type'], ['radio', 'select'], true)) {
        return array_key_exists($waarde, $vraag['opties'] ?? []) ? $waarde : '';
    }

    return $waarde;
}

/**
 * Aangekruiste opties in de volgorde van de lijst. Met opties_uit blijven
 * alleen de opties over die in die andere vraag zijn aangekruist.
 *
 * @return list<string>
 */
function cm_lees_keuzes(array $vraag, mixed $ruw, array $a): array
{
    $geldig = array_map('strval', array_keys($vraag['opties'] ?? []));

    if (!empty($vraag['opties_uit'])) {
        $geldig = array_intersect($geldig, (array) ($a[$vraag['opties_uit']] ?? []));
    }

    return array_values(array_intersect($geldig, array_map('strval', is_array($ruw) ? $ruw : [])));
}

/**
 * Per maatregel de keuze, in de volgorde van de lijst. Een maatregel zonder
 * keuze staat er niet in.
 *
 * @return array<string,string>
 */
function cm_lees_maatregelen(array $vraag, mixed $ruw): array
{
    $keuzes = [];

    foreach (array_keys($vraag['opties']) as $maatregel) {
        $keuze = is_array($ruw) ? ($ruw[$maatregel] ?? '') : '';
        if (is_string($keuze) && isset(CM_MAATREGELKEUZES[$keuze])) {
            $keuzes[$maatregel] = $keuze;
        }
    }

    return $keuzes;
}

/**
 * Rijen: elke kolom gelezen als een losse vraag. Een rij zonder tekst telt niet.
 *
 * @return list<array<string,string>>
 */
function cm_lees_rijen(array $vraag, mixed $ruw): array
{
    $max = (int) (cm_config()['max_rijen'] ?? 40);
    $rijen = [];

    foreach (array_slice(is_array($ruw) ? array_values($ruw) : [], 0, $max) as $ruweRij) {
        $rij = [];
        $gevuld = false;

        foreach ($vraag['kolommen'] as $kolom => $def) {
            $rij[$kolom] = cm_lees_waarde($def, is_array($ruweRij) ? ($ruweRij[$kolom] ?? null) : null);
            $gevuld = $gevuld || ($def['type'] !== 'select' && $rij[$kolom] !== '');
        }

        if ($gevuld) {
            $rijen[] = $rij;
        }
    }

    return $rijen;
}

/**
 * Controleert de invulplicht. Alleen vragen die van toepassing zijn tellen.
 * Daarna komen de controles van het formulier zelf.
 *
 * @return array<string,string> foutmelding per vraagsleutel
 */
function cm_valideer(array $a): array
{
    $fouten = [];

    foreach (cm_secties() as $sectie) {
        if (!cm_zichtbaar($sectie, $a)) {
            continue;
        }

        foreach ($sectie['vragen'] as $vraag) {
            if (!cm_is_invoer($vraag) || !cm_zichtbaar($vraag, $a)) {
                continue;
            }

            $key = $vraag['key'];
            $waarde = $a[$key] ?? '';

            if (cm_is_leeg($vraag, $waarde) && cm_vraag_verplicht($vraag, $a)) {
                $fouten[$key] = match ($vraag['type']) {
                    'rijen' => 'Vul minstens één rij in.',
                    'maatregelen' => 'Bij deze omvang is een nieuwe maatregel nodig. Kies bij minstens één '
                        . 'maatregel ‘Komt er’, of vul er zelf een in bij ‘Andere maatregelen’.',
                    'radio', 'select', 'checkbox' => 'Maak hier een keuze.',
                    default => 'Dit veld is nog leeg.',
                };
                continue;
            }

            if ($vraag['type'] === 'rijen') {
                $melding = cm_rij_fout($vraag, $waarde);
                if ($melding !== null) {
                    $fouten[$key] = $melding;
                }
            }

            if ($vraag['type'] === 'email' && $waarde !== '' && !filter_var($waarde, FILTER_VALIDATE_EMAIL)) {
                $fouten[$key] = 'Dit lijkt geen geldig mailadres.';
            }
        }
    }

    $eigen = cm_formulier()['valideer'] ?? null;

    return $eigen === null ? $fouten : $fouten + $eigen($a);
}

/** Bij maatregelen telt als ingevuld: minstens één maatregel die er komt. */
function cm_is_leeg(array $vraag, mixed $waarde): bool
{
    return $vraag['type'] === 'maatregelen'
        ? !in_array('komt', (array) $waarde, true)
        : (is_array($waarde) ? $waarde === [] : $waarde === '');
}

/**
 * Hoe ver een formulier is: de verplichte vragen die van toepassing zijn, en
 * hoeveel daarvan ingevuld zijn. Het totaal groeit mee met de antwoorden,
 * want een keuze kan nieuwe vragen verplicht maken. Klaar betekent dat het
 * document gemaakt kan worden.
 *
 * @param array<string,mixed> $a schone antwoorden, uit cm_lees_antwoorden() zonder concept
 * @return array{ingevuld:int,verplicht:int,klaar:bool}
 */
function cm_voortgang(array $a): array
{
    $verplicht = 0;
    $ingevuld = 0;

    foreach (cm_secties() as $sectie) {
        if (!cm_zichtbaar($sectie, $a)) {
            continue;
        }

        foreach ($sectie['vragen'] as $vraag) {
            if (cm_is_invoer($vraag) && cm_zichtbaar($vraag, $a) && cm_vraag_verplicht($vraag, $a)) {
                $verplicht++;
                $ingevuld += cm_is_leeg($vraag, $a[$vraag['key']] ?? '') ? 0 : 1;
            }
        }
    }

    return ['ingevuld' => $ingevuld, 'verplicht' => $verplicht, 'klaar' => cm_valideer($a) === []];
}

/** De eerste rij waarin een verplichte kolom leeg is. */
function cm_rij_fout(array $vraag, array $rijen): ?string
{
    foreach ($rijen as $nummer => $rij) {
        foreach ($vraag['kolommen'] as $kolom => $def) {
            if (!empty($def['verplicht']) && ($rij[$kolom] ?? '') === '') {
                return sprintf(
                    '%s %d is nog niet compleet. Vul ook ‘%s’ in.',
                    $vraag['rij_label'],
                    $nummer + 1,
                    $def['label']
                );
            }
        }
    }

    return null;
}

/** Leesbaar antwoord: het label in plaats van de sleutel. */
function cm_antwoord_tekst(string $key, array $a): string
{
    $vraag = cm_alle_vragen()[$key] ?? null;
    $waarde = $a[$key] ?? '';

    if ($vraag === null || !isset($vraag['opties'])) {
        return is_array($waarde) ? '' : (string) $waarde;
    }

    if (is_array($waarde)) {
        return cm_opsomming(array_map(
            static fn (string $w): string => (string) ($vraag['opties'][$w]['label'] ?? $w),
            $waarde
        ));
    }

    return (string) ($vraag['opties'][$waarde]['label'] ?? $waarde);
}
