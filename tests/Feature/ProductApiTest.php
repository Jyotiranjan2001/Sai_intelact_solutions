<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Inventory & Stock Management', false);
    }

    public function test_can_create_product(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Mechanical Keyboard',
            'sku' => 'MK-001',
            'price' => 79.99,
            'stock' => 25,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Mechanical Keyboard',
                    'sku' => 'MK-001',
                    'price' => '79.99',
                    'stock' => 25,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'MK-001',
            'stock' => 25,
        ]);
    }

    public function test_create_product_validates_required_unique_and_non_negative_fields(): void
    {
        $existing = new Product();
        $existing->name = 'Mouse';
        $existing->sku = 'SKU-DUP';
        $existing->price = 19.50;
        $existing->stock = 10;
        $existing->save();

        // Duplicate SKU, negative price, negative stock
        $response = $this->postJson('/api/products', [
            'name' => '',
            'sku' => 'SKU-DUP',
            'price' => -5,
            'stock' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'sku', 'price', 'stock']);
    }

    public function test_can_retrieve_products_with_pagination(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $product = new Product();
            $product->name = "Product {$i}";
            $product->sku = "SKU-{$i}";
            $product->price = 10 * $i;
            $product->stock = 5 * $i;
            $product->save();
        }

        $response = $this->getJson('/api/products?page=2&limit=2');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_sell_product_and_deduct_stock(): void
    {
        $product = new Product();
        $product->name = 'USB-C Hub';
        $product->sku = 'HUB-100';
        $product->price = 35.00;
        $product->stock = 12;
        $product->save();

        $response = $this->postJson("/api/products/{$product->id}/sell", [
            'quantity' => 4,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'stock' => 8,
                    'sold_quantity' => 4,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);
    }

    public function test_sell_rejects_zero_or_negative_quantity(): void
    {
        $product = new Product();
        $product->name = 'Monitor Stand';
        $product->sku = 'MS-100';
        $product->price = 49.00;
        $product->stock = 10;
        $product->save();

        $this->postJson("/api/products/{$product->id}/sell", ['quantity' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        $this->postJson("/api/products/{$product->id}/sell", ['quantity' => -3])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);
    }

    public function test_sell_rejects_when_quantity_exceeds_available_stock(): void
    {
        $product = new Product();
        $product->name = 'Webcam';
        $product->sku = 'WC-200';
        $product->price = 60.00;
        $product->stock = 3;
        $product->save();

        $response = $this->postJson("/api/products/{$product->id}/sell", [
            'quantity' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'available_stock' => 3,
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 3,
        ]);
    }

    public function test_sell_returns_404_for_missing_product(): void
    {
        $this->postJson('/api/products/99999/sell', ['quantity' => 1])
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    }
}
