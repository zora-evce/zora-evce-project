<?php

namespace App\Exports;

use App\Models\StationsV;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StationsExport implements FromCollection, WithHeadings
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
        $model = new StationsV();
        $query = $model->select(
            'code',
            'name',
            'connectivity_status',
            'status',
            'connectors_count',
            'brand_name',
            'vendor_name',
            'model_name',
            'firmware_version',
            'address',
            'contract_number',
            'account_name',
            'serial_number',
            'location_holder_name',
            'roaming_type_name',
            'location_type_name',
            'city_name',
            'tariff_name',
            'subscription_name',
            'subscription_start_date',
            'subscription_end_date',
            'reseller_name'
        );
        if (!empty($this->filters['filter_code'])) {
            $query = $query->where('code', 'ILIKE', '%' . $this->filters['filter_code'] . '%');
        }
        if (!empty($this->filters['filter_name'])) {
            $query = $query->where('name', 'ILIKE', '%' . $this->filters['filter_name'] . '%');
        }
        if (!empty($this->filters['filter_status'])) {
            $query = $query->where('connectivity_status', $this->filters['filter_status']);
        }
        if (!empty($this->filters['filter_city'])) {
            $query = $query->where('city_id', $this->filters['filter_city']);
        }
        $records = $query->get();
        return $records->map(function ($item, $index) {
            return [
                '#' => $index + 1,
                'code' => $item->code,
                'name' => $item->name,
                'connectivity_status' => $item->connectivity_status,
                'status' => $item->status,
                'connectors_count' => $item->connectors_count,
                'brand_name' => $item->brand_name,
                'vendor_name' => $item->vendor_name,
                'model_name' => $item->model_name,
                'firmware_version' => $item->firmware_version,
                'address' => $item->address,
                'contract_number' => $item->contract_number,
                'account_name' => $item->account_name,
                'serial_number' => $item->serial_number,
                'location_holder_name' => $item->location_holder_name,
                'roaming_type_name' => $item->roaming_type_name,
                'location_type_name' => $item->location_type_name,
                'city_name' => $item->city_name,
                'tariff_name' => $item->tariff_name,
                'subscription_name' => $item->subscription_name,
                'subscription_start_date' => $item->subscription_start_date,
                'subscription_end_date' => $item->subscription_end_date,
                'reseller_name' => $item->reseller_name
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
            'Station Name',
            'Connectivity Status',
            'Status',
            'Connectors Count',
            'Brand',
            'Vendor',
            'Model',
            'Firmware Version',
            'Address',
            'Contact Number',
            'Account',
            'Serial Number',
            'Location Holder',
            'Roaming Type',
            'Location Type',
            'City',
            'Tariff',
            'Subscription',
            'Subscription Start Date',
            'Subscription End Date',
            'Reseller'
        ];
    }

    // public function columnFormats(): array
    // {
    //     return [
    //         'C' => NumberFormat::FORMAT_TEXT
    //     ];
    // }
}
