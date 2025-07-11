<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Waitlist;
use Illuminate\Support\Facades\Http;


class WaitlistController extends Controller
{
    use ApiResponse;

    private $listName = 'Sewpro wait-list';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $waitlist_users = Waitlist::all();

            return $this->successResponse([
                'message' => 'Waitlist users retrieved successfully',
                'data'    => $waitlist_users
            ], 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to fetch waitlist users', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:waitlists,email',
            'phone_number' => 'string|max:20|unique:waitlists,phone_number',
        ]);

        try {

            $getresponseApiKey = env('GETRESPONSE_API_KEY');

            $name = $validated['full_name'];
            $email = $validated['email'];

            $waitlist_user = Waitlist::create($validated);

            $campaigns = Http::withHeaders([
                'X-Auth-Token' => 'api-key ' . $getresponseApiKey
            ])->get('https://api.getresponse.com/v3/campaigns');

            $campaign = collect($campaigns->json())->firstWhere('name', $this->listName);

            $campaignId = $campaign['campaignId'] ?? null;

            $response = Http::withHeaders([
                'X-Auth-Token' => 'api-key ' . $getresponseApiKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.getresponse.com/v3/contacts', [
                'name' => $name,
                'email' => $email,
                "dayOfCycle" => "0",
                'campaign' => [
                    'campaignId' => $campaignId ?? null
                ]
            ]);

            return $this->successResponse([
                'message' => 'User added to waitlist successfully.',
                'data'    => $waitlist_user
            ], 201);

        } catch (\Exception $e) {
            \Log::debug("message" . $e->getMessage());
            return $this->errorResponse('Failed to add user to waitlist.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
