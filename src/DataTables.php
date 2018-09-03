<?php

namespace LiveCMS\DataTables;

use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\DataTables as YajraDataTables;

class DataTables extends YajraDataTables
{
    /**
     * [$object description]
     * @var [type]
     */
    protected $object;

    /**
     * [$usesTimestamps description]
     * @var boolean
     */
    protected $usesTimestamps = true;

    /**
     * [$columns description]
     * @var [type]
     */
    protected $columns;

    /**
     * [$captions description]
     * @var [type]
     */
    protected $captions;

    /**
     * [$pattern description]
     * @var [type]
     */
    protected $pattern;

    /**
     * [$defaultOrder description]
     * @var [type]
     */
    protected $defaultOrder;

    public function __construct(HasDataTables $object, $url, array $pattern = null)
    {
        $this->object = $object;
        $this->setUrl($url);
        $this->setPattern(
            $pattern
            ?? (method_exists($object, 'getDataTablesPattern')
                ? $object->getDataTablesPattern()
                : []
            )
        );
    }

    /**
     * get config of datatables service
     * @param  string $config [description]
     * @return mix         [description]
     */
    public function getConfig($name = null, $default = null)
    {
        if ($name === null) {
            return parent::getConfig();
        }

        return parent::getConfig()->get('datatables.'.$name, $default);
    }

    /**
     * set or override datatables service config
     * @param string $name  [description]
     * @param mix $value [description]
     */
    public function setConfig($name, $value = '')
    {
        parent::getConfig()->set('datatables.'.$name, $value);
        return $this;
    }

    /**
     * [setUrl description]
     * @param [type] $url [description]
     */
    protected function setUrl($url)
    {
        $this->setConfig('view.ajax.url', $url);
        return $this;
    }

    /**
     * [useTimestamps description]
     * @return [type] [description]
     */
    public function useTimestamps()
    {
        $this->usesTimestamps = true;
        return $this;
    }

    /**
     * [dontUseTimestamps description]
     * @return [type] [description]
     */
    public function dontUseTimestamps()
    {
        $this->usesTimestamps = false;
        return $this;
    }

    /**
     * [setPattern description]
     * @param array $pattern [description]
     */
    public function setPattern(array $pattern = [])
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * [getColumnsByPattern description]
     * @return [type] [description]
     */
    protected function getColumnsByPattern()
    {
        $columns = [];

        foreach ($this->pattern as $caption => $field) {
            if (is_string($field)) {
                if (is_numeric($caption)) {
                    $columns[] = new Field($field);
                } else {
                    $columns[] = new Field($caption, $field);
                }
            } elseif (is_callable($field)) {
                $columns[] = new Field($caption, null, [
                    'resolve' => $field,
                    'searchable' => false,
                    'orderable' => false
                ]);
            } elseif (is_array($field)) {
                $columns[] = new Field($caption, null, $field);
            }
        }

        if ($this->usesTimestamps) {
            if (!isset($columns['created_at'])) {
                $columns[] = new Field(__('Created At'), 'created_at', ['visible' => false]);
            }
        }

        return collect($columns);
    }

    /**
     * get datatables columns
     * @return [type] [description]
     */
    public function getColumns()
    {
        if ($this->columns !== null) {
            return $this->columns;
        }

        if ($columns = $this->getConfig('view.columns', false)) {
            return $this->columns = $columns;
        }

        return $this->columns = $this->getColumnsByPattern()->toArray();
    }

    /**
     * set datatables columns
     */
    protected function setColumns(array $columns = null)
    {
        $columns = $columns ?? array_values($this->getColumns());
        $this->setConfig(
            'view.columns',
            $this->columns = $columns
        );
        return $this;
    }

    /**
     * [getColumnNumber description]
     * @param  [type] $column [description]
     * @return [type]         [description]
     */
    protected function getColumnNumber($column)
    {
        if ($column == null) {
            return null;
        }

        $fields = $this->getColumns();
        $number = 0;
        foreach ($fields as $field) {
            if ($field['name'] == $column) {
                return $number;
            }
            $number++;
        }

        return null;
    }

    protected function getDefaultOrderColumn()
    {
        return $this->usesTimestamps ? 'created_at' : null;
    }

    /**
     * [setDefaultOrder description]
     * @param [type] $order [description]
     */
    public function setDefaultOrder($order = null)
    {
        if ($order === false) {
            $this->defaultOrder = false;
            $this->setConfig('view.ordering', false);
            return $this;
        }

        if ($order === null) {
            if ($this->defaultOrder === false) {
                $this->setConfig('view.ordering', false);
                return $this;
            }
            if ($this->defaultOrder) {
                $order = $this->defaultOrder;
            } else if (
                $colNumber = $this->getColumnNumber(
                    $this->getDefaultOrderColumn()
                )
            ) {
                $order = [[$colNumber, 'desc']];
            }
        }

        $this->defaultOrder = $order;
        if ($order === null) {
            $this->setConfig('view.ordering', false);
            return $this;
        }

        $this->setConfig('view.ordering', true);
        $this->setConfig('view.order', $order);
        return $this;
    }

    protected function getCaptionsByColumns()
    {
        $captions = [];
        foreach ($this->getColumns() as $key => $column) {
            $captions[$key] = $column['caption'];
        }
        return $captions;
    }
    /**
     * [getCaptionByPattern description]
     * @return [type] [description]
     */
    protected function getCaptionByPattern($key, $field)
    {
        if (is_string($field)) {
            return (!is_numeric($key) && $key != $field) ? $field : title_case(str_replace('_', ' ', $field));
        } elseif (is_array($field)) {
            return $field['caption'] ?? title_case(str_replace('_', ' ', $key));
        }
        return title_case(str_replace('_', ' ', $key));
    }

    /**
     * get caption
     * @return [type] [description]
     */
    protected function getCaptions()
    {
        if ($this->captions !== null) {
            return $this->captions;
        }

        return $this->captions = $this->getCaptionsByColumns();
    }

    /**
     * render datatable and share to view
     */
    public function renderView()
    {
        $this->setColumns();
        $this->setDefaultOrder();

        $dataTablesCaptions = array_values($this->getCaptions());
        $dataTablesView = json_encode($this->getConfig('view'));

        view()->share(compact('dataTablesCaptions', 'dataTablesView'));
        return $this;
    }

    protected function fieldProcessing($dataTables)
    {
        $this->getColumnsByPattern()->map(function ($item) use (&$dataTables) {
            if ($item->resolve) {
                $dataTables->addColumn($item->name, $item->resolve);
            }
            if ($item->display) {
                $dataTables->editColumn($item->name, $item->display);
            }
        });

        return $dataTables;
    }

    /**
     * [makeDataTablesData description]
     * @param  [type] $dataTables [description]
     * @return [type]             [description]
     */
    public function makeDataTablesData($dataTables, $callback = null)
    {
        $dataTables = $this->fieldProcessing($dataTables);
        if (is_callable($callback)) {
            $dataTables = $callback($dataTables);
        }
        return $dataTables->make();
    }

    /**
     * [renderDataTablesData description]
     * @return [type] [description]
     */
    public function renderData($callback = null)
    {
        return $this->makeDataTablesData(
            static::make(
                $this->object->toDataTablesQuery()
            ),
            $callback
        );
    }
}
