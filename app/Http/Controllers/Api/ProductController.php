<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Retrieve paginated products.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = (int) ($validated['limit'] ?? 10);
        $products = Product::orderByDesc('id')->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
        ], 200);
    }

    /**
     * Create a new product using an Eloquent model object.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product = new Product();
        $product->name = trim($validated['name']);
        $product->sku = trim($validated['sku']);
        $product->price = $validated['price'];
        $product->stock = (int) $validated['stock'];
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    /**
     * Sell a specified quantity of a product safely within a DB transaction.
     */
    public function sell(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quantity = (int) $validated['quantity'];

        $result = DB::transaction(function () use ($id, $quantity): array {
            // Acquire a row-level lock so concurrent requests cannot read stale stock.
            $product = Product::where('id', $id)->lockForUpdate()->first();

            if (! $product) {
                return [
                    'status' => 404,
                    'body' => [
                        'success' => false,
                        'message' => 'Product not found.',
                    ],
                ];
            }

            if ($product->stock < $quantity) {
                return [
                    'status' => 422,
                    'body' => [
                        'success' => false,
                        'message' => "Insufficient stock. Only {$product->stock} unit(s) available.",
                        'errors' => [
                            'quantity' => [
                                "Requested quantity ({$quantity}) exceeds available stock ({$product->stock}).",
                            ],
                        ],
                        'available_stock' => $product->stock,
                    ],
                ];
            }

            $product->stock = $product->stock - $quantity;
            $product->save();

            return [
                'status' => 200,
                'body' => [
                    'success' => true,
                    'message' => "Sold {$quantity} unit(s) of {$product->name}.",
                    'data' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'stock' => $product->stock,
                        'sold_quantity' => $quantity,
                    ],
                ],
            ];
        });

        return response()->json($result['body'], $result['status']);
    }
}
