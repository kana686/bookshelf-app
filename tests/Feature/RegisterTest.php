<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録画面が正しく表示される()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee(['名前', 'メール', 'パスワード', 'パスワード確認'], false);
    }

    public function test_必須項目を全て入力して正常に登録ができる()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_名前が入力されていない場合バリデーションエラーが表示される()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    public function test_名前が256文字以上の場合バリデーションエラーが表示される()
    {
        $response = $this->post('/register', [
            'name' => str_repeat('あ', 256),
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前は255文字以内で入力してください']);
    }

    public function test_メールアドレスが入力されていない場合バリデーションエラーが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_メールアドレスがメール形式でない場合バリデーションエラーが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスはメール形式で入力してください']);
    }

    public function test_メールアドレスが256文字以上の場合バリデーションエラーが表示される()
    {
        $longEmail = str_repeat('a', 244).'@example.com';

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => $longEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスは255文字以内で入力してください']);
    }

    public function test_メールアドレスが既に登録されている場合バリデーションエラーになる()
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'このメールアドレスは既に登録されています']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_パスワードが入力されていない場合バリデーションエラーが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_パスワードが7文字以下の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    public function test_パスワードが確認用パスワードと一致しない場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }
}
