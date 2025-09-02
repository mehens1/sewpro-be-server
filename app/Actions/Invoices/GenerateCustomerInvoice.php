<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Requests\GenerateInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;

class GenerateCustomerInvoice
{
    use AsAction, ApiResponse;

    public function handle(array $params)
    {
        try {
            $invoice = DB::transaction(function () use ($params) {
                $user = auth()->user();

                $subtotal = collect($params['items'])
                    ->sum(fn($item) => $item['unit_price'] * $item['quantity']);

                $invoice = Invoice::create([
                    'user_id'        => $user->id,
                    'customer_id'    => $params['customer_id'],
                    'due_date'       => $params['due_date'],
                    'subtotal'       => $subtotal,
                    'issue_date' => $params['issue_date'],
                    'tax'            => $params['tax'] ?? 0,
                    'discount'       => $params['discount'] ?? 0,
                    'shipping_fee'   => $params['shipping_fee'] ?? 0,
                    'total'          => $subtotal + ($params['tax'] ?? 0) + ($params['shipping_fee'] ?? 0) - ($params['discount'] ?? 0),
                    'status'         => InvoiceStatus::Pending
                ]);

                $invoice->invoice_number = 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT);
                $invoice->save();

                foreach ($params['items'] as $item) {
                    InvoiceItem::create([
                        'invoice_id'   => $invoice->id,
                        'name'          => $item['name'],
                        'unit_price'        => $item['unit_price'],
                        'quantity'     => $item['quantity'],
                        'total'       => $item['unit_price'] * $item['quantity'],
                    ]);
                }

                return $invoice->load('items', 'customer');
            });

            return $this->successResponse('Invoice created successfully', $invoice);
        } catch (\Throwable $th) {
            Log::error("Error generating customer invoice: ", [$th]);

            return $this->errorResponse(
                'Failed to create invoice.',
                500,
                ['error' => $th->getMessage()]
            );
        }
    }

    public function asController(GenerateInvoiceRequest $request)
    {
        return $this->handle($request->validated());
    }
}
