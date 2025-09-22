<?php

namespace App\Exports;

use App\Models\Uploads;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UploadsExports implements FromCollection, WithHeadings, WithMapping
{
    protected $uploads;

    public function __construct($uploads)
    {
        $this->uploads = $uploads;
    }

    public function collection()
    {
        return $this->uploads;
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Company',
            'Municipality',
            'Status',
            'Email Files',
            'Workings File',
            'Systems Import File',
            'Extracted Dates',
            'System Import Date',
            'Submitted At',
            'Created At'
        ];
    }

    public function map($upload): array
    {
        return [
            $upload->reference,
            $upload->company->name,
            $upload->municipality->name,
            $upload->status,
            implode(', ', $upload->original_file_names ?? []),
            $upload->workings_file_name ?? '-',
            $upload->systems_import_file_name ?? '-',
            implode(', ', $upload->extracted_dates ?? []),
            $upload->system_import_date ? $upload->system_import_date->format('Y-m-d H:i:s') : '-',
            $upload->submitted_at->format('Y-m-d H:i:s'),
            $upload->created_at->format('Y-m-d H:i:s'),
        ];
    }
}