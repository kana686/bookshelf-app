<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GenreFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_未認証の状態でジャンル一覧画面にアクセスした場合ログイン画面にリダイレクトされる()
    {
        $response = $this->get(route('genres.index'));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_認証済みの状態でジャンル一覧画面にアクセスした場合各ジャンルに紐づく書籍数付きでジャンル一覧が表示される()
    {
        $user = User::first();
        $genre = Genre::first();
        $bookCount = $genre->books()->count();

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertSee($genre->name);
        $response->assertSee((string) $bookCount);
    }

    public function test_ジャンル一覧画面でジャンルが10件ページでページネーション表示される()
    {
        $user = User::first();

        Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);

        $response->assertViewHas('genres', function ($paginator) {
            return $paginator->perPage() === 10
                && $paginator->total() === 12;
        });
    }

    public function test_未認証の状態でジャンル詳細画面にアクセスした場合ログイン画面にリダイレクトされる()
    {
        $genre = Genre::first() ?? Genre::factory()->create();

        $response = $this->get(route('genres.show', $genre));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_ジャンル詳細画面で紐づく書籍が10件ページでページネーション表示される()
    {
        $user = User::first();
        $genre = Genre::first();

        $currentCount = $genre->books()->count();
        if ($currentCount < 12) {
            $neededCount = 12 - $currentCount;

            $books = Book::factory()->count($neededCount)->create([
                'user_id' => $user->id,
            ]);

            $genre->books()->attach($books->pluck('id'));
        }

        $response = $this->actingAs($user)->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertSee($genre->name);

        $response->assertViewHas('books', function ($paginator) {
            return $paginator->perPage() === 10
                && $paginator->total() === 12;
        });
    }

    public function test_未認証の状態でジャンル登録画面にアクセスした場合ログイン画面にリダイレクトされる()
    {
        $response = $this->get(route('genres.create'));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_正常にジャンルを登録できる()
    {
        $user = User::first();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => '新しいテストジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('genres', [
            'name' => '新しいテストジャンル',
        ]);
    }

    // 登録時バリデーションエラー
    #[DataProvider('genreValidationDataProvider')]
    public function test_登録時バリデーションエラー($inputData, $expectedErrorField, $expectedErrorMessage)
    {
        $user = User::first();

        $response = $this->actingAs($user)->post(route('genres.store'), $inputData);

        $response->assertSessionHasErrors([$expectedErrorField => $expectedErrorMessage]);
    }

    public static function genreValidationDataProvider()
    {
        return [
            '未入力' => [['name' => ''], 'name', 'ジャンル名を入力してください'],
            '256文字以上' => [['name' => str_repeat('あ', 256)], 'name', 'ジャンル名は255文字以内で入力してください'],
        ];
    }

    public function test_既に登録されているジャンル名と同じ場合バリデーションエラーになる()
    {
        $user = User::first();
        $existingGenre = Genre::first();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => $existingGenre->name,
        ]);

        $response->assertSessionHasErrors(['name' => 'このジャンル名は既に登録されています']);
    }

    // 編集
    public function test_未認証の状態でジャンル編集画面にアクセスした場合ログイン画面にリダイレクトされる()
    {
        $genre = Genre::first();

        $response = $this->get(route('genres.edit', $genre));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_ジャンル編集画面でジャンル名を変更して正常に更新できる初期値の検証含む()
    {
        $user = User::first();
        $genre = Genre::first();

        $response = $this->actingAs($user)->get(route('genres.edit', $genre));
        $response->assertStatus(200);
        $response->assertSee($genre->name);

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => '更新後のジャンル名',
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '更新後のジャンル名',
        ]);
    }

    // 更新時バリデーションエラー
    #[DataProvider('invalidGenreDataProvider')]
    public function test_更新時バリデーションエラー($name, $expectedMessage)
    {
        $user = User::first();
        $genre = Genre::first();

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => $name,
        ]);

        $response->assertSessionHasErrors(['name' => $expectedMessage]);
    }

    public static function invalidGenreDataProvider()
    {
        return [
            'ジャンル名未入力' => ['', 'ジャンル名を入力してください'],
            'ジャンル名256文字以上' => [str_repeat('あ', 256), 'ジャンル名は255文字以内で入力してください'],
        ];
    }

    public function test_既に登録されている他のジャンル名と同じ場合バリデーションエラーになり更新できない()
    {
        $user = User::first();
        $genres = Genre::take(2)->get();
        $targetGenre = $genres[0];
        $otherGenre = $genres[1];

        $response = $this->actingAs($user)->put(route('genres.update', $targetGenre), [
            'name' => $otherGenre->name,
        ]);

        $response->assertSessionHasErrors(['name' => 'このジャンル名は既に登録されています']);
    }

    public function test_書籍が紐づいていないジャンルを正常に削除できる()
    {
        $user = User::first();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_書籍が紐づいているジャンルは削除できない()
    {
        $user = User::first();
        $genre = Genre::has('books')->first();

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }
}
