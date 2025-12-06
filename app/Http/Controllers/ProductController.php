<?php

/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Http\Controllers;

use App\Factory\ProductFactory;
use App\Filters\ProductFilters;
use App\Http\Requests\Product\BulkProductRequest;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\DestroyProductRequest;
use App\Http\Requests\Product\EditProductRequest;
use App\Http\Requests\Product\ShowProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\UploadProductRequest;
use App\Models\Account;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Transformers\ProductTransformer;
use App\Utils\Traits\MakesHash;
use App\Utils\Traits\SavesDocuments;
use Illuminate\Http\Response;

class ProductController extends BaseController
{
    use MakesHash;
    use SavesDocuments;

    protected $entity_type = Product::class;

    protected $entity_transformer = ProductTransformer::class;

    protected $product_repo;

    /**
     * ProductController constructor.
     * @param ProductRepository $product_repo
     */
    public function __construct(ProductRepository $product_repo)
    {
        parent::__construct();

        $this->product_repo = $product_repo;
    }

    public function index(ProductFilters $filters)
    {
        $products = Product::filter($filters);

        return $this->listResponse($products);
    }

    public function create(CreateProductRequest $request)
    {

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $product = ProductFactory::create($user->company()->id, auth()->user()->id);

        return $this->itemResponse($product);
    }

    public function store(StoreProductRequest $request)
    {

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $product = $this->product_repo->save($request->all(), ProductFactory::create($user->company()->id, auth()->user()->id));

        return $this->itemResponse($product);
    }

 
    public function show(ShowProductRequest $request, Product $product)
    {
        return $this->itemResponse($product);
    }

    
    public function edit(EditProductRequest $request, Product $product)
    {
        return $this->itemResponse($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        if ($request->entityIsDeleted($product)) {
            return $request->disallowUpdate();
        }

        $product = $this->product_repo->save($request->all(), $product);

        return $this->itemResponse($product);
    }

    public function destroy(DestroyProductRequest $request, Product $product)
    {
        $this->product_repo->delete($product);

        return $this->itemResponse($product->fresh());
    }

    public function bulk(BulkProductRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $action = $request->input('action');

        $ids = $request->input('ids');

        $products = Product::withTrashed()->whereIn('id', $ids);

        if ($action == 'set_tax_id') {

            $tax_id = $request->input('tax_id');

            $products->update(['tax_id' => $tax_id]);

            return $this->listResponse(Product::withTrashed()->whereIn('id', $ids));
        }

        $products->cursor()->each(function ($product, $key) use ($action, $user) {
            if ($user->can('edit', $product)) {
                $this->product_repo->{$action}($product);
            }
        });

        return $this->listResponse(Product::withTrashed()->whereIn('id', $ids));
    }

    public function upload(UploadProductRequest $request, Product $product)
    {
        if (! $this->checkFeature(Account::FEATURE_DOCUMENTS)) {
            return $this->featureFailure();
        }

        if ($request->has('documents')) {
            $this->saveDocuments($request->file('documents'), $product, $request->input('is_public', true));
        }

        return $this->itemResponse($product->fresh());
    }
}
