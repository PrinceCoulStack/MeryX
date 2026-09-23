# Backend Implementation Prompts for Candidature Feature

Use these prompts with your backend agent to implement the opportunity and candidature tracking system.

---

## PROMPT 1: Database Schema Setup

```
Create the database schema for the candidature and saved opportunities tracking system.

Requirements:
1. Create "candidatures" table with columns:
   - id (BIGINT PRIMARY KEY AUTO_INCREMENT)
   - opportunity_id (BIGINT NOT NULL, foreign key to opportunities.id)
   - student_id (BIGINT NOT NULL, foreign key to student_profiles.id)
   - status (VARCHAR(50) NOT NULL DEFAULT 'applied') - enum values: applied, interview, offer, accepted, rejected
   - applied_date (DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)
   - last_updated (DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
   - feedback (TEXT nullable)
   - notes (TEXT nullable)
   - interview_date (DATETIME nullable)
   - score (INT DEFAULT 0)
   - created_at (DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)
   - updated_at (DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
   - Unique constraint on (opportunity_id, student_id)
   - Indexes on: student_id, status, created_at, (student_id, status)

2. Create "saved_opportunities" table with columns:
   - id (BIGINT PRIMARY KEY AUTO_INCREMENT)
   - opportunity_id (BIGINT NOT NULL, foreign key to opportunities.id)
   - student_id (BIGINT NOT NULL, foreign key to student_profiles.id)
   - notes (TEXT nullable)
   - saved_date (DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)
   - created_at (DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)
   - updated_at (DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
   - Unique constraint on (opportunity_id, student_id)
   - Indexes on: student_id, created_at

3. Ensure foreign keys cascade on delete
4. Use InnoDB engine
5. Create migration files for version control

After creating, provide SQL dump and migration file names.
```

---

## PROMPT 2: Candidature Entity & API Endpoint

```
Create a Candidature entity and REST API endpoint for managing student applications.

Requirements:

1. Create Candidature Entity/Model with properties:
   - id: integer
   - opportunityId: relation to Opportunity
   - studentId: relation to StudentProfile
   - status: enum (applied, interview, offer, accepted, rejected)
   - appliedDate: datetime
   - lastUpdated: datetime
   - feedback: string (nullable)
   - notes: string (nullable)
   - interviewDate: datetime (nullable)
   - score: integer (default 0)
   - createdAt: datetime (auto-set)
   - updatedAt: datetime (auto-set)

2. Implement GET /api/candidatures endpoint:
   - Return JSON-LD hydra:Collection format
   - Support query parameter: studentId (filter by student IRI like /api/studentProfiles/456)
   - Support pagination (page, limit parameters)
   - Include nested opportunity and student objects in response
   - Require authentication (Bearer token)
   - Authorization: students can only see their own, companies/admins see theirs

3. Implement POST /api/candidatures endpoint:
   - Accept JSON-LD format
   - Required fields: opportunityId, studentId
   - Optional fields: notes
   - Auto-set fields: status="applied", appliedDate=now(), lastUpdated=now()
   - Validate opportunityId and studentId are valid
   - Check for duplicate application (unique constraint)
   - Return 409 Conflict if already applied
   - Return created candidature in same format as GET
   - Require student authentication

4. Implement PATCH /api/candidatures/{id} endpoint:
   - Allow partial updates
   - Updateable fields: status, feedback, notes, interviewDate, score, lastUpdated
   - Validate status transitions (applied → interview/rejected, interview → offer/rejected, etc.)
   - Auto-update lastUpdated timestamp
   - Require company or admin authentication
   - Return updated candidature object

5. Error responses:
   - 400 Bad Request: Invalid IRI or missing required fields
   - 404 Not Found: Candidature not found
   - 409 Conflict: Already applied to this opportunity
   - 403 Forbidden: No permission to access/modify

Return the complete entity code and all three endpoint implementations.
```

---

## PROMPT 3: SavedOpportunity Entity & API Endpoint

```
Create a SavedOpportunity entity and REST API endpoints for students to bookmark opportunities.

Requirements:

1. Create SavedOpportunity Entity/Model with properties:
   - id: integer
   - opportunityId: relation to Opportunity
   - studentId: relation to StudentProfile
   - notes: string (nullable, max 500 chars)
   - savedDate: datetime
   - createdAt: datetime (auto-set)
   - updatedAt: datetime (auto-set)

2. Implement GET /api/savedOpportunities endpoint:
   - Return JSON-LD hydra:Collection format
   - Support query parameter: studentId (filter by student IRI)
   - Support pagination
   - Include nested opportunity and student objects
   - Require authentication
   - Authorization: students see only their own

3. Implement POST /api/savedOpportunities endpoint (IDEMPOTENT):
   - Accept JSON-LD format
   - Required fields: opportunityId, studentId
   - Optional fields: notes
   - Auto-set fields: savedDate=now()
   - Validate IDs are valid
   - IMPORTANT: If student already saved this opportunity, return existing record with 200 OK
   - Do NOT return 409 Conflict - operation should be idempotent
   - Return created or existing savedOpportunity object

4. Implement DELETE /api/savedOpportunities/{id} endpoint:
   - Remove the saved opportunity record
   - Return 204 No Content on success
   - Return 404 if not found
   - Require authentication and ownership

5. Error responses:
   - 400 Bad Request: Invalid data
   - 404 Not Found: Record not found
   - 403 Forbidden: No permission

Return the complete entity code and all three endpoint implementations.
Note: Idempotency is critical for the frontend save functionality.
```

---

## PROMPT 4: Opportunity Status Transitions & Business Logic

```
Implement validation logic for candidature status transitions in the Candidature entity/service.

Requirements:

1. Define valid status transitions:
   - "applied" can transition to:
     * "interview" (company schedules interview)
     * "rejected" (company rejects application)

   - "interview" can transition to:
     * "offer" (company makes offer)
     * "rejected" (company rejects after interview)

   - "offer" can transition to:
     * "accepted" (student accepts offer)
     * "rejected" (student declines offer)

   - "accepted" is terminal (no further transitions)
   - "rejected" is terminal (no further transitions)

2. When updating status to "interview":
   - interviewDate can be provided (optional)
   - feedback can be provided (optional)

3. When updating status to "offer":
   - feedback should be provided (indicate offer terms)
   - interviewDate can be cleared

4. When updating status to "accepted" or "rejected":
   - mark lastUpdated as now()
   - require feedback/notes explaining decision

5. Add validation method that:
   - Checks if transition is valid
   - Throws error if invalid transition attempted
   - Returns boolean if transition is allowed

6. Add method to get all possible next statuses for a candidature

7. Add audit logging for status changes (log who changed it and when)

Return validation methods and integration with PATCH endpoint.
```

---

## PROMPT 5: Authorization & Permissions

```
Implement authorization rules for the Candidature and SavedOpportunity endpoints.

Requirements:

1. GET /api/candidatures:
   - Students can only fetch their own candidatures (filter by their studentId)
   - Company representatives can fetch candidatures for their opportunities
   - Admins can fetch all candidatures
   - Use JWT token to identify role and user

2. POST /api/candidatures:
   - Student must be authenticated
   - Student can only create candidatures for themselves (studentId must match their profile)
   - Validate student profile is approved before allowing applications

3. PATCH /api/candidatures:
   - Only company representatives and admins can update status
   - Company can only update candidatures for their own opportunities
   - Student cannot modify status (read-only for students)
   - Audit log who made the change

4. DELETE /api/candidatures (if available):
   - Student can withdraw their own application
   - Admin can delete any candidature

5. GET /api/savedOpportunities:
   - Students can only see their own saved opportunities
   - Filter by studentId from JWT token

6. POST /api/savedOpportunities:
   - Student must be authenticated
   - Must save to their own studentId

7. DELETE /api/savedOpportunities:
   - Student can only delete their own
   - Company cannot see saved opportunities list

Implement using JWT token claims (role, studentId, companyId).
Return middleware/guards for each endpoint.
```

---

## PROMPT 6: Searching & Filtering

```
Implement advanced search and filtering for candidatures and saved opportunities.

Requirements:

1. For GET /api/candidatures, support query parameters:
   - studentId: filter by student IRI or ID
   - status: filter by single status (applied, interview, offer, etc.)
   - opportunityId: filter by specific opportunity
   - createdAfter: filter by creation date (ISO 8601)
   - createdBefore: filter by creation date
   - sortBy: sort by appliedDate, lastUpdated, or score
   - order: ASC or DESC

2. For GET /api/savedOpportunities, support:
   - studentId: filter by student
   - savedAfter: filter by saved date
   - savedBefore: filter by saved date
   - search: full-text search in opportunity title/company

3. Implement pagination with defaults:
   - Default page size: 20
   - Max page size: 100
   - Return totalItems, currentPage, totalPages in response

4. Index database columns for efficient filtering:
   - (student_id, status) composite index
   - created_at index
   - (student_id, created_at) composite index

5. Add endpoint documentation with example queries

Return query parameter documentation and implementation.
```

---

## PROMPT 7: Testing & Sample Data

```
Create unit and integration tests for the Candidature and SavedOpportunity endpoints.

Requirements:

1. Unit Tests for Candidature entity:
   - Test valid status transitions
   - Test invalid status transitions (throw errors)
   - Test field validations
   - Test automatic timestamp setting

2. Integration Tests for POST /api/candidatures:
   - Create candidature successfully
   - Prevent duplicate applications (should fail or return existing)
   - Validate required fields
   - Test authentication requirement

3. Integration Tests for PATCH /api/candidatures:
   - Update status with valid transition
   - Reject invalid status transition
   - Only admin/company can update status
   - Student cannot update status (403)

4. Integration Tests for GET /api/candidatures:
   - Student sees only their candidatures
   - Company sees their opportunities' candidatures
   - Filter by status works
   - Pagination works

5. Integration Tests for SavedOpportunity:
   - Save opportunity successfully
   - Idempotent save (saving twice returns existing)
   - Delete saved opportunity
   - Only student can see their saved

6. Create sample test data:
   - 3 sample students with profiles
   - 5 sample opportunities
   - Various candidatures with different statuses
   - Saved opportunities

Use standard testing framework (Jest, PHPUnit, pytest, etc. depending on backend).
Return test files and sample data fixtures.
```

---

## PROMPT 8: Status Change Notifications (Optional Enhancement)

```
Implement notification system when candidature status changes.

Requirements (Optional but recommended):

1. When status changes to "interview":
   - Send email to student with interview details
   - Send notification in-app
   - Include interviewDate if provided

2. When status changes to "offer":
   - Send email to student with offer details
   - Highlight key terms from feedback field

3. When status changes to "accepted" or "rejected":
   - Send confirmation email
   - Update student's status on profile if needed

4. Store notifications in database:
   - notification_id
   - student_id
   - candidature_id
   - notification_type (email, in_app, both)
   - subject
   - body
   - read_at (nullable)
   - created_at

5. Create GET /api/studentProfiles/{id}/notifications endpoint

Return notification service implementation and email templates.
```

---

## PROMPT 9: Integration with Opportunity Model

```
Ensure the Opportunity model integrates properly with Candidatures.

Requirements:

1. Add relationship in Opportunity entity:
   - One Opportunity has Many Candidatures
   - Add candidatures property/collection

2. Add method to Opportunity:
   - getCandidatureCount() - total applications
   - getCandidatureByStatus(status) - count by status
   - hasApplied(studentId) - boolean check
   - getApplicationsNotViewed() - for company dashboard

3. Add to Opportunity GET response (optional fields):
   - applicationCount: integer
   - myApplicationStatus: string or null (if student viewing their own)

4. Ensure cascade delete:
   - When opportunity is deleted, delete all related candidatures
   - When student profile is deleted, delete all their candidatures

5. Add denormalized field to track most recent status:
   - latestCandidatureStatus
   - latestCandidateUpdate

Return updated Opportunity entity code.
```

---

## PROMPT 10: Documentation & API Reference

```
Generate complete API documentation for the Candidature and SavedOpportunity endpoints.

Requirements:

1. OpenAPI/Swagger specification:
   - All endpoints (GET, POST, PATCH, DELETE)
   - Request/response schemas
   - Authentication requirements
   - Error codes and examples

2. Human-readable documentation:
   - Endpoint descriptions
   - Query parameter reference
   - Example requests and responses
   - Authorization rules
   - Error scenarios

3. Status transition diagram:
   - ASCII or visual diagram of valid transitions
   - Examples of each transition

4. Integration guide for frontend:
   - How to create candidature
   - How to update status
   - How to handle errors
   - Polling/WebSocket for real-time updates (if applicable)

5. Rate limiting documentation:
   - Max requests per minute per endpoint
   - Quota for bulk operations

6. Troubleshooting guide:
   - Common error scenarios
   - Solutions

Return Swagger/OpenAPI file and markdown documentation.
```

---

## How to Use These Prompts

1. **Start with Prompt 1** (Database Schema) - establish data structure
2. **Then Prompts 2-3** (APIs) - implement the main endpoints
3. **Follow with Prompts 4-5** (Business Logic & Auth) - add validation and security
4. **Add Prompt 6** (Filtering) - enhance query capabilities
5. **Use Prompt 7** (Tests) - ensure quality
6. **Optional: Prompts 8-9** (Notifications & Integration) - polish
7. **End with Prompt 10** (Documentation) - communicate the API

Each prompt is independent and can be modified based on your tech stack (Node/Express, PHP/Laravel, Python/Django, Java/Spring, etc.).
