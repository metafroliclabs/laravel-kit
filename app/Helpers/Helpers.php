<?php

use App\Helpers\Constant;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

// function apiResponse($status, $message, $http = 200, $data = null)
// {
//     $response['status'] = $status;
//     $response['message'] = $message;
//     if (!is_null($data) && !empty($data)) {
//         $response['data'] = $data;
//     }
//     return response()->json($response, $http);
// }

// function customResponse($status, $msg, $http = 200, $data = [])
// {
//     return [
//         'status' => $status,
//         'message' => $msg,
//         'http' => $http,
//         'data' => $data,
//     ];
// }

function generateFilename($prefix, $extension, $key)
{
    $datetime = date("YmdHis");
    return sprintf('%s_%s%d.%s', $prefix, $datetime, $key, $extension);
}

function uploadFile($file, $prefix = "Img", $folder = "upload", $key = 0)
{
    $directory = "public/{$folder}/";

    if (!Storage::exists($directory)) {
        Storage::makeDirectory($directory, 0755, true);
    }
    
    $filename = generateFilename($prefix, $file->extension(), $key);
    $path = $file->storeAs($directory, $filename);

    if ($path) {
        return ['status' => true, 'data' => "storage/{$folder}/{$filename}"];
    }
    throw new Exception("File could not upload");
}

function deleteFile($file)
{
    if ($file !== Constant::DEFAULT_AVATAR) {
        $filePath = public_path($file);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
    return ['status' => true];
}

function uploadMultipleFiles($files, $prefix = "Post", $folder = 'posts')
{
    $response = [];
    foreach ($files as $key => $file) {
        $uploadedFile = uploadFile($file, $prefix, $folder, $key);

        if ($uploadedFile['status']) {
            $response[] = [
                'file' => $uploadedFile['data'],
                'type' => $file->extension()
            ];
        }
    }
    return ['status' => true, 'data' => $response];
}

function deleteMultipleFiles($media, $filesToKeep = [], $folderPath = '')
{
    foreach ($media as $file) {
        $keepFile = collect($filesToKeep)->contains('id', $file->id);
        
        if (!$keepFile) {
            deleteFile($folderPath . $file->file);
            // $file->delete();
        }
    }
    return ['status' => true, 'message' => "Files removed successfully"];
}

function formatDate($str)
{
    $date = new DateTime($str);
    $formatted = $date->format('d/m/Y');
    return $formatted;
}

function formatTime($str)
{
    $time = new DateTime($str);
    $formatted = $time->format('H:i A');
    return $formatted;
}

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
