<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
                event(new Lockout($this));

                $seconds = RateLimiter::availableIn($this->throttleKey());
                session()->flash('lockout_seconds', max($seconds, 1));

                throw ValidationException::withMessages([
                    'email' => trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ]);
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();
        if ($user && ! $user->is_active) {
            Auth::logout();
            RateLimiter::clear($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Kepala Laboratorium.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        session()->flash('lockout_seconds', max($seconds, 1));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * Layered throttling: this key limits attempts per email + IP pair, while
     * the `throttle:5,1` middleware on the login route limits all attempts per
     * IP address alone, so credential stuffing that rotates email addresses
     * from a single IP is blocked as well.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
