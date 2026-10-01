<?php

/**
 * Eén formulier van de Compliancomatic. De motor staat in ../src, de vragen en
 * regels van dit formulier in deze map, en zijn teksten in definitie.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

// Rechtstreeks opgevraagd is dit bestand zelf het script; via de parser niet.
cm_verwerk(basename(__DIR__), realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__);
