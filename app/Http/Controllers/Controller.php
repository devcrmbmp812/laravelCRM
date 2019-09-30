<?php

namespace App\Http\Controllers;

use App\GdprSetting;
use App\Setting;
use Carbon\Carbon;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,AppBoot;

    public function __construct()
    {

        $this->showInstall();
        $this->checkMigrateStatus();

        $this->global = Setting::first();
        
        // Added for the update
        try{
            $this->gdpr = GdprSetting::first();
        }catch (\Exception $e){

        }

        config(['app.name' => $this->global->company_name]);
        config(['app.url' => url('/')]);

        App::setLocale($this->global->locale);
        Carbon::setLocale($this->global->locale);
        setlocale(LC_TIME, $this->global->locale . '_' . strtoupper($this->global->locale));
        if (config('app.env') !== 'development') {
            config(['app.debug' => $this->global->app_debug]);
        }

        $this->middleware(function ($request, $next) {
            if (auth()->user()) {
                config(['froiden_envato.allow_users_id' => true]);
            }
            return $next($request);
        });

    }

    public function checkMigrateStatus()
    {
        $status = Artisan::call('migrate:check');

        if ($status && !request()->ajax()) {
            Artisan::call('migrate', array('--force' => true)); //migrate database
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        }
    }


}
