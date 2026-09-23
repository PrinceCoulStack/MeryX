# Backend Prompts Cheat Sheet

Quick copy-paste prompts for your backend agent. Use in order.

---

## 1️⃣ DATABASE SCHEMA

```
Create database schema for candidatures and saved_opportunities tables with:
- candidatures: id, opportunity_id, student_id, status (applied/interview/offer/accepted/rejected),
  applied_date, last_updated, feedback, notes, interview_date, score, created_at, updated_at
- saved_opportunities: id, opportunity_id, student_id, notes, saved_date, created_at, updated_at
- Unique constraints on (opportunity_id, student_id) in both tables
- Foreign keys with cascade delete
- Indexes on student_id, status, created_at
Include migration files.
```

---

## 2️⃣ CANDIDATURES API

```
Implement REST API for candidatures:

1. GET /api/candidatures - fetch applications
   - Filter by studentId (query param)
   - Support pagination
   - Return JSON-LD hydra:Collection
   - Include nested opportunity and student objects
   - Auth required, students see only own

2. POST /api/candidatures - create application
   - Accept: opportunityId, studentId, notes (optional)
   - Auto-set: status="applied", appliedDate=now()
   - Validate: check for duplicates (409 if exists)
   - Return: created candidature object
   - Auth required, student only

3. PATCH /api/candidatures/{id} - update status
   - Accept: status, feedback, notes, interviewDate
   - Validate status transitions (applied→interview/rejected, interview→offer/rejected, etc.)
   - Auto-set: lastUpdated=now()
   - Auth required, company/admin only
   - Return: updated candidature
```

---

## 3️⃣ SAVED OPPORTUNITIES API

```
Implement REST API for saved opportunities:

1. GET /api/savedOpportunities - fetch saved items
   - Filter by studentId (query param)
   - Support pagination
   - Return JSON-LD hydra:Collection
   - Include nested opportunity object
   - Auth required, students see only own

2. POST /api/savedOpportunities - save opportunity
   - Accept: opportunityId, studentId, notes (optional)
   - Auto-set: savedDate=now()
   - IDEMPOTENT: if already saved, return existing with 200 OK (not 409)
   - Return: created or existing saved opportunity
   - Auth required

3. DELETE /api/savedOpportunities/{id} - remove saved
   - Return: 204 No Content
   - Auth required, student can only delete own
```

---

## 4️⃣ STATUS TRANSITIONS

```
Implement validation for candidature status transitions:

Valid paths:
- applied → interview OR rejected
- interview → offer OR rejected
- offer → accepted OR rejected
- accepted (terminal)
- rejected (terminal)

Add validation that throws error on invalid transition.
Add method to get possible next statuses.
Log all status changes (audit trail).
```

---

## 5️⃣ AUTHORIZATION

```
Add authorization rules:

GET /api/candidatures:
- Students see only their own
- Company sees their opportunities' applications
- Admins see all

POST /api/candidatures:
- Student must be authenticated
- Can only apply for themselves
- Student profile must be approved

PATCH /api/candidatures:
- Only company/admin can update status
- Company can only update their opportunities' applications
- Log who changed it

GET /api/savedOpportunities:
- Students see only their own

POST/DELETE /api/savedOpportunities:
- Student authenticated and owns the record
```

---

## 6️⃣ SEARCH & FILTERING

```
Add query parameters to GET endpoints:

GET /api/candidatures:
- ?studentId={id or IRI}
- ?status=applied|interview|offer|accepted|rejected
- ?opportunityId={id}
- ?createdAfter=2026-01-01T00:00:00Z
- ?createdBefore=2026-12-31T23:59:59Z
- ?sortBy=appliedDate|lastUpdated|score
- ?order=ASC|DESC
- ?page=1&limit=20

GET /api/savedOpportunities:
- ?studentId={id}
- ?savedAfter=2026-01-01T00:00:00Z
- ?search=keyword (searches in opportunity title/company)
- ?page=1&limit=20

Response must include totalItems, currentPage, totalPages
```

---

## 7️⃣ TESTS

```
Create tests for:

POST /api/candidatures:
- ✓ Create application successfully
- ✓ Prevent duplicate (409 or return existing)
- ✓ Validate required fields
- ✓ Auth required
- ✓ Unique constraint enforced

PATCH /api/candidatures:
- ✓ Update status with valid transition
- ✓ Reject invalid transition
- ✓ Only company/admin can update (403 for student)
- ✓ lastUpdated auto-set

GET /api/candidatures:
- ✓ Student sees only own
- ✓ Filter by status works
- ✓ Pagination works
- ✓ Nested objects included

POST /api/savedOpportunities:
- ✓ Save successfully
- ✓ Idempotent (save twice returns existing, 200 OK)
- ✓ Only student can see own

Add fixtures with sample data.
```

---

## 8️⃣ NOTIFICATIONS (Optional)

```
On status changes, send notifications:

Status → interview:
- Email student with interview details
- Include interviewDate
- In-app notification

Status → offer:
- Email student with offer
- Show feedback field

Status → accepted|rejected:
- Send confirmation email
- Update student's profile

Create GET /api/studentProfiles/{id}/notifications endpoint
```

---

## 9️⃣ OPPORTUNITY INTEGRATION

```
Update Opportunity entity:

Add relationships:
- One Opportunity has Many Candidatures

Add methods:
- getCandidatureCount(): int
- getCandidatureByStatus(status): int
- hasApplied(studentId): bool
- getApplicationsNotViewed()

Add optional fields to GET response:
- applicationCount: int
- myApplicationStatus: string|null (if student)

Ensure cascade delete when opportunity deleted
```

---

## 🔟 API DOCUMENTATION

```
Generate OpenAPI/Swagger spec and markdown docs:

Include:
- All 6 endpoints with methods
- Request/response schemas
- Authentication requirements
- All query parameters
- Error codes (400, 403, 404, 409)
- Example requests/responses
- Status transition diagram
- Authorization rules
- Frontend integration guide
- Troubleshooting section

Create interactive API docs (Swagger UI)
```

---

## QUICK START EXECUTION PLAN

**Phase 1 (Foundation):** Prompts 1, 2, 3

- Database ready
- APIs working

**Phase 2 (Quality):** Prompts 4, 5, 6

- Validation & security
- Filtering & search

**Phase 3 (Polish):** Prompts 7, 8, 9, 10

- Tests passing
- Documentation complete
- Notifications (optional)

**Estimated Timeline:** 2-3 days for experienced backend dev

---

## NOTES

- All timestamps should be ISO 8601 format (UTC)
- Use JSON-LD hydra:Collection format (consistent with your existing API)
- POST endpoints should return 201 Created, not 200 OK
- Keep appliedDate immutable (never update it)
- lastUpdated should auto-update on any PATCH
- Idempotency is CRITICAL for saved opportunities
- Log all status changes for audit trail
