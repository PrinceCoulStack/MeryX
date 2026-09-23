import DashboardView from '@/views/company/DashboardView.vue'
import CandidatesView from '@/views/company/CandidatesView.vue'
import StudentProfileView from '@/views/company/StudentProfileView.vue'
import CompanyProfileView from '@/views/company/CompanyProfileView.vue'

export default [
  {
    path: '/company',
    component: () => import('@/layouts/CompanyLayout.vue'),
    meta: { requiresAuth: true, role: 'ROLE_COMPANY' },
    children: [
      {
        path: '',
        component: DashboardView,
      },
      {
        path: 'candidates',
        name: 'companyCandidates',
        component: CandidatesView,
      },
      {
        path: 'actuality',
        name: 'companyActualityList',
        component: () => import('@/views/company/Actuality/ListActualityView.vue'),
      },
      {
        path: 'actuality/create',
        name: 'companyCreateActuality',
        component: () => import('@/views/company/Actuality/CreateActualityView.vue'),
      },
      {
        path: 'actuality/:id/edit',
        name: 'companyEditActuality',
        component: () => import('@/views/company/Actuality/EditActualityView.vue'),
      },
      {
        path: 'opportunities',
        name: 'companyOpportunities',
        component: () => import('@/views/company/Opportinuty/ListOpportinutyView.vue'),
      },
      {
        path: 'opportunities/create',
        name: 'companyCreateOpportunity',
        component: () => import('@/views/company/Opportinuty/CreateOpportinutyView.vue'),
      },
      {
        path: 'opportunities/:id/candidates',
        name: 'opportunityCandidates',
        component: () => import('@/views/company/Opportinuty/OpportunityCandidatesView.vue'),
      },
      {
        path: 'opportunities/:id/edit',
        name: 'companyEditOpportunity',
        component: () => import('@/views/company/Opportinuty/EditOpportunityView.vue'),
      },
      {
        path: 'training',
        name: 'companyTrainingList',
        component: () => import('@/views/company/Training/ListTrainingView.vue'),
      },
      {
        path: 'training/create',
        name: 'companyCreateTraining',
        component: () => import('@/views/company/Training/CreateTrainingView.vue'),
      },
      {
        path: 'training/:id/edit',
        name: 'companyEditTraining',
        component: () => import('@/views/company/Training/EditTrainingView.vue'),
      },
      {
        path: 'messages',
        name: 'companyMessages',
        component: () => import('@/views/company/MessageView.vue'),
      },
      {
        path: 'settings',
        name: 'companySettings',
        component: () => import('@/views/company/SettingView.vue'),
      },
      {
        path: 'candidate/:id',
        name: 'companyCandidateProfile',
        component: StudentProfileView,
      },
      {
        path: 'profile',
        name: 'companyProfile',
        component: CompanyProfileView,
      },
    ],
  },
]
