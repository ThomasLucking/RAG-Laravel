<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class DocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->tags)) {
            $this->merge([
                'tags' => array_values(array_filter(array_map('trim', explode(',', $this->tags)))),
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
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->isMethod('post')) {
                        return;
                    }

                    $slug = Str::slug((string) $value);

                    if (Document::withTrashed()->where('slug', $slug)->exists()) {
                        $fail('A document (possibly deleted) already uses this title.');
                    }
                },
            ],
            'summary' => ['required', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'distinct', 'max:255'],
            'updated' => ['required', 'date'],
            'content' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.unique' => 'A document (possibly deleted) already uses this title.',
        ];
    }
}
