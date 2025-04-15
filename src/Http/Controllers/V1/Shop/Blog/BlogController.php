<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\RestApi\Http\Controllers\V1\Shop\ShopController;
use Webbycrown\BlogBagisto\Repositories\BlogRepository;
use Webbycrown\BlogBagisto\Repositories\BlogCategoryRepository;
use Illuminate\Support\Facades\DB;

class BlogController extends ShopController
{
    /**
     * BlogRepository instance
     *
     * @var \Webbycrown\BlogBagisto\Repositories\BlogRepository
     */
    protected $blogRepository;

    /**
     * BlogCategoryRepository instance
     *
     * @var \Webbycrown\BlogBagisto\Repositories\BlogCategoryRepository
     */
    protected $blogCategoryRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webbycrown\BlogBagisto\Repositories\BlogRepository  $blogRepository
     * @param  \Webbycrown\BlogBagisto\Repositories\BlogCategoryRepository  $blogCategoryRepository
     * @return void
     */
    public function __construct(
        BlogRepository $blogRepository,
        BlogCategoryRepository $blogCategoryRepository
    ) {
        $this->blogRepository = $blogRepository;
        $this->blogCategoryRepository = $blogCategoryRepository;
    }

    /**
     * Get all blogs.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = $request->all();

        // Получаем только активные блоги
        $params['status'] = 1;

        // Получаем блоги с пагинацией
        $blogs = $this->blogRepository->getAll($params);

        // Преобразуем блоги в формат для API
        $formattedBlogs = [];

        foreach ($blogs as $blog) {
            $formattedBlogs[] = [
                'id' => $blog->id,
                'title' => $blog->title,
                'url_key' => $blog->url_key,
                'preview_image' => $blog->preview_image_url,
                'summary' => $blog->summary,
                'published_at' => $blog->published_at,
                'meta_title' => $blog->meta_title,
                'meta_description' => $blog->meta_description,
                'meta_keywords' => $blog->meta_keywords,
            ];
        }

        return response([
            'success' => true,
            'data' => [
                'blogs' => $formattedBlogs,
                'meta' => [
                    'total' => $blogs->total(),
                    'current_page' => $blogs->currentPage(),
                    'per_page' => $blogs->perPage(),
                    'last_page' => $blogs->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Get blog by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // Получаем блог по ID с загрузкой связанных данных
            $blog = $this->blogRepository->findOrFail($id);

            if (!$blog) {
                return response([
                    'success' => false,
                    'message' => trans('rest-api::app.shop.blog.not-found'),
                ], 404);
            }

            // Преобразуем объект блога в массив для доступа к свойствам
            $blogArray = $blog->toArray();

            // Получаем категории блога, если они доступны
            $categories = [];
            if (isset($blog->categories) && is_iterable($blog->categories)) {
                foreach ($blog->categories as $category) {
                    $categories[] = [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                }
            } else if (isset($blogArray['categories']) && is_array($blogArray['categories'])) {
                $categories = $blogArray['categories'];
            }

            // Добавляем категории к данным блога
            $blogArray['categories'] = $categories;

            // Добавляем URL изображения, если оно есть
            if (isset($blog->src) && !empty($blog->src)) {
                $blogArray['src_url'] = url('storage/' . $blog->src);
            }

            // Проверяем и выводим все доступные поля
            return response([
                'success' => true,
                'data' => $blogArray,
            ]);
        } catch (\Exception $e) {
            return response([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Для отладки
            ], 500);
        }
    }
}