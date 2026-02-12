<?php


namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\shipping_gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CartController extends Controller
{
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }
    public function index(Request $request)
    {
        $userId = Auth::id();
        $cacheKey = 'cart:' . $userId;


        $items = Cache::remember($cacheKey, 300, function () use ($userId) {
            return CartItem::with('product:id,price,name,img,rating,quantity,specs,weight')->where('user_id', $userId)->get();
        });

        try {
            foreach ($items as $item) {
                $item->specs = json_decode($item->specs);
            }
        } catch (\Throwable $th) {
            
        }

        return response()->json([
            'data' => $items
        ]);
    }

    //Admin Index Cart To display All carts 

    public function indexAdmin(Request $request)
    {

        $this->checkAdmin();
        $items = CartItem::latest()->with('user', 'product:id,price,name,img,rating,quantity,specs')->paginate(30);
        return response()->json([
            'data' => $items
        ]);
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'specs'   => 'nullable',
            
        ]);

        $userId = Auth::id();
        $item = CartItem::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $validated['product_id']],
            ['quantity' => $validated['quantity'] , 'specs' =>  json_encode( $validated['specs'] ?? []  )     ]
        );
        $cacheKey = 'cart:' . $userId;

        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Item added/updated successfully',
            'data' => $item
        ]);
    }

    public function clear()
    {
        $userId = Auth::id();

        CartItem::where('user_id', $userId)->delete();

        $cacheKey = 'cart:' . $userId;

        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Cart cleared successfully'
        ]);
    }
    public function clearOne($prdouct_id)
    {
        $userId = Auth::id();

        CartItem::where('user_id', $userId)->where('product_id', $prdouct_id)->delete();

        $cacheKey = 'cart:' . $userId;

        Cache::forget($cacheKey);
        return response()->json([
            'message' => 'Cart item cleared successfully'
        ]);
    }

    protected function GetOrderwehgit($items)
    {
        $wehgit = 0;

        foreach ($items as $item) {
            $wehgit += $item->quantity * $item->product->weight;
        }

        return $wehgit;
    }

    public function CalculateShipping(request $request)
    {
        $request->validate([
            'shiping_gate' => 'required',
            'city' => 'required',
            'cod' => 'required|boolean'
        ]);
        $userId = Auth::id();
        $cacheKey = 'cart:' . $userId;

        $city = $request->city;
        $shiping_gate = $request->shiping_gate;
        $cod = $request->cod;



        $items = Cache::remember($cacheKey, 3600 * 5, function () use ($userId) {
            return CartItem::with('product:id,price,name,img,rating,quantity,specs,weight')->where('user_id', $userId)->get();
        });

        //Get weight

        $wehgit = $this->GetOrderwehgit($items);

        if ($shiping_gate  === 'fastlo') {
            $shiping_cost = $this->CalculateShippmentForFastlo($city, $cod, $wehgit);
        } elseif ($shiping_gate  === 'naqel') {
            $shiping_cost = $this->CalculateShippmentForNaqel($cod, $wehgit);
        }
        return response()->json(['Status' => 'ok', 'shiping_cost' => $shiping_cost]);
    }

    protected function doesCityExist(string $cityName): bool
    {
        $allowedCities = [
            'abha',
            'abuarish',
            'ahadalmasarihah',
            'ahadrafidah',
            'alahsa',
            'alasyah',
            'alayun',
            'aljafr',
            'aljouf',
            'alkhutamah',
            'anak',
            'aqiq',
            'artawiyah',
            'asfan',
            'badaya',
            'baha',
            'bahrah',
            'baish',
            'baljurashi',
            'bellasmar',
            'billahmar',
            'bukayriyah',
            'buqayq',
            'buraydah',
            'dahaban',
            'dammam',
            'darb',
            'dawmataljandal',
            'dhahran',
            'dhahranaljanoub',
            'dhamad',
            'dhurma',
            'dilam',
            'ghat',
            'hafaralbaten',
            'hail',
            'hanakiyah',
            'hawtatbanitamim',
            'hawtatsudayr',
            'hufuf',
            'huraymila',
            'jamoum',
            'jazan',
            'jeddah',
            'jubail',
            'khabra',
            'khafji',
            'khamismushayt',
            'kharj',
            'khobar',
            'khulais',
            'kingabdullaheconomiccity',
            'kingkhalidmilitarycity',
            'madinah',
            'majmaah',
            'makkah',
            'mandaq',
            'midhnab',
            'mobarraz',
            'muhayil',
            'muzahimiyah',
            'najran',
            'nammas',
            'onaizah',
            'qatif',
            'qurayyat',
            'qunfudhah',
            'rabigh',
            'rafha',
            'ranyah',
            'rass',
            'riyadh',
            'sabya',
            'safwa',
            'sakaka',
            'samtah',
            'shaqra',
            'sharurah',
            'tabuk',
            'taif',
            'tarut',
            'turaif',
            'tathlith',
            'unayzah',
            'uqayr',
            'uyunaljiwa',
            'wadiad-dawasir',
            'yanbu',
            'zulfi',
        ];

        $sanitizedCityName = Str::lower(str_replace(' ', '', $cityName));
        return in_array($sanitizedCityName, $allowedCities);
    }
    public function CalculateShippmentForFastlo($city, $codOption, $wehgit)  //For Fastlo 
    {

        $city =  Str::lower(trim($city));
        $shipping_price = 0;
        $fastlo = shipping_gate::where('name', 'fastlo')->first();



        if (!$fastlo) {
            abort(500, 'Shippment Doesnt Selected from admin');
        }

        $diff_price = 0;   //Diffrant price between Platforum And trader
        //Culcluting City Logic 
        $exists = $this->doesCityExist($city);
        if ($exists || ($city === $fastlo->city)) {
            $shipping_price += $fastlo->trader_price;
            $diff_price =  $fastlo->trader_price - $fastlo->price;
        } else {
            $shipping_price += $fastlo->trader_second_price;
            $diff_price =  $fastlo->trader_second_price - $fastlo->second_price;
        }

        //Wegiht 

        $wehgit_price = ($wehgit > 5) ? ((round($wehgit) - 5) * $fastlo->kg_additional) : 0;
        $shipping_price += $wehgit_price;

        //Cod Charge
        if ($codOption) {
            $shipping_price += ($shipping_price * $fastlo->cod_charge / 100);
        }


        return $shipping_price;
    }
    public function CalculateShippmentForNaqel($codOption, $wehgit)  //For Naqel 
    {


        $shipping_price = 0;
        $naqel = shipping_gate::where('name', 'naqel')->first();
        $shipping_price += $naqel->trader_price;


        //Wegiht 

        $wehgit_price = ($wehgit > 3) ? ((round($wehgit) - 3) * $naqel->kg_additional) : 0;
        $shipping_price += $wehgit_price;

        if ($codOption) {
            $shipping_price +=   $naqel->cod_charge;
        }


        $shipping_price =  Round($shipping_price * 1.15);


        return $shipping_price;
    }
}
