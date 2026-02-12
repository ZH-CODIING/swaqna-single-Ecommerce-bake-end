<?php

namespace App\Http\Controllers;

use App\Models\shipping_gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShippingGateController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
            'city' => 'nullable|string',
            'logo' => 'nullable|string',
            'website' => 'nullable|url',
            'price' => 'required|numeric|gt:1',
            'second_price' => 'nullable|numeric|gt:1',
            'cod_charge' => 'required|numeric',
            'kg_additional' => 'required|numeric',
        ]);

        $gate = shipping_gate::firstOrNew(['name' => $validated['name']]);
        $gate->note = $validated['note'] ?? $gate->note;
        $gate->website = $validated['website'] ?? $gate->website;
        $gate->price = $validated['price'];
        $gate->second_price = $validated['second_price'] ?? $gate->price;
        $gate->logo = $validated['logo'] ?? $gate->logo;

        //Trader Price maybe asgined by trader later from dashboard
        $gate->trader_price = $validated['price'];
        $gate->trader_second_price = $validated['second_price'] ?? $gate->price;

 
        $gate->cod_charge = $validated['cod_charge'];
        $gate->kg_additional = $validated['kg_additional'];
        if ($validated['name'] != 'naqel') {
            $gate->city =   Str::lower(trim($validated['city']));
        } else {
            $gate->city =   trim($validated['city']);
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

        return response()->json(['message' => 'Shipping Gate saved', 'data' => $gate]);
    }

    public function UpdateGateByAdmin(Request $request)
    {

        $data = $request->validate([
            'trader_price' => 'required|numeric|gt:1',
            'trader_second_price' => 'nullable|numeric|gt:1',
            'city' => 'required',
            'name' => 'required|string'
        ]);


        $gate = shipping_gate::where('name', $data['name'])->first();
        if ($data['trader_price'] < $gate->price) {
            abort(403, 'Trader Price Cant be less than Shipping Price');
        }
        $data['city'] =  Str::lower($data['city']);
        $gate->update($data);

        return response()->json([
            'message' => 'Updated successfully',
            'shipping_gate' => $gate
        ]);
    }

    public function DeleteGate(Request $request, $name)
    {
        shipping_gate::where('name', $name)->delete();
        return response()->json(['message' => 'Status Saved']);
    }

    public function index()
    {
        $gates = shipping_gate::all();

        foreach ($gates as $value) {
            $value->faqs = json_decode($value->faqs);
        }
        return response()->json($gates);
    }
}
