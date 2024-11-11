<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\MainController;
use App\Http\Requests\Common\FeedbackRequest;
use App\Models\Feedback;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends MainController
{
    public function contact_us(FeedbackRequest $request){
        $data = Feedback::create($request->all());
        return $this->response->successMessage("Message sent successfully.");
    }

    public function get_page(Request $request){
        if ($request->type == 'termsCondition') {
            $page = Page::where('slug', 'terms-and-condition')->first();

        } else if ($request->type == 'privacyPolicy') {
            $page = Page::where('slug', 'privacy-policy')->first();

        } else if ($request->type == 'aboutUs') {
            $page = Page::where('slug', 'about-us')->first();

        } else {
            $page = "";
        }
        return $this->response->success($page);
    }
}
