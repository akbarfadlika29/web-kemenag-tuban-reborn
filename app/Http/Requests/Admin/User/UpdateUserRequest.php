<?php

namespace App\Http\Requests\Admin\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(
                trim((string) $this->input('email'))
            ),
            'position' => $this->filled('position')
                ? trim((string) $this->input('position'))
                : null,
            'phone' => $this->filled('phone')
                ? trim((string) $this->input('phone'))
                : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            'unit_id' => [
                'nullable',
                Rule::exists('units', 'id')
                    ->where(
                        fn ($query) =>
                            $query
                                ->where('is_active', true)
                                ->whereNull('deleted_at')
                    ),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user?->id),
            ],

            'position' => [
                'nullable',
                'string',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'unit_id' => 'unit kerja',
            'name' => 'nama',
            'email' => 'email',
            'position' => 'jabatan',
            'phone' => 'nomor telepon',
            'is_active' => 'status',
            'password' => 'password',
        ];
    }
}