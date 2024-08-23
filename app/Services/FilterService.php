<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FilterService
{
    private static $model;
    private static $table;

    private function __construct(Model $model)
    {
        self::$model = $model;
        self::$table = $model->getTable(); // getting table name here
    }

    public static function getInstance(Model $model)
    {
        try {
            return new self($model);

        } catch (Exception $e) {
            Log::info("Error catch in " . __FUNCTION__ . " function");
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public static function run($request, $latestPaginate = false)
    {
        try {
            if ($request->exists('with')) self::queryWithRelation($request->with);
            if ($request->exists(['from', 'to'])) self::filterByDate([$request->from, $request->to]);
            if ($request->exists('search', 'search_col')) self::filterBySearch($request->search, $request->search_col);
            if ($request->exists('status', 'status_col')) self::filterByStatus($request->status, $request->status_col);
            if ($request->exists('sortBy')) self::filterBySort($request->sortBy);
            if ($request->exists('filter')) self::filterByColumn($request->filter);

            return $latestPaginate ? self::$model->latest()->paginate($request->records) : self::$model;

        } catch (Exception $e) {
            Log::info("Error catch in " . __FUNCTION__ . " function");
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private static function queryWithRelation(array $values): void
    {
        try {
            self::$model = self::$model->with(...$values);

        } catch (Exception $e) {
            Log::info("Error catch in " . __FUNCTION__ . " function");
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private static function filterByDate(array $values): void
    {
        try {
            self::$model = self::$model->whereBetween(self::$table.'.created_at', $values);

        } catch (Exception $e) {
            Log::info("Error catch in " . __FUNCTION__ . " function");
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private static function filterBySearch(string $search, $col): void
    {
        try {
            $validateColumn = function (string $column) {
                return ($column === 'concat_name') ? DB::raw('concat(first_name, " ", last_name)') : $column;
            };

            if (!is_null($col) && is_array($col) && !empty($col))
            {
                self::$model = self::$model->where(function ($query) use ($col, $search, $validateColumn) {
                    foreach ($col as $column)
                    {
                        $relations = explode('->', $column);
                        if (count($relations) > 1)
                        {
                            $query->orWhereHas($relations[0], function ($subQuery) use ($relations, $search, $validateColumn) {
                                $columns = explode('&', $relations[1]);

                                if (count($columns) > 1)
                                {
                                    $subQuery->where(function ($nestedSubQuery) use ($columns, $search, $validateColumn) {
                                        foreach ($columns as $nestedCol)
                                        {
                                            $nestedSubQuery->orWhere($validateColumn($nestedCol), 'like', '%'.$search.'%');
                                        }
                                    });
                                } else {
                                    $subQuery->where($validateColumn($relations[1]), 'like', '%'.$search.'%');
                                }
                            });
                        } else {
                            $query->orWhere($validateColumn($column), 'like', '%'.$search.'%');
                        }
                    }
                });
            } else {
                $name = !is_null($col) ? $col : "name";
                self::$model = self::$model->where($validateColumn($name), 'like', '%'.$search.'%');
            }

        } catch (Exception $e) {
            Log::info("Error catch in " . __FUNCTION__ . " function");
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private static function filterByStatus($status, $col): void
    {
        try {
            $col_name = !is_null($col) ? $col : "is_active";
            self::$model = self::$model->where(self::$table.'.'.$col_name, $status);

        } catch (Exception $e) {
            Log::info("Error catch in " . __FUNCTION__ . " function");
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private static function filterBySort(string $sortBy): void
    {
        try {
            self::$model = self::$model->orderBy($sortBy, 'desc');

        } catch (Exception $e) {
            Log::info("Error catch in " . __FUNCTION__ . " function");
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private static function filterByColumn(string $filter): void
    {
        try {
            [$type, $col, $val] = array_pad(explode(':', $filter), 3, null);

            if ($type == 'whereNull' || $type == 'whereNotNull') {
                self::$model = self::$model->{$type}($col);

            } else {
                $vals = explode(',', $val);
                self::$model = self::$model->{$type}($col, count($vals) > 1 ? $vals : $val);
            }

        } catch (Exception $e) {
            Log::info("Error catch in " . __FUNCTION__ . " function");
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}