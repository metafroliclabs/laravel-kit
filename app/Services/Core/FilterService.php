<?php

namespace App\Services\Core;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FilterService
{
    private static $model;
    // private static $table;

    private function __construct(Model $model)
    {
        self::$model = $model;
        // self::$table = $model->getTable(); // getting table name here
    }

    public static function getInstance(Model $model)
    {
        return new self($model);
    }

    public static function run($request, $latestPaginate = false)
    {
        if ($request->exists('with')) self::queryWithRelation($request->with);
        if ($request->exists(['from', 'to']) || $request->exists('date_cols')) self::filterByDate($request);
        if ($request->exists('search', 'search_col')) self::filterBySearch($request->search, $request->search_col);
        if ($request->exists('status', 'status_col')) self::filterByStatus($request->status, $request->status_col);
        if ($request->exists('sortBy')) self::filterBySort($request->sortBy);
        if ($request->exists('filter')) self::filterByColumn($request->filter);

        return $latestPaginate ? self::$model->latest()->paginate($request->records) : self::$model;
    }

    private static function queryWithRelation(array $values): void
    {
        self::$model = self::$model->with(...$values);
    }

    private static function filterByDate($request): void
    {
        $dateCols = $request->date_cols;
        // If no date columns provided, fallback to default
        if (empty($dateCols)) {
            $dateCols = [
                'created_at' => ['from', 'to'],
            ];
        }

        $dateCols = collect($dateCols)
            ->mapWithKeys(fn($value, $key) => is_int($key) ? [$value => null] : [$key => $value])
            ->toArray();

        foreach ($dateCols as $column => $keys) {
            if (is_array($keys) && count($keys) === 2) {
                [$fromKey, $toKey] = $keys;
            } else {
                // Auto-generate from/to keys
                $base = \Illuminate\Support\Str::before($column, '_at');
                $fromKey = "{$base}_from";
                $toKey = "{$base}_to";
            }

            $from = request()->input($fromKey);
            $to = request()->input($toKey);

            if ($from && $to) {
                $fromDate = Carbon::parse($from)->startOfDay();
                $toDate = Carbon::parse($to)->endOfDay();

                self::$model = self::$model->whereBetween($column, [$fromDate, $toDate]);
            }
        }
    }

    private static function filterBySearch(string $search, $col): void
    {
        $validateColumn = function (string $column) {
            return ($column === 'concat_name') ? DB::raw('concat(first_name, " ", last_name)') : $column;
        };

        if (is_array($col) && !empty($col)) {
            self::$model = self::$model->where(function ($query) use ($col, $search, $validateColumn) {
                foreach ($col as $column) {
                    $relations = explode('->', $column);
                    if (count($relations) > 1) {
                        $query->orWhereHas($relations[0], function ($subQuery) use ($relations, $search, $validateColumn) {
                            $columns = explode('&', $relations[1]);

                            if (count($columns) > 1) {
                                $subQuery->where(function ($nestedSubQuery) use ($columns, $search, $validateColumn) {
                                    foreach ($columns as $nestedCol) {
                                        $nestedSubQuery->orWhere($validateColumn($nestedCol), 'like', '%' . $search . '%');
                                    }
                                });
                            } else {
                                $subQuery->where($validateColumn($relations[1]), 'like', '%' . $search . '%');
                            }
                        });
                    } else {
                        $query->orWhere($validateColumn($column), 'like', '%' . $search . '%');
                    }
                }
            });
        } else {
            $name = !is_null($col) ? $col : "name";
            self::$model = self::$model->where($validateColumn($name), 'like', '%' . $search . '%');
        }
    }

    private static function filterByStatus($status, $col): void
    {
        $col_name = !is_null($col) ? $col : "is_active";
        self::$model = self::$model->where($col_name, $status);
    }

    private static function filterBySort(string $sortBy): void
    {
        self::$model = self::$model->orderBy($sortBy, 'desc');
    }

    private static function filterByColumn(string $filter): void
    {
        [$type, $col, $val] = array_pad(explode(':', $filter), 3, null);

        if (is_null($val)) {
            self::$model = self::$model->{$type}($col);
        } else {
            $vals = explode(',', $val);
            self::$model = self::$model->{$type}($col, count($vals) > 1 ? $vals : $val);
        }
    }
}