<?php

namespace App\Http\Controllers;

use App\Constants\LoanStatus;
use App\Constants\PaymentStatus;
use App\Http\Resources\PaymentResource;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
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

        $payments = Payment::orderBy($orderBy, 'desc')->paginate($itemsPerPage);

        return response()->json([
            'payments' => PaymentResource::collection($payments),
            'total' => $payments->total(),
            'currentPage' => $payments->currentPage(),
            'message' => 'Fetched payments successfully']
        );
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
            'loan_id' => 'unique:payments',
            'due_date' => 'required|date',
            'repaid_date' => 'required|date',
            'paid_amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $statusCode = Response::HTTP_CREATED;

        try {
            DB::beginTransaction();

            if (!empty($params['loan_id'])) {
                // Find loan by id.
                /** @var Loan $loan */
                $loan = Loan::find($params['loan_id']);

                if (LoanStatus::COMPLETED === $loan->status) {
                    throw new \Exception(
                        'Cannot create a new payment for a completed loan'
                    );
                }

                if ($params['paid_amount'] > $loan->amount) {
                    throw new \Exception(
                        'Invalid paid_amount parameter: paid_mount cannot be greater than remaining amount');
                }
                $params['remaining_amount'] = $loan->amount - $params['paid_amount'];
                $params['state'] = (0 === $params['remaining_amount']) ? PaymentStatus::PAID : PaymentStatus::PARTIALLY_PAID;
            } else {
                $params['state'] = PaymentStatus::NEW;

                if (empty($params['remaining_amount'])) {
                    throw new \Exception('Remaining amount cannot be empty');
                }
            }
            /** @var Payment $payment */
            $payment = Payment::create($params);
            DB::commit();

            $response = [
                'payment' => new PaymentResource($payment),
                'message' => 'Created a new payment successfully'
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
     * @param Payment $payment
     * @return JsonResponse
     */
    public function show(Payment $payment): JsonResponse
    {
        $response = [
            'payment' => new PaymentResource($payment),
            'message' => 'Fetched a payment successfully'
        ];

        return response()->json($response);

    }

    /**
     * @param Request $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        $statusCode = Response::HTTP_OK;
        $params = $request->all();

        try {
            if ($params['paid_amount'] > $payment['remaining_amount']) {
                throw new \Exception('Invalid paid_amount parameter:
                paid_mount cannot be greater than remaining amount');
            }

            $newRemainingAmount = $payment['remaining_amount'] - $params['paid_amount'];

            // Calculate and update state, paid_amount, remaining amount
            $params['state'] = (0 === $newRemainingAmount) ? PaymentStatus::PAID : PaymentStatus::PARTIALLY_PAID;
            $params['remaining_amount'] = $newRemainingAmount;
            $params['paid_amount'] += $payment['paid_amount'];

            DB::beginTransaction();
            $payment->update($params);
            DB::commit();

            $response = [
                'payment' => new PaymentResource($payment),
                'message' => 'Payment updated successfully'
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
     * @param Payment $payment
     * @return JsonResponse
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted']);
    }
}
