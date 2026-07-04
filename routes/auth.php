<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Laravel\Passkeys\Http\Controllers\PasskeyConfirmationController;
use Laravel\Passkeys\Http\Controllers\PasskeyLoginController;
use Laravel\Passkeys\Http\Controllers\PasskeyRegistrationController;

$enableViews = config('fortify.views', true);
$guard = config('fortify.guard');
$authMiddleware = config('fortify.auth_middleware', 'auth').':'.$guard;
$limiter = config('fortify.limiters.login');
$twoFactorLimiter = config('fortify.limiters.two-factor');
$passkeyLimiter = config('fortify.limiters.passkeys');
$verificationLimiter = config('fortify.limiters.verification', '6,1');

if ($enableViews) {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware(['guest:'.$guard])
        ->name('login');
}

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(array_filter([
        'guest:'.$guard,
        $limiter ? 'throttle:'.$limiter : null,
    ]))
    ->name('login.store');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware([$authMiddleware])
    ->name('logout');

if (Features::enabled(Features::resetPasswords())) {
    if ($enableViews) {
        Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
            ->middleware(['guest:'.$guard])
            ->name('password.request');

        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
            ->middleware(['guest:'.$guard])
            ->name('password.reset');
    }

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware(['guest:'.$guard])
        ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware(['guest:'.$guard])
        ->name('password.update');
}

if (Features::enabled(Features::registration())) {
    if ($enableViews) {
        Route::get('/register', [RegisteredUserController::class, 'create'])
            ->middleware(['guest:'.$guard])
            ->name('register');
    }

    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware(['guest:'.$guard])
        ->name('register.store');
}

if (Features::enabled(Features::emailVerification())) {
    if ($enableViews) {
        Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
            ->middleware([$authMiddleware])
            ->name('verification.notice');
    }

    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware([$authMiddleware, 'signed', 'throttle:'.$verificationLimiter])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware([$authMiddleware, 'throttle:'.$verificationLimiter])
        ->name('verification.send');
}

if ($enableViews) {
    Route::get('/user/confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->middleware([$authMiddleware])
        ->name('password.confirm');
}

Route::get('/user/confirmed-password-status', [ConfirmedPasswordStatusController::class, 'show'])
    ->middleware([$authMiddleware])
    ->name('password.confirmation');

Route::post('/user/confirm-password', [ConfirmablePasswordController::class, 'store'])
    ->middleware([$authMiddleware])
    ->name('password.confirm.store');

if (Features::enabled(Features::twoFactorAuthentication())) {
    if ($enableViews) {
        Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
            ->middleware(['guest:'.$guard])
            ->name('two-factor.login');
    }

    Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:'.$guard,
            $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
        ]))
        ->name('two-factor.login.store');

    $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
        ? [$authMiddleware, 'password.confirm']
        : [$authMiddleware];

    Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.enable');

    Route::post('/user/confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.confirm');

    Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.disable');

    Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.qr-code');

    Route::get('/user/two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.secret-key');

    Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.recovery-codes');

    Route::post('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.regenerate-recovery-codes');
}

if (Features::enabled(Features::passkeys())) {
    $throttle = $passkeyLimiter ? ['throttle:'.$passkeyLimiter] : [];
    $passkeyAuthMiddleware = [$authMiddleware];
    $passkeyMiddleware = config('fortify-options.passkeys.confirmPassword', true)
        ? [...$passkeyAuthMiddleware, 'password.confirm']
        : $passkeyAuthMiddleware;
    $passkeyGuestMiddleware = ['guest:'.$guard, ...$throttle];
    $passkeyConfirmMiddleware = [...$passkeyAuthMiddleware, ...$throttle];
    $passkeyManageMiddleware = [...$passkeyMiddleware, ...$throttle];

    Route::get('/passkeys/login/options', [PasskeyLoginController::class, 'index'])
        ->middleware($passkeyGuestMiddleware)
        ->name('passkey.login-options');

    Route::post('/passkeys/login', [PasskeyLoginController::class, 'store'])
        ->middleware($passkeyGuestMiddleware)
        ->name('passkey.login');

    Route::get('/passkeys/confirm/options', [PasskeyConfirmationController::class, 'index'])
        ->middleware($passkeyConfirmMiddleware)
        ->name('passkey.confirm-options');

    Route::post('/passkeys/confirm', [PasskeyConfirmationController::class, 'store'])
        ->middleware($passkeyConfirmMiddleware)
        ->name('passkey.confirm');

    Route::get('/user/passkeys/options', [PasskeyRegistrationController::class, 'index'])
        ->middleware($passkeyManageMiddleware)
        ->name('passkey.registration-options');

    Route::post('/user/passkeys', [PasskeyRegistrationController::class, 'store'])
        ->middleware($passkeyManageMiddleware)
        ->name('passkey.store');

    Route::delete('/user/passkeys/{passkey}', [PasskeyRegistrationController::class, 'destroy'])
        ->middleware($passkeyMiddleware)
        ->name('passkey.destroy');
}
