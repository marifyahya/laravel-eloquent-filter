<?php

namespace Marifyahya\EloquentFilter\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EloquentFilterBuilder
{
    protected Builder $query;
    protected array $filters;
    protected array $config;
    protected $model;

    public function __construct(Builder $query, array $filters, array $config = [])
    {
        $this->query = $query;
        $this->config = $config;
        $this->model = $query->getModel();
        $normalizeKeys = $config['normalize_keys'] ?? $this->model->getNormalizeFilterKeys();
        $this->filters = !empty($normalizeKeys)
            ? $this->normalizeFilterKeys($filters)
            : $filters;
    }

    public function apply(): Builder
    {
        $searchable = $this->config['searchable'] ?? $this->model->getSearchableFields();
        $filterable = $this->config['filterable'] ?? $this->model->getFilterableFields();
        $dateRanges = $this->config['date_ranges'] ?? $this->model->getDateRangeFields();
        $filterableMap = $this->config['filterable_map'] ?? $this->model->getFilterableMap();
        $customFilters = $this->config['custom_filters'] ?? $this->model->getCustomFilters();
        $sortable = $this->config['sortable'] ?? $this->model->getSortableFields();

        if (!empty($this->filters['search'])) {
            $this->applySearch($this->filters['search'], $searchable);
        }

        $resolvedFields = $this->resolveFilterableFields($filterable, $filterableMap);

        foreach ($resolvedFields as $resolvedField) {
            $field = $resolvedField['field'];
            $originalKey = $resolvedField['key'];

            if (!array_key_exists($originalKey, $this->filters)) {
                continue;
            }

            $value = $this->filters[$originalKey];

            if ($value === null || $value === '') {
                continue;
            }

            if ($this->applyCustomFilterMethod($originalKey, $value)) {
                continue;
            }

            if ($this->applyCustomFilterClass($customFilters, $originalKey, $value)) {
                continue;
            }

            $this->applyFilter($field, $value);
        }

        foreach ($dateRanges as $field) {
            $fromKey = $field . '_from';
            $toKey = $field . '_to';

            if (!empty($this->filters[$fromKey])) {
                $this->query->whereDate($field, '>=', $this->filters[$fromKey]);
            }

            if (!empty($this->filters[$toKey])) {
                $this->query->whereDate($field, '<=', $this->filters[$toKey]);
            }
        }

        $this->applyTrashedFilter();
        $this->applyRelationExistsFilter();

        if (!empty($this->filters['sort_by'])) {
            $direction = strtolower($this->filters['sort_dir'] ?? 'asc');
            $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';
            $this->applySorting($this->filters['sort_by'], $sortable, $direction);
        } elseif (!empty($this->filters['sort'])) {
            $this->applySorting($this->filters['sort'], $sortable);
        }

        if (!empty($this->config['relations'])) {
            $this->applyRelationFilters();
        }

        return $this->query;
    }

    protected function normalizeFilterKeys(array $filters): array
    {
        $normalized = [];

        foreach ($filters as $key => $value) {
            $normalized[Str::snake($key)] = $value;
        }

        return $normalized;
    }

    protected function resolveFilterableFields(array $filterable, array $filterableMap): array
    {
        $resolved = [];

        foreach ($filterable as $field) {
            $resolved[] = [
                'field' => $field,
                'key' => $field,
            ];
        }

        foreach ($filterableMap as $alias => $mapped) {
            $resolved[] = [
                'field' => $mapped,
                'key' => $alias,
            ];
        }

        return $resolved;
    }

    protected function applyCustomFilterMethod(string $key, $value): bool
    {
        $method = 'filter' . ucfirst($key);

        if (method_exists($this->model, $method)) {
            $this->model->$method($this->query, $value);

            return true;
        }

        return false;
    }

    protected function applyCustomFilterClass(array $customFilters, string $key, $value): bool
    {
        if (!isset($customFilters[$key])) {
            return false;
        }

        $filterClass = $customFilters[$key];

        if (is_string($filterClass) && class_exists($filterClass)) {
            $instance = new $filterClass();
            $instance->apply($this->query, $value);

            return true;
        }

        if (is_callable($filterClass)) {
            $filterClass($this->query, $value);

            return true;
        }

        return false;
    }

    protected function applySearch(string $keyword, array $fields): void
    {
        if (empty($fields)) {
            return;
        }

        $this->query->where(function ($query) use ($keyword, $fields) {
            foreach ($fields as $field) {
                if (str_contains($field, '.')) {
                    $parts = explode('.', $field);
                    $relation = $parts[0];
                    $column = $parts[1];

                    $query->orWhereHas($relation, function ($q) use ($column, $keyword) {
                        $q->where($column, 'LIKE', "%{$keyword}%");
                    });
                } else {
                    $query->orWhere($field, 'LIKE', "%{$keyword}%");
                }
            }
        });
    }

    protected function applyFilter($field, $value): void
    {
        if (is_array($field)) {
            $this->applyMultiColumnFilter($field, $value);

            return;
        }

        if (is_array($value)) {
            $this->query->whereIn($field, $value);

            return;
        }

        if (!is_string($value)) {
            $this->query->where($field, $value);

            return;
        }

        if ($this->applyNullFilter($field, $value)) {
            return;
        }

        if ($this->applyBetweenFilter($field, $value)) {
            return;
        }

        if ($this->applyOperatorFilter($field, $value)) {
            return;
        }

        if ($this->applyCommaFilter($field, $value)) {
            return;
        }

        $this->query->where($field, $value);
    }

    protected function applyMultiColumnFilter(array $fields, $value): void
    {
        $this->query->where(function ($query) use ($fields, $value) {
            foreach ($fields as $field) {
                if (is_array($value)) {
                    $query->orWhereIn($field, $value);
                } elseif (is_string($value) && str_contains($value, ',')) {
                    $values = array_filter(array_map('trim', explode(',', $value)), fn($v) => $v !== '');
                    $query->orWhereIn($field, $values);
                } elseif (is_string($value) && str_starts_with($value, 'like')) {
                    $query->orWhere($field, 'LIKE', '%' . substr($value, 4) . '%');
                } else {
                    $query->orWhere($field, 'LIKE', "%{$value}%");
                }
            }
        });
    }

    protected function applyNullFilter(string $field, string $value): bool
    {
        $lower = strtolower($value);

        if ($lower === 'null' || $lower === 'is_null') {
            $this->query->whereNull($field);

            return true;
        }

        if ($lower === '!null' || $lower === 'is_not_null') {
            $this->query->whereNotNull($field);

            return true;
        }

        return false;
    }

    protected function applyBetweenFilter(string $field, string $value): bool
    {
        if (!str_starts_with($value, '<>')) {
            return false;
        }

        $parts = explode(',', substr($value, 2));

        if (count($parts) === 2) {
            $this->query->whereBetween($field, [trim($parts[0]), trim($parts[1])]);

            return true;
        }

        return false;
    }

    protected function applyOperatorFilter(string $field, string $value): bool
    {
        if (preg_match('/^(>=|<=|!=|=|>|<|like)/i', $value, $matches)) {
            $operator = strtolower($matches[1]);
            $actualValue = substr($value, strlen($operator));

            if ($actualValue === '' || $actualValue === null) {
                return false;
            }

            if (str_contains($actualValue, ',')) {
                $values = explode(',', $actualValue);
                $values = array_map('trim', $values);

                if ($operator === '!=') {
                    $this->query->whereNotIn($field, $values);
                } else {
                    $this->query->whereIn($field, $values);
                }
            } elseif ($operator === 'like') {
                $this->query->where($field, 'LIKE', "%{$actualValue}%");
            } elseif ($operator === '=') {
                $this->query->where($field, $actualValue);
            } else {
                $this->query->where($field, $operator, $actualValue);
            }

            return true;
        }

        return false;
    }

    protected function applyCommaFilter(string $field, string $value): bool
    {
        if (!str_contains($value, ',')) {
            return false;
        }

        $hasNegation = str_starts_with($value, '!');
        $cleanValue = $hasNegation ? substr($value, 1) : $value;

        $values = explode(',', $cleanValue);
        $values = array_map('trim', $values);
        $values = array_filter($values, fn($v) => $v !== '');

        if (empty($values)) {
            return false;
        }

        if ($hasNegation) {
            $this->query->whereNotIn($field, $values);
        } else {
            $this->query->whereIn($field, $values);
        }

        return true;
    }

    protected function applyTrashedFilter(): void
    {
        if (!isset($this->filters['trashed'])) {
            return;
        }

        if (!in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($this->model))) {
            return;
        }

        $value = $this->filters['trashed'];

        if ($value === 'only') {
            $this->query->onlyTrashed();
        } elseif ($value === 'with') {
            $this->query->withTrashed();
        }
    }

    protected function applyRelationExistsFilter(): void
    {
        $relationKeys = $this->config['relation_exists'] ?? [];

        foreach ($relationKeys as $relation) {
            $key = "has_{$relation}";
            if (!isset($this->filters[$key])) {
                continue;
            }

            $value = $this->filters[$key];

            if (in_array($value, [true, 1, '1', 'true', 'yes'], true)) {
                $this->query->has($relation);
            } elseif (in_array($value, [false, 0, '0', 'false', 'no'], true)) {
                $this->query->doesntHave($relation);
            }
        }
    }

    protected function applySorting(string $sort, array $sortable, ?string $direction = null): void
    {
        $direction = $direction ?? 'asc';
        if (str_starts_with($sort, '-')) {
            $sort = substr($sort, 1);
            $direction = 'desc';
        }

        if (!in_array($sort, $sortable, true)) {
            return;
        }

        $this->query->orderBy($sort, $direction);
    }

    protected function applyRelationFilters(): void
    {
        foreach ($this->config['relations'] as $relation => $fields) {
            foreach ($fields as $field) {
                $key = "{$relation}.{$field}";
                if (isset($this->filters[$key]) && $this->filters[$key] !== null) {
                    $this->query->whereHas($relation, function ($q) use ($field, $key) {
                        $q->where($field, $this->filters[$key]);
                    });
                }
            }
        }
    }
}
