# Volvo Dealers Vue Theme

A modern WordPress theme for Volvo dealers built with Vue.js 3, replicating the design of the partners_site_v2 theme.

## Features

- **Vue.js 3** - Modern reactive frontend framework
- **Vue Router 4** - Client-side routing for SPA experience
- **Vue i18n 9** - Multilingual support (Polish, English, Czech)
- **Swiper 8** - Touch slider for hero and car models carousel
- **WordPress REST API** - Headless CMS approach
- **Responsive Design** - Mobile-first approach
- **SCSS** - Organized styling with variables matching partners_site_v2

## Design System

The theme replicates the exact design of partners_site_v2 with:

### Colors
- Primary: `#141414` (Black)
- Secondary: `#1C6BBA` (Blue Light)
- Blue: `#284E80`
- Grey Scale: `#FAFAFA`, `#EBEBEB`, `#D5D5D5`, `#707070`

### Typography
- Font: Volvo Sans
- Base: 16px
- Headings: Tight line-height, bold weight

### Components
1. **Header** - Fixed navigation with dropdown menus, mobile hamburger menu, search overlay
2. **HeroSlider** - Full-screen hero with Swiper, autoplay, pagination, navigation
3. **ShoppingBox** - 4-column grid of promotional cards
4. **DiscoveryCard** - Mixed grid layout (2fr + 1fr) for featured content
5. **CarModels** - Tabbed carousel with filtering (All, SUV, Wagon, Electric)
6. **Footer** - 4-column links grid with social icons

## Installation

1. Copy theme folder to `wp-content/themes/`
2. Activate in WordPress admin
3. Ensure WordPress REST API is enabled
4. For multilingual: Set site language in Settings > General

## File Structure

```
volvo-dealers-vue/
├── index.php              # Main template
├── style.css              # Theme header + minimal styles
├── functions.php          # WordPress functions, REST API
├── vue-src/
│   ├── src/
│   │   ├── main.js        # Vue app entry
│   │   ├── App.vue        # Root component
│   │   ├── router/        # Vue Router config
│   │   ├── i18n/          # Translations (pl, en, cs)
│   │   ├── styles/        # SCSS variables & main styles
│   │   ├── components/    # Vue components
│   │   │   ├── Header.vue
│   │   │   ├── HeroSlider.vue
│   │   │   ├── ShoppingBox.vue
│   │   │   ├── DiscoveryCard.vue
│   │   │   ├── CarModels.vue
│   │   │   └── Footer.vue
│   │   └── views/         # Page views
│   │       ├── Home.vue
│   │       ├── Page.vue
│   │       └── CarModel.vue
│   └── package.json       # Dependencies
└── README.md
```

## Multilingual Support

The theme supports 3 languages via WordPress Multisite:

- **Polish** (pl_PL) - Default
- **English** (en_US)
- **Czech** (cs_CZ)

Language is auto-detected from WordPress locale. No language switcher needed - each site in multisite has its own language.

## REST API Endpoints

Custom endpoints for theme data:

- `GET /wp-json/volvo/v1/car-models` - List of car models
- `GET /wp-json/volvo/v1/homepage` - Homepage data with ACF fields

## Development

### With Build System (Optional)

```bash
cd vue-src
npm install
npm run dev    # Development server
npm run build  # Production build
```

### Without Build System (CDN)

The theme works without building! All dependencies are loaded via CDN:
- Vue 3 (Global build)
- Vue Router 4
- Vue i18n 9
- Swiper 8
- Axios

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Credits

- Design based on partners_site_v2 theme
- Volvo brand assets from assets.volvo.com
- Built for Volvo Car Poland dealers

## License

GPL v2 or later
