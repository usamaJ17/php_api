<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;
    protected $table = 'tbl_license';
    protected $primaryKey = 'id';
    protected $fillable = [
        'purchase_code',
        'item_id',
        'item_name',
        'buyer',
        'license_type',
        'purchase_date',
    ];
}
