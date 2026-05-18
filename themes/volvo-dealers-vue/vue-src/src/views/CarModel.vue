<template>
  <div class="car-model-page">
    <div v-if="loading" class="loading">Ładowanie...</div>
    
    <div v-else-if="car" class="page-content">
      <h1>{{ car.title }}</h1>
      
      <img 
        :src="car.featuredImage || 'https://via.placeholder.com/1200x600/1c3f94/ffffff?text=Volvo'" 
        :alt="car.title"
        class="car-hero-image"
        style="width: 100%; max-height: 500px; object-fit: cover; border-radius: 10px; margin-bottom: 30px;"
      />
      
      <div class="car-details" style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-bottom: 40px;">
        <div class="car-description">
          <h2>Opis</h2>
          <div v-html="car.content"></div>
          <p v-if="!car.content">{{ car.excerpt }}</p>
        </div>
        
        <div class="car-specs-panel" style="background: #f5f5f5; padding: 30px; border-radius: 10px; height: fit-content;">
          <h3 style="margin-bottom: 20px; color: #1c3f94;">Specyfikacja</h3>
          
          <div v-if="car.price" class="spec-item" style="margin-bottom: 15px;">
            <strong style="display: block; color: #1c3f94; font-size: 1.2rem;">
              Cena: od {{ car.price }} zł
            </strong>
          </div>
          
          <div v-if="car.engine" class="spec-item" style="margin-bottom: 10px;">
            <strong>Silnik:</strong> {{ car.engine }}
          </div>
          
          <div v-if="car.power" class="spec-item" style="margin-bottom: 10px;">
            <strong>Moc:</strong> {{ car.power }}
          </div>
          
          <div v-if="car.fuel" class="spec-item" style="margin-bottom: 10px;">
            <strong>Paliwo:</strong> {{ car.fuel }}
          </div>
          
          <button style="width: 100%; padding: 15px; background: #1c3f94; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; margin-top: 20px;">
            Zapytaj o ofertę
          </button>
          
          <button style="width: 100%; padding: 15px; background: white; color: #1c3f94; border: 2px solid #1c3f94; border-radius: 5px; font-size: 1rem; cursor: pointer; margin-top: 10px;">
            Umów jazdę testową
          </button>
        </div>
      </div>
      
      <div class="car-features" v-if="car.content && car.content.includes('<h')">
        <h2>Wyposażenie i funkcje</h2>
        <div v-html="car.content"></div>
      </div>
    </div>
    
    <div v-else class="page-content">
      <h1>Model nie znaleziony</h1>
      <p>Przepraszamy, ale model którego szukasz nie istnieje w naszej ofercie.</p>
      <router-link to="/" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #1c3f94; color: white; text-decoration: none; border-radius: 5px;">
        Wróć do strony głównej
      </router-link>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import wpApi from '../services/wp-api'

export default {
  name: 'CarModel',
  props: ['slug'],
  setup(props) {
    const route = useRoute()
    const car = ref(null)
    const loading = ref(true)

    const fetchCarModel = async () => {
      loading.value = true
      try {
        const slug = props.slug || route.params.slug
        const response = await wpApi.getCarModels()
        const models = response.data
        car.value = models.find(model => model.slug === slug)
      } catch (error) {
        console.error('Error fetching car model:', error)
        car.value = null
      } finally {
        loading.value = false
      }
    }

    onMounted(fetchCarModel)

    return {
      car,
      loading
    }
  }
}
</script>
