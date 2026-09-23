import LoginView from '@/views/auth/LoginView.vue'
import RegisterCompanyView from '@/views/auth/RegisterCompanyView.vue'
import RegisterStudentView from '@/views/auth/RegisterStudentView.vue'
import RegisterUniversityView from '@/views/auth/RegisterUniversityView.vue'
import UnauthorizedView from '@/views/auth/UnauthorizedView.vue'
import PendingApprovalView from '@/views/auth/PendingApprovalView.vue'

const authRoutes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
  },
  {
    path: '/register/student',
    name: 'register-student',
    component: RegisterStudentView,
  },
  {
    path: '/register/university',
    name: 'register-university',
    component: RegisterUniversityView,
  },
  {
    path: '/register/company',
    name: 'register-company',
    component: RegisterCompanyView,
  },
  {
    path: '/unauthorized',
    name: 'unauthorized',
    component: UnauthorizedView,
  },
  {
    path: '/pending-approval',
    name: 'pending-approval',
    component: PendingApprovalView,
  },
]

export default authRoutes
