<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $reviewId = $this->route('review')?->id ?? $this->route('id');

        $bookId = $this->route('book')?->id
            ?? $this->route('review')?->book_id
            ?? $this->input('book_id');

        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:255'],
            'book_id' => [
                'exists:books,id',
                Rule::unique('reviews')->where(function ($query) use ($bookId) {
                    return $query->where('user_id', $this->user()->id)
                        ->where('book_id', $bookId);
                })->ignore($reviewId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価を選択してください',
            'rating.between' => '評価は1から5の間で指定してください',
            'comment.required' => 'コメントを入力してください',
            'comment.max' => 'コメントは255文字以内で入力してください',
            'book_id.unique' => 'この書籍にはすでにレビューを投稿済みです',
        ];
    }
}
