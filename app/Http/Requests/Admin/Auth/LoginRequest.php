<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],

            'remember' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'email' =>
                (string) $this->input(
                    'email'
                ),

            'password' =>
                (string) $this->input(
                    'password'
                ),

            'is_active' => true,
        ];

        if (
            !Auth::attempt(
                $credentials,
                $this->boolean('remember')
            )
        ) {
            RateLimiter::hit(
                $this->throttleKey(),
                60
            );

            throw ValidationException::withMessages([
                'email' =>
                    'Email atau password tidak valid.',
            ]);
        }

        RateLimiter::clear(
            $this->throttleKey()
        );
    }

    private function ensureIsNotRateLimited(): void
    {
        if (
            !RateLimiter::tooManyAttempts(
                $this->throttleKey(),
                5
            )
        ) {
            return;
        }

        $seconds =
            RateLimiter::availableIn(
                $this->throttleKey()
            );

        throw ValidationException::withMessages([
            'email' =>
                'Terlalu banyak percobaan login. '
                . 'Silakan coba lagi dalam '
                . $seconds
                . ' detik.',
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower(
                (string) $this->input(
                    'email'
                )
            )
            . '|'
            . $this->ip()
        );
    }
}
