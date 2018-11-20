# Welcome to LiveCMS DataTables For Laravel 5.5 or above

## What is ?
This package can deal with Javascript DataTables.

## Features
- Full Yajra DataTables features with some improvements. See [documentation](https://yajrabox.com/docs/laravel-datatables/master)

### Notes
- Please add DataTables js and css, see [DataTables Documentation](https://datatables.net/)

## How to use?

### Install via composer
```shell
composer require livecms/datatables
```

### Publish config file :
```shell
php artisan vendor:publish --provider="LiveCMS\DataTables\DataTablesServiceProvider"
```

Edit 'datatables.php' config file.

### DataTables Class

Definition :
`DataTables(LiveCMS\DataTables\HasDataTables $object, $url, array $fields = null)`


### Define Base Query
You can create a new class or use existing one.
What you need to do is implement interface `LiveCMS\DataTables\HasDataTables` and create a method : `toDataTablesQuery()`
This method must return a Laravel Query Builder, Relation, Collection or API Resource Collection
```php

use App\Http\Resources\SportResource;
use App\Sport;
use LiveCMS\DataTables\HasDataTables;

class SportDataTables implements HasDataTables
{
    public function toDataTablesQuery()
    {
        // use one of these types
        return app(Sport::class)->newQuery(); // Builder
        return app(Sport::class)->players(); // Relation
        return Sport::get(); // Collection
        return SportResource::collection(Sport::get()); // API Resource Collection
    }
}
```

### Define Fields
Fields defined by array.
Example :
You have fields : `id, name, is_active, action` which are `id, name and is_active` is in your model data and `action` field is a custom field that contains action buttons : `Edit and Delete` button

Then your fields would be like this and the explanation is in the bellow :
```php
$fields = [
    'ID',
    'Sport Name' => 'name',
    'Is Active' => [
        'display' => function ($isActive) {
            return new \Illuminate\Support\HtmlString(
                $isActive ? '<strong>True</strong>' : '<strong>False</strong>';
            );
        }
    ],
    'Action' => function ($row) {
        return new \Illuminate\Support\HtmlString(
            '<button data-id="'.$row->id.'">Edit</button><button data-id="'.$row->id.'">Delete</button>';
        );
    }
];
```

You can place fields in method : `getDataTablesFields()` in class that implement `HasDataTables` interface, i.e.
```php

use Illuminate\Support\HtmlString;
use LiveCMS\DataTables\HasDataTables;

class SportDataTables implements HasDataTables
{
    ....

    public function getDataTablesFields()
    {
        return [
            'ID',
            'Sport Name' => 'name',
            'Is Active' => [
                'display' => function ($isActive) {
                    return new HtmlString(
                        $isActive ? '<strong>True</strong>' : '<strong>False</strong>';
                    );
                }
            ],
            'Action' => function ($row) {
                return new HtmlString(
                    '<button data-id="'.$row->id.'">Edit</button><button data-id="'.$row->id.'">Delete</button>';
                );
            }
        ];
    }
}
```
or in third parameter when you call DataTables class. See [definition](#datatables-class)

### Field Definition Explaination :
Every single field in the array automatically will be converted into this default form :
1. Field exists in database
```php
    'Label' => [
        'name' => 'label', // field name or will use lower case of label if not defined
        'data' => 'label', // data name or will use lower case of label if not defined
        'orderable' => true, // if this field is not orderable, set false,
        'searchable' => true, // if this field is not searchable, set false
        'display' => function ($value) {
            return new \Illuminate\Support\HtmlString('<span class="rounded">value</span>');
        ,
        // if you want to mark up the value, use display.
        
    ],
```
2. Field doesn't exist in database or custom field
```php
    'Label' => function ($row) {
        return 'anything';
    },
```
**Notes : Don't forget use class `\Illuminate\Support\HtmlString` to un-escape the html code**

### Let's play
Controller :
```php

use LiveCMS\DataTables\DataTables;

class SportController extends Controller
{
    protected $dataTables;

    public function __construct(SportDataTables $sportDataTables)
    {
        $dataTablesUrl = url('/admin/sport/data'); // route('routename')
        $this->dataTables = new DataTables($sportDataTables, $dataTablesUrl);
    }

    public function getIndex()
    {
        $this->dataTables->renderView();
        return view('admin.sports.index');
    }

    public function postData(Request $request)
    {
        return $this->dataTables->renderData();
    }

}

```

View :
`file : admin/sports/index.blade.php`
```php
    <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
        <thead>
            <tr>
                @foreach ($dataTablesCaptions as $field)
                <th @if (strtolower($field) == 'action') class="text-right" @endif>{{ $field }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#datatables').DataTable({!! $dataTablesView !!});
        });
    </script>
```

## LICENSE
MIT

## CONTRIBUTING
Fork this repo and make a pull request

## ISSUE AND DISCUSSION
Please create new issue or see the closed issues too.

