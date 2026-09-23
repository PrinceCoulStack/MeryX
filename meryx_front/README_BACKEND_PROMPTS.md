# 📚 Candidature Feature - Complete Documentation Index

## Overview

You have **7 comprehensive documents** ready to guide your backend team through implementing the student opportunity and candidature tracking system.

---

## 📄 DOCUMENTS CREATED

### 🎯 **For Backend Agent/Developer**

#### 1. **BACKEND_MEGA_PROMPT.md** ⭐ START HERE

- **Purpose:** Complete implementation prompt in one file
- **Use when:** Giving to backend agent, want full implementation
- **Contains:** Database schema → Entities → 6 API endpoints → Auth → Testing → Docs
- **Length:** ~5000 lines (comprehensive)
- **Time to implement:** 3-5 days
- **Best for:** AI agents, full requirements in one prompt

#### 2. **BACKEND_PROMPTS_CHEATSHEET.md**

- **Purpose:** 10 numbered prompts in condensed format
- **Use when:** Breaking into phases, step-by-step approach
- **Contains:** Quick copy-paste prompts for each phase
- **Length:** ~400 lines (concise)
- **Time to implement:** 2-3 days (3 phases)
- **Best for:** Human teams, iterative development

#### 3. **BACKEND_IMPLEMENTATION_PROMPTS.md**

- **Purpose:** 10 detailed, individual prompts with explanations
- **Use when:** Need detailed guidance on specific topics
- **Contains:** Deep dive into each aspect
- **Length:** ~3000 lines (detailed)
- **Time to implement:** 4-5 days (learning as you go)
- **Best for:** Teaching, complex requirements, documentation

---

### 📖 **For Reference & Understanding**

#### 4. **BACKEND_PROMPTS_GUIDE.md** (CURRENTLY READING)

- **Purpose:** Guide to which prompt to use when
- **Use when:** Deciding which approach to take
- **Contains:** Decision tree, comparison table, workflow examples
- **Best for:** Planning your implementation strategy

#### 5. **CANDIDATURE_API_REQUIREMENTS.md**

- **Purpose:** Complete technical specification
- **Use when:** Need to understand what exactly to build
- **Contains:** Endpoint specs, data models, schemas, auth matrix
- **Best for:** Reference document, specification baseline

---

### 💻 **For Frontend Integration**

#### 6. **CANDIDATURE_QUICK_START.md**

- **Purpose:** Frontend developer quick reference
- **Use when:** Frontend dev needs to integrate the APIs
- **Contains:** Composable usage, examples, data models
- **Best for:** Frontend developers using the new APIs

#### 7. **CANDIDATURE_IMPLEMENTATION.md**

- **Purpose:** Full feature documentation and architecture
- **Use when:** Understanding complete system design
- **Contains:** Architecture, file structure, integration points
- **Best for:** Project overview, understanding design decisions

---

## 🗺️ HOW THEY FIT TOGETHER

```
Your Frontend Implementation (DONE ✅)
    ↓
    └─── useCandidature.js composable
    ├─── OpportunityView.vue (enhanced)
    └─── CandidatureFlowView.vue (new)

                    ↓↓↓ NOW YOU NEED ↓↓↓

Backend Team Uses These Prompts:
    ├─ BACKEND_MEGA_PROMPT.md (complete)
    │  OR
    ├─ BACKEND_PROMPTS_CHEATSHEET.md (phases)
    │  OR
    └─ BACKEND_IMPLEMENTATION_PROMPTS.md (detailed)

                    ↓↓↓ SUPPORTED BY ↓↓↓

Reference Documents:
    ├─ CANDIDATURE_API_REQUIREMENTS.md (specification)
    └─ BACKEND_PROMPTS_GUIDE.md (which to use when)

                    ↓↓↓ IMPLEMENTS ↓↓↓

6 API Endpoints:
    ├─ GET /api/candidatures
    ├─ POST /api/candidatures
    ├─ PATCH /api/candidatures/{id}
    ├─ GET /api/savedOpportunities
    ├─ POST /api/savedOpportunities
    └─ DELETE /api/savedOpportunities/{id}

                    ↓↓↓ READY FOR ↓↓↓

Frontend Integration:
    └─ CANDIDATURE_QUICK_START.md (how frontend uses them)
```

---

## 🎯 QUICK START: WHICH FILE TO USE

### "I need to brief my backend team NOW"

```
→ Send: BACKEND_PROMPTS_CHEATSHEET.md
→ Tell them: "Use Phase 1, 2, 3 in order"
→ Timeline: 2-3 days
```

### "I have a backend AI agent and want full implementation"

```
→ Use: BACKEND_MEGA_PROMPT.md
→ Copy entire prompt content
→ Paste to agent
→ Timeline: 3-5 days
```

### "I need detailed guidance on specific components"

```
→ Use: BACKEND_IMPLEMENTATION_PROMPTS.md
→ Share Prompt 1: Database Schema
→ Share Prompt 2-3: APIs
→ Share Prompt 4-5: Logic & Auth
→ etc.
→ Timeline: 4-5 days
```

### "I need to understand the requirements first"

```
→ Read: CANDIDATURE_API_REQUIREMENTS.md
→ Then share with team
→ Then choose prompt approach above
```

### "What's my implementation strategy?"

```
→ Read: BACKEND_PROMPTS_GUIDE.md (this file)
→ Use decision tree
→ Choose your approach
```

---

## 📊 DOCUMENT COMPARISON

| Document         | Purpose        | Length      | Best For      | Tech Level   |
| ---------------- | -------------- | ----------- | ------------- | ------------ |
| MEGA_PROMPT      | Complete impl  | 5000+ lines | AI agents     | Advanced     |
| CHEATSHEET       | 10 prompts     | 400 lines   | Teams         | Beginner+    |
| IMPL_PROMPTS     | Detailed 10x   | 3000 lines  | Learning      | Beginner+    |
| API_REQUIREMENTS | Specification  | 2000 lines  | Reference     | Intermediate |
| PROMPTS_GUIDE    | Strategy       | 1000 lines  | Planning      | All          |
| QUICK_START      | Frontend usage | 1000 lines  | Frontend dev  | Intermediate |
| IMPLEMENTATION   | Architecture   | 2000 lines  | Understanding | Intermediate |

---

## 🔄 RECOMMENDED WORKFLOW

### For Backend AI Agent (Fastest)

```
1. Read BACKEND_MEGA_PROMPT.md
2. Copy entire prompt
3. Paste to AI agent
4. Agent implements everything
```

**Time: 3-5 days | Best: Complete solution**

### For Backend Team (Iterative)

```
1. Share BACKEND_PROMPTS_CHEATSHEET.md
2. Team implements Phase 1 (Database + APIs)
3. Review, then Phase 2 (Validation + Auth)
4. Review, then Phase 3 (Tests + Docs)
```

**Time: 2-3 days | Best: Quality control**

### For Teaching/Onboarding

```
1. Team reads CANDIDATURE_API_REQUIREMENTS.md
2. Team reads BACKEND_IMPLEMENTATION_PROMPTS.md (Prompts 1-4)
3. Team implements using BACKEND_MEGA_PROMPT.md
4. Team refers to BACKEND_IMPLEMENTATION_PROMPTS.md for details
```

**Time: 4-5 days | Best: Learning + Implementation**

---

## 📋 IMPLEMENTATION CHECKLIST

### Phase 1: Foundation (Day 1)

```
From BACKEND_PROMPTS_CHEATSHEET.md - Prompt #1
□ Database schema created
□ candidatures table ✓
□ saved_opportunities table ✓
□ Indexes and constraints ✓
□ Migration files ✓
```

### Phase 2: APIs (Day 2)

```
From BACKEND_PROMPTS_CHEATSHEET.md - Prompts #2-3
□ GET /api/candidatures ✓
□ POST /api/candidatures ✓
□ PATCH /api/candidatures/{id} ✓
□ GET /api/savedOpportunities ✓
□ POST /api/savedOpportunities (idempotent!) ✓
□ DELETE /api/savedOpportunities/{id} ✓
```

### Phase 2B: Quality (Day 2)

```
From BACKEND_PROMPTS_CHEATSHEET.md - Prompts #4-6
□ Status transition validation ✓
□ Authorization enforced ✓
□ Search/filtering working ✓
□ Error handling correct ✓
```

### Phase 3: Polish (Days 3-4)

```
From BACKEND_PROMPTS_CHEATSHEET.md - Prompts #7-10
□ Tests written (>80% coverage) ✓
□ Notifications (optional) ✓
□ Opportunity integration ✓
□ Documentation complete ✓
□ API spec (OpenAPI/Swagger) ✓
```

---

## 🚀 GETTING STARTED NOW

### Right Now (5 minutes)

1. ✅ You're reading this guide
2. Read BACKEND_PROMPTS_GUIDE.md (strategic overview)
3. Decide: **Mega Prompt** vs **Cheatsheet** vs **Detailed**

### Next (10 minutes)

- **If Mega Prompt:** Copy BACKEND_MEGA_PROMPT.md
- **If Cheatsheet:** Copy BACKEND_PROMPTS_CHEATSHEET.md #1
- **If Detailed:** Copy BACKEND_IMPLEMENTATION_PROMPTS.md #1

### Then (5 minutes)

- Paste to backend team/agent
- Let them know timeline (2-5 days depending on approach)
- Reference CANDIDATURE_API_REQUIREMENTS.md for questions

---

## 🎓 DOCUMENT RELATIONSHIPS

```
START HERE
    ↓
BACKEND_PROMPTS_GUIDE.md
    ↓
Choose approach (1 of 3):
    │
    ├─→ BACKEND_MEGA_PROMPT.md (Complete in one)
    │
    ├─→ BACKEND_PROMPTS_CHEATSHEET.md (3 phases)
    │
    └─→ BACKEND_IMPLEMENTATION_PROMPTS.md (10 detailed)

All approaches supported by:
    ├─ CANDIDATURE_API_REQUIREMENTS.md (spec reference)
    └─ BACKEND_PROMPTS_GUIDE.md (strategy guide)

Frontend integration:
    └─ CANDIDATURE_QUICK_START.md (how frontend uses APIs)

Architecture/context:
    └─ CANDIDATURE_IMPLEMENTATION.md (system overview)
```

---

## 💾 FILE LOCATIONS

All files are in your project root:

```
c:\VueProject\meryx_front\
├── BACKEND_MEGA_PROMPT.md ⭐
├── BACKEND_PROMPTS_CHEATSHEET.md
├── BACKEND_IMPLEMENTATION_PROMPTS.md
├── BACKEND_PROMPTS_GUIDE.md
├── CANDIDATURE_API_REQUIREMENTS.md
├── CANDIDATURE_QUICK_START.md
├── CANDIDATURE_IMPLEMENTATION.md
└── [rest of project]
```

All are markdown files, can be read in any editor.

---

## 🎯 SUCCESS CRITERIA

When backend implementation is complete, verify:

✅ Database created (2 tables with proper schema)
✅ 6 API endpoints working and tested  
✅ Authentication & authorization enforced
✅ Status transitions validated
✅ Idempotent save operations
✅ Error handling per specification
✅ Tests written and passing
✅ API documentation (OpenAPI spec)
✅ Frontend can integrate without issues

Then frontend (`OpportunityView` + `CandidatureFlowView`) will:

- ✅ Save opportunities persistently
- ✅ Apply to opportunities
- ✅ Track application status
- ✅ Show application timeline
- ✅ All with working backend

---

## 📞 QUICK ANSWERS

**Q: Where do I start?**
A: Read BACKEND_PROMPTS_GUIDE.md, pick your approach, use corresponding prompt file.

**Q: What if my backend team has questions?**
A: Share CANDIDATURE_API_REQUIREMENTS.md for specification reference.

**Q: How long will this take?**
A: 2-5 days depending on approach (cheatsheet fastest, detailed slowest).

**Q: Can I use multiple prompt files?**
A: Yes! Use cheatsheet for planning, mega prompt for execution, detailed prompts for specific questions.

**Q: What about frontend?**
A: Already done! Your frontend is ready. Backend just needs to implement the APIs.

**Q: What if backend doesn't finish?**
A: Priority order: Database schema → APIs → Auth → Filtering → Tests → Docs → Optional features.

---

## ✨ YOU'RE ALL SET!

You now have:

- ✅ Complete frontend implementation (useCandidature, CandidatureFlowView)
- ✅ 7 comprehensive documents for backend guidance
- ✅ Clear implementation path (3 different approaches)
- ✅ Reference specifications
- ✅ Success criteria

**Next step:** Choose your prompt approach and share with backend team!

---

_Last updated: 2026-09-09_
_For candidature feature implementation_
