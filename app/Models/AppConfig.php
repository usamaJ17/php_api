<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    use HasFactory;
    protected $table = 'tbl_app_config';

    protected $fillable = [
        'package_name',
        'status',
        'redirect_url',
    ];
}
