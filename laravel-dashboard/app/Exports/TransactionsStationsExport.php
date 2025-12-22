<?php

namespace App\Exports;

use App\Models\StationsV;
use App\Models\TransactionsV;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionsStationsExport implements FromCollection, WithHeadings
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
        $model = new TransactionsV();
        $query = $model->select()->where('station_id', $this->filters['station_id']);
        $records = $query->get();
        return $records->map(function ($item, $index) {
            return [
                '#' => $index + 1,
                'station_code' => $item->code,
                'connector_id' => $item->connector_id,
                'address' => $item->address,
                'payment_status' => $item->payment_status_name,
                'start_time' => $item->start_time,
                'stop_time' => $item->stop_time,
                'total_time' => !empty($item->total_time) ? $item->total_time . ' Minutes' : '-',
                'total_cost' => $item->total_cost
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
            'Station Code',
            'Connector ID',
            'Address',
            'Payment Status',
            'Start Time',
            'Stop Time',
            'Total Time',
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
