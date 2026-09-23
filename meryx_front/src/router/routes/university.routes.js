import DashboardView from '@/views/university/DashboardView.vue'
import UniversityOpportunityView from '@/views/university/Opportunity/UniversityOpportunityView.vue'
import AddStudentsView from '@/views/university/Student/AddStudentsView.vue'
import EditStudentView from '@/views/university/Student/EditStudentView.vue'
import ListStudentsView from '@/views/university/Student/ListStudentsView.vue'
import StudentRegistrationRequestsView from '@/views/university/Student/StudentRegistrationRequestsView.vue'
import ManageProfileOptions from '@/views/university/Student/ManageProfileOptions.vue'
import ListCompanyView from '@/views/university/Company/ListCompanyView.vue'
import MessageCompanyView from '@/views/university/Company/MessageCompanyView.vue'
import AcademicRecordsView from '@/views/university/Student/AcademicRecordsView.vue'
import StudentProfilView from '@/views/university/Student/StudentProfilView.vue'
import UniversityReportsView from '@/views/university/Reports/UniversityReportsView.vue'
import SettingView from '@/views/university/SettingView.vue'
export default [
  {
    path: '/university',
    component: () => import('@/layouts/UniversityLayout.vue'),
    meta: { requiresAuth: true, role: 'ROLE_UNIVERSITY' },
    children: [
      {
        path: '',
        component: DashboardView,
      },
      // {
      //   path: 'profile',
      //   component: () => import('@/views/university/ProfileView.vue')
      // },
      {
        path: 'addStudent',
        name: 'addStudent',
        component: AddStudentsView,
      },
      {
        path: 'listStudents',
        component: ListStudentsView,
        name: 'listStudents',
      },
      {
        path: 'studentRequests',
        component: StudentRegistrationRequestsView,
        name: 'studentRequests',
      },
      {
        path: 'editStudent/:id',
        name: 'editStudent',
        component: EditStudentView,
      },
      {
        path: 'viewStudentProfile/:id',
        name: 'viewStudentProfile',
        component: StudentProfilView,
      },
      {
        path: 'academicRecords',
        name: 'academicRecords',
        component: AcademicRecordsView,
      },

      // Partener Companies
      {
        path: 'partnerCompanies',
        component: ListCompanyView,
        name: 'partnerCompanies',
      },
      {
        path: 'profileOptions',
        component: ManageProfileOptions,
        name: 'profileOptions',
      },
      {
        path: 'opportunities',
        name: 'opportunities',
        component: UniversityOpportunityView,
      },
      // {
      //   path: 'messageCompany/:id',
      //   component: MessageCompanyView,
      //   name: 'messageCompany',
      // },
      {
        path: 'messageCompany',
        component: MessageCompanyView,
        name: 'messageCompany',
      },
      {
        path: 'reports',
        component: UniversityReportsView,
        name: 'reports',
      },
      {
        path: 'settings',
        component: SettingView,
        name: 'universitySettings',
      },

      // {
      //   path: 'programs',
      //   component: () => import('@/views/university/ProgramsView.vue')
      // }
    ],
  },
]
