// Volvo Dealers Vue - Bundled App (No ES Modules)
// This version works with WordPress without CORS issues

(function() {
  'use strict';

  const { createApp, ref, computed, onMounted, onUnmounted } = Vue;
  const { createRouter, createWebHistory } = VueRouter;
  const { createI18n } = VueI18n;

  // Translations
  const messages = {
    pl: {
      nav: {
        models: 'Modele', offers: 'Oferta', available: 'Dostępne od ręki', used: 'Używane Selekt',
        tradeIn: 'Wycena Volvo', service: 'Serwis', testDrive: 'Jazda testowa', blog: 'Blog',
        financing: 'Finansowanie', configurator: 'Konfigurator', accessories: 'Akcesoria',
        events: 'Wydarzenia', jewelry: 'pARTs Jewelry', showroom: 'Volvo Showroom',
        travel: 'Podróżuję Volvo', contact: 'Kontakt', career: 'Kariera',
        whistleblowers: 'Whistleblowers', search: 'Szukaj'
      },
      hero: { learnMore: 'Dowiedz się więcej', from: 'Już od', monthly: 'netto miesięcznie' },
      carModels: {
        title: 'Odkryj gamę', titleBold: 'modeli Volvo',
        category: { suv: 'SUV', wagon: 'Kombi', electric: 'Elektryczne' },
        price: { from: 'Od', currency: 'PLN' }, cta: 'Odkryj'
      },
      shoppingBox: {
        polestar: { title: 'Volvo Polestar', description: 'Nowe emocje w Twoim starszym aucie.' },
        battery: { title: 'Volvo Battery', description: 'Sprawdź to.' },
        service: { title: 'Serwis rosnących rabatów', description: 'Im starsze Twoje auto, tym większe rabaty.' },
        wallbox: { title: 'Volvo Wallbox', description: 'Energia, którą daje Ci Volvo.' }
      },
      discovery: { category: 'oferta', cta: 'Sprawdź' },
      footer: {
        volvoPoland: 'Volvo Car Poland',
        social: { facebook: 'Facebook', instagram: 'Instagram', linkedin: 'LinkedIn', youtube: 'YouTube' }
      },
      common: { close: 'Zamknij', open: 'Otwórz', readMore: 'Czytaj więcej', submit: 'Wyślij', loading: 'Ładowanie...', error: 'Wystąpił błąd', notFound: 'Nie znaleziono' }
    },
    en: {
      nav: {
        models: 'Our Models', offers: 'Offers', available: 'Available Now', used: 'Used Selekt',
        tradeIn: 'Value Your Volvo', service: 'Service', testDrive: 'Test Drive', blog: 'Blog',
        financing: 'Financing', configurator: 'Configurator', accessories: 'Accessories',
        events: 'Events', jewelry: 'pARTs Jewelry', showroom: 'Volvo Showroom',
        travel: 'I Travel Volvo', contact: 'Contact', career: 'Career',
        whistleblowers: 'Whistleblowers', search: 'Search'
      },
      hero: { learnMore: 'Learn More', from: 'From', monthly: 'net per month' },
      carModels: {
        title: 'Discover the', titleBold: 'Volvo range',
        category: { suv: 'SUV', wagon: 'Wagon', electric: 'Electric' },
        price: { from: 'From', currency: 'PLN' }, cta: 'Discover'
      },
      shoppingBox: {
        polestar: { title: 'Volvo Polestar', description: 'New emotions in your older car.' },
        battery: { title: 'Volvo Battery', description: 'Check it out.' },
        service: { title: 'Growing Discounts Service', description: 'The older your car, the bigger the discounts.' },
        wallbox: { title: 'Volvo Wallbox', description: 'Energy that Volvo gives you.' }
      },
      discovery: { category: 'offer', cta: 'Check' },
      footer: {
        volvoPoland: 'Volvo Car Poland',
        social: { facebook: 'Facebook', instagram: 'Instagram', linkedin: 'LinkedIn', youtube: 'YouTube' }
      },
      common: { close: 'Close', open: 'Open', readMore: 'Read More', submit: 'Submit', loading: 'Loading...', error: 'An error occurred', notFound: 'Not Found' }
    },
    cs: {
      nav: {
        models: 'Naše modely', offers: 'Nabídky', available: 'Dostupné na místě', used: 'Použité Selekt',
        tradeIn: 'Oceněte své Volvo', service: 'Servis', testDrive: 'Testovací jízda', blog: 'Blog',
        financing: 'Financování', configurator: 'Konfigurátor', accessories: 'Příslušenství',
        events: 'Události', jewelry: 'pARTs Jewelry', showroom: 'Volvo Showroom',
        travel: 'Cestuji Volvo', contact: 'Kontakt', career: 'Kariéra',
        whistleblowers: 'Whistleblowers', search: 'Hledat'
      },
      hero: { learnMore: 'Zjistit více', from: 'Již od', monthly: 'netto měsíčně' },
      carModels: {
        title: 'Objevte řadu', titleBold: 'modelů Volvo',
        category: { suv: 'SUV', wagon: 'Kombi', electric: 'Elektrické' },
        price: { from: 'Od', currency: 'PLN' }, cta: 'Objevit'
      },
      shoppingBox: {
        polestar: { title: 'Volvo Polestar', description: 'Nové emoce ve vašem starším autě.' },
        battery: { title: 'Baterie Volvo', description: 'Zkontrolujte.' },
        service: { title: 'Servis rostoucích slev', description: 'Čím starší vaše auto, tím větší slevy.' },
        wallbox: { title: 'Wallbox Volvo', description: 'Energie, kterou vám Volvo dává.' }
      },
      discovery: { category: 'nabídka', cta: 'Zkontrolovat' },
      footer: {
        volvoPoland: 'Volvo Car Poland',
        social: { facebook: 'Facebook', instagram: 'Instagram', linkedin: 'LinkedIn', youtube: 'YouTube' }
      },
      common: { close: 'Zavřít', open: 'Otevřít', readMore: 'Číst více', submit: 'Odeslat', loading: 'Načítání...', error: 'Došlo k chybě', notFound: 'Nenalezeno' }
    }
  };

  // Get locale from WordPress
  const locale = (window.volvoThemeData && window.volvoThemeData.locale) || 'pl_PL';
  const langMap = { 'pl_PL': 'pl', 'en_US': 'en', 'cs_CZ': 'cs' };
  const defaultLocale = langMap[locale] || 'pl';

  // Create i18n instance
  const i18n = createI18n({
    legacy: false,
    locale: defaultLocale,
    fallbackLocale: 'pl',
    messages
  });

  // Header Component
  const Header = {
    template: `
      <header class="header" :class="{ 'header--scrolled': isScrolled, 'header--menu-open': isMenuOpen }">
        <div class="header__container">
          <div class="header__logo">
            <a href="/" class="header__logo-link">
              <img src="https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/Wordmark-Black?qlt=82&wid=200" alt="Volvo" class="header__logo-img" />
            </a>
          </div>
          <nav class="header__nav header__nav--desktop">
            <ul class="header__nav-list">
              <li class="header__nav-item"><a href="/modele" class="header__nav-link">{{ $t('nav.models') }}</a></li>
              <li class="header__nav-item header__nav-item--has-dropdown">
                <span class="header__nav-link">{{ $t('nav.offers') }}</span>
                <ul class="header__dropdown">
                  <li><a href="/oferty/dostepne-od-reki" class="header__dropdown-link">{{ $t('nav.available') }}</a></li>
                  <li><a href="/oferty/uzywane-selekt" class="header__dropdown-link">{{ $t('nav.used') }}</a></li>
                  <li><a href="/oferty/wycena-volvo" class="header__dropdown-link">{{ $t('nav.tradeIn') }}</a></li>
                </ul>
              </li>
              <li class="header__nav-item"><a href="/serwis" class="header__nav-link">{{ $t('nav.service') }}</a></li>
              <li class="header__nav-item"><a href="/jazda-testowa" class="header__nav-link">{{ $t('nav.testDrive') }}</a></li>
              <li class="header__nav-item"><a href="/blog" class="header__nav-link">{{ $t('nav.blog') }}</a></li>
              <li class="header__nav-item header__nav-item--has-dropdown">
                <span class="header__nav-link">{{ $t('nav.financing') }}</span>
                <ul class="header__dropdown">
                  <li><a href="/finansowanie/konfigurator" class="header__dropdown-link">{{ $t('nav.configurator') }}</a></li>
                  <li><a href="/finansowanie/akcesoria" class="header__dropdown-link">{{ $t('nav.accessories') }}</a></li>
                </ul>
              </li>
              <li class="header__nav-item header__nav-item--has-dropdown">
                <span class="header__nav-link">{{ $t('nav.events') }}</span>
                <ul class="header__dropdown">
                  <li><a href="/wydarzenia/parts-jewelry" class="header__dropdown-link">{{ $t('nav.jewelry') }}</a></li>
                  <li><a href="/wydarzenia/volvo-showroom" class="header__dropdown-link">{{ $t('nav.showroom') }}</a></li>
                  <li><a href="/wydarzenia/podrozuje-volvo" class="header__dropdown-link">{{ $t('nav.travel') }}</a></li>
                </ul>
              </li>
              <li class="header__nav-item"><a href="/kontakt" class="header__nav-link">{{ $t('nav.contact') }}</a></li>
            </ul>
          </nav>
          <div class="header__actions">
            <button class="header__search-btn" @click="toggleSearch">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
              </svg>
            </button>
            <button class="header__menu-toggle" @click="toggleMenu">
              <span class="header__menu-bar"></span>
              <span class="header__menu-bar"></span>
              <span class="header__menu-bar"></span>
            </button>
          </div>
        </div>
        <nav class="header__nav header__nav--mobile" v-show="isMenuOpen">
          <ul class="header__nav-list">
            <li class="header__nav-item"><a href="/modele" class="header__nav-link" @click="closeMenu">{{ $t('nav.models') }}</a></li>
            <li class="header__nav-item"><a href="/oferty" class="header__nav-link" @click="closeMenu">{{ $t('nav.offers') }}</a></li>
            <li class="header__nav-item"><a href="/serwis" class="header__nav-link" @click="closeMenu">{{ $t('nav.service') }}</a></li>
            <li class="header__nav-item"><a href="/jazda-testowa" class="header__nav-link" @click="closeMenu">{{ $t('nav.testDrive') }}</a></li>
            <li class="header__nav-item"><a href="/blog" class="header__nav-link" @click="closeMenu">{{ $t('nav.blog') }}</a></li>
            <li class="header__nav-item"><a href="/finansowanie" class="header__nav-link" @click="closeMenu">{{ $t('nav.financing') }}</a></li>
            <li class="header__nav-item"><a href="/wydarzenia" class="header__nav-link" @click="closeMenu">{{ $t('nav.events') }}</a></li>
            <li class="header__nav-item"><a href="/kontakt" class="header__nav-link" @click="closeMenu">{{ $t('nav.contact') }}</a></li>
          </ul>
        </nav>
        <div class="header__search-overlay" v-if="isSearchOpen" @click.self="closeSearch">
          <div class="header__search-container">
            <input type="text" class="header__search-input" :placeholder="$t('nav.search')" v-model="searchQuery" @keyup.enter="performSearch" />
            <button class="header__search-close" @click="closeSearch">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        </div>
      </header>
    `,
    setup() {
      const isScrolled = ref(false);
      const isMenuOpen = ref(false);
      const isSearchOpen = ref(false);
      const searchQuery = ref('');

      const handleScroll = () => { isScrolled.value = window.scrollY > 50; };
      const toggleMenu = () => { isMenuOpen.value = !isMenuOpen.value; };
      const closeMenu = () => { isMenuOpen.value = false; };
      const toggleSearch = () => { isSearchOpen.value = !isSearchOpen.value; };
      const closeSearch = () => { isSearchOpen.value = false; searchQuery.value = ''; };
      const performSearch = () => { if (searchQuery.value.trim()) window.location.href = '/?s=' + encodeURIComponent(searchQuery.value); };

      onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }));
      onUnmounted(() => window.removeEventListener('scroll', handleScroll));

      return { isScrolled, isMenuOpen, isSearchOpen, searchQuery, toggleMenu, closeMenu, toggleSearch, closeSearch, performSearch };
    }
  };

  // HeroSlider Component
  const HeroSlider = {
    template: `
      <section class="hero-slider">
        <div class="swiper hero-slider__swiper">
          <div class="swiper-wrapper">
            <div v-for="(slide, index) in slides" :key="index" class="swiper-slide hero-slider__slide">
              <div class="hero-slider__background">
                <img :src="slide.image" :alt="slide.title" class="hero-slider__image" />
                <div class="hero-slider__overlay"></div>
              </div>
              <div class="hero-slider__content">
                <div class="hero-slider__container">
                  <div class="hero-slider__text">
                    <span v-if="slide.subtitle" class="hero-slider__subtitle">{{ slide.subtitle }}</span>
                    <h2 class="hero-slider__title">{{ slide.title }}</h2>
                    <p v-if="slide.description" class="hero-slider__description">{{ slide.description }}</p>
                    <div v-if="slide.price" class="hero-slider__price">
                      <span class="hero-slider__price-label">{{ $t('hero.from') }}</span>
                      <span class="hero-slider__price-value">{{ slide.price }}</span>
                      <span class="hero-slider__price-period">{{ $t('hero.monthly') }}</span>
                    </div>
                    <div class="hero-slider__actions">
                      <a :href="slide.link" class="btn btn--primary hero-slider__cta">{{ $t('hero.learnMore') }}</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="swiper-pagination"></div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-button-next"></div>
        </div>
      </section>
    `,
    setup() {
      const slides = [
        { image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/2560x1440-volvo-xc90-recharge-hero?qlt=82&wid=1920', subtitle: 'Nowe Volvo XC90', title: 'Przestrzeń dla Twoich marzeń', description: 'Luksusowy SUV, który zmienia reguły gry.', price: '3 490 PLN', link: '/modele/xc90' },
        { image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/2560x1440-volvo-ex30-hero?qlt=82&wid=1920', subtitle: 'Elektryzujące', title: 'Volvo EX30', description: 'Najmniejsze Volvo, jakie kiedykolwiek stworzyliśmy.', price: '1 890 PLN', link: '/modele/ex30' },
        { image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/2560x1440-volvo-em90-hero?qlt=82&wid=1920', subtitle: 'Luksus w czystej formie', title: 'Volvo EM90', description: 'Pierwszy luksusowy elektryczny van Volvo.', price: '5 490 PLN', link: '/modele/em90' }
      ];

      onMounted(() => {
        if (typeof Swiper !== 'undefined') {
          new Swiper('.hero-slider__swiper', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
          });
        }
      });

      return { slides };
    }
  };

  // ShoppingBox Component
  const ShoppingBox = {
    template: `
      <section class="shopping-box">
        <div class="container">
          <div class="shopping-box__grid">
            <div v-for="(item, index) in items" :key="index" class="shopping-box__item" :class="'shopping-box__item--' + item.type">
              <div class="shopping-box__image"><img :src="item.image" :alt="item.title" /></div>
              <div class="shopping-box__content">
                <h3 class="shopping-box__title">{{ item.title }}</h3>
                <p class="shopping-box__description">{{ item.description }}</p>
                <a :href="item.link" class="shopping-box__link">{{ $t('hero.learnMore') }} →</a>
              </div>
            </div>
          </div>
        </div>
      </section>
    `,
    setup() {
      const items = [
        { type: 'polestar', title: 'Volvo Polestar', description: 'Nowe emocje w Twoim starszym aucie.', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/polestar-engineering-badge?qlt=82&wid=400', link: '/oferty/polestar' },
        { type: 'battery', title: 'Volvo Battery', description: 'Sprawdź to.', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/volvo-battery-service?qlt=82&wid=400', link: '/serwis/bateria' },
        { type: 'service', title: 'Serwis rosnących rabatów', description: 'Im starsze Twoje auto, tym większe rabaty.', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/volvo-service-discount?qlt=82&wid=400', link: '/serwis/rabaty' },
        { type: 'wallbox', title: 'Volvo Wallbox', description: 'Energia, którą daje Ci Volvo.', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/volvo-wallbox-charger?qlt=82&wid=400', link: '/elektromobilnosc/wallbox' }
      ];
      return { items };
    }
  };

  // DiscoveryCard Component
  const DiscoveryCard = {
    template: `
      <section class="discovery-cards">
        <div class="container">
          <div class="discovery-cards__grid">
            <div v-for="(card, index) in cards" :key="index" class="discovery-cards__item" :class="{ 'discovery-cards__item--large': card.large }">
              <div class="discovery-cards__image"><img :src="card.image" :alt="card.title" /><div class="discovery-cards__overlay"></div></div>
              <div class="discovery-cards__content">
                <span class="discovery-cards__category">{{ $t('discovery.category') }}</span>
                <h3 class="discovery-cards__title">{{ card.title }}</h3>
                <p class="discovery-cards__description">{{ card.description }}</p>
                <a :href="card.link" class="btn btn--secondary discovery-cards__cta">{{ $t('discovery.cta') }}</a>
              </div>
            </div>
          </div>
        </div>
      </section>
    `,
    setup() {
      const cards = [
        { title: 'Oferta specjalna Volvo XC60', description: 'Wyjątkowe warunki finansowania i bogate wyposażenie standardowe.', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/1920x1080-volvo-xc60-discovery?qlt=82&wid=800', link: '/oferty/xc60', large: true },
        { title: 'Jazda testowa', description: 'Umów się na bezpłatną jazdę testową i przekonaj się sam.', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/1920x1080-test-drive-discovery?qlt=82&wid=800', link: '/jazda-testowa', large: false },
        { title: 'Serwis Volvo', description: 'Autoryzowany serwis i oryginalne części.', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/1920x1080-service-discovery?qlt=82&wid=800', link: '/serwis', large: false }
      ];
      return { cards };
    }
  };

  // CarModels Component
  const CarModels = {
    template: `
      <section class="car-models">
        <div class="container">
          <div class="car-models__header">
            <h2 class="car-models__title">{{ $t('carModels.title') }} <span class="car-models__title-bold">{{ $t('carModels.titleBold') }}</span></h2>
            <div class="car-models__tabs">
              <button v-for="tab in tabs" :key="tab.id" class="car-models__tab" :class="{ 'car-models__tab--active': activeTab === tab.id }" @click="activeTab = tab.id">{{ tab.label }}</button>
            </div>
          </div>
          <div class="car-models__carousel">
            <div class="swiper car-models__swiper">
              <div class="swiper-wrapper">
                <div v-for="car in filteredCars" :key="car.id" class="swiper-slide car-models__slide">
                  <div class="car-models__card">
                    <div class="car-models__image"><img :src="car.image" :alt="car.name" /></div>
                    <div class="car-models__info">
                      <h3 class="car-models__name">{{ car.name }}</h3>
                      <p class="car-models__type">{{ car.type }}</p>
                      <div class="car-models__price"><span class="car-models__price-label">{{ $t('carModels.price.from') }}</span><span class="car-models__price-value">{{ car.price }} {{ $t('carModels.price.currency') }}</span></div>
                      <a :href="car.link" class="car-models__cta">{{ $t('carModels.cta') }} →</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-button-prev"></div>
              <div class="swiper-button-next"></div>
            </div>
          </div>
        </div>
      </section>
    `,
    setup() {
      const activeTab = ref('all');
      const tabs = [
        { id: 'all', label: 'Wszystkie' },
        { id: 'suv', label: 'SUV' },
        { id: 'wagon', label: 'Kombi' },
        { id: 'electric', label: 'Elektryczne' }
      ];
      const cars = [
        { id: 1, name: 'XC90', type: 'SUV', category: 'suv', price: '351 900', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/xc90-recharge-side?qlt=82&wid=600', link: '/modele/xc90' },
        { id: 2, name: 'XC60', type: 'SUV', category: 'suv', price: '289 900', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/xc60-recharge-side?qlt=82&wid=600', link: '/modele/xc60' },
        { id: 3, name: 'XC40', type: 'SUV', category: 'suv', price: '199 900', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/xc40-recharge-side?qlt=82&wid=600', link: '/modele/xc40' },
        { id: 4, name: 'V90', type: 'Kombi', category: 'wagon', price: '319 900', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/v90-recharge-side?qlt=82&wid=600', link: '/modele/v90' },
        { id: 5, name: 'V60', type: 'Kombi', category: 'wagon', price: '249 900', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/v60-recharge-side?qlt=82&wid=600', link: '/modele/v60' },
        { id: 6, name: 'EX30', type: 'Elektryczny SUV', category: 'electric', price: '179 900', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/ex30-side?qlt=82&wid=600', link: '/modele/ex30' },
        { id: 7, name: 'EX90', type: 'Elektryczny SUV', category: 'electric', price: '449 900', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/ex90-side?qlt=82&wid=600', link: '/modele/ex90' },
        { id: 8, name: 'EM90', type: 'Elektryczny van', category: 'electric', price: '599 900', image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/em90-side?qlt=82&wid=600', link: '/modele/em90' }
      ];
      const filteredCars = computed(() => activeTab.value === 'all' ? cars : cars.filter(car => car.category === activeTab.value));

      onMounted(() => {
        if (typeof Swiper !== 'undefined') {
          new Swiper('.car-models__swiper', {
            slidesPerView: 4,
            spaceBetween: 24,
            loop: true,
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            breakpoints: { 1200: { slidesPerView: 4 }, 992: { slidesPerView: 3 }, 576: { slidesPerView: 2 }, 0: { slidesPerView: 1 } }
          });
        }
      });

      return { activeTab, tabs, filteredCars };
    }
  };

  // Footer Component
  const Footer = {
    template: `
      <footer class="footer">
        <div class="container">
          <div class="footer__top">
            <div class="footer__brand">
              <img src="https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/Wordmark-Black?qlt=82&wid=150" alt="Volvo" class="footer__logo" />
              <p class="footer__tagline">{{ $t('footer.volvoPoland') }}</p>
            </div>
            <div class="footer__social">
              <a v-for="social in socialLinks" :key="social.name" :href="social.url" class="footer__social-link" target="_blank" rel="noopener noreferrer">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path v-if="social.icon === 'facebook'" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  <path v-if="social.icon === 'instagram'" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                  <path v-if="social.icon === 'linkedin'" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729
