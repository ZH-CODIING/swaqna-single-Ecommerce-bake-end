<?php

namespace App\Http\Controllers;

use App\Models\payment_gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentGateController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
            'short_description' => 'nullable|string',
            'logo' => 'nullable|file|image|max:2048',
            'faqs' => 'nullable',
            'website' => 'nullable|url',
            'status' => 'nullable|in:0,1',
            'commission' => 'nullable|numeric|min:0|max:100',
        ]);

        $gate = payment_gate::firstOrNew(['name' => $validated['name']]);

        $gate->note = $validated['note'] ?? $gate->note;
        $gate->short_description = $validated['short_description'] ?? $gate->short_description;
        $gate->website = $validated['website'] ?? $gate->website;
        $gate->status = $validated['status'];
        $gate->commission = $validated['commission'] ?? $gate->commission;

        if ($request->hasFile('logo')) {
            if ($gate->logo && Storage::exists($gate->logo)) {
                Storage::delete($gate->logo);
            }
            $gate->logo = 'storage/' . $request->file('logo')->store('payment_gate_logos', 'public');
        }


        $faq_jsoned = null;
        if (isset($validated['faqs'])) {
            if (is_string($validated['faqs'])) {
                $decoded = json_decode($validated['faqs'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $faq_jsoned = $decoded;
                }
            } elseif (is_array($validated['faqs'])) {
                $faq_jsoned = $validated['faqs'];
            }
            $gate->faqs = json_encode($faq_jsoned);
        }

        $gate->save();

        return response()->json(['message' => 'Payment Gate saved', 'data' => $gate]);
    }
    public function UpdateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:0,1|numeric',

        ]);

        $gate = payment_gate::findorfail($id);

        $gate->status = $validated['status'] ?? $gate->note;

        $gate->save();

        return response()->json(['message' => 'Status updated', 'data' => $gate]);
    }


    public function index()
    {
        $gates = payment_gate::all();

        foreach ($gates as $value) {
            $value->faqs = json_decode($value->faqs);
        }
        return response()->json($gates);
    }

    public function destroy($id)
    {
        $gate = payment_gate::findOrFail($id);

        if ($gate->logo && Storage::exists($gate->logo)) {
            Storage::delete($gate->logo);
        }

        $gate->delete();

        return response()->json(['message' => 'Payment Gate deleted']);
    }
}
