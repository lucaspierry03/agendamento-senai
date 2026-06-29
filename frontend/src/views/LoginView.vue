<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import TextInput from '../components/TextInput.vue'
import PrimaryButton from '../components/PrimaryButton.vue'

const router = useRouter()
const { login } = useAuth()

const form = ref({ email: '', password: '', remember: false })
const errors = ref({})
const loading = ref(false)
const errorMessage = ref('')

async function handleSubmit() {
  errors.value = {}
  errorMessage.value = ''
  loading.value = true

  try {
    await login(form.value.email, form.value.password, form.value.remember)
    router.push('/appointments')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else if (e.response?.status === 401) {
      errorMessage.value = e.response.data.message
    } else {
      errorMessage.value = 'Erro interno do servidor.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-white to-blue-50 px-4">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-600 rounded-2xl shadow-lg shadow-blue-200 mb-4">
          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Agendamento</h1>
        <p class="text-sm text-gray-400 mt-1">Sistema de Gerenciamento de Agenda</p>
      </div>

      <form @submit.prevent="handleSubmit" class="bg-white p-8 rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 space-y-5">
        <div v-if="errorMessage" class="bg-red-50 border border-red-100 text-red-600 text-sm p-3 rounded-xl flex items-center gap-2">
          <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          {{ errorMessage }}
        </div>

        <TextInput v-model="form.email" label="E-mail" required placeholder="seu@email.com" :error="errors.email?.[0]" />
        <TextInput v-model="form.password" label="Senha" type="password" required placeholder="••••••••" :error="errors.password?.[0]" />

        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
          <span class="text-sm text-gray-600">Lembrar de mim</span>
        </label>

        <PrimaryButton type="submit" :loading="loading" :disabled="loading" class="w-full">
          Entrar
        </PrimaryButton>


      </form>
    </div>
  </div>
</template>
