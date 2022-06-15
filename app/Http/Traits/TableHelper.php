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
}
