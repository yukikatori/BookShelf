<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|digits:13|unique:books,isbn,' . $this->route('book')->id,
            'published_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url|max:255',
            'genres' => 'required|array',
            'genres.*' => 'integer|exists:genres,id'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者を入力してください',
            'author.max' => '著者は255文字以内で入力してください',
            'published_date.date' => '日付の形式で入力してください',
            'isbn.digits' => 'ISBNコードは13桁の数字で入力してください',
            'isbn.unique' => '入力したISBNコードは既に使用されています',
            'description.max' => '説明は1000文字以内で入力してください',
            'image_url.url' => 'URL形式で入力してください',
            'image_url.max' => 'URLは255文字以内で入力してください',
            'genres.required' => 'ジャンルを選択してください',
        ];
    }
}
