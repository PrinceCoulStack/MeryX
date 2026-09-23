# Backend Implementation - Short Version (All-in-One)

Copy this entire prompt and send to your backend team/agent.

---

````
Implement a complete candidature (job application) tracking system. This system allows
students to apply to opportunities, save opportunities, and track their application status.

## DATABASE SCHEMA

Create two tables:

**candidatures**
- id (BIGINT PK auto-increment)
- opportunity_id (BIGINT NOT NULL, FK → opportunities.id cascade)
- student_id (BIGINT NOT NULL, FK → student_profiles.id cascade)
- status VARCHAR(50) default 'applied' (enum: applied, interview, offer, accepted, rejected)
- applied_date DATETIME default NOW()
- last_updated DATETIME default NOW() on update
- feedback TEXT nullable
- notes TEXT nullable
- interview_date DATETIME nullable
- score INT default 0
- created_at DATETIME default NOW()
- updated_at DATETIME default NOW() on update
- UNIQUE(opportunity_id, student_id)
- INDEX(student_id), INDEX(status), INDEX(created_at), INDEX(student_id, status)

**saved_opportunities**
- id (BIGINT PK auto-increment)
- opportunity_id (BIGINT NOT NULL, FK → opportunities.id cascade)
- student_id (BIGINT NOT NULL, FK → student_profiles.id cascade)
- notes TEXT nullable
- saved_date DATETIME default NOW()
- created_at DATETIME default NOW()
- updated_at DATETIME default NOW() on update
- UNIQUE(opportunity_id, student_id)
- INDEX(student_id), INDEX(created_at)

## ENTITIES

Candidature:
- id, opportunityId, studentId, status, appliedDate, lastUpdated, feedback, notes,
  interviewDate, score, createdAt, updatedAt
- Include nested opportunity and student objects

SavedOpportunity:
- id, opportunityId, studentId, notes, savedDate, createdAt, updatedAt
- Include nested opportunity object

## API ENDPOINTS (6 total)

### GET /api/candidatures
- Query params: studentId, status, opportunityId, createdAfter, createdBefore,
  sortBy (appliedDate|lastUpdated|score), order (ASC|DESC), page, limit
- Response: JSON-LD hydra:Collection with nested opportunity/student
- Auth: JWT required
- Authorization: Student sees own only, Company sees own opportunities', Admin sees all
- Return 200

### POST /api/candidatures
- Body: {opportunityId, studentId, notes (optional)}
- Auto-set: status="applied", appliedDate=now(), lastUpdated=now()
- Validation: Both IDs valid, unique application check, student approved
- Return 409 if duplicate, else 201 Created
- Auth: JWT, student can only apply for self

### PATCH /api/candidatures/{id}
- Body: {status, feedback (optional), notes (optional), interviewDate (optional)}
- Validate transitions: applied→interview|rejected, interview→offer|rejected,
  offer→accepted|rejected, then terminal
- Return 400 if invalid transition
- Auto-set: lastUpdated=now()
- Auth: JWT, company/admin only, company limited to own opportunities
- Log status changes
- Return 200

### GET /api/savedOpportunities
- Query params: studentId, savedAfter, savedBefore, search (fulltext), page, limit
- Response: JSON-LD hydra:Collection with nested opportunity
- Auth: JWT required, student sees own only
- Return 200

### POST /api/savedOpportunities
- Body: {opportunityId, studentId, notes (optional)}
- **IDEMPOTENT**: If already saved, return 200 with existing record (NOT 409)
- Auto-set: savedDate=now()
- Validation: Both IDs valid
- Auth: JWT, student saves for self
- Return 201 (new) or 200 (existing)

### DELETE /api/savedOpportunities/{id}
- Auth: JWT, student can only delete own
- Return 204 No Content
- Return 404 if not found

## BUSINESS LOGIC

Status Transitions:
- applied → interview OR rejected (only)
- interview → offer OR rejected
- offer → accepted OR rejected
- accepted (terminal - no changes)
- rejected (terminal - no changes)
- Enforce via validation method, throw error on invalid transition
- Log all status changes with user ID who made change

Validation:
- appliedDate: immutable (set once, never update)
- lastUpdated: auto-update on any PATCH
- duplicate applications: prevent via unique constraint
- saved opportunities: allow duplicates (return existing via idempotency)

## AUTHORIZATION

**All endpoints require JWT token**

GET /api/candidatures:
- ROLE_STUDENT: self only (filter by their studentId)
- ROLE_COMPANY: own opportunities' applications only
- ROLE_ADMIN: all

POST /api/candidatures:
- ROLE_STUDENT: authenticated, create for self only
- Student profile must be approved

PATCH /api/candidatures:
- ROLE_COMPANY: own opportunities only
- ROLE_ADMIN: any
- ROLE_STUDENT: forbidden (403)

GET /api/savedOpportunities:
- ROLE_STUDENT: self only
- ROLE_COMPANY: forbidden
- ROLE_ADMIN: can see all

POST/DELETE /api/savedOpportunities:
- ROLE_STUDENT: authenticated, own records only
- ROLE_COMPANY: forbidden
- ROLE_ADMIN: can manage any

Implement middleware to extract role/userId from JWT claims.

## ERROR RESPONSES

Format:
```json
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "Error title",
  "hydra:description": "Detailed message"
}
````

Codes:

- 201 Created: successful POST
- 200 OK: successful GET or existing record returned (save idempotency)
- 204 No Content: successful DELETE
- 400 Bad Request: invalid data or invalid status transition
- 401 Unauthorized: missing/invalid JWT
- 403 Forbidden: authenticated but no permission
- 404 Not Found: resource not found
- 409 Conflict: duplicate candidature (NOT for saved - they're idempotent)

## RESPONSE FORMAT

All responses use JSON-LD hydra:Collection format:

Single candidature:

```json
{
  "@context": "/api/contexts/Candidature",
  "@type": "Candidature",
  "@id": "/api/candidatures/1",
  "id": 1,
  "opportunityId": "/api/opportunities/123",
  "opportunity": {
    "@type": "Opportunity",
    "@id": "/api/opportunities/123",
    "id": 123,
    "title": "Senior Dev",
    "company": "TechCorp"
  },
  "studentId": "/api/studentProfiles/456",
  "student": {
    "@type": "StudentProfile",
    "@id": "/api/studentProfiles/456",
    "id": 456,
    "fullName": "John Doe"
  },
  "status": "applied",
  "appliedDate": "2026-01-15T10:00:00Z",
  "lastUpdated": "2026-01-15T10:00:00Z",
  "feedback": null,
  "notes": null,
  "interviewDate": null,
  "score": 0,
  "createdAt": "2026-01-15T10:00:00Z",
  "updatedAt": "2026-01-15T10:00:00Z"
}
```

Collection:

```json
{
  "@context": "/api/contexts/Candidature",
  "@type": "hydra:Collection",
  "@id": "/api/candidatures",
  "hydra:member": [...],
  "hydra:totalItems": 42,
  "hydra:view": {
    "@id": "/api/candidatures?page=1",
    "@type": "hydra:PartialCollectionView",
    "hydra:first": "/api/candidatures?page=1",
    "hydra:last": "/api/candidatures?page=3",
    "hydra:next": "/api/candidatures?page=2"
  }
}
```

## TESTING

Create tests for:

POST /api/candidatures:

- ✓ Create successfully
- ✓ Prevent duplicate (409)
- ✓ Validate required fields (400)
- ✓ Auth required
- ✓ Unique constraint enforced

PATCH /api/candidatures:

- ✓ Update with valid transition
- ✓ Reject invalid transition (400)
- ✓ Student cannot update (403)
- ✓ Company can only update own opportunities
- ✓ lastUpdated auto-set
- ✓ Admin can update any

GET /api/candidatures:

- ✓ Student sees only own
- ✓ Company sees own opportunities'
- ✓ Filter by status works
- ✓ Pagination works
- ✓ Nested objects included

GET /api/savedOpportunities:

- ✓ Student sees only own
- ✓ Filter by search works
- ✓ Pagination works

POST /api/savedOpportunities:

- ✓ Save successfully
- ✓ Idempotent (save twice = same record, 200 OK)
- ✓ Only student can see own saved

Create fixtures: 3 students, 5 opportunities, 8 candidatures with various statuses, saved opportunities.

## DOCUMENTATION

Generate:

- OpenAPI/Swagger spec
- Endpoint documentation with examples
- Authorization matrix (table showing what each role can do)
- Status transition diagram
- Error scenarios guide
- Frontend integration guide

## OPTIONAL ENHANCEMENTS

- Email notifications on status changes
- Audit logging table (track all changes)
- Opportunity.getCandidatureCount()
- Analytics (success rates, time to offer, etc.)
- WebSocket for real-time updates

## DELIVERABLES

1. Database migrations
2. Candidature & SavedOpportunity entities
3. All 6 API endpoints
4. Authorization middleware
5. Tests (>80% coverage)
6. API documentation (OpenAPI)
7. Sample data fixtures

## KEY REQUIREMENTS SUMMARY

✅ 2 database tables with proper constraints
✅ 6 REST API endpoints (GET/POST/PATCH/DELETE)
✅ JSON-LD hydra:Collection format responses
✅ JWT authentication on all endpoints
✅ Role-based authorization (ROLE_STUDENT, ROLE_COMPANY, ROLE_ADMIN)
✅ Status transition validation
✅ Idempotent save operations (critical!)
✅ Unique constraint on applications (prevent duplicates)
✅ Nested opportunity/student objects in responses
✅ Proper error codes and messages
✅ Audit logging for status changes
✅ Comprehensive test coverage
✅ Complete API documentation

Implement in this order:

1. Database schema + migrations
2. Entities (Candidature, SavedOpportunity)
3. GET endpoints (read data)
4. POST endpoints (create data)
5. PATCH endpoint (update status)
6. DELETE endpoint (remove saved)
7. Authorization layer
8. Error handling
9. Tests
10. Documentation

```

---

## QUICK NOTES

- **All timestamps:** ISO 8601 UTC format
- **appliedDate:** immutable, set once, never update
- **lastUpdated:** auto-update on any PATCH
- **Idempotency:** Critical for saved opportunities - return 200 with existing if already saved
- **Status transitions:** Validate, throw error on invalid
- **Authorization:** Check JWT claims, enforce role-based access
- **Nested objects:** Always include opportunity and student objects in responses
- **Unique constraints:** Prevent duplicate applications via DB constraint
- **Frontend:** Uses OpportunityView (apply/save) + CandidatureFlowView (track status)

Done in 3-5 days by experienced backend dev.
```
