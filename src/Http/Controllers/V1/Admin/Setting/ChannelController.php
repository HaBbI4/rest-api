<?php

namespace Webkul\RestApi\Http\Controllers\V1\Admin\Setting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Core\Repositories\ChannelRepository;

class ChannelController extends SettingController
{
    /**
     * Channel repository instance.
     *
     * @var \Webkul\Core\Repositories\ChannelRepository
     */
    protected $channelRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Core\Repositories\ChannelRepository  $channelRepository
     * @return void
     */
    public function __construct(ChannelRepository $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $channels = $this->channelRepository->all();

        return response([
            'data' => $channels,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            /* general */
            'code'              => ['required', 'unique:channels,code', new \Webkul\Core\Contracts\Validations\Code],
            'name'              => 'required',
            'description'       => 'nullable',
            'inventory_sources' => 'required|array|min:1',
            'root_category_id'  => 'required',
            'hostname'          => 'unique:channels,hostname',

            /* currencies and locales */
            'locales'           => 'required|array|min:1',
            'default_locale_id' => 'required|in_array:locales.*',
            'currencies'        => 'required|array|min:1',
            'base_currency_id'  => 'required|in_array:currencies.*',

            /* design */
            'theme'             => 'nullable',
            'home_page_content' => 'nullable',
            'footer_content'    => 'nullable',
            'logo.*'            => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
            'favicon.*'         => 'nullable|mimes:bmp,jpeg,jpg,png,webp',

            /* seo */
            'seo_title'       => 'required|string',
            'seo_description' => 'required|string',
            'seo_keywords'    => 'required|string',

            /* maintenance mode */
            'is_maintenance_on'     => 'boolean',
            'maintenance_mode_text' => 'nullable',
            'allowed_ips'           => 'nullable',
        ]);

        $data = $this->setSEOContent($data);

        Event::dispatch('core.channel.create.before');

        $channel = $this->channelRepository->create($data);

        Event::dispatch('core.channel.create.after', $channel);

        return response([
            'data'    => $channel,
            'message' => trans('admin::app.settings.channels.create-success'),
        ]);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $channel = $this->channelRepository->with(['locales', 'currencies'])->findOrFail($id);

        return response([
            'data' => $channel,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $locale = core()->getRequestedLocaleCode();

        $data = $request->validate([
            /* general */
            'code'                   => ['required', 'unique:channels,code,' . $id, new \Webkul\Core\Contracts\Validations\Code],
            $locale . '.name'        => 'required',
            $locale . '.description' => 'nullable',
            'inventory_sources'      => 'required|array|min:1',
            'root_category_id'       => 'required',
            'hostname'               => 'unique:channels,hostname,' . $id,

            /* currencies and locales */
            'locales'           => 'required|array|min:1',
            'default_locale_id' => 'required|in_array:locales.*',
            'currencies'        => 'required|array|min:1',
            'base_currency_id'  => 'required|in_array:currencies.*',

            /* design */
            'theme'                        => 'nullable',
            $locale . '.home_page_content' => 'nullable',
            $locale . '.footer_content'    => 'nullable',
            'logo.*'                       => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
            'favicon.*'                    => 'nullable|mimes:bmp,jpeg,jpg,png,webp',

            /* seo */
            $locale . '.seo_title'       => 'nullable',
            $locale . '.seo_description' => 'nullable',
            $locale . '.seo_keywords'    => 'nullable',

            /* maintenance mode */
            'is_maintenance_on'                => 'boolean',
            $locale . '.maintenance_mode_text' => 'nullable',
            'allowed_ips'                      => 'nullable',
        ]);

        $data = $this->setSEOContent($data, $locale);

        Event::dispatch('core.channel.update.before', $id);

        $channel = $this->channelRepository->update($data, $id);

        if ($channel->base_currency->code !== session()->get('currency')) {
            session()->put('currency', $channel->base_currency->code);
        }

        Event::dispatch('core.channel.update.after', $channel);

        return response([
            'data'    => $channel,
            'message' => trans('admin::app.settings.channels.update-success'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $channel = $this->channelRepository->findOrFail($id);

        if ($channel->code == config('app.channel')) {
            return response([
                'message' => trans('admin::app.settings.channels.last-delete-error'),
            ], 400);
        }

        try {
            Event::dispatch('core.channel.delete.before', $id);

            $this->channelRepository->delete($id);

            Event::dispatch('core.channel.delete.after', $id);

            return response([
                'message' => trans('admin::app.settings.channels.delete-success'),
            ]);
        } catch (\Exception $e) {}

        return response([
            'message' => trans('admin::app.response.delete-failed', ['name' => 'Channel']),
        ], 400);
    }

    /**
     * Set the seo content and return back the updated array.
     *
     * @param  array  $data
     * @param  string  $locale
     * @return array
     */
    private function setSEOContent(array $data, $locale = null)
    {
        $editedData = $data;

        if ($locale) {
            $editedData = $data[$locale];
        }

        $editedData['home_seo']['meta_title'] = $editedData['seo_title'];
        $editedData['home_seo']['meta_description'] = $editedData['seo_description'];
        $editedData['home_seo']['meta_keywords'] = $editedData['seo_keywords'];
        $editedData['home_seo'] = json_encode($editedData['home_seo']);

        $editedData = $this->unsetKeys($editedData, ['seo_title', 'seo_description', 'seo_keywords']);

        if ($locale) {
            $data[$locale] = $editedData;
            $editedData = $data;
        }

        return $editedData;
    }

    /**
     * Unset keys.
     *
     * @param  array  $keys
     * @return array
     */
    private function unsetKeys($data, $keys)
    {
        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }
}
