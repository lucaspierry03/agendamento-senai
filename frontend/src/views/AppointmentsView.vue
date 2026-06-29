<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import api from '../services/api'
import Modal from '../components/Modal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import SelectInput from '../components/SelectInput.vue'
import TextInput from '../components/TextInput.vue'
import Alert from '../components/Alert.vue'
import PrimaryButton from '../components/PrimaryButton.vue'
import SecondaryButton from '../components/SecondaryButton.vue'
import PageHeader from '../components/PageHeader.vue'
import DataTable from '../components/DataTable.vue'
import EmptyState from '../components/EmptyState.vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

const calendarRef = ref(null)
const currentView = ref('timeGridWeek')

const appointments = ref([])
const attendants = ref([])
const clients = ref([])
const slots = ref([])
const loadingTable = ref(true)
const showCreateModal = ref(false)
const showCancelModal = ref(false)
const cancelTarget = ref(null)
const submitting = ref(false)
const alert = ref({ type: '', message: '' })
const errors = ref({})

const form = ref({ user_id: '', client_id: '', date: '', start_time: '', end_time: '', notes: '' })

const statusColors = {
  scheduled: '#2563eb',
  completed: '#059669',
  cancelled: '#dc2626',
}

const statusLabels = {
  scheduled: 'Agendado',
  completed: 'Concluído',
  cancelled: 'Cancelado',
}

const statusClasses = {
  scheduled: 'bg-blue-50 text-blue-700',
  completed: 'bg-emerald-50 text-emerald-700',
  cancelled: 'bg-red-50 text-red-600',
}

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  locale: 'pt-br',
  headerToolbar: false,
  slotMinTime: '07:00:00',
  slotMaxTime: '19:00:00',
  slotDuration: '00:30:00',
  slotLabelInterval: '01:00:00',
  allDaySlot: false,
  height: 'auto',
  events: calendarEvents.value,
  dateClick: handleDateClick,
  dayHeaderFormat: { weekday: 'short', day: 'numeric', month: 'numeric' },
}))

const calendarEvents = computed(() =>
  appointments.value.map(appt => ({
    id: appt.id,
    title: appt.client_name,
    start: `${appt.date_raw}T${appt.start_time}`,
    end: `${appt.date_raw}T${appt.end_time}`,
    backgroundColor: statusColors[appt.status],
    borderColor: statusColors[appt.status],
  }))
)

const tableColumns = [
  { key: 'date', label: 'Data' },
  { key: 'time', label: 'Horário' },
  { key: 'client', label: 'Cliente' },
  { key: 'attendant', label: 'Atendente' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Ações', align: 'right' },
]

function navigatePrev() { calendarRef.value?.getApi()?.prev() }
function navigateNext() { calendarRef.value?.getApi()?.next() }
function navigateToday() { calendarRef.value?.getApi()?.today() }
function setView(view) {
  currentView.value = view
  calendarRef.value?.getApi()?.changeView(view)
}

onMounted(async () => {
  await Promise.all([fetchAppointments(), fetchAttendants(), fetchClients()])
})

async function fetchAppointments() {
  loadingTable.value = true
  const { data } = await api.get('/appointments')
  appointments.value = data
  loadingTable.value = false
}

async function fetchAttendants() {
  const { data } = await api.get('/users')
  attendants.value = data.filter(u => u.role === 'attendant').map(u => ({ value: u.id, label: u.name }))
}

async function fetchClients() {
  const { data } = await api.get('/clients/all')
  clients.value = data.map(c => ({ value: c.id, label: c.name }))
}

function handleDateClick(info) {
  form.value.date = info.dateStr.split('T')[0]
  form.value.start_time = ''
  form.value.end_time = ''
  slots.value = []
  errors.value = {}
  showCreateModal.value = true
}

function openCreate() {
  form.value = { user_id: '', client_id: '', date: '', start_time: '', end_time: '', notes: '' }
  slots.value = []
  errors.value = {}
  showCreateModal.value = true
}

watch(() => [form.value.user_id, form.value.date], async ([userId, date]) => {
  if (userId && date) {
    try {
      const { data } = await api.get('/slots', { params: { user_id: userId, date } })
      slots.value = data
    } catch {
      slots.value = []
    }
  } else {
    slots.value = []
  }
})

function selectSlot(slot) {
  form.value.start_time = slot.start_time
  form.value.end_time = slot.end_time
}

async function handleCreate() {
  errors.value = {}
  submitting.value = true
  try {
    await api.post('/appointments', form.value)
    alert.value = { type: 'success', message: 'Agendamento criado com sucesso.' }
    showCreateModal.value = false
    await fetchAppointments()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else {
      alert.value = { type: 'error', message: e.response?.data?.message || 'Erro ao criar agendamento.' }
    }
  } finally {
    submitting.value = false
  }
}

function openCancel(appt) {
  cancelTarget.value = appt
  showCancelModal.value = true
}

async function handleCancel() {
  try {
    await api.patch(`/appointments/${cancelTarget.value.id}/cancel`)
    alert.value = { type: 'success', message: 'Agendamento cancelado com sucesso.' }
    showCancelModal.value = false
    await fetchAppointments()
  } catch (e) {
    alert.value = { type: 'error', message: e.response?.data?.message || 'Erro ao cancelar.' }
    showCancelModal.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Agendamentos">
      <PrimaryButton @click="openCreate">+ Novo Agendamento</PrimaryButton>
    </PageHeader>

    <Alert :type="alert.type" :message="alert.message" @close="alert.message = ''" class="mb-4" />

    <!-- Calendar Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
      <!-- Custom Toolbar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <!-- Navigation -->
        <div class="flex items-center gap-1.5">
          <button @click="navigatePrev" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-gray-50 transition-colors">
            ←
          </button>
          <button @click="navigateToday" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-gray-50 transition-colors">
            Hoje
          </button>
          <button @click="navigateNext" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-gray-50 transition-colors">
            →
          </button>
        </div>

        <!-- View Toggle -->
        <div class="flex items-center gap-0.5 rounded-lg border border-gray-200 bg-gray-50 p-1">
          <button
            @click="setView('timeGridDay')"
            class="rounded-md px-3 py-1.5 text-sm font-medium transition-all duration-200"
            :class="currentView === 'timeGridDay' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white'"
          >
            Dia
          </button>
          <button
            @click="setView('timeGridWeek')"
            class="rounded-md px-3 py-1.5 text-sm font-medium transition-all duration-200"
            :class="currentView === 'timeGridWeek' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white'"
          >
            Semana
          </button>
        </div>
      </div>

      <!-- Calendar -->
      <FullCalendar ref="calendarRef" :options="calendarOptions" />
    </div>

    <!-- Table -->
    <DataTable :columns="tableColumns" :data="appointments" :loading="loadingTable">
      <template #row="{ row: appt }">
        <td class="px-5 py-4 text-slate-700">{{ appt.date }}</td>
        <td class="px-5 py-4 text-gray-500 font-mono text-xs">{{ appt.start_time }} – {{ appt.end_time }}</td>
        <td class="px-5 py-4 font-medium text-slate-700">{{ appt.client_name }}</td>
        <td class="px-5 py-4 text-gray-500">{{ appt.user_name }}</td>
        <td class="px-5 py-4">
          <span class="text-xs px-2.5 py-1 rounded-lg font-medium" :class="statusClasses[appt.status]">
            {{ statusLabels[appt.status] }}
          </span>
        </td>
        <td class="px-5 py-4 text-right">
          <button
            v-if="appt.status === 'scheduled'"
            @click="openCancel(appt)"
            class="text-red-500 hover:text-red-700 text-xs font-semibold transition-colors"
          >
            Cancelar
          </button>
        </td>
      </template>
      <template #empty>
        <EmptyState icon="calendar" title="Nenhum agendamento" description="Crie seu primeiro agendamento clicando no botão acima ou em uma data no calendário." />
      </template>
    </DataTable>

    <!-- Create Modal -->
    <Modal :show="showCreateModal" title="Novo Agendamento" max-width="lg" @close="showCreateModal = false">
      <form @submit.prevent="handleCreate" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <SelectInput v-model="form.user_id" label="Atendente" required :options="attendants" :error="errors.user_id?.[0]" />
          <SelectInput v-model="form.client_id" label="Cliente" required :options="clients" :error="errors.client_id?.[0]" />
        </div>

        <TextInput v-model="form.date" label="Data" type="date" required :error="errors.date?.[0]" />

        <!-- Slots -->
        <div v-if="form.user_id && form.date">
          <label class="block text-sm font-medium text-gray-600 mb-2">
            Horários Disponíveis<span class="text-red-400 ml-0.5">*</span>
          </label>
          <div v-if="slots.length" class="grid grid-cols-4 sm:grid-cols-6 gap-2">
            <button
              v-for="slot in slots"
              :key="slot.start_time"
              type="button"
              @click="selectSlot(slot)"
              class="px-2 py-2 text-xs rounded-xl border font-medium transition-all duration-200"
              :class="form.start_time === slot.start_time
                ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                : 'bg-white text-slate-600 border-gray-200 hover:border-blue-300 hover:bg-blue-50'"
            >
              {{ slot.start_time }}
            </button>
          </div>
          <p v-else class="text-sm text-gray-400 bg-gray-50 rounded-xl p-4 text-center">Nenhum horário disponível para esta data.</p>
          <p v-if="errors.start_time?.[0]" class="mt-1.5 text-xs text-red-500">{{ errors.start_time[0] }}</p>
        </div>

        <!-- Selected slot summary -->
        <div v-if="form.start_time" class="bg-blue-50 rounded-xl p-3 flex items-center gap-2">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-medium text-blue-700">Horário selecionado: {{ form.start_time }} – {{ form.end_time }}</span>
        </div>

        <TextInput v-model="form.notes" label="Observações" :error="errors.notes?.[0]" />

        <div class="flex justify-end gap-3 pt-3">
          <SecondaryButton @click="showCreateModal = false">Cancelar</SecondaryButton>
          <PrimaryButton type="submit" :loading="submitting">Agendar</PrimaryButton>
        </div>
      </form>
    </Modal>

    <!-- Cancel Confirm -->
    <ConfirmModal
      :show="showCancelModal"
      title="Cancelar agendamento"
      message="Tem certeza que deseja cancelar este agendamento? O horário ficará disponível novamente."
      @confirm="handleCancel"
      @cancel="showCancelModal = false"
    />
  </div>
</template>

<style>
.fc {
  --fc-border-color: #e5e7eb;
  --fc-page-bg-color: #ffffff;
  --fc-neutral-bg-color: #f9fafb;
  --fc-today-bg-color: rgba(37, 99, 235, 0.04);
  --fc-now-indicator-color: #2563eb;
}
.fc .fc-timegrid-slot { height: 2.25rem; }
.fc .fc-event {
  border-left-width: 3px;
  border-radius: 6px;
  font-size: 0.75rem;
  padding: 2px 6px;
  cursor: pointer;
}
.fc .fc-col-header-cell {
  padding: 0.5rem 0;
  font-size: 0.8rem;
  color: #374151;
  background: #f9fafb;
}
.fc .fc-timegrid-slot-label {
  font-size: 0.75rem;
  color: #6b7280;
}
.fc td, .fc th { border-color: #e5e7eb; }
.fc .fc-timegrid-axis { background: #f9fafb; }
</style>
