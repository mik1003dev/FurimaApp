<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Support\CreatesMarketplaceData;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use CreatesMarketplaceData;
    use RefreshDatabase;

    /** 会員登録時にユーザー名が必須であることを確認する */
    public function test_register_requires_name(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => '',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /** 会員登録時にメールアドレスが必須であることを確認する */
    public function test_register_requires_email(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** 会員登録時にパスワードが必須であることを確認する */
    public function test_register_requires_password(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** 会員登録時に8文字未満のパスワードが拒否されることを確認する */
    public function test_register_requires_password_with_minimum_length(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /** 会員登録時に確認用パスワードとの不一致が検知されることを確認する */
    public function test_register_requires_matching_password_confirmation(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    /** 正常な会員登録後にメール認証案内へ遷移し、認証メールが送信されることを確認する */
    public function test_register_redirects_to_verification_notice_and_sends_email(): void
    {
        Event::fake([Registered::class]);
        Notification::fake();

        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'user@example.com')->firstOrFail();

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->email_verified_at);
        Event::assertDispatched(Registered::class);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** 未認証ユーザーは同じメールアドレスで再登録でき、認証メールが再送されることを確認する */
    public function test_unverified_user_can_re_register_with_same_email(): void
    {
        Notification::fake();

        $existingUser = $this->createUser([
            'name' => '旧ユーザー',
            'email' => 'user@example.com',
            'email_verified_at' => null,
        ]);

        $response = $this->post(route('register'), [
            'name' => '新ユーザー',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($existingUser->fresh());
        $this->assertSame('新ユーザー', $existingUser->fresh()->name);
        $this->assertTrue(Hash::check('password123', $existingUser->fresh()->password));
        $this->assertDatabaseCount('users', 1);
        Notification::assertSentTo($existingUser->fresh(), VerifyEmail::class);
    }

    /** 認証済みユーザーは同じメールアドレスで再登録できないことを確認する */
    public function test_verified_user_cannot_re_register_with_same_email(): void
    {
        $this->createUser([
            'email' => 'user@example.com',
        ]);

        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'email' => 'このメールアドレスは既に登録されています',
        ]);
        $this->assertGuest();
    }

    /** ログイン時にメールアドレス入力が必須であることを確認する */
    public function test_login_requires_email(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** ログイン時にパスワード入力が必須であることを確認する */
    public function test_login_requires_password(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'user@example.com',
            'password' => '',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** 誤ったログイン情報ではログインできないことを確認する */
    public function test_login_rejects_invalid_credentials(): void
    {
        $this->createUser([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
        $this->assertGuest();
    }

    /** 未認証ユーザーがログインすると認証案内画面へ遷移することを確認する */
    public function test_unverified_user_is_redirected_to_verification_notice_when_logging_in(): void
    {
        Notification::fake();

        $user = $this->createUser([
            'email' => 'user@example.com',
            'email_verified_at' => null,
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** 認証済みユーザーは正常にログインできることを確認する */
    public function test_verified_user_can_log_in(): void
    {
        $user = $this->createUser([
            'email' => 'verified@example.com',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'verified@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/?tab=mylist');
        $this->assertAuthenticatedAs($user);
    }

    /** ログイン中のユーザーがログアウトできることを確認する */
    public function test_user_can_log_out(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /** 未認証ユーザーが認証案内画面を閲覧し、認証メールを再送できることを確認する */
    public function test_unverified_user_can_view_verification_notice_and_resend_email(): void
    {
        Notification::fake();

        $user = $this->createUser([
            'email_verified_at' => null,
        ]);

        $page = $this->actingAs($user)->get(route('verification.notice'));
        $page->assertOk();
        $page->assertSee('登録していただいたメールアドレスに認証メールを送付しました。');

        $response = $this->actingAs($user)->post(route('verification.send'));
        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** 認証リンクにアクセスするとメール認証が完了し、プロフィール設定へ遷移することを確認する */
    public function test_verified_user_can_complete_email_verification_and_is_redirected_to_profile_edit(): void
    {
        $user = $this->createUser([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('profile.edit'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
