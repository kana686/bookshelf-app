<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillableに設定された属性が正しく一括代入できる()
    {
        $attributes = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $user = new User($attributes);

        $this->assertEquals($attributes['name'], $user->name);
        $this->assertEquals($attributes['email'], $user->email);
        $this->assertTrue(Hash::check($attributes['password'], $user->password));
    }

    public function test_hiddenに設定された属性が配列や_jso_n変換時に含まれない()
    {
        $user = new User([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_castsが正しく機能している()
    {
        $user = new User([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $user->email_verified_at = now();

        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
        $this->assertTrue(Hash::check('password', $user->password));
    }
}
