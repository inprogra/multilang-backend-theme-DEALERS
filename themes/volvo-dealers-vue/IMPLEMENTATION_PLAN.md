# Volvo Dealers Vue Theme - Implementation Plan

## Overview
Convert volvo-dealers-vue theme to replicate partners_site_v2 design while maintaining Vue.js architecture with WordPress Multisite i18n support.

## Information Gathered

### Current Architecture
- **Theme**: volvo-dealers-vue (Vue 3 SPA with CDN)
- **Reference**: partners_site_v2 (Traditional WordPress with Twig)
- **Static HTML**: Available at `/static/index.html`
- **Language**: WordPress Multisite - locale per site (no language switcher needed)

### Design Requirements from partners_site_v2
- **Colors**: Black (#141414), Blue (#284E80), Blue-light (#1C6BBA), Grey variants
- **Typography**: Volvo Sans font family
- **Libraries**: Swiper (hero slider), Owl Carousel equivalent (car models)
- **Component Structure**: Atomic design (atoms/molecules/organisms)

### Homepage Sections (from HomepageController.class.php)
1. Special Offer Banner (top notification)
2. Hero Slider (m-hero-slider with Swiper)
3. Shopping Box (4 quick links)
4. Discovery Cards (2 large promotional cards)
5. Featured Car Models (carousel)
6. Discovery Card Small (additional promotional)
7. Quotation Cars (car listings)

### Translation Requirements
- **Polish** (pl.json) - Primary language
- **English** (en.json) - Secondary
- **Czech** (cs.json) - Required for Czech market

---

## Implementation Phases

### Phase 1: i18n Setup (Multisite-aware)
- [ ] Add Vue i18n via CDN to index.php
- [ ] Pass WordPress locale from PHP to Vue (pl_PL, en_US, cs_CZ)
- [ ] Configure i18n to use WordPress locale
- [ ] Create translation files:
  - `locales/pl.json` (Polish)
  - `locales/en.json` (English)
  - `locales/cs.json` (Czech)
- [ ] Set up automatic locale detection from WordPress

### Phase 2: Styling System (partners_site_v2)
- [ ] Create SCSS variables matching partners_site_v2:
  - `_colors.scss` - Volvo color palette
  - `_typography.scss` - Volvo Sans fonts
  - `_grid.scss` - Responsive grid
  - `_breakpoints.scss` - Media queries
- [ ] Set up atomic component structure
- [ ] Import Swiper CSS for hero slider
- [ ] Create utility classes

### Phase 3: Core Components

#### A. Header Component (o-header)
- [ ] Volvo logo SVG
- [ ] Main navigation (fetch from WordPress menu)
- [ ] Hamburger menu for mobile
- [ ] Side navigation drawer
- [ ] Social media links
- [ ] Match exact HTML structure from static/index.html

#### B. Hero Slider Component (m-hero-slider)
- [ ] Integrate Swiper.js (CDN)
- [ ] Fetch slides from WordPress API
- [ ] Auto-play with navigation
- [ ] Responsive images
- [ ] Match m-hero-slider structure

#### C. Shopping Box Component
- [ ] 4-column quick links grid
- [ ] Expandable on mobile
- [ ] Fetch from WordPress API
- [ ] Match mainContainerShoppingBox structure

#### D. Discovery Cards Component
- [ ] 2-column promotional cards
- [ ] Category badges
- [ ] CTA buttons
- [ ] Fetch from WordPress API
- [ ] Match discoveryCardMainContainer structure

#### E. Featured Car Models Component
- [ ] Car carousel (vue3-carousel or Swiper)
- [ ] Model cards with images
- [ ] Pricing and category badges
- [ ] Fetch from WordPress API
- [ ] Match featuredCarModelsMainContainer structure

#### F. Footer Component
- [ ] Multi-column layout
- [ ] Contact info and social links
- [ ] Legal links
- [ ] Match footer structure from static HTML

### Phase 4: WordPress Integration
- [ ] **Update functions.php**
  - Add REST API endpoints for homepage data
  - Pass locale to Vue via wp_localize_script
  - Enqueue necessary scripts
  
- [ ] **Create API Endpoints**
  - `/wp-json/volvo/v1/homepage-data` - All homepage sections
  - `/wp-json/volvo/v1/hero-slider` - Hero slides
  - `/wp-json/volvo/v1/shopping-box` - Quick links
  - `/wp-json/volvo/v1/discovery-cards` - Promotional cards
  - `/wp-json/volvo/v1/car-models` - Featured cars
  - All endpoints return data in current site's language

- [ ] **Update wp-api.js**
  - Add methods for new endpoints
  - Handle errors gracefully
  - Add caching if needed

### Phase 5: Translation Files
- [ ] **Create pl.json** (Polish)
  - Navigation labels
  - Button texts
  - Common UI strings
  - Form labels
  
- [ ] **Create en.json** (English)
  - Same keys as Polish
  - English translations

- [ ] **Create cs.json** (Czech)
  - Same keys as Polish
  - Czech translations

### Phase 6: Responsive & Polish
- [ ] Mobile-first responsive design
- [ ] Touch interactions
- [ ] Loading states
- [ ] Error handling
- [ ] Performance optimization

---

## Files to Create/Modify

### New Vue Components
- `src/components/ShoppingBox.vue`
- `src/components/FeaturedCarModels.vue`

### Updated Vue Components
- `src/components/Header.vue` - Match partners_site_v2 design
- `src/components/HeroSlider.vue` - Use Swiper, match design
- `src/components/DiscoveryCard.vue` - Match design
- `src/components/Footer.vue` - Match design
- `src/views/Home.vue` - Assemble all homepage sections

### New Style Files
- `src/styles/variables/_colors.scss`
- `src/styles/variables/_typography.scss`
- `src/styles/variables/_grid.scss`
- `src/styles/variables/_breakpoints.scss`
- `src/styles/components/` - Atomic structure

### Updated Files
- `src/main.js` - Add i18n configuration
- `src/styles/main.scss` - Import new variables
- `src/services/wp-api.js` - Add new endpoints

### New Translation Files
- `src/locales/pl.json`
- `src/locales/en.json`
- `src/locales/cs.json`

### WordPress PHP Files
- `functions.php` - Add locale passing, API endpoints
- `inc/api-endpoints.php` (NEW) - REST API handlers
- `index.php` - Add i18n CDN, pass locale

---

## Followup Steps
1. Test on WordPress Multisite (Polish, English, Czech sites)
2. Verify locale detection works correctly (pl_PL, en_US, cs_CZ)
3. Test all API endpoints
4. Responsive testing on devices
5. Performance optimization
6. Cross-browser testing
7. Accessibility audit
