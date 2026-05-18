<template>
  <div class="home">
    <HeroSlider />
    <ShoppingBox />
    <DiscoveryCard />
    <CarModels />
  </div>
</template>

<script>
import HeroSlider from '../components/HeroSlider.vue'
import ShoppingBox from '../components/ShoppingBox.vue'
import DiscoveryCard from '../components/DiscoveryCard.vue'
import CarModels from '../components/CarModels.vue'

export default {
  name: 'Home',
  components: {
    HeroSlider,
    ShoppingBox,
    DiscoveryCard,
    CarModels
  }
}
</script>

<style lang="scss" scoped>
.home {
  padding-top: 80px; // Account for fixed header

  @media (max-width: 992px) {
    padding-top: 60px;
  }
}
</style>
