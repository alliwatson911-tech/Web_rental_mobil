<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $result = $this->paymentService->handleWebhook($request);

        if (! empty($result['status']) && $result['status'] === 'unauthorized') {
            return response()->json($result, 401);
        }

        return response()->json($result, 200);
    }
}

