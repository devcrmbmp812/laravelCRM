<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = ['name'];
    public function members()
    {
        return $this->hasMany(EmployeeDetails::class, 'designation_id');
    }

}
