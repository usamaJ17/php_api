<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $table = 'tbl_settings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'limit_recent_wallpaper',
        'category_sort',
        'category_order',
        'onesignal_app_id',
        'onesignal_rest_api_key',
        'providers',
        'protocol_type',
        'privacy_policy',
        'package_name',
        'fcm_server_key',
        'fcm_notification_topic',
        'more_apps_url'
    ];

}
