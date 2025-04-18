<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class CustomerCompanyController
{
    /**
     * @OA\Post(
     *      path="/api/v1/customer/company-register",
     *      operationId="registerCompanyCustomer",
     *      tags={"Клиенты"},
     *      summary="Регистрация клиента с компанией",
     *      description="Регистрация клиента с данными компании (оптовый клиент)",
     *
     *      @OA\RequestBody(
     *
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="first_name",
     *                      type="string",
     *                      example="Иван"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      example="Иванов"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      format="email",
     *                      example="company@example.com"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      example="+79001234567"
     *                  ),
     *                  @OA\Property(
     *                      property="company_name",
     *                      type="string",
     *                      example="ООО Компания"
     *                  ),
     *                  @OA\Property(
     *                      property="company_address",
     *                      type="string",
     *                      example="г. Москва, ул. Примерная, д. 123"
     *                  ),
     *                  @OA\Property(
     *                      property="company_requisites",
     *                      type="string",
     *                      example="ИНН: 1234567890, КПП: 123456789"
     *                  ),
     *                  @OA\Property(
     *                      property="social_networks",
     *                      type="string",
     *                      example="Instagram: @company, VK: company"
     *                  ),
     *                  required={"first_name", "email", "phone", "company_name"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Успешная операция",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Заявка на регистрацию компании успешно отправлена. После проверки администратором вы получите доступ к системе."
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/Customer"
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Ошибка валидации",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=false
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="object",
     *                  example={"email": {"The email has already been taken."}}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Ошибка сервера",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=false
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Ошибка при создании клиента"
     *              )
     *          )
     *      )
     * )
     */
    public function register()
    {
    }
}
