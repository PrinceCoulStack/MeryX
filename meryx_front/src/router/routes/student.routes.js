import DashboardView from '@/views/student/DashboardView.vue'
import OpportunityView from '@/views/student/OpportunityView.vue'
import CandidatureFlowView from '@/views/student/CandidatureFlowView.vue'
import formationView from '@/views/student/FormationView.vue'
import ProfileView from '@/views/student/ProfileView.vue'
import SettingView from '@/views/student/SettingView.vue'
import MessageView from '@/views/student/MessageView.vue'

export default [
  {
    path: '/student',
    component: () => import('@/layouts/StudentLayout.vue'),
    meta: { requiresAuth: true, role: 'ROLE_STUDENT' },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: DashboardView,
      },
      {
        path: 'studentOpportunities',
        name: 'studentOpportunities',
        component: OpportunityView,
      },
      {
        path: 'candidature',
        name: 'candidatureFlow',
        component: CandidatureFlowView,
      },
      {
        path: 'profile',
        name: 'profile',
        component: ProfileView,
      },
      {
        path: 'formation',
        name: 'formation',
        component: formationView,
      },
      {
        path: 'messages',
        name: 'studentMessages',
        component: MessageView,
      },
      {
        path: 'settings',
        name: 'studentSettings',
        component: SettingView,
      },
      // {
      //   path: 'profile',
      //   component: () => import('@/views/student/ProfileView.vue')
      // },
      // {
      //   path: 'internships',
      //   component: () => import('@/views/student/InternshipsView.vue')
      // },
      // {
      //   path: 'scholarships',
      //   component: () => import('@/views/student/ScholarshipsView.vue')
      // },
      // {
      //   path: 'applications',
      //   component: () => import('@/views/student/ApplicationsView.vue')
      // }
    ],
  },
]
