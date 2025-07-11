<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\Admin;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_admins_can_authenticate_using_the_login_screen(): void
    {
        // これでCSRFチェックを無効にする
        $this->withoutMiddleware();
        
         // Create an Admin
        $admin = Admin::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('nagoyameshi'),
        ]);

        // Login Request
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'nagoyameshi',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/home'); // 期待するホームページに設定

        // Adjust the login route and response as per your applicatio

        $this->assertTrue(Auth::guard('admin')->check());
        $response->assertRedirect(RouteServiceProvider::ADMIN_HOME);
    }

    public function test_admins_can_not_authenticate_with_invalid_password(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_admins_can_logout(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->post('/admin/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}