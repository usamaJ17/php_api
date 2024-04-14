<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $table = 'tbl_menu';
    protected $primaryKey = 'menu_id';
    protected $fillable = [
        'menu_title',
        'menu_order',
        'menu_filter',
        'menu_category',
        'menu_status',
    ];
}
