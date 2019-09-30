<?php

/*
|--------------------------------------------------------------------------
| Register Namespaces And Routes
|--------------------------------------------------------------------------
|
| When a module starting, this file will executed automatically. This helps
| to register some namespaces like translator or view. Also this file
| will load the routes file for each module. You may also modify
| this file as you want.
|
*/

use App\MessageSetting;
use Illuminate\Support\Str;

if (!function_exists('user')) {

    /**
     * Return current logged in user
     */
    function user()
    {
        if (session()->has('user')) {
            return session('user');
        }

        $user = auth()->user();

        if ($user) {
            session(['user' => $user]);
            return session('user');
        }

        return null;


    }

}

if (!function_exists('admin_theme')) {


    function admin_theme()
    {
        if (!session()->has('admin_theme')) {
            session(['admin_theme' => \App\ThemeSetting::where('panel', 'admin')->first()]);
        }

        return session('admin_theme');

    }

}

if (!function_exists('employee_theme')) {


    function employee_theme()
    {
        if (!session()->has('employee_theme')) {
            session(['employee_theme' => \App\ThemeSetting::where('panel', 'employee')->first()]);
        }

        return session('employee_theme');
    }
}

if (!function_exists('client_theme')) {


    function client_theme()
    {
        if (!session()->has('client_theme')) {
            session(['client_theme' => \App\ThemeSetting::where('panel', 'client')->first()]);
        }

        return session('client_theme');
    }

}

if (!function_exists('global_setting')) {


    function global_setting()
    {
        if (!session()->has('global_setting')) {
            session(['global_setting' => \App\Setting::first()]);
        }

        return session('global_setting');
    }

}

if (!function_exists('push_setting')) {


    function push_setting()
    {
        if (!session()->has('push_setting')) {
            session(['push_setting' => \App\PushNotificationSetting::first()]);
        }

        return session('push_setting');
    }

}

if (!function_exists('language_setting')) {

    function language_setting()
    {
        if (!session()->has('language_setting')) {
            session(['language_setting' => \App\LanguageSetting::where('status', 'enabled')->get()]);
        }

        return session('language_setting');
    }
}

if (!function_exists('smtp_setting')) {

    function smtp_setting()
    {
        if (!session()->has('smtp_setting')) {
            session(['smtp_setting' => \App\SmtpSetting::first()]);
        }

        return session('smtp_setting');
    }
}

if (!function_exists('message_setting')) {

    function message_setting()
    {
        if (!session()->has('message_setting')) {
            session(['message_setting' => MessageSetting::first()]);
        }

        return session('message_setting');
    }
}

if (!function_exists('storage_setting')) {

    function storage_setting()
    {
        if (!session()->has('storage_setting')) {
            session(['storage_setting' => \App\StorageSetting::where('status', 'enabled')->first()]);
        }

        return session('storage_setting');
    }

}

if (!function_exists('email_notification_setting')) {


    function email_notification_setting()
    {
        if(user()->hasRole('client') || user()->hasRole('employee')) {
            return \App\EmailNotificationSetting::all();
        }

        if (!session()->has('email_notification_setting')) {
            session(['email_notification_setting' => \App\EmailNotificationSetting::all()]);
        }

        return session('email_notification_setting');

    }

}

if (!function_exists('asset_url')) {

    // @codingStandardsIgnoreLine
    function asset_url($path)
    {
        $path = 'user-uploads/' . $path;
        $storageUrl = $path;

        if (!Str::startsWith($storageUrl, 'http')) {
            return url($storageUrl);
        }

        return $storageUrl;

    }

}

if (!function_exists('user_modules')) {


    function user_modules()
    {
        if (!session()->has('user_modules')) {
            $user = auth()->user();

            $module = new \App\ModuleSetting();

            if ($user->hasRole('admin')) {
                $module = $module->where('type', 'admin');

            } elseif ($user->hasRole('client')) {
                $module = $module->where('type', 'client');

            } elseif ($user->hasRole('employee')) {
                $module = $module->where('type', 'employee');
            }

            $module = $module->where('status', 'active');
            $module->select('module_name');

            $module = $module->get();
            $moduleArray = [];
            foreach ($module->toArray() as $item) {
                array_push($moduleArray, array_values($item)[0]);
            }

            session(['user_modules' => $moduleArray]);
        }

        return session('user_modules');

    }

}
