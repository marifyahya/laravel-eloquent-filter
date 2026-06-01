<?php

namespace Marifyahya\EloquentFilter\Traits;

use Illuminate\Database\Eloquent\Builder;
use Marifyahya\EloquentFilter\Builders\EloquentFilterBuilder;

/**
 * Adds the filter() scope and model-level filter configuration.
 *
 * Supported query behavior includes whitelisted exact filters, comparison
 * operators (`>`, `>=`, `<`, `<=`, `!=`, `=`), comma-separated `whereIn`,
 * negated comma-separated `whereNotIn`, `null` / `!null`, between syntax
 * (`<>min,max`), date range keys (`{field}_from`, `{field}_to`), global
 * `search`, sorting with `sort=-field` or `sort_by=field&sort_dir=desc`,
 * soft delete filters, relation existence filters, relation field filters,
 * filter aliases, key normalization, and custom filter callbacks/classes.
 *
 * Field-level LIKE filters are intentionally custom behavior. Define a
 * filter{Field} method, custom filter class, or callback when a project needs
 * partial matching for a specific request key.
 */
trait HasEloquentFilter
{
    /**
     * Apply whitelisted filters to the model query.
     *
     * When no filter array is provided, request()->all() is used. Per-query
     * config keys can override model properties: searchable, filterable,
     * sortable, date_ranges, filterable_map, custom_filters, relations,
     * relation_exists, and normalize_keys.
     *
     * @param Builder $query
     * @param array<string, mixed>|null $filters
     * @param array<string, mixed> $config
     * @return Builder
     */
    public function scopeFilter($query, ?array $filters = null, array $config = [])
    {
        $filters = $filters ?? request()->all();

        return (new EloquentFilterBuilder($query, $filters, $config))->apply();
    }

    /**
     * @return array<int, string>
     */
    public function getFilterableFields(): array
    {
        return $this->filterableFields ?? ['id'];
    }

    /**
     * @return array<int, string>
     */
    public function getSortableFields(): array
    {
        return $this->sortableFields ?? $this->getFilterableFields();
    }

    /**
     * @return array<int, string>
     */
    public function getSearchableFields(): array
    {
        return $this->searchableFields ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function getDateRangeFields(): array
    {
        return $this->dateRangeFields ?? ['created_at'];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function getFilterableMap(): array
    {
        return $this->filterableMap ?? [];
    }

    /**
     * @return array<string, class-string|callable>
     */
    public function getCustomFilters(): array
    {
        return $this->customFilters ?? [];
    }

    /**
     * Convert camelCase request keys to snake_case before filtering.
     */
    public function getNormalizeFilterKeys(): bool
    {
        return $this->normalizeFilterKeys ?? false;
    }
}
