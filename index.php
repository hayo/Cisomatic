<?php

/**
 * Compliancomatic: de voordeur, met een lijst van de formulieren.
 *
 * Via de parser van de site levert dit bestand alleen de inhoud. Rechtstreeks
 * opgevraagd levert het een hele pagina.
 */

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

// Rechtstreeks opgevraagd is dit bestand zelf het script; via de parser niet.
cm_kiezer(realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__);
