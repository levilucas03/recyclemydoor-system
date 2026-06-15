<?php

namespace App\Http\Controllers;
use App\Models\EbayAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class EbayAuthController extends Controller
{
    public function redirect()
    {
        $params = http_build_query([
            'client_id' => config('services.ebay.client_id'),
            'redirect_uri' => config('services.ebay.runame'),
            'response_type' => 'code',
            'scope' => implode(' ', [
                'https://api.ebay.com/oauth/api_scope',
                'https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly',
            ]),
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect('https://auth.ebay.com/oauth2/authorize?' . $params);
    }

    public function callback(Request $request)
    {
        //  dd($request->all());

        if (! $request->has('code')) {
            return redirect()->route('dashboard')
                ->with('error', 'eBay did not return an auth code.');
        }

        $response = Http::asForm()
            ->withBasicAuth(
                config('services.ebay.client_id'),
                config('services.ebay.client_secret')
            )
            ->post('https://api.ebay.com/identity/v1/oauth2/token', [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => config('services.ebay.runame'),
            ]);

        if ($response->failed()) {
            dd($response->status(), $response->json(), $response->body());
        }

        $data = $response->json();

        EbayAccount::updateOrCreate(
            ['name' => 'Main eBay'],
            [
                'environment' => 'production',
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'access_token_expires_at' => now()->addSeconds($data['expires_in']),
                'is_active' => true,
            ]
        );

        return redirect()->route('dashboard')
            ->with('success', 'eBay connected successfully.');
    }
}