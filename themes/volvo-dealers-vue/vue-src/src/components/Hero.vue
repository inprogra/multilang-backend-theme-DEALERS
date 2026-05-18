<template>
  <section class="hero">
    <div class="hero-content">
      <h1>{{ title }}</h1>
      <p>{{ subtitle }}</p>
      <router-link to="/modele" class="cta-button">
        Zobacz modele
      </router-link>
    </div>
  </section>
</template>

<script>
import { ref, onMounted } from 'vue'
import wpApi from '../services/wp-api'

export default {
  name: 'Hero',
  setup() {
    const title = ref('Volvo Car Warszawa')
    const subtitle = ref('Odkryj luksusowe samochody Volvo. Bezpieczeństwo, design i innowacje w jednym.')

    onMounted(async () => {
      try {
        const response = await wpApi.getOptions()
        const options = response.data
        if (options.siteName) {
          title.value = options.siteName
        }
        if (options.siteDescription) {
          subtitle.value = options.siteDescription
        }
      } catch (error) {
        console.log('Using default hero content')
      }
    })

    return {
      title,
      subtitle
    }
  }
}
</script>
