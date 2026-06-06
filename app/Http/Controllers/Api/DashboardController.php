<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Application\Dashboard\UseCases\DashboardOwnerUseCase;
use App\Http\Responses\ApiResponse;

class DashboardController extends Controller
{
    public function owner(Request $request, DashboardOwnerUseCase $useCase)
    {
        $filters = $request->only(['start_date', 'end_date']);

        $data = $useCase->execute($filters);

        return ApiResponse::success(
            data: $data,
            message: 'Dashboard data retrieved successfully'
        );
    }
}