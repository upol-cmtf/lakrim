<?php
namespace App\Traits;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

trait AuthenticatesUsers
{
    use RedirectsUsers;
    use ThrottlesLogins;

    /**
     * Show the application's login form.
     */
    public function showLoginForm(): View
    {
        /** @var view-string $viewName */
        $viewName = 'auth.login';

        return view($viewName);
    }

    /**
     * Handle a login request to the application.
     *
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @throws ValidationException
     */
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     */
    protected function attemptLogin(Request $request): bool
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember'),
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @return array<string, mixed>
     */
    protected function credentials(Request $request): array
    {
        /** @var array<string, mixed> $credentials */
        $credentials = $request->only($this->username(), 'password');

        return $credentials;
    }

    /**
     * Send the response after the user was authenticated.
     */
    protected function sendLoginResponse(Request $request): RedirectResponse|JsonResponse
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $response = $this->authenticated($request, $this->guard()->user());
        if ($response) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    protected function authenticated(Request $request, mixed $user): RedirectResponse|JsonResponse|null
    {
        return null;
    }

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    protected function sendFailedLoginResponse(Request $request): never
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    public function username(): string
    {
        return 'email';
    }

    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        $response = $this->loggedOut($request);

        if ($response) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
    }

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    protected function loggedOut(Request $request): RedirectResponse|JsonResponse|null
    {
        return null;
    }

    protected function guard(): StatefulGuard
    {
        return Auth::guard();
    }
}
