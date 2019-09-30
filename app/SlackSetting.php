<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlackSetting extends Model
{

    protected $appends = ['slack_logo_url'];

    public function getSlackLogoUrlAttribute()
    {
        if (is_null($this->slack_logo)) {
            return "http://via.placeholder.com/200x150.png?text=".__('modules.slackSettings.uploadSlackLogo');
        }
        return asset_url('slack-logo/'.$this->slack_logo);
    }
}
