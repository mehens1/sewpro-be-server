<?php

use Lorisleiva\Actions\Facades\Actions;

Route::group(['prefix' => 'v1'], function () {

    // Route::group(['middleware' => ['auth:api', 'logContext']], function () {
    //     Actions::registerRoutes();
    // });

    Route::group(['prefix' => 'auth'], function () {
        Route::post("/register", \App\Actions\Auth\Register::class);
        Route::post("/login", \App\Actions\Auth\Login::class);
        // Route::post("/google", \App\Actions\auth\GoogleAuth::class);
        // Route::post("/google/link-account", \App\Actions\auth\SocialAuthLinkAccount::class);
        // Route::post("/apple", \App\Actions\auth\AppleAuth::class);
        // Route::post("/apple/link-account", \App\Actions\auth\SocialAuthLinkAccount::class);
        // Route::post("/verify-email-token", \App\Actions\auth\VerifyEmail::class);
        // Route::post("/resend-verification-code", \App\Actions\auth\ResendVerificationCode::class);

        // Route::group(['prefix' => 'password'], function () {
        //     Route::post("/reset", \App\Actions\auth\RequestPasswordReset::class);
        //     Route::post("/verify-token", \App\Actions\auth\VerifyPasswordResetToken::class);
        //     Route::post("/update", \App\Actions\auth\UpdatePassword::class);
        // });

        // Route::group(['middleware' => ['auth:api', 'allowedAppVersions', 'logContext']], function () {
        //     Route::post("/personal-profile-setup", \App\Actions\Profile\PersonalInformationSetup::class);
        //     Route::post("/student-profile-setup", \App\Actions\Profile\SchoolInformationSetup::class);
        //     Route::post("/course-setup", \App\Actions\Course\CreateUserCourses::class);
        // });
    });

    // Route::group(['prefix' => 'profile', 'middleware' => ['auth:api', 'allowedAppVersions', 'logContext']], function () {
    //     Route::get("/tag/{tag}", \App\Actions\Profile\ChooseATag::class);
    //     Route::post("/tag/{tag}", \App\Actions\Profile\ChooseATag::class);
    //     Route::post("/pii", \App\Actions\Profile\PersonalInformationSetup::class);
    //     Route::put("/pii", \App\Actions\Profile\PersonalInformationUpdate::class);
    //     Route::put("/socials", \App\Actions\Profile\SocialLinksUpdate::class);
    //     Route::post("/school", \App\Actions\Profile\SchoolInformationSetup::class);
    //     Route::get("/me", \App\Actions\Profile\GetUserProfile::class);
    //     Route::get("/firebase-token", \App\Actions\Profile\GetFirebaseToken::class);
    //     Route::post('/end-semester', \App\Actions\Profile\EndSemester::class);
    //     Route::post('/change-department', \App\Actions\Profile\StudentDepartmentChange::class);
    // });

    // Route::group(['prefix' => 'user/courses', 'middleware' => ['auth:api', 'allowedAppVersions', 'logContext']], function () {
    //     Route::post("/", \App\Actions\Course\CreateUserCourses::class);
    //     Route::post("/suggested", \App\Actions\Course\CreateSuggestedCoursesAction::class);
    //     Route::put("/", \App\Actions\Course\UpdateUserCourses::class);
    //     Route::get("/", \App\Actions\Course\GetUserCourses::class);
    // });

    // Route::group(['prefix' => 'past-questions-meta', 'middleware' => ['auth:api', 'allowedAppVersions']], function () {
    //     Route::get("/{id}", \App\Actions\Course\GetCoursePastQuestionMeta::class);
    //     Route::get("/", \App\Actions\Course\ListCoursePastQuestionMeta::class);
    // });
    // Route::post("/subscription/verify", \App\Actions\Subscriptions\VerifySubscription::class)
    //     ->middleware(['auth:api', 'logContext']);
    // Route::post("/appstore/test", \App\Actions\Subscriptions\RequestTestAppStoreNotification::class);

    // Password reset routes
    Route::post('/forgot-password', \App\Actions\Auth\ForgotPassword::class)
        ->middleware('guest')
        ->name('password.email');

    Route::post('/reset-password', \App\Actions\Auth\ResetPassword::class)
        ->middleware('guest')
        ->name('password.update');
    
    // Email verification routes
    Route::get('/email/verify/{id}/{hash}', \App\Actions\Auth\VerifyEmail::class)
        ->middleware(['auth:api', 'signed'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', \App\Actions\Auth\ResendEmailVerification::class)
        ->middleware(['auth:api', 'throttle:6,1'])
        ->name('verification.send');
});
