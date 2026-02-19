<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
/*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
*/
        \App\Models\Client::factory(10)->create();
        \App\Models\Product::factory(10)->create();

        \App\Models\Invoice::factory(5)->create()->each(function ($invoice) {
            \App\Models\InvoiceItem::factory(3)->create([
                'invoice_id' => $invoice->id,
            ]);
        });
    }
}
