<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_有効なisbnを指定してリクエストした際正しい書籍情報が返却される()
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍タイトル',
                            'authors' => ['テスト著者'],
                            'publishedDate' => '2026',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784297123456');

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'テスト書籍タイトル',
                'author' => 'テスト著者',
            ]);
    }

    public function test_無効な_isbnや存在しないisbnを指定した際エラーレスポンスが返る()
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/0000000000000');

        $response->assertStatus(404)->assertJson([
            'error' => '該当する書籍が見つかりませんでした。',
        ]);
    }

    public function test_不正な形式のisbnを指定した際バリデーションエラーメッセージが返る()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/books/isbn/12345');

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'ISBNは13桁で入力してください。',
            ]);
    }

    public function test_isbnやpublished_dateが未入力の状態でも書籍が正常に登録できる()
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => 'テストジャンル']);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => '手動登録の書籍',
            'author' => 'テスト著者名',
            'genres' => [$genre->id],
            'isbn' => null,
            'published_date' => null,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('books', [
            'title' => '手動登録の書籍',
            'author' => 'テスト著者名',
            'isbn' => null,
            'published_date' => null,
        ]);
    }
}
