<?php

use Illuminate\Support\Facades\Http;

// api response function
function apiResponse($status, $message, $http = 200, $data = null)
{
    $response['status'] = $status;
    $response['message'] = $message;
    if (!is_null($data) && !empty($data)) {
        $response['data'] = $data;
    }

    return response()->json($response, $http);
}

// function response
function customResponse($status, $msg, $http = 200, $data = [])
{
    return [
        'status' => $status,
        'message' => $msg,
        'http' => $http,
        'data' => $data,
    ];
}

// upload single file
function uploadFile($file, $pre = "Img", $path = "upload")
{
    $filename = $pre .'_'. date("YmdHis") .'.'. $file->extension();
    if ($path = $file->storeAs("public/{$path}/", $filename)) {
        return "storage/{$path}/{$filename}";
    }
}

// upload more than one file
function uploadManyFiles($files, $pre = "Img", $path = "upload")
{
    $allowedExtensions = ['jpg', 'png', 'jpeg'];

    foreach ($files as $key => $file) {
        $extension = $file->extension();

        if (in_array($extension, $allowedExtensions)) {
            $file_name = $pre .'_'. date("YmdHisu") . $key .'.'. $extension;
            if ($path = $file->storeAs("public/{$path}/", $file_name)) {
                $file_info['name'] = $file_name;
                $file_info['type'] = $extension;
                $response[] = $file_info;
            }
        } else {
            return customResponse(false, "Only jpg, png, jpeg files are allowed");
        }
    }
    return customResponse(true, "Files uploaded successfully", 200, $response);
}

// format date from string
function formatDate($str)
{
    $date = new DateTime($str);
    $formatted = $date->format('d/m/Y');
    return $formatted;
}

// format time from string
function formatTime($str)
{
    $time = new DateTime($str);
    $formatted = $time->format('H:i A');
    return $formatted;
}

// send push notification
function sendPushNotification($user, $title, $body, $id = 1)
{
    if ($user->device_tokens->count() > 0){
        $ids = $user->device_tokens->pluck('device_id')->toArray();
        $fields = [
            "registration_ids" => $ids,
            "notification" => [
                "title" => $title,
                "body" => $body,
            ],
            "data" => ["key" => $id]
        ];

        $response = Http::withBody(json_encode($fields))->withHeaders([
            'Authorization' => 'key='. env('FIREBASE_SERVER_KEY'),
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send');

        return $response->json();
    }
}
