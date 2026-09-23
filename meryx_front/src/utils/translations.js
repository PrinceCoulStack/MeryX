// src/utils/translations.js

import { ref, computed } from 'vue'

export const translations = {
  en: {
    common: {
      backToOpportunities: 'Back to Opportunities',
      close: 'Close',
      loading: 'Loading...',
      noResults: 'No candidates match your filters.',
      candidate: 'Candidate',
      candidates: 'Candidates',
      viewFullProfile: 'View Full Profile',
    },
    opportunities: {
      title: 'Opportunities',
      manage: 'Manage',
      applications: 'Applications',
    },
    candidatesView: {
      eyebrow: 'Company / Opportunities / Candidates',
      intro: 'Review and manage all candidates who applied for this opportunity.',
      timeFilter: 'Time Period',
      allTime: 'All time',
      last24h: 'Last 24 hours',
      last7d: 'Last 7 days',
      last30d: 'Last 30 days',
      last3m: 'Last 3 months',
      statusFilter: 'Status',
      matchFilter: 'Profile Match',
      sortBy: 'Sort By',
      mostRecent: 'Most Recent',
      bestMatch: 'Best Match',
      oldest: 'Oldest',
      highMatch: 'High (80%+)',
      mediumMatch: 'Medium (50-79%)',
      lowMatch: 'Low (Below 50%)',
      allStatuses: 'All statuses',
      search: 'Search by name, program, skills...',
      loadingCandidates: 'Loading candidates...',
      noCandidates: 'No candidates match your filters.',
      applied: 'Applied',
      interviewScheduled: 'Interview Scheduled',
      offerExtended: 'Offer Extended',
      accepted: 'Accepted',
      rejected: 'Rejected',
      match: 'Match',
      appliedDate: 'Applied',
      gpa: 'GPA',
      skills: 'Skills',
      scheduleInterview: 'Schedule Interview',
      sendOffer: 'Send Offer',
      reject: 'Reject',
      markAsAccepted: 'Mark as Accepted',
    },
    candidateProfile: {
      profileMatch: 'Profile Match',
      applicationStatus: 'Application Status',
      appliedDate: 'Applied Date',
      forOpportunity: 'For Opportunity',
      applicationMessage: 'Application Message',
      academicProfile: 'Academic Profile',
      program: 'Program',
      academicLevel: 'Academic Level',
      email: 'Email',
      phone: 'Phone',
      skillsLanguages: 'Skills & Languages',
      requirementMatching: 'Requirement Matching',
      about: 'About',
    },
  },
  fr: {
    common: {
      backToOpportunities: 'Retour aux offres',
      close: 'Fermer',
      loading: 'Chargement...',
      noResults: 'Aucun candidat ne correspond à vos critères.',
      candidate: 'Candidat',
      candidates: 'Candidats',
      viewFullProfile: 'Voir le profil complet',
    },
    opportunities: {
      title: 'Offres',
      manage: 'Gérer',
      applications: 'Candidatures',
    },
    candidatesView: {
      eyebrow: 'Entreprise / Offres / Candidats',
      intro: 'Examinez et gérez tous les candidats qui ont postulé pour cette offre.',
      timeFilter: 'Période',
      allTime: 'Tous',
      last24h: 'Les 24 dernières heures',
      last7d: 'Les 7 derniers jours',
      last30d: 'Les 30 derniers jours',
      last3m: 'Les 3 derniers mois',
      statusFilter: 'Statut',
      matchFilter: 'Correspondance profil',
      sortBy: 'Trier par',
      mostRecent: 'Le plus récent',
      bestMatch: 'Meilleure correspondance',
      oldest: 'Le plus ancien',
      highMatch: 'Élevée (80%+)',
      mediumMatch: 'Moyenne (50-79%)',
      lowMatch: 'Faible (Moins de 50%)',
      allStatuses: 'Tous les statuts',
      search: 'Rechercher par nom, programme, compétences...',
      loadingCandidates: 'Chargement des candidats...',
      noCandidates: 'Aucun candidat ne correspond à vos critères.',
      applied: 'Candidature reçue',
      interviewScheduled: 'Entretien programmé',
      offerExtended: 'Offre reçue',
      accepted: 'Accepté',
      rejected: 'Rejeté',
      match: 'Correspondance',
      appliedDate: 'Candidature reçue',
      gpa: 'Moyenne',
      skills: 'Compétences',
      scheduleInterview: 'Programmer un entretien',
      sendOffer: 'Envoyer une offre',
      reject: 'Rejeter',
      markAsAccepted: 'Marquer comme accepté',
    },
    candidateProfile: {
      profileMatch: 'Correspondance profil',
      applicationStatus: 'Statut de candidature',
      appliedDate: 'Date de candidature',
      forOpportunity: "Pour l'offre",
      applicationMessage: 'Message de candidature',
      academicProfile: 'Profil académique',
      program: 'Programme',
      academicLevel: 'Niveau académique',
      email: 'E-mail',
      phone: 'Téléphone',
      skillsLanguages: 'Compétences et langues',
      requirementMatching: 'Correspondance des compétences',
      about: 'À propos',
    },
  },
}

export const useI18n = () => {
  const language = ref(localStorage.getItem('language') || 'en')

  const t = computed(() => translations[language.value] || translations.en)

  const setLanguage = (lang) => {
    if (translations[lang]) {
      language.value = lang
      localStorage.setItem('language', lang)
    }
  }

  const toggleLanguage = () => {
    setLanguage(language.value === 'en' ? 'fr' : 'en')
  }

  return { t, language, setLanguage, toggleLanguage }
}
