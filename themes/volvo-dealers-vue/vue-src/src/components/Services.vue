<template>
  <section class="m-services section">
    <div class="container">
      <h2 class="m-services__title text-center mb-40">{{ title }}</h2>
      
      <div v-if="loading" class="loading">
        <div class="loading__spinner"></div>
      </div>

      <div v-else class="m-services__grid">
        <div 
          v-for="service in services" 
          :key="service.id"
          class="service-card"
        >
          <a :href="service.url" class="service-card__link">
            <div class="service-card__icon" v-if="service.icon">
              <img :src="service.icon" :alt="service.title" />
            </div>
            <h3 class="service-card__title">{{ service.title }}</h3>
            <p class="service-card__description">{{ service.description }}</p>
            <span class="service-card__cta">
              {{ service.cta || 'Dowiedz się więcej' }}
              <svg viewBox="0 0 24 24" width="16" height="16">
                <path fill="currentColor" d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/>
              </svg>
            </span>
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
  name: 'Services',
  props: {
    title: {
      type: String,
      default: 'Nasze usługi'
    }
  },
  setup() {
    const services = ref([])
    const loading = ref(true)

    const loadServices = async () => {
      try {
        // Try to fetch from WordPress API
        // const response = await fetch('/wp-json/volvo-dealers/v1/services')
        // const data = await response.json()
        // if (data && data.length > 0) {
        //   services.value = data
        // } else {
        //   throw new Error('No services data')
        // }
        
        // For now, use fallback data
        throw new Error('Using fallback data')
      } catch (error) {
        console.log('Using fallback services data')
        // Fallback services data
        services.value = [
          {
            id: 1,
            title: 'Test drive',
            description: 'Umów się na jazdę próbną i przekonaj się o możliwościach Volvo.',
            icon: '',
            url: '/jazda-probna/',
            cta: 'Umów jazdę'
          },
          {
            id: 2,
            title: 'Serwis',
            description: 'Profesjonalna obsługa serwisowa i oryginalne części Volvo.',
            icon: '',
            url: '/serwis/',
            cta: 'Umów wizytę'
          },
          {
            id: 3,
            title: 'Finansowanie',
            description: 'Elastyczne opcje finansowania dopasowane do Twoich potrzeb.',
            icon: '',
            url: '/finansowanie/',
            cta: 'Dowiedz się więcej'
          },
          {
            id: 4,
            title: 'Wycena pojazdu',
            description: 'Sprawdź wartość swojego obecnego samochodu.',
            icon: '',
            url: '/wycena/',
            cta: 'Wycenij'
          }
        ]
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      loadServices()
    })

    return {
      services,
      loading
    }
  }
}
</script>

<style scoped lang="scss">
.m-services {
  background-color: #ffffff;

  &__title {
    font-family: 'Volvo Broad Pro', Arial, sans-serif;
    font-size: 36px;
    color: #000000;

    @media (max-width: 997px) {
      font-size: 28px;
    }
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;

    @media (max-width: 997px) {
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }

    @media (max-width: 600px) {
      grid-template-columns: 1fr;
    }
  }
}

.service-card {
  background-color: #f5f5f5;
  border: 1px solid #d5d5d5;
  padding: 30px;
  transition: box-shadow 0.3s ease;
  height: 100%;

  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  &__link {
    display: flex;
    flex-direction: column;
    height: 100%;
    text-decoration: none;
    color: inherit;

    &:hover {
      text-decoration: none;
    }
  }

  &__icon {
    width: 60px;
    height: 60px;
    margin-bottom: 20px;

    img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
  }

  &__title {
    font-family: 'Volvo Broad Pro', Arial, sans-serif;
    font-size: 20px;
    color: #000000;
    margin-bottom: 15px;
  }

  &__description {
    font-size: 14px;
    color: #666666;
    line-height: 1.5;
    margin-bottom: 20px;
    flex-grow: 1;
  }

  &__cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    font-weight: 500;
    color: #1c3f94;
    transition: gap 0.3s ease;

    &:hover {
      gap: 10px;
    }

    svg {
      flex-shrink: 0;
    }
  }
}

.mb-40 {
  margin-bottom: 40px;
}

.text-center {
  text-align: center;
}

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;

  &__spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #d5d5d5;
    border-top-color: #1c3f94;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
