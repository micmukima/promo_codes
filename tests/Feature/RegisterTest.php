<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RegisterTest extends TestCase
{
    public function testRegistersSuccessfully()
    {
        $payload = [
            'name' => 'John',
            'email' => 'john@safeboda.com',
            'password' => 'safeboda123',
            'password_confirmation' => 'safeboda123',
        ];

        $this->json('post', '/api/register', $payload)
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'api_token',
                ],
            ]);;
    }

    public function testRequiresPasswordEmailAndName()
    {
        $this->json('post', '/api/register')
            ->assertStatus(422)
            ->assertJson([
                'message' => "The given data was invalid.",
                'errors' => [
                    "name" => ['The name field is required.'],
                    "email" => ['The email field is required.'],
                    "password" => ['The password field is required.'],
                ],
                
            ]);
    }

    public function testRequirePasswordConfirmation()
    {
        $payload = [
            'name' => 'John',
            'email' => 'john@safeboda.com',
            'password' => 'safeboda123',
        ];

        $this->json('post', '/api/register', $payload)
            ->assertStatus(422)
            ->assertJson([
                'message' => "The given data was invalid.",
                'errors' => [
                    "password" => ['The password confirmation does not match.']
                ],
                
            ]);
    }

    public function testPasswordAndConfirmationMustmatch()
    {
        $payload = [
            'name' => 'John',
            'email' => 'john@safeboda.com',
            'password' => 'safeboda123',
            'password_confirmation' => 'safeboda1234',
        ];

        $this->json('post', '/api/register', $payload)
            ->assertStatus(422)
            ->assertJson([
                'message' => "The given data was invalid.",
                'errors' => [
                    "password" => ['The password confirmation does not match.']
                ],
                
            ]);
    }
}
