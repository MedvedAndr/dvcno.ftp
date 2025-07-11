<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Cookie;
// use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\JsonResponse;

class ComponentController extends Controller {
    public function getItem(Request $request): JsonResponse {
        $data = $request->data ?? [];
        $response = [
            'status' => 'success',
            'data' => [],
            'meta' => [
                'component' => $request->component,
                'index'     => (int) $request->index,
                'form_data' => $data,
            ],
            'debug' => $data['elements'] ?? []
        ];

        if(in_array($response['meta']['component'], ['add-term', 'add-permalink', 'add-list-link', 'add-list-block', 'add-list-doc', 'add-list-video', 'add-list-accordion'])) {
            foreach(app('languages') as $language) {
                $form_data = $data['elements'][$language['locale_code']] ?? [];
                $form_data['language_id'] = $language['aid'];
                $response['data'][$language['locale_code']] = view('components.items.'. $request->component, [
                    'index' => (int) $request->index,
                    'locale' => $language['aid'],
                    'form_data' => $form_data,
                ])->render();
            }
        }
        else {
            $response['data'] = view('components.items.'. $request->component, [
                'index' => (int) $request->index,
                'form_data' => $request->data['elements'] ?? null,
            ])->render();
        }

        return response()->json($response);
    }

    // public function getComponent(Request $request): JsonResponse {
    //     $data = $request->data ?? [];
    //     $response = [
    //         'status' => 'success',
    //         'data' => [],
    //         'meta' => [
    //             'component' => $request->component,
    //             'data' => $request->data,
    //         ],
    //     ];

    //     $response['data'] = view('components.'. $request->component, $data)->render();

    //     return response()->json($response);
    // }

    public function getComponent(Request $request): JsonResponse {
        $response = [
            'status' => 'success',
            'data' => [],
            'meta' => $request->only([
                'component',
                'index',
                'data',
                'multi_language'
            ])
        ];

        if($response['meta']['multi_language']) {
            foreach(app('languages') as $language) {
                $response['data'][$language['locale_code']] = view('components.'. $response['meta']['component'], [
                    'index' => $response['meta']['index'],
                    'locale' => $language['aid'],
                    'form_data' => $response['meta']['data']['elements'][$language['locale_code']] ?? null
                ])->render();
            }
        }
        else {
            $response['data'] = view('components.'. $response['meta']['component'], [
                'index' => $response['meta']['index'],
                'form_data' => $response['meta']['data']['elements'] ?? null
            ])->render();
        }

        return response()->json($response);
    }
}