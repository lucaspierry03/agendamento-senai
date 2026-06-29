<script setup>
defineProps({
  columns: Array, // [{ key, label, align?, class? }]
  data: Array,
  loading: Boolean,
})
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Loading -->
    <div v-if="loading" class="p-8 text-center">
      <svg class="w-6 h-6 animate-spin text-blue-500 mx-auto" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      <p class="text-sm text-gray-400 mt-2">Carregando...</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50/80 border-b border-gray-100">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-5 py-3.5 font-medium text-gray-500 text-xs uppercase tracking-wider"
              :class="col.align === 'right' ? 'text-right' : 'text-left'"
            >
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr
            v-for="(row, index) in data"
            :key="row.id || index"
            class="hover:bg-blue-50/30 transition-colors"
          >
            <slot name="row" :row="row" :index="index" />
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state slot -->
    <slot v-if="!loading && data.length === 0" name="empty" />
  </div>
</template>
