<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Helpers\AssetsManager;
use App\Services\GenerateID;

class CheckUserAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dump((new GenerateID())->table('dictionary_items')->get());
        $view_data       = [];
        $user_session = $request->session()->get('user', false);
        $auth_action = $request->query('auth');
        $version = '0.1.1';
        
        if($user_session && $auth_action === 'sign_out') {
            $request->session()->forget('user');
            
            $get = $request->query();
            unset($get['auth']);

            $redirectUrl = $request->url() . (!empty($get) ? '?' . http_build_query($get, '', '&', PHP_QUERY_RFC3986) : '');
            
            return redirect($redirectUrl);
        }
        elseif(!$user_session) {
            switch($auth_action) {
                case 'register':
                    dump(1, $auth_action);
                    return response('');
                case 'forgot':
                    dump(2, $auth_action);
                    return response('');
                default:
                    AssetsManager::unsetStyle(asset('/css/nav_panel.css'));

                    AssetsManager::useBundle('form');
                    
                    AssetsManager::setStyle([
                        'href'      => asset('/css/sections/sign_in.css'),
                        'priority'  => 600,
                    ]);
                    
                    // $view_data['title']  = $view_data['dictionary']->key('admin_panel')->get();
                    $view_data['title']  = 'Вход';
                    // $view_data['user']                   = false;
                    $view_data['nav_panel_visibility']   = false;
                    $view_data['header_template']        = false;
                    // $view_data['footer_template']        = false;

                    $template    = view('admin.header', $view_data);
                    $template   .= view('auth.login', $view_data);
                    $template   .= view('admin.footer', $view_data);

                    return response($template);
            }
        }

        return $next($request);
    }
}
