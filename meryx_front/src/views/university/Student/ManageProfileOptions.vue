<template>
  <div class="manage-options-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Settings / Profiles</p>
        <h1>Manage profile section options</h1>
        <p class="page-copy">
          Create, edit, or remove option items used across student profiles (skills, languages,
          certificates, document types, etc.).
        </p>
      </div>
      <div>
        <button class="btn btn-primary" @click="openAddModal">Add new option</button>
      </div>
    </header>

    <!-- Search and select a student to assign options or add academic records -->
    <div class="student-selection">
      <div class="student-search">
        <input
          type="search"
          v-model="studentQuery"
          placeholder="Search students by name, program, or email"
        />
      </div>
      <ul class="student-list">
        <li
          v-for="s in filteredStudents"
          :key="s.id"
          :class="{ selected: s.id === selectedStudentId }"
          @click="selectStudent(s.id)"
        >
          <div class="student-primary">
            <strong>{{ s.fullName }}</strong>
            <small class="muted">{{ s.program }} • {{ s.level }}</small>
          </div>
          <div class="student-meta">
            <small class="muted">{{ s.email }}</small>
          </div>
        </li>
        <li v-if="!filteredStudents.length" class="empty">No students found.</li>
      </ul>
    </div>

    <div class="options-layout">
      <aside class="options-nav">
        <ul>
          <li
            v-for="section in sections"
            :key="section.key"
            :class="{ active: section.key === active }"
          >
            <button type="button" @click="selectSection(section.key)">
              <i :class="section.icon"></i>
              {{ section.label }}
            </button>
          </li>
        </ul>
      </aside>

      <main class="options-main">
        <div class="workspace">
          <div class="workspace-header sticky-top bg-white p-3">
            <div class="d-flex align-items-start gap-3">
              <div>
                <h2 class="h5 mb-1">{{ workspaceTitle }}</h2>
                <p class="mb-0 text-muted small">{{ workspaceDescription }}</p>
              </div>
              <div class="ms-auto d-flex gap-2 align-items-center">
                <span v-if="loading" class="text-muted small">
                  <i class="bi bi-hourglass-split me-1"></i>Loading...
                </span>
                <button
                  v-if="showReset"
                  class="btn btn-sm btn-outline-secondary"
                  @click="resetWorkspace"
                >
                  <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
              </div>
            </div>
          </div>

          <transition name="fade" mode="out-in">
            <section :key="active" class="workspace-body p-3">
              <!-- Academic -->
              <div v-if="active === 'academic'">
                <div class="card mb-3 shadow-sm rounded-3">
                  <div class="card-body">
                    <form @submit.prevent="saveAcademic">
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <label class="form-label">Program / Course Name</label>
                          <input
                            v-model="academicForm.course"
                            type="text"
                            class="form-control"
                            required
                            placeholder="JavaScript Programming"
                          />
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label">Course Code (optional)</label>
                          <input
                            v-model="academicForm.code"
                            type="text"
                            class="form-control"
                            placeholder="JS-101"
                          />
                        </div>

                        <div class="col-12 col-md-4">
                          <label class="form-label">Semester</label>
                          <input
                            v-model="academicForm.semester"
                            type="text"
                            class="form-control"
                            placeholder="Fall"
                          />
                        </div>
                        <div class="col-12 col-md-4">
                          <label class="form-label">Academic Year</label>
                          <input
                            v-model="academicForm.year"
                            type="text"
                            class="form-control"
                            placeholder="2025/2026"
                          />
                        </div>
                        <div class="col-6 col-md-2">
                          <label class="form-label">Credits</label>
                          <input
                            v-model.number="academicForm.credits"
                            type="number"
                            min="0"
                            class="form-control"
                          />
                        </div>
                        <div class="col-6 col-md-2">
                          <label class="form-label">Mark / Score</label>
                          <input
                            v-model.number="academicForm.mark"
                            @input="computeGrade"
                            type="number"
                            min="0"
                            max="100"
                            class="form-control"
                            placeholder="15"
                          />
                        </div>

                        <div class="col-12 col-md-4">
                          <label class="form-label">Grade</label>
                          <select v-model="academicForm.grade" class="form-select">
                            <option value="">Auto / Select</option>
                            <option v-for="g in grades" :key="g" :value="g">{{ g }}</option>
                          </select>
                        </div>
                        <div class="col-12 col-md-4">
                          <label class="form-label">Status</label>
                          <select v-model="academicForm.status" class="form-select">
                            <option>Passed</option>
                            <option>Failed</option>
                            <option>In Progress</option>
                          </select>
                        </div>
                        <div class="col-12 col-md-4">
                          <label class="form-label">Instructor</label>
                          <input
                            v-model="academicForm.instructor"
                            type="text"
                            class="form-control"
                          />
                        </div>

                        <div class="col-12">
                          <label class="form-label">Remarks</label>
                          <textarea
                            v-model="academicForm.remarks"
                            rows="2"
                            class="form-control"
                          ></textarea>
                        </div>
                      </div>

                      <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                          <i class="bi bi-plus-circle me-1"></i>
                          {{ academicEditId ? 'Save Record' : 'Add Record' }}
                        </button>
                        <button
                          type="button"
                          class="btn btn-outline-secondary"
                          @click="resetAcademic"
                        >
                          Reset
                        </button>
                        <div class="ms-auto"></div>
                      </div>
                    </form>
                  </div>
                </div>

                <div class="card shadow-sm rounded-3">
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover mb-0">
                        <thead class="table-light small">
                          <tr>
                            <th>Course</th>
                            <th>Semester</th>
                            <th>Year</th>
                            <th>Mark</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="r in academicRecords" :key="r.id">
                            <td>
                              {{ r.course }}
                              <div class="text-muted small">{{ r.code }}</div>
                            </td>
                            <td>{{ r.semester }}</td>
                            <td>{{ r.year }}</td>
                            <td>{{ r.mark }}</td>
                            <td>{{ r.grade }}</td>
                            <td>{{ r.status }}</td>
                            <td class="text-end">
                              <button
                                class="btn btn-sm btn-outline-secondary me-1"
                                @click="viewAcademic(r)"
                              >
                                <i class="bi bi-eye"></i>
                              </button>
                              <button
                                class="btn btn-sm btn-outline-primary me-1"
                                @click="editAcademic(r)"
                              >
                                <i class="bi bi-pencil"></i>
                              </button>
                              <button
                                class="btn btn-sm btn-outline-secondary"
                                @click="deleteAcademic(r.id)"
                              >
                                <i class="bi bi-trash"></i>
                              </button>
                            </td>
                          </tr>
                          <tr v-if="!academicRecords.length">
                            <td colspan="7" class="text-center text-muted py-3">
                              No academic records.
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Skills -->
              <div v-else-if="active === 'skills'">
                <div class="card mb-3 shadow-sm rounded-3">
                  <div class="card-body">
                    <form @submit.prevent="saveSkill">
                      <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-5">
                          <label class="form-label">Skill Name</label>
                          <input
                            v-model="skillForm.name"
                            type="text"
                            class="form-control"
                            required
                            placeholder="JavaScript"
                          />
                        </div>
                        <div class="col-12 col-md-3">
                          <label class="form-label">Level</label>
                          <select v-model="skillForm.level" class="form-select">
                            <option>Beginner</option>
                            <option>Intermediate</option>
                            <option>Advanced</option>
                            <option>Expert</option>
                          </select>
                        </div>
                        <div class="col-12 col-md-2">
                          <label class="form-label">Category</label>
                          <select v-model="skillForm.category" class="form-select">
                            <option>Programming</option>
                            <option>Framework</option>
                            <option>Database</option>
                            <option>Soft Skill</option>
                          </select>
                        </div>
                        <div class="col-12 col-md-2 text-end">
                          <button class="btn btn-primary w-100" type="submit">
                            <i class="bi bi-plus-circle me-1"></i> Add Skill
                          </button>
                        </div>
                        <div class="col-12">
                          <label class="form-label">Description (optional)</label>
                          <input v-model="skillForm.desc" type="text" class="form-control" />
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <div class="row g-3">
                  <div v-for="s in skills" :key="s.id" class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 rounded-3 shadow-sm skill-card">
                      <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start">
                          <h6 class="mb-1">{{ s.name }}</h6>
                          <span class="badge bg-info text-dark">{{ s.level }}</span>
                        </div>
                        <div class="text-muted small mb-2">{{ s.category }}</div>
                        <p class="small text-muted mb-3">{{ s.desc }}</p>
                        <div class="mt-auto d-flex gap-2">
                          <button class="btn btn-sm btn-outline-primary" @click="editSkill(s)">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button
                            class="btn btn-sm btn-outline-secondary"
                            @click="deleteSkill(s.id)"
                          >
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="!skills.length" class="col-12 text-center text-muted">
                    No skills added yet.
                  </div>
                </div>
              </div>

              <!-- Languages -->
              <div v-else-if="active === 'languages'">
                <div class="card mb-3 shadow-sm rounded-3 p-3">
                  <form @submit.prevent="saveLanguage">
                    <div class="row g-3">
                      <div class="col-12 col-md-4">
                        <label class="form-label">Language</label>
                        <input v-model="languageForm.language" class="form-control" required />
                      </div>
                      <div class="col-6 col-md-2">
                        <label class="form-label">Speaking</label>
                        <select v-model="languageForm.speaking" class="form-select">
                          <option>Basic</option>
                          <option>Conversational</option>
                          <option>Fluent</option>
                        </select>
                      </div>
                      <div class="col-6 col-md-2">
                        <label class="form-label">Reading</label>
                        <select v-model="languageForm.reading" class="form-select">
                          <option>Basic</option>
                          <option>Conversational</option>
                          <option>Fluent</option>
                        </select>
                      </div>
                      <div class="col-6 col-md-2">
                        <label class="form-label">Writing</label>
                        <select v-model="languageForm.writing" class="form-select">
                          <option>Basic</option>
                          <option>Conversational</option>
                          <option>Fluent</option>
                        </select>
                      </div>
                      <div class="col-6 col-md-2">
                        <label class="form-label">Listening</label>
                        <select v-model="languageForm.listening" class="form-select">
                          <option>Basic</option>
                          <option>Conversational</option>
                          <option>Fluent</option>
                        </select>
                      </div>
                      <div class="col-12 col-md-3">
                        <label class="form-label">CEFR Level</label>
                        <input v-model="languageForm.cefr" class="form-control" placeholder="B2" />
                      </div>
                      <div class="col-12 col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">Add Language</button>
                      </div>
                    </div>
                  </form>
                </div>

                <div class="d-flex flex-wrap gap-3">
                  <div
                    v-for="l in languages"
                    :key="l.id"
                    class="language-card p-3 rounded-3 shadow-sm"
                  >
                    <div class="d-flex justify-content-between align-items-start">
                      <h6 class="mb-1">{{ l.language }}</h6>
                      <div class="text-muted small">{{ l.cefr }}</div>
                    </div>
                    <div class="small text-muted">
                      S: {{ l.speaking }} • R: {{ l.reading }} • W: {{ l.writing }} • L:
                      {{ l.listening }}
                    </div>
                    <div class="mt-2 d-flex gap-2">
                      <button class="btn btn-sm btn-outline-primary" @click="editLanguage(l)">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        @click="deleteLanguage(l.id)"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>
                  <div v-if="!languages.length" class="text-center text-muted w-100">
                    No languages added.
                  </div>
                </div>
              </div>

              <!-- Projects (cards) -->
              <div v-else-if="active === 'projects'">
                <div class="card mb-3 shadow-sm rounded-3 p-3">
                  <form @submit.prevent="saveProject">
                    <div class="row g-3">
                      <div class="col-12 col-md-6">
                        <label class="form-label">Project Name</label>
                        <input v-model="projectForm.name" class="form-control" required />
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label">Role</label>
                        <input v-model="projectForm.role" class="form-control" />
                      </div>
                      <div class="col-12">
                        <label class="form-label">Technologies Used</label>
                        <input
                          v-model="projectForm.tech"
                          class="form-control"
                          placeholder="Vue, Bootstrap, Node"
                        />
                      </div>
                      <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea
                          v-model="projectForm.desc"
                          rows="2"
                          class="form-control"
                        ></textarea>
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">Start Date</label
                        ><input v-model="projectForm.start" type="date" class="form-control" />
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">End Date</label
                        ><input v-model="projectForm.end" type="date" class="form-control" />
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">GitHub URL</label
                        ><input v-model="projectForm.github" type="url" class="form-control" />
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">Live Demo URL</label
                        ><input v-model="projectForm.demo" type="url" class="form-control" />
                      </div>
                      <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Add Project</button>
                        <button
                          class="btn btn-outline-secondary"
                          type="button"
                          @click="resetProject"
                        >
                          Reset
                        </button>
                      </div>
                    </div>
                  </form>
                </div>

                <div class="row g-3">
                  <div v-for="p in projects" :key="p.id" class="col-12 col-md-6">
                    <div class="card rounded-3 shadow-sm h-100">
                      <div class="card-body d-flex flex-column">
                        <h6>{{ p.name }}</h6>
                        <div class="text-muted small">Role: {{ p.role }} • Tech: {{ p.tech }}</div>
                        <p class="small text-muted mt-2">{{ p.desc }}</p>
                        <div class="mt-auto d-flex gap-2">
                          <a
                            :href="p.github"
                            target="_blank"
                            v-if="p.github"
                            class="btn btn-sm btn-outline-secondary"
                            >GitHub</a
                          >
                          <a
                            :href="p.demo"
                            target="_blank"
                            v-if="p.demo"
                            class="btn btn-sm btn-outline-secondary"
                            >Live</a
                          >
                          <button
                            class="btn btn-sm btn-outline-primary ms-auto"
                            @click="editProject(p)"
                          >
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button
                            class="btn btn-sm btn-outline-secondary"
                            @click="deleteProject(p.id)"
                          >
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="!projects.length" class="col-12 text-center text-muted">
                    No projects yet.
                  </div>
                </div>
              </div>

              <!-- Certificates -->
              <div v-else-if="active === 'certificates'">
                <div class="card mb-3 shadow-sm rounded-3 p-3">
                  <form @submit.prevent="saveCertificate">
                    <div class="row g-3">
                      <div class="col-12 col-md-6">
                        <label class="form-label">Certificate Name</label
                        ><input v-model="certForm.name" class="form-control" required />
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label">Issuer</label
                        ><input v-model="certForm.issuer" class="form-control" />
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">Issue Date</label
                        ><input v-model="certForm.issued" type="date" class="form-control" />
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">Expiry Date</label
                        ><input v-model="certForm.expiry" type="date" class="form-control" />
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label">Credential ID</label
                        ><input v-model="certForm.credId" class="form-control" />
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label">Verification Link</label
                        ><input v-model="certForm.link" type="url" class="form-control" />
                      </div>
                      <div class="col-12">
                        <label class="form-label">Upload PDF</label
                        ><input
                          @change="onCertUpload"
                          accept="application/pdf"
                          type="file"
                          class="form-control"
                        />
                      </div>
                      <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Add Certificate</button
                        ><button
                          class="btn btn-outline-secondary"
                          type="button"
                          @click="resetCertificate"
                        >
                          Reset
                        </button>
                      </div>
                    </div>
                  </form>
                </div>

                <div class="row g-3">
                  <div v-for="c in certificates" :key="c.id" class="col-12 col-md-6">
                    <div
                      class="card rounded-3 shadow-sm p-3 d-flex flex-row gap-3 align-items-center"
                    >
                      <div class="flex-grow-1">
                        <h6 class="mb-0">{{ c.name }}</h6>
                        <div class="small text-muted">{{ c.issuer }} • {{ c.issued }}</div>
                        <div class="text-muted small">ID: {{ c.credId }}</div>
                      </div>
                      <div class="d-flex gap-2">
                        <a
                          v-if="c.fileUrl"
                          :href="c.fileUrl"
                          target="_blank"
                          class="btn btn-sm btn-outline-secondary"
                          ><i class="bi bi-file-earmark-pdf"></i
                        ></a>
                        <a
                          v-if="c.link"
                          :href="c.link"
                          target="_blank"
                          class="btn btn-sm btn-outline-secondary"
                          ><i class="bi bi-link-45deg"></i
                        ></a>
                        <button
                          class="btn btn-sm btn-outline-secondary"
                          @click="deleteCertificate(c.id)"
                        >
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div v-if="!certificates.length" class="col-12 text-center text-muted">
                    No certificates.
                  </div>
                </div>
              </div>

              <!-- Experience -->
              <div v-else-if="active === 'experience'">
                <div class="card mb-3 shadow-sm rounded-3 p-3">
                  <form @submit.prevent="saveExperience">
                    <div class="row g-3">
                      <div class="col-12 col-md-6">
                        <label class="form-label">Company</label
                        ><input v-model="expForm.company" class="form-control" required />
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label">Position</label
                        ><input v-model="expForm.position" class="form-control" />
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">Employment Type</label
                        ><select v-model="expForm.type" class="form-select">
                          <option>Full-time</option>
                          <option>Part-time</option>
                          <option>Contract</option>
                        </select>
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">Start Date</label
                        ><input v-model="expForm.start" type="date" class="form-control" />
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">End Date</label
                        ><input v-model="expForm.end" type="date" class="form-control" />
                      </div>
                      <div class="col-12">
                        <label class="form-label">Responsibilities</label
                        ><textarea v-model="expForm.resp" class="form-control" rows="2"></textarea>
                      </div>
                      <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Add Experience</button
                        ><button
                          class="btn btn-outline-secondary"
                          type="button"
                          @click="resetExperience"
                        >
                          Reset
                        </button>
                      </div>
                    </div>
                  </form>
                </div>

                <div class="list-group">
                  <div
                    v-for="e in experiences"
                    :key="e.id"
                    class="list-group-item d-flex justify-content-between align-items-start"
                  >
                    <div>
                      <h6 class="mb-0">{{ e.position }} — {{ e.company }}</h6>
                      <div class="small text-muted">{{ e.start }} — {{ e.end || 'Present' }}</div>
                      <div class="small text-muted">{{ e.resp }}</div>
                    </div>
                    <div class="d-flex gap-2">
                      <button class="btn btn-sm btn-outline-primary" @click="editExperience(e)">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        @click="deleteExperience(e.id)"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>
                  <div v-if="!experiences.length" class="list-group-item text-center text-muted">
                    No experience entries.
                  </div>
                </div>
              </div>

              <!-- Achievements -->
              <div v-else-if="active === 'achievements'">
                <div class="card mb-3 shadow-sm rounded-3 p-3">
                  <form @submit.prevent="saveAchievement">
                    <div class="row g-3">
                      <div class="col-12 col-md-6">
                        <label class="form-label">Achievement Title</label
                        ><input v-model="achForm.title" class="form-control" required />
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label">Organization</label
                        ><input v-model="achForm.org" class="form-control" />
                      </div>
                      <div class="col-6 col-md-3">
                        <label class="form-label">Award Date</label
                        ><input v-model="achForm.date" type="date" class="form-control" />
                      </div>
                      <div class="col-12">
                        <label class="form-label">Description</label
                        ><textarea v-model="achForm.desc" class="form-control" rows="2"></textarea>
                      </div>
                      <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Add Achievement</button
                        ><button
                          class="btn btn-outline-secondary"
                          type="button"
                          @click="resetAchievement"
                        >
                          Reset
                        </button>
                      </div>
                    </div>
                  </form>
                </div>

                <div class="row g-3">
                  <div v-for="a in achievements" :key="a.id" class="col-12 col-md-6">
                    <div class="card rounded-3 shadow-sm p-3">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <h6 class="mb-1">{{ a.title }}</h6>
                          <div class="small text-muted">{{ a.org }} • {{ a.date }}</div>
                        </div>
                        <div class="d-flex gap-2">
                          <button
                            class="btn btn-sm btn-outline-primary"
                            @click="editAchievement(a)"
                          >
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button
                            class="btn btn-sm btn-outline-secondary"
                            @click="deleteAchievement(a.id)"
                          >
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </div>
                      <p class="small text-muted mt-2">{{ a.desc }}</p>
                    </div>
                  </div>
                  <div v-if="!achievements.length" class="col-12 text-center text-muted">
                    No achievements yet.
                  </div>
                </div>
              </div>

              <!-- Documents -->
              <div v-else-if="active === 'documents'">
                <div class="card mb-3 shadow-sm rounded-3 p-3">
                  <div
                    class="border rounded-3 p-4 text-center drop-zone"
                    @drop.prevent="onDrop"
                    @dragover.prevent
                  >
                    <i class="bi bi-cloud-arrow-up display-6 text-muted"></i>
                    <p class="mb-1">Drag & drop files here or click to upload</p>
                    <small class="text-muted">Supported: PDF, DOCX, JPG, PNG</small>
                    <input
                      ref="docInput"
                      @change="onDocSelect"
                      accept="application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*"
                      type="file"
                      multiple
                      class="d-none"
                    />
                    <div class="mt-3 d-flex justify-content-center">
                      <button class="btn btn-outline-secondary" @click="$refs.docInput.click()">
                        Choose files
                      </button>
                    </div>
                  </div>
                </div>

                <div class="row g-3">
                  <div v-for="f in documents" :key="f.id" class="col-12 col-md-6">
                    <div
                      class="card p-3 rounded-3 shadow-sm d-flex align-items-center gap-3 flex-row"
                    >
                      <div
                        style="
                          width: 64px;
                          height: 64px;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                        "
                      >
                        <i
                          v-if="f.type.includes('pdf')"
                          class="bi bi-file-earmark-pdf display-5 text-secondary"
                        ></i>
                        <i
                          v-else-if="f.type.startsWith('image')"
                          class="bi bi-file-earmark-image display-5 text-secondary"
                        ></i>
                        <i v-else class="bi bi-file-earmark display-5 text-secondary"></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                          <div>
                            <div class="fw-bold">{{ f.name }}</div>
                            <div class="small text-muted">{{ f.sizeText }}</div>
                          </div>
                          <div class="d-flex gap-2">
                            <a
                              :href="f.url"
                              target="_blank"
                              class="btn btn-sm btn-outline-secondary"
                              >Preview</a
                            >
                            <a
                              :href="f.url"
                              :download="f.name"
                              class="btn btn-sm btn-outline-primary"
                              >Download</a
                            >
                            <button
                              class="btn btn-sm btn-outline-secondary"
                              @click="deleteDocument(f.id)"
                            >
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="!documents.length" class="col-12 text-center text-muted">
                    No files uploaded.
                  </div>
                </div>
              </div>
            </section>
          </transition>
        </div>
      </main>
    </div>

    <div class="modal-backdrop" v-if="showModal" @click="closeModal"></div>
    <div class="modal drawer-modal" v-if="showModal" role="dialog" aria-modal="true">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ editMode ? 'Edit' : 'Add' }} {{ currentSection.label }} item
          </h5>
          <button type="button" class="btn-close" @click="closeModal"></button>
        </div>
        <form @submit.prevent="saveItem">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" v-model="form.name" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Meta / Description</label>
              <input type="text" class="form-control" v-model="form.meta" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="closeModal">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary">
              {{ editMode ? 'Save changes' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue'
import { useStudentStore } from '@/stores/student.store'

const studentStore = useStudentStore()

const sections = [
  { key: 'academic', label: 'Academic', icon: 'bi bi-journal-bookmark' },
  { key: 'skills', label: 'Skills', icon: 'bi bi-lightning-charge' },
  { key: 'languages', label: 'Languages', icon: 'bi bi-translate' },
  { key: 'projects', label: 'Projects', icon: 'bi bi-kanban' },
  { key: 'certificates', label: 'Certificates', icon: 'bi bi-award' },
  { key: 'experience', label: 'Experience', icon: 'bi bi-briefcase' },
  { key: 'achievements', label: 'Achievements', icon: 'bi bi-trophy' },
  { key: 'documents', label: 'Documents', icon: 'bi bi-folder2-open' },
]

const active = ref('skills')
// const query = ref('')
// const sortBy = ref('name')
const showModal = ref(false)
const editMode = ref(false)
const form = reactive({ id: null, name: '', meta: '' })

// sample option data per section
const data = reactive({
  skills: [
    { id: 1, name: 'JavaScript', meta: 'Programming' },
    { id: 2, name: 'Vue 3', meta: 'Framework' },
  ],
  languages: [
    { id: 1, name: 'English', meta: 'CEFR B2+' },
    { id: 2, name: 'French', meta: 'CEFR C1' },
  ],
  projects: [],
  certificates: [{ id: 1, name: 'Data Science Fundamentals', meta: 'Coursera' }],
  experience: [],
  achievements: [],
  documents: [
    { id: 1, name: 'Transcript', meta: 'Official transcript' },
    { id: 2, name: 'CV', meta: 'Resume' },
  ],
})

const currentSection = computed(() => sections.find((s) => s.key === active.value) || sections[0])

// const items = computed(() => data[active.value] || [])

// const filteredItems = computed(() => {
//   const term = query.value.trim().toLowerCase()
//   let list = [...items.value]
//   if (term) {
//     list = list.filter(
//       (i) => i.name.toLowerCase().includes(term) || (i.meta || '').toLowerCase().includes(term),
//     )
//   }
//   if (sortBy.value === 'name') list.sort((a, b) => a.name.localeCompare(b.name))
//   return list
// })

const selectSection = (key) => {
  active.value = key
}

// selected student and assignment state
const selectedStudentId = ref(null)
const selectedStudent = computed(
  () => studentStore.students.find((s) => s.id === selectedStudentId.value) || null,
)

const studentQuery = ref('')
const filteredStudents = computed(() => {
  const q = studentQuery.value.trim().toLowerCase()
  if (!q) return studentStore.students
  return studentStore.students.filter((s) => {
    return (
      (s.fullName || '').toLowerCase().includes(q) ||
      (s.program || '').toLowerCase().includes(q) ||
      (s.email || '').toLowerCase().includes(q) ||
      (s.level || '').toLowerCase().includes(q)
    )
  })
})

const selectStudent = (id) => {
  selectedStudentId.value = id
}

// track temporary assignments by item name for the current selection
const assignments = reactive({})

watch(
  () => [selectedStudentId.value, active.value],
  () => {
    // reset assignment map when student or section changes
    Object.keys(assignments).forEach((k) => delete assignments[k])
    if (selectedStudent.value) {
      const arr = selectedStudent.value[active.value] || []
      arr.forEach((name) => (assignments[name] = true))
    }
  },
  { immediate: true },
)

// const isAssigned = (item) => !!assignments[item.name]

// const toggleAssign = (item) => {
//   if (!selectedStudent.value) return
//   assignments[item.name] = !assignments[item.name]
// }

// const applyAllVisible = () => {
//   if (!selectedStudent.value) return
//   filteredItems.value.forEach((i) => (assignments[i.name] = true))
// }

// const clearAssignments = () => {
//   if (!selectedStudent.value) return
//   filteredItems.value.forEach((i) => delete assignments[i.name])
// }

// const saveAssignments = async () => {
//   if (!selectedStudent.value) return
//   const list = Object.keys(assignments).filter((k) => assignments[k])
//   const payload = { ...selectedStudent.value, [active.value]: list }
//   try {
//     await studentStore.updateStudent(selectedStudent.value.id, payload)
//   } catch (err) {
//     console.warn('Failed saving assignments', err)
//     // fallback: update local store array directly
//     const idx = studentStore.students.findIndex((s) => s.id === selectedStudent.value.id)
//     if (idx >= 0) studentStore.students[idx][active.value] = list
//   }
// }

const openAddModal = () => {
  editMode.value = false
  form.id = null
  form.name = ''
  form.meta = ''
  showModal.value = true
}

// const editItem = (item) => {
//   editMode.value = true
//   form.id = item.id
//   form.name = item.name
//   form.meta = item.meta || ''
//   showModal.value = true
// }

const closeModal = () => {
  showModal.value = false
}

const saveItem = () => {
  const list = data[active.value]
  if (editMode.value) {
    const idx = list.findIndex((i) => i.id === form.id)
    if (idx >= 0) list[idx] = { id: form.id, name: form.name, meta: form.meta }
  } else {
    list.push({ id: Date.now(), name: form.name, meta: form.meta })
  }
  closeModal()
}

// const removeItem = (id) => {
//   const list = data[active.value]
//   const idx = list.findIndex((i) => i.id === id)
//   if (idx >= 0) list.splice(idx, 1)
// }

// --- Workspace specific state and handlers ---
const loading = ref(false)
const showReset = ref(false)

const grades = ['A', 'B', 'C', 'D', 'E', 'F']

const academicForm = reactive({
  course: '',
  code: '',
  semester: '',
  year: '',
  credits: null,
  mark: null,
  grade: '',
  status: 'In Progress',
  instructor: '',
  remarks: '',
})
const academicRecords = ref([
  // realistic sample data
  {
    id: 1,
    course: 'JavaScript Programming',
    code: 'JS-101',
    semester: 'Fall',
    year: '2024/2025',
    credits: 3,
    mark: 88,
    grade: 'A',
    status: 'Passed',
    instructor: 'Dr. Amina',
    remarks: '',
  },
  {
    id: 2,
    course: 'Data Structures',
    code: 'CS-202',
    semester: 'Spring',
    year: '2024/2025',
    credits: 4,
    mark: 72,
    grade: 'B',
    status: 'Passed',
    instructor: 'Prof. Gomez',
    remarks: '',
  },
])
const academicEditId = ref(null)

const computeGrade = () => {
  const m = Number(academicForm.mark || 0)
  if (!m && m !== 0) return
  if (m >= 85) academicForm.grade = 'A'
  else if (m >= 70) academicForm.grade = 'B'
  else if (m >= 60) academicForm.grade = 'C'
  else if (m >= 50) academicForm.grade = 'D'
  else academicForm.grade = 'F'
}

const resetAcademic = () => {
  academicEditId.value = null
  Object.assign(academicForm, {
    course: '',
    code: '',
    semester: '',
    year: '',
    credits: null,
    mark: null,
    grade: '',
    status: 'In Progress',
    instructor: '',
    remarks: '',
  })
}

const saveAcademic = () => {
  const payload = { ...academicForm }
  if (academicEditId.value) {
    const idx = academicRecords.value.findIndex((r) => r.id === academicEditId.value)
    if (idx >= 0) academicRecords.value[idx] = { id: academicEditId.value, ...payload }
  } else {
    academicRecords.value.unshift({ id: Date.now(), ...payload })
  }
  resetAcademic()
}

const editAcademic = (r) => {
  academicEditId.value = r.id
  Object.assign(academicForm, { ...r })
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const viewAcademic = (r) => {
  // quick view using alert for now (could be modal)
  alert(`${r.course} — ${r.grade} (${r.mark})\nInstructor: ${r.instructor}\nRemarks: ${r.remarks}`)
}

const deleteAcademic = (id) => {
  academicRecords.value = academicRecords.value.filter((r) => r.id !== id)
}

// Skills
const skills = ref([
  { id: 1, name: 'JavaScript', level: 'Expert', category: 'Programming', desc: 'ES6+, DOM, async' },
  {
    id: 2,
    name: 'Vue 3',
    level: 'Advanced',
    category: 'Framework',
    desc: 'Composition API, Vuex, Router',
  },
])
const skillForm = reactive({
  id: null,
  name: '',
  level: 'Intermediate',
  category: 'Programming',
  desc: '',
})

const saveSkill = () => {
  if (skillForm.id) {
    const idx = skills.value.findIndex((s) => s.id === skillForm.id)
    if (idx >= 0) skills.value[idx] = { ...skillForm }
  } else {
    skills.value.unshift({ id: Date.now(), ...skillForm })
  }
  Object.assign(skillForm, {
    id: null,
    name: '',
    level: 'Intermediate',
    category: 'Programming',
    desc: '',
  })
}
const editSkill = (s) => Object.assign(skillForm, { ...s })
const deleteSkill = (id) => (skills.value = skills.value.filter((s) => s.id !== id))

// Languages
const languages = ref([
  {
    id: 1,
    language: 'English',
    speaking: 'Fluent',
    reading: 'Fluent',
    writing: 'Fluent',
    listening: 'Fluent',
    cefr: 'C1',
  },
])
const languageForm = reactive({
  id: null,
  language: '',
  speaking: 'Conversational',
  reading: 'Conversational',
  writing: 'Conversational',
  listening: 'Conversational',
  cefr: '',
})

const saveLanguage = () => {
  if (languageForm.id) {
    const idx = languages.value.findIndex((l) => l.id === languageForm.id)
    if (idx >= 0) languages.value[idx] = { ...languageForm }
  } else {
    languages.value.unshift({ id: Date.now(), ...languageForm })
  }
  Object.assign(languageForm, {
    id: null,
    language: '',
    speaking: 'Conversational',
    reading: 'Conversational',
    writing: 'Conversational',
    listening: 'Conversational',
    cefr: '',
  })
}
const editLanguage = (l) => Object.assign(languageForm, { ...l })
const deleteLanguage = (id) => (languages.value = languages.value.filter((l) => l.id !== id))

// Projects
const projects = ref([])
const projectForm = reactive({
  id: null,
  name: '',
  role: '',
  tech: '',
  desc: '',
  start: '',
  end: '',
  github: '',
  demo: '',
})
const saveProject = () => {
  if (projectForm.id) {
    const idx = projects.value.findIndex((p) => p.id === projectForm.id)
    if (idx >= 0) projects.value[idx] = { ...projectForm }
  } else projects.value.unshift({ id: Date.now(), ...projectForm })
  resetProject()
}
const editProject = (p) => Object.assign(projectForm, { ...p })
const deleteProject = (id) => (projects.value = projects.value.filter((p) => p.id !== id))
const resetProject = () =>
  Object.assign(projectForm, {
    id: null,
    name: '',
    role: '',
    tech: '',
    desc: '',
    start: '',
    end: '',
    github: '',
    demo: '',
  })

// Certificates
const certificates = ref([])
const certForm = reactive({
  id: null,
  name: '',
  issuer: '',
  issued: '',
  expiry: '',
  credId: '',
  link: '',
  file: null,
  fileUrl: '',
})
const onCertUpload = (e) => {
  const f = e.target.files && e.target.files[0]
  if (!f) return
  certForm.file = f
  certForm.fileUrl = URL.createObjectURL(f)
}
const saveCertificate = () => {
  const payload = {
    id: certForm.id || Date.now(),
    name: certForm.name,
    issuer: certForm.issuer,
    issued: certForm.issued,
    expiry: certForm.expiry,
    credId: certForm.credId,
    link: certForm.link,
    fileUrl: certForm.fileUrl,
  }
  certificates.value.unshift(payload)
  resetCertificate()
}
const resetCertificate = () =>
  Object.assign(certForm, {
    id: null,
    name: '',
    issuer: '',
    issued: '',
    expiry: '',
    credId: '',
    link: '',
    file: null,
    fileUrl: '',
  })
const deleteCertificate = (id) =>
  (certificates.value = certificates.value.filter((c) => c.id !== id))

// Experience
const experiences = ref([])
const expForm = reactive({
  id: null,
  company: '',
  position: '',
  type: 'Full-time',
  start: '',
  end: '',
  resp: '',
})
const saveExperience = () => {
  if (expForm.id) {
    const idx = experiences.value.findIndex((e) => e.id === expForm.id)
    if (idx >= 0) experiences.value[idx] = { ...expForm }
  } else experiences.value.unshift({ id: Date.now(), ...expForm })
  resetExperience()
}
const editExperience = (e) => Object.assign(expForm, { ...e })
const deleteExperience = (id) => (experiences.value = experiences.value.filter((e) => e.id !== id))
const resetExperience = () =>
  Object.assign(expForm, {
    id: null,
    company: '',
    position: '',
    type: 'Full-time',
    start: '',
    end: '',
    resp: '',
  })

// Achievements
const achievements = ref([])
const achForm = reactive({ id: null, title: '', org: '', date: '', desc: '' })
const saveAchievement = () => {
  achievements.value.unshift({ id: Date.now(), ...achForm })
  resetAchievement()
}
const editAchievement = (a) => Object.assign(achForm, { ...a })
const deleteAchievement = (id) =>
  (achievements.value = achievements.value.filter((a) => a.id !== id))
const resetAchievement = () =>
  Object.assign(achForm, { id: null, title: '', org: '', date: '', desc: '' })

// Documents upload
const documents = ref([])
const onDrop = (e) => {
  const files = Array.from(e.dataTransfer.files || [])
  files.forEach(addDocument)
}
const onDocSelect = (e) => {
  const files = Array.from(e.target.files || [])
  files.forEach(addDocument)
}
const addDocument = (file) => {
  const id = Date.now() + Math.round(Math.random() * 1000)
  const url = URL.createObjectURL(file)
  const sizeText =
    file.size > 1024 * 1024
      ? (file.size / (1024 * 1024)).toFixed(2) + ' MB'
      : Math.round(file.size / 1024) + ' KB'
  documents.value.unshift({ id, name: file.name, type: file.type, size: file.size, sizeText, url })
}
const deleteDocument = (id) => {
  const d = documents.value.find((x) => x.id === id)
  if (d && d.url) URL.revokeObjectURL(d.url)
  documents.value = documents.value.filter((x) => x.id !== id)
}

// Workspace helpers
const workspaceTitle = computed(() => {
  const map = {
    academic: 'Academic Records',
    skills: 'Skills',
    languages: 'Languages',
    projects: 'Projects',
    certificates: 'Certificates',
    experience: 'Experience',
    achievements: 'Achievements',
    documents: 'Documents',
  }
  return map[active.value] || 'Workspace'
})
const workspaceDescription = computed(() => {
  const map = {
    academic: 'Manage the academic records for the selected student.',
    skills: 'Add and categorize skills for the student.',
    languages: 'Define language proficiencies and CEFR levels.',
    projects: 'Add project entries with links and descriptions.',
    certificates: 'Upload and list certificates (PDF).',
    experience: 'Record professional or internship experience.',
    achievements: 'Log awards, scholarships and recognitions.',
    documents: 'Upload documents related to the student.',
  }
  return map[active.value] || ''
})

const resetWorkspace = () => {
  // reset forms for active workspace
  if (active.value === 'academic') resetAcademic()
  if (active.value === 'skills')
    Object.assign(skillForm, {
      id: null,
      name: '',
      level: 'Intermediate',
      category: 'Programming',
      desc: '',
    })
  if (active.value === 'languages')
    Object.assign(languageForm, {
      id: null,
      language: '',
      speaking: 'Conversational',
      reading: 'Conversational',
      writing: 'Conversational',
      listening: 'Conversational',
      cefr: '',
    })
  if (active.value === 'projects') resetProject()
  if (active.value === 'certificates') resetCertificate()
  if (active.value === 'experience') resetExperience()
  if (active.value === 'achievements') resetAchievement()
  if (active.value === 'documents') documents.value = []
}
</script>

<style scoped>
.manage-options-page {
  padding: 24px;
  background: #f8f9fa;
  min-height: 100vh;
  color: #0d2b45;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}
.eyebrow {
  color: #d4a017;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.12em;
}
.page-copy {
  color: #47505c;
  max-width: 720px;
}
.options-layout {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 20px;
  margin-top: 18px;
}
.options-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.options-nav li {
  margin-bottom: 8px;
}
.options-nav button {
  width: 100%;
  text-align: left;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #e4eef3;
  background: white;
  color: #0d2b45;
}
.options-nav li.active button {
  background: #d4a017;
  color: white;
}
.options-main {
  background: white;
  padding: 18px;
  border-radius: 14px;
  border: 1px solid #e4eef3;
}
.options-controls {
  display: flex;
  gap: 12px;
  margin: 12px 0 16px;
}
.student-selection {
  margin: 12px 0 18px;
}
.student-search input {
  width: 100%;
  padding: 8px 10px;
  border-radius: 8px;
  border: 1px solid #e6eef2;
}
.student-list {
  list-style: none;
  padding: 0;
  margin: 8px 0 0;
  max-height: 220px;
  overflow: auto;
  border-radius: 8px;
  border: 1px solid #eef6f9;
  background: #fff;
}
.student-list li {
  padding: 10px 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1f6f8;
  cursor: pointer;
}
.student-list li.selected {
  background: #d4a017;
  color: white;
}
.student-list li.selected .muted {
  color: rgba(255, 255, 255, 0.85);
}
.student-list li .muted {
  color: #6b7785;
  display: block;
}
.student-assign-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}
.assign-controls {
  display: flex;
  gap: 8px;
  align-items: center;
}
.assign-controls .form-select {
  min-width: 280px;
}
.assign-actions {
  margin-top: 12px;
  display: flex;
  gap: 12px;
  align-items: center;
}
.items-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 10px;
}
.items-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid #eef6f9;
  background: #fcfeff;
}
.items-list .empty {
  text-align: center;
  color: #6b7785;
}
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(4, 33, 44, 0.5);
  z-index: 30;
}
.drawer-modal {
  position: fixed;
  right: 0;
  top: 10%;
  transform: translateY(0);
  width: 420px;
  z-index: 40;
}
.modal-content {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #e6eef2;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #eef6f9;
}
.modal-body {
  padding: 12px 16px;
}
.modal-footer {
  padding: 12px 16px;
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  border-top: 1px solid #eef6f9;
}
.btn-close {
  border: none;
  background: transparent;
  font-size: 1.1rem;
  cursor: pointer;
}

.workspace-header {
  border-bottom: 1px solid #eef6f9;
  position: sticky;
  top: 0;
  z-index: 5;
}
.workspace .card {
  border-radius: 12px;
}
.skill-card:hover {
  transform: translateY(-4px);
  transition: transform 0.18s ease;
}
.language-card {
  min-width: 220px;
  max-width: 320px;
}
.drop-zone {
  cursor: pointer;
  transition: background 0.15s ease;
}
.drop-zone:hover {
  background: #fbfcfe;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* responsive adjustments per requirements */
@media (max-width: 1199px) and (min-width: 768px) {
  /* tablet: allow workspace to expand */
  .options-layout {
    grid-template-columns: 80px 1fr;
  }
  .options-nav button {
    padding: 10px 8px;
    font-size: 0.95rem;
  }
}
@media (max-width: 767px) {
  /* mobile: student list becomes off-canvas externally handled; workspace full width */
  .options-layout {
    grid-template-columns: 1fr;
  }
  .options-nav {
    display: none;
  }
  .workspace-body {
    padding: 12px;
  }
  .workspace .card {
    border-radius: 10px;
  }
  .table-responsive table {
    font-size: 0.95rem;
  }
}

@media (max-width: 900px) {
  .options-layout {
    grid-template-columns: 1fr;
  }
  .drawer-modal {
    width: 100%;
    top: 12%;
  }
}
</style>
