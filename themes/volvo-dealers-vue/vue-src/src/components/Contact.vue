<template>
  <section class="m-contact section">
    <div class="container">
      <div class="m-contact__grid">
        <!-- Contact Info -->
        <div class="m-contact__info">
          <h2 class="m-contact__title">{{ title }}</h2>
          <p v-if="description" class="m-contact__description">{{ description }}</p>
          
          <div v-if="contactInfo" class="contact-details">
            <div v-if="contactInfo.phone" class="contact-details__item">
              <h4>Telefon</h4>
              <a :href="`tel:${contactInfo.phone}`">{{ contactInfo.phone }}</a>
            </div>
            <div v-if="contactInfo.email" class="contact-details__item">
              <h4>Email</h4>
              <a :href="`mailto:${contactInfo.email}`">{{ contactInfo.email }}</a>
            </div>
            <div v-if="contactInfo.address" class="contact-details__item">
              <h4>Adres</h4>
              <p>{{ contactInfo.address }}</p>
            </div>
            <div v-if="contactInfo.hours" class="contact-details__item">
              <h4>Godziny otwarcia</h4>
              <p>{{ contactInfo.hours }}</p>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="m-contact__form">
          <form @submit.prevent="submitForm" class="contact-form">
            <div class="form-group">
              <label for="name">Imię i nazwisko *</label>
              <input 
                type="text" 
                id="name" 
                v-model="form.name" 
                required
                class="form-control"
              />
            </div>
            
            <div class="form-group">
              <label for="email">Email *</label>
              <input 
                type="email" 
                id="email" 
                v-model="form.email" 
                required
                class="form-control"
              />
            </div>
            
            <div class="form-group">
              <label for="phone">Telefon</label>
              <input 
                type="tel" 
                id="phone" 
                v-model="form.phone"
                class="form-control"
              />
            </div>
            
            <div class="form-group">
              <label for="subject">Temat *</label>
              <select id="subject" v-model="form.subject" required class="form-control">
                <option value="">Wybierz temat</option>
                <option value="test-drive">Jazda próbna</option>
                <option value="service">Serwis</option>
                <option value="offer">Oferta</option>
                <option value="financing">Finansowanie</option>
                <option value="other">Inne</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="message">Wiadomość *</label>
              <textarea 
                id="message" 
                v-model="form.message" 
                rows="5" 
                required
                class="form-control"
              ></textarea>
            </div>
            
            <div class="form-group form-group--checkbox">
              <label class="checkbox-label">
                <input 
                  type="checkbox" 
                  v-model="form.consent" 
                  required
                />
                <span class="checkbox-text">
                  Wyrażam zgodę na przetwarzanie moich danych osobowych w celu udzielenia odpowiedzi na moje zapytanie. *
                </span>
              </label>
            </div>
            
            <button 
              type="submit" 
              class="volvo-button"
              :disabled="submitting"
            >
              {{ submitting ? 'Wysyłanie...' : 'Wyślij wiadomość' }}
            </button>
            
            <div v-if="successMessage" class="alert alert-success">
              {{ successMessage }}
            </div>
            
            <div v-if="errorMessage" class="alert alert-error">
              {{ errorMessage }}
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
  name: 'Contact',
  props: {
    title: {
      type: String,
      default: 'Skontaktuj się z nami'
    },
    description: {
      type: String,
      default: 'Masz pytania? Skontaktuj się z nami, a nasz zespół chętnie Ci pomoże.'
    }
  },
  setup() {
    const contactInfo = ref({
      phone: '+48 22 123 45 67',
      email: 'kontakt@volvocarwarszawa.pl',
      address: 'ul. Przykładowa 123, 00-001 Warszawa',
      hours: 'Pon-Pt: 9:00 - 18:00, Sob: 10:00 - 14:00'
    })

    const form = ref({
      name: '',
      email: '',
      phone: '',
      subject: '',
      message: '',
      consent: false
    })

    const submitting = ref(false)
    const successMessage = ref('')
    const errorMessage = ref('')

    const submitForm = async () => {
      submitting.value = true
      successMessage.value = ''
      errorMessage.value = ''

      try {
        // Here you would send the form data to WordPress
        // const response = await fetch('/wp-json/volvo-dealers/v1/contact', {
        //   method: 'POST',
        //   headers: {
        //     'Content-Type': 'application/json',
        //     'X-WP-Nonce': window.wpData?.nonce
        //   },
        //   body: JSON.stringify(form.value)
        // })
        
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500))
        
        // Reset form
        form.value = {
          name: '',
          email: '',
          phone: '',
          subject: '',
          message: '',
          consent: false
        }
        
        successMessage.value = 'Dziękujemy za wiadomość! Skontaktujemy się z Tobą wkrótce.'
      } catch (error) {
        console.error('Error submitting form:', error)
        errorMessage.value = 'Wystąpił błąd podczas wysyłania wiadomości. Spróbuj ponownie później.'
      } finally {
        submitting.value = false
      }
    }

    const loadContactInfo = async () => {
      try {
        // Try to fetch from WordPress API
        // const response = await fetch('/wp-json/volvo-dealers/v1/options')
        // const data = await response.json()
        // if (data) {
        //   contactInfo.value = {
        //     phone: data.phone || contactInfo.value.phone,
        //     email: data.email || contactInfo.value.email,
        //     address: data.address || contactInfo.value.address,
        //     hours: data.hours || contactInfo.value.hours
        //   }
        // }
      } catch (error) {
        console.log('Using fallback contact info')
      }
    }

    onMounted(() => {
      loadContactInfo()
    })

    return {
      contactInfo,
      form,
      submitting,
      successMessage,
      errorMessage,
      submitForm
    }
  }
}
</script>

<style scoped lang="scss">
.m-contact {
  background-color: #f5f5f5;

  &__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: start;

    @media (max-width: 997px) {
      grid-template-columns: 1fr;
      gap: 40px;
    }
  }

  &__info {
    @media (max-width: 997px) {
      text-align: center;
    }
  }

  &__title {
    font-family: 'Volvo Broad Pro', Arial, sans-serif;
    font-size: 36px;
    color: #000000;
    margin-bottom: 20px;

    @media (max-width: 997px) {
      font-size: 28px;
    }
  }

  &__description {
    font-size: 16px;
    color: #666666;
    line-height: 1.6;
    margin-bottom: 30px;
  }
}

.contact-details {
  &__item {
    margin-bottom: 25px;

    &:last-child {
      margin-bottom: 0;
    }

    h4 {
      font-family: 'Volvo Novum', Arial, sans-serif;
      font-size: 14px;
      font-weight: 600;
      color: #000000;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    a {
      font-size: 18px;
      color: #1c3f94;
      text-decoration: none;

      &:hover {
        text-decoration: underline;
      }
    }

    p {
      font-size: 16px;
      color: #333333;
      margin: 0;
    }
  }
}

.contact-form {
  background-color: #ffffff;
  padding: 40px;
  border: 1px solid #d5d5d5;

  @media (max-width: 997px) {
    padding: 30px 20px;
  }
}

.form-group {
  margin-bottom: 20px;

  &--checkbox {
    margin-top: 30px;
  }

  label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #333333;
    margin-bottom: 8px;

    &.checkbox-label {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-weight: normal;
      cursor: pointer;

      input[type="checkbox"] {
        margin-top: 3px;
      }

      .checkbox-text {
        font-size: 13px;
        color: #666666;
        line-height: 1.4;
      }
    }
  }
}

.form-control {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #d5d5d5;
  font-size: 16px;
  font-family: 'Volvo Novum', Arial, sans-serif;
  transition: border-color 0.3s ease;

  &:focus {
    outline: none;
    border-color: #1c3f94;
  }

  select& {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 40px;
  }
}

textarea.form-control {
  resize: vertical;
  min-height: 120px;
}

.volvo-button {
  display: inline-block;
  padding: 15px 30px;
  background-color: #1c3f94;
  color: #ffffff;
  text-decoration: none;
  font-family: 'Volvo Novum', Arial, sans-serif;
  font-size: 16px;
  font-weight: 500;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease;
  width: 100%;

  &:hover:not(:disabled) {
    background-color: #0f265c;
  }

  &:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }
}

.alert {
  padding: 15px;
  margin-top: 20px;
  border-radius: 4px;
  font-size: 14px;

  &-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  &-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
}
</style>
