<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('passport:install');
    }

    public function test_user_login_successful()
    {
        User::factory()->create([
            'email' => 'user@gmail.com',
            'password' => Hash::make('secret'),
        ]);

        $user = [
            'email' => 'user@gmail.com',
            'password' => 'secret',
        ];
        
        $response = $this->postJson('/api/login',$user);
        // dd($response);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success','data']);
    }

    public function test_user_login_invalid_data_successful()
    {
        User::factory()->create([
            'email' => 'user@gmail.com',
            'password' => Hash::make('secret'),
        ]);

        $user = [
            'email' => 'user@gmail.com',
            'password' => '',
        ];
        
        $response = $this->postJson('/api/login',$user);
        // dd($response);
        $response->assertStatus(404);
        $response->assertJsonFragment(['success' => false,
                                    'data' => ['error','unauthorized']]);
    }

    public function test_user_register_successfully()
    {
        $user = [
            'name' => 'user',
            'type' => 'owner',
            'email' => 'user@gmail.com',
            'password' => 'secret',
            'c_password' => 'secret',
        ];
     
        $response = $this->postJson('api/register',$user);
     
        $response->assertStatus(200);
        $response->assertJsonStructure(['success','data'=>['token']]);
    }

    public function test_user_register_invalid_email_successfully()
    {
        User::factory()->create(['email' => 'user@gmail.com']);
        $user = [
            'name' => 'user',
            'type' => 'owner',
            'email' => 'user@gmail.com',
            'password' => 'secret',
            'c_password' => 'secret',
        ];
     
        $response = $this->postJson('api/register',$user);
     
        $response->assertStatus(404);
        $response->assertJsonFragment(['data' => 'this email address is not available. choose a different address'
                                        ,'success' => false]);
    }

    public function test_user_register_validation_error_successfully()
    {
        $user = [
            'name' => '',
            'type' => 'owner',
            'email' => 'user@gmail.com',
            'password' => 'secret',
            'c_password' => 'secret',
        ];
     
        $response = $this->postJson('api/register',$user);
     
        $response->assertStatus(404);
        $response->assertJsonFragment([
            'data' => [
                'name'=>['The name field is required.']
                ]
            ,'success' => false
        ]);

    }
}
