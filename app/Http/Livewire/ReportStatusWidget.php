<?php

namespace App\Http\Livewire;

use App\Jobs\Report\StoreReportPdfInFileSystem;
use App\Models\Report;
use App\Tweekracht\Actions\Reports\ReportDeleteAction;
use App\Tweekracht\Html\Alert;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ReportStatusWidget extends Component
{
    public Report $report;

    public function render()
    {
        return view('livewire.report-status-widget', [
            'report' => $this->report,
        ]);
    }

    public function createFile()
    {
        if (
            Storage::disk('reports')
                ->exists($this->report->client->report_file_name)
        ) {
            Storage::disk('reports')
                ->delete($this->report->client->report_file_name);
        }

        $this->report->update([
            'file_status' => Report::$FILE_STATUS_IN_THE_MAKE,
        ]);

        StoreReportPdfInFileSystem::dispatch($this->report);
    }

    public function destroy(ReportDeleteAction $reportDeleteAction)
    {
        $result = ($reportDeleteAction)($this->report);

        if (!$result) {
            return redirect()->back()
                ->with(Alert::DANGER, __('report.delete.messages.fail'));
        }

        return redirect()->route('reports')
            ->with(Alert::SUCCESS, __('report.delete.messages.success'));
    }
}
