<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MainController;
use App\Http\Resources\DefaultResource;
use App\Models\Query;
use App\Services\Core\FilterService;
use Illuminate\Http\Request;

class QueryController extends MainController
{
    public function index(Request $request)
    {
        $request->merge([
            'search_col' => ['name', 'email', 'subject'],
            'status_col' => 'user_type'
        ]);
        $data = FilterService::getInstance(new Query())->run($request, true);
        return $this->response->success(
            DefaultResource::collection($data)->response()->getData(true)
        );
    }

    public function show(Query $query)
    {
        return $this->response->success(
            new DefaultResource($query)
        );
    }
}
