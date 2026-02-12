<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CoordinatorController extends Controller
{
    public function index(Request $request)
    {
        $this->checkAdmin();

        $perPage = 15;

        $coordinators = Coordinator::latest()->paginate($perPage);

        return response()->json($coordinators);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:coordinators,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        $coordinator = Coordinator::create($validated);

        return response()->json($coordinator, 201);
    }


    public function show($id)
    {
        $coordinator = Coordinator::findOrFail($id);
        return response()->json($coordinator);
    }


    public function update(Request $request, $id)
    {
        $coordinator = Coordinator::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:coordinators,email,' . $coordinator->id,
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $coordinator->update($validated);

        return response()->json($coordinator);
    }

    public function destroy($id)
    {
        $coordinator = Coordinator::findOrFail($id);
        $coordinator->delete();

        return response()->json(['message' => 'Coordinator deleted successfully.']);
    }

    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }
}
