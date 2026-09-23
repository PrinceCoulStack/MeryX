<template>
  <div class="candidate-profile-modal-overlay" @click.self="$emit('close')">
    <div class="candidate-profile-modal">
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="candidate-header-info">
          <div class="avatar">
            <i class="bi bi-person-circle"></i>
          </div>
          <div>
            <h2>{{ application.student?.fullName || 'Unknown' }}</h2>
            <p>{{ application.student?.program || 'Program not specified' }}</p>
          </div>
        </div>
        <button class="close-btn" type="button" @click="$emit('close')">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <!-- Modal Content -->
      <div class="modal-body">
        <!-- Match Score & Status -->
        <div class="application-overview">
          <div class="overview-item">
            <span class="label">Profile Match</span>
            <div class="match-bar">
              <div class="match-fill" :style="{ width: (application.matchScore || 0) + '%' }"></div>
            </div>
            <span class="value">{{ application.matchScore || 0 }}%</span>
          </div>

          <div class="overview-item">
            <span class="label">Application Status</span>
            <span class="badge" :class="statusClass(application.status)">
              {{ formatApplicationStatus(application.status) }}
            </span>
          </div>

          <div class="overview-item">
            <span class="label">Applied Date</span>
            <span class="value">{{ formatDate(application.createdAt) }}</span>
          </div>

          <div class="overview-item">
            <span class="label">For Opportunity</span>
            <span class="value">{{ opportunity?.title || 'Unknown' }}</span>
          </div>
        </div>

        <!-- Application Message -->
        <div v-if="application.notes" class="section">
          <h3>Application Message</h3>
          <div class="application-message">
            <p>{{ application.notes }}</p>
          </div>
        </div>

        <!-- Student Profile Information -->
        <div class="section">
          <h3>Academic Profile</h3>
          <div class="profile-grid">
            <div class="profile-item">
              <span class="label">Program</span>
              <span class="value">{{ application.student?.program || '—' }}</span>
            </div>

            <div class="profile-item">
              <span class="label">Academic Level</span>
              <span class="value">{{ application.student?.level || '—' }}</span>
            </div>

            <div class="profile-item">
              <span class="label">GPA</span>
              <span class="value">{{ application.student?.gpa || '—' }}/4.0</span>
            </div>

            <div class="profile-item">
              <span class="label">Status</span>
              <span class="value">{{ application.student?.status || '—' }}</span>
            </div>

            <div class="profile-item">
              <span class="label">Email</span>
              <span class="value">{{ application.student?.email || '—' }}</span>
            </div>

            <div class="profile-item">
              <span class="label">Phone</span>
              <span class="value">{{ application.student?.phone || '—' }}</span>
            </div>
          </div>
        </div>

        <!-- Skills -->
        <div v-if="application.student?.skills?.length" class="section">
          <h3>Skills & Languages</h3>
          <div class="skills-container">
            <span v-for="skill in application.student.skills" :key="skill" class="skill-badge">
              {{ skill }}
            </span>
          </div>
        </div>

        <!-- Requirements Match -->
        <div v-if="opportunity?.requirements?.length" class="section">
          <h3>Requirement Matching</h3>
          <div class="requirements-list">
            <div
              v-for="(requirement, index) in opportunity.requirements"
              :key="index"
              class="requirement-item"
            >
              <i :class="hasSkill(requirement) ? 'bi bi-check-circle-fill' : 'bi bi-circle'"></i>
              <span>{{ requirement }}</span>
            </div>
          </div>
        </div>

        <!-- Summary Section -->
        <div v-if="application.student?.summary" class="section">
          <h3>About</h3>
          <p class="summary">{{ application.student.summary }}</p>
        </div>
      </div>

      <!-- Modal Footer with Actions -->
      <div class="modal-footer">
        <button class="secondary-btn" type="button" @click="$emit('close')">Close</button>

        <div class="action-buttons">
          <button
            v-if="application.status === 'applied'"
            class="primary-btn"
            type="button"
            @click="$emit('action', 'interview')"
          >
            <i class="bi bi-calendar-check"></i>
            Schedule Interview
          </button>

          <button
            v-if="['applied', 'interview'].includes(application.status)"
            class="success-btn"
            type="button"
            @click="$emit('action', 'offer')"
          >
            <i class="bi bi-star"></i>
            Send Offer
          </button>

          <button
            v-if="application.status === 'offer'"
            class="success-btn"
            type="button"
            @click="$emit('action', 'accepted')"
          >
            <i class="bi bi-check-lg"></i>
            Mark as Accepted
          </button>

          <button
            v-if="application.status !== 'rejected' && application.status !== 'accepted'"
            class="danger-btn"
            type="button"
            @click="$emit('action', 'rejected')"
          >
            <i class="bi bi-x-lg"></i>
            Reject
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
  opportunity: {
    type: Object,
    default: null,
  },
  language: {
    type: String,
    default: 'en',
  },
})

defineEmits(['close', 'action'])

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString(props.language === 'en' ? 'en-GB' : 'fr-FR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const formatApplicationStatus = (status) => {
  const map = {
    applied: 'Applied',
    interview: 'Interview Scheduled',
    offer: 'Offer Extended',
    accepted: 'Accepted',
    rejected: 'Rejected',
  }
  return map[status] || 'Unknown'
}

const statusClass = (status) => {
  if (status === 'rejected') return 'rejected'
  if (status === 'accepted') return 'accepted'
  if (status === 'offer') return 'offer'
  if (status === 'interview') return 'interview'
  return 'applied'
}

const hasSkill = (requirement) => {
  if (!requirement || !Array.isArray(props.application?.student?.skills)) return false
  return props.application.student.skills.some(
    (s) =>
      s.toLowerCase().includes(requirement.toLowerCase()) ||
      requirement.toLowerCase().includes(s.toLowerCase()),
  )
}
</script>

<style scoped>
.candidate-profile-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  overflow-y: auto;
}

.candidate-profile-modal {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  color: #333;
  transition: all 0.3s;
}

html[data-theme='dark'] .candidate-profile-modal {
  background: #2a2a2a;
  color: #e0e0e0;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding: 24px;
  border-bottom: 1px solid #e0e0e0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

html[data-theme='dark'] .modal-header {
  border-bottom-color: #444;
}

.candidate-header-info {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  flex-shrink: 0;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

html[data-theme='dark'] .application-overview {
  background: #1a1a1a;
}

.application-overview {
  background: #f5f5f5;
  padding: 16px;
  border-radius: 8px;
  display: grid;
  gap: 12px;
  margin-bottom: 24px;
}

.overview-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.overview-item .label {
  font-weight: 600;
  color: #666;
  font-size: 13px;
  min-width: 120px;
}

html[data-theme='dark'] .overview-item .label {
  color: #aaa;
}

.section {
  margin-bottom: 24px;
}

.section h3 {
  margin: 0 0 12px;
  font-size: 15px;
  font-weight: 600;
  color: #333;
}

html[data-theme='dark'] .section h3 {
  color: #ddd;
}

.application-message {
  background: #f9f9f9;
  padding: 12px;
  border-radius: 6px;
  border-left: 3px solid #667eea;
}

html[data-theme='dark'] .application-message {
  background: #1a1a1a;
  border-left-color: #64b5f6;
}

.application-message p {
  margin: 0;
  font-size: 13px;
  line-height: 1.5;
  color: #555;
}

html[data-theme='dark'] .application-message p {
  color: #bbb;
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.profile-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 12px;
  background: #f5f5f5;
  border-radius: 6px;
}

html[data-theme='dark'] .profile-item {
  background: #1a1a1a;
}

.profile-item .label {
  font-size: 12px;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

html[data-theme='dark'] .profile-item .label {
  color: #aaa;
}

.profile-item .value {
  font-size: 14px;
  color: #333;
}

html[data-theme='dark'] .profile-item .value {
  color: #ddd;
}

.skills-container {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.skill-badge {
  display: inline-block;
  padding: 6px 12px;
  background: #e0e7ff;
  color: #3f51b5;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

html[data-theme='dark'] .skill-badge {
  background: #3f51b5;
  color: #e0e7ff;
}

.requirements-list {
  display: grid;
  gap: 8px;
}

.requirement-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #f5f5f5;
  border-radius: 6px;
  font-size: 13px;
}

html[data-theme='dark'] .requirement-item {
  background: #1a1a1a;
}

.requirement-item i {
  font-size: 16px;
  color: #667eea;
}

html[data-theme='dark'] .requirement-item i {
  color: #64b5f6;
}

.summary {
  margin: 0;
  font-size: 13px;
  line-height: 1.6;
  color: #555;
}

html[data-theme='dark'] .summary {
  color: #bbb;
}

.modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #e0e0e0;
  background: #fafafa;
  flex-wrap: wrap;
}

html[data-theme='dark'] .modal-footer {
  background: #333;
  border-top-color: #444;
}

.secondary-btn,
.primary-btn,
.success-btn,
.danger-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.secondary-btn {
  background: #f0f0f0;
  color: #333;
}

html[data-theme='dark'] .secondary-btn {
  background: #444;
  color: #e0e0e0;
}

.secondary-btn:hover {
  background: #e0e0e0;
}

html[data-theme='dark'] .secondary-btn:hover {
  background: #555;
}

.primary-btn {
  background: #667eea;
  color: white;
}

.primary-btn:hover {
  background: #5568d3;
}

.success-btn {
  background: #4caf50;
  color: white;
}

.success-btn:hover {
  background: #388e3c;
}

.danger-btn {
  background: #f44336;
  color: white;
}

.danger-btn:hover {
  background: #d32f2f;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

@media (max-width: 600px) {
  .candidate-profile-modal {
    max-width: 100%;
  }

  .modal-header {
    flex-direction: column;
  }

  .profile-grid {
    grid-template-columns: 1fr;
  }

  .modal-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .secondary-btn,
  .primary-btn,
  .success-btn,
  .danger-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
