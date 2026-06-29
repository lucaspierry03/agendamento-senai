<script setup>
defineProps({
  type: { type: String, default: 'success' },
  message: String,
})

const emit = defineEmits(['close'])

const styles = {
  success: 'bg-emerald-50 border-emerald-100 text-emerald-700',
  error: 'bg-red-50 border-red-100 text-red-700',
  warning: 'bg-amber-50 border-amber-100 text-amber-700',
}

const icons = {
  success: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
  error: 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z',
  warning: 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
}
</script>

<template>
  <Transition name="slide">
    <div v-if="message" class="rounded-xl border p-3.5 text-sm flex items-center gap-2.5" :class="styles[type]">
      <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" :d="icons[type]" clip-rule="evenodd" />
      </svg>
      <span class="flex-1 font-medium">{{ message }}</span>
      <button @click="emit('close')" class="opacity-50 hover:opacity-100 transition-opacity p-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </Transition>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: all 0.3s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
