<?php

namespace Webkul\Saas\Http\Controllers\Api;

use Illuminate\Http\Request;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Sales\Repositories\OrderRepository;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected OrderRepository $orders) {}

    /**
     * List the orders of the authenticated tenant.
     */
    public function index(Request $request)
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));

        $orders = $this->orders
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'data' => collect($orders->items())->map(fn ($order) => [
                'id' => $order->getKey(),
                'increment_id' => $order->increment_id,
                'status' => $order->status,
                'channel' => $order->channel_name,
                'grand_total' => (float) $order->grand_total,
                'currency' => $order->order_currency_code,
                'created_at' => $order->created_at?->toIso8601String(),
            ])->all(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }
}
