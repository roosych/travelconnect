<?php

namespace App\Providers;

use App\Domain\Agencies\Models\Agency;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Clients\Models\Client;
use App\Domain\Offers\Models\Offer;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\Bookings\Events\BookingStatusChanged;
use App\Domain\Notifications\Listeners\SendBookingNotification;
use App\Domain\Notifications\Listeners\SendOfferDecisionNotification;
use App\Domain\Notifications\Listeners\SendOperatorOfferNotification;
use App\Domain\Notifications\Listeners\SendOperatorProposalNotification;
use App\Domain\Notifications\Listeners\SendOperatorRequestNotification;
use App\Domain\Notifications\Listeners\SendProposalNotification;
use App\Domain\Notifications\Listeners\SendRfqNotification;
use App\Domain\Offers\Events\OfferAccepted;
use App\Domain\Offers\Events\OfferRejected;
use App\Domain\Offers\Events\OfferSubmitted;
use App\Domain\Notifications\Listeners\SendRequestStatusNotification;
use App\Domain\Proposals\Events\ProposalDecided;
use App\Domain\Proposals\Events\ProposalSent;
use App\Domain\Requests\Events\RequestStatusChanged;
use App\Domain\Requests\Events\RequestSubmitted;
use App\Domain\RFQs\Events\RfqSentToSupplier;
use App\Domain\Suppliers\Models\Supplier;
use App\Listeners\ActivateSupplierPortal;
use App\Policies\AgencyPolicy;
use App\Policies\BookingPolicy;
use App\Policies\ClientPolicy;
use App\Policies\OfferPolicy;
use App\Policies\ProposalPolicy;
use App\Policies\RfqPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\TravelRequestPolicy;
use Illuminate\Auth\Events\Login;
use App\Http\View\Composers\MenuBadgeComposer;
use App\Http\View\Composers\SupplierMenuBadgeComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(Login::class, ActivateSupplierPortal::class);

        // Notification dispatch (phase 1: mail + telegram).
        Event::listen(RfqSentToSupplier::class, SendRfqNotification::class);
        Event::listen(ProposalSent::class, SendProposalNotification::class);
        Event::listen(BookingStatusChanged::class, SendBookingNotification::class);
        Event::listen(RequestStatusChanged::class, SendRequestStatusNotification::class);
        Event::listen(OfferAccepted::class, [SendOfferDecisionNotification::class, 'accepted']);
        Event::listen(OfferRejected::class, [SendOfferDecisionNotification::class, 'rejected']);

        // Operator-facing (coordination layer between agencies and suppliers).
        Event::listen(OfferSubmitted::class, SendOperatorOfferNotification::class);
        Event::listen(ProposalDecided::class, SendOperatorProposalNotification::class);
        Event::listen(RequestSubmitted::class, SendOperatorRequestNotification::class);

        // Global API rate limit: 120 req/min per authenticated user (or per IP
        // for unauthenticated requests such as the public supplier portal).
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Throttle login attempts: 5/min per email+IP, then 20/min per IP as a
        // backstop against distributed credential stuffing.
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(5)->by(mb_strtolower($email).'|'.$request->ip()),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        Gate::policy(TravelRequest::class, TravelRequestPolicy::class);
        Gate::policy(Proposal::class, ProposalPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Rfq::class, RfqPolicy::class);
        Gate::policy(Offer::class, OfferPolicy::class);
        Gate::policy(Agency::class, AgencyPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);

        // Operator menu "needs attention" badges.
        View::composer('layouts.app', MenuBadgeComposer::class);

        // Supplier menu "needs attention" badges.
        View::composer('layouts.supplier', SupplierMenuBadgeComposer::class);
    }
}
