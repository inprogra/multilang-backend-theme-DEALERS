<?php
/**
 * Asset Downloader for Volvo Dealers Vue Theme
 * Run this script to download all required JS libraries and create placeholder images
 */

// Security check - only allow from same domain
if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] !== $_SERVER['SERVER_NAME']) {
    die('Access denied');
}

$theme_dir = dirname(__FILE__);
$assets_dir = $theme_dir . '/assets';
$js_dir = $assets_dir . '/js';
$css_dir = $assets_dir . '/css';
$fonts_dir = $assets_dir . '/fonts';
$images_dir = $assets_dir . '/images';

// Create directories
$dirs = [$assets_dir, $js_dir, $css_dir, $fonts_dir, $images_dir];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "Created: $dir<br>";
    }
}

// Files to download
$downloads = [
    // JS Libraries
    'https://unpkg.com/vue@3/dist/vue.global.js' => $js_dir . '/vue.global.js',
    'https://unpkg.com/vue-router@4/dist/vue-router.global.js' => $js_dir . '/vue-router.global.js',
    'https://unpkg.com/vue-i18n@9/dist/vue-i18n.global.js' => $js_dir . '/vue-i18n.global.js',
    'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js' => $js_dir . '/swiper-bundle.min.js',
    
    // CSS
    'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css' => $css_dir . '/swiper-bundle.min.css',
];

// Download files
foreach ($downloads as $url => $destination) {
    if (file_exists($destination)) {
        echo "Already exists: " . basename($destination) . "<br>";
        continue;
    }
    
    $content = @file_get_contents($url);
    if ($content !== false) {
        file_put_contents($destination, $content);
        echo "Downloaded: " . basename($destination) . "<br>";
    } else {
        echo "FAILED: " . basename($destination) . " - Could not download from $url<br>";
    }
}

// Create Volvo Sans font CSS
$font_css = <<<'CSS'
/* Volvo Sans Font - Fallback to system fonts */
@font-face {
  font-family: 'Volvo Sans';
  src: local('Arial');
  font-weight: 400;
  font-style: normal;
}

@font-face {
  font-family: 'Volvo Sans';
  src: local('Arial');
  font-weight: 500;
  font-style: normal;
}

@font-face {
  font-family: 'Volvo Sans';
  src: local('Arial Bold');
  font-weight: 700;
  font-style: normal;
}
CSS;

$font_file = $fonts_dir . '/volvo-sans.css';
if (!file_exists($font_file)) {
    file_put_contents($font_file, $font_css);
    echo "Created: volvo-sans.css<br>";
}

// Create Volvo logo SVG
$logo_svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 50">
  <text x="10" y="35" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="#141414">VOLVO</text>
</svg>
SVG;

$logo_file = $images_dir . '/volvo-logo.svg';
if (!file_exists($logo_file)) {
    file_put_contents($logo_file, $logo_svg);
    echo "Created: volvo-logo.svg<br>";
}

// Create placeholder images
$placeholders = [
    'hero-xc90.jpg', 'hero-ex30.jpg', 'hero-em90.jpg',
    'polestar.jpg', 'battery.jpg', 'service.jpg', 'wallbox.jpg',
    'discovery-xc60.jpg', 'discovery-testdrive.jpg', 'discovery-service.jpg',
    'xc90-recharge-side.jpg', 'xc60-recharge-side.jpg', 'xc40-recharge-side.jpg',
    'v90-recharge-side.jpg', 'v60-recharge-side.jpg', 'ex30-side.jpg',
    'ex90-side.jpg', 'em90-side.jpg'
];

foreach ($placeholders as $img) {
    $img_file = $images_dir . '/' . $img;
    if (file_exists($img_file)) {
        continue;
    }
    
    $name = str_replace('.jpg', '', $img);
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600">
  <rect width="800" height="600" fill="#f0f0f0"/>
  <text x="400" y="300" font-family="Arial, sans-serif" font-size="32" text-anchor="middle" fill="#666">$name</text>
  <text x="400" y="340" font-family="Arial, sans-serif" font-size="16" text-anchor="middle" fill="#999">Placeholder - Replace with actual image</text>
</svg>
SVG;
    file_put_contents($img_file, $svg);
    echo "Created placeholder: $img<br>";
}

echo "<br><strong>Done!</strong><br>";
echo "<br>Next steps:<br>";
echo "1. Replace placeholder images in: $images_dir<br>";
echo "2. Download real Volvo images from: https://assets.volvo.com/<br>";
echo "3. Clear browser cache and refresh the page<br>";
