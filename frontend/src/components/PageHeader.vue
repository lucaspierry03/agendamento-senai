<script setup>
defineProps({
  title: String,
})
</script>

<template>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold text-slate-800">{{ title }}</h1>
    <div class="flex items-center gap-2">
      <slot />
    </div>
  </div>
</template>
