<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateInvoiceStatus
{
    use AsAction;

    /**
     * Handle status update.
     */
    public function handle(int $id, string $status)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->update([
            'status' => $status
        ]);

        return $invoice;
    }

    public function rules()
    {
        return [
            'status' => [
                'required',
                Rule::in(['draft', 'pending', 'paid', 'cancelled']), // add more as needed
            ]
        ];
    }

    public function asController(Request $request, $id)
    {
        $validated = $request->validate($this->rules());

        $invoice = $this->handle($id, $validated['status']);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice status updated successfully.',
            'data' => $invoice,
        ]);
    }
}
