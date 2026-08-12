<?php

$srcPath = __DIR__ . '/../public/images/home/mockup-full.png';
$outDir = __DIR__ . '/../public/images/home';

$info = getimagesize($srcPath);
[$w, $h] = $info;
echo "Source: {$w}x{$h}\n";

$src = imagecreatefrompng($srcPath);
if (!$src) {
    fwrite(STDERR, "Failed to load mockup\n");
    exit(1);
}

/**
 * Crop a region as percentages of the full mockup and save as JPEG.
 */
function cropPercent($src, $w, $h, $outPath, $x1, $y1, $x2, $y2, $quality = 90): void
{
    $sx = (int) round($w * $x1);
    $sy = (int) round($h * $y1);
    $sw = max(1, (int) round($w * ($x2 - $x1)));
    $sh = max(1, (int) round($h * ($y2 - $y1)));

    $dst = imagecreatetruecolor($sw, $sh);
    imagecopy($dst, $src, 0, 0, $sx, $sy, $sw, $sh);
    imagejpeg($dst, $outPath, $quality);
    imagedestroy($dst);
    echo "Wrote {$outPath} ({$sw}x{$sh})\n";
}

// Approximate regions based on the tall landing mockup layout.
cropPercent($src, $w, $h, "$outDir/hero.jpg", 0.00, 0.00, 1.00, 0.145);
cropPercent($src, $w, $h, "$outDir/dish-1.jpg", 0.30, 0.33, 0.46, 0.41);
cropPercent($src, $w, $h, "$outDir/dish-2.jpg", 0.47, 0.33, 0.63, 0.41);
cropPercent($src, $w, $h, "$outDir/dish-3.jpg", 0.64, 0.33, 0.80, 0.41);
cropPercent($src, $w, $h, "$outDir/dish-4.jpg", 0.81, 0.33, 0.97, 0.41);
cropPercent($src, $w, $h, "$outDir/story-chef.jpg", 0.05, 0.45, 0.36, 0.58);
cropPercent($src, $w, $h, "$outDir/story-interior.jpg", 0.37, 0.45, 0.52, 0.51);
cropPercent($src, $w, $h, "$outDir/story-pizza.jpg", 0.37, 0.515, 0.52, 0.58);
cropPercent($src, $w, $h, "$outDir/ig-1.jpg", 0.02, 0.72, 0.18, 0.78);
cropPercent($src, $w, $h, "$outDir/ig-2.jpg", 0.18, 0.72, 0.34, 0.78);
cropPercent($src, $w, $h, "$outDir/ig-3.jpg", 0.34, 0.72, 0.50, 0.78);
cropPercent($src, $w, $h, "$outDir/ig-4.jpg", 0.50, 0.72, 0.66, 0.78);
cropPercent($src, $w, $h, "$outDir/ig-5.jpg", 0.66, 0.72, 0.82, 0.78);
cropPercent($src, $w, $h, "$outDir/ig-6.jpg", 0.82, 0.72, 0.98, 0.78);
cropPercent($src, $w, $h, "$outDir/avatar-1.jpg", 0.12, 0.64, 0.18, 0.67);
cropPercent($src, $w, $h, "$outDir/avatar-2.jpg", 0.45, 0.64, 0.51, 0.67);
cropPercent($src, $w, $h, "$outDir/avatar-3.jpg", 0.78, 0.64, 0.84, 0.67);

imagedestroy($src);
echo "Done\n";
