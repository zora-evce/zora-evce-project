<?php

namespace App\Exports;

use App\Helpers\GlobalHelper;
use App\Models\StationsV;
use App\Models\TransactionsV;
use App\Models\TransactionsVTemporary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionsStationsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // $query = TransactionsV::query()->where('station_id', $this->filters['station_id']);
        $query = TransactionsVTemporary::query()->where('station_id', $this->filters['station_id']);
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $start = Carbon::parse($this->filters['start_date'])->startOfDay();
            $end   = Carbon::parse($this->filters['end_date'])->endOfDay();

            $query->whereBetween('start_time', [$start, $end]);
        }
        if (!empty($this->filters['transaction_id'])) {
            $query->where('transaction_id', $this->filters['transaction_id']);
        }
        if (!empty($this->filters['customer_name'])) {
            $query->where('customer_name', 'ILIKE', '%'.$this->filters['customer_name'].'%');
        }
        if (array_key_exists('payment_status', $this->filters) && $this->filters['payment_status'] !== '' && $this->filters['payment_status'] !== null) {
            $query->where('payment_status', $this->filters['payment_status']);
        }
        $records = $query->orderByDesc('id')->get();
        return $records->map(function ($item, $index) {
            return [
                '#' => $index + 1,
                'station_name' => $item->station_name,
                'transaction_id' => $item->transaction_id,
                'connector_id' => $item->connector_id,
                'station_address' => $item->station_address,
                'customer_name' => $item->customer_name,
                'customer_email' => $item->customer_email,
                'customer_phone' => $item->customer_phone,
                'payment_status' => $item->payment_status_name,
                'midtrans_order_id' => $item->midtrans_order_id,
                'tariff_code' => $item->tariff_code,
                'start_time' => $item->start_time,
                'stop_time' => $item->stop_time,
                'total_time' => !empty($item->total_time) ? $item->total_time . ' Minutes' : '-',
                'total_kwh' => !empty($item->calculated_kwh) ? $item->calculated_kwh . ' kWh' : '-',
                'total_cost' => GlobalHelper::convertToRupiah($item->total_cost)
            ];
        });
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return [
            '#',
            'Station Name',
            'Transaction ID',
            'Connector ID',
            'Station Address',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Payment Status',
            'Midtrans Order ID',
            'Tariff Code',
            'Start Time',
            'Stop Time',
            'Total Time',
            'Total Kwh',
            'Total Cost'
        ];
    }

    // public function columnFormats(): array
    // {
    //     return [
    //         'C' => NumberFormat::FORMAT_TEXT
    //     ];
    // }
}
