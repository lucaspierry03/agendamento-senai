<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import Modal from '../components/Modal.vue'
import Alert from '../components/Alert.vue'
import PrimaryButton from '../components/PrimaryButton.vue'
import SecondaryButton from '../components/SecondaryButton.vue'
import PageHeader from '../components/PageHeader.vue'

const attendants = ref([])
const loading = ref(true)
const scheduling = ref(null)
const submitting = ref(false)
const alert = ref({ type: '', message: '' })

const days = [
  { value: 0, label: 'Domingo' },
  { value: 1, label: 'Segunda' },
  { value: 2, label: 'Terça' },
  { value: 3, label: 'Quarta' },
  { value: 4, label: 'Quinta' },
  { value: 5, label: 'Sexta' },
  { value: 6, label: 'Sábado' },
]

const scheduleForm = ref([])

onMounted(fetchAttendants)

async function fetchAttendants() {
  loading.value = true
  const { data } = await api.get('/users')
  attendants.value = data.filter(u => u.role === 'attendant')
  loading.value = false
}

async function openSchedule(attendant) {
  scheduling.value = attendant

  // Buscar disponibilidades existentes deste atendente
  const { data } = await api.get('/availabilities', { params: { user_id: attendant.id } })

  // Montar formulário com os 7 dias
  scheduleForm.value = days.map(d => {
    const existing = data.find(a => a.day_of_week === d.value)
    return {
      day_of_week: d.value,
      label: d.label,
      enabled: !!existing,
      start_time: existing?.start_time || '08:00',
      end_time: existing?.end_time || '17:30',
      is_active: existing?.is_active ?? true,
      id: existing?.id || null,
    }
  })
}

async function submitSchedule() {
  submitting.value = true
  try {
    // Deleta os que foram desmarcados (se existiam)
    const toDelete = scheduleForm.value.filter(s => !s.enabled && s.id)
    for (const s of toDelete) {
      await api.delete(`/availabilities/${s.id}`)
    }

    // Cria ou atualiza os habilitados
    const toSave = scheduleForm.value.filter(s => s.enabled)
    for (const s of toSave) {
      const payload = {
        user_id: scheduling.value.id,
        day_of_week: s.day_of_week,
        start_time: s.start_time,
        end_time: s.end_time,
        is_active: s.is_active,
      }
      if (s.id) {
        await api.put(`/availabilities/${s.id}`, payload)
      } else {
        await api.post('/availabilities', payload)
      }
    }

    alert.value = { type: 'success', message: `Horários de ${scheduling.value.name} atualizados com sucesso.` }
    scheduling.value = null
    await fetchAttendants()
  } catch (e) {
    alert.value = { type: 'error', message: 'Erro ao salvar horários.' }
  } finally {
    submitting.value = false
  }
}

function getScheduleSummary(attendant) {
  // Pega as disponibilidades desse atendente (precisamos carregar)
  return ''
}
</script>

<template>
  <div>
    <PageHeader title="Disponibilidade" />

    <Alert :type="alert.type" :message="alert.message" @close="alert.message = ''" class="mb-4" />

    <p class="text-sm text-gray-500 mb-6">Configure os horários de atendimento de cada profissional.</p>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <svg class="w-6 h-6 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
    </div>

    <!-- Attendant cards -->
    <div v-else class="grid gap-4 sm:grid-cols-2">
      <div
        v-for="attendant in attendants"
        :key="attendant.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center">
              <span class="text-sm font-bold text-blue-600">{{ attendant.name.charAt(0) }}</span>
            </div>
            <div>
              <p class="font-semibold text-slate-700">{{ attendant.name }}</p>
              <p class="text-xs text-gray-400">{{ attendant.email }}</p>
            </div>
          </div>
          <button
            @click="openSchedule(attendant)"
            class="text-blue-600 hover:text-blue-800 text-sm font-semibold transition-colors px-3 py-1.5 rounded-lg hover:bg-blue-50"
          >
            Configurar
          </button>
        </div>
      </div>
    </div>

    <!-- Schedule Modal (Loukanos style) -->
    <Modal :show="!!scheduling" title="" max-width="lg" @close="scheduling = null">
      <div class="mb-4">
        <h3 class="text-lg font-semibold text-slate-800">Horários — {{ scheduling?.name }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">Marque os dias e defina o expediente.</p>
      </div>

      <div class="space-y-2.5">
        <div
          v-for="s in scheduleForm"
          :key="s.day_of_week"
          class="rounded-xl border px-4 py-3 transition-all duration-200"
          :class="s.enabled ? 'bg-blue-50/50 border-blue-200' : 'border-gray-100 bg-gray-50/30'"
        >
          <div class="flex items-center gap-3">
            <input
              type="checkbox"
              v-model="s.enabled"
              class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="w-20 text-sm font-medium" :class="s.enabled ? 'text-slate-700' : 'text-gray-400'">
              {{ s.label }}
            </span>

            <template v-if="s.enabled">
              <input
                type="time"
                v-model="s.start_time"
                step="1800"
                class="rounded-lg border border-gray-200 bg-white text-sm px-3 py-1.5 focus:ring-2 focus:ring-blue-400 focus:outline-none"
              />
              <span class="text-gray-400 text-sm">até</span>
              <input
                type="time"
                v-model="s.end_time"
                step="1800"
                class="rounded-lg border border-gray-200 bg-white text-sm px-3 py-1.5 focus:ring-2 focus:ring-blue-400 focus:outline-none"
              />
            </template>
            <span v-else class="text-sm text-gray-300 ml-2">Não atende</span>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <SecondaryButton @click="scheduling = null">Cancelar</SecondaryButton>
        <PrimaryButton @click="submitSchedule" :loading="submitting">Salvar</PrimaryButton>
      </div>
    </Modal>
  </div>
</template>
