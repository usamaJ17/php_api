<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $primaryKey = 'cid';
    protected $table = 'tbl_category';

    protected $fillable = [
        'category_name',
        'category_image',
        'category_status'
    ];
    public function galleries()
    {
        return $this->hasMany(Gallery::class, 'cat_id', 'cid');
    }

}
