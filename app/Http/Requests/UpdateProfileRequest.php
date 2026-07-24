<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $user = $this->user();
        if(!$user->isProfileCompleted()){
            return [
                'full_name' => ['required', 'string', 'max:100', 'min:5'],
                'field_of_study'=>['required','string'],
                'university_name'=>['required', 'string'],
                'student_number'=>['required' ,'string', 'size:9'],
                'email'=>['email', Rule::unique('users', 'email')->ignore($user->id) ,'nullable'],
                'bio'=>['nullable', 'string','max:1000'],
                'avatar' => [
                    'sometimes',
                    'nullable',
                    'image',
                    'mimes:jpeg,jpg,png,webp',
                    'max:5048',
                ],
                'resume_file' => [
                    'sometimes',
                    'nullable',
                    'file',
                    'mimes:pdf',
                    'max:10024',
                ],

            ];

        }
        return [
            'full_name' => ['sometimes', 'string', 'max:100', 'min:5'],
            'field_of_study'=>['sometimes', 'string'],
            'university_name'=>['sometimes', 'string'],
            'student_number'=>['sometimes' , 'string' ,'size:9'],
            'email'=>['email', Rule::unique('users', 'email')->ignore($user->id),'nullable'],
            'bio'=>['nullable', 'string','max:1000'],
            'avatar' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5048',
            ],
            'resume_file' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:pdf',
                'max:10024',
            ],

        ];
    }

    public function messages()
    {
        return [

            'full_name.required' => 'نام کامل شما الزامی است.',
            'field_of_study.required' => 'رشته تحصیلی شما الزامی است.',
            'university_name.required'=> 'نام دانشگاه شما الزامی است.',
            'student_number.required' => 'شماره دانشجویی شما الزامی است.',
            'avatar.image'=>'برای بارگذاری آواتار باید تصویر آپلود کنید.',
            'avatar.mimes'=>'تصویر آپلودی شما باید از نوع های (jpeg, jpg, png, webp) باشد.',
            'resume_file.file'=>'برای بارگذاری رزومه باید فایل آپلود کنید',
            'resume_file.mimes'=>'فایل آپلودی شما باید از نوع (pdf) باشد.'
        ];
    }
}
