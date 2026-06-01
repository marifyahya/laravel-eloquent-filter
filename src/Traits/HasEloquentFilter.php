<?php

namespace Marifyahya\EloquentFilter\Traits;

use Marifyahya\EloquentFilter\Builders\EloquentFilterBuilder;

trait HasEloquentFilter
{
    public function scopeFilter($query, ?array $filters = null, array $config = [])
    {
        $filters = $filters ?? request()->all();

        return (new EloquentFilterBuilder($query, $filters, $config))->apply();
    }

    public function getFilterableFields(): array
    {
        return $this->filterableFields ?? ['id'];
    }

    public function getSortableFields(): array
    {
        return $this->sortableFields ?? $this->getFilterableFields();
    }

    public function getSearchableFields(): array
    {
        return $this->searchableFields ?? [];
    }

    public function getDateRangeFields(): array
    {
        return $this->dateRangeFields ?? ['created_at'];
    }

    public function getFilterableMap(): array
    {
        return $this->filterableMap ?? [];
    }

    public function getCustomFilters(): array
    {
        return $this->customFilters ?? [];
    }
}
