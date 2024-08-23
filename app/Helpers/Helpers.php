<?php

use App\Helpers\Constant;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

function apiResponse($status, $message, $http = 200, $data = null)
{
    $response['status'] = $status;
    $response['message'] = $message;
    if (!is_null($data) && !empty($data)) {
        $response['data'] = $data;
    }
    return response()->json($response, $http);
}

function customResponse($status, $msg, $http = 200, $data = [])
{
    return [
        'status' => $status,
        'message' => $msg,
        'http' => $http,
        'data' => $data,
    ];
}

function generateFilename($prefix, $extension, $key)
{
    return $prefix . '_' . date("YmdHis") . $key . '.' . $extension;
}

function uploadFile($file, $prefix = "Img", $folder = "upload", $key = 0)
{
    $filename = generateFilename($prefix, $file->extension(), $key);
    $path = $file->storeAs("public/{$folder}/", $filename);
    return $path ? "storage/{$folder}/{$filename}" : null;
}

function deleteFile($file)
{
    if ($file !== Constant::DEFAULT_AVATAR) {
        $filePath = public_path($file);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
    return true;
}

function uploadMultipleFiles($files, $prefix = "Post", $folder = 'posts')
{
    $response = [];
    foreach ($files as $key => $file) {
        $filePath = uploadFile($file, $prefix, $folder, $key);

        if ($filePath) {
            $response[] = [
                'file' => $filePath,
                'type' => $file->extension()
            ];
        } else {
            return customResponse(false, "Error uploading files", 500);
        }
    }
    return customResponse(true, "Files uploaded successfully", 200, $response);
}

function deleteMultipleFiles($media, $filesToKeep = [], $folderPath = '')
{
    foreach ($media as $file) {
        $keepFile = collect($filesToKeep)->contains('id', $file->id);
        
        if (!$keepFile) {
            deleteFile($folderPath . $file->file);
            $file->delete();
        }
    }
    return customResponse(true, "Files removed successfully");
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
