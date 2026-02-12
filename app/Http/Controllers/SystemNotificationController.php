<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\Request;

class SystemNotificationController extends Controller
{
    public function index()
    {
        $notifications = SystemNotification::all();
        return response()->json($notifications);
    }

    public function store(Request $request)
    {


        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        $notification = SystemNotification::create($validatedData);
        return response()->json($notification, 201);
    }

    public function show(SystemNotification $notification)
    {
        return response()->json($notification);
    }

    public function update(Request $request, SystemNotification $notification)
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'phone_number' => 'required|string',

        ]);

        $notification->update($validatedData);
        return response()->json($notification);
    }

    public function destroy(SystemNotification $notification)
    {
        $notification->delete();
        return response()->json(null, 204);
    }
}
