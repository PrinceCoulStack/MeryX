# Student Opportunity & Candidature Feature - Implementation Summary

## Overview

This implementation adds functionality for students to save opportunities, track their applications (candidature flow), and monitor the progression of their job applications.

## Files Created

### 1. `src/compasables/useCandidature.js`

A composable that manages all candidature and saved opportunity operations.

**Key Features:**

- `fetchCandidatures(studentId)` - Fetch student's applications
- `fetchSavedOpportunities(studentId)` - Fetch saved opportunities
- `saveOpportunity(opportunityId, studentId)` - Save an opportunity
- `unsaveOpportunity(opportunityId, studentId)` - Remove saved opportunity
- `applyCandidature(opportunityId, studentId)` - Apply to an opportunity
- `updateCandidatureStatus(candidatureId, status)` - Update application status
- Helper functions: `isSaved()`, `isApplied()`, `getCandidatureByOpportunity()`

**State Management:**

- `candidatures` - Array of applications with status tracking
- `savedOpportunities` - Array of bookmarked opportunities
- `isLoadingCandidatures` - Loading state
- `isSavingOpportunity` - Saving operation state
- `candidatureError` - Error messages

### 2. `src/views/student/CandidatureFlowView.vue`

A comprehensive view for tracking the candidature journey.

**Features:**

- **Application Timeline**: Visual progression through stages (applied → interview → offer → accepted)
- **Status Filters**: Filter applications by status (applied, interview, offer, accepted, rejected)
- **Search**: Search by opportunity title or company name
- **Saved Opportunities List**: Manage and apply to saved opportunities
- **Statistics**: Display counts for each status category
- **Bilingual Support**: Full French/English translations
- **Offer Management**: Accept offers directly from the view

**Sections:**

1. Hero section with application statistics
2. Toolbar with filters and search
3. Timeline view of candidatures with status progression
4. Saved opportunities list with quick actions

### 3. Updated `src/views/student/OpportunityView.vue`

Enhanced with persistent save/apply functionality.

**Changes:**

- Integrated `useCandidature` composable
- Replaced local state with backend-persisted data
- Added toast notifications for user feedback
- Load candidatures and saved opportunities on component mount
- Dynamic save/apply buttons based on actual backend state

### 4. Updated `src/router/routes/student.routes.js`

Added new route for candidature tracking.

**New Route:**

```javascript
{
  path: 'candidature',
  name: 'candidatureFlow',
  component: CandidatureFlowView,
}
```

Route path: `/student/candidature`

## Database Models Expected

The implementation assumes the following API endpoints and data models:

### Candidatures/Applications

```
GET/POST /api/candidatures
PATCH /api/candidatures/{id}

Expected fields:
- id: number
- opportunityId: string (IRI reference)
- studentId: string (IRI reference)
- status: 'applied' | 'interview' | 'offer' | 'accepted' | 'rejected'
- appliedDate: ISO date string
- lastUpdated: ISO date string
- feedback: string (optional)
- notes: string (optional)
- interviewDate: ISO date string (optional)
- score: number (optional)
```

### Saved Opportunities

```
GET/POST/DELETE /api/savedOpportunities

Expected fields:
- id: number
- opportunityId: string (IRI reference)
- studentId: string (IRI reference)
- savedDate: ISO date string
- notes: string (optional)
```

## Integration Points

### 1. OpportunityView - Save/Apply Buttons

When a student clicks "Save" or "Apply" buttons in OpportunityView:

1. `toggleSave()` calls `saveOpportunity()` or `unsaveOpportunity()`
2. `applyNow()` calls `applyCandidature()`
3. User receives toast notification of success/error
4. UI updates reflect the persisted state

### 2. CandidatureFlowView - Application Tracking

Students can:

- View all their applications with current status
- See timeline progress through interview stages
- Filter by status to focus on specific applications
- Search for opportunities by name or company
- Manage saved opportunities
- Accept offers when received

## Toast Notifications

Uses existing `useToast()` composable to show user feedback:

- Success messages on save/apply/accept actions
- Error messages with descriptive text
- Info messages for duplicate actions

## Bilingual Support

Full English/French translations for:

- All UI labels and buttons
- Status labels and messages
- Placeholder text and empty states
- Toast notifications

## State Management Flow

```
OpportunityView
├── Loads opportunities on mount
├── Loads student profile
├── Loads candidatures from useCandidature
├── Loads saved opportunities from useCandidature
└── Updates saved/applied state dynamically

CandidatureFlowView
├── Loads student profile
├── Fetches candidatures on mount
├── Filters and displays timeline
├── Allows status updates
└── Manages saved opportunities
```

## Error Handling

- Network errors caught and displayed to user
- Duplicate action prevention (checking existing saves/applies)
- Validation of student profile before operations
- User-friendly error messages via toast

## Testing Checklist

### OpportunityView Tests

- [ ] Click "Save" - should persist to backend
- [ ] Click "Saved" - should remove from backend
- [ ] Click "Apply Now" - should create candidature
- [ ] See toast notifications on actions
- [ ] Saved/Applied state reflects backend on page reload

### CandidatureFlowView Tests

- [ ] View displays all student's applications
- [ ] Timeline shows correct status progression
- [ ] Status filters work correctly
- [ ] Search filters by title and company
- [ ] Can accept offers
- [ ] Can view saved opportunities
- [ ] Can apply to saved opportunities
- [ ] Can remove saved opportunities

### Bilingual Tests

- [ ] French translations display correctly
- [ ] UI is responsive on mobile
- [ ] Dates format correctly for locale

## Performance Considerations

- Candidatures fetched only after student profile loads
- No unnecessary API calls on repeated mounts
- Toast notifications auto-dismiss
- Efficient filtering using computed properties

## Future Enhancements

1. Add interview scheduling from the UI
2. Email notifications on status changes
3. Analytics dashboard for application success rates
4. Bulk actions on applications
5. Interview preparation materials
6. Export application history
7. Networking/follow-up features
8. Company review integration
