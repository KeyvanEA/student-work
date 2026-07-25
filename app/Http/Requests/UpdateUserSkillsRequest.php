<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSkillsRequest extends FormRequest
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
        return ['skills' => 'sometimes|array|max:10', 'skills.*' => 'exists:skills,id|integer'

        ];
    }

    public function messages(): array
    {
        return [
            'skills.*.exists' => 'مهارت مورد نظر وجود ندارد!',
            'skills.max'=>'تعداد مهارت های وارد شده از تعداد مجاز (10) بیشتر است.'
        ];
    }
}
