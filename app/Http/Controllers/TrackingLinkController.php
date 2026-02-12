<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\TrackingLink;
use App\Models\Coordinator;
use Illuminate\Http\Request;

class TrackingLinkController extends Controller
{
    public function store(Request $request)
    {
        $messages = [
            'title.required' => 'حقل العنوان مطلوب.',
            'title.string' => 'يجب أن يكون العنوان نصًا.',
            'title.max' => 'يجب ألا يتجاوز العنوان 255 حرفًا.',
            'coordinator_id.required' => 'حقل معرف المنسق مطلوب.',
            'coordinator_id.exists' => 'معرف المنسق غير موجود.',
            'custom_keyword.string' => 'يجب أن تكون الكلمة الدلالية نصًا.',
            'custom_keyword.max' => 'يجب ألا تتجاوز الكلمة الدلالية 255 حرفًا.',
            'added_date.required' => 'حقل تاريخ الإضافة مطلوب.',
            'added_date.date' => 'يجب أن يكون تاريخ الإضافة تاريخًا صحيحًا.',
            'base_url.required' => 'الرابط الأساسي مطلوب.',
            'base_url.url' => 'يجب أن يكون الرابط الأساسي رابطًا صحيحًا.',
        ];

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'coordinator_id' => 'required|exists:coordinators,id',
            'custom_keyword' => 'nullable|string|max:255',
            'added_date' => 'required|date',
            'base_url' => 'required|url',
        ], $messages);

        $coordinator = Coordinator::findOrFail($validatedData['coordinator_id']);
        

        $baseUrl = rtrim($validatedData['base_url'], '/');


        


        $trackingLink = TrackingLink::create([
            'title' => $validatedData['title'],
            'custom_keyword' => $validatedData['custom_keyword'] ?? '',
            'added_date' => $validatedData['added_date'],
            'url' => null,
            'coordinator_id' => $coordinator->id,
        ]);
        
        $trackingUrl = "{$baseUrl}?tracking_link_id={$trackingLink->id}&custom_keyword=" . urlencode($validatedData['custom_keyword'] ?? '');
        
        $trackingLink->update(['url' => $trackingUrl] );

        $trackingLinkUrl =     $request->base_url . "/api/tracking/{$trackingLink->id}";

        return response()->json(['tracking_link' => $trackingLinkUrl]);
    }


    public function track($id, Request $request)
    {
        $trackingLink = TrackingLink::find($id);

        if (!$trackingLink) {

            return response()->json(['message' => 'رابط التتبع غير موجود'], 404);
        }


        $trackingLink->increment('visits');


        // إذا كان هناك عملية شراء
        if ($request->input('purchase') === 'true') {
            $trackingLink->increment('purchases_count');
        }


        $redirectUrl = $trackingLink->url;

        if (!filter_var($redirectUrl, FILTER_VALIDATE_URL)) {

            return response()->json(['message' => 'الرابط غير صحيح'], 400);
        }

        return redirect()->to($redirectUrl);
    }


    public function tracking($id, Request $request)
    {
        $trackingLink = TrackingLink::find($id);

        if (!$trackingLink) {

            return response()->json(['message' => 'رابط التتبع غير موجود'], 404);
        }

        // تحديث عدد الزيارات إذا كانت موجودة في الطلب
        if ($request->has('visits')) {
            $visits = $request->input('visits', 0);
            $trackingLink->increment('visits', $visits);
        }

        // تحديث عدد عمليات الشراء إذا كانت موجودة في الطلب
        if ($request->has('purchases_count')) {
            $purchasesCount = $request->input('purchases_count', 0);
            $trackingLink->increment('purchases_count', $purchasesCount);
        }


        return response()->json([
            'message' => 'تم تحديث البيانات بنجاح',
            'data' => $trackingLink,
        ], 200);
    }

    public function show($id)
    {
        $trackingLink = TrackingLink::find($id);

        if (!$trackingLink) {
            return response()->json(['message' => 'رابط التتبع غير موجود'], 404);
        }
        return response()->json([
            'id' => $trackingLink->id,
            'title' => $trackingLink->title,
            'custom_keyword' => $trackingLink->custom_keyword,
            'added_date' => $trackingLink->added_date,
            'visits' => $trackingLink->visits,
            'new_donors' => $trackingLink->new_donors,
            'donation_transactions' => $trackingLink->donation_transactions,
            'donations' => $trackingLink->donations,
            'url' => $trackingLink->url,
            'purchases_count' => $trackingLink->purchases_count,
            'is_archived' => $trackingLink->is_archived,
            'created_at' => $trackingLink->created_at->toDateTimeString(),
            'updated_at' => $trackingLink->updated_at->toDateTimeString(),
            'coordinator_id' => $trackingLink->coordinator_id,
        ], 200);
    }

    public function index()
    {
        $trackingLinks = TrackingLink::with('coordinator')->get();

        $trackingLinks->transform(function ($link) {
            $link->url = url("api/tracking/{$link->id}");
            return $link;
        });



        return response()->json($trackingLinks, 200);
    }

    public function archive($id)
    {
        $trackingLink = TrackingLink::findOrFail($id);
        $trackingLink->is_archived = true;
        $trackingLink->save();

        Log::info('تم أرشفة الرابط بنجاح', ['tracking_link_id' => $id]);

        return response()->json(['message' => 'تمت أرشفة الرابط بنجاح']);
    }

    public function unarchive($id)
    {
        $trackingLink = TrackingLink::findOrFail($id);
        $trackingLink->is_archived = false;
        $trackingLink->save();

        return response()->json(['message' => 'تم إلغاء أرشفة الرابط بنجاح']);
    }
    public function update(Request $request, $id)
    {
        $messages = [
            'title.required' => 'حقل العنوان مطلوب.',
            'title.string' => 'يجب أن يكون العنوان نصًا.',
            'title.max' => 'يجب ألا يتجاوز العنوان 255 حرفًا.',
            'coordinator_id.required' => 'حقل معرف المنسق مطلوب.',
            'coordinator_id.exists' => 'معرف المنسق غير موجود.',
            'custom_keyword.string' => 'يجب أن تكون الكلمة الدلالية نصًا.',
            'custom_keyword.max' => 'يجب ألا تتجاوز الكلمة الدلالية 255 حرفًا.',
            'added_date.required' => 'حقل تاريخ الإضافة مطلوب.',
            'added_date.date' => 'يجب أن يكون تاريخ الإضافة تاريخًا صحيحًا.',
            'base_url.required' => 'الرابط الأساسي مطلوب.',
            'base_url.url' => 'يجب أن يكون الرابط الأساسي رابطًا صحيحًا.',
        ];

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'coordinator_id' => 'required|exists:coordinators,id',
            'custom_keyword' => 'nullable|string|max:255',
            'added_date' => 'required|date',
            'base_url' => 'required|url',
        ], $messages);


        $trackingLink = TrackingLink::findOrFail($id);

        $coordinator = Coordinator::findOrFail($validatedData['coordinator_id']);


        $baseUrl = rtrim($validatedData['base_url'], '/');


        $trackingUrl = "{$baseUrl}?coordinator_id={$coordinator->id}&custom_keyword=" . urlencode($validatedData['custom_keyword'] ?? '');

        // Update tracking link
        $trackingLink->update([
            'title' => $validatedData['title'],
            'custom_keyword' => $validatedData['custom_keyword'] ?? '',
            'added_date' => $validatedData['added_date'],
            'url' => $trackingUrl,
            'coordinator_id' => $coordinator->id,
        ]);

        $trackingLinkUrl = $request->base_url . "/api/tracking/{$trackingLink->id}";

        return response()->json([
            'message' => 'تم تحديث الرابط بنجاح.',
            'tracking_link' => $trackingLinkUrl,
        ]);
    }
    public function destroy($id)
    {
        $trackingLink = TrackingLink::findOrFail($id);

        $trackingLink->delete();

        return response()->json([
            'message' => 'تم حذف الرابط بنجاح.'
        ], 200);
    }
}
