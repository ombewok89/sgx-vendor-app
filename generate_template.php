<?php
// Script to generate high-resolution TEMPLATE_TIMESLAP_SG.png

$width = 1920;
$height = 1440;

$img = imagecreatetruecolor($width, $height);
imagesavealpha($img, true);

// 1. Clean White Background (as requested, will be replaced by field photo during runtime)
$white = imagecolorallocate($img, 255, 255, 255);
imagefill($img, 0, 0, $white);

// Colors
$black = imagecolorallocate($img, 15, 23, 42); // Slate 900
$pureBlack = imagecolorallocate($img, 0, 0, 0);
$darkGray = imagecolorallocate($img, 51, 65, 85);
$lightGray = imagecolorallocate($img, 148, 163, 184);
$grayBorder = imagecolorallocate($img, 226, 232, 240);
$gold = imagecolorallocate($img, 234, 179, 8);
$cyan = imagecolorallocate($img, 14, 165, 233);
$green = imagecolorallocate($img, 16, 185, 129);

// 2. Load & Draw Header Logo (Top-Left)
$logoPath = __DIR__ . '/client/public/sgx_logo.png';
if (file_exists($logoPath)) {
    $logoSrc = @imagecreatefrompng($logoPath);
    if ($logoSrc) {
        $logoW = imagesx($logoSrc);
        $logoH = imagesy($logoSrc);
        $targetLogoW = 160;
        $targetLogoH = 160;
        
        // Draw shadow under logo
        $shadow = imagecolorallocatealpha($img, 0, 0, 0, 100);
        imagefilledrectangle($img, 55, 55, 55 + $targetLogoW, 55 + $targetLogoH, $shadow);
        
        imagecopyresampled($img, $logoSrc, 50, 50, 0, 0, $targetLogoW, $targetLogoH, $logoW, $logoH);
    }
}

// 3. Bottom Information & Footer Area
$footerH = 140;
$panelH = 360;
$totalBottomH = $panelH + $footerH;
$panelY = $height - $totalBottomH;

// NO dark background - 100% transparent so field photo is fully visible!

// --- AREA INFORMASI LOKASI (Bawah-Kiri / Tengah) ---

// A. Blok Waktu
$timeBoxX = 70;
$timeBoxY = $panelY + 40;

$timeStr = "0 0 : 0 9";
$dateStr = "19/08/2026";
$dayStr = "Rabu";

// Large bold time with crisp black stroke
imagestring($img, 5, $timeBoxX, $timeBoxY, $timeStr, $pureBlack);
imagestring($img, 5, $timeBoxX + 1, $timeBoxY, $timeStr, $pureBlack);
imagestring($img, 5, $timeBoxX + 2, $timeBoxY, $timeStr, $pureBlack);
imagestring($img, 5, $timeBoxX, $timeBoxY + 1, $timeStr, $pureBlack);
imagestring($img, 5, $timeBoxX, $timeBoxY + 2, $timeStr, $pureBlack);

// Vertical Divider Bar
$divX = $timeBoxX + 200;
imagefilledrectangle($img, $divX, $timeBoxY - 5, $divX + 6, $timeBoxY + 70, $gold);

// Date & Day
imagestring($img, 5, $divX + 25, $timeBoxY + 5, $dateStr, $pureBlack);
imagestring($img, 5, $divX + 26, $timeBoxY + 5, $dateStr, $pureBlack);
imagestring($img, 5, $divX + 25, $timeBoxY + 38, $dayStr, $darkGray);
imagestring($img, 5, $divX + 26, $timeBoxY + 38, $dayStr, $darkGray);

// Stage Badge
$stageY = $timeBoxY + 95;
$stageStr = "[SPK-SGX-20260818-0001] • TAHAP: BEFORE";
imagestring($img, 5, $timeBoxX, $stageY, $stageStr, $gold);
imagestring($img, 5, $timeBoxX + 1, $stageY, $stageStr, $gold);

// B. Blok Alamat
$addressY = $stageY + 45;
$addressStr = "Mataram, Kec. Tugumulyo, Kabupaten Musi Rawas, Sumatera Selatan 31626";
imagestring($img, 5, $timeBoxX, $addressY, $addressStr, $pureBlack);
imagestring($img, 5, $timeBoxX + 1, $addressY, $addressStr, $pureBlack);
imagestring($img, 5, $timeBoxX, $addressY + 1, $addressStr, $pureBlack);

// C. Blok Koordinat (Solid Black Badge with Bright White Text)
$coordY = $addressY + 48;
$coordBadgeW = 560;
$coordBadgeH = 52;
imagefilledrectangle($img, $timeBoxX, $coordY, $timeBoxX + $coordBadgeW, $coordY + $coordBadgeH, $pureBlack);

$whiteText = imagecolorallocate($img, 255, 255, 255);
$coordStr = "Koordinat: -3.824921, 102.286299";
imagestring($img, 5, $timeBoxX + 25, $coordY + 16, $coordStr, $whiteText);
imagestring($img, 5, $timeBoxX + 26, $coordY + 16, $coordStr, $whiteText);


// --- SISIPAN MAP (Bawah-Kanan, di samping koordinat) ---
$mapW = 380;
$mapH = 260;
$mapX = $width - $mapW - 70;
$mapY = $panelY + 35;

// Map Background (Satellite Texture)
$mapBg = imagecolorallocate($img, 30, 41, 59);
$satGreen1 = imagecolorallocate($img, 39, 78, 19);
$satGreen2 = imagecolorallocate($img, 56, 118, 29);
$roofGray = imagecolorallocate($img, 71, 85, 105);
$roadYellow = imagecolorallocate($img, 250, 204, 21);
$roadOrange = imagecolorallocate($img, 217, 119, 6);
$roadWhite = imagecolorallocate($img, 255, 255, 255);

imagefilledrectangle($img, $mapX, $mapY, $mapX + $mapW, $mapY + $mapH, $mapBg);

// Satellite foliage & buildings
imagefilledrectangle($img, $mapX, $mapY, $mapX + 180, $mapY + 110, $satGreen1);
imagefilledrectangle($img, $mapX + 200, $mapY + 120, $mapX + $mapW, $mapY + $mapH, $satGreen2);
imagefilledrectangle($img, $mapX + 40, $mapY + 130, $mapX + 150, $mapY + 230, $roofGray);
imagefilledrectangle($img, $mapX + 220, $mapY + 30, $mapX + 340, $mapY + 110, $roofGray);

// Road - Jl. Jend. Sudirman
imagefilledrectangle($img, $mapX, $mapY + 110, $mapX + $mapW, $mapY + 130, $roadOrange);
imagefilledrectangle($img, $mapX, $mapY + 114, $mapX + $mapW, $mapY + 126, $roadYellow);

// Street Label
imagestring($img, 4, $mapX + 20, $mapY + 90, "Jl. Jend. Sudirman", $white);
imagestring($img, 4, $mapX + 21, $mapY + 90, "Jl. Jend. Sudirman", $white);

// Vision Angle & GPS Pin
$pinX = $mapX + 190;
$pinY = $mapY + 130;

// Location Pin (Blue & Green)
$pinBlue = imagecolorallocate($img, 2, 132, 199);
$pinGreen = imagecolorallocate($img, 16, 185, 129);
imagefilledellipse($img, $pinX, $pinY, 28, 28, $pinBlue);
imagefilledellipse($img, $pinX, $pinY, 18, 18, $pinGreen);
imagefilledellipse($img, $pinX, $pinY, 8, 8, $white);

// Compass on top of pin
imagefilledpolygon($img, [
    $pinX, $pinY - 24,
    $pinX - 6, $pinY - 14,
    $pinX + 6, $pinY - 14
], 3, $pureBlack);

// Google logo bottom-left of map
$mapOverlay = imagecolorallocatealpha($img, 0, 0, 0, 80);
imagefilledrectangle($img, $mapX, $mapY + $mapH - 30, $mapX + $mapW, $mapY + $mapH, $mapOverlay);
imagestring($img, 4, $mapX + 15, $mapY + $mapH - 24, "Google", $white);

// Map Outline Border
imagerectangle($img, $mapX, $mapY, $mapX + $mapW, $mapY + $mapH, $white);
imagerectangle($img, $mapX - 1, $mapY - 1, $mapX + $mapW + 1, $mapY + $mapH + 1, $grayBorder);


// --- AREA KONTAK PERUSAHAAN (Bawah-Kanan Footer Bar) ---
$footerY = $height - $footerH;

// Footer Bar (Clean White / Light Slate)
imagefilledrectangle($img, 0, $footerY, $width, $height, $white);
imagefilledrectangle($img, 0, $footerY, $width, $footerY + 2, $grayBorder);

// Left Address in Footer
$officeAddrStr = "Jl. Ratu Agung No. 04 - Kel. Anggut Bawah, Kec. Ratu Samban, Kota Bengkulu";
imagestring($img, 5, 70, $footerY + 30, $officeAddrStr, $black);

// Phone in Footer
$phoneStr = "Telp / WA: 0823 8888 5251";
imagestring($img, 5, 70, $footerY + 65, $phoneStr, $darkGray);
// Right Website URL in Footer (No icon)
$webUrlStr = "vendor.sinargrafika.my.id";
imagestring($img, 5, $width - 320, $footerY + 55, $webUrlStr, $lightGray);
imagestring($img, 5, $width - 319, $footerY + 55, $webUrlStr, $lightGray);


// 4. Save PNG File
$outputPath = __DIR__ . '/TEMPLATE_TIMESLAP_SG.png';
$publicPath = __DIR__ . '/client/public/TEMPLATE_TIMESLAP_SG.png';

imagepng($img, $outputPath);
imagepng($img, $publicPath);
imagedestroy($img);

echo "✅ TEMPLATE_TIMESLAP_SG.png generated successfully at: " . $outputPath . "\n";
