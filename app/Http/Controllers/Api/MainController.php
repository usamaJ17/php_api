<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    public function get_wallpapers(Request $request)
    {
        $limit = $request->input('count', 10);
        $page = $request->input('page', 1);
        $order = $request->input('order');
        $filter = $request->input('filter');

        $offset = ($page - 1) * $limit;

        $query = Gallery::with('category')
            ->join('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->where('c.category_status', 1)
            ->where('tbl_gallery.image_status', 1);

        if (!empty($filter)) {
            $query->whereRaw($filter);
        }

        if (!empty($order)) {
            $query->whereRaw($order);
        }

        $result = $query->skip($offset)
            ->take($limit)
            ->get();

        $count_total = Gallery::join('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->where('c.category_status', 1)
            ->where('tbl_gallery.image_status', 1);

        if (!empty($filter)) {
            $count_total->whereRaw($filter);
        }

        if (!empty($order)) {
            $count_total->whereRaw($order);
        }

        $count_total = $count_total->distinct('tbl_gallery.id')
            ->count();

        $count = $result->count();

        $response = [
            'status' => 'ok',
            'count' => $count,
            'count_total' => $count_total,
            'pages' => $page,
            'posts' => $result
        ];

        return response()->json($response, 200);
    }

    public function get_new_wallpapers(Request $request)
    {
        $limit = $request->input('count', 10);
        $page = $request->input('page', 1);
        $order = $request->input('order', 'recent');
        $filter = $request->input('filter', 'all');
        $category = $request->input('category', '0');

        $offset = ($page - 1) * $limit;

        switch ($order) {
            case 'oldest':
                $sqlOrder = "tbl_gallery.id ASC";
                break;
            case 'featured':
                $sqlOrder = "tbl_gallery.featured = 'yes', tbl_gallery.last_update DESC";
                break;
            case 'popular':
                $sqlOrder = "tbl_gallery.view_count DESC";
                break;
            case 'download':
                $sqlOrder = "tbl_gallery.download_count DESC";
                break;
            case 'random':
                $sqlOrder = "RAND()";
                break;
            default:
                $sqlOrder = "tbl_gallery.id DESC";
                break;
        }

        switch ($filter) {
            case 'wallpaper':
                $sqlFilter = "tbl_gallery.image_extension != 'image/gif' AND tbl_gallery.image_extension != 'application/octet-stream'";
                break;
            case 'live':
                $sqlFilter = "tbl_gallery.image_extension = 'image/gif' OR tbl_gallery.image_extension = 'application/octet-stream'";
                break;
            case 'both':
                $sqlFilter = "tbl_gallery.image_extension != 'all'";
                break;
            default:
                $sqlFilter = "tbl_gallery.image_extension != 'all'";
                break;
        }

        $sqlCategory = ($category == '0') ? "tbl_gallery.cat_id != '0'" : "tbl_gallery.cat_id = '$category'";

        $count_total = Gallery::join('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->where('c.category_status', 1)
            ->where('tbl_gallery.image_status', 1)
            ->whereRaw($sqlFilter)
            ->whereRaw($sqlCategory)
            ->distinct('tbl_gallery.id')
            ->count('tbl_gallery.id');

        $query = Gallery::select('tbl_gallery.id AS image_id', 'tbl_gallery.image_name', 'tbl_gallery.image AS image_upload', 'tbl_gallery.image_thumb', 'tbl_gallery.image_url', 'tbl_gallery.type', 'tbl_gallery.image_resolution AS resolution', 'tbl_gallery.image_size AS size', 'tbl_gallery.image_extension AS mime', 'tbl_gallery.view_count AS views', 'tbl_gallery.download_count AS downloads', 'tbl_gallery.featured', 'tbl_gallery.tags', 'c.cid AS category_id', 'c.category_name', 'tbl_gallery.rewarded', 'tbl_gallery.last_update')
            ->join('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->where('c.category_status', 1)
            ->where('tbl_gallery.image_status', 1)
            ->whereRaw($sqlFilter)
            ->whereRaw($sqlCategory)
            ->orderByRaw($sqlOrder)
            ->limit($limit)
            ->offset($offset)
            ->get();

        $count = $query->count();

        $response = [
            'status' => 'ok',
            'count' => $count,
            'count_total' => $count_total,
            'pages' => $page,
            'posts' => $query
        ];

        return response()->json($response, 200);
    }

    public function get_wallpaper_details(Request $request)
    {
        if ($request->method() !== "GET") {
            return response()->json('', 406);
        }

        $id = $request->input('id');

        $wallpaper = Gallery::with('category')
            ->where('id', $id)
            ->first();

        if (!$wallpaper) {
            return response()->json(['status' => 'error', 'message' => 'Wallpaper not found'], 404);
        }

        $response = [
            'status' => 'ok',
            'count' => 1,
            'count_total' => 1,
            'pages' => '1',
            'posts' => $wallpaper
        ];

        return response()->json($response, 200);
    }

    public function get_one_wallpaper(Request $request)
    {
        // if ($request->method() !== "GET") {
        //     return response()->json('', 406);
        // }

        // $id = $request->input('id');

        // $wallpaper = Gallery::with('category')
        //     ->where('id', $id)
        //     ->first();

        // if (!$wallpaper) {
        //     return response()->json(['status' => 'error', 'message' => 'Wallpaper not found'], 404);
        // }

        // $response = [
        //     'status' => 'ok',
        //     'wallpaper' => $wallpaper
        // ];

        // return response()->json($response, 200);

        if ($request->method() !== "GET") {
            return response()->json('', 406);
        }

        $id = $request->input('id');

        // Query the database using the DB facade
        $wallpaper = DB::selectOne("SELECT g.id AS 'image_id', g.image_name, g.image AS 'image_upload', g.image_thumb, g.image_url, g.type, g.image_resolution AS 'resolution', g.image_size AS 'size', g.image_extension AS 'mime', g.view_count AS 'views', g.download_count AS 'downloads', g.featured, g.tags, c.cid AS 'category_id', c.category_name, g.rewarded, g.last_update 
            FROM tbl_category c, tbl_gallery g 
            WHERE c.cid = g.cat_id AND g.id = :id", ['id' => $id]);

        if (!$wallpaper) {
            return response()->json(['status' => 'error', 'message' => 'Wallpaper not found'], 404);
        }

        // Convert stdClass object to associative array
        $wallpaper = (array)$wallpaper;

        $response = [
            'status' => 'ok',
            'wallpaper' => $wallpaper
        ];

        return response()->json($response, 200);
    }

    public function get_categories(Request $request)
    {
        if ($request->method() !== "GET") {
            return response()->json('', 406);
        }

        // Fetch category sorting and ordering settings
        $settings = DB::table('tbl_settings')->select('category_sort', 'category_order')->where('id', 1)->first();
        $sort = $settings->category_sort;
        $order = $settings->category_order;

        // Query categories with total wallpapers
        $categories = Category::select('tbl_category.cid AS category_id', 'tbl_category.category_name', 'tbl_category.category_image')
            ->leftJoin('tbl_gallery', 'tbl_category.cid', '=', 'tbl_gallery.cat_id')
            ->where('tbl_category.category_status', 1)
            ->groupBy('tbl_category.cid')
            ->orderBy($sort, $order)
            ->get();

        // Calculate total wallpapers for each category
        foreach ($categories as $category) {
            $category->total_wallpaper = Gallery::where('cat_id', $category->category_id)->count();
        }

        $count = $categories->count();

        $response = [
            'status' => 'ok',
            'count' => $count,
            'categories' => $categories
        ];

        return response()->json($response, 200);
    }

    // not working
    public function get_category_details(Request $request)
    {
        if ($request->method() !== "GET") {
            return response()->json('', 406);
        }

        $limit = $request->input('count', 10);
        $page = $request->input('page', 1);
        $id = $request->input('id');
        $order = $request->input('order');
        $filter = $request->input('filter');

        $offset = ($page * $limit) - $limit;

        $category = Category::with(['galleries' => function ($query) use ($order, $filter, $limit, $offset) {
            $query->where('image_status', 1)
                ->whereRaw($filter)
                ->orderByRaw($order)
                ->skip($offset)
                ->take($limit);
        }])->findOrFail($id);

        $count_total = $category->galleries()->where('image_status', 1)->whereRaw($filter)->count();

        $posts = $category->galleries;

        $count = $posts->count();

        $response = [
            'status' => 'ok',
            'count' => $count,
            'count_total' => $count_total,
            'pages' => $page,
            'posts' => $posts
        ];

        return response()->json($response, 200);
    }

    public function get_search(Request $request)
    {
        // Check if the request method is GET
        if ($request->method() !== "GET") {
            return response()->json('', 406);
        }
    
        // Retrieve query parameters or set defaults
        $limit = $request->input('count', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $order = $request->input('order', 'recent');
    
        // Determine the SQL order
        $sqlOrder = ($order == 'recent') ? 'DESC' : 'DESC';
    
        // Calculate offset
        $offset = ($page * $limit) - $limit;
    
        // Count total results
        $count_total = Gallery::join('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->where('c.category_status', 1)
            ->where('tbl_gallery.image_status', 1)
            ->where(function ($query) use ($search) {
                $query->where('tbl_gallery.image_name', 'like', '%' . $search . '%')
                    ->orWhere('tbl_gallery.tags', 'like', '%' . $search . '%');
            })
            ->distinct()
            ->count('tbl_gallery.id');
    
        // Retrieve search results
        $posts = Gallery::join('tbl_category as c', 'c.cid', '=', 'tbl_gallery.cat_id')
            ->where('c.category_status', 1)
            ->where('tbl_gallery.image_status', 1)
            ->where(function ($query) use ($search) {
                $query->where('tbl_gallery.image_name', 'like', '%' . $search . '%')
                    ->orWhere('tbl_gallery.tags', 'like', '%' . $search . '%');
            })
            ->select('tbl_gallery.id as image_id', 'tbl_gallery.image_name', 'tbl_gallery.image as image_upload', 'tbl_gallery.image_thumb', 'tbl_gallery.image_url', 'tbl_gallery.type', 'tbl_gallery.image_resolution as resolution', 'tbl_gallery.image_size as size', 'tbl_gallery.image_extension as mime', 'tbl_gallery.view_count as views', 'tbl_gallery.download_count as downloads', 'tbl_gallery.featured', 'tbl_gallery.tags', 'c.cid as category_id', 'c.category_name', 'tbl_gallery.rewarded', 'tbl_gallery.last_update')
            ->orderBy('tbl_gallery.id', $sqlOrder)
            ->skip($offset)
            ->take($limit)
            ->get();
    
        // Count retrieved posts
        $count = count($posts);
    
        // Prepare response data
        $response = [
            'status' => 'ok',
            'count' => $count,
            'count_total' => $count_total,
            'pages' => $page,
            'posts' => $posts
        ];
    
        // Return JSON response
        return response()->json($response, 200);
    }

    public function get_search_category(Request $request)
{
    // Check if the request method is GET
    if ($request->method() !== "GET") {
        return response()->json('', 406);
    }

    // Retrieve search query from request
    $search = $request->input('search');

    // Retrieve categories matching the search query
    $categories = Category::withCount('galleries')
        ->where('category_status', 1)
        ->where('category_name', 'like', '%' . $search . '%')
        ->orderByDesc('cid')
        ->get(['cid as category_id', 'category_name', 'category_image', 'total_wallpaper']);

    // Count retrieved categories
    $count = $categories->count();

    // Prepare response data
    $response = [
        'status' => 'ok',
        'count' => $count,
        'categories' => $categories
    ];

    // Return JSON response
    return response()->json($response, 200);
}

public function update_view(Request $request)
{
    // Retrieve image_id from request
    $image_id = $request->input('image_id');

    // Update view count in the database
    $updated = Gallery::where('id', $image_id)->increment('view_count');

    // Check if the update was successful
    if ($updated) {
        return response()->json(['response' => 'View updated']);
    } else {
        return response()->json(['response' => 'Failed'], 500);
    }
}

public function update_download(Request $request)
{
    // Retrieve image_id from request
    $image_id = $request->input('image_id');

    // Update download count in the database
    $updated = Gallery::where('id', $image_id)->increment('download_count');

    // Check if the update was successful
    if ($updated) {
        return response()->json(['response' => 'Download updated']);
    } else {
        return response()->json(['response' => 'Failed'], 500);
    }
}
}
