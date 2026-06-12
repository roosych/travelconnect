<?php

namespace App\Http\Controllers\Web\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function index(Request $request)
    {
        $supplier = $request->user()->suppliers()->first();

        return view('pages.supplier.cabinet.employees.index', compact('supplier'));
    }
}
