<?php

namespace Tests\Unit\Models;

use App\Models\Review;
use PHPUnit\Framework\TestCase;

class ReviewTest extends TestCase
{
    public function test_fillableに設定された属性が一括代入できる(): void
    {
        $review = new Review([
            'user_id' => 1,
            'book_id' => 1,
            'rating' => 5,
            'comment' => 'とても参考になる本でした！',
        ]);

        $this->assertEquals(1, $review->user_id);
        $this->assertEquals(1, $review->book_id);
        $this->assertEquals(5, $review->rating);
        $this->assertEquals('とても参考になる本でした！', $review->comment);
    }
}
