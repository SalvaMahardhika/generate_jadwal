import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../pages/auth/Login.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/admin/dashboard',
    name: 'admin.dashboard',
    component: () => import('../pages/admin/Dashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/teachers',
    name: 'admin.teachers',
    component: () => import('../pages/admin/Teachers.vue'),
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' });
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next({ name: 'admin.dashboard' });
  } else {
    next();
  }
});

export default router;