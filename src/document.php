<?php

/**
 * De hele pagina om de inhoud heen.
 *
 * standalone.css komt na app.css. Het geeft de kale pagina een basis, en levert
 * de kleuren en maten waarop app.css draait. Een eigen huisstijl vraagt dus
 * alleen om een aangepast standalone.css.
 *
 * Verwacht $titel, en in $inhoud het pad van de inhoud.
 */

declare(strict_types=1);

/** @var string $titel */
/** @var string $inhoud */

// Geen inline script of stijl: de opmaak heeft geen onclick of style nodig.
header("Content-Security-Policy: script-src 'self'; style-src 'self'");

?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= h($titel) ?></title>
<link rel="stylesheet" href="<?= h(cm_asset('assets/app.css')) ?>">
<link rel="stylesheet" href="<?= h(cm_asset('assets/standalone.css')) ?>">
</head>
<body>
<main>
<?php require $inhoud; ?>
</main>
</body>
</html>
