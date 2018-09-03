<?php

namespace LiveCMS\DataTables;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Field implements Arrayable
{
    protected $properties = [
        'caption',
        'className',
        'contentPadding',
        'createdCell',
        'data',
        'defaultContent',
        'name',
        'orderable',
        'orderData',
        'orderDataType',
        'orderSequence',
        'render',
        'searchable',
        'title',
        'type',
        'visible',
        'width',
    ];

    protected $configs = [];

    public function __construct($caption, $name = null, $configs = [])
    {
        $this->configs = $configs;
        $this->caption = $caption;
        $this->name = $name = $name ?? Str::snake(strtolower($caption));
        $this->data = $this->data ?? $name;
    }

    public function __set($config, $value)
    {
        $this->configs[$config] = $value;
    }

    public function __get($config)
    {
        return $this->configs[$config] ?? null;
    }

    public function __call($func, $args)
    {
        $this->configs[$func] = $args;
        return $this;
    }

    public function toArray()
    {
        return Arr::only($this->configs, $this->properties);
    }
}
