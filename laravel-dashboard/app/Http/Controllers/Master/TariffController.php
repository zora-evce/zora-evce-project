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
        if ($request->filled('tariff_code')) {
            $query->where('tariff_code', $request->tariff_code);
        }
        if ($request->filled('tariff_name')) {
            $query->where('tariff_name', 'ILIKE', '%'.$request->tariff_name.'%');
        }
        return response()->json(GlobalHelper::dataTable($request, $query));
    }

    public function saveTariff(Request $request)
    {
        DB::beginTransaction();
        try {
            $post = $request->post();
            $tariff_id = isset($post['tariff_id']) ? $post['tariff_id'] : null;
            $model = new Tariff();
            if (!empty($tariff_id)) {
                $query = $model->find($tariff_id);
                if ($query != null) {
                    $model = $model->find($tariff_id);
                    $post['tariff_id'] = $tariff_id;
                    $model->rules['tariff_code'] = 'required|unique:tariff,tariff_code,' . $tariff_id . ',tariff_id';
                }
            } else {
                unset($post['tariff_id']);
            }
            unset($post['_token']);
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
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with([
                'error' => ConstantsHelper::MESSAGE_ERROR_SAVE.' '.$e->getMessage()
            ]);
        }
    }

    public function deleteTariff($id)
    {
        try {
            $data = Tariff::findOrFail($id);
            $data->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Data not found'
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Cannot delete this data because it is still being used.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

}
