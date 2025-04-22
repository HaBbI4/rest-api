<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\AttributeResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\AttributeOptionResource;

class AttributeController extends CatalogController
{
    /**
     * AttributeOptionRepository object
     *
     * @var \Webkul\Attribute\Repositories\AttributeOptionRepository
     */
    protected $attributeOptionRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Attribute\Repositories\AttributeOptionRepository  $attributeOptionRepository
     * @return void
     */
    public function __construct(AttributeOptionRepository $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;

        parent::__construct();
    }

    /**
     * Is resource authorized.
     */
    public function isAuthorized(): bool
    {
        return false;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return AttributeRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return AttributeResource::class;
    }

    /**
     * Get all brands.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBrands()
    {
        // Получаем атрибут "brand"
        $brandAttribute = app(AttributeRepository::class)->findOneByField('code', 'brand');

        if (! $brandAttribute) {
            return response()->json([
                'message' => trans('rest-api::app.shop.response.not-found', ['name' => 'Brand attribute']),
            ], 404);
        }

        // Получаем все опции атрибута "brand"
        $brands = $this->attributeOptionRepository->findWhere(['attribute_id' => $brandAttribute->id]);

        return response()->json([
            'data' => AttributeOptionResource::collection($brands),
        ]);
    }
}
