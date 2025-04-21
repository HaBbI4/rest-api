<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Facades\ProductImage;
use Webkul\Product\Helpers\BundleOption;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /* assign product */
        $product = $this->product ?? $this;

        /* get type instance */
        $productTypeInstance = $product->getTypeInstance();

        /* Get review helper */
        $reviewHelper = app(\Webkul\Product\Helpers\Review::class);

        /* Проверяем, является ли пользователь оптовым клиентом */
        $isWholesaleCustomer = false;
        $debugInfo = [];
        
        // Получаем токен из заголовка Authorization
        $token = $request->bearerToken();
        $debugInfo['has_token'] = !empty($token);
        
        // Если есть токен, пытаемся получить пользователя
        if ($token) {
            // Находим токен в базе данных
            $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            $debugInfo['token_found'] = !empty($tokenModel);
            
            if ($tokenModel) {
                // Получаем пользователя по токену
                $user = $tokenModel->tokenable;
                $debugInfo['user_found'] = !empty($user);
                $debugInfo['user_type'] = $user ? get_class($user) : null;
                
                // Если пользователь - клиент, проверяем его группу
                if ($user && $user instanceof \Webkul\Customer\Models\Customer) {
                    $customerGroup = app(\Webkul\Customer\Repositories\CustomerGroupRepository::class)->find($user->customer_group_id);
                    $isWholesaleCustomer = $customerGroup && $customerGroup->code === 'wholesale';
                    
                    $debugInfo['customer_id'] = $user->id;
                    $debugInfo['customer_email'] = $user->email;
                    $debugInfo['customer_group_id'] = $user->customer_group_id;
                    $debugInfo['customer_group'] = $customerGroup ? [
                        'id' => $customerGroup->id,
                        'name' => $customerGroup->name,
                        'code' => $customerGroup->code,
                    ] : null;
                    $debugInfo['is_wholesale'] = $isWholesaleCustomer;
                }
            }
        }
        
        // Также проверяем сессионную авторизацию
        $sessionAuth = auth()->guard('customer')->check();
        $debugInfo['session_auth'] = $sessionAuth;
        
        if ($sessionAuth && !$isWholesaleCustomer) {
            $customer = auth()->guard('customer')->user();
            $customerGroup = app(\Webkul\Customer\Repositories\CustomerGroupRepository::class)->find($customer->customer_group_id);
            $isWholesaleCustomer = $customerGroup && $customerGroup->code === 'wholesale';
            
            $debugInfo['session_customer_id'] = $customer->id;
            $debugInfo['session_customer_email'] = $customer->email;
            $debugInfo['session_customer_group_id'] = $customer->customer_group_id;
            $debugInfo['session_customer_group'] = $customerGroup ? [
                'id' => $customerGroup->id,
                'name' => $customerGroup->name,
                'code' => $customerGroup->code,
            ] : null;
            $debugInfo['session_is_wholesale'] = $isWholesaleCustomer;
        }

        /* generating resource */
        $resource = [
            /* product's information */
            'id'                 => $product->id,
            'sku'                => $product->sku,
            'type'               => $product->type,
            'name'               => $product->name,
            'url_key'            => $product->url_key,
            'checkout_without_cart' => (bool) $this->checkout_without_cart,
            'price'              => core()->convertPrice($productTypeInstance->getMinimalPrice()),
            'formatted_price'    => core()->currency($productTypeInstance->getMinimalPrice()),
            'short_description'  => $product->short_description,
            'description'        => $product->description,
            'images'             => ProductImageResource::collection($product->images),
            'videos'             => ProductVideoResource::collection($product->videos),
            'base_image'         => ProductImage::getProductBaseImage($product),
            'created_at'         => $product->created_at,
            'updated_at'         => $product->updated_at,

            /* product's reviews */
            'reviews' => [
                'total'          => $total = $reviewHelper->getTotalReviews($product),
                'total_rating'   => $total ? $reviewHelper->getTotalRating($product) : 0,
                'average_rating' => $total ? $reviewHelper->getAverageRating($product) : 0,
                'percentage'     => $total ? json_encode($reviewHelper->getPercentageRating($product)) : [],
            ],

            /* product's checks */
            'in_stock'              => $product->haveSufficientQuantity(1),
            'is_saved'              => false,
            'is_item_in_cart'       => \Cart::getCart(),
            'show_quantity_changer' => $this->when(
                $product->type !== 'grouped',
                $product->getTypeInstance()->showQuantityBox()
            ),
        ];

        /* Добавляем цены для оптовых клиентов, если пользователь из оптовой группы */
        if ($isWholesaleCustomer) {
            $customerGroupPrices = $this->getCustomerGroupPrices($product);
            $resource['customer_group_prices'] = $customerGroupPrices['prices'];
        }
        
        /* Добавляем отладочную информацию в режиме отладки */
        if (config('app.debug')) {
            $resource['auth_debug'] = $debugInfo;
            
            // В режиме отладки всегда показываем цены для групп клиентов
            if (!$isWholesaleCustomer) {
                $customerGroupPrices = $this->getCustomerGroupPrices($product);
                $resource['debug_customer_group_prices'] = $customerGroupPrices['prices'];
                $resource['debug_prices_info'] = $customerGroupPrices['debug_info'];
            }
        }

        /* product's extra information */
        $resource = array_merge($resource, $this->allProductExtraInfo());

        /* special price cases */
        $resource = array_merge($resource, $this->specialPriceInfo());

        /* super attributes */
        if ($productTypeInstance->isComposite()) {
            $resource['super_attributes'] = AttributeResource::collection($product->super_attributes);
        }

        return $resource;
    }

    /**
     * Get special price information.
     *
     * @return array
     */
    private function specialPriceInfo()
    {
        $product = $this->product ?? $this;

        $productTypeInstance = $product->getTypeInstance();

        return [
            'special_price'           => $this->when(
                $productTypeInstance->haveDiscount(),
                core()->convertPrice($productTypeInstance->getMinimalPrice())
            ),
            'formatted_special_price' => $this->when(
                $productTypeInstance->haveDiscount(),
                core()->currency($productTypeInstance->getMinimalPrice())
            ),
            'regular_price'           => $this->when(
                $productTypeInstance->haveDiscount(),
                data_get($productTypeInstance->getProductPrices(), 'regular_price.price')
            ),
            'formatted_regular_price' => $this->when(
                $productTypeInstance->haveDiscount(),
                data_get($productTypeInstance->getProductPrices(), 'regular_price.formated_price')
            ),
        ];
    }

    /**
     * Get all product's extra information.
     *
     * @return array
     */
    private function allProductExtraInfo()
    {
        $product = $this->product ?? $this;

        $productTypeInstance = $product->getTypeInstance();

        return [
            /* grouped product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Grouped,
                $product->type == 'grouped'
                    ? $this->getGroupedProductInfo($product)
                    : null
            ),

            /* bundle product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Bundle,
                $product->type == 'bundle'
                    ? $this->getBundleProductInfo($product)
                    : null
            ),

            /* configurable product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Configurable,
                $product->type == 'configurable'
                    ? $this->getConfigurableProductInfo($product)
                    : null
            ),

            /* downloadable product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Downloadable,
                $product->type == 'downloadable'
                    ? $this->getDownloadableProductInfo($product)
                    : null
            ),
        ];
    }

    /**
     * Get grouped product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getGroupedProductInfo($product)
    {
        return [
            'grouped_products' => $product->grouped_products->map(function ($groupedProduct) {
                $associatedProduct = $groupedProduct->associated_product;

                $data = $associatedProduct->toArray();

                return array_merge($data, [
                    'qty'                   => $groupedProduct->qty,
                    'isSaleable'            => $associatedProduct->getTypeInstance()->isSaleable(),
                    'formatted_price'       => $associatedProduct->getTypeInstance()->getPriceHtml(),
                    'show_quantity_changer' => $associatedProduct->getTypeInstance()->showQuantityBox(),
                ]);
            }),
        ];
    }

    /**
     * Get bundle product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getBundleProductInfo($product)
    {
        return [
            'bundle_options' => app(BundleOption::class)->getBundleConfig($product),
        ];
    }

    /**
     * Get configurable product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getConfigurableProductInfo($product)
    {
        return [
            'variants' => $product->variants,
        ];
    }

    /**
     * Get downloadable product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getDownloadableProductInfo($product)
    {
        return [
            'downloadable_links' => $product->downloadable_links->map(function ($downloadableLink) {
                $data = $downloadableLink->toArray();

                if (isset($data['sample_file'])) {
                    $data['price'] = core()->currency($downloadableLink->price);
                    $data['sample_download_url'] = route('shop.downloadable.download_sample', ['type' => 'link', 'id' => $downloadableLink['id']]);
                }

                return $data;
            }),

            'downloadable_samples' => $product->downloadable_samples->map(function ($downloadableSample) {
                $sample = $downloadableSample->toArray();
                $data = $sample;
                $data['download_url'] = route('shop.downloadable.download_sample', ['type' => 'sample', 'id' => $sample['id']]);

                return $data;
            }),
        ];
    }

    /**
     * Получить цены для групп клиентов
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getCustomerGroupPrices($product)
    {
        $prices = [];
        $debug = [];

        try {
            // Добавляем информацию о продукте
            $debug['product_id'] = $product->id;
            $debug['product_type'] = $product->type;
            
            // Добавляем информацию об авторизации
            $debug['auth_check'] = auth()->guard('customer')->check();
            $debug['user_info'] = auth()->guard('customer')->check() ? [
                'id' => auth()->guard('customer')->user()->id,
                'email' => auth()->guard('customer')->user()->email,
                'customer_group_id' => auth()->guard('customer')->user()->customer_group_id,
            ] : null;
            
            // Добавляем информацию о группе клиентов
            if (auth()->guard('customer')->check()) {
                $customer = auth()->guard('customer')->user();
                $customerGroup = app(\Webkul\Customer\Repositories\CustomerGroupRepository::class)->find($customer->customer_group_id);
                $debug['customer_group'] = $customerGroup ? [
                    'id' => $customerGroup->id,
                    'name' => $customerGroup->name,
                    'code' => $customerGroup->code,
                ] : null;
            }
            
            // Проверяем наличие репозитория цен для групп клиентов
            $debug['repository_exists'] = class_exists(\Webkul\Product\Repositories\ProductCustomerGroupPriceRepository::class);
            
            // Если репозиторий существует, пытаемся получить цены
            if (class_exists(\Webkul\Product\Repositories\ProductCustomerGroupPriceRepository::class)) {
                $customerGroupPriceRepository = app(\Webkul\Product\Repositories\ProductCustomerGroupPriceRepository::class);
                $customerGroupPrices = $customerGroupPriceRepository->findWhere(['product_id' => $product->id]);
                
                $debug['customer_group_prices_count'] = $customerGroupPrices->count();
                
                if ($customerGroupPrices->count() > 0) {
                    foreach ($customerGroupPrices as $price) {
                        $customerGroup = app(\Webkul\Customer\Repositories\CustomerGroupRepository::class)->find($price->customer_group_id);
                        
                        if (!$customerGroup) {
                            continue;
                        }
                        
                        $prices[] = [
                            'customer_group_id' => $price->customer_group_id,
                            'customer_group_code' => $customerGroup->code,
                            'customer_group_name' => $customerGroup->name,
                            'qty' => $price->qty,
                            'value_type' => $price->value_type,
                            'value' => $price->value,
                            'calculated_price' => $this->calculatePrice($product, $price),
                            'formatted_calculated_price' => core()->currency($this->calculatePrice($product, $price)),
                        ];
                    }
                } else {
                    // Если цен для групп клиентов нет, добавляем стандартную цену
                    $customerGroups = app(\Webkul\Customer\Repositories\CustomerGroupRepository::class)->all();
                    
                    foreach ($customerGroups as $customerGroup) {
                        $prices[] = [
                            'customer_group_id' => $customerGroup->id,
                            'customer_group_code' => $customerGroup->code,
                            'customer_group_name' => $customerGroup->name,
                            'qty' => 1,
                            'value_type' => 'fixed',
                            'value' => $product->getTypeInstance()->getMinimalPrice(),
                            'calculated_price' => $product->getTypeInstance()->getMinimalPrice(),
                            'formatted_calculated_price' => core()->currency($product->getTypeInstance()->getMinimalPrice()),
                        ];
                    }
                }
            } else {
                $debug['error'] = 'ProductCustomerGroupPriceRepository не существует';
                
                // Пробуем получить цены через свойство продукта
                if (property_exists($product, 'customer_group_prices') && $product->customer_group_prices) {
                    $debug['has_customer_group_prices_property'] = true;
                    $debug['customer_group_prices_count'] = count($product->customer_group_prices);
                    
                    foreach ($product->customer_group_prices as $price) {
                        $customerGroup = app(\Webkul\Customer\Repositories\CustomerGroupRepository::class)->find($price->customer_group_id);
                        
                        if (!$customerGroup) {
                            continue;
                        }
                        
                        $prices[] = [
                            'customer_group_id' => $price->customer_group_id,
                            'customer_group_code' => $customerGroup->code,
                            'customer_group_name' => $customerGroup->name,
                            'qty' => $price->qty,
                            'value_type' => $price->value_type,
                            'value' => $price->value,
                            'calculated_price' => $this->calculatePrice($product, $price),
                            'formatted_calculated_price' => core()->currency($this->calculatePrice($product, $price)),
                        ];
                    }
                } else {
                    $debug['has_customer_group_prices_property'] = false;
                }
            }
        } catch (\Exception $e) {
            $debug['error'] = $e->getMessage();
            $debug['trace'] = $e->getTraceAsString();
        }
        
        // Всегда возвращаем отладочную информацию
        return [
            'prices' => $prices,
            'debug_info' => $debug
        ];
    }

    /**
     * Рассчитать цену с учетом скидки для группы клиентов
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @param  object  $price
     * @return float
     */
    private function calculatePrice($product, $price)
    {
        $productTypeInstance = $product->getTypeInstance();
        $productPrice = $productTypeInstance->getMinimalPrice();

        if ($price->value_type === 'discount') {
            return $productPrice - ($productPrice * $price->value / 100);
        }

        return $price->value;
    }
}
