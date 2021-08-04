<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\LoanResource;
use App\Http\Resources\PaymentResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * @param $id
     * @return JsonResponse
     */
    public function getCustomerLoans($id): JsonResponse
    {
        /** @var Customer $customer */
        $customer = Customer::find($id);

        return response()->json([
            'loans' => LoanResource::collection($customer->loans)
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function getCustomerPayments($id): JsonResponse
    {
        /** @var Customer $customer */
        $customer = Customer::find($id);

        return response()->json([
            'payment' => PaymentResource::collection($customer->payments)
        ]);
    }

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

        $customers = Customer::orderBy($orderBy, $orderType)->paginate($itemsPerPage);

        return response()->json([
            'customers' => CustomerResource::collection($customers),
            'total' => $customers->total(),
            'currentPage' => $customers->currentPage(),
            'message' => 'Fetched customers successfully'
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
            'name' => 'required|max:50',
            'phone_number' => 'required|max:15',
            'address' => 'required',
            'date_of_birth' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $statusCode = Response::HTTP_CREATED;

        try {
            DB::beginTransaction();
            /** @var Customer $customer */
            $customer = Customer::create($params);
            $customer->save();
            DB::commit();

            $response = [
                'customer' => new CustomerResource($customer),
                'message' => 'Created a new customer successfully'
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
     * @param Customer $customer
     * @return JsonResponse
     */
    public function show(Customer $customer): JsonResponse
    {
        $response = [
            'customer' => new CustomerResource($customer),
            'message' => 'Fetched a customer successfully'
        ];

        return response()->json($response);

    }

    /**
     * @param Request $request
     * @param Customer $customer
     * @return JsonResponse
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $request->validate([
            'name' => 'max:50',
            'phone_number' => 'max:15',
            'date_of_birth' => 'date'
        ]);
        $statusCode = Response::HTTP_OK;

        try {
            DB::beginTransaction();
            $customer->update($request->all());
            DB::commit();

            $response = [
                'customer' => new CustomerResource($customer),
                'message' => 'Customer updated successfully'
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
     * @param Customer $customer
     * @return JsonResponse
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(['message' => 'Customer deleted']);
    }
}
