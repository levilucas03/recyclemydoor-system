<?php

namespace App\Services\Ebay;

use App\Models\EbayAccount;
use Illuminate\Support\Facades\Http;

class EbayAuthService
{
    public function getAccessToken(EbayAccount $account): string
    {
        if (
            $account->access_token &&
            $account->access_token_expires_at &&
            $account->access_token_expires_at->isFuture()
        ) {
            return $account->access_token;
        }

        $response = Http::asForm()
            ->withBasicAuth(
                config('services.ebay.client_id'),
                config('services.ebay.client_secret')
            )
            ->post('https://api.ebay.com/identity/v1/oauth2/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $account->refresh_token,
                'scope' => implode(' ', [
                    'https://api.ebay.com/oauth/api_scope',
                    'https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly',
                ]),
            ]);

        if ($response->failed()) {
            throw new \Exception('eBay token refresh failed: ' . $response->body());
        }

        $data = $response->json();

        $account->update([
            'access_token' => $data['access_token'],
            'access_token_expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        return $data['access_token'];
    }
}