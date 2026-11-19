<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GeneratePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $images = $this->input('reference_images', []);
        if (is_array($images)) {
            $images = array_filter($images, fn($image) => !empty($image));
             $this->merge([
                'reference_images' => $images,
                'data' => $this->input('data', null),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pixelforge = config('pixelforge');
        return [
            'prompt' => ['required', 'string'],
            'size' => ['required', 'string', 'in:' . implode(',', $pixelforge['size'] ?? [])],
            'quality' => ['required', 'string', 'in:' . implode(',', $pixelforge['quality'] ?? [])],
            'reference_images' => ['array'],
            'reference_images.*' => ['string', 'url', 'nullable'],
            'data' => ['nullable', 'array'],
        ];
    }
}
