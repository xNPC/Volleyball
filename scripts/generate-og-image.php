<?php

$width = 1200;
$height = 630;
$output = __DIR__ . '/../public/og-image.jpg';

$fonts = [
    'bold' => 'C:/Windows/Fonts/segoeuib.ttf',
    'regular' => 'C:/Windows/Fonts/segoeui.ttf',
];

foreach ($fonts as $name => $path) {
    if (!is_file($path)) {
        fwrite(STDERR, "Font not found: {$path}\n");
        exit(1);
    }
}

$im = imagecreatetruecolor($width, $height);
imageantialias($im, true);

$from = [0x00, 0x4E, 0x89];
$to = [0x03, 0x30, 0x4F];

for ($y = 0; $y < $height; $y++) {
    $t = $y / ($height - 1);
    $r = (int) round($from[0] + ($to[0] - $from[0]) * $t);
    $g = (int) round($from[1] + ($to[1] - $from[1]) * $t);
    $b = (int) round($from[2] + ($to[2] - $from[2]) * $t);
    $color = imagecolorallocate($im, $r, $g, $b);
    imagefilledrectangle($im, 0, $y, $width - 1, $y, $color);
}

$white = imagecolorallocate($im, 255, 255, 255);
$seam = imagecolorallocate($im, 0x03, 0x30, 0x4F);
$accent = imagecolorallocate($im, 0xFF, 0x6B, 0x35);
$muted = imagecolorallocate($im, 0xC7, 0xD9, 0xE8);

function bezier($im, $p0, $p1, $p2, $color, $thickness = 6)
{
    imagesetthickness($im, $thickness);
    $steps = 140;
    $prev = null;
    for ($i = 0; $i <= $steps; $i++) {
        $t = $i / $steps;
        $mt = 1 - $t;
        $x = $mt * $mt * $p0[0] + 2 * $mt * $t * $p1[0] + $t * $t * $p2[0];
        $y = $mt * $mt * $p0[1] + 2 * $mt * $t * $p1[1] + $t * $t * $p2[1];
        if ($prev !== null) {
            imageline($im, (int) round($prev[0]), (int) round($prev[1]), (int) round($x), (int) round($y), $color);
        }
        $prev = [$x, $y];
    }
    imagesetthickness($im, 1);
}

function textWidth($size, $font, $text)
{
    $bbox = imagettfbbox($size, 0, $font, $text);
    return $bbox[2] - $bbox[0];
}

function drawCentered($im, $size, $font, $color, $text, $y, $width)
{
    $w = textWidth($size, $font, $text);
    $x = (int) round(($width - $w) / 2);
    imagettftext($im, $size, 0, $x, $y, $color, $font, $text);
}

$cx = (int) ($width / 2);
$cy = 200;
$r = 96;

imagefilledellipse($im, $cx, $cy, $r * 2, $r * 2, $white);

bezier($im, [$cx, $cy - $r], [$cx - 1.35 * $r, $cy], [$cx, $cy + $r], $seam, 6);
bezier($im, [$cx, $cy - $r], [$cx + 1.35 * $r, $cy], [$cx, $cy + $r], $seam, 6);
bezier($im, [$cx - $r, $cy], [$cx, $cy - 1.35 * $r], [$cx + $r, $cy], $seam, 6);
bezier($im, [$cx - $r, $cy], [$cx, $cy + 1.35 * $r], [$cx + $r, $cy], $seam, 6);

imagefilledrectangle($im, $cx - 48, 336, $cx + 48, 344, $accent);

$title = 'Единая волейбольная лига';
$subtitle = 'Портал волейбольных турниров Кузбасса';

drawCentered($im, 62, $fonts['bold'], $white, $title, 440, $width);
drawCentered($im, 30, $fonts['regular'], $muted, $subtitle, 500, $width);

imagejpeg($im, $output, 88);
imagedestroy($im);

echo "Saved: {$output}\n";
