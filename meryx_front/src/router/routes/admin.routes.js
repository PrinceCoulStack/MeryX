import DashboardView from '@/views/admin/DashboardView.vue'
import ListRolesView from '@/views/admin/RoleManagement/ListRolesView.vue'
import AddRoleView from '@/views/admin/RoleManagement/AddRoleView.vue'
import EditRoleView from '@/views/admin/RoleManagement/EditRoleView.vue'
import ListUniversitiesView from '@/views/admin/University/ListUniversitiesView.vue'
import UniversityStudentsView from '@/views/admin/University/UniversityStudentsView.vue'
import ListCompaniesView from '@/views/admin/Company/ListCompaniesView.vue'
import CompanyPostsView from '@/views/admin/Company/CompanyPostsView.vue'
import AllTrainingsView from '@/views/admin/Company/AllTrainingsView.vue'
import AllOpportunitiesView from '@/views/admin/Company/AllOpportunitiesView.vue'
import ListStudentsView from '@/views/admin/Student/ListStudentsView.vue'
import StudentUsageRankingView from '@/views/admin/Student/StudentUsageRankingView.vue'
import SettingsView from '@/views/admin/Configuration/SettingsView.vue'
import IntegrationsView from '@/views/admin/Configuration/IntegrationsView.vue'
import AuditLogsView from '@/views/admin/Configuration/AuditLogsView.vue'
import SubAdminAccountsView from '@/views/admin/Configuration/SubAdminAccountsView.vue'
import ReportsView from '@/views/admin/ReportsView.vue'

export default [
  {
    path: '/super-admin',
    alias: ['/admin'],
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
    children: [
      {
        path: '',
        name: 'adminDashboard',
        component: DashboardView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },

      // {
      //   path: 'users',
      //   component: () => import('@/views/admin/UsersView.vue')
      // },
      // {
      //   path: 'students',
      //   component: () => import('@/views/admin/StudentsView.vue')
      // },
      // {
      //   path: 'universities',
      //   component: () => import('@/views/admin/UniversitiesView.vue')
      // },
      // {
      //   path: 'companies',
      //   component: () => import('@/views/admin/CompaniesView.vue')
      // }

      // Role Management
      {
        path: 'roles',
        name: 'adminRoles',
        component: ListRolesView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'roles/create',
        name: 'adminRoleCreate',
        component: AddRoleView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'roles/:id/edit',
        name: 'adminRoleEdit',
        component: EditRoleView,
        props: true,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'universities',
        name: 'adminUniversities',
        component: ListUniversitiesView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'universities/:id/students',
        name: 'adminUniversityStudents',
        component: UniversityStudentsView,
        props: true,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'students',
        name: 'adminStudents',
        component: ListStudentsView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'students/usage',
        name: 'adminStudentsUsage',
        component: StudentUsageRankingView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'reports',
        name: 'adminReports',
        component: ReportsView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'settings',
        name: 'adminSettings',
        component: SettingsView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'integrations',
        name: 'adminIntegrations',
        component: IntegrationsView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'audit-logs',
        name: 'adminAuditLogs',
        component: AuditLogsView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'sub-admins',
        name: 'adminSubAdmins',
        component: SubAdminAccountsView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'companies',
        name: 'adminCompanies',
        component: ListCompaniesView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'companies/:id/posts',
        name: 'adminCompanyPosts',
        component: CompanyPostsView,
        props: true,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'companies/trainings',
        name: 'adminAllTrainings',
        component: AllTrainingsView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
      {
        path: 'companies/opportunities',
        name: 'adminAllOpportunities',
        component: AllOpportunitiesView,
        meta: { requiresAuth: true, role: 'ROLE_ADMIN' },
      },
    ],
  },
]
