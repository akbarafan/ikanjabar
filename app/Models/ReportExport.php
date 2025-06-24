<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements WithMultipleSheets
{
    protected $reportData;
    protected $params;

    public function __construct($reportData, $params)
    {
        $this->reportData = $reportData;
        $this->params = $params;
    }

    public function sheets(): array
    {
        $sheets = [];

        switch ($this->params['report_type']) {
            case 'production':
                $sheets[] = new ProductionReportSheet($this->reportData);
                break;
            case 'financial':
                $sheets[] = new FinancialReportSheet($this->reportData);
                break;
            case 'mortality':
                $sheets[] = new MortalityReportSheet($this->reportData);
                break;
            case 'feeding':
                $sheets[] = new FeedingReportSheet($this->reportData);
                break;
            case 'water_quality':
                $sheets[] = new WaterQualityReportSheet($this->reportData);
                break;
            case 'comprehensive':
                $sheets[] = new ProductionReportSheet($this->reportData['production']);
                $sheets[] = new FinancialReportSheet($this->reportData['financial']);
                $sheets[] = new MortalityReportSheet($this->reportData['mortality']);
                $sheets[] = new FeedingReportSheet($this->reportData['feeding']);
                $sheets[] = new WaterQualityReportSheet($this->reportData['water_quality']);
                break;
        }

        return $sheets;
    }
}

class ProductionReportSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['batch_details'])->map(function($batch) {
            return [
                $batch['id'],
                $batch['pond_name'],
                $batch['branch_name'],
                $batch['fish_type'],
                $batch['date_start'],
                $batch['age_days'],
                $batch['initial_count'],
                $batch['current_stock'],
                $batch['total_harvested'],
                $batch['total_mortality'],
                $batch['survival_rate'],
                $batch['fcr'],
                $batch['total_feed'],
                $batch['revenue'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Batch ID',
            'Pond Name',
            'Branch',
            'Fish Type',
            'Start Date',
            'Age (Days)',
            'Initial Count',
            'Current Stock',
            'Total Harvested',
            'Total Mortality',
            'Survival Rate (%)',
            'FCR',
            'Total Feed (kg)',
            'Revenue',
        ];
    }

    public function title(): string
    {
        return 'Production Report';
    }
}

class FinancialReportSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['daily_revenue'])->map(function ($day) {
            return [
                $day['date'],
                $day['revenue'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Revenue',
        ];
    }

    public function title(): string
    {
        return 'Financial Report';
    }
}

class MortalityReportSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['by_batch'])->map(function ($batch) {
            return [
                $batch['batch_id'],
                $batch['pond_name'],
                $batch['branch_name'],
                $batch['fish_type'],
                $batch['total_deaths'],
                $batch['incident_count'],
                $batch['mortality_rate'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Batch ID',
            'Pond Name',
            'Branch',
            'Fish Type',
            'Total Deaths',
            'Incident Count',
            'Mortality Rate (%)',
        ];
    }

    public function title(): string
    {
        return 'Mortality Report';
    }
}

class FeedingReportSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['fcr_analysis'])->map(function ($batch) {
            return [
                $batch['batch_id'],
                $batch['pond_name'],
                $batch['branch_name'],
                $batch['fish_type'],
                $batch['total_feed'],
                $batch['total_harvest'],
                $batch['fcr'],
                $batch['feed_cost'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Batch ID',
            'Pond Name',
            'Branch',
            'Fish Type',
            'Total Feed (kg)',
            'Total Harvest (kg)',
            'FCR',
            'Feed Cost',
        ];
    }

    public function title(): string
    {
        return 'Feeding Report';
    }
}

class WaterQualityReportSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['daily_trends'])->map(function ($day) {
            return [
                $day['date'],
                $day['temperature'],
                $day['ph'],
                $day['dissolved_oxygen'],
                $day['ammonia'],
                $day['measurement_count'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Temperature (°C)',
            'pH',
            'Dissolved Oxygen (mg/L)',
            'Ammonia (mg/L)',
            'Measurement Count',
        ];
    }

    public function title(): string
    {
        return 'Water Quality Report';
    }
}
