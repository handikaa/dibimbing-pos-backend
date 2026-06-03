<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Sales\AddSaleItemRequest;
use App\Application\Sales\DTOs\AddSaleItemDTO;
use App\Application\Sales\UseCases\AddSaleItemToBillUseCase;
use App\Application\Sales\DTOs\ListSalesDTO;
use App\Application\Sales\UseCases\ListSalesUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\ListSalesRequest;
use App\Application\Sales\DTOs\ShowSaleDTO;
use App\Application\Sales\UseCases\ShowSaleUseCase;
use App\Http\Resources\Sales\SaleResource;
use App\Http\Responses\ApiResponse;
use App\Application\Sales\DTOs\CancelSaleDTO;
use App\Application\Sales\UseCases\CancelSaleUseCase;
use App\Http\Requests\Sales\UpdateSaleItemRequest;
use App\Application\Sales\DTOs\UpdateSaleItemDTO;
use App\Application\Sales\DTOs\DeleteSaleItemDTO;
use App\Application\Sales\UseCases\UpdateSaleItemUseCase;
use App\Http\Requests\Pos\OpenBillCheckoutCashRequest;
use App\Application\Pos\DTOs\OpenBillCheckoutCashDTO;
use App\Application\Pos\UseCases\OpenBillCheckoutCashUseCase;
use App\Application\Sales\UseCases\DeleteSaleItemUseCase;
use Illuminate\Http\JsonResponse;

class SalesController extends Controller
{
    public function index(ListSalesRequest $request, ListSalesUseCase $useCase): JsonResponse
    {
        $dto = ListSalesDTO::fromArray($request->validated());
        $sales = $useCase->execute($dto);

        return response()->json([
            'success' => true,
            'message' => 'Sales retrieved successfully',
            'data' => SaleResource::collection($sales),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ]
        ]);
    }

    public function show(
        int $sale,
        ShowSaleUseCase $useCase
    ): JsonResponse {

        $result = $useCase->execute(
            actor: request()->user(),
            dto: ShowSaleDTO::fromId($sale)
        );

        return ApiResponse::success(
            data: new SaleResource($result),
            message: 'Sale retrieved successfully'
        );
    }

    public function receipt(int $sale, ShowSaleUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(
            actor: request()->user(),
            dto: ShowSaleDTO::fromId($sale)
        );

        // Bisa pakai SaleResource sama dengan detail, atau bikin ReceiptResource khusus jika perlu format print
        return response()->json([
            'success' => true,
            'message' => 'Sale receipt retrieved successfully',
            'data' => new SaleResource($result)
        ]);
    }

    public function cancel(int $sale, CancelSaleUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(
            actor: request()->user(),
            dto: CancelSaleDTO::fromId($sale)
        );

        return response()->json([
            'success' => true,
            'message' => 'Sale cancelled successfully',
            'data' => $result
        ]);
    }

    public function addItem(
        int $sale,
        AddSaleItemRequest $request,
        AddSaleItemToBillUseCase $useCase
    ) {
        $updatedSale = $useCase->execute(
            actor: $request->user(),
            dto: AddSaleItemDTO::fromArray($sale, $request->validated())
        );

        return ApiResponse::success(
            data: new SaleResource($updatedSale),
            message: 'Item added to bill successfully',
            code: 201
        );
    }

    public function updateItem(
        int $sale,
        int $item,
        UpdateSaleItemRequest $request,
        UpdateSaleItemUseCase $useCase
    ) {
        $updatedSale = $useCase->execute(
            actor: $request->user(),
            dto: UpdateSaleItemDTO::fromArray($sale, $item, $request->validated())
        );

        return ApiResponse::success(
            data: new SaleResource($updatedSale),
            message: 'Bill item updated successfully'
        );
    }



    public function deleteItem(int $sale, int $item, Request $request, DeleteSaleItemUseCase $useCase)
    {
        $dto = DeleteSaleItemDTO::fromRequest(
            actor: $request->user(), // sekarang sudah typed User
            saleId: $sale,
            itemId: $item
        );

        $updatedSale = $useCase->execute($dto);

        return ApiResponse::success(
            data: new SaleResource($updatedSale),
            message: 'Item deleted successfully'
        );
    }

    public function openBillCheckoutCash(
        int $sale,
        OpenBillCheckoutCashRequest $request,
        OpenBillCheckoutCashUseCase $useCase
    ) {
        $dto = OpenBillCheckoutCashDTO::fromArray($sale, $request->validated());

        $sale = $useCase->execute(
            actor: $request->user(),
            dto: $dto
        );

        return ApiResponse::success(
            data: new SaleResource($sale),
            message: 'Cash checkout completed successfully',
            code: 201
        );
    }
}
