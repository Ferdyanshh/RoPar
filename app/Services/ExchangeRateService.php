<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ExchangeRateService
{
    /**
     * @return array{idr_to_usd: float, updated_at: string}
     */
    public function idrToUsd(): array
    {
        return Cache::remember('exchange_rates.idr_to_usd', now()->addHour(), function (): array {
            $response = Http::acceptJson()
                ->timeout(5)
                ->get(config('services.exchange_rates.url'));

            if (! $response->successful()) {
                throw new RuntimeException('The exchange-rate provider returned an unsuccessful response.');
            }

            $rate = $response->json('rates.USD');
            $updatedAt = $response->json('time_last_update_utc');

            if (! is_numeric($rate) || (float) $rate <= 0 || ! is_string($updatedAt) || $updatedAt === '') {
                throw new RuntimeException('The exchange-rate provider returned invalid rate data.');
            }

            return [
                'idr_to_usd' => (float) $rate,
                'updated_at' => $updatedAt,
            ];
        });
    }
}
