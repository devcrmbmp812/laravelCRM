<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'organisation_settings';
    protected $appends = ['logo_url', 'login_background_url'];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function getLogoUrlAttribute()
    {
        if (is_null($this->logo)) {
            return asset('img/worksuite-logo.png');
        }
        return asset_url('app-logo/'.$this->logo);
    }

    public function getLoginBackgroundUrlAttribute()
    {
        if (is_null($this->login_background) || $this->login_background == 'login-background.jpg'){
            return asset_url('login-background.jpg');
        }

        return asset_url('login-background/'.$this->login_background);
    }
}
