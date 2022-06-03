<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name_en',
        'first_name_ua',
        'last_name_en',
        'last_name_ua',
        'email',
        'phone',
        'company_id'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
