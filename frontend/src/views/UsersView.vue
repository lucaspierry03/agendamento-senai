<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../composables/useAuth'
import api from '../services/api'
import Modal from '../components/Modal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import TextInput from '../components/TextInput.vue'
import SelectInput from '../components/SelectInput.vue'
import Alert from '../components/Alert.vue'
import PrimaryButton from '../components/PrimaryButton.vue'
import SecondaryButton from '../components/SecondaryButton.vue'
import PageHeader from '../components/PageHeader.vue'
import DataTable from '../components/DataTable.vue'
import EmptyState from '../components/EmptyState.vue'

const { user, isAdmin } = useAuth()

const users = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteModal = ref(false)
const editing = ref(null)
const deleteTarget = ref(null)
const submitting = ref(false)
const alert = ref({ type: '', message: '' })
const errors = ref({})

const form = ref({ name: '', email: '', password: '', password_confirmation: '', role: 'attendant' })

const roleOptions = [
  { value: 'admin', label: 'Administrador' },
  { value: 'attendant', label: 'Atendente' },
]

const columns = [
  { key: 'name', label: 'Nome' },
  { key: 'email', label: 'E-mail' },
  { key: 'role', label: 'Tipo' },
  { key: 'actions', label: 'Ações', align: 'right' },
]

onMounted(fetchUsers)

async function fetchUsers() {
  loading.value = true
  const { data } = await api.get('/users')
  users.value = data
  loading.value = false
}

function openCreate() {
  editing.value = null
  form.value = { name: '', email: '', password: '', password_confirmation: '', role: 'attendant' }
  errors.value = {}
  showModal.value = true
}

function openEdit(u) {
  editing.value = u
  form.value = { name: u.name, role: u.role }
  errors.value = {}
  showModal.value = true
}

function openDelete(u) {
  deleteTarget.value = u
  showDeleteModal.value = true
}

async function handleSubmit() {
  errors.value = {}
  submitting.value = true
  try {
    if (editing.value) {
      await api.put(`/users/${editing.value.id}`, form.value)
      alert.value = { type: 'success', message: 'Usuário atualizado com sucesso.' }
    } else {
      await api.post('/users', form.value)
      alert.value = { type: 'success', message: 'Usuário criado com sucesso.' }
    }
    showModal.value = false
    await fetchUsers()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else if (e.response?.status === 403) {
      alert.value = { type: 'error', message: 'Sem permissão para realizar esta ação.' }
      showModal.value = false
    }
  } finally {
    submitting.value = false
  }
}

async function handleDelete() {
  try {
    await api.delete(`/users/${deleteTarget.value.id}`)
    alert.value = { type: 'success', message: 'Usuário excluído com sucesso.' }
    showDeleteModal.value = false
    await fetchUsers()
  } catch (e) {
    alert.value = { type: 'error', message: e.response?.data?.message || 'Erro ao excluir.' }
    showDeleteModal.value = false
  }
}

function canEdit(u) {
  return isAdmin() || u.id === user.value?.id
}

function canDelete(u) {
  return isAdmin() && u.id !== user.value?.id
}
</script>

<template>
  <div>
    <PageHeader title="Usuários">
      <PrimaryButton v-if="isAdmin()" @click="openCreate">
        + Novo Usuário
      </PrimaryButton>
    </PageHeader>

    <Alert :type="alert.type" :message="alert.message" @close="alert.message = ''" class="mb-4" />

    <DataTable :columns="columns" :data="users" :loading="loading">
      <template #row="{ row: u }">
        <td class="px-5 py-4 font-medium text-slate-700">{{ u.name }}</td>
        <td class="px-5 py-4 text-gray-500">{{ u.email }}</td>
        <td class="px-5 py-4">
          <span
            class="text-xs px-2.5 py-1 rounded-lg font-medium"
            :class="u.role === 'admin' ? 'bg-slate-100 text-slate-700' : 'bg-blue-50 text-blue-700'"
          >
            {{ u.role === 'admin' ? 'Administrador' : 'Atendente' }}
          </span>
        </td>
        <td class="px-5 py-4 text-right space-x-3">
          <button v-if="canEdit(u)" @click="openEdit(u)" class="text-blue-600 hover:text-blue-800 text-xs font-semibold transition-colors">Editar</button>
          <button v-if="canDelete(u)" @click="openDelete(u)" class="text-red-500 hover:text-red-700 text-xs font-semibold transition-colors">Excluir</button>
        </td>
      </template>
      <template #empty>
        <EmptyState icon="users" title="Nenhum usuário cadastrado" />
      </template>
    </DataTable>

    <!-- Create/Edit Modal -->
    <Modal :show="showModal" :title="editing ? 'Editar Usuário' : 'Novo Usuário'" @close="showModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <TextInput v-model="form.name" label="Nome" required :error="errors.name?.[0]" />
        <template v-if="!editing">
          <TextInput v-model="form.email" label="E-mail" required :error="errors.email?.[0]" />
          <TextInput v-model="form.password" label="Senha" type="password" required :error="errors.password?.[0]" />
          <TextInput v-model="form.password_confirmation" label="Confirme a Senha" type="password" required />
        </template>
        <SelectInput v-if="isAdmin()" v-model="form.role" label="Tipo de Usuário" required :options="roleOptions" :error="errors.role?.[0]" />
        <div class="flex justify-end gap-3 pt-3">
          <SecondaryButton @click="showModal = false">Cancelar</SecondaryButton>
          <PrimaryButton type="submit" :loading="submitting">{{ editing ? 'Salvar' : 'Criar' }}</PrimaryButton>
        </div>
      </form>
    </Modal>

    <ConfirmModal :show="showDeleteModal" @confirm="handleDelete" @cancel="showDeleteModal = false" />
  </div>
</template>
