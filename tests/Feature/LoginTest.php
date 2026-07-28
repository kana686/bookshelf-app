<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログイン画面が正しく表示される()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee(['メール', 'パスワード'], false);
    }

    public function test_正しい情報が入力された場合ログイン処理が実行される()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_ログイン時のメールアドレスが入力されていない場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_ログイン時のパスワードが入力されていない場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_ログイン時の入力情報が間違っている場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function test_ログイン済みの状態でログイン画面にアクセスした場合自動でリダイレクトされる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/books');
    }

    public function test_ログアウトができる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
