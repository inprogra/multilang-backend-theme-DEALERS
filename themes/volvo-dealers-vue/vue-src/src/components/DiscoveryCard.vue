<template>
  <section class="discovery-cards">
    <div class="container">
      <div class="discovery-cards__grid">
        <div 
          v-for="(card, index) in cards" 
          :key="index"
          class="discovery-cards__item"
          :class="{ 'discovery-cards__item--large': card.large }"
        >
          <div class="discovery-cards__image">
            <img :src="card.image" :alt="card.title" />
            <div class="discovery-cards__overlay"></div>
          </div>
          <div class="discovery-cards__content">
            <span class="discovery-cards__category">{{ $t('discovery.category') }}</span>
            <h3 class="discovery-cards__title">{{ card.title }}</h3>
            <p class="discovery-cards__description">{{ card.description }}</p>
            <router-link :to="card.link" class="btn btn--secondary discovery-cards__cta">
              {{ $t('discovery.cta') }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'DiscoveryCard',
  setup() {
    const cards = [
      {
        title: 'Oferta specjalna Volvo XC60',
        description: 'Wyjątkowe warunki finansowania i bogate wyposażenie standardowe.',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/1920x1080-volvo-xc60-discovery?qlt=82&wid=800',
        link: '/oferty/xc60',
        large: true
      },
      {
        title: 'Jazda testowa',
        description: 'Umów się na bezpłatną jazdę testową i przekonaj się sam.',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/1920x1080-test-drive-discovery?qlt=82&wid=800',
        link: '/jazda-testowa',
        large: false
      },
      {
        title: 'Serwis Volvo',
        description: 'Autoryzowany serwis i oryginalne części.',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/1920x1080-service-discovery?qlt=82&wid=800',
        link: '/serwis',
        large: false
      }
    ]

    return {
      cards
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../styles/variables';

.discovery-cards {
  padding: $spacer-3xl 0;

  @media (max-width: $breakpoint-md) {
    padding: $spacer-2xl 0;
  }

  &__grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    grid-template-rows: repeat(2, 1fr);
    gap: $spacer-lg;
    min-height: 600px;

    @media (max-width: $breakpoint-lg) {
      grid-template-columns: 1fr 1fr;
      grid-template-rows: auto;
      min-height: auto;
    }

    @media (max-width: $breakpoint-md) {
      grid-template-columns: 1fr;
      gap: $spacer-md;
    }
  }

  &__item {
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    min-height: 280px;

    &--large {
      grid-row: span 2;

      @media (max-width: $breakpoint-lg) {
        grid-row: span 1;
        grid-column: span 2;
      }

      @media (max-width: $breakpoint-md) {
        grid-column: span 1;
      }

      .discovery-cards__title {
        font-size: $font-size-2xl;

        @media (max-width: $breakpoint-md) {
          font-size: $font-size-xl;
        }
      }
    }

    &:hover {
      .discovery-cards__image img {
        transform: scale(1.05);
      }
    }
  }

  &__image {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
  }

  &__overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
      to top,
      rgba(0, 0, 0, 0.8) 0%,
      rgba(0, 0, 0, 0.4) 50%,
      rgba(0, 0, 0, 0.1) 100%
    );
    z-index: 2;
  }

  &__content {
    position: relative;
    z-index: 3;
    padding: $spacer-xl;
    color: $white;

    @media (max-width: $breakpoint-md) {
      padding: $spacer-lg;
    }
  }

  &__category {
    display: inline-block;
    font-size: $font-size-xs;
    font-weight: $font-weight-medium;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: $spacer-xs $spacer-sm;
    background-color: $blue-light;
    color: $white;
    margin-bottom: $spacer-md;
  }

  &__title {
    font-size: $font-size-xl;
    font-weight: $font-weight-bold;
    color: $white;
    margin-bottom: $spacer-sm;
    line-height: $line-height-tight;

    @media (max-width: $breakpoint-md) {
      font-size: $font-size-lg;
    }
  }

  &__description {
    font-size: $font-size-base;
    line-height: $line-height-relaxed;
    margin-bottom: $spacer-lg;
    opacity: 0.9;

    @media (max-width: $breakpoint-md) {
      font-size: $font-size-sm;
    }
  }

  &__cta {
    &.btn--secondary {
      border-color: $white;
      color: $white;

      &:hover {
        background-color: $white;
        color: $black;
      }
    }
  }
}
</style>
