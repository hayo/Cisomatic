<?php

declare(strict_types=1);

/** De vijf niveaus, van Zeer laag tot Zeer hoog. */
const CM_NIVEAUS = ['zl' => 'Zeer laag', 'l' => 'Laag', 'm' => 'Midden', 'h' => 'Hoog', 'zh' => 'Zeer hoog'];

/** De keuzes per maatregel in een vraag van het type maatregelen. */
const CM_MAATREGELKEUZES = ['al' => 'Is er al', 'komt' => 'Komt er', 'nee' => 'Niet nodig'];

/** HTML-escape. */
function h(?string $waarde): string
{
    return htmlspecialchars((string) $waarde, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Maakt van een naam een veilig stuk bestandsnaam: alleen a-z, 0-9 en
 * koppeltekens. Er kan dus nooit een pad in de bestandsnaam terechtkomen.
 */
function cm_slug(string $tekst, int $maxLengte = 60): string
{
    $tekst = strtr($tekst, [
        'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'å' => 'a',
        'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
        'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
        'ç' => 'c', 'ñ' => 'n', 'ß' => 'ss', 'ĳ' => 'ij',
    ]);
    $tekst = mb_strtolower($tekst, 'UTF-8');
    $tekst = trim((string) preg_replace('/[^a-z0-9]+/', '-', $tekst), '-');

    if ($tekst === '') {
        return 'naamloos';
    }

    return strlen($tekst) > $maxLengte ? rtrim(substr($tekst, 0, $maxLengte), '-') : $tekst;
}

function cm_niveau_label(string $niveau): string
{
    return CM_NIVEAUS[$niveau] ?? $niveau;
}

/** Rangorde van 1 (Zeer laag) tot 5 (Zeer hoog); leeg of onbekend is 0. */
function cm_niveau_rang(string $niveau): int
{
    $rang = array_search($niveau, array_keys(CM_NIVEAUS), true);

    return $rang === false ? 0 : $rang + 1;
}

/** Een aantal stappen omhoog of omlaag, niet voorbij de bodem of Zeer hoog. */
function cm_niveau_stap(string $niveau, int $stappen, string $bodem = 'zl'): string
{
    $rang = cm_niveau_rang($niveau);

    if ($rang === 0) {
        return $niveau;
    }

    return array_keys(CM_NIVEAUS)[max(cm_niveau_rang($bodem), min(5, $rang + $stappen)) - 1];
}

/** Het hoogste niveau uit een rij; leeg als er geen enkel niveau in zit. */
function cm_niveau_max(string ...$niveaus): string
{
    $hoogste = '';

    foreach ($niveaus as $niveau) {
        if (cm_niveau_rang($niveau) > cm_niveau_rang($hoogste)) {
            $hoogste = $niveau;
        }
    }

    return $hoogste;
}

/** Nederlandse datum; PHP's eigen format() geeft Engelse maandnamen. */
function cm_datum_nl(DateTimeImmutable $moment, bool $metTijd = false): string
{
    $maanden = [
        1 => 'januari', 'februari', 'maart', 'april', 'mei', 'juni',
        'juli', 'augustus', 'september', 'oktober', 'november', 'december',
    ];

    $datum = $moment->format('j') . ' ' . $maanden[(int) $moment->format('n')] . ' ' . $moment->format('Y');

    return $metTijd ? $datum . ' om ' . $moment->format('H:i') : $datum;
}

/** "a", "a en b", "a, b en c". */
function cm_opsomming(array $delen): string
{
    $delen = array_values($delen);

    return count($delen) < 2
        ? (string) ($delen[0] ?? '')
        : implode(', ', array_slice($delen, 0, -1)) . ' en ' . end($delen);
}

/**
 * De niet-lege regels uit een tekstvak.
 *
 * @return list<string>
 */
function cm_regels(string $tekst): array
{
    return array_values(array_filter(array_map('trim', explode("\n", $tekst)), 'strlen'));
}

/** Bestandsnaam met de datum, het formulier en de naam van het project. */
function cm_bestandsnaam(string $naam, string $extensie): string
{
    return (new DateTimeImmutable('now'))->format('Y-m-d') . '_' . cm_formulier()['id'] . '_'
        . cm_slug($naam) . '.' . $extensie;
}
