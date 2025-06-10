<?php

namespace App\Http\Controllers\Ajax;

// use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
// use Illuminate\Http\UploadedFile;

use App\Mail\SendMail;

use Illuminate\Support\Facades\DB;
// use App\Models\User;
// use App\Models\BindRouteBlock;
// use App\Models\Blocks;
// use App\Models\BindBlockAttribute;

class AjaxController 
{
    public function sendMail(Request $query_data) {
            
        if (Mail::to("undead_medved@mail.ru")->send(new sendMail($query_data))) {
            return "success";
        } else {
            return "fail";
        }
    }

    public function get_search_info(Request $query_data) {
        try {
            $response = [
                'status' => 'success',
                'data' => [],
                'meta' => $query_data->only([
                    'value',
                ]),
            ];
            
            // $pages_query = DB::table('pages as p')
            //     ->select(
            //         'p.front_url as url',
            //         'p.title as title',
            //         's.content as content',
            //         DB::raw("'' as description")
            //     )
            //     ->join('sections as s', function($join) {
            //         $join
            //             ->on('p.aid', '=', 's.page_id')
            //             ->on('p.language_id', '=', 's.language_id');
            //     })
            //     ->where(function($query) use ($value) {
            //         $query
            //             ->where('p.title', 'like', $value)
            //             ->orWhereJsonContains('s.content', $value);
            //     });

            // $newsQuery = DB::table('news as n')  // Добавляем ассоциацию для таблицы news
            //     ->select(
            //         DB::raw("CONCAT('/news/', n.slug) as url"),  // Формируем URL из slug
            //         'n.title as title',                          // Переименовываем title
            //         'n.content as content',                      // Переименовываем content
            //         'n.description as description'               // Переименовываем description
            //     )
            //     ->where(function($query) use ($value) {
            //         $query->where('n.title', 'like', $value)
            //             ->orWhere('n.description', 'like', $value)
            //             ->orWhere('n.content', 'like', $value);
            //     });

            // $final_query = $pages_query
            //     ->union($news_query)
            //     ->union($events_query);

            // $data = $final_query
            //     ->orderBy('title')
            //     ->limit(10)
            //     ->get();

            return $response;
        }
        catch(\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage(),
            ]);
        }
    }
}
