<?php

namespace App\Http\View\Composers;

use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\RFQs\Enums\RfqStatus;
use App\Domain\RFQs\Models\Rfq;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * "Needs attention" counters for the supplier menu. Currently: open RFQs the
 * supplier still has to respond to (sent/awaiting and no active offer yet).
 * Cached briefly per supplier so the badge doesn't add a query to every page.
 */
class SupplierMenuBadgeComposer
{
    public function compose(View $view): void
    {
        $badges = ['rfqs' => 0];

        $user = Auth::user();
        $supplierIds = $user ? $user->suppliers()->pluck('suppliers.id') : collect();

        if ($supplierIds->isNotEmpty()) {
            $key = 'supplier.menu_badges.' . $supplierIds->sort()->implode('-');

            $badges = Cache::remember($key, now()->addSeconds(60), function () use ($supplierIds) {
                // Open RFQs assigned to this supplier with no active offer yet —
                // i.e. запросы, на которые поставщик ещё должен ответить.
                $awaiting = Rfq::whereIn('status', [RfqStatus::Sent->value, RfqStatus::Awaiting->value])
                    ->whereHas('suppliers', fn ($q) => $q->whereIn('suppliers.id', $supplierIds))
                    ->whereDoesntHave('offers', fn ($q) => $q
                        ->whereIn('supplier_id', $supplierIds)
                        ->whereNotIn('status', [
                            OfferStatus::Rejected->value,
                            OfferStatus::Expired->value,
                            OfferStatus::Withdrawn->value,
                        ]))
                    ->count();

                return ['rfqs' => $awaiting];
            });
        }

        $view->with('supplierMenuBadges', $badges);
    }
}
