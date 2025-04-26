<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class MainCon extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'maincon';
    protected $fillable = [
        'name',
        'address',
        'logo',
    ];
    protected $casts = [
        'file' => 'array',
    ];

    public function company()
    {
        return $this->hasMany(Company::class, 'maincon_id');
    }
    public function user()
    {
        return $this->hasMany(User::class, 'maincon_id');
    }
}
