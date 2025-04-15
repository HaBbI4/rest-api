<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\RestApi\Http\Controllers\V1\Shop\ShopController;
use Webbycrown\BlogBagisto\Repositories\BlogRepository;
use Webbycrown\BlogBagisto\Repositories\BlogCategoryRepository;

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
     * Get blog by url_key.
     *
     * @param  string  $urlKey
     * @return \Illuminate\Http\Response
     */
    public function show($urlKey)
    {
        // Получаем блог по url_key
        $blog = $this->blogRepository->findByUrlKeyOrFail($urlKey);

        if (!$blog || !$blog->status) {
            return response([
                'success' => false,
                'message' => trans('rest-api::app.shop.blog.not-found'),
            ], 404);
        }

        // Получаем категории блога
        $categories = [];
        foreach ($blog->categories as $category) {
            $categories[] = [
                'id' => $category->id,
                'name' => $category->name,
                'url_key' => $category->url_key,
            ];
        }

        // Получаем теги блога
        $tags = [];
        foreach ($blog->tags as $tag) {
            $tags[] = [
                'id' => $tag->id,
                'name' => $tag->name,
            ];
        }

        // Форматируем данные блога для API
        $formattedBlog = [
            'id' => $blog->id,
            'title' => $blog->title,
            'url_key' => $blog->url_key,
            'preview_image' => $blog->preview_image_url,
            'content' => $blog->content,
            'summary' => $blog->summary,
            'published_at' => $blog->published_at,
            'meta_title' => $blog->meta_title,
            'meta_description' => $blog->meta_description,
            'meta_keywords' => $blog->meta_keywords,
            'categories' => $categories,
            'tags' => $tags,
        ];

        return response([
            'success' => true,
            'data' => $formattedBlog,
        ]);
    }
}