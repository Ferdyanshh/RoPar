<?php

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ExchangeRateController extends Controller
{
    public function show(ExchangeRateService $exchangeRates): JsonResponse
    {
        try {
            return response()->json(['data' => $exchangeRates->idrToUsd()]);
        } catch (ConnectionException|RuntimeException $exception) {
            Log::warning('Unable to retrieve the IDR to USD exchange rate.', [
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Kurs mata uang sedang tidak tersedia. Silakan coba lagi nanti.',
            ], 503);
        }
    }
}
