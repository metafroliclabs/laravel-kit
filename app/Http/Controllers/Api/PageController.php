<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Constant;
use App\Http\Controllers\MainController;
use App\Http\Requests\Common\QueryRequest;
use App\Http\Resources\DefaultResource;
use App\Models\Page;
use App\Models\Query;
use App\Models\User;
use App\Notifications\DatabaseNotification;
use Illuminate\Http\Request;

class PageController extends MainController
{
    public function contact_us(QueryRequest $request)
    {
        $data = Query::create($request->all());

        // send notification to admin
        $admin = User::whereRole(Constant::ADMIN)->first();

        $message = auth()->user()->first_name . " " . auth()->user()->last_name . " has a query: " . $request->subject;
        $admin->notify(new DatabaseNotification("New Query", $message));

        return $this->response->successMessage("Message sent successfully.");
    }

    public function get_page(Request $request)
    {
        $page = Page::where('slug', $request->type)->first();
        return $this->response->success(new DefaultResource($page));
    }
}
