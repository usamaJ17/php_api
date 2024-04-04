<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;
    protected $table = 'tbl_gallery';

    protected $fillable = [
        'cat_id',
        'image',
        'view_count',
        'download_count',
        'image_url',
        'type',
        'featured',
        'tags',
        'image_name',
        'image_resolution',
        'image_size',
        'image_extension',
        'image_status',
        'image_thumb',
        'last_update',
        'rewarded'
    ];
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cid');
    }

}
