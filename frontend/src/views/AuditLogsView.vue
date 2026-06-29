<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import DataTable from '../components/DataTable.vue'
import EmptyState from '../components/EmptyState.vue'

const logs = ref([])
const loading = ref(true)
const pagination = ref({})

const columns = [
  { key: 'date', label: 'Data' },
  { key: 'user', label: 'Usuário' },
  { key: 'action', label: 'Ação' },
  { key: 'model', label: 'Registro' },
  { key: 'ip', label: 'IP' },
]

const actionLabels = {
  created: 'Criação',
  updated: 'Edição',
  deleted: 'Exclusão',
}

const actionClasses = {
  created: 'bg-emerald-50 text-emerald-700',
  updated: 'bg-blue-50 text-blue-700',
  deleted: 'bg-red-50 text-red-600',
}

onMounted(() => fetchLogs())

async function fetchLogs(page = 1) {
  loading.value = true
  const { data } = await api.get('/audit-logs', { params: { page } })
  logs.value = data.data
  pagination.value = data.meta || {}
  loading.value = false
}
</script>

<template>
  <div>
    <PageHeader title="Auditoria" />

    <DataTable :columns="columns" :data="logs" :loading="loading">
      <template #row="{ row: log }">
        <td class="px-5 py-4 text-gray-500 text-xs">{{ log.created_at }}</td>
        <td class="px-5 py-4 font-medium text-slate-700">{{ log.user_name || '—' }}</td>
        <td class="px-5 py-4">
          <span class="text-xs px-2.5 py-1 rounded-lg font-medium" :class="actionClasses[log.action]">
            {{ actionLabels[log.action] || log.action }}
          </span>
        </td>
        <td class="px-5 py-4 text-gray-500 text-xs">{{ log.auditable_type }} #{{ log.auditable_id }}</td>
        <td class="px-5 py-4 text-gray-400 text-xs font-mono">{{ log.ip_address }}</td>
      </template>
      <template #empty>
        <EmptyState icon="inbox" title="Nenhum registro de auditoria" />
      </template>
    </DataTable>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex justify-center gap-1.5 mt-5">
      <button
        v-for="page in pagination.last_page"
        :key="page"
        @click="fetchLogs(page)"
        class="w-9 h-9 text-sm rounded-xl transition-all"
        :class="page === pagination.current_page
          ? 'bg-blue-600 text-white font-semibold shadow-sm'
          : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>
