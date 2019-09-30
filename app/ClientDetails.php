<?php

namespace App;

use App\Traits\CustomFieldsTrait;
use Illuminate\Database\Eloquent\Model;

class ClientDetails extends Model
{
    use CustomFieldsTrait;

    protected $fillable = ['company_name','user_id','address','website','note','skype','facebook','twitter','linkedin','gst_number'];

    protected $table = 'client_details';

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }
}
