<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StoreInfo;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Cache;
use Google_Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    // تسجيل دخول
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();


        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $user->img = $user->img ? asset(Storage::url($user->img)) : null;


        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function loginWithgoogle(Request $request)
    {
        $idToken = $request->input('token');

        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($idToken);

        if (!$payload) {
            return response()->json(['error' => 'Invalid Google token'], 401);
        }

        $email = $payload['email'];
        $name = $payload['name'] ?? '';


        $user = User::where('email', $email)->first();

        if (!$user) {

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(16)),
            ]);
        }


        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    // تسجيل خروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function GetNotifications()
    {
        $user = Auth::user();
        $Notifications = $user->Notifications()->orderBy('created_at', 'desc')->limit(10)->get();

        $mapped = $Notifications->map(function ($notification) {
            $typeMap = [
                'App\\Notifications\\AdminBroadcastNotification' => 'admin_notification',
                'App\\Notifications\\AdminOrderPlaced' => 'order_received_notification',
            ];

            $notification['type'] = $typeMap[$notification['type']] ?? 'Unknown Notification';
            return $notification;
        });
        return response()->json($Notifications);
    }

    public function DeleteNotification($noti_id)
    {
        $deleted = DB::table('notifications')
            ->where('id', $noti_id)
            ->delete();

        return $deleted
            ? response()->json(['message' => 'Notification deleted'], 200)
            : response()->json(['error' => 'Notification not found'], 404);
    }

    public function DeleteNotifications()
    {
        $user = Auth::user();

        $deleted = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->delete();

        return $deleted
            ? response()->json(['message' => 'Notifications deleted'], 200)
            : response()->json(['error' => 'error'], 404);
    }

    public function ReadNotification($noti_id)
    {
        $notification = DatabaseNotification::whereKey($noti_id)->firstOrFail();
        $notification->markAsRead();
        return response()->json([], 200);
    }
    public function ReadNotifications()
    {
        DatabaseNotification::where('notifiable_id', Auth::id())->get()->markAsRead();

        return response()->json([], 200);
    }

    public function SendCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $request->validate(['email' => 'required|email']);

        $code = rand(100000, 999999);

        $code = 123456;  //for test

        Cache::put('reset_' . $request->email, $code, now()->addMinutes(15));


        Mail::send('emails.reset_code', [
            'code' => $code,
            'email' => $request->email,
        ], function ($message) use ($request) {
            $message->to($request->email)
                ->subject('رمز إعادة تعيين كلمة المرور');
        });


        return response()->json(['message' => 'تم إرسال رمز إعادة تعيين كلمة المرور.']);
    }
    public function PasswordResetConfirm(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|numeric',
        ]);


        $cachedCode = Cache::get('reset_' . $request->email);

        if ($cachedCode && $cachedCode == $request->code) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['error' => 'رمز غير صحيح أو منتهي الصلاحية'], 422);
    }

    public function PasswordResetUpdate(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'code'     => 'required|numeric',
            'password' => 'required|string|min:6|confirmed',
        ]);



        $cachedCode = Cache::get('reset_' . $request->email);


        if (!$cachedCode || $cachedCode != $request->code) {
            return response()->json(['error' => 'رمز التحقق غير صالح أو منتهي الصلاحية'], 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();


        Cache::forget('reset_' . $request->email);

        return response()->json(['message' => 'تم تحديث كلمة المرور بنجاح']);
    }

    public function SendTestMail(Request $request)
    {


        Mail::send('emails.order_confirmed', [
            'email' => $request->email,
        ], function ($message) use ($request) {
            $message->to('walid.reda345@gmail.com')
                ->subject('✅ طلبك رقم #1234 تم بنجاح – شكراً لثقتك بنا! 🙌');
        });
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'phone'        => 'nullable|string|max:20',
          
            'location'  => 'nullable|string|max:250',
            'img'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);


        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->location = $request->location;
 


        if ($request->hasFile('img')) {

            if ($user->img && Storage::exists($user->img)) {
                Storage::delete($user->img);
            }

            // Store new image

            $imgPath = $request->file('img')->store('users', 'public');


            $user->img = $imgPath;
        }

        $user->save();

        $user->img = $user->img ? asset(Storage::url($user->img)) : null;
        $token = $user->createToken('api-token')->plainTextToken;


        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
            'token' => $token
        ]);
    }
}
