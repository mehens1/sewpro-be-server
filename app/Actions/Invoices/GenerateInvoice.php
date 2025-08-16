<?php

namespace App\Actions\Invoices;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;

class GenerateInvoice
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'customer_id'    => 'required|exists:customers,id',
            'invoice_date'   => 'required|date',
            'due_date'       => 'required|date',
            'items'          => 'required|array',
            'items.*.name'   => 'required|string',
            'items.*.price'  => 'required|numeric|min:0',
            'items.*.qty'    => 'required|integer|min:1',
            'tax'            => 'nullable|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'shipping_fee'   => 'nullable|numeric|min:0',
        ];
    }

    public function handle(array $params)
    {
        return DB::transaction(function () use ($params) {
            $subtotal = collect($params['items'])->sum(fn($item) => $item['price'] * $item['qty']);

            $invoice = Invoice::create([
                'customer_id'   => $params['customer_id'],
                'invoice_date'  => $params['invoice_date'],
                'due_date'      => $params['due_date'],
                'subtotal'      => $subtotal,
                'tax'           => $params['tax'] ?? 0,
                'discount'      => $params['discount'] ?? 0,
                'shipping_fee'  => $params['shipping_fee'] ?? 0,
                'total'         => $subtotal + ($params['tax'] ?? 0) + ($params['shipping_fee'] ?? 0) - ($params['discount'] ?? 0),
                'amount_received' => 0,
                'status'        => 'pending',
            ]);

            foreach ($params['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'   => $invoice->id,
                    'product_name' => $item['name'],
                    'price'        => $item['price'],
                    'quantity'     => $item['qty'],
                    'amount'       => $item['price'] * $item['qty'],
                ]);
            }

            return $invoice->load('items', 'customer');
        });
    }

    public function asController(ActionRequest $request)
    {
        $invoice = $this->handle($request->all());
        return $this->success('Invoice created successfully', $invoice);
    }
}
