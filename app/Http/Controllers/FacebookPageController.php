<?php

namespace App\Http\Controllers;

use App\Models\FacebookPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FacebookPageController extends Controller
{
    // تخزين بيانات الصفحة والتوكن
    public function store(Request $request)
    {
        $request->validate([
            'page_id' => 'required|string|unique:facebook_pages,page_id',
            'page_name' => 'required|string',
            'access_token' => 'required|string',
        ]);

        $page = FacebookPage::create($request->only(['page_id', 'page_name', 'access_token']));

        return response()->json(['message' => 'تم الحفظ بنجاح', 'page' => $page]);
    }

    // نشر منشور على الصفحة

    
    
public function postToPage(Request $request, $pageId)
{
    $request->validate([
        'message' => 'required|string',
        'access_token' => 'required|string',
    ]);

    $response = Http::post("https://graph.facebook.com/v23.0/{$pageId}/feed", [
        'message' => $request->message,
        'access_token' => $request->access_token,
    ]);

    if ($response->successful()) {
        return response()->json([
            'message' => 'تم نشر المنشور بنجاح.',
            'data' => $response->json()
        ]);
    } else {
        return response()->json([
            'error' => 'فشل في نشر المنشور.',
            'details' => $response->json()
        ], $response->status());
    }
}


public function commentOnPost(Request $request, $postId)
{
    // تحقق من صحة البيانات
    $request->validate([
        'message' => 'required|string',
        'access_token' => 'required|string',
    ]);

    // إرسال التعليق باستخدام التوكن المرسل
    $response = Http::post("https://graph.facebook.com/v23.0/{$postId}/comments", [
        'message' => $request->message,
        'access_token' => $request->access_token,
    ]);

    // معالجة الاستجابة
    if ($response->successful()) {
        return response()->json([
            'message' => 'تم نشر التعليق بنجاح.',
            'data' => $response->json()
        ]);
    } else {
        return response()->json([
            'error' => 'فشل في نشر التعليق.',
            'details' => $response->json()
        ], $response->status());
    }

  }

public function replyToComment(Request $request, $commentId)
{
    $request->validate([
        'message' => 'required|string',
        'access_token' => 'required|string',
    ]);

    $response = Http::post("https://graph.facebook.com/v23.0/{$commentId}/comments", [
        'message' => $request->message,
        'access_token' => $request->access_token,
    ]);

    if ($response->successful()) {
        return response()->json([
            'message' => 'تم إرسال الرد بنجاح.',
            'data' => $response->json()
        ]);
    } else {
        return response()->json([
            'error' => 'فشل في إرسال الرد.',
            'details' => $response->json()
        ], $response->status());
    }
}

}
