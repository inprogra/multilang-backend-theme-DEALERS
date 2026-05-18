<template>
  <section class="hero-slider">
    <swiper
      :modules="modules"
      :slides-per-view="1"
      :loop="true"
      :autoplay="{
        delay: 5000,
        disableOnInteraction: false
      }"
      :pagination="{
        clickable: true
      }"
      :navigation="true"
      class="hero-slider__swiper"
    >
      <swiper-slide v-for="(slide, index) in slides" :key="index" class="hero-slider__slide">
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
                <router-link :to="slide.link" class="btn btn--primary hero-slider__cta">
                  {{ $t('hero.learnMore') }}
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </swiper-slide>
    </swiper>
  </section>
</template>

<script>
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Pagination, Navigation } from 'swiper/modules'

// Import Swiper styles
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation'

export default {
  name: 'HeroSlider',
  components: {
    Swiper,
    SwiperSlide
  },
  setup() {
    const modules = [Autoplay, Pagination, Navigation]

    // Sample slides data - in production, this would come from WordPress API
    const slides = [
      {
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/2560x1440-volvo-xc90-recharge-hero?qlt=82&wid=1920',
        subtitle: 'Nowe Volvo XC90',
        title: 'Przestrzeń dla Twoich marzeń',
        description: 'Luksusowy SUV, który zmienia reguły gry.',
        price: '3 490 PLN',
        link: '/modele/xc90'
      },
      {
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/2560x1440-volvo-ex30-hero?qlt=82&wid=1920',
        subtitle: 'Elektryzujące',
        title: 'Volvo EX30',
        description: 'Najmniejsze Volvo, jakie kiedykolwiek stworzyliśmy.',
        price: '1 890 PLN',
        link: '/modele/ex30'
      },
      {
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/2560x1440-volvo-em90-hero?qlt=82&wid=1920',
        subtitle: 'Luksus w czystej formie',
        title: 'Volvo EM90',
        description: 'Pierwszy luksusowy elektryczny van Volvo.',
        price: '5 490 PLN',
        link: '/modele/em90'
      }
    ]

    return {
      modules,
      slides
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../styles/variables';

.hero-slider {
  position: relative;
  height: 100vh;
  min-height: 600px;
  max-height: 900px;
  overflow: hidden;

  @media (max-width: $breakpoint-md) {
    min-height: 500px;
    height: 70vh;
  }

  &__swiper {
    height: 100%;
  }

  &__slide {
    position: relative;
    height: 100%;
    display: flex;
    align-items: center;
  }

  &__background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
  }

  &__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }

  &__overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
      to right,
      rgba(0, 0, 0, 0.6) 0%,
      rgba(0, 0, 0, 0.3) 50%,
      rgba(0, 0, 0, 0.1) 100%
    );
    z-index: 2;
  }

  &__content {
    position: relative;
    z-index: 3;
    width: 100%;
    padding: $spacer-xl 0;
  }

  &__container {
    max-width: $container-max-width;
    margin: 0 auto;
    padding: 0 $container-padding-x;

    @media (max-width: $breakpoint-md) {
      padding: 0 $container-padding-x-sm;
    }
  }

  &__text {
    max-width: 600px;
    color: $white;
  }

  &__subtitle {
    display: block;
    font-size: $font-size-sm;
    font-weight: $font-weight-medium;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: $spacer-sm;
    opacity: 0.9;
  }

  &__title {
    font-size: $font-size-4xl;
    font-weight: $font-weight-bold;
    line-height: $line-height-tight;
    color: $white;
    margin-bottom: $spacer-md;

    @media (max-width: $breakpoint-md) {
      font-size: $font-size-3xl;
    }

    @media (max-width: $breakpoint-sm) {
      font-size: $font-size-2xl;
    }
  }

  &__description {
    font-size: $font-size-lg;
    line-height: $line-height-relaxed;
    margin-bottom: $spacer-lg;
    opacity: 0.9;

    @media (max-width: $breakpoint-md) {
      font-size: $font-size-base;
    }
  }

  &__price {
    margin-bottom: $spacer-xl;

    &-label {
      display: block;
      font-size: $font-size-sm;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: $spacer-xs;
      opacity: 0.8;
    }

    &-value {
      display: block;
      font-size: $font-size-3xl;
      font-weight: $font-weight-bold;
      line-height: 1;

      @media (max-width: $breakpoint-md) {
        font-size: $font-size-2xl;
      }
    }

    &-period {
      display: block;
      font-size: $font-size-sm;
      margin-top: $spacer-xs;
      opacity: 0.8;
    }
  }

  &__actions {
    display: flex;
    gap: $spacer-md;
    flex-wrap: wrap;
  }

  &__cta {
    min-width: 180px;
  }

  // Swiper custom styles
  :deep(.swiper-pagination) {
    bottom: $spacer-xl;
    left: $container-padding-x;
    width: auto;
    text-align: left;

    @media (max-width: $breakpoint-md) {
      left: $container-padding-x-sm;
      bottom: $spacer-lg;
    }
  }

  :deep(.swiper-pagination-bullet) {
    width: 12px;
    height: 12px;
    background-color: rgba(255, 255, 255, 0.5);
    opacity: 1;
    margin: 0 6px;

    &-active {
      background-color: $white;
    }
  }

  :deep(.swiper-button-prev),
  :deep(.swiper-button-next) {
    width: 50px;
    height: 50px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: $white;
    transition: all 0.2s ease;

    @media (max-width: $breakpoint-md) {
      display: none;
    }

    &:hover {
      background-color: rgba(255, 255, 255, 0.3);
    }

    &::after {
      font-size: 18px;
      font-weight: bold;
    }
  }

  :deep(.swiper-button-prev) {
    left: $container-padding-x;
  }

  :deep(.swiper-button-next) {
    right: $container-padding-x;
  }
}
</style>
