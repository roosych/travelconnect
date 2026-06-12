# B2B Inbound Tourism RFQ Platform — Architecture Design

**Version:** 1.0
**Date:** 2026-05-06
**Stack:** Laravel 13 / PHP 8.3 — Single monolithic application

---

## 1. Architecture Overview

This is a single Laravel monolith. There are no microservices, no event buses, and no distributed concerns. The application is organized into domain folders inside `app/` but shares one database, one request lifecycle, and one deployment unit.

The entire system exists to serve one operational reality:

> An operator receives a travel request from an agency, sources the best supplier combination, and confirms a booking — manually, at every critical step.

Everything in the architecture is oriented around giving the operator clear visibility and control. Automation is minimal by design. The operator is the system.

### Application layers

```
Browser / API Client
        |
   Laravel Routes  (routes/web.php, routes/api.php)
        |
   Controllers  (thin — input validation + response only)
        |
   Domain Services  (all business logic lives here)
        |
   Eloquent Models  (data access)
        |
   Single MySQL/PostgreSQL database
```

### Directory structure (domain-organized)

```
app/
  Domain/
    Requests/          # Agency travel requests
    RFQs/              # Request-for-quotation sent to suppliers
    Offers/            # Supplier responses to RFQs
    Proposals/         # Operator-assembled packages
    Bookings/          # Confirmed travel packages
    Users/             # Authentication + role management
```

Each domain folder contains: `Models/`, `Services/`, `Http/Controllers/`, and `Http/Requests/` (form validation). No shared kernel, no abstract base domains. Keep it flat and readable.

---

## 2. Roles

Three roles exist in the system. Role is a field on the `users` table.

| Role | What they can do |
|---|---|
| `agency` | Submit travel requests, view their own proposals, accept or reject proposals |
| `operator` | Full access — manage requests, create RFQs, review offers, build proposals, confirm bookings |
| `supplier` | Receive RFQs, submit offers against RFQs they are assigned to |

There is exactly one operator account type. All operator users share the same access. Role-based access is enforced via Laravel middleware and policies.

[POST-MVP] Granular operator sub-roles (e.g. sales vs. operations vs. finance).
[POST-MVP] Agency sub-accounts with varying permission levels.

---

## 3. Core Domain Modules

### 3.1 Requests module

Owns the agency's initial travel need. This is the entry point for all work.

An agency fills in what they need: destination, travel dates, group size, requested services (accommodation, transport, guides, activities). The request is deliberately open — it is not a structured booking form. The operator interprets it.

**What this module does NOT do:**
- It does not create RFQs automatically.
- It does not validate supplier availability.
- It does not price anything.

The operator reads the request and decides what to do next.

### 3.2 RFQs module

Owns the operator's structured query sent to one or more suppliers.

When the operator decides a request needs supplier input, they create one or more RFQs. Each RFQ describes a specific service category or travel segment. The operator selects which suppliers to send each RFQ to.

A single travel request will typically generate multiple RFQs (one for accommodation, one for transport, one for a guide, etc.).

**What this module does NOT do:**
- It does not send emails automatically. [POST-MVP]
- It does not auto-select suppliers.
- It does not track supplier delivery SLAs automatically.

### 3.3 Offers module

Owns the supplier's response to an RFQ. Partial responses are normal.

A supplier may respond to only part of what was asked. The system accepts this. The operator reviews all received offers and decides which to use. An offer has no automatic effect on anything — it sits in a reviewed state until the operator acts on it.

**What this module does NOT do:**
- It does not auto-accept offers.
- It does not reject offers that don't cover 100% of the RFQ.
- It does not compare offers automatically.

### 3.4 Proposals module

Owns the operator's assembled travel package, ready to send to the agency for approval.

The operator manually selects one or more offers, potentially combining suppliers, to build the proposal. A proposal maps directly back to the original request. The operator writes a covering description and sets the final price.

This is the most operator-intensive module. The operator must:
- Choose which offers to include
- Handle gaps where no offer was received
- Set the final price (which may differ from supplier prices)
- Write the proposal narrative for the agency

**What this module does NOT do:**
- It does not auto-build proposals from offers.
- It does not enforce that all requested services are covered (operator decides to proceed with gaps).

### 3.5 Bookings module

Owns the confirmed travel package after agency approval.

A booking is created when the agency accepts a proposal. A booking is a record of what was agreed. The operator then coordinates with suppliers to confirm operational details.

**What this module does NOT do:**
- It does not process payments. [POST-MVP]
- It does not send itineraries automatically. [POST-MVP]
- It does not create supplier work orders automatically. [POST-MVP]

### 3.6 Users module

Owns authentication and role assignment. Uses Laravel's built-in auth scaffolding with a `role` column added to the `users` table.

---

## 4. Entity Map

### 4.1 Core entities and fields

**users**
```
id
name
email
password
role          (enum: agency, operator, supplier)
company_name
phone
created_at
updated_at
```

**requests**
```
id
agency_id          (FK → users)
title
destination
travel_date_from
travel_date_to
pax_count
services_needed    (text — free-form description of required services)
notes
status             (enum: draft, submitted, processing, completed, cancelled)
created_at
updated_at
```

**rfqs**
```
id
request_id         (FK → requests)
operator_id        (FK → users)
title
description        (what exactly is being quoted)
service_type       (e.g. accommodation, transport, guide, activity)
deadline_at        (date by which supplier must respond)
status             (enum: draft, sent, awaiting, closed)
created_at
updated_at
```

**rfq_supplier** (pivot — which suppliers received this RFQ)
```
id
rfq_id             (FK → rfqs)
supplier_id        (FK → users)
sent_at
```

**offers**
```
id
rfq_id             (FK → rfqs)
supplier_id        (FK → users)
is_partial         (boolean — supplier self-declared or operator-flagged)
covered_services   (text — what the supplier is actually covering)
uncovered_services (text — what the supplier is NOT covering, if partial)
unit_price
currency
valid_until
notes
status             (enum: received, reviewed, selected, rejected, expired)
created_at
updated_at
```

**proposals**
```
id
request_id         (FK → requests)
operator_id        (FK → users)
title
description        (narrative for agency)
total_price
currency
valid_until
status             (enum: draft, sent, accepted, rejected, expired)
created_at
updated_at
```

**proposal_offer** (pivot — which offers are included in this proposal)
```
id
proposal_id        (FK → proposals)
offer_id           (FK → offers)
operator_notes     (why this offer was chosen, gap-filling notes)
```

**bookings**
```
id
proposal_id        (FK → proposals)
request_id         (FK → requests)
agency_id          (FK → users)
operator_id        (FK → users)
confirmed_at
travel_date_from
travel_date_to
pax_count
final_price
currency
status             (enum: confirmed, in_progress, completed, cancelled)
notes
created_at
updated_at
```

### 4.2 Relationship summary

```
users (agency)
  └── has many → requests

requests
  └── has many → rfqs
  └── has one  → proposal (active)
  └── has one  → booking

rfqs
  └── belongs to       → requests
  └── belongs to many  → users (suppliers) via rfq_supplier
  └── has many         → offers

offers
  └── belongs to       → rfqs
  └── belongs to       → users (supplier)
  └── belongs to many  → proposals via proposal_offer

proposals
  └── belongs to       → requests
  └── belongs to many  → offers via proposal_offer
  └── has one          → booking

bookings
  └── belongs to       → proposals
  └── belongs to       → requests
  └── belongs to       → users (agency)
  └── belongs to       → users (operator)
```

---

## 5. Lifecycle State Flow

### 5.1 Request lifecycle

```
draft
  |
  | Agency submits request
  v
submitted
  |
  | Operator opens request, begins RFQ work
  v
processing
  |
  | Proposal accepted by agency, booking created
  v
completed
```

```
Any state → cancelled   (operator or agency initiates cancellation)
```

**State rules:**
- Agency can move: `draft → submitted`
- Operator can move: `submitted → processing`, `processing → completed`, any state `→ cancelled`
- Agency can also cancel their own request while in `draft` or `submitted`

### 5.2 RFQ lifecycle

```
draft
  |
  | Operator sends RFQ to selected suppliers
  v
sent
  |
  | At least one offer received, or deadline passed with no response
  v
awaiting
  |
  | Operator closes RFQ (manually, when ready to build proposal)
  v
closed
```

**State rules:**
- Only operator moves RFQ through states.
- `sent` and `awaiting` can co-exist while partial offer responses are arriving.
- There is no automatic transition. The operator closes the RFQ when satisfied.
- An RFQ with no offers can still be closed — the operator acknowledges the gap.

### 5.3 Offer lifecycle

```
received
  |
  | Operator opens and reads the offer
  v
reviewed
  |
  | Operator includes offer in a proposal
  v
selected
```

```
reviewed → rejected     (operator explicitly rejects offer)
received/reviewed/selected → expired   (valid_until date passes)
```

**State rules:**
- `received` is set when the supplier submits.
- `reviewed` is set when any operator user opens the offer.
- `selected` is set when the operator adds the offer to a proposal.
- `rejected` is an explicit operator action — it signals to no one automatically. [POST-MVP: notify supplier]
- `expired` can be set by a scheduled job or on-read if `valid_until` has passed.

### 5.4 Proposal lifecycle

```
draft
  |
  | Operator sends proposal to agency
  v
sent
  |
  | Agency reviews proposal
  v
accepted  (agency approves)  →  triggers booking creation
rejected  (agency declines)
expired   (valid_until passes with no response)
```

**State rules:**
- Only operator moves `draft → sent`.
- Only agency moves `sent → accepted` or `sent → rejected`.
- Expiry is set by a scheduled job or on-read check. [POST-MVP: automated expiry job]
- A rejected proposal can result in the operator building a new proposal (new draft) against the same request.

### 5.5 Booking lifecycle

```
confirmed    (created on proposal acceptance)
  |
  | Travel begins
  v
in_progress
  |
  | Travel ends, operator closes out
  v
completed
```

```
confirmed/in_progress → cancelled   (operator initiates, requires notes)
```

**State rules:**
- `confirmed` is set automatically when proposal is accepted by agency.
- `in_progress` is set manually by operator when travel begins.
- `completed` is set manually by operator after travel ends.
- `cancelled` requires operator to record a reason.

---

## 6. Operator Decision Points

These are the moments where the system stops and waits for a human operator to act. They are features, not gaps.

| Step | Decision the operator makes |
|---|---|
| Request arrives (submitted) | Read it. Decide if it is workable. Move to `processing` or `cancel`. |
| Creating RFQs | Decide how to split the request into service categories. Choose which suppliers to contact for each RFQ. Set realistic deadlines. |
| Monitoring offers | Decide when to stop waiting for supplier responses. Close the RFQ. Accept that some suppliers did not respond. |
| Reviewing a partial offer | Decide if partial coverage is acceptable. Identify what is missing. Decide whether to fill the gap from another offer or proceed without it. |
| Building a proposal | Select which offers to combine. Resolve service gaps. Set the final agency-facing price (which may differ from supplier costs). Write the proposal narrative. |
| Offer expiry conflict | If a selected offer expires before the proposal is accepted, decide whether to re-source or re-price. |
| Proposal rejected by agency | Decide whether to renegotiate, rebuild the proposal, or cancel the request. |
| Booking in progress | Coordinate with suppliers outside the system (calls, emails) to confirm operational details. |
| Booking completion | Confirm all services were delivered. Mark booking completed. |

---

## 7. Module Boundaries

Modules share the database but own their logic through their Service classes. Cross-module calls go through the service layer, never directly between models of different domains.

```
Requests module
  - Owns: Request creation, submission, status management
  - Can read: User (to identify agency)
  - Cannot write: RFQs, Offers, Proposals, Bookings

RFQs module
  - Owns: RFQ creation, supplier assignment, deadline management
  - Can read: Requests (to understand scope), Users (to pick suppliers)
  - Cannot write: Offers, Proposals, Bookings

Offers module
  - Owns: Offer submission, review, selection/rejection, expiry
  - Can read: RFQs (to understand what is being quoted)
  - Cannot write: Proposals, Bookings

Proposals module
  - Owns: Proposal assembly, sending to agency, acceptance/rejection
  - Can read: Offers (to include in proposal), Requests (for reference)
  - Can write: triggers Booking creation on acceptance
  - Cannot write: RFQs

Bookings module
  - Owns: Booking status management, completion
  - Can read: Proposals, Requests, Users
  - Cannot write: any upstream module

Users module
  - Owns: Authentication, role assignment
  - Read-only dependency of all other modules
```

---

## 8. Key Design Decisions

### Why no automatic RFQ creation from requests

The operator must decide how to split a request into RFQs. A request for "10-day Morocco group tour" might need 3 RFQs or 8 depending on what suppliers exist and what the operator knows. No algorithm can replace this judgment.

### Why partial offers are first-class

Suppliers in B2B inbound tourism frequently respond to only part of an RFQ. A ground handler may cover transport but not accommodation. Rejecting partial offers would make the system unusable. The proposal layer is specifically designed to assemble complete packages from fragments.

### Why proposals are hand-assembled

The operator may choose a more expensive supplier for reliability reasons. They may fill a gap themselves. They may rewrite the price entirely. The proposal is the operator's professional judgment — not a mechanical aggregation of offers.

### Why bookings are simple state records

MVP bookings do not manage operational details. They exist to confirm what was agreed and give the operator a reference. Full itinerary, voucher, and supplier work order management is [POST-MVP].

### Why one monolith

The team is small. The workflow is sequential. Breaking this into services would add deployment and debugging overhead without any benefit at this scale. A well-organized Laravel monolith with domain folders gives clean separation without distributed system complexity.

---

## 9. What is Explicitly [POST-MVP]

| Feature | Why deferred |
|---|---|
| Automated email notifications to suppliers when RFQ is sent | Manual outreach works for MVP; adds email template and queue complexity |
| Automated expiry job for offers and proposals | On-read expiry check is sufficient for MVP |
| Payment processing | Not needed to confirm a booking operationally |
| Supplier portal (web UI for suppliers to submit offers) | Email/manual offer entry by operator is sufficient for MVP |
| Agency self-service portal | Operator can create agency requests on their behalf for MVP |
| Itinerary and voucher generation | Post-booking operational detail, not needed to confirm |
| Supplier work orders | Coordination happens off-platform for MVP |
| Granular operator roles | Single operator role sufficient for MVP team size |
| RFQ templates | Useful but not essential; operator builds from scratch |
| Multi-currency conversion | Single working currency per booking for MVP |
| Reporting and analytics dashboard | Not needed for operational MVP |
| Audit log / change history | Valuable but not blocking |

---

## 10. Implementation Sequence (MVP)

Build in this order. Each phase is independently usable.

**Phase 1 — Users and Roles**
Set up authentication. Add `role` to users. Add policies for each role. Seed operator, one test agency, one test supplier.

**Phase 2 — Requests**
Agency (or operator on behalf of agency) can create and submit requests. Operator sees a list of submitted requests.

**Phase 3 — RFQs**
Operator can create RFQs from a request, assign suppliers, mark as sent/closed.

**Phase 4 — Offers**
Operator can record offers received from suppliers (entered manually by operator for MVP). Offer review and selection.

**Phase 5 — Proposals**
Operator can build a proposal from selected offers. Send to agency. Agency can accept or reject.

**Phase 6 — Bookings**
On proposal acceptance, booking is created automatically. Operator manages booking status through completion.
