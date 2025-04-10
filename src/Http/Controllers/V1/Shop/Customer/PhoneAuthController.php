<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Customer\CustomerResource;

class PhoneAuthController extends CustomerController
{
    /**
     * Verification codes repository
     *
     * @var array
     */
    protected static $verificationCodes = [];

    /**
     * Controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository
    ) {
        parent::__construct($customerRepository);
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

        // Генерация кода подтверждения (6-значный код)
        $verificationCode = rand(100000, 999999);

        // Сохраняем код и данные пользователя в сессии
        $request->session()->put('phone_verification', [
            'phone' => $request->phone,
            'code'  => $verificationCode,
            'data'  => $request->only(['first_name', 'last_name', 'phone', 'email']),
            'expires_at' => now()->addMinutes(10), // Код действителен 10 минут
        ]);

        // В реальном приложении здесь должна быть отправка SMS
        // Например: $this->sendSMS($request->phone, "Ваш код подтверждения: $verificationCode");

        // Для тестирования возвращаем код в ответе (в продакшене убрать!)
        return response([
            'success' => true,
            'message' => trans('rest-api::app.shop.customer.accounts.verification-code-sent'),
            'code'    => $verificationCode, // Удалить в продакшене
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

        // Получаем данные верификации из сессии
        $verificationData = $request->session()->get('phone_verification');

        // Проверяем валидность кода
        if (
            ! $verificationData
            || $verificationData['phone'] != $request->phone
            || $verificationData['code'] != $request->code
            || now()->isAfter($verificationData['expires_at'])
        ) {
            return response([
                'success' => false,
                'message' => trans('rest-api::app.shop.customer.accounts.invalid-verification-code'),
            ], 400);
        }

        $data = $verificationData['data'];

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

        // Очищаем данные верификации из сессии
        $request->session()->forget('phone_verification');

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

        // Генерация кода подтверждения (6-значный код)
        $verificationCode = rand(100000, 999999);

        // Сохраняем код в сессии
        $request->session()->put('phone_login_verification', [
            'phone' => $request->phone,
            'code'  => $verificationCode,
            'customer_id' => $customer->id,
            'expires_at' => now()->addMinutes(10), // Код действителен 10 минут
        ]);

        // В реальном приложении здесь должна быть отправка SMS
        // Например: $this->sendSMS($request->phone, "Ваш код подтверждения: $verificationCode");

        // Для тестирования возвращаем код в ответе (в продакшене убрать!)
        return response([
            'success' => true,
            'message' => trans('rest-api::app.shop.customer.accounts.verification-code-sent'),
            'code'    => $verificationCode, // Удалить в продакшене
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
            'device_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'message' => $validator->messages(),
            ], 400);
        }

        // Получаем данные верификации из сессии
        $verificationData = $request->session()->get('phone_login_verification');

        // Проверяем валидность кода
        if (
            ! $verificationData
            || $verificationData['phone'] != $request->phone
            || $verificationData['code'] != $request->code
            || now()->isAfter($verificationData['expires_at'])
        ) {
            return response([
                'success' => false,
                'message' => trans('rest-api::app.shop.customer.accounts.invalid-verification-code'),
            ], 400);
        }

        // Получаем пользователя
        $customer = $this->customerRepository->find($verificationData['customer_id']);

        if (! $customer) {
            return response([
                'success' => false,
                'message' => trans('rest-api::app.shop.customer.accounts.customer-not-found'),
            ], 404);
        }

        // Удаляем существующие токены
        $customer->tokens()->delete();

        // Создаем новый токен
        $token = $customer->createToken($request->device_name, ['role:customer'])->plainTextToken;

        // Очищаем данные верификации из сессии
        $request->session()->forget('phone_login_verification');

        // Событие после входа
        Event::dispatch('customer.after.login', $customer);

        return response([
            'success'  => true,
            'message'  => trans('rest-api::app.shop.customer.accounts.logged-in-success'),
            'customer' => new CustomerResource($customer),
            'token'    => $token,
        ]);
    }

    /**
     * Send SMS with verification code (mock implementation).
     */
    protected function sendSMS(string $phone, string $message): bool
    {
        // Здесь должна быть реализация отправки SMS через выбранный сервис
        // Например:
        // $client = new SmsClient(config('services.sms.key'));
        // return $client->send($phone, $message);

        // Заглушка для тестирования
        \Log::info("SMS to $phone: $message");

        return true;
    }
}