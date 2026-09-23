import { createRouter, createWebHistory } from 'vue-router'

import publicRoutes from './routes/public.routes'
import authRoutes from './routes/auth.routes'
import adminRoutes from './routes/admin.routes'
import studentRoutes from './routes/student.routes'
import universityRoutes from './routes/university.routes'
import companyRoutes from './routes/company.routes'

import { authGuard } from './guards'

const routes = [
  // Public pages
  ...publicRoutes,

  // Authentication
  ...authRoutes,

  // Protected areas
  ...adminRoutes,
  ...studentRoutes,
  ...universityRoutes,
  ...companyRoutes,

  // 404 Page
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/errors/NotFoundView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return {
      top: 0,
      behavior: 'smooth',
    }
  },
})

router.beforeEach(authGuard)

export default router
