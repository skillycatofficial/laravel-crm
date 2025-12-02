<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\QuoteResource;
use Illuminate\Http\Request;
use Webkul\Quote\Repositories\QuoteRepository;

class QuoteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  QuoteRepository  $quoteRepository
     * @return void
     */
    public function __construct(protected QuoteRepository $quoteRepository)
    {
    }

    /**
     * Display a listing of quotes.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $userId = $request->input('user_id');
        $personId = $request->input('person_id');

        $query = $this->quoteRepository->with(['user', 'person', 'items']);

        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($personId) {
            $query->where('person_id', $personId);
        }

        // Order by
        $query->orderBy('created_at', 'desc');

        $quotes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => QuoteResource::collection($quotes),
            'meta' => [
                'current_page' => $quotes->currentPage(),
                'from' => $quotes->firstItem(),
                'last_page' => $quotes->lastPage(),
                'per_page' => $quotes->perPage(),
                'to' => $quotes->lastItem(),
                'total' => $quotes->total(),
            ],
        ], 200);
    }

    /**
     * Display the specified quote.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $quote = $this->quoteRepository->with(['user', 'person', 'items', 'leads'])->find($id);

        if (! $quote) {
            return response()->json([
                'success' => false,
                'message' => 'Quote not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new QuoteResource($quote),
        ], 200);
    }

    /**
     * Store a newly created quote.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'person_id' => 'required|exists:persons,id',
            'billing_address' => 'nullable|array',
            'shipping_address' => 'nullable|array',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'adjustment_amount' => 'nullable|numeric',
            'sub_total' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'expired_at' => 'nullable|date',
            'items' => 'nullable|array',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $quote = $this->quoteRepository->create($validated);

        // Create quote items
        if (!empty($items)) {
            foreach ($items as $item) {
                $quote->items()->create($item);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Quote created successfully',
            'data' => new QuoteResource($quote->load(['user', 'person', 'items'])),
        ], 201);
    }

    /**
     * Update the specified quote.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $quote = $this->quoteRepository->find($id);

        if (! $quote) {
            return response()->json([
                'success' => false,
                'message' => 'Quote not found',
            ], 404);
        }

        $validated = $request->validate([
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'sometimes|required|exists:users,id',
            'person_id' => 'sometimes|required|exists:persons,id',
            'billing_address' => 'nullable|array',
            'shipping_address' => 'nullable|array',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'adjustment_amount' => 'nullable|numeric',
            'sub_total' => 'sometimes|required|numeric|min:0',
            'grand_total' => 'sometimes|required|numeric|min:0',
            'expired_at' => 'nullable|date',
            'items' => 'nullable|array',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        $items = $validated['items'] ?? null;
        unset($validated['items']);

        $quote = $this->quoteRepository->update($validated, $id);

        // Update quote items if provided
        if ($items !== null) {
            // Delete existing items
            $quote->items()->delete();
            
            // Create new items
            foreach ($items as $item) {
                $quote->items()->create($item);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Quote updated successfully',
            'data' => new QuoteResource($quote->load(['user', 'person', 'items'])),
        ], 200);
    }

    /**
     * Remove the specified quote.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $quote = $this->quoteRepository->find($id);

        if (! $quote) {
            return response()->json([
                'success' => false,
                'message' => 'Quote not found',
            ], 404);
        }

        $this->quoteRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Quote deleted successfully',
        ], 200);
    }
}

