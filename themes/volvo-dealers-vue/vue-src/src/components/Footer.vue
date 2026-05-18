<template>
  <footer class="footer">
    <div class="container">
      <div class="footer__top">
        <div class="footer__brand">
          <img 
            src="https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/Wordmark-Black?qlt=82&wid=150" 
            alt="Volvo" 
            class="footer__logo"
          />
          <p class="footer__tagline">{{ $t('footer.volvoPoland') }}</p>
        </div>
        
        <div class="footer__social">
          <a 
            v-for="social in socialLinks" 
            :key="social.name"
            :href="social.url"
            class="footer__social-link"
            target="_blank"
            rel="noopener noreferrer"
            :aria-label="social.name"
          >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path v-if="social.icon === 'facebook'" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              <path v-if="social.icon === 'instagram'" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              <path v-if="social.icon === 'linkedin'" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              <path v-if="social.icon === 'youtube'" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
          </a>
        </div>
      </div>

      <div class="footer__grid">
        <div class="footer__column">
          <h4 class="footer__heading">Modele</h4>
          <ul class="footer__links">
            <li><router-link to="/modele/xc90">XC90</router-link></li>
            <li><router-link to="/modele/xc60">XC60</router-link></li>
            <li><router-link to="/modele/xc40">XC40</router-link></li>
            <li><router-link to="/modele/v90">V90</router-link></li>
            <li><router-link to="/modele/v60">V60</router-link></li>
            <li><router-link to="/modele/ex30">EX30</router-link></li>
            <li><router-link to="/modele/ex90">EX90</router-link></li>
          </ul>
        </div>

        <div class="footer__column">
          <h4 class="footer__heading">Oferta</h4>
          <ul class="footer__links">
            <li><router-link to="/oferty/dostepne-od-reki">Dostępne od ręki</router-link></li>
            <li><router-link to="/oferty/uzywane-selekt">Używane Selekt</router-link></li>
            <li><router-link to="/oferty/wycena-volvo">Wycena Volvo</router-link></li>
            <li><router-link to="/finansowanie">Finansowanie</router-link></li>
            <li><router-link to="/jazda-testowa">Jazda testowa</router-link></li>
          </ul>
        </div>

        <div class="footer__column">
          <h4 class="footer__heading">Serwis</h4>
          <ul class="footer__links">
            <li><router-link to="/serwis">Autoryzowany serwis</router-link></li>
            <li><router-link to="/serwis/umow-wizyte">Umów wizytę</router-link></li>
            <li><router-link to="/serwis/akcesoria">Akcesoria</router-link></li>
            <li><router-link to="/serwis/czesci">Części</router-link></li>
            <li><router-link to="/serwis/ubezpieczenia">Ubezpieczenia</router-link></li>
          </ul>
        </div>

        <div class="footer__column">
          <h4 class="footer__heading">O nas</h4>
          <ul class="footer__links">
            <li><router-link to="/o-nas">O firmie</router-link></li>
            <li><router-link to="/kontakt">Kontakt</router-link></li>
            <li><router-link to="/kariera">{{ $t('nav.career') }}</router-link></li>
            <li><router-link to="/whistleblowers">{{ $t('nav.whistleblowers') }}</router-link></li>
            <li><router-link to="/blog">Blog</router-link></li>
          </ul>
        </div>
      </div>

      <div class="footer__bottom">
        <div class="footer__legal">
          <router-link to="/polityka-prywatnosci">Polityka prywatności</router-link>
          <router-link to="/cookies">Cookies</router-link>
          <router-link to="/regulamin">Regulamin</router-link>
        </div>
        <p class="footer__copyright">
          © {{ currentYear }} Volvo Car Poland. Wszelkie prawa zastrzeżone.
        </p>
      </div>
    </div>
  </footer>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'Footer',
  setup() {
    const currentYear = computed(() => new Date().getFullYear())

    const socialLinks = [
      { name: 'Facebook', icon: 'facebook', url: 'https://www.facebook.com/VolvoCarsPoland' },
      { name: 'Instagram', icon: 'instagram', url: 'https://www.instagram.com/volvocarspoland/' },
      { name: 'LinkedIn', icon: 'linkedin', url: 'https://www.linkedin.com/company/volvo-car-poland/' },
      { name: 'YouTube', icon: 'youtube', url: 'https://www.youtube.com/user/VolvoCarsPoland' }
    ]

    return {
      currentYear,
      socialLinks
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../styles/variables';

.footer {
  background-color: $grey-100;
  border-top: 1px solid $grey-200;
  padding: $spacer-3xl 0 $spacer-xl;

  @media (max-width: $breakpoint-md) {
    padding: $spacer-2xl 0 $spacer-lg;
  }

  &__top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: $spacer-2xl;
    padding-bottom: $spacer-xl;
    border-bottom: 1px solid $grey-200;

    @media (max-width: $breakpoint-md) {
      flex-direction: column;
      gap: $spacer-lg;
      margin-bottom: $spacer-xl;
    }
  }

  &__brand {
    .footer__logo {
      height: 20px;
      width: auto;
      margin-bottom: $spacer-sm;
    }

    .footer__tagline {
      font-size: $font-size-sm;
      color: $grey-400;
      margin: 0;
    }
  }

  &__social {
    display: flex;
    gap: $spacer-md;

    &-link {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      color: $grey-400;
      transition: all 0.2s ease;

      &:hover {
        color: $blue-light;
        transform: translateY(-2px);
      }
    }
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: $spacer-xl;
    margin-bottom: $spacer-2xl;

    @media (max-width: $breakpoint-lg) {
      grid-template-columns: repeat(2, 1fr);
    }

    @media (max-width: $breakpoint-sm) {
      grid-template-columns: 1fr;
      gap: $spacer-lg;
    }
  }

  &__column {
    .footer__heading {
      font-size: $font-size-sm;
      font-weight: $font-weight-semibold;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: $primary;
      margin-bottom: $spacer-md;
    }

    .footer__links {
      list-style: none;
      margin: 0;
      padding: 0;

      li {
        margin-bottom: $spacer-sm;
      }

      a {
        font-size: $font-size-sm;
        color: $grey-400;
        transition: color 0.2s ease;

        &:hover {
          color: $blue-light;
        }
      }
    }
  }

  &__bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: $spacer-xl;
    border-top: 1px solid $grey-200;

    @media (max-width: $breakpoint-md) {
      flex-direction: column;
      gap: $spacer-md;
      text-align: center;
    }
  }

  &__legal {
    display: flex;
    gap: $spacer-lg;

    @media (max-width: $breakpoint-sm) {
      flex-wrap: wrap;
      justify-content: center;
      gap: $spacer-md;
    }

    a {
      font-size: $font-size-xs;
      color: $grey-400;
      transition: color 0.2s ease;

      &:hover {
        color: $blue-light;
      }
    }
  }

  &__copyright {
    font-size: $font-size-xs;
    color: $grey-400;
    margin: 0;
  }
}
</style>
