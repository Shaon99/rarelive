<?php

namespace App\Livewire;

use App\Constants\CommonConstant;
use App\Jobs\ExportCsvJob;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\GeneralSetting;
use App\Models\Sales;
use App\Services\CarrybeeIntegrationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SalesTable extends Component
{
    use WithPagination;

    public $user;

    public $search = '';

    public $sortBy = 'created_at';

    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 25;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setSortBy($sortByField): void
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == 'ASC') ? 'DESC' : 'ASC';

            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public $showFilters = false;

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public $created_by = '';

    public $lead_by = '';

    public $startDate = '';

    public $endDate = '';

    public $system_status = '';

    public $courier_status = '';

    public $platform = '';

    public $payment_status = '';

    public function applyFilter() {}

    protected $queryString = ['system_status'];

    private function buildQuery()
    {
        if ($this->user->can('view_all_sales')) {
            $query = Sales::with('user', 'customer', 'warehouse', 'paymentMethod')
                ->when($this->system_status !== 'draft', function ($query) {
                    return $query->whereNot('system_status', CommonConstant::DRAFT);
                })
                ->when($this->system_status === 'draft', function ($query) {
                    return $query->where('system_status', CommonConstant::DRAFT);
                })
                ->when($this->search, function ($query) {
                    return $query->search($this->search);
                })
                ->orderBy($this->sortBy, $this->sortDir);
        } elseif ($this->user->can('view_own_sales')) {
            $query = Sales::with('user', 'customer', 'warehouse', 'paymentMethod')
                ->when($this->system_status !== 'draft', function ($query) {
                    return $query->whereNot('system_status', CommonConstant::DRAFT);
                })
                ->when($this->system_status === 'draft', function ($query) {
                    return $query->where('system_status', CommonConstant::DRAFT);
                })
                ->where('user_id', $this->user->id)
                ->when($this->search, function ($query) {
                    return $query->search($this->search);
                })
                ->orderBy($this->sortBy, $this->sortDir);
        }

        if (request()->has('trashed') && request('trashed') == true) {
            $query->onlyTrashed();
        }

        if ($this->created_by) {
            $query->where('user_id', $this->created_by);
        }

        if ($this->lead_by) {
            $query->where('lead_id', $this->lead_by);
        }

        if ($this->system_status) {
            $query->where('system_status', $this->system_status);
        }

        if ($this->courier_status) {
            $query->where('status', $this->courier_status);
        }

        if ($this->platform) {
            $query->where('platform', $this->platform);
        }

        if ($this->payment_status) {
            $query->where('payment_status', $this->payment_status);
        }

        if (! empty($this->startDate) && ! empty($this->endDate)) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }

        return $query;
    }

    public $fileName = null;

    public $batchId;

    public $exporting = false;

    public $exportFinished = false;

    public $progress = 0;

    public $totalChunks;

    public function mount()
    {
        $this->system_status = request()->get('system_status', '');
        $this->exporting = session('exporting', false);
        $this->exportFinished = session('exportFinished', false);
        $this->progress = session('progress', 0);
        $this->fileName = session('fileName', null);
        $this->batchId = session('batchId', null);
        $this->totalChunks = session('totalChunks', 0);
        $this->user = Auth::guard('admin')->user();
    }

    public function downloadCSV()
    {
        // Validate filters
        if (
            empty($this->created_by) && empty($this->lead_by) && empty($this->startDate) && empty($this->endDate) &&
            empty($this->system_status) && empty($this->courier_status) && empty($this->platform)
        ) {
            session()->flash('error', 'At least one filter option must be provided before exporting.');

            return;
        }

        $salesQuery = $this->buildQuery();
        $this->fileName = 'sales_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $transformer = new \App\Transformers\SalesTransformer();

        if (config('queue.enabled', false)) {
            // Queue enabled — dispatch batch jobs

            $chunkSize = 200;
            $totalRows = $salesQuery->count();
            $this->totalChunks = ceil($totalRows / $chunkSize);

            $batch = Bus::batch([])->then(function ($batch) {
                $this->exporting = false;
                $this->exportFinished = true;
                $this->progress = 100;
                session(['exporting' => false, 'exportFinished' => true, 'progress' => 100]);
            })->catch(function ($batch, $exception) {
                session()->forget(['exporting', 'exportFinished', 'progress', 'fileName', 'totalChunks']);
                $this->exporting = false;
                $this->exportFinished = false;
                session()->flash('error', 'Export failed. Please try again.');
            })->dispatch();

            $salesQuery->chunk($chunkSize, function ($salesData) use ($batch, $transformer) {
                $batch->add(new ExportCsvJob($salesData, $this->fileName, $transformer));
            });

            $this->batchId = $batch->id;

            session([
                'exporting' => true,
                'exportFinished' => false,
                'progress' => 0,
                'fileName' => $this->fileName,
                'batchId' => $this->batchId,
                'totalChunks' => $this->totalChunks,
            ]);

            $this->exporting = true;
            $this->exportFinished = false;
            $this->progress = 0;

            return;
        }

        // Queue not enabled — immediate streaming download

        $callback = function () use ($salesQuery, $transformer) {
            $handle = fopen('php://output', 'w');

            // Write CSV header
            fputcsv($handle, $transformer->getHeaders());

            $salesQuery->chunk(500, function ($sales) use ($handle, $transformer) {
                foreach ($sales as $sale) {
                    fputcsv($handle, $transformer->transform($sale));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, $this->fileName, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function updateExportProgress()
    {
        try {
            if (! $this->exportBatch) {
                return;
            }

            $processedJobs = $this->exportBatch->processedJobs();
            $this->progress = $this->totalChunks > 0
                ? intval(($processedJobs / $this->totalChunks) * 10)
                : 0;

            session(['progress' => $this->progress]);

            if ($this->exportBatch->finished()) {
                $this->completeExport();
            }
        } catch (\Exception $e) {
            $this->handleExportError($e);
        }
    }

    private function completeExport()
    {
        session([
            'exporting' => false,
            'exportFinished' => true,
            'progress' => 100,
        ]);

        $this->exportFinished = true;
        $this->exporting = false;
        $this->progress = 100;
    }

    private function handleExportError(\Exception $e)
    {
        Log::error('Error updating export progress: ' . $e->getMessage(), [
            'exception' => $e,
        ]);

        session([
            'exporting' => false,
            'exportFinished' => false,
            'progress' => 0,
        ]);

        $this->exportFinished = false;
        $this->exporting = false;
        $this->progress = 0;
    }

    public function getExportBatchProperty()
    {
        if (! $this->batchId) {
            return;
        }

        return Bus::findBatch($this->batchId);
    }

    public function downloadExport()
    {
        $this->fileName = session('fileName') ? session('fileName') : $this->fileName;

        if ($this->fileName) {

            return redirect()->route('admin.export.download', ['fileName' => $this->fileName]);
        }

        session()->flash('error', 'File not found. Please try again.');

        return redirect()->back();
    }

    public function resetFilter()
    {
        $this->startDate = null;
        $this->endDate = null;
        $this->created_by = '';
        $this->lead_by = '';
        $this->system_status = '';
        $this->courier_status = '';
        $this->platform = '';
        $this->search = '';
        $this->exportFinished = false;
        $this->exporting = false;
        $this->payment_status = '';

        // Clear the session after download
        session()->forget('exporting');
        session()->forget('exportFinished');
        session()->forget('exporting');
        session()->forget('progress');
        session()->forget('batchId');
        session()->forget('totalChunks');
        session()->forget('fileName');
    }

    public function render()
    {
        $query = $this->buildQuery();

        $sales = $query->paginate($this->perPage);

        $adminUser = Admin::select('id', 'name')->get();

        $leads = Employee::select('id', 'employee_name')->get();

        $carrybeeAccountsForSend = [];
        if ((int) GeneralSetting::query()->value('enable_carrybee') === 1) {
            $carrybeeAccountsForSend = array_values(array_map(
                static fn(array $a): array => [
                    'key' => $a['key'],
                    'title' => $a['account_name'] ?? $a['label'],
                    'store_id' => trim((string) ($a['store_id'] ?? '')),
                ],
                CarrybeeIntegrationService::accountsForDisplay()
            ));
        }

        return view('livewire.sales-table', [
            'sales' => $sales,
            'leads' => $leads,
            'adminUser' => $adminUser,
            'carrybeeAccountsForSend' => $carrybeeAccountsForSend,
        ]);
    }
}
