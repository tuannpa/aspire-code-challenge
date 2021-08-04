<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $params = $request->all();
        $itemsPerPage = $params['itemsPerPage'] ?? 10;
        $orderBy = $params['order'] ?? 'id';
        $orderType = $params['orderType'] ?? 'desc';

        $products = Product::orderBy($orderBy, $orderType)->paginate($itemsPerPage);

        return response()->json([
            'products' => ProductResource::collection($products),
            'total' => $products->total(),
            'currentPage' => $products->currentPage(),
            'message' => 'Fetched products successfully'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'name' => 'required',
            'type' => 'required',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $statusCode = Response::HTTP_CREATED;

        try {
            DB::beginTransaction();
            /** @var Product $product */
            $product = Product::create($params);
            $product->save();
            DB::commit();

            $response = [
                'product' => new ProductResource($product),
                'message' => 'Created a new product successfully'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'error' => $e->getMessage()
            ];
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        $response = [
            'product' => new ProductResource($product),
            'message' => 'Fetched a product successfully'
        ];

        return response()->json($response);

    }

    /**
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $statusCode = Response::HTTP_OK;

        try {
            DB::beginTransaction();
            $product->update($request->all());
            DB::commit();

            $response = [
                'product' => new ProductResource($product),
                'message' => 'Product updated successfully'
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'error' => $e->getMessage()
            ];
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }


        return response()->json($response, $statusCode);
    }

    /**
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
