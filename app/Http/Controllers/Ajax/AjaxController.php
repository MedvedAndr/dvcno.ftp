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
            
        //file_put_contents("log.txt", print_r($query_data, true));
        if (Mail::to("mmvova@yandex.ru")->send(new sendMail($query_data))) {
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
                    'lang'
                ]),
            ];

            
            $value = "%". $response['meta']['value'] ."%";
            
            $pages_query = DB::table('pages as p')
                ->select(
                    'p.front_url as url',
                    'p.title as title',
                    's.content as content',
                    DB::raw("'' as description")
                )
                ->join('sections as s', function($join) {
                    $join
                        ->on('p.aid', '=', 's.page_id')
                        ->on('p.language_id', '=', 's.language_id');
                })
                ->join('languages as l', function($join) {
                    $join
                        ->on('p.language_id', '=', 'l.aid');
                })
                ->where('l.locale_code', '=', $response['meta']['lang'])
                ->where(function($query) use ($value) {
                    $query
                        ->where('p.title', 'like', $value)
                        // ->orWhereJsonContains('s.content', $value);
                        ->orWhereRaw("REGEXP_REPLACE(REGEXP_REPLACE(s.content, '<[^>]*>', ' '), '[[:space:]]+', ' ') LIKE ?", [$value]);
                });

            $news_query = DB::table('news as n')  // Добавляем ассоциацию для таблицы news
                ->select(
                    DB::raw("CONCAT('/news/', n.slug) as url"),  // Формируем URL из slug
                    'n.title as title',                          // Переименовываем title
                    'n.content as content',                      // Переименовываем content
                    'n.description as description'               // Переименовываем description
                )
                ->join('languages as l', function($join) {
                    $join
                        ->on('n.language_id', '=', 'l.aid');
                })
                ->where('l.locale_code', '=', $response['meta']['lang'])
                ->where(function($query) use ($value) {
                    $query->where('n.title', 'like', $value)
                        ->orWhere('n.description', 'like', $value)
                        ->orWhere('n.content', 'like', $value);
                });

            $events_query = DB::table('events as e')  // Добавляем ассоциацию для таблицы news
                ->select(
                    DB::raw("CONCAT('/event/', e.slug) as url"),  // Формируем URL из slug
                    'e.title as title',                          // Переименовываем title
                    'e.content as content',                      // Переименовываем content
                    'e.description as description'               // Переименовываем description
                )
                ->join('languages as l', function($join) {
                    $join
                        ->on('e.language_id', '=', 'l.aid');
                })
                ->where('l.locale_code', '=', $response['meta']['lang'])
                ->where(function($query) use ($value) {
                    $query->where('e.title', 'like', $value)
                        ->orWhere('e.description', 'like', $value)
                        ->orWhere('e.content', 'like', $value);
                });


	        $final_query = $pages_query
                ->unionAll($news_query)
                ->unionAll($events_query);

            $data = $final_query
                ->orderBy('title')
                ->limit(50)
                ->get();
            
            $response['data'] = $data->toArray();
            // 
            // $data = $finalQuery
            //     ->orderBy('title')
            //     ->limit(10);

            return $response;
            //return $data->toSql();
        }
        catch(\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage(),
            ]);
        }
    }
}
