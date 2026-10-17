<?php

namespace App\Http\Controllers;

use App\Models\DocumentItem;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('supplier')
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%$t%")->orWhere('reference', 'like', "%$t%")))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->boolean('low'), fn ($q) => $q->lowStock())
            ->orderBy('type')->orderBy('name')
            ->paginate(25)->withQueryString();

        $stockValue = Product::where('type', 'produit')->where('stock_qty', '>', 0)->selectRaw('COALESCE(SUM(stock_qty * purchase_price),0) as v')->value('v');
        $lowCount = Product::lowStock()->where('is_active', true)->count();
        $categories = Product::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return view('products.index', compact('products', 'stockValue', 'lowCount', 'categories'));
    }

    public function create()
    {
        return view('products.form', $this->formData(new Product(['type' => 'produit', 'unit' => 'pièce', 'is_active' => true, 'alert_qty' => 2])));
    }

    public function store(Request $request)
    {
        $product = Product::create($this->validated($request));

        return redirect()->route('products.show', $product)->with('success', 'Article créé.');
    }

    public function show(Product $product)
    {
        $sales = DocumentItem::with('document.client')->where('product_id', $product->id)
            ->where('document_type', Invoice::class)->latest()->limit(15)->get();
        $purchases = PurchaseItem::with('purchase.supplier')->where('product_id', $product->id)->latest()->limit(15)->get();

        return view('products.show', compact('product', 'sales', 'purchases'));
    }

    public function edit(Product $product)
    {
        return view('products.form', $this->formData($product));
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product));

        return redirect()->route('products.show', $product)->with('success', 'Article mis à jour.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Article archivé.');
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'categories' => collect(\App\Support\Options::PRODUCT_CATEGORIES)
                ->merge(Product::whereNotNull('category')->distinct()->pluck('category'))->unique()->sort()->values(),
        ];
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['produit', 'service'])],
            'reference' => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($product)],
            'name' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'stock_qty' => ['nullable', 'integer'],
            'alert_qty' => ['nullable', 'integer', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['purchase_price'] = $data['purchase_price'] ?? 0;
        $data['stock_qty'] = $data['type'] === 'service' ? 0 : ($data['stock_qty'] ?? 0);
        $data['alert_qty'] = $data['type'] === 'service' ? 0 : ($data['alert_qty'] ?? 0);

        return $data;
    }
}
