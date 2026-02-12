<?php

namespace App\Http\Controllers;

use App\Jobs\EmailAllUsers;
use App\Models\User;
use App\Models\whatsapp_limit;
use App\Notifications\AdminBroadcastNotification;
use App\Traits\StatsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Notification;

class ServiceController extends Controller
{
    use StatsTrait;
    protected function checkAdmin()
    {
        $user = Auth::user();



        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }
    public function enableWhatsappService(Request $request)
    {

        $request->validate(['token' => 'required']);

        $token = $request->input("token");

        if (!$this->HasAccess($token)) {
            abort(401, "Unauthorized");
        }

        $limit = whatsapp_limit::first();
        $limit->update(['status' => 1]);
        if ($request->filled('add_messages')) {
            $limit->increment('remaining_messages', (int)$request->add_messages);
        }
        return response()->json(['status' => 'تم تفعيل خدمة الواتس اب بنجاح']);
    }

    public function disableWhatsappService(Request $request)
    {

        $request->validate(['token' => 'required']);

        $token = $request->input("token");

        if (!$this->HasAccess($token)) {
            abort(401, "Unauthorized");
        }

        $limit = whatsapp_limit::first();
        $limit->update(['status' => 1]);

        return response()->json(['status' => 'تم إلغاء تفعيل خدمة الواتس اب بنجاح']);
    }


    public function AddWhatsappLimit(request $request)
    {
        $request->validate(['token' => 'required', 'messages_count' => ['required']]);

        $token = $request->input("token");

        if (!$this->HasAccess($token)) {
            abort(401, "Unauthorized");
        }

        $limit = whatsapp_limit::first();
        $limit->increment('remaining_messages', $request->messages_count);

        return response()->json([
            'status' => 'تم إتاحة عدد من رسائل الواتس اب إلى التاجر بنجاح',
            'new_limit' => $limit->remaining_messages
        ]);
    }

    public function HasAccess($token)
    {
        return $token == env('ACCESS_DASHBOARD_TOKEN') ? true : false;
    }

    public function enableShippingService(Request $request)
    {


        $request->validate([
            'token' => 'required',
            'SHIPPING_TOKEN' => 'required|string',
            'SHIPPING_PROVIDER' => 'required|string|max:100',
        ]);
        if (!$this->HasAccess($request->token)) {
            abort(401, 'unauthorized ');
        }
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $envUpdates = [
            'SHIPPING_ENABLED' => 'true',
        ];

        foreach ($envUpdates as $key => $value) {
            if (strpos($str, "$key=") !== false) {
                $str = preg_replace("/^$key=.*/m", "$key=$value", $str);
            } else {
                $str .= "\n$key=$value";
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        file_put_contents($envFile, $str);

        Artisan::call('config:clear');
        return response()->json([
            'message' => 'Shipping Service has been enabled',
        ]);
    }

    public function UpdateEnvKey(Request $request)
    {


        $request->validate([
            'token' => 'required',
            'ENV_KEY' => 'required|string',
            'ENV_VALUE' => 'required|string',
        ]);

        if (!$this->HasAccess($request->token)) {
            abort(401, 'unauthorized');
        }

        $key = strtoupper(trim($request->ENV_KEY));
        $value = trim($request->ENV_VALUE);

        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (strpos($str, "$key=") !== false) {
            $str = preg_replace("/^$key=.*$/m", "$key=$value", $str);
        } else {
            $str .= "\n$key=$value";
        }

        file_put_contents($envFile, $str);


        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;

        Artisan::call('config:clear');

        return response()->json([
            'message' => "$key has been set to $value.",
        ]);
    }

    public function GetWhatsAppStatus(request $request)
    {
        $request->validate([
            'token' => 'required',
        ]);
        if (!$this->HasAccess($request->token)) {
            abort(401, 'unauthorized ');
        }

        $limit = whatsapp_limit::first();

        return response()->json([
            'status' => $limit->status,
        ]);
    }

    public function GetShippingServiceStatus(request $request)
    {
        $request->validate([
            'token' => 'required',
        ]);
        if (!$this->HasAccess($request->token)) {
            abort(401, 'unauthorized ');
        }

        return response()->json([
            'status' => env('SHIPPING_ENABLED'),
        ]);
    }


    public function MailToUsers(Request $request)
    {
        $request->validate([
            'subject' => 'required|string',
            'html'    => 'required|string',
            'email'   => 'email|required'
        ]);


        $HTML = $request->html;
        $subject = $request->subject;

        EmailAllUsers::dispatch($HTML, $subject);



        return response()->json(['message' => 'Email Marketting is being Porccessing ...']);
    }

    public function EmailMarkettingStatus(request $request)
    {

        return response()->json([
            'email_marketing_enabled' => env('email_marketing_enabled'),
            'MAIL_MAILER'      => env('MAIL_MAILER'),
            'MAIL_HOST'        => env('MAIL_HOST'),
            'MAIL_PORT'        => env('MAIL_PORT'),
            'MAIL_USERNAME'    => env('MAIL_USERNAME'),
            'MAIL_PASSWORD'    => env('MAIL_PASSWORD'),
            'MAIL_ENCRYPTION'  => env('MAIL_ENCRYPTION'),
            'MAIL_FROM_ADDRESS'=> env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME'   => env('MAIL_FROM_NAME'),
        ]);
    }


    public function enableMailMarketing(Request $request)
    {

        if (!$this->HasAccess($request->token)) {
            abort(401, 'unauthorized ');
        }
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $envUpdates = [
            'email_marketing_enabled' => 'true',
        ];

        foreach ($envUpdates as $key => $value) {
            if (strpos($str, "$key=") !== false) {
                $str = preg_replace("/^$key=.*/m", "$key=$value", $str);
            } else {
                $str .= "\n$key=$value";
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        file_put_contents($envFile, $str);


        Artisan::call('config:clear');

        return response()->json([
            'message' => 'Email Marketing has been enabled successfully',
        ]);
    }

    public function disableMailMarketing(Request $request)
    {

        if (!$this->HasAccess($request->token)) {
            abort(401, 'unauthorized');
        }
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $envUpdates = [
            'email_marketing_enabled' => 'false',
        ];

        foreach ($envUpdates as $key => $value) {
            if (strpos($str, "$key=") !== false) {
                $str = preg_replace("/^$key=.*/m", "$key=$value", $str);
            } else {
                $str .= "\n$key=$value";
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        file_put_contents($envFile, $str);


        return response()->json([
            'message' => 'Email Marketing has been disabled successfully',
        ]);
    }

    public function EnableTaqnyatSms(Request $request)
    {
        $this->checkAdmin();
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $request->validate([
            'TAQNYAT_SMS_TOKEN' => 'required|string'
        ]);

        $envUpdates = [
            'TAQNYAT_SMS_TOKEN' => $request->TAQNYAT_SMS_TOKEN,
            'TAQNYAT_SENDER_NAME' => $request->TAQNYAT_SENDER_NAME,
        ];

        foreach ($envUpdates as $key => $value) {
            if (strpos($str, "$key=") !== false) {
                $str = preg_replace("/^$key=.*/m", "$key=$value", $str);
            } else {
                $str .= "\n$key=$value";
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        file_put_contents($envFile, $str);


        return response()->json([
            'message' => 'Taqnyat Sms Service enabled successfully',
        ]);
    }
    public function SetupEmail(Request $request)
    {
        $this->checkAdmin();
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);



        $request->validate([
            'MAIL_HOST' => 'required|string',
            'MAIL_PORT' => 'required|string',
            'MAIL_USERNAME' => 'required|string',
            'MAIL_PASSWORD' => 'required|string',
            'MAIL_FROM_NAME' => 'required|string',


        ]);

        $envUpdates = [
            'MAIL_HOST' => $request->MAIL_HOST,
            'MAIL_PORT' => $request->MAIL_PORT,
            'MAIL_USERNAME' => $request->MAIL_USERNAME,
            'MAIL_PASSWORD' => $request->MAIL_PASSWORD,
            'MAIL_FROM_ADDRESS' => $request->MAIL_USERNAME,
            'MAIL_FROM_NAME' => $request->MAIL_FROM_NAME,
            'email_marketing_enabled' => true
        ];

        foreach ($envUpdates as $key => $value) {
            if (strpos($str, "$key=") !== false) {
                $str = preg_replace("/^$key=.*/m", "$key=$value", $str);
            } else {
                $str .= "\n$key=$value";
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        file_put_contents($envFile, $str);

        Artisan::call('config:clear');



        return response()->json([
            'message' => 'Mail SMTP Settting setup successfully',
        ]);
    }
    public function getTaqnyatSmsToken()
    {
        $this->checkAdmin();
        $token = env('TAQNYAT_SMS_TOKEN');
        $sender_name = env('TAQNYAT_SENDER_NAME');

        return response()->json([
            'token' => $token,
            'sender_name' => $sender_name,
        ], 200);
    }

    public function SendUsersNotifcation(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'message' => 'required|string',
            'title'   => 'required|string',
            'emails'  => 'required',
        ]);

        foreach ($request->emails as $email) {
            Mail::send('emails.general_notify_template', [
                'title'       => $request->title,
                'bodyContent' => $request->message,
            ], function ($message) use ($email, $request) {
                $message->to($email)
                    ->subject($request->title);
            });
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Emails sent successfully',
        ]);
    }
    public function AppendUserVisit()
    {
        $this->trackVisit(1, null);  // 1 Mean Store Visit
    }
    public function getStats()
    {
        return response()->json($this->getChartData('store', null));
    }

    public function getUsers(Request $request)
    {
        $request->validate([
            'type'  => 'required|string',
        ]);

        $type = $request->type;
        $users = collect();

        switch ($type) {
            case 'broadcastAll':
                $users = User::select('name', 'phone', 'email')
                    ->where('role', 'user')
                    ->get();
                break;

            case 'cartAbandoned':
                $users = User::select('name', 'phone', 'email')
                    ->whereHas('cartItems')
                    ->get();
                break;

            case 'previousBuyers':
                $users = User::select('name', 'phone', 'email')
                    ->whereHas('orders')
                    ->get();
                break;

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid type provided'
                ], 400);
        }

        return response()->json([
            'status' => true,
            'count'  => $users->count(),
            'users'  => $users
        ]);
    }
}
