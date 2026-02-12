<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    public function GetCustomers(Request $request)
    {
        $perPage = 15;
        $page = $request->get('page', 1);

        $customers = User::where('role', 'user')
            ->with('orders')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'customers' => $customers
        ], 200);
    }
}
