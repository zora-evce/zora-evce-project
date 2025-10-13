<?php
namespace App\Helpers;

use App\Models\LaboratoryExaminationM;
use App\Models\LookupC;
use App\Models\PackageM;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class GlobalHelper {
    public static function validation($data, $rules, $messages, $attributes = null){
        if (isset($data)) {
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                // return json_decode($validator->messages(), true);
                $string = implode(', ', $validator->messages()->all());
                return $string;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public static function dataTable($request, $query)
    {
        $totalRecords = $query->count();
        $search = [];

        if($request->has('search')){
            foreach ($request['columns'] as $column) {
                if (isset($column['searchable']) && $column['searchable'] === 'true' && isset($column['data'])) {
                    $search[] = $column['data'];
                }
            }
        }
        if ($request->has('search') && !empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $query = $query->where(function ($q) use ($search, $searchValue) {
                foreach ($search as $column) {
                    if (strpos($column, '.') === false) {
                        $q->orWhere($column, 'ilike', '%' . $searchValue . '%');
                    } else {
                        $arr = explode(".", $column);
                        $q->orWhereHas($arr[0], function ($qRel) use ($arr, $searchValue) {
                            $qRel->where($arr[1], 'ilike', '%' . $searchValue . '%');
                        });
                    }
                }
            });
        }
        $filteredRecords = $query->count();
        if ($request->has('order') && is_array($request->order)) {
            foreach ($request->order as $order) {
                $columnIndex = $order['column'];
                $columnName = $request->columns[$columnIndex]['data'];
                $direction = $order['dir'];
                $query = $query->orderBy($columnName, $direction);
            }
        }
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $data = $query->offset($start)->limit($length)->get();
        return [
            'draw' => $request->draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
    }

}
