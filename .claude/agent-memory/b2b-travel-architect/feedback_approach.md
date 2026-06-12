---
name: Design Approach Preferences
description: How the user wants architecture and design work approached in this project
type: feedback
---

Always prioritize operator simplicity. When in doubt, make the operator more powerful and the system less clever.

Do not suggest automation as a default. Automation is [POST-MVP] unless the user explicitly asks for it.

Do not propose microservices, event-driven patterns, or distributed architecture under any circumstances for this project.

Keep all designs MVP-first. If a feature feels too complex for MVP, it is wrong by default.

When producing architecture output, always structure it as: Workflow → Entities → States → Operator Decision Points → MVP Notes.

**Why:** This was established in the system prompt and validated by the user's initial task framing. The operator is the center of the system, not an afterthought.

**How to apply:** Before answering any design question, run the validation checklist: Is operator always in control? Can system handle partial offers? Is flow understandable in real-world agency use? Is this minimal enough for MVP? Did I avoid overengineering?
