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

        //file_put_contents("./log.txt", config("mail.mailers.smtp.host"));
        //file_put_contents("log.txt", "test", FILE_APPEND);
        //file_put_contents("log.txt", env("MAIL_HOST"), FILE_APPEND);            
        if (Mail::to(env("MAIL_FROM_ADDRESS"))->send(new sendMail($query_data))) {
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

            $newsQuery = DB::table('news as n')  // Добавляем ассоциацию для таблицы news
                ->select(
                    DB::raw("CONCAT('/news/', n.slug) as url"),  // Формируем URL из slug
                    'n.title as title',                          // Переименовываем title
                    'n.content as content',                      // Переименовываем content
                    'n.description as description'               // Переименовываем description
                )
                ->where(function($query) use ($value) {
                    $query->where('n.title', 'like', $value)
                        ->orWhere('n.description', 'like', $value)
                        ->orWhere('n.content', 'like', $value);
                });

            $eventsQuery = DB::table('events as e')  // Добавляем ассоциацию для таблицы news
                ->select(
                    DB::raw("CONCAT('/events/', e.slug) as url"),  // Формируем URL из slug
                    'e.title as title',                          // Переименовываем title
                    'e.content as content',                      // Переименовываем content
                    'e.description as description'               // Переименовываем description
                )
                ->where(function($query) use ($value) {
                    $query->where('e.title', 'like', $value)
                        ->orWhere('e.description', 'like', $value)
                        ->orWhere('e.content', 'like', $value);
                });


	    $finalQuery = $newsQuery
                ->union($eventsQuery);

            $data = $finalQuery
                ->orderBy('title')
                ->limit(10)
                ->get();

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
