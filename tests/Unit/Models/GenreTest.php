<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use PHPUnit\Framework\TestCase;

class GenreTest extends TestCase
{
    public function test_fillableに設定された属性が正しく一括代入できる(): void
    {
        $genre = new Genre([
            'name' => '小説',
        ]);

        $this->assertEquals('小説', $genre->name);
    }
}
