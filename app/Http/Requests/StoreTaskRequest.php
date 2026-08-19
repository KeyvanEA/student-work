<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'title' => ['required', 'string','min:5', 'max:120'],
            'description' => ['required', 'string', 'max:5000'],
            'budget' => ['required', 'integer', 'min:100000'],
            'deadline' => ['required', 'date', 'after:today'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            'skills' => ['required', 'array','max:10',],
            'skills.*' => ['required', 'integer', 'exists:skills,id','distinct'],

            'files' => ['nullable', 'array','max:3',],
            'files.*' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,zip,rar', 'max:10024',],


        ];
    }
}
