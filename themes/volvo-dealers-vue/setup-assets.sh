#!/bin/bash
# Setup script to download all required assets for Volvo Dealers Vue theme

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ASSETS_DIR="$THEME_DIR/assets"
JS_DIR="$ASSETS_DIR/js"
CSS_DIR="$ASSETS_DIR/css"
FONTS_DIR="$ASSETS_DIR/fonts"
IMAGES_DIR="$ASSETS_DIR/images"

echo "Creating directories..."
mkdir -p "$JS_DIR" "$CSS_DIR" "$FONTS_DIR" "$IMAGES_DIR"

echo "Downloading Vue 3..."
curl -L "https://unpkg.com/vue@3/dist/vue.global.js" -o "$JS_DIR/vue.global.js"

echo "Downloading Vue Router 4..."
curl -L "https://unpkg.com/vue-router@4/dist/vue-router.global.js" -o "$JS_DIR/vue-router.global.js"

echo "Downloading Vue i18n 9..."
curl -L "https://unpkg.com/vue-i18n@9/dist/vue-i18n.global.js" -o "$JS_DIR/vue-i18n.global.js"

echo "Downloading Swiper CSS..."
curl -L "https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" -o "$CSS_DIR/swiper-bundle.min.css"

echo "Downloading Swiper JS..."
curl -L "https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js" -o "$JS_DIR/swiper-bundle.min.js"

echo "Creating Volvo logo SVG..."
cat > "$IMAGES_DIR/volvo-logo.svg" << 'EOF'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 50">
  <text x="10" y="35" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="#141414">VOLVO</text>
</svg>
EOF

echo "Creating placeholder images (you should replace these with actual Volvo images)..."
for img in hero-xc90.jpg hero-ex30.jpg hero-em90.jpg polestar.jpg battery.jpg service.jpg wallbox.jpg discovery-xc60.jpg discovery-testdrive.jpg discovery-service.jpg xc90-recharge-side.jpg xc60-recharge-side.jpg xc40-recharge-side.jpg v90-recharge-side.jpg v60-recharge-side.jpg ex30-side.jpg ex90-side.jpg em90-side.jpg; do
    # Create a simple placeholder SVG for each image
    name=$(echo "$img" | sed 's/.jpg//')
    cat > "$IMAGES_DIR/$img" << EOF
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600">
  <rect width="800" height="600" fill="#f0f0f0"/>
  <text x="400" y="300" font-family="Arial, sans-serif" font-size="32" text-anchor="middle" fill="#666">$name</text>
  <text x="400" y="340" font-family="Arial, sans-serif" font-size="16" text-anchor="middle" fill="#999">Placeholder - Replace with actual image</text>
</svg>
EOF
done

echo "Creating Volvo Sans font CSS..."
cat > "$FONTS_DIR/volvo-sans.css" << 'EOF'
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
EOF

echo ""
echo "=============================================="
echo "Asset setup complete!"
echo "=============================================="
echo ""
echo "Directories created:"
echo "  - $JS_DIR"
echo "  - $CSS_DIR"
echo "  - $FONTS_DIR"
echo "  - $IMAGES_DIR"
echo ""
echo "IMPORTANT: Replace placeholder images in $IMAGES_DIR"
echo "with actual Volvo car images from:"
echo "  https://assets.volvo.com/"
echo ""
echo "To download real images, use:"
echo "  curl -L 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/[IMAGE-NAME]?qlt=82&wid=800' -o '$IMAGES_DIR/[filename].jpg'"
echo ""
