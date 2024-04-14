<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdsPlacement extends Model
{
    use HasFactory;
    protected $table = 'tbl_ads_placement';
    protected $primaryKey = 'ads_placement_id';

    protected $fillable = [
        'banner_home',
        'banner_post_details',
        'banner_category_details',
        'banner_search',
        'interstitial_post_list',
        'rewarded_post_details',
        'native_ad_post_list',
        'native_ad_exit_dialog',
        'app_open_ad_on_start',
        'app_open_ad_on_resume',
    ];
}
