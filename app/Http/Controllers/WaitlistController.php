<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Waitlist;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class WaitlistController extends Controller
{
    use ApiResponse;

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
        }

        catch (\Exception $e) {
            \Log::debug("message" . $e->getMessage());
            return $this->errorResponse('Failed to fetch waitlist users: ', 500, [
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:waitlists,email',
            'phone_number' => 'string|max:20|unique:waitlists,phone_number',
        ]);

        try {
            $waitlist_user = DB::transaction(function () use ($validated) {

                $name = $validated['full_name'];
                $email = $validated['email'];
                $phone = $validated['phone_number'];

                $tagId = env('SYSTEME_IO_TAG_ID');
                $apiKey = env('SYSTEME_IO_API_KEY');
                $apiUrl = rtrim(env('SYSTEME_IO_URL'), '/');

                $nameParts = explode(' ', $name);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '';

                $waitlist_user = Waitlist::create($validated);

                // Step 1: Create the contact
                $contactPayload = [
                    "email" => $email,
                    "locale" => "en",
                    "email_campaign" => "SEWPRO WAITLIST",
                    "fields" => [
                        [
                            "fieldName" => "First name",
                            "slug" => "first_name",
                            "value" => $firstName,
                        ],
                        [
                            "fieldName" => "Last name",
                            "slug" => "surname",
                            "value" => $lastName,
                        ],
                        [
                            "fieldName" => "Phone number",
                            "slug" => "phone_number",
                            "value" => $phone,
                        ],
                        [
                            "fieldName" => "Country",
                            "slug" => "country",
                            "value" => "NG"
                        ],
                    ],
                ];

                $contactResponse = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-API-Key' => $apiKey,
                ])->post("{$apiUrl}/contacts", $contactPayload);

                if (!$contactResponse->successful()) {
                    $responseData = $contactResponse->json();
                    $errorMessage = $responseData['detail'] ?? 'Systeme.io contact creation failed.';
                    throw new \Exception($errorMessage);
                }

                // Step 2: Get contact ID
                $contactData = $contactResponse->json();
                $contactId = $contactData['id'] ?? null;

                if (!$contactId) {
                    throw new \Exception('Missing contact ID from Systeme.io response.');
                }

                // Step 3: Assign tag to contact
                $tagAssignmentPayload = [
                    'tagId' => (int) $tagId,
                ];

                $tagResponse = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-API-Key' => $apiKey,
                ])->post("{$apiUrl}/contacts/{$contactId}/tags", $tagAssignmentPayload);

                if (!$tagResponse->successful()) {
                    $tagError = $tagResponse->json()['detail'] ?? 'Failed to assign tag to contact.';
                    throw new \Exception($tagError);
                }

                return $waitlist_user;
            });

            return $this->successResponse([
                'message' => 'User added to waitlist and tagged successfully.',
                'data'    => $waitlist_user
            ], 201);

        } catch (\Exception $e) {
            \Log::error("Waitlist registration failed", ["exception" => $e]);
            return $this->errorResponse('Failed to add user to waitlist: '.$e->getMessage(), 500, [
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
