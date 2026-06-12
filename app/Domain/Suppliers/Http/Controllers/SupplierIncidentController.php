<?php

namespace App\Domain\Suppliers\Http\Controllers;

use App\Domain\Suppliers\Http\Resources\SupplierIncidentResource;
use App\Domain\Suppliers\Models\Supplier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierIncidentController extends Controller
{
    public function index(Supplier $supplier): AnonymousResourceCollection
    {
        $this->authorize('viewIncidents', $supplier);

        $incidents = $supplier->incidents()
            ->orderByDesc('created_at')
            ->get();

        return SupplierIncidentResource::collection($incidents);
    }
}
