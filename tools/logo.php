<?php

/**
 * Maakt huisstijl/rijkslogo.png: het lint met het woordmerk ernaast, als één
 * beeld. Als losse tekst naast het lint schoof het woordmerk in Word soms weg;
 * een beeld blijft staan.
 *
 *   php tools/logo.php                  met "Rijksoverheid"
 *   php tools/logo.php "Ministerie van Algemene Zaken"
 *
 * Het lint is 1,6 cm breed en het woordmerk 13 pt, in RijksoverheidSerif. Zet
 * daarna in config.php onder 'logo' de breedte die dit script noemt.
 */

declare(strict_types=1);

require __DIR__ . '/laad.php';

$map = __DIR__ . '/../huisstijl';
$tekst = $argv[1] ?? 'Rijksoverheid';
$lint = imagecreatefrompng($map . '/rijkslint.png') ?: tl_stop('Kan het lint niet lezen.');
$letter = $map . '/RijksoverheidSerif-Italic.ttf';

$perCm = imagesx($lint) / 1.6;
// GD rekent een lettergrootte om met 96 pixels per inch.
$grootte = 13 / 72 * 2.54 * $perCm * 72 / 96;
$doos = imagettfbbox($grootte, 0, $letter, $tekst) ?: tl_stop('Kan de letter niet lezen.');

$x = (int) round(imagesx($lint) + 0.2 * $perCm);
$basis = (int) round(imagesy($lint) * 0.77);
$breedte = (int) ($x + $doos[2] + round(0.1 * $perCm));

$beeld = imagecreatetruecolor($breedte, imagesy($lint));
imagesavealpha($beeld, true);
imagealphablending($beeld, false);
imagefill($beeld, 0, 0, imagecolorallocatealpha($beeld, 255, 255, 255, 127));
imagealphablending($beeld, true);
imagecopy($beeld, $lint, 0, 0, 0, 0, imagesx($lint), imagesy($lint));
imagettftext($beeld, $grootte, 0, $x, $basis, imagecolorallocate($beeld, 0, 0, 0), $letter, $tekst);
imagepng($beeld, $map . '/rijkslogo.png', 9);

printf("huisstijl/rijkslogo.png: %.2F cm breed, het midden van het lint op 0,8 cm.\n", $breedte / $perCm);
