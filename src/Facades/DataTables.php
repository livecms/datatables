<?php

namespace LiveCMS\DataTables\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \LiveCMS\DataTables\DataTables
 * @method static \Yajra\DataTables\EloquentDatatable eloquent($builder)
 * @method static \Yajra\DataTables\QueryDataTable query($builder)
 * @method static \Yajra\DataTables\CollectionDataTable collection($collection)
 *
 * @see \LiveCMS\DataTables\DataTables
 * @see \Yajra\DataTables\DataTables
 */
class DataTables extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'datatables';
    }
}
