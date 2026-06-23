<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'book_id' => 'required|integer|exists:books,id|unique:reading_plans,book_id,NULL,id,user_id,' . auth()->id(),
            'target_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください',
            'book_id.unique' => 'この書籍にはすでに読書計画が登録されています',
            'target_date.required' => '期日を選択してください',
            'target_date.after_or_equal' => '過去の日付は選択できません',
        ];
    }
}
