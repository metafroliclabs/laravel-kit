<?php

namespace App\Services\Core;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class BaseService
{
    protected function findOrFail($model, $closure)
    {
        $query = $model::query();

        $closure($query);

        $data = $query->first();

        if (!$data)
            $table = class_basename($model);
            throw new ModelNotFoundException("Invalid ID, {$table} not found.");

        return $data;
    }
}