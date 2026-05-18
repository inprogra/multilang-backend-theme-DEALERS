<template>
  <div class="page">
    <div v-if="loading" class="loading">Ładowanie...</div>
    <div v-else-if="page" class="page-content">
      <h1>{{ page.title.rendered }}</h1>
      <div v-html="page.content.rendered"></div>
    </div>
    <div v-else class="page-content">
      <h1>Strona nie znaleziona</h1>
      <p>Przepraszamy, ale strona której szukasz nie istnieje.</p>
      <router-link to="/">Wróć do strony głównej</router-link>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import wpApi from '../services/wp-api'

export default {
  name: 'Page',
  props: ['slug'],
  setup(props) {
    const route = useRoute()
    const page = ref(null)
    const loading = ref(true)

    const fetchPage = async () => {
      loading.value = true
      try {
        const slug = props.slug || route.params.slug
        if (!slug || slug === '') {
          // Redirect to home if no slug
          return
        }
        const response = await wpApi.getPage(slug)
        if (response.data && response.data.length > 0) {
          page.value = response.data[0]
        } else {
          page.value = null
        }
      } catch (error) {
        console.error('Error fetching page:', error)
        page.value = null
      } finally {
        loading.value = false
      }
    }

    onMounted(fetchPage)
    watch(() => route.params.slug, fetchPage)

    return {
      page,
      loading
    }
  }
}
</script>
