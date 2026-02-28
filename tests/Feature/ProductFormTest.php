<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Volt\Volt;
use App\Models\User;

class ProductFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_form_renders()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/products/create');
        
        $response->assertStatus(200);
    }

    public function test_product_form_saves()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Volt::test('products.product-form')
            ->set('name', 'Test Product')
            ->set('description', 'test description')
            ->set('current_price', '1000')
            ->call('save')
            ->assertRedirect(route('products.index', absolute: false));
    }
}
