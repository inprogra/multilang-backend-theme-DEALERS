<template>
  <section class="car-models">
    <div class="container">
      <div class="car-models__header">
        <h2 class="car-models__title">
          {{ $t('carModels.title') }}
          <span class="car-models__title-bold">{{ $t('carModels.titleBold') }}</span>
        </h2>
        
        <div class="car-models__tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            class="car-models__tab"
            :class="{ 'car-models__tab--active': activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>

      <div class="car-models__carousel">
        <swiper
          :modules="modules"
          :slides-per-view="slidesPerView"
          :space-between="24"
          :loop="true"
          :navigation="true"
          class="car-models__swiper"
        >
          <swiper-slide 
            v-for="car in filteredCars" 
            :key="car.id"
            class="car-models__slide"
          >
            <div class="car-models__card">
              <div class="car-models__image">
                <img :src="car.image" :alt="car.name" />
              </div>
              <div class="car-models__info">
                <h3 class="car-models__name">{{ car.name }}</h3>
                <p class="car-models__type">{{ car.type }}</p>
                <div class="car-models__price">
                  <span class="car-models__price-label">{{ $t('carModels.price.from') }}</span>
                  <span class="car-models__price-value">{{ car.price }} {{ $t('carModels.price.currency') }}</span>
                </div>
                <router-link :to="car.link" class="car-models__cta">
                  {{ $t('carModels.cta') }}
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                  </svg>
                </router-link>
              </div>
            </div>
          </swiper-slide>
        </swiper>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation } from 'swiper/modules'

import 'swiper/css'
import 'swiper/css/navigation'

export default {
  name: 'CarModels',
  components: {
    Swiper,
    SwiperSlide
  },
  setup() {
    const { t } = useI18n()
    const modules = [Navigation]
    const activeTab = ref('all')

    const tabs = [
      { id: 'all', label: 'Wszystkie' },
      { id: 'suv', label: t('carModels.category.suv') },
      { id: 'wagon', label: t('carModels.category.wagon') },
      { id: 'electric', label: t('carModels.category.electric') }
    ]

    const cars = [
      {
        id: 1,
        name: 'XC90',
        type: 'SUV',
        category: 'suv',
        price: '351 900',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/xc90-recharge-side?qlt=82&wid=600',
        link: '/modele/xc90'
      },
      {
        id: 2,
        name: 'XC60',
        type: 'SUV',
        category: 'suv',
        price: '289 900',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/xc60-recharge-side?qlt=82&wid=600',
        link: '/modele/xc60'
      },
      {
        id: 3,
        name: 'XC40',
        type: 'SUV',
        category: 'suv',
        price: '199 900',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/xc40-recharge-side?qlt=82&wid=600',
        link: '/modele/xc40'
      },
      {
        id: 4,
        name: 'V90',
        type: 'Kombi',
        category: 'wagon',
        price: '319 900',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/v90-recharge-side?qlt=82&wid=600',
        link: '/modele/v90'
      },
      {
        id: 5,
        name: 'V60',
        type: 'Kombi',
        category: 'wagon',
        price: '249 900',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/v60-recharge-side?qlt=82&wid=600',
        link: '/modele/v60'
      },
      {
        id: 6,
        name: 'EX30',
        type: 'Elektryczny SUV',
        category: 'electric',
        price: '179 900',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/ex30-side?qlt=82&wid=600',
        link: '/modele/ex30'
      },
      {
        id: 7,
        name: 'EX90',
        type: 'Elektryczny SUV',
        category: 'electric',
        price: '449 900',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/ex90-side?qlt=82&wid=600',
        link: '/modele/ex90'
      },
      {
        id: 8,
        name: 'EM90',
        type: 'Elektryczny van',
        category: 'electric',
        price: '599 900',
        image: 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/em90-side?qlt=82&wid=600',
        link: '/modele/em90'
      }
    ]

    const filteredCars = computed(() => {
      if (activeTab.value === 'all') {
        return cars
      }
      return cars.filter(car => car.category === activeTab.value)
    })

    const slidesPerView = computed(() => {
      if (window.innerWidth >= 1200) return 4
      if (window.innerWidth >= 992) return 3
      if (window.innerWidth >= 576) return 2
      return 1
    })

    return {
      modules,
      activeTab,
      tabs,
      filteredCars,
      slidesPerView
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../styles/variables';

.car-models {
  padding: $spacer-3xl 0;
  background-color: $white;

  @media (max-width: $breakpoint-md) {
    padding: $spacer-2xl 0;
  }

  &__header {
    display: flex;
    flex-direction: column;
    gap: $spacer-lg;
    margin-bottom: $spacer-2xl;

    @media (min-width: $breakpoint-lg) {
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
    }
  }

  &__title {
    font-size: $font-size-3xl;
    font-weight: $font-weight-normal;
    color: $primary;
    margin: 0;

    @media (max-width: $breakpoint-md) {
      font-size: $font-size-2xl;
    }

    &-bold {
      font-weight: $font-weight-bold;
    }
  }

  &__tabs {
    display: flex;
    gap: $spacer-sm;
    flex-wrap: wrap;
  }

  &__tab {
    padding: $spacer-sm $spacer-md;
    font-size: $font-size-sm;
    font-weight: $font-weight-medium;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: none;
    border: 1px solid $grey-300;
    color: $grey-400;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
      border-color: $blue-light;
      color: $blue-light;
    }

    &--active {
      background-color: $blue-light;
      border-color: $blue-light;
      color: $white;

      &:hover {
        background-color: $hover;
        border-color: $hover;
        color: $white;
      }
    }
  }

  &__carousel {
    position: relative;
  }

  &__swiper {
    padding-bottom: $spacer-xl;
  }

  &__slide {
    height: auto;
  }

  &__card {
    background-color: $grey-100;
    border: 1px solid $grey-200;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;

    &:hover {
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
      transform: translateY(-4px);

      .car-models__image img {
        transform: scale(1.05);
      }
    }
  }

  &__image {
    position: relative;
    padding-top: 60%;
    overflow: hidden;
    background-color: $white;

    img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: contain;
      transition: transform 0.5s ease;
    }
  }

  &__info {
    padding: $spacer-lg;
    flex: 1;
    display: flex;
    flex-direction: column;

    @media (max-width: $breakpoint-md) {
      padding: $spacer-md;
    }
  }

  &__name {
    font-size: $font-size-xl;
    font-weight: $font-weight-bold;
    color: $primary;
    margin-bottom: $spacer-xs;
    line-height: $line-height-tight;
  }

  &__type {
    font-size: $font-size-sm;
    color: $grey-400;
    margin-bottom: $spacer-md;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  &__price {
    margin-bottom: $spacer-lg;
    margin-top: auto;

    &-label {
      display: block;
      font-size: $font-size-xs;
      color: $grey-400;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: $spacer-xs;
    }

    &-value {
      display: block;
      font-size: $font-size-lg;
      font-weight: $font-weight-semibold;
      color: $primary;
    }
  }

  &__cta {
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

  // Swiper custom styles
  :deep(.swiper-button-prev),
  :deep(.swiper-button-next) {
    width: 50px;
    height: 50px;
    background-color: $white;
    border: 1px solid $grey-300;
    border-radius: 50%;
    color: $primary;
    transition: all 0.2s ease;

    &:hover {
      background-color: $blue-light;
      border-color: $blue-light;
      color: $white;
    }

    &::after {
      font-size: 18px;
      font-weight: bold;
    }
  }

  :deep(.swiper-button-prev) {
    left: -25px;

    @media (max-width: $breakpoint-lg) {
      left: 10px;
    }
  }

  :deep(.swiper-button-next) {
    right: -25px;

    @media (max-width: $breakpoint-lg) {
      right: 10px;
    }
  }
}
</style>
