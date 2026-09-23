const ENTITIES = [
  'userType',
  'user',
  'university',
  'trainingRequirement',
  'trainingEnrollment',
  'training',
  'systemSetting',
  'studentProfile',
  'studentDocuments',
  'skills',
  'postRection',
  'postComments',
  'partnership',
  'opportunityRequirement',
  'opportunities',
  'notification',
  'message',
  'marks',
  'language',
  'department',
  'conversationParticipant',
  'conversation',
  'companyPost',
  'company',
  'candidate',
  'auditLog',
  'approvalRequest',
  'applicationStatusHistory',
  'application',
  'address',
  'academicProgram',
  'academicClass',
]

const ACTIONS = [
  { key: 'create', labels: { en: 'Create', fr: 'Creer' } },
  { key: 'read', labels: { en: 'Read', fr: 'Lire' } },
  { key: 'update', labels: { en: 'Update', fr: 'Modifier' } },
  { key: 'delete', labels: { en: 'Delete', fr: 'Supprimer' } },
]

const toSnakeCase = (value) =>
  value
    .replace(/([a-z0-9])([A-Z])/g, '$1_$2')
    .replace(/[\s-]+/g, '_')
    .toLowerCase()

const humanizeEntity = (value) =>
  value
    .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
    .replace(/[_-]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\b\w/g, (char) => char.toUpperCase())

export const getPermissionGroups = (locale = 'en') =>
  ENTITIES.map((entity) => {
    const entityCode = toSnakeCase(entity)

    return {
      entity,
      label: humanizeEntity(entity),
      actions: ACTIONS.map((action) => ({
        code: `${action.key}_${entityCode}`,
        label: action.labels[locale] || action.labels.en,
      })),
    }
  })
