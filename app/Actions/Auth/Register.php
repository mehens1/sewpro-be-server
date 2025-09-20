<?php

namespace App\Actions\Auth;

use App\Mail\VerifyEmailCodeMail;
use App\Traits\ApiResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Register
{
    use AsAction;
    use ApiResponse;

    public function rules()
    {
        return [
            'email' => 'bail|required|email|unique:users,email',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => 'required|string|min:8',
            'referral_code' => 'nullable|exists:users,referral_code',
        ];
    }

    public function handle($details)
    {

        DB::beginTransaction();

        try {
            $existingEmail = User::where('email', $details['email'])->first();
            if ($existingEmail) {
                return $this->errorResponse('This email has been registered, please login.', 422, [
                    'error' => 'Email already exists.'
                ]);
            }

            $existingPhone = User::where('phone_number', $details['phone_number'])->first();
            if ($existingPhone) {
                return $this->errorResponse('This phone number has been registered, please login.', 422, [
                    'error' => 'Phone number already exists.'
                ]);
            }

            $referrer = null;

            if (!empty($details['referral_code'])) {
                $referrer = User::where('referral_code', $details['referral_code'])->first();
            }

            $generatedCode = strtoupper(Str::random(8));
            $user = User::create([
                'email' => $details['email'],
                'phone_number' => $details['phone_number'],
                'password' => Hash::make($details['password']),
                'referral_code' => $generatedCode,
                'referred_by' => $referrer?->id ?? User::find(1)?->id,
            ]);

            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            PasswordResetToken::create([
                'email' => $details['email'],
                'token' => $code,
            ]);

            Mail::to($user->email)->send(new VerifyEmailCodeMail($code));

            DB::commit();

            return $this->successResponse([
                'message' => 'User registered successfully, Kindly verify your email before Login',
                'data'    => $user
            ], 201);
        } catch (\Exception $exp) {
            DB::rollBack();
            \Log::error("User registration failed", ["type" => "user_registration_failed", "server_error" => true, "email" => $details["email"], "exception" => $exp]);
            return $this->errorResponse('User registration failed.', 500, [
                'error' => $exp->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
