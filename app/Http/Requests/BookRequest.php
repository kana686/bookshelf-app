<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $book = $this->route('book');
        $bookId = $book ? $book->id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => [
                'string',
                'regex:/^(\d{10}|\d{13})$/',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ],
            'published_date' => ['date'],
            'description' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'genres' => ['required', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者名を入力してください',
            'author.max' => '著者名は255文字以内で入力してください',
            'isbn.regex' => 'ISBNは10桁または13桁で入力してください',
            'isbn.unique' => 'このISBNは既に登録されています',
            'published_date.date' => '正しい日付を入力してください',
            'description.max' => '説明は255文字以内で入力してください',
            'genres.required' => 'ジャンルを1つ以上選択してください',
            'genres.*.integer' => '正しいジャンルを選択してください',
            'genres.*.exists' => '正しいジャンルを選択してください',
            'image_url.url' => '正しいURL形式で入力してください',
        ];
    }
}
