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

            $types = [
                'ru' => [
                    'page' => 'Страница',
                    'news' => 'Новость',
                    'event' => 'Мероприятие',
                ],
                'en' => [
                    'page' => 'Page',
                    'news' => 'News',
                    'event' => 'Event',
                ],
            ];

            // Определение и установка языка
            $response['meta']['lang'] = $response['meta']['lang'] ?? 'ru';
            
            // Шаблон запрашиваемого значения
            $value = "%". $response['meta']['value'] ."%";
            
            // Запрос для страниц
            $pages_query = DB::table('pages as p')
                ->select(
                    'p.aid as aid',
                    DB::raw("'page' as type"),
                    DB::raw("'". $types[$response['meta']['lang']]['page'] ."' as type_translate"),
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
                        ->orWhereRaw("REGEXP_REPLACE(REGEXP_REPLACE(s.content, '<[^>]*>', ' '), '[[:space:]]+', ' ') LIKE ?", [$value]);
                });

            // Запрос для новостей
            $news_query = DB::table('news as n')
                ->select(
                    'n.aid as aid',
                    DB::raw("'news' as type"),
                    DB::raw("'". $types[$response['meta']['lang']]['news'] ."' as type_translate"),
                    DB::raw("CONCAT('/news/', n.slug) as url"),
                    'n.title as title',
                    'n.content as content',
                    'n.description as description'
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

            // Запрос для мероприятий
            $events_query = DB::table('events as e')
                ->select(
                    'e.aid as aid',
                    DB::raw("'event' as type"),
                    DB::raw("'". $types[$response['meta']['lang']]['event'] ."' as type_translate"),
                    DB::raw("CONCAT('/event/', e.slug) as url"),
                    'e.title as title',
                    'e.content as content',
                    'e.description as description'
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


            // Собираем запросы в UNION ALL
	        $final_query = $pages_query
                ->unionAll($news_query)
                ->unionAll($events_query);

            // Применяем фильтры и сортировку
            $data = DB::query()
                ->fromSub($final_query, 'u')
                ->orderBy('title')
                ->get();

            // Парсим данные
            $data_parse = [];

            foreach($data as $item) {
                if(!isset($data_parse[$item->aid])) {
                    $data_parse[$item->aid] = [
                        'aid'               => $item->aid,
                        'type'              => $item->type,
                        'type_translate'    => $item->type_translate,
                        'url'               => $item->url,
                        'title'             => $item->title,
                        'content'           => [],
                        'description'       => $item->description,
                    ];
                };

                $data_parse[$item->aid]['content'][] = $item->content;
            }

            $data_parse = array_values($data_parse);
            array_splice($data_parse, 50);

            // Формируем ответ
            $response['data'] = $data_parse;
            
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
