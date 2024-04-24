<?php

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

// upload single image
function uploadImage($image, $pre = "")
{
    $filename = $pre . date("YmdHis") . '.' . $image->extension();
    if ($path = $image->storeAs('public/upload/', $filename)) {
        return "storage/upload/{$filename}";
    }
}

// upload more than one images
function uploadManyImages($images)
{
    $allowedExtensions = ['jpg', 'png', 'jpeg'];

    foreach ($images as $key => $image) {
        $extension = $image->extension();

        if (in_array($extension, $allowedExtensions)) {
            $image_name = 'ads_' . date("YmdHisu") . $key . '.' . $extension;
            if ($path = $image->storeAs('public/', $image_name)) {
                $image_info['name'] = $image_name;
                $image_info['type'] = $extension;
                $response[] = $image_info;
            }
        } else {
            return customResponse(false, "Only jpg, png, jpeg files are allowed");
        }
    }
    return customResponse(true, "Images uploaded successfully", 200, $response);
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
