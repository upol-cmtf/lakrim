<?php
namespace App\Traits;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ThrottlesLogins
{
    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($request),
            $this->maxAttempts(),
        );
    }

    protected function incrementLoginAttempts(Request $request): void
    {
        $this->limiter()->hit(
            $this->throttleKey($request),
            $this->decayMinutes() * 60,
        );
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendLockoutResponse(Request $request): never
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request),
        );

        throw ValidationException::withMessages([
            $this->username() => [
                trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ],
        ])->status(Response::HTTP_TOO_MANY_REQUESTS);
    }

    protected function clearLoginAttempts(Request $request): void
    {
        $this->limiter()->clear($this->throttleKey($request));
    }

    protected function fireLockoutEvent(Request $request): void
    {
        event(new Lockout($request));
    }

    protected function throttleKey(Request $request): string
    {
        /** @var string $username */
        $username = $request->input($this->username(), '');

        return Str::transliterate(Str::lower($username) . '|' . $request->ip());
    }

    protected function limiter(): RateLimiter
    {
        return app(RateLimiter::class);
    }

    public function maxAttempts(): int
    {
        /** @var int $maxAttempts */
        $maxAttempts = property_exists($this, 'maxAttempts') ? $this->maxAttempts : 5;

        return $maxAttempts;
    }

    public function decayMinutes(): int
    {
        /** @var int $decayMinutes */
        $decayMinutes = property_exists($this, 'decayMinutes') ? $this->decayMinutes : 1;

        return $decayMinutes;
    }
}
