<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Volt\Volt;
use App\Models\User;

class ClientFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_form_renders()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/clients/create');
        $response->assertStatus(200);
    }

    public function test_client_form_edit_renders()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $client = \App\Models\Client::factory()->create();
        $response = $this->get('/clients/' . $client->id . '/edit');
        
        $response->assertStatus(200);
    }

    public function test_client_form_saves()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Volt::test('clients.client-form')
            ->set('name', 'Test Client')
            ->set('email', 'test@test.com')
            ->call('save')
            ->assertRedirect(route('clients.index', absolute: false));
    }
}
