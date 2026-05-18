import axios from 'axios'

// Get WordPress data from global object or use defaults
const wpData = window.wpData || {
  restUrl: '/wp-json/',
  nonce: ''
}

// Create axios instance
const api = axios.create({
  baseURL: wpData.restUrl,
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': wpData.nonce
  }
})

/**
 * Get menu items by location
 * @param {string} location - Menu location (primary, footer, side-nav)
 * @returns {Promise<Array>}
 */
export const getMenuItems = async (location) => {
  try {
    const response = await api.get(`volvo-dealers/v1/menu/${location}`)
    return response.data
  } catch (error) {
    console.error(`Error fetching menu ${location}:`, error)
    return null
  }
}

/**
 * Get site options
 * @returns {Promise<Object>}
 */
export const getSiteOptions = async () => {
  try {
    const response = await api.get('volvo-dealers/v1/options')
    return response.data
  } catch (error) {
    console.error('Error fetching site options:', error)
    return null
  }
}

/**
 * Get car models
 * @returns {Promise<Array>}
 */
export const getCarModels = async () => {
  try {
    const response = await api.get('volvo-dealers/v1/car-models')
    return response.data
  } catch (error) {
    console.error('Error fetching car models:', error)
    return []
  }
}

/**
 * Get all pages
 * @returns {Promise<Array>}
 */
export const getPages = async () => {
  try {
    const response = await api.get('wp/v2/pages')
    return response.data
  } catch (error) {
    console.error('Error fetching pages:', error)
    return []
  }
}

/**
 * Get a single page by slug
 * @param {string} slug - Page slug
 * @returns {Promise<Object>}
 */
export const getPageBySlug = async (slug) => {
  try {
    const response = await api.get(`wp/v2/pages?slug=${slug}`)
    return response.data[0] || null
  } catch (error) {
    console.error(`Error fetching page ${slug}:`, error)
    return null
  }
}

/**
 * Get posts
 * @param {number} perPage - Number of posts per page
 * @returns {Promise<Array>}
 */
export const getPosts = async (perPage = 10) => {
  try {
    const response = await api.get(`wp/v2/posts?per_page=${perPage}`)
    return response.data
  } catch (error) {
    console.error('Error fetching posts:', error)
    return []
  }
}

/**
 * Get a single post by slug
 * @param {string} slug - Post slug
 * @returns {Promise<Object>}
 */
export const getPostBySlug = async (slug) => {
  try {
    const response = await api.get(`wp/v2/posts?slug=${slug}`)
    return response.data[0] || null
  } catch (error) {
    console.error(`Error fetching post ${slug}:`, error)
    return null
  }
}

/**
 * Get media by ID
 * @param {number} id - Media ID
 * @returns {Promise<Object>}
 */
export const getMedia = async (id) => {
  try {
    const response = await api.get(`wp/v2/media/${id}`)
    return response.data
  } catch (error) {
    console.error(`Error fetching media ${id}:`, error)
    return null
  }
}

/**
 * Submit contact form
 * @param {Object} formData - Form data
 * @returns {Promise<Object>}
 */
export const submitContactForm = async (formData) => {
  try {
    const response = await api.post('volvo-dealers/v1/contact', formData)
    return response.data
  } catch (error) {
    console.error('Error submitting contact form:', error)
    throw error
  }
}

/**
 * Get stock cars
 * @returns {Promise<Array>}
 */
export const getStockCars = async () => {
  try {
    const response = await api.get('wp/v2/stock-car')
    return response.data
  } catch (error) {
    console.error('Error fetching stock cars:', error)
    return []
  }
}

/**
 * Get a single stock car by slug
 * @param {string} slug - Stock car slug
 * @returns {Promise<Object>}
 */
export const getStockCarBySlug = async (slug) => {
  try {
    const response = await api.get(`wp/v2/stock-car?slug=${slug}`)
    return response.data[0] || null
  } catch (error) {
    console.error(`Error fetching stock car ${slug}:`, error)
    return null
  }
}

export default api
