---
name: B2B RFQ Platform — Core Architecture
description: Domain structure, key decisions, and scaffold state after initial implementation
type: project
---

Laravel 13 / PHP 8.3 monolith. PostgreSQL via Docker Compose (Sail). DB host is 127.0.0.1 in .env (not the docker hostname "pgsql").

Domain lives under `app/Domain/{Module}/` with Models/, Services/, Enums/, Http/Controllers/, Http/Requests/ subdirs.

App\Models\User is a thin alias extending App\Domain\Users\Models\User — keeps Laravel scaffolding (auth config, factories) happy.

**Entities implemented:** TravelRequest, Rfq, Offer, Proposal, Booking, User (extended).

**Pivot tables:** rfq_supplier (rfq_id, supplier_id, sent_at), proposal_offer (proposal_id, offer_id, operator_notes).

**Key design decisions:**
- travel_requests.destination/travel_date_from/travel_date_to/services_needed are nullable at DB level (draft can be incomplete); service layer validates completeness at submit() time.
- No supplier portal — operator enters all offers manually via OfferService::recordOffer().
- RFQ auto-transitions sent→awaiting when first offer is recorded (the only automatic transition).
- Request auto-transitions submitted→processing when first RFQ is created.
- Booking creation is atomic inside ProposalService::accept() via DB::transaction.
- Booking completion auto-transitions parent request to completed; cancellation reverts to processing.

**Two typed domain exceptions:** App\Exceptions\Domain\InvalidStatusTransitionException, App\Exceptions\Domain\BusinessRuleException.

**Why:** keeps exception handling granular (controllers can catch each type separately for appropriate HTTP response codes).

**How to apply:** all service methods throw one of these two — never return error codes or booleans for failure cases.

Seeded users (password: "password"): operator@b2btravel.test, agency1@b2btravel.test, agency2@b2btravel.test, supplier1-3@b2btravel.test.

**HTTP layer implemented (2026-05-06):** Full API layer built on top of domain. Laravel Sanctum installed (^4.3) for token auth. personal_access_tokens migration published.

HTTP layer file locations:
- Controllers: app/Domain/{Module}/Http/Controllers/
- FormRequests: app/Domain/{Module}/Http/Requests/
- Resources: app/Domain/{Module}/Http/Resources/
- Routes: routes/api.php (35 routes, all auth:sanctum except POST /api/auth/login)
- Exception rendering: bootstrap/app.php ($exceptions->render() for domain exceptions)
- Custom Handler: app/Exceptions/Handler.php (unauthenticated → 401 JSON, invalidJson → 422 JSON)

bootstrap/app.php uses withSingletons to bind App\Exceptions\Handler, withRouting includes api: routes/api.php, and withMiddleware calls statefulApi().

config/auth.php provider updated to use App\Domain\Users\Models\User (not App\Models\User).

User model had HasApiTokens added (required by Sanctum — the only intentional domain model touch).
