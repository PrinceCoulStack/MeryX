# Student Candidature Feature - Developer Quick Start

## Quick Overview

Students can now:

1. **Save opportunities** for later review
2. **Apply to opportunities** (creating a candidature)
3. **Track application status** through the candidature journey (applied → interview → offer → accepted)

## Using the Composable in Components

### Import

```javascript
import { useCandidature } from '@/compasables/useCandidature'
```

### In `<script setup>`

```javascript
const {
  // State (reactive)
  candidatures, // Array of application objects
  savedOpportunities, // Array of saved opportunity objects
  isLoadingCandidatures, // Boolean for loading state
  isSavingOpportunity, // Boolean for save operation state
  candidatureError, // String with error message

  // Methods
  fetchCandidatures, // Load student's applications
  fetchSavedOpportunities, // Load student's saved opportunities
  saveOpportunity, // Add opportunity to saved list
  unsaveOpportunity, // Remove from saved list
  applyCandidature, // Submit application
  updateCandidatureStatus, // Change application status (admin only)

  // Query helpers
  isSaved, // Check if opportunity is saved
  isApplied, // Check if opportunity is applied to
  getCandidatureByOpportunity, // Get application details
} = useCandidature()
```

## Common Usage Patterns

### Load Student's Data

```javascript
const studentId = 123

await Promise.all([fetchCandidatures(studentId), fetchSavedOpportunities(studentId)])

// Now you can use candidatures.value and savedOpportunities.value
```

### Save an Opportunity

```javascript
try {
  const saved = await saveOpportunity(
    opportunityId, // number
    studentId, // number
    notes, // optional string
  )
  console.log('Saved:', saved)
} catch (error) {
  console.error('Failed to save:', error.message)
}
```

### Apply to Opportunity

```javascript
try {
  const candidature = await applyCandidature(
    opportunityId, // number
    studentId, // number
    notes, // optional string
  )
  console.log('Applied:', candidature)
} catch (error) {
  console.error('Failed to apply:', error.message)
}
```

### Check Status

```javascript
// Check if already saved
if (isSaved(opportunityId, studentId)) {
  console.log('This opportunity is saved')
}

// Check if already applied
if (isApplied(opportunityId, studentId)) {
  console.log('Already applied to this opportunity')
}
```

### Remove Saved Opportunity

```javascript
try {
  await unsaveOpportunity(opportunityId, studentId)
  console.log('Removed from saved')
} catch (error) {
  console.error('Failed to remove:', error.message)
}
```

### Update Candidature Status (Admin/System)

```javascript
try {
  const updated = await updateCandidatureStatus(
    candidatureId, // number
    'interview', // 'applied' | 'interview' | 'offer' | 'accepted' | 'rejected'
    feedback, // optional string
  )
  console.log('Status updated:', updated)
} catch (error) {
  console.error('Failed to update:', error.message)
}
```

## Candidature Status Flow

```
┌─────────┐
│ applied │  Student just applied
└────┬────┘
     │
     ▼
┌───────────┐
│ interview │  Company wants to interview
└────┬──────┘
     │
     ├─────────────────┐
     │                 │
     ▼                 ▼
┌────────┐        ┌──────────┐
│ offer  │        │ rejected │
└───┬────┘        └──────────┘
    │
    ├──────────────────┐
    │                  │
    ▼                  ▼
┌──────────┐      ┌──────────┐
│ accepted │      │ rejected │
└──────────┘      └──────────┘
```

## Data Models

### Candidature Object

```javascript
{
  id: 1,
  opportunityId: 123,
  opportunity: {
    id: 123,
    title: 'Senior Developer',
    company: 'TechCorp'
  },
  studentId: 456,
  student: {
    id: 456,
    fullName: 'John Doe'
  },
  status: 'interview',           // Current status
  appliedDate: '2026-01-15T10:00:00Z',
  lastUpdated: '2026-01-20T14:30:00Z',
  feedback: 'Great progress...',
  notes: 'Interview scheduled for next week',
  interviewDate: '2026-01-25T14:00:00Z',
  score: 85,
  raw: { /* full API response */ }
}
```

### Saved Opportunity Object

```javascript
{
  id: 1,
  opportunityId: 123,
  opportunity: {
    id: 123,
    title: 'Senior Developer',
    company: 'TechCorp'
  },
  studentId: 456,
  student: {
    id: 456,
    fullName: 'John Doe'
  },
  savedDate: '2026-01-15T10:00:00Z',
  notes: 'Interesting role, salary range matches expectations',
  raw: { /* full API response */ }
}
```

## Views Available

### OpportunityView

- Path: `/student/studentOpportunities`
- Shows available opportunities with save/apply buttons
- Displays recommendation status and match reasons
- Filters and search functionality

### CandidatureFlowView

- Path: `/student/candidature`
- Shows student's application timeline
- Status progression visualization
- Manage saved opportunities
- Accept offers

## Error Handling

All methods throw errors on failure. Always wrap calls in try/catch:

```javascript
try {
  await applyCandidature(opId, studentId)
} catch (error) {
  console.error('Error:', error.message)
  // Show user-friendly message
  showToast(error.message, 'error')
}
```

## With useToast Integration

```javascript
import { useToast } from '@/compasables/useToast'
const { showToast } = useToast()

try {
  await saveOpportunity(opId, studentId)
  showToast('Opportunity saved successfully', 'success')
} catch (error) {
  showToast(error?.message || 'Failed to save', 'error')
}
```

## Reactive Computed Values

```javascript
// Filter by status
const appliedCount = computed(() => candidatures.value.filter((c) => c.status === 'applied').length)

// Find specific candidature
const userApplications = computed(() =>
  candidatures.value.filter((c) => c.studentId === currentStudentId),
)

// Group by status
const applicationsByStatus = computed(() => {
  const grouped = {}
  candidatures.value.forEach((c) => {
    if (!grouped[c.status]) grouped[c.status] = []
    grouped[c.status].push(c)
  })
  return grouped
})
```

## Best Practices

1. **Always check for existing states** before saving/applying:

   ```javascript
   if (!isSaved(opId, studentId)) {
     await saveOpportunity(opId, studentId)
   }
   ```

2. **Use try/catch for all operations** - any API call can fail

3. **Show loading states** - use `isLoadingCandidatures` in UI

4. **Provide user feedback** - toast notifications for actions

5. **Batch fetch data** - load everything on component mount

   ```javascript
   await Promise.all([fetchCandidatures(studentId), fetchSavedOpportunities(studentId)])
   ```

6. **Handle errors gracefully** - always show error messages to user

## Testing Example

```javascript
import { useCandidature } from '@/compasables/useCandidature'
import { describe, it, expect, vi, beforeEach } from 'vitest'

describe('useCandidature', () => {
  it('should save opportunity', async () => {
    const { saveOpportunity, isSaved } = useCandidature()
    await saveOpportunity(1, 1)
    expect(isSaved(1, 1)).toBe(true)
  })

  it('should apply to opportunity', async () => {
    const { applyCandidature, isApplied } = useCandidature()
    await applyCandidature(1, 1)
    expect(isApplied(1, 1)).toBe(true)
  })
})
```
