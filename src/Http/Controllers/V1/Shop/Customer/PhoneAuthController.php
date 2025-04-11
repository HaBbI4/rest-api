<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use App\Services\PhoneVerificationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Customer\CustomerResource;

class PhoneAuthController extends CustomerController
{
    protected $phoneVerificationService;

    /**
     * Controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository,
        PhoneVerificationServiceInterface $phoneVerificationService
    ) {
        parent::__construct($customerRepository);
        $this->phoneVerificationService = $phoneVerificationService;
    }

    /**
     * Register the customer by phone.
     */
    public function registerByPhone(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name'  => 'required',
            'phone'      => 'required|unique:customers,phone',
            'email'      => 'nullable|email|unique:customers,email',
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'message' => $validator->messages(),
            ], 400);
        }

        // Отправляем код подтверждения
        $code = $this->phoneVerificationService->sendVerificationCode($request->phone);

        // Сохраняем данные пользователя в кэше
        \Cache::put(
            'phone_registration_data_' . $request->phone,
            $request->only(['first_name', 'last_name', 'phone', 'email']),
            now()->addMinutes(10)
        );

        // Для тестирования возвращаем код в ответе (в продакшене убрать!)
        return response([
            'success' => true,
            'message' => trans('rest-api::app.shop.customer.accounts.verification-code-sent'),
        ]);
    }

    /**
     * Verify phone code and complete registration.
     */
    public function verifyPhone(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'code'  => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'message' => $validator->messages(),
            ], 400);
        }

        // Проверяем код
        if (!$this->phoneVerificationService->verifyCode($request->phone, $request->code)) {
            return response([
                'success' => false,
                'message' => trans('rest-api::app.shop.customer.accounts.invalid-verification-code'),
            ], 400);
        }

        // Получаем данные пользователя из кэша
        $data = \Cache::get('phone_registration_data_' . $request->phone);

        if (!$data) {
            return response([
                'success' => false,
                'message' => 'Registration data expired. Please try again.',
            ], 400);
        }

        // Добавляем необходимые поля для создания пользователя
        $data['password'] = bcrypt(uniqid()); // Генерируем случайный пароль
        $data['is_verified'] = 1;
        $data['channel_id'] = core()->getCurrentChannel()->id;
        $data['customer_group_id'] = $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id;

        Event::dispatch('customer.registration.before');

        // Создаем пользователя
        $customer = $this->customerRepository->create($data);

        // Создаем токен для API
        $token = $customer->createToken('customer-phone-auth', ['role:customer'])->plainTextToken;

        // Очищаем данные из кэша
        \Cache::forget('phone_verification_code_' . $request->phone);
        \Cache::forget('phone_registration_data_' . $request->phone);

        Event::dispatch('customer.registration.after', $customer);

        return response([
            'success'  => true,
            'message'  => trans('rest-api::app.shop.customer.accounts.registration-successful'),
            'customer' => new CustomerResource($customer),
            'token'    => $token,
        ]);
    }

    /**
     * Login the customer by phone.
     */
    public function loginByPhone(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'message' => $validator->messages(),
            ], 400);
        }

        // Проверяем существование пользователя с таким телефоном
        $customer = $this->customerRepository->findOneByField('phone', $request->phone);

        if (! $customer) {
            return response([
                'success' => false,
                'message' => trans('rest-api::app.shop.customer.accounts.phone-not-found'),
            ], 404);
        }

        // Отправляем код подтверждения
        $code = $this->phoneVerificationService->sendVerificationCode($request->phone);

        // Сохраняем ID пользователя в кэше
        \Cache::put('phone_login_customer_id_' . $request->phone, $customer->id, now()->addMinutes(10));

        // Для тестирования возвращаем код в ответе (в продакшене убрать!)
        return response([
            'success' => true,
            'message' => trans('rest-api::app.shop.customer.accounts.verification-code-sent'),
        ]);
    }

    /**
     * Verify phone code for login.
     */
    public function verifyPhoneLogin(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'code'  => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'message' => $validator->messages(),
            ], 400);
        }

        // Проверяем код
        if (!$this->phoneVerificationService->verifyCode($request->phone, $request->code)) {
            return response([
                'success' => false,
                'message' => trans('rest-api::app.shop.customer.accounts.invalid-verification-code'),
            ], 400);
        }

        // Получаем ID пользователя из кэша
        $customerId = \Cache::get('phone_login_customer_id_' . $request->phone);

        if (!$customerId) {
            return response([
                'success' => false,
                'message' => 'Login session expired. Please try again.',
            ], 400);
        }

        // Получаем пользователя
        $customer = $this->customerRepository->find($customerId);

        if (! $customer) {
            return response([
                'success' => false,
                'message' => trans('rest-api::app.shop.customer.accounts.customer-not-found'),
            ], 404);
        }

        // Удаляем существующие токены
        $customer->tokens()->delete();

        // Создаем новый токен
        $token = $customer->createToken('device_name', ['role:customer'])->plainTextToken;

        // Очищаем данные из кэша
        \Cache::forget('phone_verification_code_' . $request->phone);
        \Cache::forget('phone_login_customer_id_' . $request->phone);

        // Событие после входа
        Event::dispatch('customer.after.login', $customer);

        return response([
            'success'  => true,
            'message'  => trans('rest-api::app.shop.customer.accounts.logged-in-success'),
            'customer' => new CustomerResource($customer),
            'token'    => $token,
        ]);
    }
}