<?php

namespace DelocalZrt\SimpleCrud\Controllers;

use DelocalZrt\SimpleCrud\Services\SimpleCrudHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class XhrController extends BaseController
{

    public function select2xhr(Request $request)
    {
        $eloqClass = substr($request->model, 0, 64);
        $relatFieldName = substr($request->relatFieldName, 0, 64);

        $class = SimpleCrudHelper::getFullSimpleCrudClassFromClassBasename($eloqClass);
        $ent = new $class();

        $limit = config('datatable.max_element_per_ajax_loading');
        $offset = (intval(($request->page ?? 1) - 1) * $limit);

        $hits = $class::where($ent->getKeyName(), '>', 0)
            ->whereRaw('LOWER(' . $relatFieldName . ') LIKE ? ', [(mb_strtolower($request->q) . '%')])
            ->limit($limit)
            ->offset($offset)->get([$ent->getKeyName(), $relatFieldName]);

        $count = $class::all()->count();
        $items = [];
        foreach ($hits as $item) {
            $name = $item->{$relatFieldName};
            if (strpos($name, '<!-- LANG') !== false) {
                $name = SimpleCrudHelper::getTextValueByLang($name);
            }

            $items[] = ['id' => $item->getKey(), 'text' => $name];
        }

        return response()->json(['items' => $items, 'total_count' => $count]);
    }


}
