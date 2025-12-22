<?php

namespace App\Http\Controllers\Master;

use App\Helpers\ConstantsHelper;
use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Models\Tariff;
use App\Models\TransactionsV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TariffController extends Controller
{
    public function index()
    {
        $data = [];
        return view('master.tariff.index', $data);
    }

    public function getData(Request $request)
    {
        $model = new Tariff();
        $query = $model->select();
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

    public function addTariff(Request $request)
    {
        $post = ($request->post());
        DB::beginTransaction();
        $model = new Tariff();
        unset($post['_token']);
        $query = $model->select('tariff_code')->where('tariff_code', $post['tariff_code'])->first();
        if (!empty($query)) {
            return redirect()->back()->with([
                'error' => 'Tariff Code Already Used!'
            ]);
        }
        $model->attributes = $post;
        if ($model->validate() === true) {
            if ($model->save()) {
                DB::commit();
                return redirect()->back()->with([
                    'success' => ConstantsHelper::MESSAGE_SUCCESS_SAVE
                ]);
            }
        } else {
            DB::rollback();
            return redirect()->back()->with([
                'error' => $model->validate()
            ]);
        }
    }

}
