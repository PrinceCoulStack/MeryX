# Complete Backend Implementation - Single Comprehensive Prompt

Copy this entire prompt and paste it to your backend agent for complete implementation.

---

````
I need you to implement a complete candidature (job application) tracking system for our student
opportunity platform. This will handle students applying to opportunities, saving opportunities, and
tracking the progression of their applications.

## DATABASE SCHEMA

Create two new database tables:

### 1. candidatures table
Columns:
- id: BIGINT PRIMARY KEY AUTO_INCREMENT
- opportunity_id: BIGINT NOT NULL (foreign key to opportunities.id, cascade delete)
- student_id: BIGINT NOT NULL (foreign key to student_profiles.id, cascade delete)
- status: VARCHAR(50) NOT NULL DEFAULT 'applied'
  (enum values: applied, interview, offer, accepted, rejected)
- applied_date: DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- last_updated: DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- feedback: TEXT (nullable)
- notes: TEXT (nullable)
- interview_date: DATETIME (nullable)
- score: INT DEFAULT 0
- created_at: DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- updated_at: DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Constraints:
- UNIQUE KEY unique_application (opportunity_id, student_id) - prevent duplicate applications
- INDEX idx_student_id (student_id)
- INDEX idx_status (status)
- INDEX idx_created_at (created_at)
- INDEX idx_student_status (student_id, status)

### 2. saved_opportunities table
Columns:
- id: BIGINT PRIMARY KEY AUTO_INCREMENT
- opportunity_id: BIGINT NOT NULL (foreign key to opportunities.id, cascade delete)
- student_id: BIGINT NOT NULL (foreign key to student_profiles.id, cascade delete)
- notes: TEXT (nullable)
- saved_date: DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- created_at: DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- updated_at: DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

Constraints:
- UNIQUE KEY unique_saved (opportunity_id, student_id) - prevent duplicate saves
- INDEX idx_student_id (student_id)
- INDEX idx_created_at (created_at)

Use InnoDB engine with cascade on delete.

## ENTITIES/MODELS

Create Candidature entity with:
- Properties: id, opportunityId (relation), studentId (relation), status, appliedDate,
  lastUpdated, feedback, notes, interviewDate, score, createdAt, updatedAt
- Getter methods to access nested opportunity and student data

Create SavedOpportunity entity with:
- Properties: id, opportunityId (relation), studentId (relation), notes, savedDate,
  createdAt, updatedAt
- Getter methods for nested opportunity data

## REST API ENDPOINTS

### Candidatures Endpoints

**GET /api/candidatures**
- Fetch student applications (candidatures)
- Query parameters:
  * studentId (optional): filter by student IRI or ID like /api/studentProfiles/456 or 456
  * status (optional): filter by single status value
  * opportunityId (optional): filter by opportunity
  * createdAfter (optional): ISO 8601 date
  * createdBefore (optional): ISO 8601 date
  * sortBy (optional): appliedDate, lastUpdated, or score
  * order (optional): ASC or DESC
  * page (optional): default 1
  * limit (optional): default 20, max 100
- Response format: JSON-LD hydra:Collection
- Include nested opportunity and student objects in each item
- Authorization: JWT required
  * Students can only fetch their own candidatures
  * Company reps see candidatures for their opportunities
  * Admins see all
- Return 200 with collection

**POST /api/candidatures**
- Create new candidature (student applies to opportunity)
- Request body: { "opportunityId": "/api/opportunities/123", "studentId": "/api/studentProfiles/456",
  "notes": "optional notes" }
- Validation:
  * Both opportunityId and studentId must be valid (check they exist)
  * Unique constraint: if student already applied, return 409 Conflict
  * Student profile must be approved/active
  * Opportunity must be open/active
- Auto-set fields: status="applied", appliedDate=now(), lastUpdated=now()
- Authorization: JWT required, student must create for themselves only
- Response: 201 Created with full candidature object (JSON-LD)
- Error codes: 400 (invalid data), 409 (duplicate application), 403 (unauthorized)

**PATCH /api/candidatures/{id}**
- Update candidature status (company/admin only)
- Request body: { "status": "interview", "feedback": "optional feedback",
  "notes": "optional", "interviewDate": "2026-01-25T14:00:00Z" }
- Validation:
  * Validate status transition is allowed:
    - applied → interview OR rejected
    - interview → offer OR rejected
    - offer → accepted OR rejected
    - accepted and rejected are terminal (no further transitions)
  * Reject invalid transitions with 400 Bad Request
  * If transitioning to "interview", interviewDate is optional but can be set
  * If transitioning to "offer" or higher, feedback should contain offer details
- Auto-set: lastUpdated=now()
- Authorization: Only company/admin can update, not students
  * Company can only update candidatures for their own opportunities
  * Log who made the change (audit trail)
- Response: 200 OK with updated candidature
- Error codes: 400 (invalid transition), 404 (not found), 403 (unauthorized)

**DELETE /api/candidatures/{id}** (Optional)
- Allow student to withdraw application
- Authorization: student can only delete own candidatures
- Response: 204 No Content
- Error codes: 404 (not found), 403 (unauthorized)

### SavedOpportunity Endpoints

**GET /api/savedOpportunities**
- Fetch student's saved opportunities
- Query parameters:
  * studentId (optional): filter by student
  * savedAfter (optional): ISO 8601 date
  * savedBefore (optional): ISO 8601 date
  * search (optional): full-text search in opportunity title/company
  * page (optional): default 1
  * limit (optional): default 20, max 100
- Response format: JSON-LD hydra:Collection
- Include nested opportunity object
- Authorization: JWT required, students see only own
- Return 200 with collection

**POST /api/savedOpportunities**
- Save opportunity for later review
- Request body: { "opportunityId": "/api/opportunities/123",
  "studentId": "/api/studentProfiles/456", "notes": "optional personal notes" }
- Validation: both IDs must be valid
- **CRITICAL: MAKE THIS IDEMPOTENT**
  * If student already saved this opportunity, return 200 OK with existing record
  * Do NOT return 409 Conflict
  * This is important for frontend save functionality
- Auto-set fields: savedDate=now()
- Authorization: JWT required, student creates for themselves
- Response: 201 Created (first time) or 200 OK (already exists)
- Error codes: 400 (invalid data), 403 (unauthorized)

**DELETE /api/savedOpportunities/{id}**
- Remove saved opportunity
- Authorization: student can only delete own
- Response: 204 No Content
- Error codes: 404 (not found), 403 (unauthorized)

## BUSINESS LOGIC & VALIDATION

1. Status Transitions
   - Implement validation method that checks allowed transitions
   - Create method that returns list of valid next statuses
   - Add status transition diagram in code documentation

2. Duplicate Prevention
   - Candidatures: unique constraint on (opportunity_id, student_id)
   - SavedOpportunities: unique constraint, but return existing on duplicate (idempotent)

3. Audit Trail
   - Log all status changes with timestamp and user ID
   - Store in separate table or in candidature_changelog table:
     * id, candidature_id, old_status, new_status, changed_by_user_id, changed_at

4. Timestamp Management
   - appliedDate: set once on creation, never update
   - lastUpdated: auto-set on any PATCH operation
   - All timestamps in ISO 8601 UTC format

## AUTHORIZATION & SECURITY

1. Authentication
   - All endpoints require valid JWT token (Bearer token in Authorization header)
   - Extract role and user ID from JWT claims

2. Authorization by Role

   **Students:**
   - Can GET only their own candidatures
   - Can POST new candidatures (apply) for themselves
   - Can see only their own saved opportunities
   - Can DELETE their own saved opportunities
   - Cannot update candidature status (read-only)
   - Cannot delete candidatures (can withdraw but soft-delete)

   **Company Representatives:**
   - Can GET candidatures for their own opportunities only
   - Can PATCH candidatures for their opportunities (change status)
   - Cannot see saved opportunities
   - Cannot modify student records

   **Admins:**
   - Can GET all candidatures
   - Can PATCH any candidature status
   - Can DELETE any candidatures
   - Full access

3. Field-Level Authorization
   - Students: cannot modify status field
   - Company/Admin: can modify feedback, notes, interviewDate, score
   - appliedDate: immutable for all

## ERROR HANDLING

Return proper HTTP status codes and JSON-LD error format:

```json
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "Error Title",
  "hydra:description": "Detailed error message"
}
````

Error codes to implement:

- 200 OK: successful GET or existing record returned
- 201 Created: successful POST
- 204 No Content: successful DELETE
- 400 Bad Request: invalid input data, invalid status transition
- 401 Unauthorized: missing or invalid JWT
- 403 Forbidden: authenticated but no permission
- 404 Not Found: resource not found
- 409 Conflict: duplicate candidature application (not for saved opportunities - they are idempotent)

## RESPONSE FORMAT

All responses must follow JSON-LD + Hydra format used in your existing API:

Single resource:

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
    "title": "Senior Developer",
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

## OPTIONAL ENHANCEMENTS

1. Email Notifications (recommended)
   - When status changes to "interview": email student with interview details
   - When status changes to "offer": email student with offer
   - When status changes to "accepted/rejected": send confirmation

2. Real-time Updates (WebSocket optional)
   - Company can see new applications in real-time
   - Student can see status updates immediately

3. Analytics
   - Add methods to calculate:
     - Total applications per opportunity
     - Application success rate by status
     - Average time from applied to offer

4. Integrate with Opportunity
   - Add relationship: Opportunity hasMany Candidatures
   - Add Opportunity.getCandidatureCount()
   - Add Opportunity.myApplicationStatus() for student viewing

## TESTING REQUIREMENTS

Include comprehensive tests:

1. Create application:
   - ✓ Create successfully with valid data
   - ✓ Prevent duplicate (409 response)
   - ✓ Auto-set status, appliedDate, lastUpdated
   - ✓ Require authentication
   - ✓ Student can only apply for themselves

2. Update status:
   - ✓ Update with valid transition succeeds
   - ✓ Reject invalid transitions (400 Bad Request)
   - ✓ Student cannot update status (403)
   - ✓ Company can only update own opportunities
   - ✓ Admin can update any

3. Get candidatures:
   - ✓ Student sees only own
   - ✓ Company sees only own opportunities'
   - ✓ Filters work (by status, etc.)
   - ✓ Pagination works
   - ✓ Nested objects included

4. Save opportunity:
   - ✓ Save successfully
   - ✓ Idempotent (save twice, get same record, 200 OK)
   - ✓ Student can only save for themselves
   - ✓ Student can only see own saved

5. Authorization:
   - ✓ All endpoints require auth
   - ✓ Role-based access enforced
   - ✓ Users cannot access others' data

Create fixtures with:

- 3 students with approved profiles
- 5 opportunities (some open, some closed)
- 8 candidatures with various statuses
- Saved opportunities from different students

## DOCUMENTATION

Generate and include:

- OpenAPI/Swagger specification for all endpoints
- Human-readable markdown documentation
- Integration guide for frontend developers
- Example requests and responses for each endpoint
- Authorization/permission matrix table
- Status transition diagram

## DELIVERABLES

1. Database migration files (with rollback)
2. Entity/Model classes for Candidature and SavedOpportunity
3. All 6 API endpoints implemented
4. Validation and authorization middleware
5. Comprehensive test suite (>80% coverage)
6. API documentation (OpenAPI spec + markdown)
7. Sample data/fixtures for testing
8. Audit logging implementation

Please implement this complete system. Start with database schema, then entities, then endpoints,
then tests. Let me know if you have any questions about requirements.

````

---

## END OF MEGA-PROMPT

Simply copy the entire code block above (between the ``` markers) and paste it directly into your backend agent.

## Why This Prompt Works

✅ **Complete** - Covers all database, entities, endpoints, auth, testing
✅ **Specific** - Exact field names, types, relationships
✅ **Structured** - Clear sections guide implementation order
✅ **Example-Based** - Shows JSON formats, error responses
✅ **Validation Rules** - Specific what to validate
✅ **Authorization** - Clear who can do what
✅ **Edge Cases** - Handles duplicates, idempotency, transitions
✅ **Testing Guide** - What tests to write
✅ **Documentation** - What to document

## Alternative: Use Section by Section

If your backend agent has token limits, you can send it section by section:
1. First: DATABASE SCHEMA
2. Then: ENTITIES/MODELS
3. Then: REST API ENDPOINTS (split into subsections)
4. Then: BUSINESS LOGIC, AUTHORIZATION, ERROR HANDLING
5. Finally: TESTING and DOCUMENTATION

Each section is self-contained and can stand alone.
````
