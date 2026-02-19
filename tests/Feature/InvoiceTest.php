<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_list_invoices(): void
    {
        $user = User::factory()->create();
        Invoice::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('invoices.index'));

        $response->assertStatus(200);
        $response->assertViewHas('invoices');
    }

    public function test_it_can_create_invoice(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($user)->post(route('invoices.store'), [
            'client_id' => $client->id,
            'invoice_number' => 'INV-TEST-001',
            'issue_date' => '2023-01-01',
            'due_date' => '2023-01-15',
            'status' => 'draft',
            'notes' => 'Test Invoice',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-TEST-001',
            'status' => 'draft',
        ]);
    }
}
