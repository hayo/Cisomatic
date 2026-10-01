<?php

/**
 * Een hele pagina om de inhoud heen, voor als er geen site omheen zit.
 *
 * standalone.css komt na app.css. Het geeft de kale pagina een basis, en levert
 * de kleuren en maten die app.css binnen de site van de site leent. Een eigen
 * huisstijl vraagt dus alleen om een aangepast standalone.css.
 *
 * Verwacht $titel, en in $inhoud het pad van de inhoud.
 */

declare(strict_types=1);

/** @var string $titel */
/** @var string $inhoud */

// Binnen de site stuurt de parser deze kop mee; hier doet niemand dat.
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
