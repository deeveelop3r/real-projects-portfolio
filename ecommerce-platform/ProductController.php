<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Get all products with pagination
    public function index(Request $request)
    {
        $query = Product::active()->inStock();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        // Price range filter
        if ($request->has('min_price') && $request->has('max_price')) {
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    // Get single product
    public function show(Product $product)
    {
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    // Get categories
    public function categories()
    {
        $categories = Product::distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    // Get featured products
    public function featured()
    {
        $products = Product::active()
            ->inStock()
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    // Get related products
    public function related(Product $product, $limit = 4)
    {
        $related = Product::active()
            ->inStock()
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $related,
        ]);
    }
}
