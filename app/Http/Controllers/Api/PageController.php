<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactUsRequest;
use App\Models\ContactUs;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function contact_us(ContactUsRequest $request){
        $data = ContactUs::create($request->all());
        return apiResponse(true, "Message sent successfully.");
    }

    public function get_page(Request $request){
        if ($request->type == 'terms') {
            $page = Page::where('slug', 'terms')->first();

        } else if ($request->type == 'privacyPolicy') {
            $page = Page::where('slug', 'privacy')->first();

        } else {
            $page = "";
        }
        return apiResponse(true, "Page data", 200, $page);
    }
}
