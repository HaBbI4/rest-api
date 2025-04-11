<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class PhoneAuthController
{
    /**
     * @OA\Post(
     *      path="/api/v1/customer/register-by-phone",
     *      operationId="registerByPhone",
     *      tags={"Customers"},
     *      summary="Register customer by phone",
     *      description="Register customer using phone number and send verification code",
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
     *                      example="John"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      example="Doe"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      example="+79001234567"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      format="email",
     *                      example="shop@example.com"
     *                  ),
     *                  required={"first_name", "last_name", "phone"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
     *                  example="Verification code has been sent to your phone."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
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
     *                  example={"phone": {"The phone field is required."}}
     *              )
     *          )
     *      )
     * )
     */
    public function registerByPhone()
    {
    }

    /**
     * @OA\Post(
     *      path="/api/v1/customer/verify-phone",
     *      operationId="verifyPhone",
     *      tags={"Customers"},
     *      summary="Verify phone and complete registration",
     *      description="Verify phone number with code and complete customer registration",
     *
     *      @OA\RequestBody(
     *
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      example="+79001234567"
     *                  ),
     *                  @OA\Property(
     *                      property="code",
     *                      type="string",
     *                      example="123456"
     *                  ),
     *                  required={"phone", "code"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
     *                  example="Customer registered successfully."
     *              ),
     *              @OA\Property(
     *                  property="customer",
     *                  type="object",
     *                  ref="#/components/schemas/Customer"
     *              ),
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
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
     *                  example="Invalid verification code."
     *              )
     *          )
     *      )
     * )
     */
    public function verifyPhone()
    {
    }

    /**
     * @OA\Post(
     *      path="/api/v1/customer/login-by-phone",
     *      operationId="loginByPhone",
     *      tags={"Customers"},
     *      summary="Login customer by phone",
     *      description="Login customer using phone number and send verification code",
     *
     *      @OA\RequestBody(
     *
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      example="+79001234567"
     *                  ),
     *                  required={"phone"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
     *                  example="Verification code has been sent to your phone."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
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
     *                  example={"phone": {"The phone field is required."}}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
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
     *                  example="Phone number not found."
     *              )
     *          )
     *      )
     * )
     */
    public function loginByPhone()
    {
    }

    /**
     * @OA\Post(
     *      path="/api/v1/customer/verify-phone-login",
     *      operationId="verifyPhoneLogin",
     *      tags={"Customers"},
     *      summary="Verify phone and complete login",
     *      description="Verify phone number with code and complete customer login",
     *
     *      @OA\RequestBody(
     *
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="phone",
     *                      type="string",
     *                      example="+79001234567"
     *                  ),
     *                  @OA\Property(
     *                      property="code",
     *                      type="string",
     *                      example="123456"
     *                  ),
     *                  @OA\Property(
     *                      property="device_name",
     *                      type="string",
     *                      example="android"
     *                  ),
     *                  required={"phone", "code", "device_name"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
     *                  example="Logged in successfully."
     *              ),
     *              @OA\Property(
     *                  property="customer",
     *                  type="object",
     *                  ref="#/components/schemas/Customer"
     *              ),
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
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
     *                  example="Invalid verification code."
     *              )
     *          )
     *      )
     * )
     */
    public function verifyPhoneLogin()
    {
    }
}
