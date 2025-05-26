<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Services\GenerateID;

use App\Models\Users;
use App\Models\BindUserRole;
use App\Models\Dictionaries;
use App\Models\DictionaryItems;

class AuthController extends Controller {
    public function formVerification(Request $request) {
        $return = [
            'status' => 'error',
            'error' => 'Not valid form',
            'meta' => $request->only([
                '__form_name',
                '__form_errors',
            ]),
        ];

        if(isset($return['meta']['__form_errors'])) {
            $return['meta']['__form_errors'] = array_map(function($error_key) {
                return (app('dictionary'))->dictionary('system_messages')->key($error_key)->get();
            }, $return['meta']['__form_errors']);
        }
        
        switch($return['meta']['__form_name']) {
            case 'login':
                $data = $request->only([
                    'login',
                    'password',
                ]);

                if(!isset($return['meta']['__form_errors']['login']) && !isset($return['meta']['__form_errors']['password'])) {
                    $user = Users::where('login', $data['login'])
                        ->orWhere('email', $data['login'])
                        ->first();
                    
                    if($user && Hash::check($data['password'], $user->password)) {
                        $hasAccess = BindUserRole::from('bind_user_role as bur')
                            ->join('bind_role_access as bra', 'bur.role_id', '=', 'bra.role_id')
                            ->join('accesses as a', 'bra.access_id', '=', 'a.aid')
                            ->where('bur.user_id', '=', $user->aid)
                            ->where('a.name', '=', 'admin_panel')
                            ->where('bra.enabled', '=', 1)
                            ->exists();

                        if($hasAccess) {
                            $return['status'] = 'success';
                            $return['data'] = $data;
                            $return['data']['password'] = '********';
                            unset($return['error']);

                            $return['meta']['__form_messages']['__form_message'] = (app('dictionary'))->dictionary('system_messages')->key('success_sign_in')->get();

                            session()->put('user', [
                                'aid' => $user->aid,
                            ]);
                        }
                        else {
                            $return['error'] = '403 Access denied';
                            $return['meta']['__form_errors']['__form_message'] = (app('dictionary'))->dictionary('system_messages')->key('admin_access_denied')->get();
                        }
                    }
                    else {
                        $return['error'] = 'Incorrect data';
                        $return['meta']['__form_errors']['__form_message'] = (app('dictionary'))->dictionary('system_messages')->key('invalid_sign_in')->get();
                    }
                }
                else {
                    $return['error'] = 'Incorrect data';
                }

                break;
            case 'create_menu':
                $data = $request->all();
                $return['status'] = 'success';
                $return['data'] = $data;
                unset($return['error']);
                
                break;
            case 'create_dictionary':
                $data = $request->only([
                    'dictionary',
                    'terms',
                ]);

                $dictionaries_empty_flag = true;

                $data_dictionaries = [];
                $data_dictionary_items = [];

                foreach($data['dictionary'] as $language_aid => &$data_dictionary) {
                    if($data_dictionary['required'] === 'true' && ($data_dictionary['name'] || $data_dictionary['description'] || $data_dictionary['alias'])) {
                        // $dictionaries_empty_flag = true;
                        
                        if(!$data_dictionary['name']) {
                            $return['error'] = 'Incorrect data';
                            $return['meta']['__form_errors']['dictionary['. $language_aid .'][name]'] = 'Поле обязательно для заполнения';
                        }

                        if(!$data_dictionary['alias']) {
                            $return['error'] = 'Incorrect data';
                            $return['meta']['__form_errors']['dictionary['. $language_aid .'][alias]'] = 'Поле обязательно для заполнения';
                        }

                        $dictionary_aid = (new GenerateID())->table('dictionaries')->get();

                        $data_dictionaries[] = [
                            'aid'           => $dictionary_aid,
                            'language_id'   => $language_aid,
                            'name'          => $data_dictionary['name'],
                            'description'   => $data_dictionary['description'] ?? '',
                            'alias'         => $data_dictionary['alias'],
                        ];

                        foreach($data['terms'] as $dictionary_item) {
                            $dictionary_item[$language_aid];
                        }
                    }
                    elseif($data_dictionary['required'] === 'true' && (!$data_dictionary['name'] && !$data_dictionary['description'] && !$data_dictionary['alias'])) {
                        $return['meta']['__set_data']['dictionary['. $language_aid .'][required]'] = 'false';
                    }
                }

                if($dictionaries_empty_flag) {
                    $return['status'] = 'success';
                    $return['data'] = $data;
                    $return['meta']['debug'] = $data_dictionaries;
                    unset($return['error']);
                }
                else {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['__form_message'] = 'Нужно заполнить хотя бы 1 язык.';
                }

                // $return['status'] = 'success';
                // $return['data'] = $data;
                // unset($return['error']);
                
                // $return['meta']['debug'] = Dictionaries::insert($data_dictionaries);
                
                break;
        }

        return $return;
    }
}