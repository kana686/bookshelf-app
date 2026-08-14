<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('invalidRegisterDataProvider')]
    public function test_会員登録時バリデーションエラー($field, $invalidValue, $requestDataOverrides, $expectedMessage)
    {
        $baseData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $requestData = array_merge($baseData, $requestDataOverrides);

        $response = $this->post('/register', $requestData);

        $response->assertInvalid([
            $field => $expectedMessage,
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public static function invalidRegisterDataProvider()
    {
        return [
            '名前未入力' => [
                'name',
                '',
                ['name' => ''],
                'お名前を入力してください',
            ],
            '名前256文字以上' => [
                'name',
                str_repeat('あ', 256),
                ['name' => str_repeat('あ', 256)],
                'お名前は255文字以内で入力してください',
            ],
            'メールアドレス未入力' => [
                'email',
                '',
                ['email' => ''],
                'メールアドレスを入力してください',
            ],
            'メールアドレス形式不正' => [
                'email',
                'invalid-email',
                ['email' => 'invalid-email'],
                'メールアドレスはメール形式で入力してください',
            ],
            'メールアドレス256文字以上' => [
                'email',
                str_repeat('a', 244).'@example.com',
                ['email' => str_repeat('a', 244).'@example.com'],
                'メールアドレスは255文字以内で入力してください',
            ],
            'パスワード未入力' => [
                'password',
                '',
                ['password' => '', 'password_confirmation' => ''],
                'パスワードを入力してください',
            ],
            'パスワード7文字以下' => [
                'password',
                'pass123',
                ['password' => 'pass123', 'password_confirmation' => 'pass123'],
                'パスワードは8文字以上で入力してください',
            ],
            'パスワード不一致' => [
                'password',
                'different_password',
                ['password_confirmation' => 'different_password'],
                'パスワードと一致しません',
            ],
        ];
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
}
