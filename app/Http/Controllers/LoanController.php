<?php

namespace App\Http\Controllers;

use App\Constants\LoanStatus;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\LoanResource;
use App\Http\Resources\ProductResource;
use App\Models\Loan;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    /**
     * @param Loan $loan
     * @throws \Exception
     */
    private function approveLoan(Loan &$loan): void
    {
        if (!empty($loan->customer)) {
            $customer = new CustomerResource($loan->customer);
        } else {
            throw new \Exception('Unable to approve loan as it does not belong to any customers.');
        }

        if (!empty($customer)) {
            $product = new ProductResource(Product::find($loan->product_id));
        } else {
            throw new \Exception('Unable to approve loan as the product applied for this loan is not available');
        }

        if ((!empty($customer->credit_point) && empty($product->minimum_credit_point_requirement)) ||
            (!empty($customer->credit_point) && !empty($product->minimum_credit_point_requirement)
                && $customer->credit_point >= $product->minimum_credit_point_requirement)

        ) {
            $loan->status = LoanStatus::APPROVED;
            $loan->approved_by = auth()->user()->getAuthIdentifierName();
        }
    }

    /**
     * @param $loan
     * @throws \Exception
     */
    private function completeLoan(&$loan): void
    {
        $payment = $loan->payment->toArray();

        if (empty($payment)) {
            throw new \Exception('Unable to complete loan as it is not yet paid.');
        }

        $remainingAmount = $payment['remaining_amount'];

        if ($remainingAmount === 0) {
            $loan->status = LoanStatus::COMPLETED;
        }
    }

    /**
     * @param Loan $loan
     * @param $status
     * @throws \Exception
     */
    private function handleLoanStatus(Loan &$loan, $status): void
    {
        switch ($status) {
            case LoanStatus::APPROVED:
                $this->approveLoan($loan);
                break;
            case LoanStatus::COMPLETED:
                $this->completeLoan($loan);
                break;
            default:
                break;
        }
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

        $loans = Loan::orderBy($orderBy, $orderType)->paginate($itemsPerPage);

        return response()->json([
            'loans' => LoanResource::collection($loans),
            'total' => $loans->total(),
            'currentPage' => $loans->currentPage(),
            'message' => 'Fetched loans successfully'
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
            'customer_id' => 'required',
            'interest_rate' => 'regex:/^-?[0-9]+(?:\.[0-9]{1,2})?$/',
            'duration' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $statusCode = Response::HTTP_CREATED;

        try {
            DB::beginTransaction();
            $params['status'] = LoanStatus::NEW;
            /** @var Loan $loan */
            $loan = Loan::create($params);
            DB::commit();

            $response = [
                'loan' => new LoanResource($loan),
                'message' => 'Created a new loan successfully'
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
     * @param Loan $loan
     * @return JsonResponse
     */
    public function show(Loan $loan): JsonResponse
    {
        $response = [
            'loan' => new LoanResource($loan),
            'message' => 'Fetched a loan successfully'
        ];

        return response()->json($response);

    }

    /**
     * @param Request $request
     * @param Loan $loan
     * @return JsonResponse
     */
    public function update(Request $request, Loan $loan): JsonResponse
    {
        $request->validate([
            'interest_rate' => 'regex:/^-?[0-9]+(?:\.[0-9]{1,2})?$/'
        ]);
        $statusCode = Response::HTTP_OK;
        $params = $request->all();

        try {
            DB::beginTransaction();
            $this->handleLoanStatus($loan, $params['status'] ?? '');
            if (!empty($loan->status)) {
                unset($params['status']);
            }
            $loan->update($params);
            DB::commit();

            $response = [
                'loan' => new LoanResource($loan),
                'message' => 'Loan updated successfully'
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
     * @param Loan $loan
     * @return JsonResponse
     */
    public function destroy(Loan $loan): JsonResponse
    {
        $loan->delete();

        return response()->json(['message' => 'Loan deleted']);
    }
}
