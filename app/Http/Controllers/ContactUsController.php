<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ContactUsController extends Controller
{
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    // عرض كل الرسائل (للمشرف فقط)
    public function index()
    {
        $cacheSeconds = env('DEFAULT_CACHE_VALUE');

        $contacts = Cache::remember('contact_us' ,$cacheSeconds, function () {
            return ContactUs::all();
        });

        return response()->json($contacts);
    }

    public function show($id)
    {
        $this->checkAdmin();



        $cacheSeconds = env('DEFAULT_CACHE_VALUE');

        $contact = Cache::remember('contact_us_' . $id ,$cacheSeconds, function () use($id) {
            return ContactUs::findOrFail($id);
        });

        return response()->json($contact);
    }

    // استقبال رسالة من المستخدم (مفتوح للجميع)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        $contact = ContactUs::create($validated);

        //Reset contact us cache

        $this->ResetCache('contact_us');
        return response()->json([
            'message' => 'Contact info created successfully',
            'contact' => $contact,
        ], 201);
    }

    // تعديل رسالة (للمشرف فقط)
    public function update(Request $request, $id)
    {


        $contact = ContactUs::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        $contact->update($validated);

        //Reset contact us cache

        $this->ResetCache('contact_us');
        $this->ResetCache('contact_us_' . $id);

        return response()->json([
            'message' => 'Contact info updated successfully',
            'contact' => $contact,
        ]);
    }

    // حذف رسالة (للمشرف فقط)
    public function destroy($id)
    {
        $this->checkAdmin();

        $contact = ContactUs::findOrFail($id);
        $contact->delete();


        $this->ResetCache('contact_us');
        $this->ResetCache('contact_us_' . $id);

        return response()->json(['message' => 'Contact info deleted successfully']);
    }
}
