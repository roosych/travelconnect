---
name: Core Entity Schema — Field Reference
description: Agreed database entities and their fields from the initial architecture design
type: project
---

Entities agreed in docs/architecture.md v1.0. Use these as the canonical field reference when writing migrations or models.

**users**: id, name, email, password, role (enum: agency/operator/supplier), company_name, phone, timestamps

**requests**: id, agency_id (FK users), title, destination, travel_date_from, travel_date_to, pax_count, services_needed (text), notes, status (enum: draft/submitted/processing/completed/cancelled), timestamps

**rfqs**: id, request_id (FK requests), operator_id (FK users), title, description, service_type, deadline_at, status (enum: draft/sent/awaiting/closed), timestamps

**rfq_supplier** (pivot): id, rfq_id, supplier_id (FK users), sent_at

**offers**: id, rfq_id (FK rfqs), supplier_id (FK users), is_partial (bool), covered_services (text), uncovered_services (text), unit_price, currency, valid_until, notes, status (enum: received/reviewed/selected/rejected/expired), timestamps

**proposals**: id, request_id (FK requests), operator_id (FK users), title, description, total_price, currency, valid_until, status (enum: draft/sent/accepted/rejected/expired), timestamps

**proposal_offer** (pivot): id, proposal_id, offer_id, operator_notes

**bookings**: id, proposal_id (FK proposals), request_id (FK requests), agency_id (FK users), operator_id (FK users), confirmed_at, travel_date_from, travel_date_to, pax_count, final_price, currency, status (enum: confirmed/in_progress/completed/cancelled), notes, timestamps

**Why:** Having the schema agreed before coding prevents drift between architecture decisions and implementation.

**How to apply:** When generating migrations or Eloquent models, use these fields as the source of truth. Do not add columns not listed here without checking with the user first.
