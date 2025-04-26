<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'contract';
    protected $fillable = [
        'company_id',
        'contract_from',
        'contract_to',
        'type',
        'default',
        'extra',
        'file',
    ];
    protected $casts = [
        'file' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
    public function isExpire()
    {
        return ($this->contract_to < now());
    }
}
