export default [
  {
    path: '/',
    name: 'home',
    component: () => import('@/layouts/PublicLayout.vue'),
    children: [
      {
        path: '',
        name: 'home-index',
        component: () => import('@/views/public/HomeView.vue'),
      },
      {
        path: 'about',
        name: 'about',
        component: () => import('@/views/public/AboutView.vue'),
      },
      {
        path: 'contact',
        name: 'contact',
        component: () => import('@/views/public/ContactView.vue'),
      },
      {
        path: 'opportunities',
        name: 'opportunities',
        component: () => import('@/views/public/OpportunitiesView.vue'),
      },
      {
        path: 'universities',
        name: 'universities',
        component: () => import('@/views/public/UniversitiesView.vue'),
      },
      {
        path: 'companies',
        name: 'companies',
        component: () => import('@/views/public/CompaniesView.vue'),
      },
      {
        path: 'register-choice',
        name: 'register-choice',
        component: () => import('@/views/public/ProfilChoiceView.vue'),
      },
      // {
      //   path: 'companies/:id',
      //   name: 'company-details',
      //   component: () =>
      //     import('@/views/public/CompanyDetailsView.vue'),
      // },
      // {
      //   path: 'universities/:id',
      //   name: 'university-details',
      //   component: () =>
      //     import('@/views/public/UniversityDetailsView.vue'),
      // },
    ],
  },
]
