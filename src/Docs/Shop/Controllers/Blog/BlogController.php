<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Blog;

class BlogController
{
    /**
     * @OA\Get(
     *      path="/api/v1/blogs",
     *      operationId="getBlogs",
     *      tags={"Блоги"},
     *      summary="Получение списка блогов",
     *      description="Возвращает список блогов с пагинацией",
     *
     *      @OA\Parameter(
     *          name="page",
     *          description="Номер страницы",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="limit",
     *          description="Количество записей на странице",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
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
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="blogs",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="bloga"),
     *                          @OA\Property(property="slug", type="string", example="bloga"),
     *                          @OA\Property(property="short_description", type="string", example="descblog"),
     *                          @OA\Property(property="description", type="string", example="<p>description bloga</p>"),
     *                          @OA\Property(property="channels", type="integer", example=1),
     *                          @OA\Property(property="default_category", type="integer", example=1),
     *                          @OA\Property(property="categorys", type="string", example=null),
     *                          @OA\Property(property="tags", type="string", example=""),
     *                          @OA\Property(property="author", type="string", example="Example"),
     *                          @OA\Property(property="author_id", type="integer", example=1),
     *                          @OA\Property(property="src", type="string", example=""),
     *                          @OA\Property(property="locale", type="string", example="en"),
     *                          @OA\Property(property="status", type="integer", example=1),
     *                          @OA\Property(property="allow_comments", type="integer", example=0),
     *                          @OA\Property(property="meta_title", type="string", example="bloga"),
     *                          @OA\Property(property="meta_description", type="string", example="bloga"),
     *                          @OA\Property(property="meta_keywords", type="string", example="blog"),
     *                          @OA\Property(property="published_at", type="string", format="date-time", example="2025-04-15 00:00:00"),
     *                          @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-15T06:57:46.000000Z"),
     *                          @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-15T06:57:46.000000Z"),
     *                          @OA\Property(property="deleted_at", type="string", format="date-time", example=null),
     *                          @OA\Property(property="src_url", type="string", example=null)
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="meta",
     *                      type="object",
     *                      @OA\Property(property="total", type="integer", example=10),
     *                      @OA\Property(property="current_page", type="integer", example=1),
     *                      @OA\Property(property="per_page", type="integer", example=10),
     *                      @OA\Property(property="last_page", type="integer", example=1)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Ошибка сервера",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Сообщение об ошибке")
     *          )
     *      )
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Get(
     *      path="/api/v1/blogs/{id}",
     *      operationId="getBlogById",
     *      tags={"Блоги"},
     *      summary="Получение блога по ID",
     *      description="Возвращает детальную информацию о блоге по его ID",
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="ID блога",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
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
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="bloga"),
     *                  @OA\Property(property="slug", type="string", example="bloga"),
     *                  @OA\Property(property="short_description", type="string", example="descblog"),
     *                  @OA\Property(property="description", type="string", example="<p>description bloga</p>"),
     *                  @OA\Property(property="channels", type="integer", example=1),
     *                  @OA\Property(property="default_category", type="integer", example=1),
     *                  @OA\Property(property="categorys", type="string", example=null),
     *                  @OA\Property(property="tags", type="string", example=""),
     *                  @OA\Property(property="author", type="string", example="Example"),
     *                  @OA\Property(property="author_id", type="integer", example=1),
     *                  @OA\Property(property="src", type="string", example=""),
     *                  @OA\Property(property="locale", type="string", example="en"),
     *                  @OA\Property(property="status", type="integer", example=1),
     *                  @OA\Property(property="allow_comments", type="integer", example=0),
     *                  @OA\Property(property="meta_title", type="string", example="bloga"),
     *                  @OA\Property(property="meta_description", type="string", example="bloga"),
     *                  @OA\Property(property="meta_keywords", type="string", example="blog"),
     *                  @OA\Property(property="published_at", type="string", format="date-time", example="2025-04-15 00:00:00"),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-15T06:57:46.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-15T06:57:46.000000Z"),
     *                  @OA\Property(property="deleted_at", type="string", format="date-time", example=null),
     *                  @OA\Property(property="src_url", type="string", example=null),
     *                  @OA\Property(
     *                      property="categories",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="Категория блога"),
     *                          @OA\Property(property="slug", type="string", example="kategoriya-bloga")
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Блог не найден",
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
     *                  example="Блог не найден"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Ошибка сервера",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Сообщение об ошибке")
     *          )
     *      )
     * )
     */
    public function show()
    {
    }
}