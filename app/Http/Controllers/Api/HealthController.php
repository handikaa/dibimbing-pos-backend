<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;

class HealthController extends Controller
{
    public function __invoke()
    {
        return ApiResponse::success([
            'service' => 'pos-backend',
            'status' => 'ok',
            'version' => 'v1',
        ], 'POS API is running');
    }
}
