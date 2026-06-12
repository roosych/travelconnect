---
name: Architecture Decisions — MVP Design Choices
description: Key architecture decisions made in the initial design that must be respected in future sessions
type: project
---

The following decisions were made in the initial architecture design (docs/architecture.md) and must be respected:

1. Single Laravel monolith. No microservices, no event buses, no distributed concerns.

2. Domain folders under app/Domain/ (Requests, RFQs, Offers, Proposals, Bookings, Users). Each folder contains Models/, Services/, Http/Controllers/, Http/Requests/. Cross-domain calls go through Service classes only.

3. Three roles: agency, operator, supplier — stored as an enum column on the users table.

4. Partial offers are first-class. The system must never reject an offer because it only covers part of an RFQ. is_partial is a boolean on the offers table.

5. Proposals are manually assembled by the operator from selected offers. No auto-assembly.

6. For MVP, suppliers do NOT have a web portal — offers are entered manually by the operator.

7. For MVP, no automated email sending, no payment processing, no itinerary generation, no reporting.

8. The proposal_offer pivot table connects proposals to offers (many-to-many). This is how multi-supplier packages are assembled.

9. Implementation sequence: Phase 1 Users → Phase 2 Requests → Phase 3 RFQs → Phase 4 Offers → Phase 5 Proposals → Phase 6 Bookings.

**Why:** MVP-first philosophy. The operator team is small. Keeping it simple means it can be built and used quickly. Over-engineering was explicitly rejected.

**How to apply:** When asked to implement any feature, check it against the MVP scope. If it is in the [POST-MVP] list in architecture.md, flag it and ask before proceeding.
