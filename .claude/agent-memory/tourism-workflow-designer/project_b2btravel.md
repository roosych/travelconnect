---
name: B2B Travel Platform — project context
description: Core facts about what is being built, its MVP scope, and key design decisions
type: project
---

Laravel 13 monolith, PHP 8.3. Single database, no microservices. Domain-organized under app/Domain/.

Modules: Requests, RFQs, Offers, Proposals, Bookings, Users.

Key design decisions locked in architecture:
- Operator is the central human actor — no automation at any decision point
- Partial offers are first-class (supplier may cover only part of an RFQ)
- Suppliers do NOT use the system in MVP; operator enters offers on their behalf
- Proposals are hand-assembled from multiple offers — no auto-assembly
- One active (draft/sent) proposal per request at a time
- Bookings are simple state records in MVP — no payment, no vouchers, no work orders

MVP excludes: automated email, supplier portal, payment, itinerary generation, granular operator roles, reporting, audit log.

Workflow document saved at: /home/rkandiba/projects/b2btravel/docs/workflow.md
Architecture document at: /home/rkandiba/projects/b2btravel/docs/architecture.md

**Why:** Small team, sequential workflow, simplicity over distributed complexity.
**How to apply:** When asked about new features, check MVP scope first. Default to operator-manual over automation unless explicitly requested.
