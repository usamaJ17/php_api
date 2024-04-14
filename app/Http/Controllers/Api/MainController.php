<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdsPlacement;
use App\Models\AdsStatus;
use App\Models\AppConfig;
use App\Models\Gallery;
use App\Models\Category;
use App\Models\License;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    public function get_wallpapers(Request $request)
    {
        if ($request->method() != 'GET') {
            return response()->json('', 406);
        }

        $limit = $request->input('count', 10);
        $page = $request->input('page', 1);
        $order = $request->input('order', 'id DESC'); // Use the default column name without table prefix
        $filter = $request->input('filter', '1=1'); // Default filter to true, ensure safe usage

        $query = Gallery::with(['category' => function($query) {
            $query->select('cid', 'category_name')->where('category_status', '1');
        }])
        ->select('id AS image_id', 'image_name', 'image AS image_upload', 'image_thumb', 'image_url', 'type', 
                 'image_resolution AS resolution', 'image_size AS size', 'image_extension AS mime', 'view_count AS views', 
                 'download_count AS downloads', 'featured', 'tags', 'cat_id AS category_id', 'rewarded', 'last_update')
        ->where('image_status', '1')
        ->whereRaw($filter)  // Ensure safe usage of raw SQL
        ->orderByRaw($order);  // Correct chaining of the orderByRaw method

        $countTotal = $query->distinct()->count('id');
        $posts = $query->offset(($page - 1) * $limit)->limit($limit)->get();
        $count = $posts->count();

        $response = [
            'status' => 'ok',
            'count' => $count,
            'count_total' => $countTotal,
            'pages' => $page,
            'posts' => $posts
        ];

        return response()->json($response);
    }
    

    public function get_new_wallpapers(Request $request)
    {
        if ($request->method() != 'GET') {
            return response()->json('', 406);
        }

        $limit = $request->input('count', 10);
        $page = $request->input('page', 1);
        $order = $request->input('order', 'recent');
        $filter = $request->input('filter', 'all');
        $category = $request->input('category', '0');

        // Define order based on input
        $sqlOrder = $this->determineOrder($order);

        // Define filter based on input
        $sqlFilter = $this->determineFilter($filter);

        // Define category filter
        $sqlCategory = $category == '0' ? 'cat_id != 0' : 'cat_id = ' . intval($category);

        // Building the query using Eloquent
        $query = Gallery::select('id AS image_id', 'image_name', 'image AS image_upload', 'image_thumb', 'image_url', 'type',
                                 'image_resolution AS resolution', 'image_size AS size', 'image_extension AS mime', 
                                 'view_count AS views', 'download_count AS downloads', 'featured', 'tags', 
                                 'cat_id AS category_id', 'rewarded', 'last_update')
                        ->with('category:id,category_name')
                        ->whereHas('category', function ($q) {
                            $q->where('category_status', '1');
                        })
                        ->where('image_status', '1')
                        ->whereRaw($sqlFilter)
                        ->whereRaw($sqlCategory)
                        ->orderByRaw($sqlOrder);

        $countTotal = $query->distinct()->count('id');
        $posts = $query->paginate($limit, ['*'], 'page', $page);

        $response = [
            'status' => 'ok',
            'count' => $posts->count(),
            'count_total' => $countTotal,
            'pages' => $posts->currentPage(),
            'posts' => $posts->items()
        ];

        return response()->json($response);
    }

    private function determineOrder($order)
    {
        switch ($order) {
            case 'recent':
                return 'id DESC';
            case 'oldest':
                return 'id ASC';
            case 'featured':
                return 'featured = \'yes\' DESC, last_update DESC';
            case 'popular':
                return 'view_count DESC';
            case 'download':
                return 'download_count DESC';
            case 'random':
                return 'RAND()';
            default:
                return 'id DESC';
        }
    }

    private function determineFilter($filter)
    {
        switch ($filter) {
            case 'wallpaper':
                return "(image_extension != 'image/gif' AND image_extension != 'application/octet-stream')";
            case 'live':
                return "(image_extension = 'image/gif' OR image_extension = 'application/octet-stream')";
            case 'both':
                return "image_extension != 'all'";
            default:
                return "image_extension != 'all'";
        }
    }

    public function get_wallpaper_details(Request $request)
    {
        if ($request->method() != 'GET') {
            return response()->json('', 406);
        }

        $id = $request->query('id'); // Use Laravel's request object to access query parameters

        // Perform the query using Eloquent ORM
        $wallpaper = Gallery::select('id AS image_id', 'image_name', 'image AS image_upload', 'image_thumb', 'image_url', 
                                     'type', 'image_resolution AS resolution', 'image_size AS size', 'image_extension AS mime', 
                                     'view_count AS views', 'download_count AS downloads', 'featured', 'tags', 
                                     'cat_id AS category_id', 'rewarded', 'last_update')
                            ->with(['category' => function($query) {
                                $query->select('cid AS category_id', 'category_name');
                            }])
                            ->where('id', $id)
                            ->first();

        if (!$wallpaper) {
            return response()->json(['status' => 'error', 'message' => 'Wallpaper not found'], 404);
        }

        // Since it's details for a single wallpaper, count will always be 1 or 0 if not found
        $response = [
            'status' => 'ok',
            'count' => 1,
            'count_total' => 1,
            'pages' => 1,
            'posts' => [$wallpaper] // Ensure response format consistency, wrap in array
        ];

        return response()->json($response);
    }

    public function get_one_wallpaper(Request $request)
    {
        if ($request->method() != 'GET') {
            return response()->json('', 406);
        }

        $id = $request->query('id');

        // Perform the query using Eloquent ORM
        $wallpaper = Gallery::select('id AS image_id', 'image_name', 'image AS image_upload', 'image_thumb', 'image_url', 
                                     'type', 'image_resolution AS resolution', 'image_size AS size', 'image_extension AS mime', 
                                     'view_count AS views', 'download_count AS downloads', 'featured', 'tags', 
                                     'cat_id AS category_id', 'rewarded', 'last_update')
                            ->with(['category' => function($query) {
                                $query->select('cid AS category_id', 'category_name');
                            }])
                            ->where('id', $id)
                            ->first();

        if (!$wallpaper) {
            return response()->json(['status' => 'error', 'message' => 'Wallpaper not found'], 404);
        }

        $response = [
            'status' => 'ok',
            'wallpaper' => $wallpaper
        ];

        return response()->json($response);
    }

    public function get_categories()
    {
        // Fetch settings
        $settings = Setting::find(1); // Assuming settings are stored with ID 1
    
        // Check request method
        if(request()->method() != "GET") {
            return response()->json(['status' => 'error', 'message' => 'Method Not Allowed'], 405);
        }
    
        // Extract sort and order from settings
        $sort = $settings->category_sort;
        $order = $settings->category_order;
    
        // Fetch categories with total wallpapers count
        $categories = Category::leftJoin('tbl_gallery as g', 'tbl_category.cid', '=', 'g.cat_id')
            ->select('tbl_category.cid as category_id', 'tbl_category.category_name', 'tbl_category.category_image',
                     DB::raw('COUNT(DISTINCT g.id) as total_wallpaper'))
            ->where('tbl_category.category_status', '1')
            ->groupBy('tbl_category.cid')
            ->orderBy($sort, $order)
            ->get();
    
        // Prepare response
        $count = $categories->count();
        $response = [
            'status' => 'ok',
            'count' => $count,
            'categories' => $categories
        ];
    
        return response()->json($response, 200);
    }

    public function get_category_details(Request $request)
    {
        $id = $request->input('id');
        $order = $request->input('order');
        $filter = $request->input('filter');
        $limit = $request->input('count', 10);
        $page = $request->input('page', 1);
    
        $offset = ($page * $limit) - $limit;
    
        // Fetch total count of wallpapers for pagination
        $count_total = Gallery::leftJoin('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->where('tbl_gallery.cat_id', $id)
            ->where('c.category_status', '1')
            ->where('tbl_gallery.image_status', '1')
            ->where($filter)
            ->count();
    
        // Fetch wallpapers with applied filters
        $posts = Gallery::leftJoin('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->select('tbl_gallery.id as image_id', 'tbl_gallery.image_name', 'tbl_gallery.image as image_upload',
                'tbl_gallery.image_thumb', 'tbl_gallery.image_url', 'tbl_gallery.type',
                'tbl_gallery.image_resolution as resolution', 'tbl_gallery.image_size as size',
                'tbl_gallery.image_extension as mime', 'tbl_gallery.view_count as views',
                'tbl_gallery.download_count as downloads', 'tbl_gallery.featured', 'tbl_gallery.tags',
                'c.cid as category_id', 'c.category_name', 'tbl_gallery.last_update')
            ->where('tbl_gallery.cat_id', $id)
            ->where('c.category_status', '1')
            ->where('tbl_gallery.image_status', '1')
            ->where($filter);
    
        // Add order by clause if $order is set
        if ($order) {
            $posts->orderByRaw($order);
        }
    
        // Apply limit and offset for pagination
        $posts = $posts->skip($offset)->take($limit)->get();
    
        // Prepare the response
        $response = [
            'status' => 'ok',
            'count' => count($posts),
            'count_total' => $count_total,
            'pages' => $page,
            'posts' => $posts
        ];
    
        return response()->json($response, 200);
    }

    public function get_search(Request $request)
    {
        // Validate request method
        if (!$request->isMethod('GET')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        // Get query parameters
        $limit = $request->input('count', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $order = $request->input('order', 'recent');

        // Define order by clause
        if ($order === 'recent') {
            $orderBy = 'tbl_gallery.id DESC';
        } else {
            $orderBy = 'tbl_gallery.id DESC';
        }

        // Calculate offset
        $offset = ($page - 1) * $limit;

        // Retrieve total count
        $totalCount = Gallery::join('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->where('c.category_status', 1)
            ->where('tbl_gallery.image_status', 1)
            ->where(function ($query) use ($search) {
                $query->where('tbl_gallery.image_name', 'like', "%$search%")
                    ->orWhere('tbl_gallery.tags', 'like', "%$search%");
            })
            ->count();

        // Retrieve data
        $posts = Gallery::select(
            'tbl_gallery.id as image_id',
            'tbl_gallery.image_name',
            'tbl_gallery.image as image_upload',
            'tbl_gallery.image_thumb',
            'tbl_gallery.image_url',
            'tbl_gallery.type',
            'tbl_gallery.image_resolution as resolution',
            'tbl_gallery.image_size as size',
            'tbl_gallery.image_extension as mime',
            'tbl_gallery.view_count as views',
            'tbl_gallery.download_count as downloads',
            'tbl_gallery.featured',
            'tbl_gallery.tags',
            'c.cid as category_id',
            'c.category_name',
            'tbl_gallery.rewarded',
            'tbl_gallery.last_update'
        )
            ->join('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->where('c.category_status', 1)
            ->where('tbl_gallery.image_status', 1)
            ->where(function ($query) use ($search) {
                $query->where('tbl_gallery.image_name', 'like', "%$search%")
                    ->orWhere('tbl_gallery.tags', 'like', "%$search%");
            })
            ->orderByRaw($orderBy)
            ->limit($limit)
            ->offset($offset)
            ->get();

        // Prepare response
        $response = [
            'status' => 'ok',
            'count' => $posts->count(),
            'count_total' => $totalCount,
            'pages' => $page,
            'posts' => $posts
        ];

        return response()->json($response, 200);
    }

    public function get_search_category(Request $request)
    {
        // Validate request method
        if (!$request->isMethod('GET')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        // Get search query parameter
        $search = $request->input('search');

        // Retrieve categories based on search query
        $categories = Category::select(
            'tbl_category.cid as category_id',
            'tbl_category.category_name',
            'tbl_category.category_image',
            Gallery::raw('COUNT(DISTINCT tbl_gallery.id) as total_wallpaper')
        )
            ->leftJoin('tbl_gallery', 'tbl_category.cid', '=', 'tbl_gallery.cat_id')
            ->where('tbl_category.category_status', 1)
            ->where('tbl_category.category_name', 'like', "%$search%")
            ->groupBy('tbl_category.cid')
            ->orderBy('tbl_category.cid', 'DESC')
            ->get();

        // Prepare response
        $response = [
            'status' => 'ok',
            'count' => $categories->count(),
            'categories' => $categories
        ];

        return response()->json($response, 200);
    }

    public function update_view(Request $request)
    {
        // Validate request method
        if (!$request->isMethod('POST')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        // Retrieve image_id from the request
        $image_id = $request->input('image_id');

        // Find the gallery record by id and update the view count
        $gallery = Gallery::find($image_id);
        if ($gallery) {
            $gallery->view_count += 1;
            $gallery->save();

            return response()->json(['response' => 'View updated']);
        } else {
            return response()->json(['response' => 'Failed'], 404);
        }
    }

    public function update_download(Request $request)
    {
        // Validate request method
        if (!$request->isMethod('POST')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        // Retrieve image_id from the request
        $image_id = $request->input('image_id');

        // Find the gallery record by id and update the download count
        $gallery = Gallery::find($image_id);
        if ($gallery) {
            $gallery->download_count += 1;
            $gallery->save();

            return response()->json(['response' => 'Download updated']);
        } else {
            return response()->json(['response' => 'Failed'], 404);
        }
    }

    public function get_ads(Request $request)
    {
        if ($request->method() != 'GET') {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $ads = Ad::all();
        $adsStatus = AdsStatus::first();

        return response()->json([
            'status' => 'ok',
            'ads' => $ads,
            'ads_status' => $adsStatus,
        ], 200);
    }
    public function get_settings(Request $request)
    {
        // Check if the request method is GET
        if (!$request->isMethod('get')) {
            return response('', 406);
        }

        // Get the package name from the request
        $package_name = $request->input('package_name');

        // Retrieve data from database using Eloquent models
        $settings = Setting::all()->first();
        $ads = Ad::all()->first();
        $ads_status = AdsStatus::all()->first();
        $ads_placement = AdsPlacement::all()->first();
        $app = AppConfig::where('package_name', $package_name)->first();
        $menus = Menu::where('menu_status', 1)->orderBy('menu_id', 'asc')->get();
        $license = License::all()->first();

        // Check if AppConfig data exists for the provided package name
        if ($app) {
            $app_data = $app->toArray();
        } else {
            $app_data = [
                'package_name' => '',
                'status' => '',
                'redirect_url' => '',
            ];
        }

        // Prepare the response array
        $response = [
            'status' => 'ok',
            'app' => $app_data,
            'menus' => $menus,
            'settings' => $settings,
            'ads' => $ads,
            'ads_status' => $ads_status,
            'ads_placement' => $ads_placement,
            'license' => $license,
        ];

        // Return the response as JSON
        return response()->json($response, 200);
    }
}
