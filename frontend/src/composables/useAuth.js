import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import api from '../services/api'

const user = ref(null)
const loading = ref(false)

export function useAuth() {
  const router = useRouter()

  async function login(email, password, remember = false) {
    await axios.get('/sanctum/csrf-cookie', { baseURL: '', withCredentials: true })
    const { data } = await api.post('/login', { email, password, remember })
    user.value = data.user
    return data
  }

  async function logout() {
    await api.post('/logout')
    user.value = null
    router.push('/login')
  }

  async function fetchUser() {
    if (user.value) return user.value
    loading.value = true
    try {
      const { data } = await api.get('/me')
      user.value = data.user
      return data.user
    } catch {
      user.value = null
      return null
    } finally {
      loading.value = false
    }
  }

  function isAdmin() {
    return user.value?.role === 'admin'
  }

  return { user, loading, login, logout, fetchUser, isAdmin }
}
