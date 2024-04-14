<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdsStatus extends Model
{
    use HasFactory;
    protected $table = 'tbl_ads_status';
    protected $primaryKey = 'ads_status_id';

    protected $fillable = [
        'banner_ad_on_home_page',
        'banner_ad_on_search_page',
        'banner_ad_on_wallpaper_detail',
        'banner_ad_on_wallpaper_by_category',
        'interstitial_ad_on_click_wallpaper',
        'interstitial_ad_on_wallpaper_detail',
        'native_ad_on_wallpaper_list',
        'native_ad_on_exit_dialog',
        'app_open_ad',
    ];
}
