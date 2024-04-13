<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class BoathouseApi
{
    private $boathouseApi;
    private $boathousePortalID;
    private $boathouseSecret;
    private $client;

    public function __construct()
    {
        $this->boathouseApi = env('BOATHOUSE_API');
        $this->boathousePortalID = env('BOATHOUSE_PORTAL_ID');
        $this->boathouseSecret = env('BOATHOUSE_SECRET');


        $this->client = new Client();
    }

    public function getBoathouseResponse(string $email = null, string $customerID = null, string $returnUrl = null)
    {
        try {
            $response = $this->client->post($this->boathouseApi, [
                'json' => [
                    'portalId' => $this->boathousePortalID,
                    'secret' => $this->boathouseSecret,
                    'email' => $email,
                    'paddleCustomerId' => $customerID,
                    'returnUrl' => $returnUrl
                ]
            ]);


            $body = $response->getBody()->getContents();
            $ret = json_decode($body, true);

            Log::channel('stderr')->debug($ret);

            return $ret;
        } catch (RequestException $e) {
            Log::debug($e->getMessage());

            return null;
        }
    }
}

class BoathouseResponse
{
    public $paddleCustomerID;
    public $billingPortalUrl;
    public $pricingTableHtml;
    public $activeSubscriptions;
}
