# B2B Inbound Tourism RFQ Platform — Workflow Design

**Version:** 1.0
**Date:** 2026-05-06
**Depends on:** architecture.md v1.0
**Scope:** MVP operator-centric workflow. No automation. No supplier portal. Operator is the central actor at every transition.

---

## Table of Contents

1. [Canonical State Inventories](#1-canonical-state-inventories)
2. [Full Request Lifecycle and State Transitions](#2-full-request-lifecycle-and-state-transitions)
3. [RFQ Lifecycle and State Transitions](#3-rfq-lifecycle-and-state-transitions)
4. [Offer Lifecycle and State Transitions](#4-offer-lifecycle-and-state-transitions)
5. [Proposal Lifecycle and State Transitions](#5-proposal-lifecycle-and-state-transitions)
6. [Booking Lifecycle and State Transitions](#6-booking-lifecycle-and-state-transitions)
7. [Cross-Module Interaction Rules](#7-cross-module-interaction-rules)
8. [Partial Offer Handling](#8-partial-offer-handling)
9. [Supplier Non-Response and Timeout Logic](#9-supplier-non-response-and-timeout-logic)
10. [Proposal Creation Conditions](#10-proposal-creation-conditions)
11. [Blocking Conditions by State Transition](#11-blocking-conditions-by-state-transition)
12. [Operator Decision Points](#12-operator-decision-points)
13. [Edge Case Handling](#13-edge-case-handling)
14. [Business Rule Catalog](#14-business-rule-catalog)
15. [Validation Rule Catalog](#15-validation-rule-catalog)

---

## 1. Canonical State Inventories

These are the only valid status values for each entity. No other values may be persisted. The system must reject any attempt to set a status value not in this list.

### 1.1 Request statuses

| Status | Meaning |
|---|---|
| `draft` | Agency has started but not submitted the request |
| `submitted` | Agency has submitted; operator has not yet acted |
| `processing` | Operator is actively working the request (RFQs exist or are being created) |
| `completed` | A booking derived from this request has reached `completed` |
| `cancelled` | Request will not proceed; no further action possible |

### 1.2 RFQ statuses

| Status | Meaning |
|---|---|
| `draft` | Operator is composing the RFQ; not yet sent to any supplier |
| `sent` | RFQ has been dispatched to one or more suppliers; awaiting responses |
| `awaiting` | At least one offer has been received; operator is still reviewing or waiting for more |
| `closed` | Operator has manually closed the RFQ; no further offers will be accepted |

### 1.3 Offer statuses

| Status | Meaning |
|---|---|
| `received` | Offer has been entered by operator on supplier's behalf; not yet reviewed |
| `reviewed` | Operator has opened and read the offer |
| `selected` | Operator has included this offer in a proposal draft |
| `rejected` | Operator has explicitly excluded this offer |
| `expired` | The `valid_until` date has passed before the offer was selected |

### 1.4 Proposal statuses

| Status | Meaning |
|---|---|
| `draft` | Operator is assembling the proposal; not sent to agency |
| `sent` | Proposal has been delivered to agency for review |
| `accepted` | Agency has approved the proposal; triggers booking creation |
| `rejected` | Agency has declined the proposal |
| `expired` | The proposal `valid_until` date passed before agency responded |

### 1.5 Booking statuses

| Status | Meaning |
|---|---|
| `confirmed` | Booking created on proposal acceptance; travel not yet begun |
| `in_progress` | Travel is currently underway |
| `completed` | Travel has concluded; all services delivered |
| `cancelled` | Booking was cancelled after confirmation; requires documented reason |

---

## 2. Full Request Lifecycle and State Transitions

### 2.1 State transition table

| From | To | Actor | Trigger | Blocking conditions |
|---|---|---|---|---|
| _(none)_ | `draft` | Agency or Operator | Agency creates request form | None |
| `draft` | `submitted` | Agency or Operator | Agency submits completed request | destination, travel_date_from, travel_date_to, pax_count, and services_needed must all be non-empty |
| `submitted` | `processing` | Operator | Operator opens request and begins RFQ work | At least one RFQ must exist in any status against this request |
| `processing` | `completed` | Operator (system-assisted) | Booking linked to this request reaches `completed` | Booking linked to this request must be in `completed` status |
| `draft` | `cancelled` | Agency or Operator | Explicit cancellation action | None |
| `submitted` | `cancelled` | Agency or Operator | Explicit cancellation action | None |
| `processing` | `cancelled` | Operator | Explicit cancellation action | If a booking is in `confirmed` or `in_progress`, the booking must be cancelled first |

### 2.2 State flow diagram

```
[Agency creates]
      |
      v
    DRAFT ──────────────────────────────────────────► CANCELLED
      |
      | Agency submits (all required fields present)
      v
  SUBMITTED ──────────────────────────────────────── CANCELLED
      |
      | Operator creates first RFQ
      v
  PROCESSING ─────────────────────────────────────── CANCELLED
      |
      | Linked booking reaches completed
      v
  COMPLETED
```

### 2.3 If/Then rules — Request

**R-01:** IF an agency attempts to submit a request AND any of (destination, travel_date_from, travel_date_to, pax_count, services_needed) is null or empty THEN the system must refuse the submission and return field-level validation errors.

**R-02:** IF travel_date_from is in the past at time of submission THEN the system must warn the operator but must NOT block submission (backdated requests are operationally valid in some cases, e.g. correcting an existing booking).

**R-03:** IF travel_date_to is before travel_date_from THEN the system must block submission with an error.

**R-04:** IF pax_count is less than 1 THEN the system must block submission.

**R-05:** IF an operator attempts to move a request to `processing` AND no RFQ exists for that request THEN the system must block the transition.

**R-06:** IF a request is in `cancelled` status THEN no further transitions are permitted on it or on any of its child RFQs, Offers, or Proposals. The operator must acknowledge this if attempting to act on a child record.

**R-07:** IF a request is in `completed` status THEN no new RFQs or Proposals may be created against it.

---

## 3. RFQ Lifecycle and State Transitions

### 3.1 State transition table

| From | To | Actor | Trigger | Blocking conditions |
|---|---|---|---|---|
| _(none)_ | `draft` | Operator | Operator creates RFQ from a request | Parent request must be in `submitted` or `processing` |
| `draft` | `sent` | Operator | Operator sends RFQ to selected suppliers | RFQ must have: title, description, service_type, deadline_at set. At least one supplier must be assigned via rfq_supplier. Parent request must not be `cancelled` |
| `sent` | `awaiting` | Operator | First offer is entered against this RFQ | At least one offer with status `received` must exist for this RFQ |
| `awaiting` | `closed` | Operator | Operator manually closes the RFQ | No condition — operator may close with zero, one, or many offers |
| `sent` | `closed` | Operator | Operator manually closes with no offers received | No condition — operator may close a no-response RFQ |
| `draft` | `cancelled` | Operator | Operator discards draft RFQ | None |
| `sent` | `cancelled` | Operator | Operator cancels RFQ before any offers | All suppliers on this RFQ must be notified manually (system records cancellation, no automatic notification in MVP) |

### 3.2 State flow diagram

```
[Operator creates RFQ]
         |
         v
       DRAFT ──────────────────────────────────────► CANCELLED
         |
         | Operator assigns suppliers and sends
         v
       SENT ───────────────────────────────────────► CANCELLED
         |              |
         |              | First offer received
         |              v
         |           AWAITING
         |              |
         └──────────────┘
                        | Operator manually closes
                        v
                      CLOSED
```

### 3.3 Service type constraint

**R-08:** Each RFQ has exactly one `service_type`. If a request requires accommodation AND transport, the operator must create two separate RFQs — one per service type. This is an explicit design constraint, not a UI restriction. The system must enforce that `service_type` is set and is a value from the defined enum before allowing `draft → sent`.

**R-09:** Multiple RFQs may exist for the same `service_type` against a single request. This is valid — for example, the operator may issue a second accommodation RFQ if the first yields no suitable offers.

**R-10:** IF a parent request is moved to `cancelled` THEN all `draft` and `sent` RFQs under it must be moved to `cancelled` automatically. `awaiting` and `closed` RFQs are left as-is for historical record.

### 3.4 Deadline logic

**R-11:** `deadline_at` on an RFQ is informational in MVP. The system records it but does not automatically close the RFQ or mark suppliers as non-responsive. The operator monitors deadlines manually. [POST-MVP: automated deadline enforcement and supplier non-response flags]

**R-12:** IF `deadline_at` has passed AND the RFQ is still in `sent` or `awaiting` status THEN the system must display a visual warning to the operator but must NOT change the RFQ status automatically in MVP.

---

## 4. Offer Lifecycle and State Transitions

### 4.1 State transition table

| From | To | Actor | Trigger | Blocking conditions |
|---|---|---|---|---|
| _(none)_ | `received` | Operator | Operator enters offer on supplier's behalf | Parent RFQ must be in `sent` or `awaiting`. Offer must have: rfq_id, supplier_id, unit_price, currency, valid_until, covered_services |
| `received` | `reviewed` | Operator | Operator opens offer detail view | None — occurs on first view |
| `reviewed` | `selected` | Operator | Operator adds offer to a proposal draft | Offer `valid_until` must not have passed. Offer's parent RFQ must not be in `cancelled` status |
| `reviewed` | `rejected` | Operator | Operator explicitly rejects offer | None |
| `selected` | `reviewed` | Operator | Operator removes offer from a proposal draft | The proposal must be in `draft` status (cannot remove from a sent proposal) |
| `received` | `expired` | System (on-read check) | `valid_until` date has passed | None |
| `reviewed` | `expired` | System (on-read check) | `valid_until` date has passed | None |
| `selected` | `expired` | System (on-read check) | `valid_until` date has passed | Triggers Offer Expiry in Active Proposal edge case (see section 13.4) |
| `selected` | `withdrawn` | Operator (recording supplier action) | Supplier communicates withdrawal | Triggers Supplier Withdrawal edge case (see section 13.3) |

Note: `withdrawn` is an additional status not in the original enum that must be added. A supplier revoking an accepted offer is a distinct business event from an operator rejection, and must be distinguishable in records. See section 13.3.

### 4.2 State flow diagram

```
[Operator records offer]
         |
         v
      RECEIVED ───────────────────────────────────► EXPIRED
         |
         | Operator views offer
         v
      REVIEWED ───────────────────────────────────► EXPIRED
         |              |
         |              | Operator rejects
         |              v
         |           REJECTED
         |
         | Operator adds to proposal
         v
      SELECTED ───────────────────────────────────► EXPIRED
         |                                            |
         | Operator removes from draft proposal       | (triggers re-sourcing flow)
         v                                            v
      REVIEWED                                    WITHDRAWN
```

### 4.3 Partial offer rules

**R-13:** An offer where `is_partial = true` MUST have `uncovered_services` populated. If `is_partial = true` and `uncovered_services` is null or empty, the system must block offer creation with a validation error.

**R-14:** An offer where `is_partial = false` MUST have `uncovered_services` null or empty. If the operator sets `is_partial = false` but also fills in `uncovered_services`, the system must block and return a validation error.

**R-15:** An offer may be added to a proposal regardless of whether it is partial. The proposal layer owns the responsibility of coverage completeness. The offer layer makes no coverage assertions.

**R-16:** `covered_services` is mandatory on all offers (partial or full). An offer with empty `covered_services` must be rejected by the system — it is meaningless to record an offer that covers nothing.

### 4.4 Multiple offers per RFQ

**R-17:** Multiple offers may exist against a single RFQ (one per supplier assigned to that RFQ, plus any additional offers the operator enters). There is no limit.

**R-18:** IF two offers exist for the same RFQ AND the operator attempts to add both to the same proposal THEN the system must warn the operator that both offers cover the same service_type. The system must NOT block this — the operator may have a valid reason (e.g. two transport legs under the same service_type). The warning must be explicit.

---

## 5. Proposal Lifecycle and State Transitions

### 5.1 State transition table

| From | To | Actor | Trigger | Blocking conditions |
|---|---|---|---|---|
| _(none)_ | `draft` | Operator | Operator creates new proposal against a request | Parent request must be in `processing`. No other proposal in `draft` or `sent` status may exist for the same request simultaneously |
| `draft` | `sent` | Operator | Operator sends proposal to agency | See full conditions in section 10 |
| `sent` | `accepted` | Agency | Agency explicitly approves proposal | Proposal must be in `sent` status. Proposal `valid_until` must not have passed. Automatically triggers booking creation |
| `sent` | `rejected` | Agency | Agency explicitly rejects proposal | Proposal must be in `sent` status |
| `sent` | `expired` | System (on-read check) | `valid_until` has passed with no agency response | No further agency action is permitted on an expired proposal |
| `draft` | `cancelled` | Operator | Operator discards the proposal draft | None |
| `rejected` | _(new draft)_ | Operator | Operator creates a replacement proposal | A new `draft` proposal record is created; the rejected one is retained for history |
| `expired` | _(new draft)_ | Operator | Operator rebuilds after expiry | A new `draft` proposal record is created; the expired one is retained for history |

### 5.2 State flow diagram

```
[Operator creates proposal]
         |
         v
       DRAFT ───────────────────────────────────── CANCELLED
         |
         | Operator satisfies all send conditions
         v
        SENT ────────────────────────────────────── EXPIRED
         |              |
         | Agency accepts   | Agency rejects
         v              v
      ACCEPTED       REJECTED
         |              |
         | (auto)        | Operator creates new draft
         v              v
     [Booking        [new DRAFT]
      created]
```

### 5.3 One active proposal constraint

**R-19:** At any moment, a request may have at most one proposal in `draft` or `sent` status. If the operator attempts to create a second `draft` proposal while one already exists in `draft` or `sent`, the system must block and display a message identifying the existing active proposal.

**R-20:** After a proposal is `rejected` or `expired`, the operator may create a new `draft` proposal. The prior rejected/expired proposal must remain in the database as a historical record. It must not be deleted.

---

## 6. Booking Lifecycle and State Transitions

### 6.1 State transition table

| From | To | Actor | Trigger | Blocking conditions |
|---|---|---|---|---|
| _(none)_ | `confirmed` | System | Proposal moves to `accepted` | Booking is created automatically; operator does not trigger this manually |
| `confirmed` | `in_progress` | Operator | Travel begins | Operator must confirm; travel_date_from should be on or before today's date (warning only, not a hard block — date adjustments happen) |
| `in_progress` | `completed` | Operator | All services delivered; operator closes out booking | Operator must add completion notes (notes field must not be null) |
| `confirmed` | `cancelled` | Operator | Booking cancelled before travel | Operator must record cancellation reason in notes field |
| `in_progress` | `cancelled` | Operator | Booking cancelled mid-travel | Operator must record cancellation reason. This is a critical escalation — system must flag for review |

### 6.2 State flow diagram

```
[Proposal accepted by agency]
         |
         v
     CONFIRMED (auto-created)
         |
         | Operator marks travel started
         v
     IN_PROGRESS
         |
         | Operator marks all services delivered
         v
     COMPLETED


CONFIRMED ──────────────────────────────────────► CANCELLED (requires notes)
IN_PROGRESS ────────────────────────────────────► CANCELLED (requires notes, flagged)
```

### 6.3 Booking creation rules

**R-21:** When a proposal moves to `accepted`, the system must automatically create a booking record copying the following fields from the proposal and its parent request: request_id, proposal_id, agency_id (from request), operator_id (from proposal), travel_date_from (from request), travel_date_to (from request), pax_count (from request), final_price (from proposal total_price), currency (from proposal).

**R-22:** The booking creation must be atomic with the proposal acceptance. If booking creation fails, the proposal must not move to `accepted`. The entire operation must roll back.

**R-23:** Once a booking exists in `confirmed` or `in_progress` status, the parent request's status must remain `processing`. The request moves to `completed` only when the booking reaches `completed`.

---

## 7. Cross-Module Interaction Rules

These rules govern how state changes in one module affect another module. All cross-module writes go through Service classes, never directly between models.

### 7.1 Request → RFQ interactions

**R-24:** An RFQ may only be created if the parent request is in `submitted` or `processing` status. The RFQ creation action must also transition the request to `processing` if it is currently in `submitted`.

**R-25:** If a request is cancelled, all RFQs in `draft` or `sent` status must be moved to `cancelled`. RFQs in `awaiting` or `closed` are frozen as-is. No further offers may be entered against a cancelled RFQ.

### 7.2 RFQ → Offer interactions

**R-26:** An offer may only be entered if its parent RFQ is in `sent` or `awaiting` status. Offers cannot be entered against `draft`, `closed`, or `cancelled` RFQs.

**R-27:** When the first offer is entered against an RFQ in `sent` status, the RFQ status must automatically advance to `awaiting`. This is the one automatic transition in the system.

**R-28:** Closing an RFQ (`awaiting → closed`) does not change the status of any existing offers. Offers retain their current status.

### 7.3 Offer → Proposal interactions

**R-29:** An offer may be added to a proposal only if: the offer is in `reviewed` status, the offer's `valid_until` has not passed, and the parent proposal is in `draft` status.

**R-30:** Adding an offer to a proposal sets the offer status to `selected`. Removing an offer from a draft proposal sets the offer status back to `reviewed`.

**R-31:** An offer that is `selected` into one proposal cannot be added to a different proposal simultaneously. If the operator attempts this, the system must block and display which proposal already holds this offer.

### 7.4 Proposal → Booking interactions

**R-32:** When a proposal moves to `accepted`, the system must: (1) create a booking record (see R-21), (2) set all offers in `proposal_offer` for this proposal to `selected` permanently (they remain `selected` and are frozen), (3) set the parent request status to `processing` (it remains there until booking completion).

**R-33:** When a booking moves to `completed`, the system must automatically move the parent request to `completed`.

**R-34:** When a booking is `cancelled`, the parent request reverts to `processing`. The operator must then decide whether to build a new proposal or cancel the request.

---

## 8. Partial Offer Handling

### 8.1 What partial means

A partial offer means the supplier is responding to only a subset of what the RFQ asked for. The RFQ is tied to a single `service_type`. Partiality within a service type means the supplier covers some but not all of the scope — for example: the RFQ asks for accommodation for 10 nights and the supplier can only quote 7, or the RFQ asks for transport between 3 cities and the supplier can only cover 2 legs.

### 8.2 Coverage tracking

**R-35:** Coverage is tracked at the offer level via two fields: `covered_services` (what this offer covers, in text/JSON) and `uncovered_services` (what this offer does not cover). These fields are entered by the operator when recording the offer. The system does not automatically compute coverage gaps.

**R-36:** The operator is responsible for assessing whether the combination of offers in a proposal covers all services requested by the agency. The system does not perform this assessment automatically in MVP. It is an operator judgment call.

**R-37:** The `proposal_offer` pivot table has an `operator_notes` field. The operator must use this to document why each offer was chosen and how it addresses coverage. This is required before a proposal can be sent (see section 10).

### 8.3 Combining partial offers

**R-38:** Multiple partial offers from different suppliers may be added to the same proposal, provided they cover different (non-overlapping) services. The system will warn but not block when overlapping coverage is detected (see R-18).

**R-39:** IF no single offer fully covers a service type AND two partial offers together provide full coverage THEN the operator may add both to the proposal. The operator must document in `operator_notes` how the two partial offers together satisfy the requirement.

**R-40:** IF no offer (full or partial combination) covers a requested service AND the operator chooses to proceed THEN the operator must add an explicit gap note to the proposal `description` field before sending. The system does not enforce this structurally in MVP — it is a procedural requirement documented here for operator training.

### 8.4 Coverage status summary (operator-facing)

Although the system does not auto-compute coverage, the operator's proposal assembly screen must display, for each offer added:
- `covered_services` from each offer
- `uncovered_services` from each partial offer
- The `service_type` of the RFQ each offer came from

This allows the operator to visually verify completeness before sending.

---

## 9. Supplier Non-Response and Timeout Logic

### 9.1 MVP approach

In MVP, there is no automated supplier non-response enforcement. The operator monitors deadlines manually. The system provides information; the operator makes decisions.

### 9.2 What the system records

Each row in `rfq_supplier` records `sent_at` (when the RFQ was sent to that supplier). The `rfqs` table records `deadline_at`. Together, these give the operator a clear picture of who has not responded.

**R-41:** For every RFQ in `sent` or `awaiting` status, the system must be able to produce a list of suppliers who have not submitted an offer, along with how many days have passed since `sent_at` and how many days remain until `deadline_at`.

**R-42:** When `deadline_at` passes on an RFQ in `sent` or `awaiting` status, the system must display a visual alert to the operator (e.g. a badge or banner on the RFQ record). The system must NOT automatically close the RFQ or change any status.

### 9.3 Operator actions when supplier does not respond

The operator has four options when a supplier has not responded by deadline. These are manual decisions, not system states:

| Operator decision | What they do in the system |
|---|---|
| Wait longer | Take no action. The RFQ remains open. |
| Find a replacement supplier | Add a new supplier to the RFQ via rfq_supplier, note the extension manually. |
| Proceed without this supplier | Close the RFQ manually. Accept the coverage gap. |
| Cancel the request | Cancel the request if no viable sourcing path exists. |

**R-43:** The system must allow the operator to add a new supplier to an existing `sent` or `awaiting` RFQ at any time before it is `closed`. Adding a supplier does not reset the deadline.

**R-44:** There is no formal `unresponsive` flag on suppliers in MVP. Non-response is an operational judgment the operator makes. [POST-MVP: supplier reliability scoring based on response rate]

---

## 10. Proposal Creation Conditions

### 10.1 Conditions that must be true before a proposal can be created (draft)

| # | Condition | What to check |
|---|---|---|
| PC-1 | Parent request exists and is in `processing` status | requests.status = 'processing' |
| PC-2 | No other proposal in `draft` or `sent` status exists for this request | COUNT of proposals WHERE request_id = X AND status IN ('draft', 'sent') = 0 |
| PC-3 | At least one RFQ linked to this request is in `closed` status | COUNT of rfqs WHERE request_id = X AND status = 'closed' >= 1 |
| PC-4 | At least one offer exists across all RFQs for this request | COUNT of offers WHERE rfq_id IN (rfqs for this request) >= 1 |

### 10.2 Conditions that must be true before a proposal can be sent (draft → sent)

| # | Condition | What to check |
|---|---|---|
| PS-1 | Proposal has title and description set | proposals.title NOT NULL, proposals.description NOT NULL and NOT empty |
| PS-2 | Proposal has total_price and currency set | proposals.total_price NOT NULL and > 0, proposals.currency NOT NULL |
| PS-3 | Proposal has valid_until set and it is in the future | proposals.valid_until NOT NULL and > today |
| PS-4 | At least one offer is linked to this proposal | COUNT of proposal_offer WHERE proposal_id = X >= 1 |
| PS-5 | All linked offers are in `selected` status and not expired | offers.status = 'selected' AND offers.valid_until >= today for all offers in this proposal |
| PS-6 | All entries in proposal_offer have operator_notes set | operator_notes NOT NULL and NOT empty for all rows in proposal_offer for this proposal |

### 10.3 What is NOT required before sending a proposal

The following are intentional non-requirements:

- Full service coverage across all request services_needed — the operator decides whether to proceed with gaps.
- All RFQs for the request must be closed — the operator may send a proposal while other RFQs are still open.
- A minimum number of offers — one offer is sufficient if it covers what the operator deems adequate.

These non-requirements reflect the architecture decision that the operator's judgment supersedes mechanical completeness checks.

---

## 11. Blocking Conditions by State Transition

A consolidated reference. IF the listed condition is not met, THEN the transition must be refused by the system with a descriptive error message.

### 11.1 Request transitions

| Transition | Blocking condition(s) |
|---|---|
| `draft → submitted` | destination is null; OR travel_date_from is null; OR travel_date_to is null; OR pax_count < 1; OR services_needed is empty; OR travel_date_to < travel_date_from |
| `submitted → processing` | No RFQ exists for this request in any status |
| `processing → completed` | No booking linked to this request is in `completed` status |
| `processing → cancelled` | A booking in `confirmed` or `in_progress` status exists for this request |

### 11.2 RFQ transitions

| Transition | Blocking condition(s) |
|---|---|
| `draft` creation | Parent request is not in `submitted` or `processing` |
| `draft → sent` | title is null; OR description is null; OR service_type is null; OR deadline_at is null; OR no supplier assigned via rfq_supplier |
| `sent → awaiting` | No offer in `received` status exists for this RFQ (this transition is triggered by offer entry, so it cannot be manually triggered without an offer) |

### 11.3 Offer transitions

| Transition | Blocking condition(s) |
|---|---|
| `received` creation | Parent RFQ is not in `sent` or `awaiting`; OR rfq_id missing; OR supplier_id missing; OR unit_price missing or <= 0; OR currency missing; OR valid_until missing or in the past; OR covered_services empty; OR (is_partial = true AND uncovered_services empty); OR (is_partial = false AND uncovered_services not empty) |
| `reviewed → selected` | offer.valid_until < today; OR parent RFQ is cancelled |
| `selected → reviewed` (removal) | Parent proposal is not in `draft` status |

### 11.4 Proposal transitions

| Transition | Blocking condition(s) |
|---|---|
| `draft` creation | Parent request is not in `processing`; OR another proposal in `draft` or `sent` already exists for this request |
| `draft → sent` | Any of PS-1 through PS-6 from section 10.2 is not satisfied |
| `sent → accepted` | Proposal is not in `sent` status; OR proposal.valid_until < today |

### 11.5 Booking transitions

| Transition | Blocking condition(s) |
|---|---|
| `confirmed` creation | Parent proposal is not in `accepted` status (booking is auto-created; this block prevents orphan creation) |
| `in_progress → completed` | notes field is null or empty |
| `confirmed → cancelled` | notes field is null or empty |
| `in_progress → cancelled` | notes field is null or empty |

---

## 12. Operator Decision Points

Every point at which the system stops and requires a human operator action. These are features. They are not gaps in automation. The operator's judgment is the product.

| # | Point in workflow | Decision the operator makes | What happens if they don't act |
|---|---|---|---|
| OD-1 | Request arrives in `submitted` | Assess if the request is workable. Open it (triggering `processing`) or cancel. | Request sits in `submitted` indefinitely with no progress. |
| OD-2 | Creating RFQs from a request | Decide how to split the request into service categories. How many RFQs to create. Which suppliers to assign to each. What deadline to set. | No sourcing happens. Request stays in `processing` with no offers. |
| OD-3 | Monitoring RFQ deadlines | Decide whether to wait, find replacement suppliers, or proceed with gaps when a deadline passes without a full response set. | RFQ stays open past deadline. No automatic escalation. |
| OD-4 | Receiving a partial offer | Decide if partial coverage is acceptable. Identify the gap. Decide whether another supplier can fill it or the gap is acceptable to the agency. | Partial offer sits in `reviewed`. No further sourcing for the gap happens unless operator initiates. |
| OD-5 | Closing an RFQ | Decide when enough offers have been received to stop waiting. Acknowledge that late offers will not be accepted after closing. | RFQ stays `awaiting` indefinitely. |
| OD-6 | Building a proposal | Select which offers to include. Resolve coverage gaps. Set the agency-facing price. Write the proposal narrative. | No proposal exists. Request stalls. |
| OD-7 | Offer expiry during proposal build | If a selected offer expires before the proposal is sent, decide whether to find a replacement offer or adjust the proposal. | The system will block the proposal send (condition PS-5). The proposal cannot be sent until the expired offer is removed or replaced. |
| OD-8 | Sending proposal to agency | Review the assembled proposal for accuracy. Confirm the price is correct. Confirm coverage is acceptable. | Proposal stays in `draft`. Agency does not see it. |
| OD-9 | Proposal rejected by agency | Decide whether to rebuild the proposal (new `draft`), renegotiate with suppliers, or cancel the request. | Request stalls in `processing` indefinitely. |
| OD-10 | Proposal expired (no agency response) | Decide whether to rebuild and re-send, or cancel the request. | Request stalls in `processing`. Agency has no active proposal to act on. |
| OD-11 | Supplier withdrawal (post-proposal-sent) | Assess impact. The proposal may need to be rebuilt. Agency may need to be informed. A replacement offer must be sourced. | If the booking is already `confirmed`, the operator must resolve operationally. The booking remains `confirmed` but the underlying offer is `withdrawn`. |
| OD-12 | Booking: mark in progress | When travel begins, operator marks booking `in_progress`. | Booking stays `confirmed` past travel start date. No system impact; only a record accuracy issue. |
| OD-13 | Booking: mark completed | After all services are delivered, operator marks booking `completed` and writes completion notes. | Request never moves to `completed`. Booking stays `in_progress` indefinitely. |
| OD-14 | Booking cancellation | If a booking must be cancelled, operator records the reason and cancels. Must then decide whether to rebuild the proposal or cancel the request. | No system action occurs. The operational situation is unresolved. |

---

## 13. Edge Case Handling

### 13.1 Missing Supplier (no viable supplier found for a service type)

**Scenario:** The operator has sent an RFQ to all known suppliers for a service type. The deadline has passed. No offer has been received.

**Business rules:**

**EC-01:** The operator must manually close the RFQ. The `sent → closed` transition is valid even with zero offers.

**EC-02:** IF the operator closes an RFQ with zero offers AND that service type was requested by the agency THEN the operator must decide one of:
- (a) Source the gap from a supplier not in the system (operator enters an offer manually after off-system contact)
- (b) Build a proposal that does not cover that service type and explicitly notes the gap in the proposal description
- (c) Cancel the request if the missing service is essential

**EC-03:** The system does not block proposal creation because an RFQ has no offers. The operator's judgment governs whether a gap is acceptable.

**EC-04:** If the operator chooses option (b), the proposal description must explicitly reference the uncovered service type. This is a procedural requirement for MVP. [POST-MVP: a structured gap-acknowledgment field on proposals]

---

### 13.2 Partial Coverage (no single supplier covers all services)

**Scenario:** Multiple RFQs exist. Each has received offers. But across all offers, some services from the original request are not covered by any offer.

**Business rules:**

**EC-05:** The operator must review all received offers across all RFQs before building a proposal.

**EC-06:** The operator may combine offers from different RFQs (even different service types) into a single proposal via the `proposal_offer` pivot. There is no constraint that each offer in a proposal must come from a different RFQ.

**EC-07:** IF the combination of all `selected` offers in a proposal still does not cover all services requested AND the operator proceeds to send THEN the proposal description field must contain explicit acknowledgment of the gap. The system must display a warning at send time listing which RFQ service types have no corresponding offer in the proposal, but it must NOT block the send.

**EC-08:** The agency's decision to accept or reject a proposal with coverage gaps is their prerogative. The system records what was agreed when accepted.

---

### 13.3 Supplier Withdrawal (supplier retracts offer after proposal is built or sent)

**Scenario:** A supplier informs the operator that they are withdrawing an offer that is currently `selected` in a proposal.

**This requires an additional status value:** `withdrawn` on offers (see section 4.1).

**Business rules:**

**EC-09:** The operator records the withdrawal by changing the offer status to `withdrawn`. This is an explicit operator action, not automatic.

**EC-10:** IF the offer is `withdrawn` AND the proposal is in `draft` status THEN:
- The offer is automatically removed from the `proposal_offer` pivot.
- The proposal remains in `draft`.
- The operator must source a replacement offer and add it to the proposal before the proposal can be sent (PS-5 will block sending with a `withdrawn` offer in the pivot).

**EC-11:** IF the offer is `withdrawn` AND the proposal is in `sent` status THEN:
- The system must flag the proposal with a `has_withdrawn_offer` warning.
- The proposal is NOT automatically reverted to `draft` — revoking a sent proposal may confuse the agency.
- The operator must decide: inform the agency and rebuild the proposal, OR source a replacement and send a new proposal version, OR cancel the request.
- No system action is automatic. The operator resolves this manually.

**EC-12:** IF the offer is `withdrawn` AND the proposal is in `accepted` status (booking exists) THEN:
- The booking remains `confirmed`. The booking is a record of what was agreed, not what is currently operationally possible.
- The system must flag the booking with an alert indicating a withdrawn offer is in its source proposal.
- The operator must resolve this operationally (find a replacement supplier off-platform or cancel the booking).
- Cancelling the booking requires notes explaining the supplier withdrawal.

**EC-13:** The `withdrawn` status is permanent. A withdrawn offer cannot be reactivated. If the supplier agrees to re-submit, a new offer record must be created.

---

### 13.4 Expired Pricing (offer valid_until passes before proposal is accepted)

**Scenario:** An offer was `selected` into a proposal. Before the agency accepted the proposal, the offer's `valid_until` date passed.

**Business rules:**

**EC-14:** Offer expiry is detected on-read in MVP. When any view of an offer or proposal is loaded, the system checks `valid_until` against today. If expired, the offer is marked `expired` immediately.

**EC-15:** IF a `selected` offer becomes `expired` AND the proposal is in `draft` status THEN:
- The proposal send is blocked (condition PS-5).
- The operator is alerted that an offer in their draft has expired.
- The operator must remove the expired offer from the proposal and replace it (new offer from the same supplier or a different one).

**EC-16:** IF a `selected` offer becomes `expired` AND the proposal is in `sent` status THEN:
- The proposal is NOT automatically recalled or reverted.
- The system must display a warning on the proposal record.
- The operator must contact the supplier to confirm whether pricing is still valid.
- If the supplier agrees to extend: the operator creates a new offer record with an updated `valid_until` and replaces the expired offer in the proposal. The proposal must be reverted to `draft`, the expired offer removed, the new offer added, and the proposal re-sent.
- If the supplier does not agree to extend: the operator must rebuild the proposal with an alternative offer.

**EC-17:** IF a `selected` offer becomes `expired` AND the proposal is in `accepted` status (booking created) THEN:
- The booking remains `confirmed`. The booking price is locked at proposal acceptance (bookings.final_price is copied from proposals.total_price).
- The underlying offer expiry does not change the agreed price.
- The operator must confirm with the supplier whether they will honor the price for the confirmed booking. This is an off-system negotiation.
- If the supplier refuses to honor the price: the operator must decide whether to cancel the booking or absorb the difference. The system supports either path.

**EC-18:** The `valid_until` field on a proposal is independent of offer `valid_until` dates. A proposal may remain valid even after some of its source offers have expired. The proposal's own `valid_until` is the deadline for the agency to respond.

---

### 13.5 Group Size Change (agency updates pax_count after RFQ is sent)

**Scenario:** An agency changes their group size (pax_count) after the operator has already sent RFQs to suppliers, received offers, or built a proposal.

**Business rules:**

**EC-19:** `pax_count` is a field on the `requests` table. There is no version history on this field in MVP. The operator must be warned that changing pax_count can invalidate existing offers (which were priced for a different group size).

**EC-20:** The system must record a `pax_count_changed_at` timestamp on requests whenever pax_count is updated. [MVP alternative: if timestamp column is not added, the operator must be shown a mandatory confirmation dialog before saving a pax_count change that reads: "Changing pax count may invalidate existing supplier offers. Review all offers after saving."]

**EC-21:** IF pax_count changes AND RFQs exist in `sent` or `awaiting` status for this request THEN:
- The system must display a warning listing all open RFQs.
- The operator must decide whether to: (a) notify suppliers of the pax change (off-system) and request revised offers, or (b) close the RFQs, discard existing offers, and re-issue new RFQs with the updated pax count.

**EC-22:** IF pax_count changes AND offers exist in `received` or `reviewed` status THEN:
- The system must flag all existing offers for this request with a "pax count has changed — re-verify pricing" warning.
- The offers are NOT automatically expired or invalidated. The operator manually determines which offers are still valid.

**EC-23:** IF pax_count changes AND a proposal exists in `draft` status THEN:
- The system must warn the operator.
- The proposal total_price likely needs revision (supplier unit prices may be per-person).
- The proposal cannot be sent until the operator manually confirms the price is still accurate (operator must edit and re-save the proposal to clear the warning).

**EC-24:** IF pax_count changes AND a proposal is in `sent` status THEN:
- The operator must contact the agency to confirm the change.
- The operator must decide whether to recall the proposal (move back to `draft`), revise it, and re-send.
- There is no automatic proposal recall — the operator manages this manually.

**EC-25:** IF pax_count changes AND a booking exists in `confirmed` status THEN:
- The booking's pax_count field is copied from the request at booking creation. It is independent of subsequent request changes.
- The operator must manually update the booking's pax_count if the group size change is confirmed.
- A change to booking pax_count must require operator confirmation and a note explaining the change.

---

## 14. Business Rule Catalog

A numbered, searchable reference of all If/Then rules in this document.

| Rule | Summary | Section |
|---|---|---|
| R-01 | Block request submission if required fields are missing | 2.3 |
| R-02 | Warn (not block) if travel_date_from is in the past | 2.3 |
| R-03 | Block if travel_date_to < travel_date_from | 2.3 |
| R-04 | Block if pax_count < 1 | 2.3 |
| R-05 | Block processing transition if no RFQ exists | 2.3 |
| R-06 | No actions on children of a cancelled request | 2.3 |
| R-07 | No new RFQs or proposals against a completed request | 2.3 |
| R-08 | Each RFQ has exactly one service_type | 3.3 |
| R-09 | Multiple RFQs per service_type per request are valid | 3.3 |
| R-10 | Cancelling a request cancels draft/sent RFQs | 3.3 |
| R-11 | RFQ deadline is informational only in MVP | 3.4 |
| R-12 | Show visual warning when RFQ is past deadline | 3.4 |
| R-13 | Partial offer must have uncovered_services populated | 4.3 |
| R-14 | Non-partial offer must not have uncovered_services populated | 4.3 |
| R-15 | Partial offers may be added to proposals — no block | 4.3 |
| R-16 | covered_services is mandatory on all offers | 4.3 |
| R-17 | Multiple offers per RFQ are allowed | 4.4 |
| R-18 | Warn (not block) when two offers for same service_type are added to same proposal | 4.4 |
| R-19 | Only one active (draft/sent) proposal per request | 5.3 |
| R-20 | Rejected/expired proposals are retained as history | 5.3 |
| R-21 | Booking creation copies fields from proposal and request | 6.3 |
| R-22 | Booking creation must be atomic with proposal acceptance | 6.3 |
| R-23 | Request stays processing while booking is active | 6.3 |
| R-24 | RFQ creation transitions request to processing if submitted | 7.1 |
| R-25 | Request cancellation cascades to draft/sent RFQs | 7.1 |
| R-26 | Offers only against sent/awaiting RFQs | 7.2 |
| R-27 | First offer entry auto-advances RFQ from sent to awaiting | 7.2 |
| R-28 | Closing RFQ does not change offer statuses | 7.2 |
| R-29 | Offer add to proposal: offer must be reviewed, not expired, proposal must be draft | 7.3 |
| R-30 | Adding/removing offer from proposal changes offer status | 7.3 |
| R-31 | A selected offer cannot be in two proposals simultaneously | 7.3 |
| R-32 | Proposal acceptance freezes selected offers and keeps request in processing | 7.4 |
| R-33 | Booking completion moves request to completed | 7.4 |
| R-34 | Booking cancellation reverts request to processing | 7.4 |
| R-35 | Coverage tracking is manual (operator-entered) | 8.2 |
| R-36 | Operator assesses coverage completeness — no system enforcement | 8.2 |
| R-37 | operator_notes required in proposal_offer before send | 8.2 |
| R-38 | Multiple partials for different services may be combined | 8.3 |
| R-39 | Two partials covering the same service together may both be added | 8.3 |
| R-40 | Proceed-with-gap requires operator to note the gap in proposal description | 8.3 |
| R-41 | System must show non-responding suppliers per RFQ | 9.2 |
| R-42 | Overdue RFQ must show visual alert; no auto-close | 9.2 |
| R-43 | Operator may add a new supplier to an open RFQ at any time | 9.3 |
| R-44 | No formal unresponsive flag on suppliers in MVP | 9.3 |

---

## 15. Validation Rule Catalog

Validation rules are checked by the system before any data is persisted or any status transition is executed. Failures return descriptive error messages to the operator.

### 15.1 Request validation

| Field | Rule |
|---|---|
| destination | Required, non-empty string |
| travel_date_from | Required, valid date |
| travel_date_to | Required, valid date, must be >= travel_date_from |
| pax_count | Required, integer >= 1 |
| services_needed | Required, non-empty string |
| status transitions | Must follow allowed transitions table (section 2.1) |

### 15.2 RFQ validation

| Field | Rule |
|---|---|
| request_id | Required, must reference a request in `submitted` or `processing` |
| service_type | Required, must be a value from the defined enum |
| title | Required, non-empty string |
| description | Required, non-empty string |
| deadline_at | Required when moving draft → sent, must be a future date |
| rfq_supplier entries | At least one required before draft → sent |
| status transitions | Must follow allowed transitions table (section 3.1) |

### 15.3 Offer validation

| Field | Rule |
|---|---|
| rfq_id | Required, must reference an RFQ in `sent` or `awaiting` |
| supplier_id | Required, must reference a user with role `supplier` |
| unit_price | Required, numeric, > 0 |
| currency | Required, non-empty string (ISO 4217 code recommended) |
| valid_until | Required, must be a future date at time of entry |
| covered_services | Required, non-empty |
| is_partial + uncovered_services | If is_partial = true, uncovered_services must be non-empty. If is_partial = false, uncovered_services must be null/empty |
| status transitions | Must follow allowed transitions table (section 4.1) |

### 15.4 Proposal validation

| Field / Condition | Rule |
|---|---|
| request_id | Required, must reference a request in `processing` |
| Uniqueness check | No other proposal in `draft` or `sent` for same request_id |
| title | Required, non-empty string |
| description | Required, non-empty string |
| total_price | Required, numeric, > 0 |
| currency | Required, non-empty string |
| valid_until | Required, must be a future date |
| proposal_offer entries | At least one required before draft → sent |
| All linked offers | Must be in `selected` status and not expired (valid_until >= today) |
| operator_notes on pivot | Must be non-empty for all proposal_offer rows before draft → sent |
| status transitions | Must follow allowed transitions table (section 5.1) |

### 15.5 Booking validation

| Field / Condition | Rule |
|---|---|
| proposal_id | Required, must reference a proposal in `accepted` status |
| notes (on complete) | Required, non-empty string before in_progress → completed |
| notes (on cancel) | Required, non-empty string before confirmed/in_progress → cancelled |
| status transitions | Must follow allowed transitions table (section 6.1) |

---

*End of workflow design document.*
*This document governs the business logic layer. Implementation must not deviate from these rules without updating this document first.*
*Owner: Operator team. Review cadence: on any proposal to change lifecycle states or add new modules.*
