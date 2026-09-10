<?php

/**
 * Genera la imagen demo del seed (bin/seed-demo-content.sh): un gradiente
 * PNG real para ejercitar el pipeline de media (resize, webp, imgproxy).
 *
 * Uso: ddev exec php /var/www/html/bin/generate-demo-image.php
 * Salida: /tmp/rs-demo.png (1200x800, borradla después o queda en /tmp).
 */

declare(strict_types=1);

if (! function_exists('imagecreatetruecolor')) {
    fwrite(STDERR, "GD no está disponible en el contenedor web\n");
    exit(1);
}

$im = imagecreatetruecolor(1200, 800);

for ($y = 0; $y < 800; $y += 4) {
    $r = (int) ($y * 255 / 800);
    $g = (int) (120 + 80 * sin($y / 90));
    $b = 255 - $r;
    $c = imagecolorallocate($im, $r, $g, $b);
    imagefilledrectangle($im, 0, $y, 1200, $y + 4, $c);
}

imagepng($im, '/tmp/rs-demo.png');
imagedestroy($im);
echo "OK /tmp/rs-demo.png\n";
