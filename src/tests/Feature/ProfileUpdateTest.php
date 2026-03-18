<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_avatar_is_preserved_when_profile_update_validation_fails(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'avatar' => UploadedFile::fake()->image('avatar.png'),
            'name' => str_repeat('a', 21),
            'postal_code' => '123-4567',
            'address' => 'Tokyo',
            'building' => 'Building',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors(['name']);
        $response->assertSessionHasInput('avatar_temp_path', function ($path) {
            Storage::disk('public')->assertExists($path);

            return str_starts_with($path, 'avatars/tmp/');
        });
    }
}
