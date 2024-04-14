<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;
    protected $table = 'tbl_ads';
    protected $primaryKey = 'id';

    protected $fillable = [
        'ad_status',
        'ad_type',
        'backup_ads',
        'admob_publisher_id',
        'admob_app_id',
        'admob_banner_unit_id',
        'admob_interstitial_unit_id',
        'admob_rewarded_unit_id',
        'admob_native_unit_id',
        'admob_app_open_ad_unit_id',
        'fan_banner_unit_id',
        'fan_interstitial_unit_id',
        'fan_rewarded_unit_id',
        'fan_native_unit_id',
        'startapp_app_id',
        'unity_game_id',
        'unity_banner_placement_id',
        'unity_interstitial_placement_id',
        'unity_rewarded_placement_id',
        'applovin_banner_ad_unit_id',
        'applovin_interstitial_ad_unit_id',
        'applovin_rewarded_ad_unit_id',
        'applovin_native_ad_manual_unit_id',
        'applovin_app_open_ad_unit_id',
        'applovin_banner_zone_id',
        'applovin_banner_mrec_zone_id',
        'applovin_interstitial_zone_id',
        'applovin_rewarded_zone_id',
        'ad_manager_banner_unit_id',
        'ad_manager_interstitial_unit_id',
        'ad_manager_rewarded_unit_id',
        'ad_manager_native_unit_id',
        'ad_manager_app_open_ad_unit_id',
        'ironsource_app_key',
        'ironsource_banner_placement_name',
        'ironsource_interstitial_placement_name',
        'ironsource_rewarded_placement_name',
        'wortise_app_id',
        'wortise_banner_unit_id',
        'wortise_interstitial_unit_id',
        'wortise_rewarded_unit_id',
        'wortise_native_unit_id',
        'wortise_app_open_unit_id',
        'mopub_banner_ad_unit_id',
        'mopub_interstitial_ad_unit_id',
        'interstitial_ad_interval',
        'native_ad_interval',
        'native_ad_index',
        'native_ad_index_2',
        'native_ad_index_3',
        'native_ad_style_post_list',
        'native_ad_style_post_details',
        'native_ad_style_exit_dialog',
    ];
}
