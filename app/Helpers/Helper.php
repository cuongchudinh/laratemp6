<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade as PDF;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

class Helper
{

    public function formatTimeJPStyle($datetime, $hasSecond = true)
    {
        $week_map = ['日', '月', '火', '水', '木', '金', '土'];
        $datetime_str = date_format($datetime,  'Y年m月d日|w|h時i分' . ($hasSecond ? 's秒' : ''));
        $format_array = explode('|', $datetime_str);
        $datime_output = [
            'day' => $format_array[0],
            'week' => $week_map[$format_array[1]],
            'time' => $format_array[2]
        ];
        return $datime_output;
    }

    public function uploadImage($request, $field, $path)
    {
        if (!$request->hasFile($field)) return null;
        $file = $request->file($field);
        // return $this->uploadToS3($path, $file);
        return $this->uploadToLocal($path, $file);
    }

    public function downloadPdf($view, $data = null, $filename = 'default.pdf')
    {
        $pdf = PDF::loadView($view, compact('data'));
        return $pdf ? $pdf->download($filename) : null;
    }

    private function uploadToS3($path, $file)
    {
        $fileName = $file->getClientOriginalName();
        $fullpath = $path . '/' . $fileName;
        Storage::disk('s3')->put($fullpath, file_get_contents($file), 'public');
        if (Storage::disk('s3')->exists($fullpath)) {
            return Storage::disk('s3')->url($fullpath);
        }
        return null;
    }

    private function uploadToLocal($path, $file)
    {
        $fileName = $file->getClientOriginalName();
        if (!$file->move($path, $fileName)) return null;
        return url($path) . '/' . $fileName;
    }

    /**
     * How to use:
     * $body = [
     *    'form_params' => [
     *        'name' => 'test'
     *     ],
     *    'auth' => [$client_id, $client_secret]
     * ];
     */
    public function callAPI($method, $url, $body = [])
    {
        $client = new Client();
        $res = $client->request($method, $url, $body);
        $result = $res->getBody()->getContents();
        return json_decode($result, true);
    }
    
    /* Redis */
    public function searchFromRedis($key) {
        return Redis::get($key);
    }

    public function saveToRedis($key, $value) {
        Redis::set($key, $value);
    }

}
