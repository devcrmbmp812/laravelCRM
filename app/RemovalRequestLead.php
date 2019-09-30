<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RemovalRequestLead extends Model
{

    protected $table = 'removal_requests_lead';

    public function lead(){
        return $this->belongsTo(Lead::class);
    }
}
