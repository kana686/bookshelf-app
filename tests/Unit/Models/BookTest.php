<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase
{
    public function test_fillableに設定された属性が一括代入できる()
    {
        $book = new Book([
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '9784101010999',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の説明文',
        ]);

        $this->assertEquals('テストタイトル', $book->title);
        $this->assertEquals('テスト著者', $book->author);
        $this->assertEquals('9784101010999', $book->isbn);
        $this->assertEquals('2026-01-01', $book->published_date->format('Y-m-d'));
        $this->assertEquals('テスト用の説明文', $book->description);
    }
}
