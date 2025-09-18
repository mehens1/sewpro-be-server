<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowInvoice
{
    use AsAction;

    /**
     * Handle the incoming request.
     */
    public function handle(int $id)
    {
        return Invoice::with('items')->findOrFail($id);
    }

    public function asController(Request $request, $id)
    {
        $invoice = $this->handle($id);

        return response()->json([
            'status' => 'success',
            'data' => $invoice,
        ]);
    }
}
