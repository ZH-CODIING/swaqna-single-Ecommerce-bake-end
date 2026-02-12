<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IframeSetting;

class IframeController extends Controller
{
    public function index()
    {
        $setting = IframeSetting::first();
        if (!$setting) {
            return response()->json(['message' => 'Settings not found'], 404);
        }
        return response()->json($setting);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'facebook' => 'required|boolean',
            'instagram' => 'required|boolean',
            'twitter' => 'required|boolean',
            'whatsapp' => 'required|boolean',
        ]);

        $setting = IframeSetting::first();

        if (!$setting) {
            $setting = IframeSetting::create($validated);
        } else {
            $setting->update($validated);
        }

        return response()->json($setting);
    }
}
