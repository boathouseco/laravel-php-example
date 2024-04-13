<?php
use App\Services\BoathouseApi;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', function () {
    return view('login');
})->name('login');


Route::post('/login', function () {
    // Generate a random email address
    $randomEmail = 'playground-' . Str::uuid()->toString() . '@mailexample.com';

    // Call Boathouse API
    $boathouseApi = new BoathouseApi();
    $response = $boathouseApi->getBoathouseResponse(
        $randomEmail,
        null,
        url()->full()
    );

    // Set cookie in the response
    $cookie = cookie('PaddleCustomerID', $response["paddleCustomerId"], 60); // 60 minutes for cookie expiration

    return redirect('/account')->withCookie($cookie);
});

Route::get('/account', function (Request $request) {
    $paddleCustomerId = $request->cookie('PaddleCustomerID');

    // Call Boathouse API
    $boathouseApi = new BoathouseApi();
    $boathouse = $boathouseApi->getBoathouseResponse(
        null,
        $paddleCustomerId,
        url()->full()
    );

    return view('account', [
        'paddleCustomerId' => $paddleCustomerId,
        'boathouse' => $boathouse
    ]);
})->name('account');


Route::get('/processing', function (Request $request) {
    $priceIds = explode(',', $request->input('pids', ''));
    $paddleCustomerID = $request->cookie('PaddleCustomerID');

    $boathouseApi = new BoathouseApi();
    $boathouse = $boathouseApi->getBoathouseResponse(null, $paddleCustomerID);

    $checkoutCompleted = collect($priceIds)->every(function ($pid) use ($boathouse) {
        return collect($boathouse['activeSubscriptions'])->contains(strtolower($pid));
    });

    if ($checkoutCompleted) {
        return redirect()->route('account');
    } else {
        return view('processing');
    }
});

