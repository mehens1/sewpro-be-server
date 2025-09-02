<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateInvoiceStatus
{
    use AsAction, ApiResponse;

    public function rules()
    {
        return [
            'invoice_id' => 'required|integer|exists:invoices,id',
            'status' => [
                'required',
                Rule::in(['pending', 'paid', 'cancelled']),
            ]
        ];
    }

    public function handle(array $data)
    {
        $id = $data['invoice_id'];
        $status = $data['status'];

        try {
            $invoice = DB::transaction(function () use ($id, $status) {
                $invoice = Invoice::findOrFail($id);

                if (in_array($invoice->status, ['paid', 'cancelled'])) {
                    throw new \Exception("This invoice has already been {$invoice->status} and cannot be updated.");
                }

                $updateData = ['status' => $status];

                if ($status === 'paid') {
                    $lastReceiptNumber = Invoice::whereNotNull('receipt_number')
                        ->orderByDesc('id')
                        ->value('receipt_number');

                    $nextNumber = $lastReceiptNumber
                        ? (intval(str_replace('RCPT-', '', $lastReceiptNumber)) + 1)
                        : 1;

                    $updateData['receipt_number'] = 'RCPT-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                    $updateData['receipt_at'] = now();
                }

                if ($status === 'cancelled') {
                    $updateData['cancelled_at'] = now();
                }

                $invoice->update($updateData);

                return $invoice;
            });

            return $this->successResponse($invoice, 'Invoice status updated successfully.');
        } catch (\Throwable $th) {
            return $this->errorResponse("Error updating invoice", 400, $th->getMessage());
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
