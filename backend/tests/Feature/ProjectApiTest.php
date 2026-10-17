<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase; // Mereset DB uji otomatis setiap test berjalan

    /**
     * Test 1: Mengakses create project tanpa Bearer Token harus menghasilkan 401 Unauthorized.
     */
    public function test_unauthenticated_user_cannot_create_project(): void
    {
        $response = $this->postJson('/api/projects', [
            'title' => 'Project Tanpa Token',
            'description' => 'Mencoba membuat project tanpa otentikasi.',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test 2: Pengguna dengan token Sanctum valid dapat membuat project baru (201 Created).
     */
    public function test_authenticated_user_can_create_project(): void
    {
        // 1. Buat dummy user
        $user = User::factory()->create();

        // 2. Request create project dengan otentikasi Sanctum
        $payload = [
            'title' => 'Project Baru Sanctum',
            'description' => 'Deskripsi project yang valid.',
            'link' => 'https://github.com/kaireiras',
            'is_featured' => true,
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/projects', $payload);

        // 3. Verifikasi HTTP Status 201 Created dan data masuk ke database
        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'Project Baru Sanctum');

        $this->assertDatabaseHas('projects', [
            'title' => 'Project Baru Sanctum',
        ]);
    }
}