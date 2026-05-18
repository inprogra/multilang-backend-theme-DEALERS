<template>
  <header class="header" :class="{ 'header--scrolled': isScrolled, 'header--menu-open': isMenuOpen }">
    <div class="header__container">
      <!-- Logo -->
      <div class="header__logo">
        <router-link to="/" class="header__logo-link">
          <img 
            src="https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/Wordmark-Black?qlt=82&wid=200" 
            alt="Volvo" 
            class="header__logo-img"
          />
        </router-link>
      </div>

      <!-- Desktop Navigation -->
      <nav class="header__nav header__nav--desktop">
        <ul class="header__nav-list">
          <li class="header__nav-item">
            <router-link to="/modele" class="header__nav-link">{{ $t('nav.models') }}</router-link>
          </li>
          <li class="header__nav-item header__nav-item--has-dropdown">
            <span class="header__nav-link">{{ $t('nav.offers') }}</span>
            <ul class="header__dropdown">
              <li><router-link to="/oferty/dostepne-od-reki" class="header__dropdown-link">{{ $t('nav.available') }}</router-link></li>
              <li><router-link to="/oferty/uzywane-selekt" class="header__dropdown-link">{{ $t('nav.used') }}</router-link></li>
              <li><router-link to="/oferty/wycena-volvo" class="header__dropdown-link">{{ $t('nav.tradeIn') }}</router-link></li>
            </ul>
          </li>
          <li class="header__nav-item">
            <router-link to="/serwis" class="header__nav-link">{{ $t('nav.service') }}</router-link>
          </li>
          <li class="header__nav-item">
            <router-link to="/jazda-testowa" class="header__nav-link">{{ $t('nav.testDrive') }}</router-link>
          </li>
          <li class="header__nav-item">
            <router-link to="/blog" class="header__nav-link">{{ $t('nav.blog') }}</router-link>
          </li>
          <li class="header__nav-item header__nav-item--has-dropdown">
            <span class="header__nav-link">{{ $t('nav.financing') }}</span>
            <ul class="header__dropdown">
              <li><router-link to="/finansowanie/konfigurator" class="header__dropdown-link">{{ $t('nav.configurator') }}</router-link></li>
              <li><router-link to="/finansowanie/akcesoria" class="header__dropdown-link">{{ $t('nav.accessories') }}</router-link></li>
            </ul>
          </li>
          <li class="header__nav-item header__nav-item--has-dropdown">
            <span class="header__nav-link">{{ $t('nav.events') }}</span>
            <ul class="header__dropdown">
              <li><router-link to="/wydarzenia/parts-jewelry" class="header__dropdown-link">{{ $t('nav.jewelry') }}</router-link></li>
              <li><router-link to="/wydarzenia/volvo-showroom" class="header__dropdown-link">{{ $t('nav.showroom') }}</router-link></li>
              <li><router-link to="/wydarzenia/podrozuje-volvo" class="header__dropdown-link">{{ $t('nav.travel') }}</router-link></li>
            </ul>
          </li>
          <li class="header__nav-item">
            <router-link to="/kontakt" class="header__nav-link">{{ $t('nav.contact') }}</router-link>
          </li>
        </ul>
      </nav>

      <!-- Right Side Actions -->
      <div class="header__actions">
        <button class="header__search-btn" @click="toggleSearch" aria-label="Search">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
        </button>
        
        <!-- Mobile Menu Toggle -->
        <button class="header__menu-toggle" @click="toggleMenu" aria-label="Toggle menu">
          <span class="header__menu-bar"></span>
          <span class="header__menu-bar"></span>
          <span class="header__menu-bar"></span>
        </button>
      </div>
    </div>

    <!-- Mobile Navigation -->
    <nav class="header__nav header__nav--mobile" v-show="isMenuOpen">
      <ul class="header__nav-list">
        <li class="header__nav-item">
          <router-link to="/modele" class="header__nav-link" @click="closeMenu">{{ $t('nav.models') }}</router-link>
        </li>
        <li class="header__nav-item">
          <router-link to="/oferty" class="header__nav-link" @click="closeMenu">{{ $t('nav.offers') }}</router-link>
        </li>
        <li class="header__nav-item">
          <router-link to="/serwis" class="header__nav-link" @click="closeMenu">{{ $t('nav.service') }}</router-link>
        </li>
        <li class="header__nav-item">
          <router-link to="/jazda-testowa" class="header__nav-link" @click="closeMenu">{{ $t('nav.testDrive') }}</router-link>
        </li>
        <li class="header__nav-item">
          <router-link to="/blog" class="header__nav-link" @click="closeMenu">{{ $t('nav.blog') }}</router-link>
        </li>
        <li class="header__nav-item">
          <router-link to="/finansowanie" class="header__nav-link" @click="closeMenu">{{ $t('nav.financing') }}</router-link>
        </li>
        <li class="header__nav-item">
          <router-link to="/wydarzenia" class="header__nav-link" @click="closeMenu">{{ $t('nav.events') }}</router-link>
        </li>
        <li class="header__nav-item">
          <router-link to="/kontakt" class="header__nav-link" @click="closeMenu">{{ $t('nav.contact') }}</router-link>
        </li>
      </ul>
    </nav>

    <!-- Search Overlay -->
    <div class="header__search-overlay" v-if="isSearchOpen" @click.self="closeSearch">
      <div class="header__search-container">
        <input 
          type="text" 
          class="header__search-input" 
          :placeholder="$t('nav.search')"
          v-model="searchQuery"
          @keyup.enter="performSearch"
        />
        <button class="header__search-close" @click="closeSearch">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
  name: 'Header',
  setup() {
    const isScrolled = ref(false)
    const isMenuOpen = ref(false)
    const isSearchOpen = ref(false)
    const searchQuery = ref('')

    const handleScroll = () => {
      isScrolled.value = window.scrollY > 50
    }

    const toggleMenu = () => {
      isMenuOpen.value = !isMenuOpen.value
      document.body.style.overflow = isMenuOpen.value ? 'hidden' : ''
    }

    const closeMenu = () => {
      isMenuOpen.value = false
      document.body.style.overflow = ''
    }

    const toggleSearch = () => {
      isSearchOpen.value = !isSearchOpen.value
      if (isSearchOpen.value) {
        setTimeout(() => {
          document.querySelector('.header__search-input')?.focus()
        }, 100)
      }
    }

    const closeSearch = () => {
      isSearchOpen.value = false
      searchQuery.value = ''
    }

    const performSearch = () => {
      if (searchQuery.value.trim()) {
        // Navigate to search results
        window.location.href = `/?s=${encodeURIComponent(searchQuery.value)}`
      }
    }

    onMounted(() => {
      window.addEventListener('scroll', handleScroll, { passive: true })
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll)
    })

    return {
      isScrolled,
      isMenuOpen,
      isSearchOpen,
      searchQuery,
      toggleMenu,
      closeMenu,
      toggleSearch,
      closeSearch,
      performSearch
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../styles/variables';

.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background-color: $white;
  border-bottom: 1px solid transparent;
  transition: all 0.3s ease;

  &--scrolled {
    border-bottom-color: $header-border;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  }

  &__container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: $container-max-width;
    margin: 0 auto;
    padding: 0 $container-padding-x;
    height: $header-height;

    @media (max-width: $breakpoint-md) {
      height: $header-height-mobile;
      padding: 0 $container-padding-x-sm;
    }
  }

  &__logo {
    flex-shrink: 0;

    &-link {
      display: block;
    }

    &-img {
      height: 24px;
      width: auto;
      display: block;
    }
  }

  &__nav {
    &--desktop {
      @media (max-width: $breakpoint-lg) {
        display: none;
      }

      .header__nav-list {
        display: flex;
        align-items: center;
        gap: $spacer-xl;
        list-style: none;
        margin: 0;
        padding: 0;
      }

      .header__nav-item {
        position: relative;

        &--has-dropdown {
          .header__nav-link {
            cursor: pointer;
            
            &::after {
              content: '';
              display: inline-block;
              width: 0;
              height: 0;
              margin-left: 6px;
              vertical-align: middle;
              border-top: 4px solid currentColor;
              border-right: 4px solid transparent;
              border-left: 4px solid transparent;
            }
          }

          &:hover {
            .header__dropdown {
              opacity: 1;
              visibility: visible;
              transform: translateY(0);
            }
          }
        }
      }

      .header__nav-link {
        display: flex;
        align-items: center;
        font-size: $nav-font-size;
        font-weight: $nav-font-weight;
        color: $nav-link-color;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: $spacer-sm 0;
        transition: color 0.2s ease;

        &:hover {
          color: $nav-link-hover;
        }

        &.router-link-active {
          color: $nav-link-hover;
        }
      }

      .header__dropdown {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        background-color: $white;
        border: 1px solid $grey-200;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        min-width: 220px;
        padding: $spacer-sm 0;
        list-style: none;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;

        li {
          margin: 0;
        }
      }

      .header__dropdown-link {
        display: block;
        padding: $spacer-sm $spacer-md;
        font-size: $font-size-sm;
        color: $body-color;
        transition: all 0.2s ease;

        &:hover {
          background-color: $grey-100;
          color: $blue-light;
        }
      }
    }

    &--mobile {
      position: fixed;
      top: $header-height-mobile;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: $white;
      padding: $spacer-lg;
      overflow-y: auto;

      .header__nav-list {
        list-style: none;
        margin: 0;
        padding: 0;
      }

      .header__nav-item {
        border-bottom: 1px solid $grey-200;
      }

      .header__nav-link {
        display: block;
        padding: $spacer-md 0;
        font-size: $font-size-lg;
        font-weight: $font-weight-medium;
        color: $body-color;
        text-transform: uppercase;
        letter-spacing: 0.05em;

        &.router-link-active {
          color: $blue-light;
        }
      }
    }
  }

  &__actions {
    display: flex;
    align-items: center;
    gap: $spacer-md;
  }

  &__search-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: none;
    border: none;
    color: $body-color;
    cursor: pointer;
    transition: color 0.2s ease;

    &:hover {
      color: $blue-light;
    }
  }

  &__menu-toggle {
    display: none;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    background: none;
    border: none;
    cursor: pointer;
    gap: 5px;

    @media (max-width: $breakpoint-lg) {
      display: flex;
    }
  }

  &__menu-bar {
    display: block;
    width: 24px;
    height: 2px;
    background-color: $body-color;
    transition: all 0.3s ease;
  }

  &--menu-open {
    .header__menu-bar {
      &:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
      }
      &:nth-child(2) {
        opacity: 0;
      }
      &:nth-child(3) {
        transform: rotate(-45deg) translate(5px, -5px);
      }
    }
  }

  &__search-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 20vh;
    z-index: 1001;
  }

  &__search-container {
    position: relative;
    width: 90%;
    max-width: 600px;
  }

  &__search-input {
    width: 100%;
    padding: $spacer-md $spacer-xl $spacer-md $spacer-md;
    font-size: $font-size-xl;
    font-family: $font-family-base;
    background-color: $white;
    border: none;
    border-radius: 0;
    outline: none;

    &::placeholder {
      color: $grey-400;
    }
  }

  &__search-close {
    position: absolute;
    right: $spacer-md;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: $grey-400;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: $spacer-xs;

    &:hover {
      color: $body-color;
    }
  }
}
</style>
