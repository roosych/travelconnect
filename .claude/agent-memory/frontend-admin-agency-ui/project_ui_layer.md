---
name: B2B Travel RFQ — UI Layer Implementation
description: First working UI layer built from Keen Bootstrap template. All pages, layout system, and web routes are implemented.
type: project
---

The first full UI layer for the B2B Travel RFQ platform is implemented.

**Template used:** Keen Bootstrap 5 (KeenThemes) at `/public/ui_template`. Uses `ki-outline` icon classes, `badge-light-*` for status badges, `app-sidebar` + `app-main` layout structure, Keen's JS bundle (`scripts.bundle.js`).

**Architecture:** Layouts → Partials → Components → Pages (no duplication).

**Layout files:**
- `/resources/views/layouts/app.blade.php` — main authenticated layout with sidebar, toolbar, content area, toast helper, global `window.api` fetch wrapper
- `/resources/views/layouts/auth.blade.php` — blank layout for login page
- `/resources/views/partials/sidebar.blade.php` — icon-rail sidebar with RFQ nav items, logout JS
- `/resources/views/partials/footer.blade.php` — minimal footer

**Blade components:**
- `components/status-badge.blade.php` — maps all status strings to `badge-light-*` classes
- `components/page-header.blade.php` — title + breadcrumbs + actions slot
- `components/card.blade.php` — card wrapper with optional title/toolbar slots
- `components/empty-state.blade.php` — centered empty state with icon

**Pages built:**
- `auth/login.blade.php` — token-based login, stores Bearer token in localStorage
- `pages/dashboard/index.blade.php` — stat cards + recent requests table
- `pages/requests/index.blade.php` — full table with search/filter, create-request modal
- `pages/requests/show.blade.php` — tabbed view: RFQs | Offers | Proposals, create RFQ modal
- `pages/rfqs/index.blade.php` — aggregated RFQ list across all requests
- `pages/rfqs/show.blade.php` — RFQ details with suppliers panel + offers panel
- `pages/offers/index.blade.php` — all offers across all RFQs
- `pages/offers/show.blade.php` — single offer detail with context sidebar
- `pages/proposals/index.blade.php` — all proposals list
- `pages/proposals/create.blade.php` — create proposal form (takes `?request_id=`)
- `pages/proposals/show.blade.php` — proposal builder: included offers + available offers, add/remove
- `pages/bookings/index.blade.php` — bookings table with lifecycle actions
- `pages/bookings/show.blade.php` — booking detail with proposal context

**Web controllers** (thin, view-only): `/app/Http/Controllers/Web/`
- DashboardController, RequestWebController, RfqWebController, OfferWebController, ProposalWebController, BookingWebController

**Routes registered:** 16 web routes in `/routes/web.php`, all named (e.g., `dashboard`, `requests.index`, `rfqs.show`, `proposals.show`).

**Auth pattern:** Login posts to `/api/auth/login`, stores token in `localStorage('auth_token')`. Global `window.api` object wraps all fetch calls with Bearer token. 401 redirects to `/login`.

**Why:** The RFQ workflow requires fetching nested data (requests → rfqs → offers). The pages do this aggregation in JS since the API is nested. The `requests/show` is the central operator screen — tabs expose RFQs, all their offers, and proposals in one place.
