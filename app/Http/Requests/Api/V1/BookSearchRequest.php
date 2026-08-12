<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BookSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('genre_id') && ! is_array($this->genre_id)) {
            $this->merge([
                'genre_id' => [$this->genre_id],
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'keyword' => ['string', 'max:255'],
            'genre_id' => ['array'],
            'genre_id.*' => ['integer', 'exists:genres,id'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.max' => '検索キーワードは255文字以内で入力してください',
            'genre_id.*.integer' => '選択されたジャンルが正しくありません',
            'genre_id.*.exists' => '選択されたジャンルが正しくありません',
            'page.min' => 'ページ番号は1以上の整数で指定してください',
            'page.integer' => 'ページ番号は1以上の整数で指定してください',
            'per_page.min' => '1ページあたりの件数は1以上の整数で指定してください',
            'per_page.integer' => '1ページあたりの件数は1以上の整数で指定してください',
            'per_page.max' => '1ページあたりの件数は50件以下で指定してください',
        ];
    }
}
