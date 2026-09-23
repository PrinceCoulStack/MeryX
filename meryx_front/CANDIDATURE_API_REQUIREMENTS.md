# Candidature Feature - Backend API Requirements

## Overview

This document specifies the API endpoints and data structures required for the candidature (application tracking) feature.

## API Endpoints Required

### 1. Candidatures / Applications

Tracks student applications to opportunities.

#### GET /api/candidatures

Fetch list of candidatures (applications).

**Query Parameters:**

- `studentId` (optional): Filter by student IRI `/api/studentProfiles/{id}`
- Standard pagination parameters (page, limit, etc.)

**Response Format:**

```json
{
  "@context": "/api/contexts/Candidature",
  "@type": "hydra:Collection",
  "@id": "/api/candidatures",
  "hydra:member": [
    {
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
      "lastUpdated": "2026-01-20T14:30:00Z",
      "feedback": null,
      "notes": null,
      "interviewDate": null,
      "score": 0,
      "createdAt": "2026-01-15T10:00:00Z",
      "updatedAt": "2026-01-20T14:30:00Z"
    }
  ],
  "hydra:totalItems": 1,
  "hydra:view": {
    "@id": "/api/candidatures?page=1",
    "@type": "hydra:PartialCollectionView"
  }
}
```

#### POST /api/candidatures

Create a new candidature (application).

**Request Body:**

```json
{
  "opportunityId": "/api/opportunities/123",
  "studentId": "/api/studentProfiles/456",
  "status": "applied",
  "notes": "Interested in this role",
  "appliedDate": "2026-01-15T10:00:00Z"
}
```

**Response:** Returns created candidature object (same as GET detail)

**Validation:**

- `opportunityId` must be valid IRI or ID
- `studentId` must be valid IRI or ID
- `status` should default to "applied" if not provided
- `appliedDate` should default to now() if not provided

#### PATCH /api/candidatures/{id}

Update candidature status (typically done by company/admin).

**Request Body (partial update):**

```json
{
  "status": "interview",
  "feedback": "Great technical skills",
  "lastUpdated": "2026-01-20T14:30:00Z",
  "interviewDate": "2026-01-25T14:00:00Z"
}
```

**Valid Status Values:**

- `applied` - Initial application
- `interview` - Company wants to interview
- `offer` - Job offer extended
- `accepted` - Student accepted offer
- `rejected` - Application rejected

**Response:** Returns updated candidature object

#### DELETE /api/candidatures/{id}

Remove an application (soft delete or hard delete based on business rules).

**Response:** 204 No Content

---

### 2. Saved Opportunities

Tracks opportunities bookmarked by students for later review.

#### GET /api/savedOpportunities

Fetch list of saved opportunities.

**Query Parameters:**

- `studentId` (optional): Filter by student IRI `/api/studentProfiles/{id}`
- Standard pagination parameters

**Response Format:**

```json
{
  "@context": "/api/contexts/SavedOpportunity",
  "@type": "hydra:Collection",
  "@id": "/api/savedOpportunities",
  "hydra:member": [
    {
      "@type": "SavedOpportunity",
      "@id": "/api/savedOpportunities/1",
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
      "notes": "Interesting role, good salary",
      "savedDate": "2026-01-15T10:00:00Z",
      "createdAt": "2026-01-15T10:00:00Z",
      "updatedAt": "2026-01-15T10:00:00Z"
    }
  ],
  "hydra:totalItems": 1,
  "hydra:view": {
    "@id": "/api/savedOpportunities?page=1",
    "@type": "hydra:PartialCollectionView"
  }
}
```

#### POST /api/savedOpportunities

Save an opportunity for later review.

**Request Body:**

```json
{
  "opportunityId": "/api/opportunities/123",
  "studentId": "/api/studentProfiles/456",
  "notes": "Interested, might apply later",
  "savedDate": "2026-01-15T10:00:00Z"
}
```

**Response:** Returns created saved opportunity object

**Validation:**

- Both `opportunityId` and `studentId` must be valid
- Unique constraint: student cannot save same opportunity twice
- Return existing record if already saved (idempotent operation preferred)

#### DELETE /api/savedOpportunities/{id}

Remove a saved opportunity.

**Response:** 204 No Content

---

## Field Requirements

### Candidature Entity

| Field         | Type        | Required | Notes                                               |
| ------------- | ----------- | -------- | --------------------------------------------------- |
| id            | Integer     | Yes      | Auto-generated                                      |
| opportunityId | IRI/Integer | Yes      | Reference to Opportunity                            |
| studentId     | IRI/Integer | Yes      | Reference to StudentProfile                         |
| status        | String      | Yes      | Enum: applied, interview, offer, accepted, rejected |
| appliedDate   | DateTime    | Yes      | When application was submitted                      |
| lastUpdated   | DateTime    | Yes      | Last status change timestamp                        |
| feedback      | String      | No       | Company feedback on application                     |
| notes         | String      | No       | Internal notes/comments                             |
| interviewDate | DateTime    | No       | Scheduled interview date                            |
| score         | Integer     | No       | Application evaluation score                        |
| createdAt     | DateTime    | Yes      | Record creation timestamp                           |
| updatedAt     | DateTime    | Yes      | Record update timestamp                             |

### SavedOpportunity Entity

| Field         | Type        | Required | Notes                       |
| ------------- | ----------- | -------- | --------------------------- |
| id            | Integer     | Yes      | Auto-generated              |
| opportunityId | IRI/Integer | Yes      | Reference to Opportunity    |
| studentId     | IRI/Integer | Yes      | Reference to StudentProfile |
| notes         | String      | No       | Student's personal notes    |
| savedDate     | DateTime    | Yes      | When saved                  |
| createdAt     | DateTime    | Yes      | Record creation timestamp   |
| updatedAt     | DateTime    | Yes      | Record update timestamp     |

---

## Data Relationships

```
StudentProfile (1) ──────┬──────── (M) Candidature
                         │
                         └──────── (M) SavedOpportunity

Opportunity (1) ──────┬──────── (M) Candidature
                      │
                      └──────── (M) SavedOpportunity
```

---

## Authorization & Permissions

### Candidatures

- **Read**: Student can read their own; Company/Admin can read theirs
- **Create**: Student creates their own
- **Update**: Company/Admin updates status (student cannot modify status)
- **Delete**: Student can withdraw application (soft delete)

### SavedOpportunities

- **Read**: Student can read their own
- **Create**: Student creates their own
- **Delete**: Student can remove

---

## Implementation Notes

### Idempotency

- `POST /api/savedOpportunities` should be idempotent
  - If student saves same opportunity twice, return existing record
  - Return 200 with existing record instead of 409 Conflict

### Status Transitions

Valid transitions:

```
applied → interview
applied → rejected
interview → offer
interview → rejected
offer → accepted
offer → rejected
```

### Timestamps

- Use ISO 8601 format for all datetime fields
- Store in UTC timezone
- Frontend will handle locale-specific formatting

### Nested Resource Loading

- Include `opportunity` and `student` objects in responses for convenience
- Allows frontend to display full details without additional API calls

### Filtering Support

- Support `studentId` query parameter for filtering
- Should accept both IRI format and plain integer IDs
- Example: `GET /api/candidatures?studentId=/api/studentProfiles/456`
- Also support: `GET /api/candidatures?studentId=456`

---

## Error Responses

### 400 Bad Request

```json
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "opportunityId must be a valid IRI",
  "trace": []
}
```

### 404 Not Found

```json
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "Not Found",
  "hydra:description": "Candidature not found"
}
```

### 409 Conflict

```json
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "Conflict",
  "hydra:description": "This student has already applied to this opportunity"
}
```

---

## Migration/Setup

### Database Schema

```sql
-- Candidatures table
CREATE TABLE candidatures (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  opportunity_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'applied',
  applied_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  feedback TEXT,
  notes TEXT,
  interview_date DATETIME,
  score INT DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
  UNIQUE KEY unique_application (opportunity_id, student_id),
  INDEX idx_student (student_id),
  INDEX idx_status (status),
  INDEX idx_created (created_at)
);

-- SavedOpportunities table
CREATE TABLE saved_opportunities (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  opportunity_id BIGINT NOT NULL,
  student_id BIGINT NOT NULL,
  notes TEXT,
  saved_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
  UNIQUE KEY unique_saved (opportunity_id, student_id),
  INDEX idx_student (student_id),
  INDEX idx_created (created_at)
);
```

---

## Testing Scenarios

### Candidature Tests

1. Create application to opportunity
2. Update application to interview status
3. Prevent duplicate applications
4. Student cannot update status (only company can)
5. Fetch student's applications
6. Delete/withdraw application
7. Test status transition validation

### SavedOpportunity Tests

1. Save opportunity (first time)
2. Save same opportunity again (verify idempotency)
3. Remove saved opportunity
4. Fetch student's saved opportunities
5. Verify uniqueness constraint

---

## Performance Considerations

### Indexes

- `(student_id, status)` for filtering
- `(opportunity_id, student_id)` for uniqueness
- `created_at` for timeline queries

### Pagination

- Use cursor-based pagination for large result sets
- Default page size: 20 items
- Max page size: 100 items

### Caching

- Cache opportunity details for 1 hour
- Invalidate on opportunity update
- No caching for user-specific candidature data
