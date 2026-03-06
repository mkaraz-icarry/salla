<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Credential;
use Illuminate\Support\Facades\Cookie;
use App\ICarryShipping\ICarry;
use App\ICarryShipping\ICarryApi;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/**
 * Start Methods
 */

function saveUserSession($merchantId, $userSession) {

    //[API] Get merchant information
    // $storeResponse = Http::withHeaders([
    //     'Authorization' => "Bearer {$userSession->access_token}",
    //     'Accept' => 'application/json',
    //     //'X-Manager-Token' => $tokenDataResponse['access_token'],
    //     //'X-Salla-Security-Strategy' => env('WEBHOOK_SECRET'),
    //     'Accept-Language' => 'en'
    //     // Add any other headers you need
    // ])->get(env('API_BASE_URL') . '/oauth2/store/info');
    // // Log::debug("storeResponse => " . json_encode($storeResponse->json()));
    // $storeResponseData = null;
    // if ($storeResponse->successful()) {
    //     $storeResponseData = $storeResponse->json();
    // }

    // //Save information in the DB if not exist
    // $user_info = Credential::where('merchant_id', $merchantId)->first();


    // $user_info->merchant_id = $merchantId;
    // $user_info->merchant_email = $storeResponseData['data']['email'];
    // $user_info->merchant_store_id = $storeResponseData['data']['id'];
    // $user_info->merchant_store_url = $storeResponseData['data']['domain'];

    //Save information in the DB if not exist
    $user_info = Credential::where('merchant_id', $merchantId)->first();
    if (empty($user_info)) { //create new row
        $user_info = new Credential;
    }

    $user_info->merchant_id = $merchantId;
    $user_info->access_token = $userSession->access_token;
    $user_info->expires = $userSession->expires;
    $user_info->refresh_token = $userSession->refresh_token;
    $user_info->scope = $userSession->scope;
    $user_info->token_type = $userSession->token_type;

    $user_info->save();
}



function refreshToken($merchantId) {

    $user_info = Credential::where('merchant_id', $merchantId)->first();
    if (!$user_info) {
        // handle no user
    }
    $tokenDataResponse = Http::post(env('AUTH_URL') . '/oauth2/token', [
        'grant_type' => 'refresh_token',
        'refresh_token' => $user_info->refresh_token,
        'client_id' => env('CLIENT_ID'),
        'client_secret' => env('CLIENT_SECRET'),
        'redirect_uri' => env('APP_URL') . '/api/callback',
        //'scope' => 'offline_access'
    ]);

    // Log::debug("tokenData => " . json_encode($tokenDataResponse->json()));

    if ($tokenDataResponse->successful()) {
        $tokenDataResponse['expires'] = $tokenDataResponse['expires_in'];
        saveUserSession($user_info->merchant_id, $tokenDataResponse);
    }
}

function validateToken($merchantId) {
    $tokenExpired = true;

    $user_info = Credential::where('merchant_id', $merchantId)->first();

    if ($user_info) {
        if (isset($user_info->expires)) {
            // Get the current timestamp
            $currentTimestamp = time();

            // Compare the token's expiration timestamp with the current timestamp
            if ($user_info->expires > $currentTimestamp) {
                // Token is not expired
                $tokenExpired = false;
            } else {
                // Token has expired
                $tokenExpired = true;
            }
        } else {
            // Token does not have an expiration timestamp
            $tokenExpired = true;
        }

        if ($tokenExpired) {
            //refresh token
            refreshToken($merchantId);
            return true;
        }
    }
    return false;
}

function handleOrderCreateEvent($merchantId, $order) {
    Log::debug("Inside handle order creation");
    // try {

    // } catch (\Exception $e) {
    //     Log::error('An error occurred: ' . $e->getMessage());
    //     return response(['message' => ""], 500);
    // }
    // $APP_ID = env('APP_ID');
    // $user_info = Credential::where('merchant_id', $merchantId)->first();

    // $appSettingResponse = Http::withHeaders([
    //     'Authorization' => "Bearer {$user_info->access_token}",
    //     'Accept' => 'application/json',
    //     //'X-Manager-Token' => $tokenDataResponse['access_token'],
    //     //'X-Salla-Security-Strategy' => env('WEBHOOK_SECRET'),
    //     'Accept-Language' => 'en'
    //     // Add any other headers you need
    // ])->get(env('API_BASE_URL') . "/apps/{$APP_ID}/settings");
    // // Log::debug("storeResponse => " . json_encode($storeResponse->json()));
    // $appSettingData = null;
    // if ($appSettingResponse->successful()) {
    //     $appSettingData = $appSettingResponse->json();
    // }
    // Log::debug(json_encode($appSettingData));



    $APP_ID = env('APP_ID');
    $user_info = Credential::where('merchant_id', $merchantId)->first();
    $appSettingResponse = Http::withHeaders([
        'Authorization' => "Bearer {$user_info->access_token}",
        'Accept' => 'application/json',
        'Accept-Language' => 'en'
        // Add any other headers you need
    ])->get(env('API_BASE_URL') . "/apps/{$APP_ID}/settings");
    // Log::debug("storeResponse => " . json_encode($storeResponse->json()));
    $appSettingData = null;
    if ($appSettingResponse->successful()) {
        $appSettingData = $appSettingResponse->json();
    }
    Log::debug("APP SETTING Data=======> ".json_encode($appSettingData));
    //Log::debug("APP SETTING Response =======> ".json_encode($appSettingResponse));






    // $icarryStoreUrl = $appSettingData['data']['store_url'];
    // $icarryEmail = $appSettingData['data']['email'];
    // $icarryPassword = $appSettingData['data']['password'];

    $appSettingData = Array(
        "store_url" => $appSettingData['data']['settings']['store_url'],
        "email" => $appSettingData['data']['settings']['email'],
        "password" => $appSettingData['data']['settings']['password'],
    );

    //$order = (array)$order;
    // $webhookEvent = $request->header('webhook-event');
    //return view('callback');
    // Log::debug("webhookEvent [Order Create]=> " . $webhookEvent);
    Log::debug("request [Order Create]=> " . json_encode($order));
    Log::debug("request [appSettingData]=> " . json_encode($appSettingData));

    try {
        //code...
        $icarry_request = ICarry::orderDataToICarryRequest($order);
        // Log::debug("icarry_request => " .json_encode($icarry_request));

        //Get Token
        $ICarryApi = new ICarryApi();
        $ICarryTokenResponse = $ICarryApi->getToken($appSettingData);
        // Log::debug("ICarryTokenResponse => " .json_encode($ICarryTokenResponse));

        $token = "";
        if ($ICarryTokenResponse['type'] == 'success') {
            $token = $ICarryTokenResponse['message'];
        }

        $createOrderResponse = $ICarryApi->createOrder($appSettingData['store_url'], $token, $icarry_request);

        Log::debug("createOrderResponse => " .json_encode($createOrderResponse));


        if ($createOrderResponse['type'] == 'success') {
            //update tracking number
            // $orderDetails = updateTrackingNumber($order, $createOrderResponse['message']['TrackingNumber'], $user_info);
            return response(['message' => ""], 200);
        }

        return response(['message' => ""], 500);
    } catch (\Exception $e) {
        Log::error('An error occurred: ' . $e->getMessage());
        return response(['message' => ""], 500);
    }
}

function handleUninstallEvent($merchantId) {
    //delete merchant information
    $user_info = Credential::where('merchant_id', $merchantId)->first();
    if ($user_info) {
        // Delete the record
        $user_info->delete();
    }
    return response(['message' => ""], 200);
}
/*
function PrepareUserSession($tokenDataResponse) {
    //[API] Get merchant information
    $accountProfileResponse = Http::withHeaders([
        'Authorization' => "Bearer {$tokenDataResponse['authorization']}",
        'Accept' => 'application/json',
        //'X-Manager-Token' => $tokenDataResponse['access_token'],
        //'X-Salla-Security-Strategy' => env('WEBHOOK_SECRET'),
        'Accept-Language' => 'en'
        // Add any other headers you need
    ])->get(env('API_BASE_URL') . '/oauth2/user/info');
    // Log::debug("accountProfileResponse => " . json_encode($accountProfileResponse->json()));
    if ($accountProfileResponse->successful()) {
        $accountProfileData = $accountProfileResponse->json(); // Convert the response to a JSON array

        //Save information in the DB if not exist
        $user_info = Credential::where('merchant_store_id', $accountProfileResponse['user']['store']['id'])->first();

        // Log::debug("user_info => " . json_encode($user_info) );
        if (empty($user_info)) { //create new row
            $user_info = new Credential;
        }

        $user_info->merchant_id = $accountProfileResponse['merchant']['id'];
        $user_info->merchant_name = $accountProfileResponse['merchant']['name'];
        $user_info->merchant_email = $accountProfileResponse['merchant']['email'];
        $user_info->merchant_store_id = $accountProfileResponse['merchant']['store']['id'];
        $user_info->merchant_store_url = $accountProfileResponse['merchant']['store']['url'];

        // $user_info->iCARRYStoreURL = $data['access_token'];
        // $user_info->iCARRYEmail = $data['access_token'];
        // $user_info->iCARRYPassword = $data['access_token'];
        // $user_info->iCARRYEnableRates = $data['access_token'];

        $user_info->token_type = $tokenDataResponse['token_type'];
        $user_info->access_token = $tokenDataResponse['access_token'];
        $user_info->refresh_token = $tokenDataResponse['refresh_token'];
        $user_info->expires_in = $tokenDataResponse['expires_in'];
        $user_info->authorization = $tokenDataResponse['authorization'];

        $user_info->save();


        //Subscribe to order.create webhook
        $webhookResponse = Http::withHeaders([
            'Authorization' => "Bearer {$tokenDataResponse['authorization']}",
            'Accept' => 'application/json',
            'X-Manager-Token' => $tokenDataResponse['access_token'],
            'Accept-Language' => 'en'
            // Add any other headers you need
        ])->post(env('API_BASE_URL') . '/managers/webhooks', [
            "event" => "order.create",
            "target_url" => env('APP_URL') . "/webhook/order/create",
            "original_id" => $user_info->merchant_store_id,//storeId
            "subscriber" => "iCARRY",
            "conditions" => [
                "status"=> "new"
            ]
        ]);
        // Log::debug("accountProfileResponse => " . json_encode($accountProfileResponse->json()));
        if (!$webhookResponse->successful()) {
            Log::debug("Webhook subscription Failed [order.create] => " . json_encode($webhookResponse->json()));
        }



        // $result['message'] = "created";
        // return response()->json($result);
    }



    // return view('setting', compact("user_info"));

    $cookieValue = [
        'merchant_store_id' => $accountProfileResponse['user']['store']['id']
    ];

    // $response = response()->view('home', ['data' => $user_info])->withCookie(Cookie::make('icarry_zid', json_encode($cookieValue), 525600 )); // expires in 365 day
    $response = redirect()->away('/')->withCookie(Cookie::make('icarry_zid', json_encode($cookieValue), 525600 )); // expires in 365 days
    return $response;
    // return view('callback', ["name" => "james"]);
}

function getStoreIdFromCookie() {
    $icarry_zid_cookie = Cookie::get('icarry_zid');

    // Log::debug("icarry_zid_cookie => {$icarry_zid_cookie}");
    if ($icarry_zid_cookie) {
        // Cookie exists, do something with the value
        $icarry_zid = json_decode($icarry_zid_cookie);

        $merchant_store_id = $icarry_zid->merchant_store_id;
        $user_info = Credential::where('merchant_store_id', $merchant_store_id)->first();

        // Log::debug("merchant_store_id => " . $merchant_store_id );
        // Log::debug("user_info => " . json_encode($user_info) );
        if (!empty($user_info)) {
            return $merchant_store_id;
        }
    }
    return null;
}

function updateTrackingNumber($order, $tracking_number, $user_info) {

    try {
        $orderDetailsResponse = Http::withHeaders([
            'Authorization' => "Bearer {$user_info->authorization}",
            'Accept' => 'application/json',
            'X-Manager-Token' => $user_info->access_token,
            'Accept-Language' => 'en'
            // Add any other headers you need
        ])->post(env('API_BASE_URL') . '/managers/store/orders/'. $order['id'] .'/change-order-status', [
            'order_status' => $order['order_status']['code'],
            'tracking_number' => $tracking_number,
            'tracking_url' => $user_info->iCARRYStoreURL . "Order/TraceShipment?trackingNumber=" . $tracking_number
        ]);
        // Log::debug("accountProfileResponse => " . json_encode($accountProfileResponse->json()));
        Log::debug("orderDetailsResponse [updateTrackingNumber] => " . json_encode($orderDetailsResponse));
        if ($orderDetailsResponse->successful()) {
            $orderDetails = $orderDetailsResponse->json(); // Convert the response to a JSON array
            Log::debug("orderDetails => " . json_encode($orderDetails));
            return $orderDetails;
        }
    } catch (\Exception $e) {
        Log::error('An error occurred: [updateTrackingNumber]' . $e->getMessage());
    }

}
*/
/**
 * End Methods
 */






/**
 * Start Auth
 */

Route::get('/api/redirect', function () {
    $queries = http_build_query([
        'client_id' => env('CLIENT_ID'),
        'redirect_uri' => env('APP_URL') . '/api/callback',
        'response_type' => 'code',
        'scope' => 'read write',
        'state' => 'random_value'
    ]);
    return redirect(env('AUTH_URL') . '/oauth2/auth?' . $queries);
});


Route::get('/api/callback', function (Request $request) {

    $user_info = [];

    $tokenDataResponse = Http::post(env('AUTH_URL') . '/oauth2/token', [
        'grant_type' => 'authorization_code',
        'client_id' => env('CLIENT_ID'),
        'client_secret' => env('CLIENT_SECRET'),
        'redirect_uri' => env('APP_URL') . '/api/callback',
        'code' => $request->code // grant code
    ]);

    // Log::debug("tokenData => " . json_encode($tokenDataResponse->json()));

    if (!$tokenDataResponse->successful()) {
        $tokenData = $tokenDataResponse->json();
        // if (!isset($tokenData['access_token'])) {
        // }
        // return view('Home', compact("user_info"));




        return redirect('/');
    }

    // if (!isset($tokenData['access_token'])) {
    //     Log::error($tokenData);
    // }

    return PrepareUserSession($tokenDataResponse);
});

Route::get('/api/refresh-token', function (Request $request) {

    $user_info = null;
    $icarry_zid_cookie = Cookie::get('icarry_zid');

    // Log::debug("icarry_zid_cookie => {$icarry_zid_cookie}");
    if ($icarry_zid_cookie) {
        // Cookie exists, do something with the value
        $icarry_zid = json_decode($icarry_zid_cookie);

        $merchant_store_id = $icarry_zid->merchant_store_id;
        $user_info = Credential::where('merchant_store_id', $merchant_store_id)->first();

        // Log::debug("merchant_store_id => " . $merchant_store_id );
        // Log::debug("user_info => " . json_encode($user_info) );
        // if (empty($user_info)) {
        //     $response = response()->view('home',  ['data' => $user_info])->withCookie(Cookie::forget('icarry_zid'));
        //     return $response;
        // } else {
        //     $user_info['merchant_store_id'] = $merchant_store_id;
        // }
    }
    if (!$user_info) {
        return redirect('/');
    }

    $tokenDataResponse = Http::post(env('AUTH_URL') . '/oauth2/token', [
        'grant_type' => 'refresh_token',
        'refresh_token' => $user_info->refresh_token,
        'client_id' => env('CLIENT_ID'),
        'client_secret' => env('CLIENT_SECRET'),
        'redirect_uri' => env('APP_URL') . '/api/callback',
        //'scope' => 'offline_access'
    ]);

    // Log::debug("tokenData => " . json_encode($tokenDataResponse->json()));

    if (!$tokenDataResponse->successful()) {
        // $tokenData = $tokenDataResponse->json();
        // if (!isset($tokenData['access_token'])) {
        // }
        // return view('Home', compact("user_info"));
        return redirect('/');
    }
    // Log::debug("refresh token => " . json_encode($tokenDataResponse->json()));
    return PrepareUserSession($tokenDataResponse);
});

Route::get('/oauth/callback', function () {
    return view('callback');
});

/**
 * End Auth
 */


/**
 * Start APIs
*/

Route::post('/api/webhook', function (Request $request) {
    try {

        // Start Webhook Validation
        $secret = env('WEBHOOK_SECRET') ?: getenv('WEBHOOK_SECRET');
        if (!$secret) {
            Log::error('WEBHOOK_SECRET is not set');
            return response(['message' => 'Server misconfigured'], 500);
        }

        $requestHMAC = $request->header('X-Salla-Signature');
        if (!$requestHMAC) {
            Log::warning('Missing X-Salla-Signature header', [
                'headers' => $request->headers->all(),
            ]);
            return response(['message' => 'Missing signature'], 401);
        }

        $rawBody = $request->getContent();
        $computedHMAC = hash_hmac('sha256', $rawBody, $secret);

        if (!hash_equals($computedHMAC, $requestHMAC)) {
            Log::warning('Invalid webhook signature', [
                'computed' => $computedHMAC,
                'received' => $requestHMAC,
            ]);
            return response(['message' => 'Invalid signature'], 401);
        }

        Log::debug('Webhook signature is valid');
        // End Webhook Validation

        $requestBody = json_decode($rawBody);
        $merchantId = $requestBody->merchant ?? null;
        $requestData = $requestBody->data ?? null;


        // do stuff
        switch ($requestBody->event) {
            // case 'app.installed':
            //     break;
            case 'app.store.authorize':
                saveUserSession($merchantId, $requestData);
                break;
            case 'app.uninstalled':
                return handleUninstallEvent($merchantId);
            case 'order.created':
                $isTokenExpired = validateToken($merchantId);
                if ($isTokenExpired) {
                    Log::debug("Token expired for merchant #{$merchantId}");
                    response(['message' => ""], 500);
                }
                return handleOrderCreateEvent($merchantId, $requestData);
        }
        return response(['message' => ""], 200);
    } catch (\Exception $e) {
        Log::error('An error occurred: ' . $e->getMessage());
        return response(['message' => ""], 500);
    }
});


Route::post('/api/validate-app-setting', function (Request $request) {
    $requestBody = $request->input();
    //return view('callback');
    Log::debug("request => [validate settings]" . json_encode($requestBody));
    // $requestBody = json_decode($requestBody);
    // $appSettingData = Array(
    //     "store_url" => $requestBody->data->store_url,
    //     "email" => $requestBody->data->email,
    //     "password" => $requestBody->data->password
    // );
    //Get Token
    $ICarryApi = new ICarryApi();
    $ICarryTokenResponse = $ICarryApi->getToken($requestBody['data']);
    $token = "";
    if ($ICarryTokenResponse['type'] == 'success') {
        $token = $ICarryTokenResponse['message'];
        return response(['message' => ""], 200);
        // $user_info = Credential::where('merchant_id', $requestBody['merchant'])->first();
        // if ($user_info) {
        //     $user_info->iCARRYStoreURL = $requestBody['data']['store_url'];
        //     $user_info->iCARRYEmail = $requestBody['data']['email'];
        //     $user_info->iCARRYPassword = $requestBody['data']['password'];
        //     $user_info->save();
        //     return response(['message' => ""], 200);
        // }

    }

    //$icarryUserData = $ICarryTokenResponse['message'];
    return response(['message' => ""], 500);
});
/**
 * End APIs
 */

