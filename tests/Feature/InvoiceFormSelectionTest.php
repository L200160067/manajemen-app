<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Volt\Volt;
use App\Models\User;
use App\Models\Client;
use App\Models\Product;

class InvoiceFormSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_client_selection_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create 3 clients
        $clients = Client::factory()->count(3)->create();
        $product = Product::factory()->create();

        // Use the first client
        $firstClientId = $clients->first()->id;

        $component = Volt::test('invoices.invoice-form')
            ->set('client_id', (string) $firstClientId)
            ->set('invoice_number', 'INV-001')
            ->set('issue_date', '2023-01-01')
            ->set('due_date', '2023-01-15')
            ->set('status', 'draft')
            ->set('items.0.product_id', $product->id)
            ->set('items.0.quantity', 1)
            ->set('items.0.unit_price', 100)
            ->call('save')
            ->assertHasNoErrors(['client_id']);
    }
}
