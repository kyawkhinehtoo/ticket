<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'company';
    protected $fillable = [
        'name',
        'address',
        'maincon_id',
    ];

    public function contract()
    {
        return $this->hasMany(Contract::class);
    }
    public function incident()
    {
        return $this->hasMany(Incident::class, 'company_id');
    }
    public function user()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function maincon()
    {
        return $this->belongsTo(MainCon::class);
    }
}
