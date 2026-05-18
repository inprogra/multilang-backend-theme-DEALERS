import { createI18n } from 'vue-i18n'
import pl from './locales/pl.json'
import en from './locales/en.json'
import cs from './locales/cs.json'

// Get locale from WordPress (passed via wp_localize_script)
const getLocale = () => {
  if (typeof window !== 'undefined' && window.volvoThemeData) {
    return window.volvoThemeData.locale || 'pl'
  }
  return 'pl'
}

const i18n = createI18n({
  legacy: false,
  locale: getLocale(),
  fallbackLocale: 'pl',
  messages: {
    pl,
    en,
    cs
  }
})

export default i18n
