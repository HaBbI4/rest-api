<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Customer\CustomerResource;

class CustomerCompanyController extends CustomerController
{
    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Customer\Repositories\CustomerRepository  $customerRepository
     * @param  \Webkul\Customer\Repositories\CustomerGroupRepository  $customerGroupRepository
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository
    ) {
        parent::__construct($customerRepository);
    }

    /**
     * Register a new company customer.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|unique:customers,phone',
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string',
            'company_requisites' => 'nullable|string',
            'social_networks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'message' => $validator->messages(),
            ], 400);
        }

        try {
            // Получаем группу оптовых клиентов
            $wholesaleGroup = $this->customerGroupRepository->findOneByField('code', 'wholesale');

            if (!$wholesaleGroup) {
                // Если группы wholesale нет, используем general
                $wholesaleGroup = $this->customerGroupRepository->findOneByField('code', 'general');
            }

            Event::dispatch('customer.registration.before');

            // Создаем клиента
            $data = $request->all();
            $data['customer_group_id'] = $wholesaleGroup->id;
            $data['password'] = bcrypt(uniqid()); // Генерируем случайный пароль
            $data['is_verified'] = 0; // Неактивный статус
            $data['status'] = 0; // Неактивный статус
            $data['channel_id'] = core()->getCurrentChannel()->id;

            $customer = $this->customerRepository->create($data);

            Event::dispatch('customer.registration.after', $customer);

            return response([
                'success' => true,
                'message' => trans('rest-api::app.shop.customer.accounts.company-registration-successful'),
                'data' => new CustomerResource($customer),
            ]);
        } catch (\Exception $e) {
            return response([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
