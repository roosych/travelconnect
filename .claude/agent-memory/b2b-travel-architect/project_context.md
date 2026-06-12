---
name: B2B Travel Platform — Project Context
description: Core facts about what is being built, the stack, and current state
type: project
---

We are building a B2B inbound tourism RFQ platform as a Laravel 13 / PHP 8.3 monolith. The project sits at /home/rkandiba/projects/b2btravel.

Current state (2026-05-06): Fresh Laravel scaffold only. No domain models exist. Only default migrations (users, cache, jobs) are present. The docs/architecture.md file is the first design artifact produced.

The core flow is: Agency submits Request → Operator creates RFQs → Suppliers submit Offers → Operator assembles Proposal → Agency approves → Booking created.

**Why:** This is an operational tool for a travel operator who sources B2B inbound tourism packages. The operator is the central human actor in every workflow step.

**How to apply:** Always treat operator manual control as the default. Do not suggest automation unless explicitly asked. Do not suggest microservices or distributed patterns — this is and must remain a monolith.
