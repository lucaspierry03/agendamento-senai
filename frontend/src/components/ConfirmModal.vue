<script setup>
import Modal from './Modal.vue'

defineProps({
  show: Boolean,
  title: { type: String, default: 'Confirmar exclusão' },
  message: { type: String, default: 'Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita.' },
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <Modal :show="show" :title="title" max-width="sm" @close="emit('cancel')">
    <div class="text-center py-2">
      <div class="mx-auto w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mb-4">
        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </div>
      <p class="text-gray-500 text-sm mb-6">{{ message }}</p>
    </div>
    <div class="flex gap-3">
      <button
        @click="emit('cancel')"
        class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors"
      >
        Cancelar
      </button>
      <button
        @click="emit('confirm')"
        class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors shadow-sm"
      >
        Excluir
      </button>
    </div>
  </Modal>
</template>
