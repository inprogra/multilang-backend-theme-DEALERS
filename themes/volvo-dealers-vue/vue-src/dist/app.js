// Volvo Dealers Vue - Fully Bundled App (No ES Modules)
(function() {
  'use strict';

  function initApp() {
    if (typeof Vue === 'undefined' || typeof VueRouter === 'undefined' || typeof VueI18n === 'undefined') {
      setTimeout(initApp, 100);
      return;
    }

    const { createApp, ref, computed, onMounted, onUnmounted } = Vue;
    const { createRouter, createWebHistory } = VueRouter;
    const { createI18n } = VueI18n;

    // Get theme URL for local assets
    const themeUrl = (window.volvoThemeData && window.volvoThemeData.themeUrl) || '';
    const placeholdersUrl = themeUrl + '/assets/placeholders';

    // Czech translations for DB content
    const csTranslations = {
      'Modele': 'Modely', 'Oferta': 'Nabídky', 'Serwis': 'Servis', 'Kontakt': 'Kontakt',
      'Jazda testowa': 'Testovací jízda', 'Finansowanie': 'Financování', 'Blog': 'Blog',
      'XC90': 'XC90', 'XC60': 'XC60', 'XC40': 'XC40', 'V90': 'V90', 'V60': 'V60',
      'EX30': 'EX30', 'EX90': 'EX90', 'EM90': 'EM90', 'S90': 'S90', 'S60': 'S60',
      'SUV': 'SUV', 'Kombi': 'Kombi', 'Sedan': 'Sedan', 'Elektryczny SUV': 'Elektrické SUV',
      'Elektryczny van': 'Elektrický van', 'Od': 'Od', 'PLN': 'PLN', 'Odkryj': 'Objevit',
      'Dowiedz się więcej': 'Zjistit více', 'Sprawdź': 'Zkontrolovat', 'oferta': 'nabídka',
      'Już od': 'Již od', 'netto miesięcznie': 'netto měsíčně', 'Volvo Car Poland': 'Volvo Car Poland',
      'Kariera': 'Kariéra', 'Części': 'Díly', 'Oleje': 'Oleje', 'Salony': 'Salony',
      'Wszystkie prawa zastrzeżone': 'Všechna práva vyhrazena', 'Dostępne od ręki': 'Dostupné na místě',
      'Używane Selekt': 'Použité Selekt', 'Wszystkie': 'Všechny', 'Elektryczne': 'Elektrické',
      'Volvo Polestar': 'Volvo Polestar', 'Volvo Battery': 'Baterie Volvo', 'Serwis rosnących rabatów': 'Servis rostoucích slev', 'Volvo Wallbox': 'Volvo Wallbox',
      'Nowe emocje w Twoim starszym aucie.': 'Nové emoce ve vašem starším autě.', 'Sprawdź to.': 'Zkontrolujte.', 'Im starsze Twoje auto, tym większe rabaty.': 'Čím starší vaše auto, tím větší slevy.', 'Energia, którą daje Ci Volvo.': 'Energie, kterou vám Volvo dává.',
      'Oferta specjalna Volvo XC60': 'Speciální nabídka Volvo XC60', 'Wyjątkowe warunki finansowania.': 'Výjimečné podmínky financování.',
      'Umów się na bezpłatną jazdę testową.': 'Domluvte si bezplatnou zkušební jízdu.', 'Autoryzowany serwis i oryginalne części.': 'Autorizovaný servis a originální díly.',
      'Przestrzeń dla Twoich marzeń': 'Prostor pro vaše sny', 'Elektryzujące': 'Elektrizující', 'Luksus w czystej formie': 'Luxus v čisté formě',
      'Luksusowy SUV, który zmienia reguły gry.': 'Luxusní SUV, které mění pravidla hry.', 'Najmniejsze Volvo, jakie kiedykolwiek stworzyliśmy.': 'Nejmenší Volvo, jaké jsme kdy vytvořili.', 'Pierwszy luksusowy elektryczny van Volvo.': 'První luxusní elektrický van Volvo.'
    };

    const translateToCs = (text) => csTranslations[text] || text;

    const locale = (window.volvoThemeData && window.volvoThemeData.locale) || 'pl_PL';
    const langMap = { 'pl_PL': 'pl', 'en_US': 'en', 'cs_CZ': 'cs' };
    const defaultLocale = langMap[locale] || 'pl';

    const messages = {
      pl: { nav: { models: 'Modele', offers: 'Oferta', service: 'Serwis', testDrive: 'Jazda testowa', blog: 'Blog', financing: 'Finansowanie', contact: 'Kontakt', search: 'Szukaj' }, hero: { learnMore: 'Dowiedz się więcej', from: 'Już od', monthly: 'netto miesięcznie' }, carModels: { title: 'Odkryj gamę', titleBold: 'modeli Volvo', price: { from: 'Od', currency: 'PLN' }, cta: 'Odkryj' }, discovery: { category: 'oferta', cta: 'Sprawdź' }, footer: { volvoPoland: 'Volvo Car Poland' } },
      en: { nav: { models: 'Our Models', offers: 'Offers', service: 'Service', testDrive: 'Test Drive', blog: 'Blog', financing: 'Financing', contact: 'Contact', search: 'Search' }, hero: { learnMore: 'Learn More', from: 'From', monthly: 'net per month' }, carModels: { title: 'Discover the', titleBold: 'Volvo range', price: { from: 'From', currency: 'PLN' }, cta: 'Discover' }, discovery: { category: 'offer', cta: 'Check' }, footer: { volvoPoland: 'Volvo Car Poland' } },
      cs: { nav: { models: 'Modely', offers: 'Nabídky', service: 'Servis', testDrive: 'Testovací jízda', blog: 'Blog', financing: 'Financování', contact: 'Kontakt', search: 'Hledat' }, hero: { learnMore: 'Zjistit více', from: 'Již od', monthly: 'netto měsíčně' }, carModels: { title: 'Objevte řadu', titleBold: 'modelů Volvo', price: { from: 'Od', currency: 'PLN' }, cta: 'Objevit' }, discovery: { category: 'nabídka', cta: 'Zkontrolovat' }, footer: { volvoPoland: 'Volvo Car Poland' } }
    };

    const i18n = createI18n({ legacy: false, locale: defaultLocale, fallbackLocale: 'pl', messages });

    // Fixed image error handler - prevents infinite loops
    const handleImageError = (e) => {
      const img = e.target;
      // Prevent infinite loop by checking if already using placeholder
      if (img.src && img.src.includes('/placeholders/')) {
        return; // Already using placeholder, don't retry
      }
      // Set placeholder based on element dimensions
      const w = img.width || img.clientWidth || 400;
      if (w >= 800) {
        img.src = placeholdersUrl + '/hero.svg';
      } else if (w >= 400) {
        img.src = placeholdersUrl + '/card.svg';
      } else {
        img.src = placeholdersUrl + '/car.svg';
      }
    };

    const Header = {
      template: `<header class="header" :class="{ 'header--scrolled': isScrolled, 'header--menu-open': isMenuOpen }"><div class="header__container"><div class="header__logo"><a href="/" class="header__logo-link"><img :src="placeholdersUrl + '/logo.svg'" alt="Volvo" class="header__logo-img" @error="handleImageError" /></a></div><nav class="header__nav header__nav--desktop"><ul class="header__nav-list"><li class="header__nav-item"><a href="/modele" class="header__nav-link">{{ $t('nav.models') }}</a></li><li class="header__nav-item header__nav-item--has-dropdown"><span class="header__nav-link">{{ $t('nav.offers') }}</span><ul class="header__dropdown"><li><a href="/oferty/dostepne-od-reki" class="header__dropdown-link">{{ t('Dostępne od ręki') }}</a></li><li><a href="/oferty/uzywane-selekt" class="header__dropdown-link">{{ t('Używane Selekt') }}</a></li></ul></li><li class="header__nav-item"><a href="/serwis" class="header__nav-link">{{ $t('nav.service') }}</a></li><li class="header__nav-item"><a href="/jazda-testowa" class="header__nav-link">{{ $t('nav.testDrive') }}</a></li><li class="header__nav-item"><a href="/kontakt" class="header__nav-link">{{ $t('nav.contact') }}</a></li></ul></nav><div class="header__actions"><button class="header__search-btn" @click="toggleSearch"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg></button><button class="header__menu-toggle" @click="toggleMenu"><span class="header__menu-bar"></span><span class="header__menu-bar"></span><span class="header__menu-bar"></span></button></div></div><nav class="header__nav header__nav--mobile" v-show="isMenuOpen"><ul class="header__nav-list"><li class="header__nav-item" v-for="item in menu" :key="item.link"><a :href="item.link" class="header__nav-link" @click="closeMenu">{{ $t(item.key) }}</a></li></ul></nav><div class="header__search-overlay" v-if="isSearchOpen" @click.self="closeSearch"><div class="header__search-container"><input type="text" class="header__search-input" :placeholder="$t('nav.search')" v-model="searchQuery" @keyup.enter="performSearch" /><button class="header__search-close" @click="closeSearch">×</button></div></div></header>`,
      setup() {
        const isScrolled = ref(false);
        const isMenuOpen = ref(false);
        const isSearchOpen = ref(false);
        const searchQuery = ref('');
        const t = (text) => defaultLocale === 'cs' ? translateToCs(text) : text;
        const menu = [{ key: 'nav.models', link: '/modele' }, { key: 'nav.offers', link: '/oferty' }, { key: 'nav.service', link: '/serwis' }, { key: 'nav.testDrive', link: '/jazda-testowa' }, { key: 'nav.contact', link: '/kontakt' }];
        const handleScroll = () => { isScrolled.value = window.scrollY > 50; };
        const toggleMenu = () => { isMenuOpen.value = !isMenuOpen.value; };
        const closeMenu = () => { isMenuOpen.value = false; };
        const toggleSearch = () => { isSearchOpen.value = !isSearchOpen.value; };
        const closeSearch = () => { isSearchOpen.value = false; searchQuery.value = ''; };
        const performSearch = () => { if (searchQuery.value.trim()) window.location.href = '/?s=' + encodeURIComponent(searchQuery.value); };
        onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }));
        onUnmounted(() => window.removeEventListener('scroll', handleScroll));
        return { isScrolled, isMenuOpen, isSearchOpen, searchQuery, menu, placeholdersUrl, handleImageError, toggleMenu, closeMenu, toggleSearch, closeSearch, performSearch, t };
      }
    };

    const HeroSlider = {
      template: `<section class="hero-slider"><div class="swiper hero-slider__swiper"><div class="swiper-wrapper"><div v-for="(slide, index) in slides" :key="index" class="swiper-slide"><img :src="placeholdersUrl + '/hero.svg'" :alt="t(slide.title)" @error="handleImageError" /><div class="hero-slider__content"><h2>{{ t(slide.title) }}</h2><p>{{ t(slide.subtitle) }}</p><a :href="slide.link" class="btn btn--primary">{{ $t('hero.learnMore') }}</a></div></div></div><div class="swiper-pagination"></div></div></section>`,
      setup() {
        const t = (text) => defaultLocale === 'cs' ? translateToCs(text) : text;
        const slides = [{ title: 'Volvo XC90', subtitle: 'Przestrzeń dla Twoich marzeń', link: '/modele/xc90' }, { title: 'Volvo EX30', subtitle: 'Elektryzujące', link: '/modele/ex30' }, { title: 'Volvo EM90', subtitle: 'Luksus w czystej formie', link: '/modele/em90' }];
        onMounted(() => { if (typeof Swiper !== 'undefined') new Swiper('.hero-slider__swiper', { loop: true, autoplay: { delay: 5000 }, pagination: { el: '.swiper-pagination', clickable: true } }); });
        return { slides, placeholdersUrl, handleImageError, t };
      }
    };

    const ShoppingBox = {
      template: `<section class="shopping-box"><div class="container"><div class="shopping-box__grid"><div v-for="(item, index) in items" :key="index" class="shopping-box__item"><img :src="placeholdersUrl + '/card.svg'" :alt="t(item.title)" @error="handleImageError" /><h3>{{ t(item.title) }}</h3><p>{{ t(item.description) }}</p></div></div></div></section>`,
      setup() {
        const t = (text) => defaultLocale === 'cs' ? translateToCs(text) : text;
        const items = [{ title: 'Volvo Polestar', description: 'Nowe emocje w Twoim starszym aucie.' }, { title: 'Volvo Battery', description: 'Sprawdź to.' }, { title: 'Serwis rosnących rabatów', description: 'Im starsze Twoje auto, tym większe rabaty.' }, { title: 'Volvo Wallbox', description: 'Energia, którą daje Ci Volvo.' }];
        return { items, placeholdersUrl, handleImageError, t };
      }
    };

    const DiscoveryCard = {
      template: `<section class="discovery-cards"><div class="container"><div class="discovery-cards__grid"><div v-for="(card, index) in cards" :key="index" class="discovery-cards__item" :class="{ 'discovery-cards__item--large': card.large }"><img :src="placeholdersUrl + '/card.svg'" :alt="t(card.title)" @error="handleImageError" /><h3>{{ t(card.title) }}</h3><p>{{ t(card.description) }}</p></div></div></div></section>`,
      setup() {
        const t = (text) => defaultLocale === 'cs' ? translateToCs(text) : text;
        const cards = [{ title: 'Oferta specjalna Volvo XC60', description: 'Wyjątkowe warunki finansowania.', large: true }, { title: 'Jazda testowa', description: 'Umów się na bezpłatną jazdę testową.', large: false }, { title: 'Serwis Volvo', description: 'Autoryzowany serwis i oryginalne części.', large: false }];
        return { cards, placeholdersUrl, handleImageError, t };
      }
    };

    const CarModels = {
      template: `<section class="car-models"><div class="container"><h2>{{ $t('carModels.title') }} <span>{{ $t('carModels.titleBold') }}</span></h2><div class="car-models__tabs"><button v-for="tab in tabs" :key="tab.id" :class="{ active: activeTab === tab.id }" @click="setTab(tab.id)">{{ tab.label }}</button></div><div class="swiper car-models__swiper"><div class="swiper-wrapper"><div v-for="car in filteredCars" :key="car.id" class="swiper-slide"><img :src="placeholdersUrl + '/car.svg'" :alt="t(car.name)" @error="handleImageError" /><h3>{{ t(car.name) }}</h3><p>{{ t(car.type) }}</p><p>{{ $t('carModels.price.from') }} {{ car.price }} {{ $t('carModels.price.currency') }}</p></div></div></div></div></section>`,
      setup() {
        const activeTab = ref('all');
        let swiperInstance = null;
        const t = (text) => defaultLocale === 'cs' ? translateToCs(text) : text;
        const tabs = [{ id: 'all', label: t('Wszystkie') }, { id: 'suv', label: 'SUV' }, { id: 'wagon', label: t('Kombi') }, { id: 'electric', label: t('Elektryczne') }];
        const cars = [{ id: 1, name: 'XC90', type: 'SUV', category: 'suv', price: '351 900' }, { id: 2, name: 'XC60', type: 'SUV', category: 'suv', price: '289 900' }, { id: 3, name: 'XC40', type: 'SUV', category: 'suv', price: '199 900' }, { id: 4, name: 'V90', type: 'Kombi', category: 'wagon', price: '319 900' }, { id: 5, name: 'V60', type: 'Kombi', category: 'wagon', price: '249 900' }, { id: 6, name: 'EX30', type: 'Elektryczny SUV', category: 'electric', price: '179 900' }, { id: 7, name: 'EX90', type: 'Elektryczny SUV', category: 'electric', price: '449 900' }, { id: 8, name: 'EM90', type: 'Elektryczny van', category: 'electric', price: '599 900' }];
        const filteredCars = computed(() => activeTab.value === 'all' ? cars : cars.filter(c => c.category === activeTab.value));
        const setTab = (id) => { activeTab.value = id; setTimeout(() => { if (typeof Swiper !== 'undefined') { if (swiperInstance) swiperInstance.destroy(); swiperInstance = new Swiper('.car-models__swiper', { slidesPerView: 4, spaceBetween: 24, loop: true, breakpoints: { 1200: { slidesPerView: 4 }, 992: { slidesPerView: 3 }, 576: { slidesPerView: 2 }, 0: { slidesPerView: 1 } } }); } }, 0); };
        onMounted(() => setTab('all'));
        return { activeTab, tabs, filteredCars, placeholdersUrl, handleImageError, t, setTab };
      }
    };

    const Footer = {
      template: `<footer class="footer"><div class="container"><img :src="placeholdersUrl + '/logo.svg'" alt="Volvo" @error="handleImageError" /><p>{{ $t('footer.volvoPoland') }}</p><div class="footer__grid"><div v-for="col in columns" :key="col.title"><h4>{{ t(col.title) }}</h4><ul><li v-for="link in col.links" :key="link.url"><a :href="link.url">{{ t(link.text) }}</a></li></ul></div></div><p>&copy; 2024 {{ $t('footer.volvoPoland') }}. {{ t('Wszystkie prawa zastrzeżone') }}.</p></div></footer>`,
      setup() {
        const t = (text) => defaultLocale === 'cs' ? translateToCs(text) : text;
        const columns = [{ title: 'Modele', links: [{ text: 'XC90', url: '/modele/xc90' }, { text: 'XC60', url: '/modele/xc60' }, { text: 'XC40', url: '/modele/xc40' }] }, { title: 'Oferta', links: [{ text: 'Oferta', url: '/oferty' }, { text: 'Finansowanie', url: '/finansowanie' }] }, { title: 'Serwis', links: [{ text: 'Serwis', url: '/serwis' }, { text: 'Części', url: '/serwis/czesci' }] }, { title: 'Kontakt', links: [{ text: 'Kontakt', url: '/kontakt' }, { text: 'Salony', url: '/salony' }] }];
        return { columns, placeholdersUrl, handleImageError, t };
      }
    };

    const Home = { components: { HeroSlider, ShoppingBox, DiscoveryCard, CarModels }, template: `<div><HeroSlider /><ShoppingBox /><DiscoveryCard /><CarModels /></div>` };
    const App = { components: { Header, Footer }, template: `<div class="app"><Header /><main><router-view /></main><Footer /></div>` };
    const router = createRouter({ history: createWebHistory(), routes: [{ path: '/', component: Home }] });
    const app = createApp(App);
    app.use(router);
    app.use(i18n);
    app.mount('#app');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
  } else {
    initApp();
  }
})();
