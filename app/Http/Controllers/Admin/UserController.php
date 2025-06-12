<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Constant;
use App\Http\Controllers\MainController;
use App\Http\Resources\DefaultResource;
use App\Models\User;
use App\Services\Core\FilterService;
use Illuminate\Http\Request;

class UserController extends MainController
{
    public function index(Request $request)
    {
        $request->merge(['search_col' => ['concat_name', 'email'], 'filter' => 'where:role:user']);
        $data = FilterService::getInstance(new User())->run($request, true);
        return $this->response->success(
            DefaultResource::collection($data)->response()->getData(true)
        );
    }

    public function show($id)
    {
        $user = User::whereRole(Constant::USER)->findOrFail($id);
        return $this->response->success(
            new DefaultResource($user)
        );
    }

    public function update_status($id)
    {
        $user = User::whereRole(Constant::USER)->findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return $this->response->success(
            new DefaultResource($user)
        );
    }
}
