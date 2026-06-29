import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    redirect: '/appointments',
  },
  {
    path: '/users',
    name: 'users',
    component: () => import('../views/UsersView.vue'),
    meta: { auth: true },
  },
  {
    path: '/clients',
    name: 'clients',
    component: () => import('../views/ClientsView.vue'),
    meta: { auth: true },
  },
  {
    path: '/availabilities',
    name: 'availabilities',
    component: () => import('../views/AvailabilitiesView.vue'),
    meta: { auth: true, admin: true },
  },
  {
    path: '/appointments',
    name: 'appointments',
    component: () => import('../views/AppointmentsView.vue'),
    meta: { auth: true },
  },
  {
    path: '/audit-logs',
    name: 'audit-logs',
    component: () => import('../views/AuditLogsView.vue'),
    meta: { auth: true, admin: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const { fetchUser, user } = useAuth()

  if (to.meta.auth) {
    const currentUser = await fetchUser()
    if (!currentUser) return '/login'
    if (to.meta.admin && currentUser.role !== 'admin') return '/appointments'
  }

  if (to.meta.guest && user.value) {
    return '/appointments'
  }
})

export default router
