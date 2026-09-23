# Backend Prompts - Quick Reference Guide

## 📋 Files Created for Backend Implementation

You now have **4 prompt documents** to guide your backend team:

---

## 1️⃣ **BACKEND_MEGA_PROMPT.md** ⭐ START HERE

**Best for:** Full implementation in one go

### When to use:

- You want one comprehensive prompt to give to backend agent
- Backend agent is capable and you want complete implementation
- You want everything from database to tests in one request
- Time-saving: single prompt covers everything

### How to use:

1. Copy the entire content between the ``` markers
2. Paste into your backend agent/developer chat
3. Agent will build complete system

### Estimated time: 3-5 days (experienced backend dev)

---

## 2️⃣ **BACKEND_PROMPTS_CHEATSHEET.md**

**Best for:** Quick reference, iterative development

### When to use:

- Breaking work into smaller tasks
- Each step needs separate development
- Want to check progress between phases
- Team members working in parallel
- You prefer step-by-step approach

### How to use:

1. Use prompts 1-6 in order
2. Wait for each phase to complete before next
3. Run Phase 1, then Phase 2, then Phase 3

### Execution phases:

- **Phase 1 (Foundation):** Prompts 1, 2, 3 → Database + APIs
- **Phase 2 (Quality):** Prompts 4, 5, 6 → Validation + Auth + Filtering
- **Phase 3 (Polish):** Prompts 7, 8, 9, 10 → Tests + Docs + Optional features

---

## 3️⃣ **BACKEND_IMPLEMENTATION_PROMPTS.md**

**Best for:** Detailed guidance on each topic

### When to use:

- Need detailed explanation for each component
- Teaching/learning backend implementation
- Specific area needs deep dive
- Documenting decisions
- Complex requirements need discussion

### 10 Prompts cover:

1. Database Schema
2. Candidature API endpoints
3. SavedOpportunity API endpoints
4. Status transitions & logic
5. Authorization & permissions
6. Search & filtering
7. Testing
8. Notifications (optional)
9. Opportunity integration
10. Documentation

### How to use:

- Pick specific prompt(s) you need
- Each is self-contained and detailed
- Use for specific problem-solving

---

## 4️⃣ **CANDIDATURE_API_REQUIREMENTS.md** (Reference)

**Best for:** Understanding what backend needs to build

### Contains:

- Complete API specification
- Request/response examples
- Error handling format
- Database schema
- Authorization matrix
- Performance considerations

### How to use:

- Share with backend team for reference
- Use when clarifying requirements
- Backend can use as specification document

---

## 🎯 QUICK DECISION TREE

```
Do you want everything at once?
    ├─ YES → Use BACKEND_MEGA_PROMPT.md ⭐
    └─ NO
        └─ Do you want step-by-step guidance?
            ├─ YES → Use BACKEND_PROMPTS_CHEATSHEET.md
            └─ NO
                └─ Do you need detailed explanations?
                    ├─ YES → Use BACKEND_IMPLEMENTATION_PROMPTS.md
                    └─ NO → Use CANDIDATURE_API_REQUIREMENTS.md as reference
```

---

## 🚀 RECOMMENDED WORKFLOW

### For Agile Teams (Recommended)

```
Day 1: Prompt from CHEATSHEET #1
        → Database schema ready

Day 2: Prompts from CHEATSHEET #2-3
        → APIs building

Day 3: Prompts from CHEATSHEET #4-6
        → Security + Validation added

Day 4-5: Prompts from CHEATSHEET #7-10
         → Tests + Polish
```

### For Waterfall/Linear Teams

```
1. Send entire BACKEND_MEGA_PROMPT.md
2. Team implements everything
3. Done!
```

### For Teaching/Documentation

```
1. Use BACKEND_IMPLEMENTATION_PROMPTS.md #1-4
2. Team understands requirements deeply
3. Implement with full context
4. Use BACKEND_IMPLEMENTATION_PROMPTS.md #5-10 for refinement
```

---

## 📝 PROMPT COMPARISON TABLE

| Need                | File             | Best For  | Time     |
| ------------------- | ---------------- | --------- | -------- |
| Everything at once  | MEGA_PROMPT      | Full impl | 3-5 days |
| Step by step        | CHEATSHEET       | Iterative | 2-3 days |
| Each phase detailed | IMPL_PROMPTS     | Learning  | 4-5 days |
| Reference doc       | API_REQUIREMENTS | Questions | N/A      |

---

## 💡 USAGE TIPS

### For Backend Agent (AI)

- Use **BACKEND_MEGA_PROMPT.md** - more complete context
- Agent can handle full requirements in one prompt
- Gets better results with complete specification

### For Backend Team (Humans)

- Use **BACKEND_PROMPTS_CHEATSHEET.md** - clear phases
- Each person knows what they're building
- Easy to track progress

### For Both

- Start with **CHEATSHEET** to plan
- Use **MEGA_PROMPT** to brief team
- Reference **API_REQUIREMENTS** during implementation
- Use **IMPL_PROMPTS** when questions arise

---

## 🔑 KEY FEATURES COVERED

All prompts ensure implementation of:

✅ Candidatures/Applications tracking
✅ Saved opportunities bookmarking  
✅ Status progression (applied → interview → offer → accepted)
✅ Validation & error handling
✅ Authorization by role (student/company/admin)
✅ Idempotent save operations
✅ Duplicate prevention
✅ Audit logging
✅ Comprehensive testing
✅ Complete documentation

---

## 📞 WHEN TO USE EACH

### MEGA_PROMPT ⭐

```
"I want one comprehensive prompt to hand to my backend dev.
I want everything covered - database to tests to docs."
```

→ Copy the prompt, paste to backend agent, done!

### CHEATSHEET

```
"My team works in phases.
I want to break this into 3 manageable phases.
I want to check progress between each phase."
```

→ Use Phase 1, 2, 3 sequence

### IMPLEMENTATION_PROMPTS

```
"We need detailed guidance on each component.
Some features are complex and need discussion.
We want to learn while building."
```

→ Use specific prompts as needed

### API_REQUIREMENTS

```
"We need this as a specification document.
Backend team will reference this during implementation."
```

→ Share with team, bookmark for reference

---

## ✨ BONUS: COMBINING PROMPTS

You can also combine approaches:

**Best of Both Approach:**

1. Send **CHEATSHEET** Prompt #1 for database
2. Send **MEGA_PROMPT** for endpoints (skip database section)
3. Use **IMPL_PROMPTS** if questions arise

**Learning + Speed:**

1. Send **MEGA_PROMPT** with note: "Reference IMPL_PROMPTS if you need details"
2. Team implements faster with detailed guidance available

---

## 🎓 TRAINING/DOCUMENTATION FLOW

For onboarding new developers:

```
1. Read CANDIDATURE_API_REQUIREMENTS.md
   → Understand what needs to be built

2. Read BACKEND_IMPLEMENTATION_PROMPTS.md #1-4
   → Learn database design + API structure

3. Read BACKEND_IMPLEMENTATION_PROMPTS.md #5-7
   → Learn auth + filtering + testing

4. Implement using BACKEND_MEGA_PROMPT.md
   → Execute with full knowledge

5. Reference BACKEND_PROMPTS_CHEATSHEET.md
   → Keep pace with checklist
```

---

## 🎯 NEXT STEPS

1. **Pick your approach** (see decision tree above)
2. **Choose your prompt file**
3. **Copy the relevant prompt**
4. **Send to backend team/agent**
5. **Track progress**
6. **Reference API_REQUIREMENTS if questions**

---

## 🆘 IF SOMETHING UNCLEAR

The hierarchy for finding answers:

1. First check: **CANDIDATURE_API_REQUIREMENTS.md** (specifications)
2. Then: **BACKEND_IMPLEMENTATION_PROMPTS.md** (detailed guidance)
3. Finally: **BACKEND_MEGA_PROMPT.md** (complete context)

---

## ✅ SUCCESS CHECKLIST

After backend implementation, verify:

- [ ] Database schema created (candidatures + saved_opportunities tables)
- [ ] 6 API endpoints working (2 for candidatures, 1 for saved, 4 operations)
- [ ] Authentication required on all endpoints
- [ ] Authorization enforced by role
- [ ] Status transitions validated
- [ ] Idempotent save operations
- [ ] Duplicate applications prevented
- [ ] Comprehensive tests written (>80% coverage)
- [ ] API documentation (OpenAPI spec)
- [ ] Error responses formatted correctly
- [ ] Audit logging implemented
- [ ] Tested with frontend (OpportunityView + CandidatureFlowView)

---

**Questions?** Check the relevant prompt file for details!
