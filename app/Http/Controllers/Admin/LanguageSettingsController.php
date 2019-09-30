<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\Admin\Language\StoreRequest;
use App\Http\Requests\Admin\Language\UpdateRequest;
use App\LanguageSetting;
use Illuminate\Http\Request;

class LanguageSettingsController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.language').' '.__('app.menu.settings');
        $this->pageIcon = 'icon-settings';
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(){
        $this->languages = LanguageSetting::all();
        return view('admin.language-settings.index', $this->data);
    }

    /**
     * @param Request $request
     * @param $id
     * @return array
     */
    public function update(Request $request, $id){
        $setting = LanguageSetting::findOrFail($request->id);
        $setting->status = $request->status;
        $setting->save();
        session(['language_setting' => \App\LanguageSetting::where('status', 'enabled')->get()]);

        return Reply::success(__('messages.languageUpdated'));
    }

    /**
     * @param UpdateRequest $request
     * @param $id
     * @return array
     */
    public function updateData(UpdateRequest $request, $id)
    {
        $setting = LanguageSetting::findOrFail($request->id);
        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->status = $request->status;
        $setting->save();

        session(['language_setting' => \App\LanguageSetting::where('status', 'enabled')->get()]);

        return Reply::redirect(route('admin.language-settings.index'), __('messages.languageUpdated'));
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        $setting = new LanguageSetting();
        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->status = $request->status;
        $setting->save();
        session(['language_setting' => \App\LanguageSetting::where('status', 'enabled')->get()]);

        return Reply::redirect(route('admin.language-settings.index'), __('messages.languageAdded'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view('admin.language-settings.create', $this->data);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, $id)
    {
        $this->languageSetting = LanguageSetting::findOrFail($id);
        session(['language_setting' => \App\LanguageSetting::where('status', 'enabled')->get()]);
        return view('admin.language-settings.edit', $this->data);
    }

    /**
     * @param $id
     * @return array
     */
    public function destroy($id){
        LanguageSetting::destroy($id);
        session(['language_setting' => \App\LanguageSetting::where('status', 'enabled')->get()]);
        return  Reply::success(__('messages.languageDeleted'));
    }
}
