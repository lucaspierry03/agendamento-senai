<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../services/api'
import { phoneMask } from '../utils/masks'
import Modal from '../components/Modal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import TextInput from '../components/TextInput.vue'
import Alert from '../components/Alert.vue'
import PrimaryButton from '../components/PrimaryButton.vue'
import SecondaryButton from '../components/SecondaryButton.vue'
import PageHeader from '../components/PageHeader.vue'
import DataTable from '../components/DataTable.vue'
import EmptyState from '../components/EmptyState.vue'

const clients = ref([])
const loading = ref(true)
const search = ref('')
const showModal = ref(false)
const showDeleteModal = ref(false)
const editing = ref(null)
const deleteTarget = ref(null)
const submitting = ref(false)
const alert = ref({ type: '', message: '' })
const errors = ref({})

const form = ref({ name: '', phone: '', email: '' })

const columns = [
  { key: 'name', label: 'Nome' },
  { key: 'phone', label: 'Telefone' },
  { key: 'email', label: 'E-mail' },
  { key: 'actions', label: 'Ações', align: 'right' },
]

onMounted(fetchClients)

let searchTimeout
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchClients, 300)
})

async function fetchClients() {
  loading.value = true
  const params = search.value ? { search: search.value } : {}
  const { data } = await api.get('/clients', { params })
  clients.value = data.data || data
  loading.value = false
}

function openCreate() {
  editing.value = null
  form.value = { name: '', phone: '', email: '' }
  errors.value = {}
  showModal.value = true
}

function openEdit(client) {
  editing.value = client
  form.value = { name: client.name, phone: client.phone || '', email: client.email || '' }
  errors.value = {}
  showModal.value = true
}

function openDelete(client) {
  deleteTarget.value = client
  showDeleteModal.value = true
}

async function handleSubmit() {
  errors.value = {}
  submitting.value = true
  try {
    if (editing.value) {
      await api.put(`/clients/${editing.value.id}`, form.value)
      alert.value = { type: 'success', message: 'Cliente atualizado com sucesso.' }
    } else {
      await api.post('/clients', form.value)
      alert.value = { type: 'success', message: 'Cliente criado com sucesso.' }
    }
    showModal.value = false
    await fetchClients()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    }
  } finally {
    submitting.value = false
  }
}

async function handleDelete() {
  try {
    await api.delete(`/clients/${deleteTarget.value.id}`)
    alert.value = { type: 'success', message: 'Cliente excluído com sucesso.' }
    showDeleteModal.value = false
    await fetchClients()
  } catch (e) {
    alert.value = { type: 'error', message: e.response?.data?.message || 'Erro ao excluir.' }
    showDeleteModal.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Clientes">
      <PrimaryButton @click="openCreate">+ Novo Cliente</PrimaryButton>
    </PageHeader>

    <Alert :type="alert.type" :message="alert.message" @close="alert.message = ''" class="mb-4" />

    <!-- Search -->
    <div class="mb-5">
      <div class="relative w-full md:w-80">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nome ou e-mail..."
          class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 bg-white hover:border-gray-300 transition-all"
        />
      </div>
    </div>

    <DataTable :columns="columns" :data="clients" :loading="loading">
      <template #row="{ row: client }">
        <td class="px-5 py-4 font-medium text-slate-700">{{ client.name }}</td>
        <td class="px-5 py-4 text-gray-500">{{ client.phone || '—' }}</td>
        <td class="px-5 py-4 text-gray-500">{{ client.email || '—' }}</td>
        <td class="px-5 py-4 text-right space-x-3">
          <button @click="openEdit(client)" class="text-blue-600 hover:text-blue-800 text-xs font-semibold transition-colors">Editar</button>
          <button @click="openDelete(client)" class="text-red-500 hover:text-red-700 text-xs font-semibold transition-colors">Excluir</button>
        </td>
      </template>
      <template #empty>
        <EmptyState icon="users" title="Nenhum cliente encontrado" description="Cadastre seu primeiro cliente para começar." />
      </template>
    </DataTable>

    <!-- Modal -->
    <Modal :show="showModal" :title="editing ? 'Editar Cliente' : 'Novo Cliente'" @close="showModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <TextInput v-model="form.name" label="Nome" required :error="errors.name?.[0]" />
        <TextInput v-model="form.phone" label="Telefone" placeholder="(41) 99999-0000" :error="errors.phone?.[0]" maxlength="15" @update:model-value="v => form.phone = phoneMask(v)" />
        <TextInput v-model="form.email" label="E-mail" :error="errors.email?.[0]" />
        <div class="flex justify-end gap-3 pt-3">
          <SecondaryButton @click="showModal = false">Cancelar</SecondaryButton>
          <PrimaryButton type="submit" :loading="submitting">{{ editing ? 'Salvar' : 'Criar' }}</PrimaryButton>
        </div>
      </form>
    </Modal>

    <ConfirmModal :show="showDeleteModal" @confirm="handleDelete" @cancel="showDeleteModal = false" />
  </div>
</template>
