import axios from 'axios'

// Create axios instance with default config
const axiosInstance = axios.create({
  baseURL: '/',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true, // Enable sending cookies with requests
})

// Request interceptor to automatically add CSRF token
axiosInstance.interceptors.request.use((config) => {
  // Laravel automatically reads XSRF-TOKEN from cookies when withCredentials is true
  // No need to manually set X-CSRF-TOKEN header
  return config
}, (error) => {
  return Promise.reject(error)
})

// Response interceptor for error handling
axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle common errors here if needed
    return Promise.reject(error)
  }
)

export default axiosInstance
