<script setup>
import { useAuth } from '../composables/useAuth'

const { user, logout, isAdmin } = useAuth()

const navigation = [
  { name: 'Agendamentos', path: '/appointments', adminOnly: false },
  { name: 'Clientes', path: '/clients', adminOnly: false },
  { name: 'Usuários', path: '/users', adminOnly: false },
  { name: 'Disponibilidade', path: '/availabilities', adminOnly: true },
  { name: 'Auditoria', path: '/audit-logs', adminOnly: true },
]

function filteredNav() {
  return navigation.filter(item => !item.adminOnly || isAdmin())
}

async function handleLogout() {
  await logout()
}
</script>

<template>
  <div class="min-h-screen bg-slate-50/50">
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200/50 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center gap-8">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <span class="text-lg font-bold text-slate-800">Agendamento</span>
            </div>

            <div class="hidden md:flex gap-1">
              <router-link
                v-for="item in filteredNav()"
                :key="item.path"
                :to="item.path"
                class="px-3.5 py-2 rounded-lg text-sm font-medium transition-all duration-200"
                :class="$route.path === item.path
                  ? 'bg-blue-50 text-blue-700 shadow-sm'
                  : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
              >
                {{ item.name }}
              </router-link>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-2">
              <div class="w-8 h-8 bg-blue-50 rounded-full flex items-center justify-center">
                <span class="text-xs font-bold text-blue-600">{{ user?.name?.charAt(0) }}</span>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-slate-700 leading-tight">{{ user?.name }}</p>
                <p class="text-xs text-gray-400">{{ user?.role === 'admin' ? 'Administrador' : 'Atendente' }}</p>
              </div>
            </div>
            <button
              @click="handleLogout"
              class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-lg hover:bg-red-50"
              title="Sair"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <div class="md:hidden bg-white border-b border-gray-100 px-4 py-2 flex gap-1 overflow-x-auto">
      <router-link
        v-for="item in filteredNav()"
        :key="item.path"
        :to="item.path"
        class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-all"
        :class="$route.path === item.path
          ? 'bg-blue-50 text-blue-700'
          : 'text-gray-500 hover:bg-gray-50'"
      >
        {{ item.name }}
      </router-link>
    </div>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>
  </div>
</template>
