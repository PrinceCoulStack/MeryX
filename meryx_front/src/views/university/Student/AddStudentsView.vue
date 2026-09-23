<template>
  <div class="student-workspace-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Student List & Search</p>
        <h1>Student management workspace</h1>
        <p class="page-copy">
          Search, select, and manage student profiles, academic records, class progression, and
          registration from one workspace.
        </p>
      </div>

      <div class="header-actions">
        <button class="ghost-btn" type="button" @click="toggleSelector">
          <i class="bi bi-search"></i>
          Search students
        </button>
        <button class="primary-btn" type="button" @click="wizardStep = 1">
          <i class="bi bi-plus-lg"></i>
          Add New Student
        </button>
      </div>
    </header>

    <div class="workspace-layout">
      <aside class="student-selector" :class="{ open: selectorOpen }">
        <div class="selector-header">
          <div>
            <h2>Students</h2>
            <p>Select a student to manage</p>
          </div>
          <button class="icon-btn mobile-only" type="button" @click="toggleSelector">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="search-block">
          <div class="search-input">
            <i class="bi bi-search"></i>
            <input
              v-model="searchQuery"
              type="search"
              placeholder="Search by name, student ID, email..."
            />
          </div>
          <button class="filter-btn" type="button" @click="showFilters = !showFilters">
            <i class="bi bi-funnel"></i>
            Filters
          </button>
        </div>

        <div v-show="showFilters" class="filters-panel">
          <div class="filter-grid">
            <label>
              <span>Gender</span>
              <select v-model="filters.gender">
                <option value="all">All</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </label>

            <label>
              <span>Faculty</span>
              <select v-model="filters.faculty">
                <option value="all">All</option>
                <option v-for="item in facultyOptions" :key="item" :value="item">
                  {{ item }}
                </option>
              </select>
            </label>

            <label>
              <span>Department</span>
              <select v-model="filters.department">
                <option value="all">All</option>
                <option v-for="item in departmentOptions" :key="item" :value="item">
                  {{ item }}
                </option>
              </select>
            </label>

            <label>
              <span>Programme / Class</span>
              <select v-model="filters.program">
                <option value="all">All</option>
                <option v-for="item in programOptions" :key="item" :value="item">
                  {{ item }}
                </option>
              </select>
            </label>

            <label>
              <span>Academic Year</span>
              <select v-model="filters.academicYear">
                <option value="all">All</option>
                <option v-for="item in academicYearOptions" :key="item" :value="item">
                  {{ item }}
                </option>
              </select>
            </label>

            <label>
              <span>Status</span>
              <select v-model="filters.status">
                <option value="all">All</option>
                <option value="Active">Active</option>
                <option value="Graduated">Graduated</option>
                <option value="Suspended">Suspended</option>
                <option value="Archived">Archived</option>
              </select>
            </label>
          </div>
        </div>

        <div class="student-list">
          <button
            v-for="student in filteredStudents"
            :key="student.id"
            type="button"
            class="student-item"
            :class="{ active: selectedStudentId === student.id }"
            @click="selectStudent(student.id)"
          >
            <img :src="studentAvatar(student)" :alt="studentName(student)" />
            <div class="student-meta">
              <strong>{{ studentName(student) }}</strong>
              <small>{{ studentNumber(student) }}</small>
              <span>{{ student.program || 'Programme' }}</span>
            </div>
            <span class="status-pill" :class="statusClass(student.status)">
              {{ student.status || 'Active' }}
            </span>
          </button>

          <div v-if="!filteredStudents.length" class="empty-list">
            No students match the current filters.
          </div>
        </div>
      </aside>

      <section class="student-workspace" v-if="selectedStudent && selectedProfile">
        <div class="workspace-card hero-card">
          <div class="student-summary">
            <img :src="selectedProfile.photo" :alt="selectedStudent.fullName" />
            <div>
              <h2>{{ studentName(selectedStudent) }}</h2>
              <p>
                {{ studentNumber(selectedStudent) }} · {{ selectedProfile.academic.currentClass }} ·
                {{ selectedProfile.academic.academicYear }}
              </p>
              <div class="summary-tags">
                <span>{{ selectedProfile.academic.program }}</span>
                <span>{{ selectedProfile.academic.status }}</span>
              </div>
            </div>
          </div>

          <div class="stats-grid">
            <article>
              <span>GPA</span>
              <strong>{{ selectedProfile.academic.gpa }}</strong>
            </article>
            <article>
              <span>Credits</span>
              <strong>{{ selectedProfile.academic.credits }}</strong>
            </article>
            <article>
              <span>Courses Passed</span>
              <strong>{{ selectedProfile.academic.passedCourses }}</strong>
            </article>
            <article>
              <span>Courses Failed</span>
              <strong>{{ selectedProfile.academic.failedCourses }}</strong>
            </article>
          </div>
        </div>

        <div class="tab-scroll workspace-card">
          <button
            v-for="category in categories"
            :key="category"
            type="button"
            class="tab-btn"
            :class="{ active: activeCategory === category }"
            @click="activeCategory = category"
          >
            {{ category }}
          </button>
        </div>

        <transition name="fade" mode="out-in">
          <div :key="activeCategory" class="workspace-content">
            <section v-if="activeCategory === 'Overview'" class="content-grid two-col">
              <article class="workspace-card">
                <h3>Overview</h3>
                <p class="muted">{{ selectedProfile.summary }}</p>
                <div class="detail-list">
                  <div>
                    <span>Faculty</span>
                    <strong>{{ selectedProfile.academic.faculty }}</strong>
                  </div>
                  <div>
                    <span>Department</span>
                    <strong>{{ selectedProfile.academic.department }}</strong>
                  </div>
                  <div>
                    <span>Advisor</span>
                    <strong>{{ selectedProfile.academic.advisor }}</strong>
                  </div>
                  <div>
                    <span>Status</span>
                    <strong>{{ selectedProfile.academic.status }}</strong>
                  </div>
                </div>
              </article>

              <article class="workspace-card">
                <h3>Current academic period</h3>
                <div class="period-pill">
                  {{ activePeriod.className }} · {{ activePeriod.year }}
                </div>
                <div class="detail-list compact">
                  <div>
                    <span>GPA</span>
                    <strong>{{ activePeriod.gpa }}</strong>
                  </div>
                  <div>
                    <span>Credits</span>
                    <strong>{{ activePeriod.credits }}</strong>
                  </div>
                  <div>
                    <span>Status</span>
                    <strong>{{ activePeriod.status }}</strong>
                  </div>
                </div>
                <button class="secondary-btn" type="button" @click="openPromotionModal">
                  Promote Student
                </button>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Personal'" class="content-grid two-col">
              <article class="workspace-card">
                <h3>Personal information</h3>
                <div class="detail-list">
                  <div>
                    <span>Full name</span>
                    <strong>{{ selectedProfile.personal.fullName }}</strong>
                  </div>
                  <div>
                    <span>Date of birth</span>
                    <strong>{{ selectedProfile.personal.dob }}</strong>
                  </div>
                  <div>
                    <span>Gender</span>
                    <strong>{{ selectedProfile.personal.gender }}</strong>
                  </div>
                  <div>
                    <span>Nationality</span>
                    <strong>{{ selectedProfile.personal.nationality }}</strong>
                  </div>
                  <div>
                    <span>Phone</span>
                    <strong>{{ selectedProfile.personal.phone }}</strong>
                  </div>
                  <div>
                    <span>Email</span>
                    <strong>{{ selectedStudent.email }}</strong>
                  </div>
                </div>
              </article>

              <article class="workspace-card">
                <h3>Profile snapshot</h3>
                <div class="snapshot-list">
                  <div>
                    <span>Address</span>
                    <strong>{{ selectedProfile.personal.address }}</strong>
                  </div>
                  <div>
                    <span>Student ID</span>
                    <strong>{{ selectedProfile.studentNumber }}</strong>
                  </div>
                  <div>
                    <span>Programme</span>
                    <strong>{{ selectedProfile.academic.program }}</strong>
                  </div>
                  <div>
                    <span>Current class</span>
                    <strong>{{ selectedProfile.academic.currentClass }}</strong>
                  </div>
                </div>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Academic'" class="content-stack">
              <article class="workspace-card">
                <div class="section-header-row">
                  <div>
                    <h3>Academic workspace</h3>
                    <p class="muted">
                      {{ selectedStudent.fullName }} · {{ selectedProfile.studentNumber }} ·
                      {{ activePeriod.className }} · {{ activePeriod.year }}
                    </p>
                  </div>
                  <button class="secondary-btn" type="button" @click="openPromotionModal">
                    Promote Student
                  </button>
                </div>

                <div class="stats-grid small">
                  <article>
                    <span>Current GPA</span>
                    <strong>{{ activePeriod.gpa }}</strong>
                  </article>
                  <article>
                    <span>Credits Earned</span>
                    <strong>{{ activePeriod.credits }}</strong>
                  </article>
                  <article>
                    <span>Courses Passed</span>
                    <strong>{{ activePeriod.passed }}</strong>
                  </article>
                  <article>
                    <span>Courses Failed</span>
                    <strong>{{ activePeriod.failed }}</strong>
                  </article>
                </div>
              </article>

              <article class="workspace-card">
                <h3>Academic record entry</h3>
                <form class="record-form" @submit.prevent="saveAcademicRecord">
                  <div class="form-grid">
                    <label>
                      <span>Course / Subject Name</span>
                      <input
                        v-model="recordForm.course"
                        type="text"
                        placeholder="JavaScript Programming"
                      />
                    </label>
                    <label>
                      <span>Course Code</span>
                      <input v-model="recordForm.code" type="text" placeholder="CS204" />
                    </label>
                    <label>
                      <span>Semester</span>
                      <input v-model="recordForm.semester" type="text" placeholder="Semester 1" />
                    </label>
                    <label>
                      <span>Academic Year</span>
                      <input v-model="recordForm.year" type="text" placeholder="2025-2026" />
                    </label>
                    <label>
                      <span>Credits</span>
                      <input v-model.number="recordForm.credits" type="number" min="0" />
                    </label>
                    <label>
                      <span>CA</span>
                      <input v-model.number="recordForm.ca" type="number" min="0" />
                    </label>
                    <label>
                      <span>Exam Score</span>
                      <input v-model.number="recordForm.exam" type="number" min="0" />
                    </label>
                    <label>
                      <span>Total Score</span>
                      <input :value="recordForm.total" type="text" disabled />
                    </label>
                    <label>
                      <span>Grade</span>
                      <input :value="recordForm.grade" type="text" disabled />
                    </label>
                    <label>
                      <span>Grade Letter</span>
                      <input :value="recordForm.gradeLetter" type="text" disabled />
                    </label>
                    <label>
                      <span>Status</span>
                      <select v-model="recordForm.status">
                        <option>Passed</option>
                        <option>Failed</option>
                        <option>In Progress</option>
                      </select>
                    </label>
                    <label>
                      <span>Instructor</span>
                      <input v-model="recordForm.instructor" type="text" placeholder="Dr. Keita" />
                    </label>
                    <label class="full">
                      <span>Remarks</span>
                      <textarea
                        v-model="recordForm.remarks"
                        rows="2"
                        placeholder="Excellent progress"
                      ></textarea>
                    </label>
                  </div>

                  <div class="button-row">
                    <button class="primary-btn" type="submit">+ Add Course</button>
                    <button class="ghost-btn" type="button" @click="resetRecordForm">Reset</button>
                  </div>
                </form>
              </article>

              <article class="workspace-card">
                <div class="table-header-row">
                  <h3>Courses for {{ viewedPeriod.year }}</h3>
                  <span :class="['status-pill', viewedPeriod.status.toLowerCase()]">
                    {{ viewedPeriod.status }}
                  </span>
                </div>

                <div class="record-cards">
                  <div v-for="record in viewedPeriod.records" :key="record.id" class="record-card">
                    <div class="record-main">
                      <strong>{{ record.course }}</strong>
                      <small>{{ record.code }} · {{ record.semester }} · {{ record.year }}</small>
                      <span>{{ record.instructor }}</span>
                    </div>
                    <div class="record-score">
                      <strong>{{ record.total }}</strong>
                      <small>{{ record.gradeLetter }}</small>
                    </div>
                    <div class="record-actions">
                      <button class="mini-btn" type="button" @click="viewRecordDetails(record)">
                        View details
                      </button>
                      <button class="mini-btn" type="button" @click="editRecord(record)">
                        Edit
                      </button>
                      <button
                        class="mini-btn danger"
                        type="button"
                        @click="deleteRecord(record.id)"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                  <div v-if="!viewedPeriod.records.length" class="empty-list">
                    No academic records for this period.
                  </div>
                </div>
              </article>

              <article class="workspace-card">
                <div class="section-header-row">
                  <div>
                    <h3>Class progression</h3>
                    <p class="muted">
                      Promote the student and archive the current academic period.
                    </p>
                  </div>
                </div>

                <div class="progression-grid">
                  <div class="progression-card current">
                    <span>CURRENT ACADEMIC PERIOD</span>
                    <strong>{{ activePeriod.className }}</strong>
                    <p>{{ activePeriod.year }}</p>
                    <div class="mini-stats">
                      <span>GPA {{ activePeriod.gpa }}</span>
                      <span>Credits {{ activePeriod.credits }}</span>
                    </div>
                    <small>{{ activePeriod.status }}</small>
                  </div>
                  <div class="progression-card">
                    <span>ARCHIVE</span>
                    <strong>{{ archivedPeriods.length }} periods</strong>
                    <p>Historical academic records are preserved for each class progression.</p>
                  </div>
                </div>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Skills'" class="content-stack">
              <article class="workspace-card">
                <h3>Skills</h3>
                <div class="chip-row">
                  <span v-for="skill in selectedProfile.skills" :key="skill">{{ skill }}</span>
                </div>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Languages'" class="content-stack">
              <article class="workspace-card">
                <h3>Languages</h3>
                <div class="simple-list">
                  <div
                    v-for="language in selectedProfile.languages"
                    :key="language.name || language"
                  >
                    <strong>{{ language.name || language }}</strong>
                    <span>{{ language.level || 'Recorded' }}</span>
                  </div>
                </div>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Projects'" class="content-stack">
              <article class="workspace-card">
                <h3>Projects</h3>
                <div class="simple-list">
                  <div v-for="project in selectedProfile.projects" :key="project.title">
                    <strong>{{ project.title }}</strong>
                    <span>{{ project.description }}</span>
                  </div>
                </div>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Assignments'" class="content-stack">
              <article class="workspace-card">
                <h3>Assignments</h3>
                <p class="muted">
                  {{ selectedProfile.assignments.length }} assignment entries recorded.
                </p>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Certificates'" class="content-stack">
              <article class="workspace-card">
                <h3>Certificates</h3>
                <div class="chip-row">
                  <span v-for="certificate in selectedProfile.certificates" :key="certificate">
                    {{ certificate }}
                  </span>
                </div>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Experience'" class="content-stack">
              <article class="workspace-card">
                <h3>Experience</h3>
                <div class="simple-list">
                  <div v-for="exp in selectedProfile.experiences" :key="exp.title">
                    <strong>{{ exp.title }}</strong>
                    <span>{{ exp.company }} · {{ exp.date }}</span>
                  </div>
                </div>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Achievements'" class="content-stack">
              <article class="workspace-card">
                <h3>Achievements</h3>
                <div class="simple-list">
                  <div v-for="achievement in selectedProfile.achievements" :key="achievement.title">
                    <strong>{{ achievement.title }}</strong>
                    <span>{{ achievement.org }} · {{ achievement.date }}</span>
                  </div>
                </div>
              </article>
            </section>

            <section v-else-if="activeCategory === 'Documents'" class="content-stack">
              <article class="workspace-card">
                <h3>Documents</h3>
                <div class="simple-list">
                  <div v-for="doc in selectedProfile.documents" :key="doc.name">
                    <strong>{{ doc.name }}</strong>
                    <span>{{ doc.type }}</span>
                  </div>
                </div>
              </article>
            </section>

            <section v-else class="content-stack">
              <article class="workspace-card">
                <h3>Class history</h3>
                <div class="history-list">
                  <div
                    v-for="period in selectedProfile.periods"
                    :key="period.year + period.className"
                    class="history-card"
                    :class="{ archived: period.status !== 'Active' }"
                  >
                    <div>
                      <strong>{{ period.year }}</strong>
                      <p>{{ period.className }}</p>
                      <small>{{ period.status }}</small>
                    </div>
                    <div class="history-actions">
                      <button class="mini-btn" type="button" @click="viewPeriod(period)">
                        View Records
                      </button>
                    </div>
                  </div>
                </div>
              </article>
            </section>
          </div>
        </transition>
      </section>

      <aside class="registration-wizard">
        <div class="workspace-card wizard-card">
          <div class="section-header-row">
            <div>
              <p class="eyebrow">Add New Student</p>
              <h2>Create a student profile</h2>
            </div>
          </div>

          <div class="stepper">
            <button
              v-for="(step, index) in wizardSteps"
              :key="step.key"
              type="button"
              class="step-item"
              :class="{ active: wizardStep === index + 1 }"
              @click="wizardStep = index + 1"
            >
              <span>{{ index + 1 }}</span>
              <small>{{ step.label }}</small>
            </button>
          </div>

          <div class="wizard-body">
            <section v-if="wizardStep === 1" class="wizard-step">
              <div class="wizard-photo">
                <img :src="wizardPhotoPreview" alt="Student preview" />
                <label class="ghost-btn upload-btn">
                  Choose Photo
                  <input type="file" accept="image/*" hidden @change="onWizardPhotoChange" />
                </label>
              </div>

              <div class="form-grid">
                <label>
                  <span>First Name</span>
                  <input v-model="newStudent.personal.firstName" type="text" placeholder="Amadou" />
                </label>
                <label>
                  <span>Last Name</span>
                  <input v-model="newStudent.personal.lastName" type="text" placeholder="Koné" />
                </label>
                <label>
                  <span>Date of Birth</span>
                  <input v-model="newStudent.personal.dob" type="date" />
                </label>
                <label>
                  <span>Gender</span>
                  <select v-model="newStudent.personal.gender">
                    <option>Male</option>
                    <option>Female</option>
                  </select>
                </label>
                <label>
                  <span>Nationality</span>
                  <input
                    v-model="newStudent.personal.nationality"
                    type="text"
                    placeholder="Malian"
                  />
                </label>
                <label>
                  <span>Phone</span>
                  <input
                    v-model="newStudent.personal.phone"
                    type="text"
                    placeholder="+223 70 12 34 56"
                  />
                </label>
                <label>
                  <span>Email</span>
                  <input
                    v-model="newStudent.personal.email"
                    type="email"
                    placeholder="student@university.edu"
                  />
                </label>
                <label>
                  <span>Password</span>
                  <div class="password-field">
                    <input
                      v-model="newStudent.personal.password"
                      :type="showWizardPassword ? 'text' : 'password'"
                      placeholder="Temporary password"
                      autocomplete="new-password"
                    />
                    <button
                      class="icon-btn"
                      type="button"
                      @click="showWizardPassword = !showWizardPassword"
                    >
                      <i :class="showWizardPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                    <button class="ghost-btn" type="button" @click="generateWizardPassword">
                      Generate
                    </button>
                  </div>
                  <small class="field-hint"
                    >At least 8 characters, with 1 letter and 1 digit. The student can change this
                    password after logging in.</small
                  >
                  <small v-if="passwordError" class="field-error">{{ passwordError }}</small>
                </label>
                <label class="full">
                  <span>Address</span>
                  <input
                    v-model="newStudent.personal.address"
                    type="text"
                    placeholder="Bamako, Mali"
                  />
                </label>
              </div>
            </section>

            <section v-else-if="wizardStep === 2" class="wizard-step">
              <div class="form-grid">
                <label>
                  <span>Student ID</span>
                  <input
                    v-model="newStudent.academic.studentId"
                    type="text"
                    placeholder="STU-2025-001"
                  />
                </label>
                <label>
                  <span>Faculty</span>
                  <input
                    v-model="newStudent.academic.faculty"
                    type="text"
                    placeholder="Sciences et Techniques"
                  />
                </label>
                <label>
                  <span>Department</span>
                  <input
                    v-model="newStudent.academic.department"
                    type="text"
                    placeholder="Computer Science"
                  />
                </label>
                <label>
                  <span>Programme</span>
                  <input
                    v-model="newStudent.academic.program"
                    type="text"
                    placeholder="Computer Science"
                  />
                </label>
                <label>
                  <span>Current Class</span>
                  <input
                    v-model="newStudent.academic.currentClass"
                    type="text"
                    placeholder="Licence 1"
                  />
                </label>
                <label>
                  <span>Academic Year</span>
                  <input
                    v-model="newStudent.academic.academicYear"
                    type="text"
                    placeholder="2025-2026"
                  />
                </label>
                <label>
                  <span>Admission Date</span>
                  <input v-model="newStudent.academic.admissionDate" type="date" />
                </label>
                <label>
                  <span>Status</span>
                  <select v-model="newStudent.academic.status">
                    <option>Active</option>
                    <option>Graduated</option>
                    <option>Suspended</option>
                    <option>Archived</option>
                  </select>
                </label>
              </div>
            </section>

            <section v-else-if="wizardStep === 3" class="wizard-step">
              <div class="radar-records">
                <div v-for="group in radarGroups" :key="group.key" class="radar-record-card">
                  <div class="record-section-header">
                    <div>
                      <span class="record-badge" :style="{ background: group.color }">{{
                        group.short
                      }}</span>
                      <h4>{{ group.label }}</h4>
                    </div>
                    <button
                      class="ghost-btn small-btn"
                      type="button"
                      @click="addRadarEntry(group.key)"
                    >
                      + Add record
                    </button>
                  </div>

                  <div
                    v-for="(entry, index) in newStudent.radar[group.key].entries"
                    :key="group.key + '-' + index"
                    class="record-entry"
                  >
                    <div v-if="group.key === 'academic'" class="entry-form">
                      <label>
                        <span>Semester notes / averages</span>
                        <textarea
                          v-model="entry.notes"
                          rows="3"
                          placeholder="Semester 1: 16/20, Semester 2: 15.5/20"
                        ></textarea>
                      </label>
                      <label>
                        <span>Bulletin photo</span>
                        <input
                          type="file"
                          accept="image/*"
                          @change="onRadarUpload(group.key, $event, index)"
                        />
                      </label>
                      <img
                        v-if="entry.bulletinPhoto"
                        :src="entry.bulletinPhoto"
                        alt="Bulletin preview"
                        class="preview-image"
                      />
                    </div>

                    <div v-else-if="group.key === 'certificate'" class="entry-form">
                      <label>
                        <span>Training / course name</span>
                        <input
                          v-model="entry.trainingName"
                          type="text"
                          placeholder="Python for Data Analysis"
                        />
                      </label>
                      <label>
                        <span>Certificate proof</span>
                        <input
                          type="file"
                          accept=".pdf,image/*"
                          @change="onRadarUpload(group.key, $event, index)"
                        />
                      </label>
                      <small v-if="entry.proofName">Attached: {{ entry.proofName }}</small>
                    </div>

                    <div v-else-if="group.key === 'numerique'" class="entry-form">
                      <label>
                        <span>IT project / technical project</span>
                        <input
                          v-model="entry.projectName"
                          type="text"
                          placeholder="Portfolio / GIS dashboard"
                        />
                      </label>
                      <label>
                        <span>Project description</span>
                        <textarea
                          v-model="entry.projectDescription"
                          rows="3"
                          placeholder="Describe the project and tools used"
                        ></textarea>
                      </label>
                      <label>
                        <span>Verification proof</span>
                        <input
                          type="file"
                          accept=".pdf,image/*"
                          @change="onRadarUpload(group.key, $event, index)"
                        />
                      </label>
                      <small v-if="entry.proofName">Attached: {{ entry.proofName }}</small>
                    </div>

                    <div v-else-if="group.key === 'langue'" class="entry-form">
                      <label>
                        <span>Languages known</span>
                        <textarea
                          v-model="entry.languages"
                          rows="2"
                          placeholder="French: Fluent; English: Advanced; Arabic: Intermediate"
                        ></textarea>
                      </label>
                    </div>

                    <div v-else-if="group.key === 'stage'" class="entry-form">
                      <label>
                        <span>Company / organization</span>
                        <input v-model="entry.company" type="text" placeholder="MeryX Lab" />
                      </label>
                      <label>
                        <span>Duration</span>
                        <input v-model="entry.duration" type="text" placeholder="3 months / 2025" />
                      </label>
                      <label>
                        <span>Internship description</span>
                        <textarea
                          v-model="entry.description"
                          rows="3"
                          placeholder="Describe the internship tasks and learning"
                        ></textarea>
                      </label>
                      <label>
                        <span>Internship proof</span>
                        <input
                          type="file"
                          accept=".pdf,image/*"
                          @change="onRadarUpload(group.key, $event, index)"
                        />
                      </label>
                      <small v-if="entry.proofName">Attached: {{ entry.proofName }}</small>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <section v-else class="wizard-step">
              <div class="review-grid">
                <div>
                  <h4>Personal information</h4>
                  <p>{{ newStudent.personal.firstName }} {{ newStudent.personal.lastName }}</p>
                  <p>{{ newStudent.personal.email }}</p>
                  <p>{{ newStudent.personal.phone }}</p>
                </div>
                <div>
                  <h4>Academic information</h4>
                  <p>{{ newStudent.academic.studentId }}</p>
                  <p>{{ newStudent.academic.faculty }}</p>
                  <p>
                    {{ newStudent.academic.currentClass }} · {{ newStudent.academic.academicYear }}
                  </p>
                </div>
                <div>
                  <h4>Skill radar evidence</h4>
                  <p>Académique: {{ getRadarSummary('academic') }}</p>
                  <p>Certificat: {{ getRadarSummary('certificate') }}</p>
                  <p>Numérique: {{ getRadarSummary('numerique') }}</p>
                  <p>Langue: {{ getRadarSummary('langue') }}</p>
                  <p>Stage: {{ getRadarSummary('stage') }}</p>
                </div>
              </div>
            </section>
          </div>

          <div class="wizard-footer">
            <button
              class="ghost-btn"
              type="button"
              :disabled="wizardStep === 1"
              @click="wizardStep -= 1"
            >
              Previous
            </button>
            <div class="wizard-foot-actions">
              <button
                v-if="wizardStep < 4"
                class="primary-btn"
                type="button"
                @click="wizardStep += 1"
              >
                Next
              </button>
              <button
                v-else
                class="primary-btn"
                type="button"
                :disabled="isCreatingStudent"
                @click="createStudent"
              >
                {{ isCreatingStudent ? 'Creating...' : 'Create Student' }}
              </button>
            </div>
          </div>
          <p v-if="wizardError" class="field-error wizard-error">{{ wizardError }}</p>
        </div>
      </aside>
    </div>

    <div v-if="showPromotionModal" class="overlay" @click="closePromotionModal"></div>
    <section v-if="showPromotionModal" class="modal-card">
      <div class="modal-header">
        <div>
          <p class="eyebrow">Class progression</p>
          <h3>Promote student</h3>
        </div>
        <button class="icon-btn" type="button" @click="closePromotionModal">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="modal-body">
        <div class="old-new-grid">
          <article class="summary-box archived">
            <span>OLD RECORD</span>
            <strong>{{ activePeriod.className }}</strong>
            <p>{{ activePeriod.year }}</p>
            <small>Status: Archived</small>
          </article>
          <article class="summary-box active">
            <span>NEW RECORD</span>
            <strong>{{ promotionForm.nextClass }}</strong>
            <p>{{ promotionForm.academicYear }}</p>
            <small>Status: Active</small>
          </article>
        </div>

        <div class="form-grid compact top-gap">
          <label>
            <span>Next Class</span>
            <input v-model="promotionForm.nextClass" type="text" />
          </label>
          <label>
            <span>New Academic Year</span>
            <input v-model="promotionForm.academicYear" type="text" />
          </label>
          <label>
            <span>Promotion Date</span>
            <input v-model="promotionForm.promotionDate" type="date" />
          </label>
          <label class="full">
            <span>Remarks</span>
            <textarea v-model="promotionForm.remarks" rows="2"></textarea>
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button class="ghost-btn" type="button" @click="closePromotionModal">Cancel</button>
        <button class="primary-btn" type="button" @click="confirmPromotion">
          Confirm Promotion
        </button>
      </div>
    </section>

    <section v-if="recordDetailOpen && selectedRecord" class="modal-card record-modal">
      <div class="modal-header">
        <div>
          <p class="eyebrow">Record details</p>
          <h3>{{ selectedRecord.course }}</h3>
        </div>
        <button class="icon-btn" type="button" @click="closeRecordDetails">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="modal-body record-detail-grid">
        <div>
          <span>Code</span><strong>{{ selectedRecord.code }}</strong>
        </div>
        <div>
          <span>Semester</span><strong>{{ selectedRecord.semester }}</strong>
        </div>
        <div>
          <span>Academic Year</span><strong>{{ selectedRecord.year }}</strong>
        </div>
        <div>
          <span>Credits</span><strong>{{ selectedRecord.credits }}</strong>
        </div>
        <div>
          <span>CA</span><strong>{{ selectedRecord.ca }}</strong>
        </div>
        <div>
          <span>Exam</span><strong>{{ selectedRecord.exam }}</strong>
        </div>
        <div>
          <span>Total</span><strong>{{ selectedRecord.total }}</strong>
        </div>
        <div>
          <span>Grade</span><strong>{{ selectedRecord.gradeLetter }}</strong>
        </div>
        <div>
          <span>Status</span><strong>{{ selectedRecord.status }}</strong>
        </div>
        <div class="full">
          <span>Instructor</span><strong>{{ selectedRecord.instructor }}</strong>
        </div>
        <div class="full">
          <span>Remarks</span><strong>{{ selectedRecord.remarks }}</strong>
        </div>
      </div>
      <div class="modal-footer">
        <button class="ghost-btn" type="button" @click="closeRecordDetails">Close</button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'
// import { validatePassword, extractApiErrorMessage } from '@/utils/password'

const router = useRouter()
const authStore = useAuthStore()
const studentStore = useStudentStore()

const parseId = (value) => {
  if (!value) return null
  if (typeof value === 'number') return value
  if (typeof value === 'string') {
    const parts = value.split('/')
    const parsed = Number(parts[parts.length - 1])
    return Number.isFinite(parsed) ? parsed : null
  }
  if (typeof value === 'object') {
    if (typeof value.id === 'number') return value.id
    if (typeof value['@id'] === 'string') {
      const parts = value['@id'].split('/')
      const parsed = Number(parts[parts.length - 1])
      return Number.isFinite(parsed) ? parsed : null
    }
  }
  return null
}

const connectedUniversityId = computed(() => {
  const user = authStore.user || {}
  return (
    parseId(user.universityId) ||
    parseId(user.university) ||
    parseId(user.universityProfile) ||
    (authStore.isUniversity ? parseId(user.id) : null)
  )
})

const categories = [
  'Overview',
  'Personal',
  'Academic',
  'Skills',
  'Languages',
  'Projects',
  'Assignments',
  'Certificates',
  'Experience',
  'Achievements',
  'Documents',
  'Class History',
]

const wizardSteps = [
  { key: 'personal', label: 'Personal' },
  { key: 'academic', label: 'Academic' },
  { key: 'records', label: 'Records' },
  { key: 'review', label: 'Review' },
]

const searchQuery = ref('')
const selectorOpen = ref(false)
const showFilters = ref(true)
const activeCategory = ref('Overview')
const selectedStudentId = ref(null)
const wizardStep = ref(1)
const showPromotionModal = ref(false)
const recordDetailOpen = ref(false)
const selectedRecord = ref(null)
const historyFocusYear = ref(null)
const wizardPhotoPreview = ref('https://i.pravatar.cc/240?img=12')
const showWizardPassword = ref(false)
const wizardError = ref('')
const isCreatingStudent = ref(false)

const generateWizardPassword = () => {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'
  let password = ''
  for (let i = 0; i < 10; i += 1) {
    password += chars[Math.floor(Math.random() * chars.length)]
  }
  // Ensure the generated password always satisfies the backend's letter+digit policy.
  password = `${password}7a`
  newStudent.personal.password = password
  showWizardPassword.value = true
}

const passwordError = computed(() => {
  if (!newStudent.personal.password) return ''
  return validatePassword(newStudent.personal.password)
})

const filters = reactive({
  gender: 'all',
  faculty: 'all',
  department: 'all',
  program: 'all',
  academicYear: 'all',
  status: 'all',
})

const newStudent = reactive({
  personal: {
    firstName: '',
    lastName: '',
    dob: '',
    gender: 'Male',
    nationality: '',
    phone: '',
    email: '',
    password: '',
    address: '',
    photo: '',
  },
  academic: {
    studentId: '',
    faculty: '',
    department: '',
    program: '',
    currentClass: 'Licence 1',
    academicYear: '2025-2026',
    admissionDate: '',
    status: 'Active',
  },
  records: [],
  radar: {
    academic: { entries: [{ notes: '', bulletinPhoto: '' }] },
    certificate: { entries: [{ trainingName: '', proofName: '', proofFile: '' }] },
    numerique: {
      entries: [{ projectName: '', projectDescription: '', proofName: '', proofFile: '' }],
    },
    langue: { entries: [{ languages: '' }] },
    stage: {
      entries: [{ company: '', duration: '', description: '', proofName: '', proofFile: '' }],
    },
  },
})

const radarGroups = [
  { key: 'academic', label: 'Académique', short: 'A', color: '#f4d35e' },
  { key: 'certificate', label: 'Certificat', short: 'C', color: '#6ec6ff' },
  { key: 'numerique', label: 'Numérique', short: 'N', color: '#7ed9b1' },
  { key: 'langue', label: 'Langue', short: 'L', color: '#d9a4ff' },
  { key: 'stage', label: 'Stage', short: 'S', color: '#ffad99' },
]

const wizardRecord = reactive({
  course: '',
  code: '',
  semester: 'Semester 1',
  year: '2025-2026',
  credits: 3,
  ca: 15,
  exam: 17,
  total: 32,
  grade: 'A',
  gradeLetter: 'A',
  status: 'Passed',
  instructor: '',
  remarks: '',
})

const promotionForm = reactive({
  nextClass: 'Licence 2',
  academicYear: '2025-2026',
  promotionDate: '',
  remarks: '',
})

const profileCatalog = reactive({
  101: {
    photo: 'https://i.pravatar.cc/240?img=1',
    studentNumber: 'STU-2401',
    summary:
      'Strong web development candidate with modern frontend skills and a collaborative mindset.',
    personal: {
      fullName: 'Amina Diallo',
      dob: '2002-08-12',
      gender: 'Female',
      nationality: 'Malian',
      phone: '+223 70 12 34 56',
      address: 'Bamako, Mali',
    },
    academic: {
      program: 'Computer Science',
      faculty: 'Sciences et Techniques',
      department: 'Informatique',
      currentClass: 'Licence 1',
      academicYear: '2024-2025',
      status: 'Active',
      advisor: 'Dr. Fatou Keita',
      gpa: '14.25/20',
      credits: '30/60',
      passedCourses: 6,
      failedCourses: 1,
    },
    skills: ['Vue 3', 'JavaScript', 'Project Management'],
    languages: [
      { name: 'French', level: 'Fluent' },
      { name: 'English', level: 'Intermediate' },
    ],
    projects: [
      { title: 'Learning Portal', description: 'Responsive student portal built with Vue 3.' },
    ],
    assignments: [{ title: 'UI Project', status: 'Submitted' }],
    certificates: ['Web Fundamentals', 'UX Basics'],
    experiences: [{ title: 'Frontend Intern', company: 'MeryX Lab', date: '2025' }],
    achievements: [{ title: 'Dean List', org: 'University', date: '2024' }],
    documents: [{ name: 'CV_Amina.pdf', type: 'pdf' }],
    periods: [
      {
        className: 'Licence 1',
        year: '2024-2025',
        status: 'Active',
        gpa: '14.25/20',
        credits: '30/60',
        passed: 6,
        failed: 1,
        records: [
          {
            id: 1,
            course: 'JavaScript Programming',
            code: 'CS204',
            semester: 'Semester 1',
            year: '2024-2025',
            credits: 3,
            ca: 15,
            exam: 17,
            total: 32,
            gradeLetter: 'A',
            grade: 'A',
            status: 'Passed',
            instructor: 'Dr. Maiga',
            remarks: 'Excellent progress',
          },
          {
            id: 2,
            course: 'Database Systems',
            code: 'CS210',
            semester: 'Semester 1',
            year: '2024-2025',
            credits: 3,
            ca: 14,
            exam: 16,
            total: 30,
            gradeLetter: 'A',
            grade: 'A',
            status: 'Passed',
            instructor: 'Prof. Traoré',
            remarks: 'Strong analytical work',
          },
        ],
      },
      {
        className: 'Foundation',
        year: '2023-2024',
        status: 'Completed',
        gpa: '13.10/20',
        credits: '24/60',
        passed: 4,
        failed: 1,
        records: [
          {
            id: 3,
            course: 'Introduction to Programming',
            code: 'CS101',
            semester: 'Semester 1',
            year: '2023-2024',
            credits: 3,
            ca: 13,
            exam: 15,
            total: 28,
            gradeLetter: 'B',
            grade: 'B',
            status: 'Passed',
            instructor: 'Dr. Keita',
            remarks: 'Completed successfully',
          },
        ],
      },
    ],
  },
  102: {
    photo: 'https://i.pravatar.cc/240?img=8',
    studentNumber: 'STU-2402',
    summary:
      'Motivated engineering student with a strong technical foundation and analytical focus.',
    personal: {
      fullName: 'Lucas Bernard',
      dob: '2001-11-04',
      gender: 'Male',
      nationality: 'French',
      phone: '+223 73 45 67 89',
      address: 'Ségou, Mali',
    },
    academic: {
      program: 'Engineering',
      faculty: 'Génie',
      department: 'Mécanique',
      currentClass: 'Licence 2',
      academicYear: '2025-2026',
      status: 'Active',
      advisor: 'Prof. Jean Lavallée',
      gpa: '13.42/20',
      credits: '60/60',
      passedCourses: 10,
      failedCourses: 2,
    },
    skills: ['CAD', 'C++', 'Data Analysis'],
    languages: [
      { name: 'French', level: 'Fluent' },
      { name: 'English', level: 'Intermediate' },
    ],
    projects: [
      { title: 'Prototype Design', description: 'Mechanical prototype for local industry use.' },
    ],
    assignments: [{ title: 'Dynamics Report', status: 'Pending' }],
    certificates: ['Mechanical Drafting'],
    experiences: [{ title: 'Workshop Assistant', company: 'TechWorks', date: '2024' }],
    achievements: [{ title: 'Innovation Prize', org: 'Faculty', date: '2025' }],
    documents: [{ name: 'CV_Lucas.pdf', type: 'pdf' }],
    periods: [
      {
        className: 'Licence 2',
        year: '2025-2026',
        status: 'Active',
        gpa: '13.42/20',
        credits: '60/60',
        passed: 10,
        failed: 2,
        records: [
          {
            id: 1,
            course: 'Thermodynamics',
            code: 'ME201',
            semester: 'Semester 1',
            year: '2025-2026',
            credits: 4,
            ca: 12,
            exam: 15,
            total: 27,
            gradeLetter: 'B',
            grade: 'B',
            status: 'Passed',
            instructor: 'Dr. Sissoko',
            remarks: 'Good fundamentals',
          },
        ],
      },
      {
        className: 'Licence 1',
        year: '2024-2025',
        status: 'Archived',
        gpa: '12.80/20',
        credits: '30/60',
        passed: 6,
        failed: 2,
        records: [],
      },
    ],
  },
  103: {
    photo: 'https://i.pravatar.cc/240?img=5',
    studentNumber: 'STU-2403',
    summary: 'High-performing business student with strong communication and analytical skills.',
    personal: {
      fullName: 'Sofia Martinez',
      dob: '2000-02-18',
      gender: 'Female',
      nationality: 'Spanish',
      phone: '+223 78 12 33 11',
      address: 'Bamako, Mali',
    },
    academic: {
      program: 'Business',
      faculty: 'Commerce',
      department: 'Marketing',
      currentClass: 'Master 1',
      academicYear: '2024-2025',
      status: 'Active',
      advisor: 'Dr. Caroline Thiam',
      gpa: '15.10/20',
      credits: '24/30',
      passedCourses: 5,
      failedCourses: 0,
    },
    skills: ['Marketing', 'Excel', 'Communication'],
    languages: [
      { name: 'Spanish', level: 'Fluent' },
      { name: 'English', level: 'Fluent' },
    ],
    projects: [
      { title: 'Market Strategy Lab', description: 'Campaign analysis and reporting project.' },
    ],
    assignments: [{ title: 'Business Case Study', status: 'Submitted' }],
    certificates: ['Digital Marketing'],
    experiences: [{ title: 'Research Assistant', company: 'University Lab', date: '2024' }],
    achievements: [{ title: 'Best Presentation', org: 'Business School', date: '2024' }],
    documents: [{ name: 'CV_Sofia.pdf', type: 'pdf' }],
    periods: [
      {
        className: 'Master 1',
        year: '2024-2025',
        status: 'Active',
        gpa: '15.10/20',
        credits: '24/30',
        passed: 5,
        failed: 0,
        records: [
          {
            id: 1,
            course: 'Strategic Marketing',
            code: 'BM501',
            semester: 'Semester 1',
            year: '2024-2025',
            credits: 4,
            ca: 16,
            exam: 17,
            total: 33,
            gradeLetter: 'A',
            grade: 'A',
            status: 'Passed',
            instructor: 'Prof. Diallo',
            remarks: 'Excellent strategic thinking',
          },
        ],
      },
    ],
  },
})

const defaultSelectedId = computed(() => studentStore.students[0]?.id ?? null)

const studentNumber = (student) => profileCatalog[student.id]?.studentNumber || `STU-${student.id}`
const studentAvatar = (student) =>
  profileCatalog[student.id]?.photo || `https://i.pravatar.cc/120?img=${(student.id % 60) + 1}`
const studentName = (student) =>
  student.fullName ||
  `${student.firstName || ''} ${student.lastName || ''}`.trim() ||
  student.name ||
  'Student'

const buildFallbackProfile = (student) => ({
  photo: studentAvatar(student),
  studentNumber: studentNumber(student),
  summary: 'Student profile summary is ready for academic management.',
  personal: {
    fullName: studentName(student),
    dob: '',
    gender: student.gender || 'Male',
    nationality: '',
    phone: student.phone || '',
    address: '',
  },
  academic: {
    program: student.program || 'Programme',
    faculty: student.faculty || 'Faculty',
    department: student.department || 'Department',
    currentClass: student.level || 'Licence 1',
    academicYear: student.academicYear || '2025-2026',
    status: student.status || 'Active',
    advisor: 'Assigned later',
    gpa: student.gpa || '0.00/20',
    credits: '0/0',
    passedCourses: 0,
    failedCourses: 0,
  },
  skills: student.skills || [],
  languages: (student.languages || []).map((language) => ({ name: language, level: 'Recorded' })),
  projects: [],
  assignments: [],
  certificates: [],
  experiences: [],
  achievements: [],
  documents: [],
  periods: [
    {
      className: student.level || 'Licence 1',
      year: student.academicYear || '2025-2026',
      status: 'Active',
      gpa: student.gpa || '0.00/20',
      credits: '0/0',
      passed: 0,
      failed: 0,
      records: [],
    },
  ],
})

const seedProfile = (student) => {
  if (!profileCatalog[student.id]) {
    profileCatalog[student.id] = buildFallbackProfile(student)
  }
}

watch(
  () => studentStore.students,
  (students) => {
    students.forEach(seedProfile)
    if (!selectedStudentId.value && students.length) {
      selectedStudentId.value = students[0].id
    }
  },
  { immediate: true, deep: true },
)

const selectedStudent = computed(
  () => studentStore.students.find((student) => student.id === selectedStudentId.value) || null,
)

const selectedProfile = computed(() => {
  if (!selectedStudent.value) return null
  return profileCatalog[selectedStudent.value.id] || buildFallbackProfile(selectedStudent.value)
})

const activePeriod = computed(() => {
  if (!selectedProfile.value) return null
  return (
    selectedProfile.value.periods.find((period) => period.status === 'Active') ||
    selectedProfile.value.periods[0]
  )
})

const archivedPeriods = computed(() => {
  if (!selectedProfile.value) return []
  return selectedProfile.value.periods.filter((period) => period.status !== 'Active')
})

const viewedPeriod = computed(() => {
  if (!selectedProfile.value) return null
  if (historyFocusYear.value) {
    return (
      selectedProfile.value.periods.find((period) => period.year === historyFocusYear.value) ||
      activePeriod.value
    )
  }
  return activePeriod.value
})

const filteredStudents = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return studentStore.students.filter((student) => {
    const profile = profileCatalog[student.id] || buildFallbackProfile(student)
    const name = studentName(student).toLowerCase()
    const studentId = studentNumber(student).toLowerCase()
    const email = (student.email || '').toLowerCase()
    // const program = (profile.academic.program || student.program || '').toLowerCase()
    const gender = (profile.personal.gender || '').toLowerCase()
    // const faculty = (profile.academic.faculty || '').toLowerCase()
    // const department = (profile.academic.department || '').toLowerCase()
    // const academicYear = (profile.academic.academicYear || '').toLowerCase()
    const status = (profile.academic.status || student.status || '').toLowerCase()

    const matchesQuery =
      !query || name.includes(query) || studentId.includes(query) || email.includes(query)

    const matchesGender = filters.gender === 'all' || gender === filters.gender.toLowerCase()
    const matchesFaculty = filters.faculty === 'all' || profile.academic.faculty === filters.faculty
    const matchesDepartment =
      filters.department === 'all' || profile.academic.department === filters.department
    const matchesProgram = filters.program === 'all' || profile.academic.program === filters.program
    const matchesAcademicYear =
      filters.academicYear === 'all' || profile.academic.academicYear === filters.academicYear
    const matchesStatus = filters.status === 'all' || status === filters.status.toLowerCase()

    return (
      matchesQuery &&
      matchesGender &&
      matchesFaculty &&
      matchesDepartment &&
      matchesProgram &&
      matchesAcademicYear &&
      matchesStatus
    )
  })
})

const facultyOptions = computed(() => [
  ...new Set(
    studentStore.students
      .map(
        (student) => (profileCatalog[student.id] || buildFallbackProfile(student)).academic.faculty,
      )
      .filter(Boolean),
  ),
])
const departmentOptions = computed(() => [
  ...new Set(
    studentStore.students
      .map(
        (student) =>
          (profileCatalog[student.id] || buildFallbackProfile(student)).academic.department,
      )
      .filter(Boolean),
  ),
])
const programOptions = computed(() => [
  ...new Set(
    studentStore.students
      .map(
        (student) => (profileCatalog[student.id] || buildFallbackProfile(student)).academic.program,
      )
      .filter(Boolean),
  ),
])
const academicYearOptions = computed(() => [
  ...new Set(
    studentStore.students
      .map(
        (student) =>
          (profileCatalog[student.id] || buildFallbackProfile(student)).academic.academicYear,
      )
      .filter(Boolean),
  ),
])

const recordForm = reactive({
  course: '',
  code: '',
  semester: 'Semester 1',
  year: '',
  credits: 3,
  ca: 15,
  exam: 17,
  total: 32,
  grade: 'A',
  gradeLetter: 'A',
  status: 'Passed',
  instructor: '',
  remarks: '',
})

const wizardRecordSync = () => {
  wizardRecord.total = Number(wizardRecord.ca || 0) + Number(wizardRecord.exam || 0)
  wizardRecord.gradeLetter = letterFromScore(wizardRecord.total)
  wizardRecord.grade = wizardRecord.gradeLetter
}

// const scoreFromRecord = (record) => Number(record.ca || 0) + Number(record.exam || 0)
const letterFromScore = (score) => {
  const pct = score / 40
  if (pct >= 0.85) return 'A'
  if (pct >= 0.75) return 'B'
  if (pct >= 0.65) return 'C'
  if (pct >= 0.5) return 'D'
  return 'F'
}

const syncRecordForm = () => {
  recordForm.total = Number(recordForm.ca || 0) + Number(recordForm.exam || 0)
  recordForm.gradeLetter = letterFromScore(recordForm.total)
  recordForm.grade = recordForm.gradeLetter
}

watch(
  () => [wizardRecord.ca, wizardRecord.exam],
  () => wizardRecordSync(),
  { immediate: true },
)

watch(
  () => [recordForm.ca, recordForm.exam],
  () => syncRecordForm(),
  { immediate: true },
)

const toggleSelector = () => {
  selectorOpen.value = !selectorOpen.value
}

const selectStudent = (id) => {
  selectedStudentId.value = id
  historyFocusYear.value = null
  if (window.innerWidth <= 1100) {
    selectorOpen.value = false
  }
}

const statusClass = (status) => {
  const value = (status || '').toLowerCase()
  if (value === 'graduated' || value === 'archived') return 'archived'
  if (value === 'suspended') return 'suspended'
  return 'active'
}

const resetRecordForm = () => {
  Object.assign(recordForm, {
    course: '',
    code: '',
    semester: 'Semester 1',
    year: activePeriod.value?.year || '',
    credits: 3,
    ca: 15,
    exam: 17,
    total: 32,
    grade: 'A',
    gradeLetter: 'A',
    status: 'Passed',
    instructor: '',
    remarks: '',
  })
}

const saveAcademicRecord = () => {
  if (!selectedProfile.value || !activePeriod.value || activePeriod.value.status !== 'Active')
    return

  activePeriod.value.records.unshift({
    id: Date.now(),
    course: recordForm.course,
    code: recordForm.code,
    semester: recordForm.semester,
    year: recordForm.year || activePeriod.value.year,
    credits: recordForm.credits,
    ca: recordForm.ca,
    exam: recordForm.exam,
    total: recordForm.total,
    gradeLetter: recordForm.gradeLetter,
    grade: recordForm.grade,
    status: recordForm.status,
    instructor: recordForm.instructor,
    remarks: recordForm.remarks,
  })

  activePeriod.value.passed = activePeriod.value.records.filter(
    (record) => record.status === 'Passed',
  ).length
  activePeriod.value.failed = activePeriod.value.records.filter(
    (record) => record.status === 'Failed',
  ).length
  resetRecordForm()
}

const viewRecordDetails = (record) => {
  selectedRecord.value = record
  recordDetailOpen.value = true
}

const closeRecordDetails = () => {
  recordDetailOpen.value = false
  selectedRecord.value = null
}

const editRecord = (record) => {
  Object.assign(recordForm, {
    course: record.course,
    code: record.code,
    semester: record.semester,
    year: record.year,
    credits: record.credits,
    ca: record.ca,
    exam: record.exam,
    total: record.total,
    grade: record.grade,
    gradeLetter: record.gradeLetter,
    status: record.status,
    instructor: record.instructor,
    remarks: record.remarks,
  })
  activeCategory.value = 'Academic'
}

const deleteRecord = (recordId) => {
  if (!activePeriod.value || activePeriod.value.status !== 'Active') return
  activePeriod.value.records = activePeriod.value.records.filter((record) => record.id !== recordId)
}

const viewPeriod = (period) => {
  historyFocusYear.value = period.year
  activeCategory.value = 'Academic'
}

const openPromotionModal = () => {
  showPromotionModal.value = true
}

const closePromotionModal = () => {
  showPromotionModal.value = false
}

const confirmPromotion = () => {
  if (!selectedProfile.value || !activePeriod.value) return

  activePeriod.value.status = 'Archived'
  selectedProfile.value.periods.unshift({
    className: promotionForm.nextClass,
    year: promotionForm.academicYear,
    status: 'Active',
    gpa: activePeriod.value.gpa,
    credits: activePeriod.value.credits,
    passed: activePeriod.value.passed,
    failed: activePeriod.value.failed,
    records: [],
  })
  selectedProfile.value.academic.currentClass = promotionForm.nextClass
  selectedProfile.value.academic.academicYear = promotionForm.academicYear
  selectedProfile.value.academic.status = 'Active'
  closePromotionModal()
}

const onWizardPhotoChange = (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  wizardPhotoPreview.value = URL.createObjectURL(file)
  newStudent.personal.photo = wizardPhotoPreview.value
}

const addRadarEntry = (groupKey) => {
  const section = newStudent.radar[groupKey]
  if (!section || !Array.isArray(section.entries)) {
    newStudent.radar[groupKey] = { entries: [] }
  }

  if (groupKey === 'academic') {
    newStudent.radar[groupKey].entries.push({ notes: '', proofFile: null, previewUrl: '' })
  } else if (groupKey === 'certificate') {
    newStudent.radar[groupKey].entries.push({ trainingName: '', proofName: '', proofFile: null })
  } else if (groupKey === 'numerique') {
    newStudent.radar[groupKey].entries.push({
      projectName: '',
      projectDescription: '',
      proofName: '',
      proofFile: null,
    })
  } else if (groupKey === 'langue') {
    newStudent.radar[groupKey].entries.push({ languages: '' })
  } else if (groupKey === 'stage') {
    newStudent.radar[groupKey].entries.push({
      company: '',
      duration: '',
      description: '',
      proofName: '',
      proofFile: null,
    })
  }
}

const getRadarSummary = (groupKey) => {
  const entries = newStudent.radar[groupKey]?.entries || []
  if (!entries.length) return 'Not provided'

  if (groupKey === 'academic') {
    return entries.map((entry) => entry.notes || 'Academic record').join(' • ') || 'Not provided'
  }
  if (groupKey === 'certificate') {
    return entries.map((entry) => entry.trainingName || 'Certificate').join(' • ') || 'Not provided'
  }
  if (groupKey === 'numerique') {
    return entries.map((entry) => entry.projectName || 'Project').join(' • ') || 'Not provided'
  }
  if (groupKey === 'langue') {
    return entries.map((entry) => entry.languages || 'Language').join(' • ') || 'Not provided'
  }
  return entries.map((entry) => entry.company || 'Stage').join(' • ') || 'Not provided'
}

const onRadarUpload = (groupKey, event, index = 0) => {
  const file = event.target.files?.[0]
  if (!file) return

  const entry = newStudent.radar[groupKey]?.entries?.[index]
  if (!entry) return

  if (groupKey === 'academic') {
    entry.bulletinPhoto = URL.createObjectURL(file)
    return
  }

  if (groupKey === 'certificate') {
    entry.proofName = file.name
    entry.proofFile = URL.createObjectURL(file)
    return
  }

  if (groupKey === 'numerique') {
    entry.proofName = file.name
    entry.proofFile = URL.createObjectURL(file)
    return
  }

  if (groupKey === 'stage') {
    entry.proofName = file.name
    entry.proofFile = URL.createObjectURL(file)
  }
}

const resetWizardRecord = () => {
  Object.assign(wizardRecord, {
    course: '',
    code: '',
    semester: 'Semester 1',
    year: newStudent.academic.academicYear,
    credits: 3,
    ca: 15,
    exam: 17,
    total: 32,
    grade: 'A',
    gradeLetter: 'A',
    status: 'Passed',
    instructor: '',
    remarks: '',
  })
  wizardRecordSync()
}

// const addWizardCourse = () => {
//   if (!wizardRecord.course) return
//   newStudent.records.push({
//     id: Date.now(),
//     course: wizardRecord.course,
//     code: wizardRecord.code,
//     semester: wizardRecord.semester,
//     year: wizardRecord.year,
//     credits: wizardRecord.credits,
//     ca: wizardRecord.ca,
//     exam: wizardRecord.exam,
//     total: wizardRecord.total,
//     grade: wizardRecord.grade,
//     gradeLetter: wizardRecord.gradeLetter,
//     status: wizardRecord.status,
//     instructor: wizardRecord.instructor,
//     remarks: wizardRecord.remarks,
//   })
//   resetWizardRecord()
// }

// const removeWizardCourse = (id) => {
//   newStudent.records = newStudent.records.filter((record) => record.id !== id)
// }

const buildProfileFromWizard = (student) => {
  const academicEntries = student.radar?.academic?.entries || []
  const certificateEntries = student.radar?.certificate?.entries || []
  const numeriqueEntries = student.radar?.numerique?.entries || []
  const langueEntries = student.radar?.langue?.entries || []
  const stageEntries = student.radar?.stage?.entries || []

  const firstAcademic = academicEntries[0] || {}
  const firstCertificate = certificateEntries[0] || {}
  const firstNumerique = numeriqueEntries[0] || {}
  const firstLangue = langueEntries[0] || {}
  const firstStage = stageEntries[0] || {}

  return {
    photo: student.personal.photo || `https://i.pravatar.cc/240?img=${(Date.now() % 60) + 1}`,
    studentNumber: student.academic.studentId,
    summary: 'New student profile created through the registration wizard.',
    personal: {
      fullName: `${student.personal.firstName} ${student.personal.lastName}`.trim(),
      dob: student.personal.dob,
      gender: student.personal.gender,
      nationality: student.personal.nationality,
      phone: student.personal.phone,
      address: student.personal.address,
    },
    academic: {
      program: student.academic.program,
      faculty: student.academic.faculty,
      department: student.academic.department,
      currentClass: student.academic.currentClass,
      academicYear: student.academic.academicYear,
      status: student.academic.status,
      advisor: 'Assigned later',
      gpa: '0.00/20',
      credits: '0/0',
      passedCourses: 0,
      failedCourses: 0,
    },
    skills: [
      firstAcademic.notes ? 'Académique' : '',
      firstCertificate.trainingName ? 'Certificat' : '',
      firstNumerique.projectName ? 'Numérique' : '',
      firstLangue.languages ? 'Langue' : '',
      firstStage.company ? 'Stage' : '',
    ].filter(Boolean),
    languages: firstLangue.languages
      ? firstLangue.languages
          .split(';')
          .map((item) => item.trim())
          .filter(Boolean)
          .map((item) => ({
            name: item.split(':')[0].trim(),
            level: item.split(':')[1]?.trim() || 'Recorded',
          }))
      : [],
    projects: firstNumerique.projectName
      ? [{ title: firstNumerique.projectName, description: firstNumerique.projectDescription }]
      : [],
    assignments: [],
    certificates: certificateEntries.map((entry) => entry.trainingName).filter(Boolean),
    experiences: stageEntries
      .filter((entry) => entry.company)
      .map((entry) => ({
        title: 'Stage',
        company: entry.company,
        date: entry.duration || 'N/A',
      })),
    achievements: [],
    documents: [
      ...academicEntries
        .filter((entry) => entry.bulletinPhoto)
        .map(() => ({ name: 'Bulletin.pdf', type: 'Academic proof' })),
      ...certificateEntries
        .filter((entry) => entry.proofFile)
        .map((entry) => ({
          name: entry.proofName || 'Certificate proof',
          type: 'Certificate proof',
        })),
      ...numeriqueEntries
        .filter((entry) => entry.proofFile)
        .map((entry) => ({ name: entry.proofName || 'Project proof', type: 'Project proof' })),
      ...stageEntries
        .filter((entry) => entry.proofFile)
        .map((entry) => ({ name: entry.proofName || 'Stage proof', type: 'Internship proof' })),
    ],
    periods: [
      {
        className: student.academic.currentClass,
        year: student.academic.academicYear,
        status: 'Active',
        gpa: '0.00/20',
        credits: '0/0',
        passed: 0,
        failed: 0,
        records: [...student.records],
      },
    ],
  }
}

const createStudent = async () => {
  const firstAcademic = newStudent.radar.academic.entries[0] || {}
  const firstCertificate = newStudent.radar.certificate.entries[0] || {}
  const firstNumerique = newStudent.radar.numerique.entries[0] || {}
  const firstLangue = newStudent.radar.langue.entries[0] || {}
  const firstStage = newStudent.radar.stage.entries[0] || {}

  const skills = [
    firstAcademic.notes ? 'Académique' : '',
    firstCertificate.trainingName ? 'Certificat' : '',
    firstNumerique.projectName ? 'Numérique' : '',
    firstLangue.languages ? 'Langue' : '',
    firstStage.company ? 'Stage' : '',
  ].filter(Boolean)

  const languages = firstLangue.languages
    ? firstLangue.languages
        .split(';')
        .map((item) => item.trim())
        .filter(Boolean)
        .map((item) => ({
          name: item.split(':')[0].trim(),
          level: item.split(':')[1]?.trim() || 'Recorded',
        }))
    : []

  const bio = {
    email: newStudent.personal.email,
    phone: newStudent.personal.phone,
    program: newStudent.academic.program,
    level: newStudent.academic.currentClass,
    currentClass: newStudent.academic.currentClass,
    status: newStudent.academic.status,
    enrollmentYear: newStudent.academic.academicYear,
    academicYear: newStudent.academic.academicYear,
    faculty: newStudent.academic.faculty,
    department: newStudent.academic.department,
    studentId: newStudent.academic.studentId,
    nationality: newStudent.personal.nationality,
    address: newStudent.personal.address,
    admissionDate: newStudent.academic.admissionDate,
    summary: 'Student profile created through registration wizard.',
    projects: firstNumerique.projectName
      ? [{ title: firstNumerique.projectName, description: firstNumerique.projectDescription }]
      : [],
    academicRecords: newStudent.records,
    languages,
    radar: {
      academic: newStudent.radar.academic,
      certificate: newStudent.radar.certificate,
      numerique: newStudent.radar.numerique,
      langue: newStudent.radar.langue,
      stage: newStudent.radar.stage,
    },
  }

  const payload = {
    fullName: `${newStudent.personal.firstName} ${newStudent.personal.lastName}`.trim(),
    gpa: '0.00',
    gender: newStudent.personal.gender,
    profileUrl: '',
    profileCompletion: 20,
    email: newStudent.personal.email,
    phone: newStudent.personal.phone,
    password: newStudent.personal.password || undefined,
    universityId: connectedUniversityId.value,
    createLinkedUser: true,
    skills,
    bio: JSON.stringify(bio),
  }

  let createdStudent = null
  try {
    createdStudent = await studentStore.createStudent(payload)
    await studentStore.fetchStudents()
  } catch (error) {
    const localId = Date.now()
    studentStore.students.unshift({ id: localId, ...payload, ...bio })
    profileCatalog[localId] = buildProfileFromWizard(newStudent)
    console.error('Failed to create student on server, added locally:', error)
  }

  const matchedStudent = createdStudent
    ? studentStore.students.find((student) => student.id === createdStudent.id)
    : studentStore.students.find(
        (student) => student.email === payload.email || student.fullName === payload.fullName,
      )
  if (matchedStudent) {
    selectedStudentId.value = matchedStudent.id
    if (!profileCatalog[matchedStudent.id]) {
      profileCatalog[matchedStudent.id] = buildProfileFromWizard(newStudent)
    }
  } else {
    const localId = Date.now()
    const localStudent = { id: localId, ...payload }
    studentStore.students.unshift(localStudent)
    profileCatalog[localId] = buildProfileFromWizard(newStudent)
    selectedStudentId.value = localId
  }

  wizardStep.value = 1
  selectorOpen.value = false
  router.push({ name: 'listStudents' })
}

onMounted(async () => {
  const universityId = connectedUniversityId.value
  if (universityId) {
    await studentStore.fetchStudentsByUniversity(universityId)
  } else {
    await studentStore.fetchStudents()
  }
  if (!selectedStudentId.value) {
    selectedStudentId.value = defaultSelectedId.value
  }
  if (studentStore.students.length) {
    selectedStudentId.value = selectedStudentId.value || studentStore.students[0].id
  }
  resetWizardRecord()
})
</script>

<style scoped>
.student-workspace-page {
  display: grid;
  gap: 20px;
  width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--primary);
  font-size: 0.78rem;
  font-weight: 700;
}

.page-header h1,
.selector-header h2,
.workspace-card h3,
.wizard-card h2 {
  margin: 0;
  color: var(--text);
}

.page-copy,
.muted,
.selector-header p,
.workspace-card p,
.wizard-card p {
  color: var(--muted);
}

.header-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.primary-btn,
.secondary-btn,
.ghost-btn,
.filter-btn,
.mini-btn,
.icon-btn {
  border: none;
  border-radius: 14px;
  cursor: pointer;
}

.primary-btn {
  padding: 12px 18px;
  background: var(--primary);
  color: white;
  display: inline-flex;
  gap: 8px;
  align-items: center;
}

.secondary-btn,
.filter-btn,
.ghost-btn {
  padding: 12px 16px;
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
}

.ghost-btn {
  background: rgba(6, 170, 197, 0.08);
  border-color: rgba(6, 170, 197, 0.2);
  color: var(--primary);
}

.icon-btn {
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  background: transparent;
  color: var(--text);
}

.workspace-layout {
  display: grid;
  grid-template-columns: 320px minmax(0, 1fr) 420px;
  gap: 20px;
  align-items: start;
}

.student-selector,
.student-workspace,
.registration-wizard {
  min-width: 0;
}

.student-selector,
.workspace-card,
.modal-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 24px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.student-selector {
  padding: 18px;
  display: grid;
  gap: 16px;
}

.selector-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.mobile-only {
  display: none;
}

.search-block {
  display: flex;
  gap: 10px;
  align-items: center;
}

.search-input {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 16px;
  border: 1px solid var(--border);
  background: var(--surface-soft);
}

.search-input input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
}

.filters-panel {
  border: 1px solid var(--border);
  border-radius: 18px;
  padding: 14px;
  background: var(--surface-soft);
}

.filter-grid,
.form-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.filter-grid label,
.form-grid label,
.record-form label {
  display: grid;
  gap: 8px;
}

.filter-grid span,
.form-grid span,
.record-form span {
  color: var(--muted);
  font-size: 0.9rem;
}

.filter-grid select,
.filter-grid input,
.form-grid select,
.form-grid input,
.form-grid textarea,
.record-form select,
.record-form input,
.record-form textarea {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 14px;
  background: var(--surface);
  color: var(--text);
  padding: 12px 14px;
}

.filters-panel,
.form-grid .full,
.record-form .full {
  grid-column: 1 / -1;
}

.password-field {
  display: flex;
  align-items: center;
  gap: 8px;
}

.password-field input {
  flex: 1;
}

.field-hint {
  color: var(--muted);
  font-size: 0.78rem;
}

.student-list {
  display: grid;
  gap: 10px;
  max-height: 78vh;
  overflow: auto;
  padding-right: 2px;
}

.student-item {
  width: 100%;
  display: grid;
  grid-template-columns: 44px minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
  text-align: left;
  padding: 12px;
  border: 1px solid transparent;
  border-radius: 18px;
  background: var(--surface-soft);
  cursor: pointer;
}

.student-item.active {
  border-color: rgba(6, 170, 197, 0.4);
  background: rgba(6, 170, 197, 0.08);
}

.student-item img,
.student-summary img {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  object-fit: cover;
}

.student-meta {
  min-width: 0;
  display: grid;
  gap: 2px;
}

.student-meta strong,
.record-main strong,
.history-card strong,
.simple-list strong {
  color: var(--text);
}

.student-meta small,
.student-meta span,
.record-main small,
.record-main span,
.simple-list span,
.history-card p,
.history-card small {
  color: var(--muted);
}

.status-pill {
  padding: 8px 12px;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-pill.active,
.status-pill.archived,
.status-pill.suspended {
  background: rgba(15, 23, 42, 0.05);
}

.status-pill.active {
  color: #0f766e;
  background: rgba(20, 184, 166, 0.14);
}

.status-pill.archived {
  color: #92400e;
  background: rgba(234, 179, 8, 0.16);
}

.status-pill.suspended {
  color: #b91c1c;
  background: rgba(239, 68, 68, 0.12);
}

.empty-list,
.empty-list,
.empty-list {
  padding: 18px;
  border-radius: 16px;
  text-align: center;
  color: var(--muted);
  background: var(--surface-soft);
}

.student-workspace {
  display: grid;
  gap: 16px;
}

.workspace-card {
  padding: 20px;
}

.hero-card {
  display: grid;
  gap: 18px;
}

.student-summary {
  display: flex;
  align-items: center;
  gap: 16px;
}

.student-summary h2 {
  margin: 0 0 4px;
}

.student-summary p {
  margin: 0;
}

.summary-tags,
.chip-row,
.tab-scroll {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.summary-tags span,
.chip-row span,
.period-pill {
  background: rgba(6, 170, 197, 0.12);
  color: var(--primary);
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 0.9rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
}

.stats-grid.small {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.stats-grid article,
.progression-card,
.summary-box {
  border-radius: 18px;
  background: var(--surface-soft);
  padding: 14px;
}

.stats-grid span,
.progression-card span,
.summary-box span {
  display: block;
  color: var(--muted);
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.stats-grid strong,
.progression-card strong,
.summary-box strong {
  display: block;
  margin-top: 8px;
  font-size: 1.4rem;
  color: var(--text);
}

.tab-scroll {
  overflow-x: auto;
  scrollbar-width: none;
}

.tab-scroll::-webkit-scrollbar {
  display: none;
}

.tab-btn {
  border: none;
  background: transparent;
  color: var(--muted);
  padding: 10px 14px;
  border-radius: 12px;
  cursor: pointer;
  white-space: nowrap;
}

.tab-btn.active {
  background: var(--primary);
  color: white;
}

.workspace-content {
  min-width: 0;
}

.content-grid,
.content-stack {
  display: grid;
  gap: 16px;
}

.two-col {
  grid-template-columns: 1fr 320px;
}

.detail-list,
.snapshot-list,
.simple-list,
.history-list,
.record-cards,
.progression-grid,
.review-grid,
.record-detail-grid {
  display: grid;
  gap: 12px;
}

.detail-list div,
.snapshot-list div,
.simple-list div,
.record-detail-grid div {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid var(--border);
}

.detail-list.compact div {
  padding: 8px 0;
}

.detail-list span,
.snapshot-list span,
.record-detail-grid span,
.simple-list span {
  color: var(--muted);
}

.record-form {
  display: grid;
  gap: 14px;
}

.button-row,
.wizard-footer,
.modal-footer,
.record-actions,
.history-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.record-cards {
  max-height: 340px;
  overflow: auto;
}

.record-card,
.history-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 90px auto;
  gap: 12px;
  align-items: center;
  padding: 14px;
  border-radius: 18px;
  background: var(--surface-soft);
}

.record-main {
  display: grid;
  gap: 4px;
}

.record-score {
  text-align: center;
}

.record-score strong {
  font-size: 1.4rem;
}

.mini-btn {
  padding: 9px 12px;
  background: var(--surface);
  border: 1px solid var(--border);
  color: var(--text);
}

.mini-btn.danger {
  color: #b91c1c;
}

.progression-grid,
.review-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.progression-card.current {
  background: rgba(6, 170, 197, 0.1);
}

.progression-card p,
.summary-box p,
.review-grid p {
  margin: 8px 0 0;
}

.mini-stats {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin: 12px 0;
}

.mini-stats span {
  padding: 6px 10px;
  border-radius: 999px;
  background: var(--surface);
  color: var(--text);
}

.history-card.archived {
  opacity: 0.9;
}

.wizard-card {
  display: grid;
  gap: 16px;
}

.stepper {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 8px;
}

.step-item {
  border: 1px solid var(--border);
  border-radius: 16px;
  background: var(--surface-soft);
  padding: 10px;
  text-align: left;
  display: grid;
  gap: 6px;
}

.step-item span {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: var(--surface);
  color: var(--primary);
  font-weight: 700;
}

.step-item.active {
  border-color: rgba(6, 170, 197, 0.4);
  background: rgba(6, 170, 197, 0.08);
}

.step-item small {
  color: var(--muted);
}

.wizard-body {
  min-height: 620px;
}

.wizard-step {
  display: grid;
  gap: 16px;
}

.wizard-photo {
  display: grid;
  justify-items: center;
  gap: 12px;
}

.wizard-photo img {
  width: 92px;
  height: 92px;
  border-radius: 50%;
  object-fit: cover;
  border: 5px solid white;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}

.radar-records {
  display: grid;
  gap: 16px;
}

.radar-record-card {
  background: linear-gradient(180deg, rgba(6, 170, 197, 0.04), rgba(255, 255, 255, 0.9));
  border: 1px solid var(--border);
  border-radius: 22px;
  padding: 18px;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
}

.record-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
}

.record-section-header h4 {
  margin: 0;
  color: var(--text);
}

.record-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 10px;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 700;
  margin-right: 10px;
}

.record-entry {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  padding: 16px;
  margin-top: 12px;
}

.entry-form {
  display: grid;
  gap: 12px;
}

.preview-image {
  max-width: 180px;
  border-radius: 14px;
  object-fit: cover;
  border: 1px solid var(--border);
}

.small-btn {
  padding: 8px 12px;
  font-size: 0.82rem;
}

.full {
  grid-column: 1 / -1;
}

.compact-table {
  max-height: 240px;
}

.review-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.review-grid > div {
  padding: 16px;
  border-radius: 18px;
  background: var(--surface-soft);
}

.review-grid h4 {
  margin: 0 0 8px;
  color: var(--text);
}

.wizard-footer {
  justify-content: space-between;
  align-items: center;
}

.overlay {
  position: fixed;
  inset: 0;
  background: rgba(4, 33, 44, 0.45);
  z-index: 40;
}

.modal-card {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: min(760px, 92vw);
  z-index: 50;
  padding: 18px;
}

.modal-header,
.modal-body,
.modal-footer {
  display: grid;
  gap: 12px;
}

.modal-header,
.section-header-row,
.table-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.old-new-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.summary-box.active {
  background: rgba(20, 184, 166, 0.12);
}

.summary-box.archived {
  background: rgba(234, 179, 8, 0.14);
}

.record-detail-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.record-detail-grid .full {
  grid-column: 1 / -1;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 1300px) {
  .workspace-layout {
    grid-template-columns: 300px minmax(0, 1fr);
  }

  .registration-wizard {
    grid-column: 1 / -1;
  }
}

@media (max-width: 1100px) {
  .workspace-layout {
    grid-template-columns: 1fr;
  }

  .student-selector {
    position: fixed;
    inset: 0 auto 0 0;
    width: min(88vw, 340px);
    transform: translateX(-105%);
    transition: transform 0.25s ease;
    z-index: 35;
    border-radius: 0 24px 24px 0;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
    max-height: 100vh;
    overflow: auto;
  }

  .student-selector.open {
    transform: translateX(0);
  }

  .mobile-only {
    display: grid;
  }

  .workspace-layout::before {
    content: '';
  }

  .student-workspace,
  .registration-wizard {
    width: 100%;
  }

  .student-list {
    max-height: none;
  }

  .page-header,
  .header-actions,
  .search-block,
  .record-section-header,
  .section-header-row,
  .table-header-row,
  .wizard-footer,
  .modal-header {
    flex-direction: column;
    align-items: stretch;
  }

  .header-actions > * {
    width: 100%;
    justify-content: center;
  }

  .two-col,
  .progression-grid,
  .review-grid,
  .record-detail-grid,
  .compact,
  .stats-grid,
  .stats-grid.small,
  .old-new-grid,
  .filter-grid,
  .form-grid {
    grid-template-columns: 1fr;
  }

  .student-summary {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 720px) {
  .page-header {
    flex-direction: column;
  }

  .search-block {
    flex-direction: column;
    align-items: stretch;
  }

  .student-item {
    grid-template-columns: 44px minmax(0, 1fr);
  }

  .student-item .status-pill {
    grid-column: 1 / -1;
    justify-self: start;
  }

  .record-card,
  .history-card,
  .record-entry {
    grid-template-columns: 1fr;
  }

  .wizard-body {
    min-height: auto;
  }

  .modal-card {
    width: calc(100vw - 24px);
  }

  .stepper {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .record-section-header {
    align-items: flex-start;
  }

  .small-btn {
    width: 100%;
  }

  .preview-image {
    max-width: 100%;
  }
}
</style>
