<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $table = 'app_configs';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'value',
        'description',
        'updated_by',
    ];
    
    protected $casts = [
        'value' => 'json',
        'updated_at' => 'datetime',
    ];
}