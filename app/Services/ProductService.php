<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImageGallery;
use App\Models\ProductVariation;
use App\Models\ProductVariationValue;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    /**
     * Create a product
     *
     * @param  Request  $request
     * @return Product
     */
    public function createProduct($request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products',
            'code' => 'required|string|max:50|unique:products,code',
            'brand' => 'required|exists:brands,id',
            'category' => 'required|exists:categories,id',
            'unit' => 'required|exists:units,id',
            'low_quantity_alert' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_date_range' => 'nullable|string',
            'has_variation' => 'nullable|boolean',
        ]);
        $product = new Product();
        $product->created_by = auth()->guard('admin')->user()->id;
        $product->name = $validated['name'];
        $product->code = $validated['code'];
        $product->brand_id = $validated['brand'];
        $product->category_id = $validated['category'];
        $product->unit_id = $validated['unit'];
        $product->low_quantity_alert = $validated['low_quantity_alert'];
        $product->description = $validated['description'];
        $product->discount = $validated['discount'];
        $product->discount_type = $validated['discount_type'];
        $product->discount_date_range = $validated['discount_date_range'];
        $product->has_variation = isset($validated['has_variation']) ? (bool) $validated['has_variation'] : false;
        $product->image = $request->image_url ?? null;
        $product->save();

        if ($request->gallery_urls) {
            $galleryImages = [];
            foreach ($request->gallery_urls as $imageUrl) {
                $galleryImages[] = [
                    'product_id' => $product->id,
                    'image' => $imageUrl,
                ];
            }
            ProductImageGallery::insert($galleryImages);
        }

        return $product;
    }

    /**
     * Create product variations and their associated values
     *
     * @param  Request  $request
     * @return void
     */
    public function createProductVariations($request, Product $product)
    {
        if (! $product->has_variation) {
            return;
        }

        $attributes = $request->attributes_data ?? [];
        $attributeValues = $request->attribute_values ?? [];
        $skus = $request->skus ?? [];

        if (empty($skus)) {
            throw new \Exception('SKUs cannot be empty.');
        }

        if (empty($attributes) || empty($attributeValues) || count($attributes) !== count($skus) || count($attributeValues) !== count($skus)) {
            throw new \Exception('Mismatch between SKUs and attributes/attribute values.');
        }

        foreach ($skus as $key => $sku) {
            $formattedAttributes = [];
            $formattedAttributeValues = [];
            foreach ($attributes[$key] ?? [] as $attrIndex => $attrId) {
                $formattedAttributes[$attrIndex] = [$attrId];
                $formattedAttributeValues[$attrIndex] = $attributeValues[$key][$attrIndex] ?? [];
            }

            $this->createProductVariation($product, $sku, $formattedAttributes, $formattedAttributeValues);
        }
    }

    /**
     * Create a product variation and its values
     *
     * @param  string  $sku
     * @param  array  $attributes
     * @param  array  $attributeValues
     * @return void
     */
    private function createProductVariation(Product $product, $sku, $attributes, $attributeValues)
    {
        if (empty($sku)) {
            throw new \Exception('SKU cannot be empty.');
        }

        $variation = new ProductVariation();
        $variation->product_id = $product->id;
        $variation->sku = $sku;
        $variation->save();

        if (! empty($attributes) && ! empty($attributeValues)) {
            $this->insertVariationValues($variation, $attributes, $attributeValues);
        }
    }

    /**
     * Insert variation values into the product_variation_values table
     *
     * @param  array  $attributes
     * @param  array  $attributeValues
     * @return void
     */
    private function insertVariationValues(ProductVariation $variation, $attributes, $attributeValues)
    {
        if (empty($attributes) || empty($attributeValues)) {
            throw new \Exception('Attributes or attribute values are empty');
        }

        $variationValues = [];
        $validKeys = array_intersect(array_keys($attributes), array_keys($attributeValues));

        foreach ($validKeys as $key) {
            $attributeIds = $attributes[$key] ?? [];
            $attributeValueIds = $attributeValues[$key] ?? [];

            if (empty($attributeIds) || empty($attributeValueIds)) {
                Log::warning("Skipping variant key {$key}: Empty attributes or attribute values");

                continue;
            }

            Log::debug("Processing variant key: {$key}", [
                'attributeIds' => $attributeIds,
                'attributeValueIds' => $attributeValueIds,
            ]);

            $attributeId = reset($attributeIds);

            if (! $attributeId) {
                Log::warning("No valid attribute ID found for variant key {$key}");

                continue;
            }

            foreach ($attributeValueIds as $index => $attributeValueId) {
                if (! $attributeValueId) {
                    Log::warning("Invalid attribute value at index {$index} for variant key {$key}");

                    continue;
                }

                Log::info("Preparing variation value: Variant ID {$variation->id}, Attribute ID {$attributeId}, Attribute Value ID {$attributeValueId}");

                $variationValues[] = [
                    'variant_id' => $variation->id,
                    'attribute_id' => $attributeId,
                    'attribute_value_id' => $attributeValueId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($variationValues)) {
            Log::warning("No variation values prepared for insertion for variation ID {$variation->id}");

            return;
        }

        ProductVariationValue::insert($variationValues);
    }

    /**
     * Update product and its variations
     *
     * @return void
     */
    public function updateProductAndVariations(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $this->updateProduct($request, $product);

            if ($request->has_variation) {
                $this->clearOldProductVariations($product);
                $this->createProductVariations($request, $product);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update the product's basic information
     *
     * @param  Request  $request
     * @return void
     */
    public function updateProduct($request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name,'.$product->id,
            'code' => 'required|string|max:50|unique:products,code,'.$product->id,
            'brand' => 'required|exists:brands,id',
            'category' => 'required|exists:categories,id',
            'unit' => 'required|exists:units,id',
            'low_quantity_alert' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_date_range' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'has_variation' => 'nullable|boolean',
        ]);

        $product->name = $validated['name'];
        $product->code = $validated['code'];
        $product->brand_id = $validated['brand'];
        $product->category_id = $validated['category'];
        $product->unit_id = $validated['unit'];
        $product->low_quantity_alert = $validated['low_quantity_alert'];
        $product->description = $validated['description'];
        $product->discount = $validated['discount'];
        $product->discount_type = $validated['discount_type'];
        $product->discount_date_range = $validated['discount_date_range'];
        $product->has_variation = isset($validated['has_variation']) ? (bool) $validated['has_variation'] : false;
        $product->image = $request->image_url;
        $product->save();

        if ($request->gallery_urls) {
            // Delete existing gallery images
            ProductImageGallery::where('product_id', $product->id)->delete();

            // Insert new gallery images
            $galleryImages = [];
            foreach ($request->gallery_urls as $imageUrl) {
                $galleryImages[] = [
                    'product_id' => $product->id,
                    'url' => $imageUrl,
                ];
            }
            ProductImageGallery::insert($galleryImages);
        }
    }

    /**
     * Clear old product variations and their associated values
     *
     * @return void
     */
    public function clearOldProductVariations(Product $product)
    {
        $product->variations->each(function ($variation) {
            $variation->variationValues()->delete();
            $variation->delete();
        });
    }

    /**
     * Update product variations and their associated values
     *
     * @param  Request  $request
     * @param  Product  $product
     * @return void
     */
    public function updateProductVariations($request, $product)
    {
        if (! $request->has_variation) {
            return;
        }

        $attributes = $request->attributes_data ?? [];
        $attributeValues = $request->attribute_values ?? [];
        $skus = $request->skus ?? [];

        if (empty($skus)) {
            throw new \Exception('SKUs cannot be empty.');
        }

        if (empty($attributes) || empty($attributeValues)) {
            throw new \Exception('Attributes or attribute values cannot be empty when variations are present.');
        }

        $existingVariations = $product->productVariations;
        foreach ($skus as $key => $sku) {
            if (! isset($attributes[$key]) || ! isset($attributeValues[$key])) {
                Log::warning("Skipping variation at key {$key} due to missing attributes or attribute values.");

                continue;
            }

            $formattedAttributes = [];
            $formattedAttributeValues = [];
            foreach ($attributes[$key] ?? [] as $attrIndex => $attrId) {
                $formattedAttributes[$attrIndex] = [$attrId];
                $formattedAttributeValues[$attrIndex] = $attributeValues[$key][$attrIndex] ?? [];
            }

            $variation = ProductVariation::where('product_id', $product->id)
                ->where('sku', $sku)
                ->first();

            if ($variation) {
                $variation->productVariationValues()->delete();
                $variation->sku = $sku;
                $variation->save();
            } else {
                $variation = new ProductVariation();
                $variation->product_id = $product->id;
                $variation->sku = $sku;
                $variation->save();
            }

            $this->insertVariationValues($variation, $formattedAttributes, $formattedAttributeValues);
        }

        $submittedSkus = collect($skus);
        $existingVariations->each(function ($existing) use ($submittedSkus) {
            if (! $submittedSkus->contains($existing->sku)) {
                $existing->productVariationValues()->delete();
                $existing->delete();
            }
        });
    }

    public function processChunkNow(array $chunk, array $header, int $adminId): void
    {
        $expectedColumnCount = count($header);

        foreach ($chunk as $row) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Trim and validate row column count
            $row = array_map('trim', $row);
            if (count($row) !== $expectedColumnCount) {
                continue;
            }

            $data = array_combine($header, $row);
            if (! $data || empty($data['name']) || empty($data['code'])) {
                continue;
            }

            // Skip duplicates
            if (Product::where('code', $data['code'])->orWhere('name', $data['name'])->exists()) {
                continue;
            }

            // Handle category
            $categoryId = null;
            if (! empty($data['category_name'])) {
                $category = Category::firstOrCreate(['name' => $data['category_name']]);
                $categoryId = $category->id;
            }

            // Handle brand
            $brandId = null;
            if (! empty($data['brand_name'])) {
                $brand = Brand::firstOrCreate(['name' => $data['brand_name']]);
                $brandId = $brand->id;
            }

            // Handle unit
            $unitId = null;
            if (! empty($data['unit_name'])) {
                $unit = Unit::firstOrCreate(['name' => $data['unit_name']]);
                $unitId = $unit->id;
            }

            Product::create([
                'created_by' => $adminId,
                'name' => $data['name'],
                'code' => $data['code'],
                'brand_id' => $brandId ?? $data['brand_id'] ?? null,
                'category_id' => $categoryId ?? $data['category_id'] ?? null,
                'unit_id' => $unitId ?? $data['unit_id'] ?? null,
                'low_quantity_alert' => $data['low_quantity_alert'] ?? null,
                'description' => $data['description'] ?? null,
                'discount' => $data['discount'] ?? null,
                'discount_type' => $data['discount_type'] ?? null,
                'image' => $data['image_url'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
