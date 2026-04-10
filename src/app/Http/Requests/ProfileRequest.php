<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'bail',
                Rule::requiredIf(fn () => empty(optional($this->user())->avatar_path) && empty($this->input('avatar_temp_path'))),
                'nullable',
                'image',
                'mimes:jpeg,png',
                'max:2048',
            ],
            'avatar_temp_path' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:20'],
            'postal_code' => ['required', 'regex:/^\\d{3}-\\d{4}$/'],
            'address' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.required' => 'プロフィール画像を選択してください',
            'avatar.image' => 'プロフィール画像は画像ファイルを選択してください',
            'avatar.mimes' => 'プロフィール画像は拡張子が.jpegもしくは.pngのファイルを選択してください',
            'avatar.max' => 'プロフィール画像は2MB以下で選択してください',
            'name.required' => 'ユーザー名を入力してください',
            'name.max' => 'ユーザー名は20文字以内で入力してください',
            'postal_code.required' => '郵便番号を入力してください',
            'postal_code.regex' => '郵便番号はハイフンありの8文字で入力してください',
            'address.required' => '住所を入力してください',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->hasFile('avatar') && !$validator->errors()->has('avatar')) {
            $tempPath = $this->file('avatar')->store('avatars/tmp', 'public');
            $previousTempPath = $this->input('avatar_temp_path');

            if (!empty($previousTempPath) && $previousTempPath !== $tempPath && Storage::disk('public')->exists($previousTempPath)) {
                Storage::disk('public')->delete($previousTempPath);
            }

            $this->merge(['avatar_temp_path' => $tempPath]);
        }

        throw new HttpResponseException(
            redirect($this->getRedirectUrl())
                ->withInput($this->except($this->dontFlash))
                ->withErrors($validator, $this->errorBag)
        );
    }
}
