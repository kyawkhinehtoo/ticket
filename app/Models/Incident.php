<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'incident';
    
    protected $fillable = [
        'start_date',
        'start_time',
        'close_date',
        'close_time',
        'type',
        'description',
        'topic',
        'assigned_id',
        'pic_name',
        'created_by',
        'company_id',
        'contract_id',
        'status',
        'service_report',
        // Remove deleted_at from fillable as SoftDeletes handles this
    ];
    
    protected $casts = [
        'service_report' => 'array',
        'assigned_id' => 'array',
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at',
        'deleted_at', // Add deleted_at to dates array
    ];

    // Fix relationship definitions
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    
    public function assignedUsers()
    {
        // Since assigned_id is an array, you might need a custom accessor
        // This depends on how you're storing the assigned users
        return $this->belongsTo(User::class, 'assigned_id', 'id');
    }
    
    protected function users(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => explode(',', $value),
            set: fn ($value) => implode(',', $value),
        );
    }
    
    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d-M-Y H:i:s');
    }
    
    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d-M-Y H:i:s');
    }
 
}
