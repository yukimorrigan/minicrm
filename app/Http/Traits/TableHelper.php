<?php
namespace App\Http\Traits;

use App\View\Components\AdminButton;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait TableHelper
{
    /**
     * @param string $model
     * @return string
     */
    public function getGenitiveCaseName($model)
    {
        $table = with(new $model)->getTable();
        $entity = Str::singular($table);
        return __('admin.'.$entity.'_genitive');
    }

    /**
     * @param string $str
     * @return string
     */
    public function mb_ucfirst($str)
    {
        return mb_strtoupper(mb_substr($str, 0, 1)).mb_substr($str, 1);
    }

    public function getPageParams(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowsPerPage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        return compact([
            'draw',
            'start',
            'rowsPerPage',
            'columnIndex',
            'columnName',
            'columnSortOrder',
            'searchValue'
        ]);
    }
}
