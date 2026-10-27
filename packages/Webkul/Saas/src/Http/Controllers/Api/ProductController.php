<?php

namespace Webkul\Saas\Http\Controllers\Api;

use Illuminate\Http\Request;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Saas\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected ProductRepository $products) {}

    /**
     * List the products of the authenticated tenant.
     */
    public function index(Request $request)
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $products = $this->products->paginate($perPage);

        return response()->json([
            'data' => collect($products->items())->map(fn ($product) => [
                'id' => $product->getKey(),
                'sku' => $product->sku,
                'type' => $product->type,
                'status' => (bool) $product->status,
                'created_at' => $product->created_at?->toIso8601String(),
                'updated_at' => $product->updated_at?->toIso8601String(),
            ])->all(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }
}
