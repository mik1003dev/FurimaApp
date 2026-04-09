<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'item_image' => ['nullable', 'mimes:jpeg,png'],
            'uploaded_item_image' => ['required_without:item_image'],
            'category_codes' => ['required', 'array', 'min:1'],
            'category_codes.*' => ['integer', Rule::in(array_keys(Item::CATEGORY_LABELS))],
            'name' => ['required', 'string'],
            'description' => ['required', 'string', 'max:255'],
            'condition' => ['required', Rule::in(array_keys(Item::CONDITION_LABELS))],
            'price' => ['required', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'item_image.mimes' => '商品画像はjpegまたはpng形式で選択してください',
            'uploaded_item_image.required_without' => '商品画像を選択してください',
            'category_codes.required' => 'カテゴリーを1つ以上選択してください',
            'category_codes.array' => 'カテゴリーを1つ以上選択してください',
            'category_codes.min' => 'カテゴリーを1つ以上選択してください',
            'category_codes.*.in' => '選択したカテゴリーが不正です',
            'name.required' => '商品名を入力してください',
            'description.required' => '商品の説明を入力してください',
            'description.max' => '商品の説明は255文字以内で入力してください',
            'condition.required' => '商品の状態を選択してください',
            'condition.in' => '商品の状態が不正です',
            'price.required' => '販売価格を入力してください',
            'price.numeric' => '販売価格は数値で入力してください',
            'price.min' => '販売価格は0円以上で入力してください',
            'brand.max' => 'ブランド名は255文字以内で入力してください',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->hasFile('item_image') && !$validator->errors()->has('item_image')) {
            $tempPath = $this->file('item_image')->store('items/tmp', 'public');
            $previousTempPath = $this->input('uploaded_item_image');

            if (!empty($previousTempPath) && $previousTempPath !== $tempPath && Storage::disk('public')->exists($previousTempPath)) {
                Storage::disk('public')->delete($previousTempPath);
            }

            $this->merge(['uploaded_item_image' => $tempPath]);
        }

        throw new HttpResponseException(
            redirect($this->getRedirectUrl())
                ->withInput($this->except($this->dontFlash))
                ->withErrors($validator, $this->errorBag)
        );
    }
}
