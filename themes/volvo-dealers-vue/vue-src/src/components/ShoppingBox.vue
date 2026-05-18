<template>
  <section class="shopping-box">
    <div class="container">
      <div class="shopping-box__grid">
        <div 
          v-for="(item, index) in items" 
          :key="index"
          class="shopping-box__item"
          :class="`shopping-box__item--${item.type}`"
        >
          <div class="shopping-box__image">
            <img :src="item.image" :alt="item.title" />
          </div>
          <div class="shopping-box__content">
            <h3 class="shopping-box__title">{{ item.title }}</h3>
            <p class="shopping-box__description">{{ item.description }}</p>
            <router-link :to="item.link" class="shopping-box__link">
              {{ $t('hero.learnMore') }}
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'ShoppingBox',
  setup() {
    const items = [
      {
        type: 'polestar',
        title: 'Volvo Polestar',
        description: 'Nowe emocje w Twoim starszym aucie.',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/polestar-engineering-badge?qlt=82&wid=400',
        link: '/oferty/polestar'
      },
      {
        type: 'battery',
        title: 'Volvo Battery',
        description: 'Sprawdź to.',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/volvo-battery-service?qlt=82&wid=400',
        link: '/serwis/bateria'
      },
      {
        type: 'service',
        title: 'Serwis rosnących rabatów',
        description: 'Im starsze Twoje auto, tym większe rabaty.',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/volvo-service-discount?qlt=82&wid=400',
        link: '/serwis/rabaty'
      },
      {
        type: 'wallbox',
        title: 'Volvo Wallbox',
        description: 'Energia, którą daje Ci Volvo.',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/volvo-wallbox-charger?qlt=82&wid=400',
        link: '/elektromobilnosc/wallbox'
      }
    ]

    return {
      items
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../styles/variables';

.shopping-box {
  padding: $spacer-3xl 0;
  background-color: $grey-100;

  @media (max-width: $breakpoint-md) {
    padding: $spacer-2xl 0;
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: $spacer-lg;

    @media (max-width: $breakpoint-lg) {
      grid-template-columns: repeat(2, 1fr);
    }

    @media (max-width: $breakpoint-sm) {
      grid-template-columns: 1fr;
      gap: $spacer-md;
    }
  }

  &__item {
    background-color: $white;
    border: 1px solid $grey-200;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;

    &:hover {
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
      transform: translateY(-4px);

      .shopping-box__image img {
        transform: scale(1.05);
      }
    }
  }

  &__image {
    position: relative;
    padding-top: 60%;
    overflow: hidden;
    background-color: $grey-100;

    img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
  }

  &__content {
    padding: $spacer-lg;
    flex: 1;
    display: flex;
    flex-direction: column;

    @media (max-width: $breakpoint-md) {
      padding: $spacer-md;
    }
  }

  &__title {
    font-size: $font-size-lg;
    font-weight: $font-weight-semibold;
    color: $primary;
    margin-bottom: $spacer-sm;
    line-height: $line-height-tight;
  }

  &__description {
    font-size: $font-size-sm;
    color: $grey-400;
    margin-bottom: $spacer-md;
    line-height: $line-height-normal;
    flex: 1;
  }

  &__link {
    display: inline-flex;
    align-items: center;
    gap: $spacer-xs;
    font-size: $font-size-sm;
    font-weight: $font-weight-medium;
    color: $blue-light;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: all 0.2s ease;

    svg {
      transition: transform 0.2s ease;
    }

    &:hover {
      color: $hover;

      svg {
        transform: translateX(4px);
      }
    }
  }
}
</style>
