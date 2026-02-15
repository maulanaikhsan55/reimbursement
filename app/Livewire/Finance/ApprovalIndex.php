<?php

namespace App\Livewire\Finance;

use App\Models\Departemen;
use App\Models\Pengajuan;
use App\Traits\FiltersPengajuan;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalIndex extends Component
{
    use FiltersPengajuan, WithPagination;

    public $search = '';

    public $departemen_id = '';

    public $status = 'menunggu_finance';

    public $start_date = '';

    public $end_date = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'departemen_id' => ['except' => ''],
        'status' => ['except' => 'menunggu_finance'],
        'start_date' => ['except' => ''],
        'end_date' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartemenId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'departemen_id', 'status', 'start_date', 'end_date']);
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->applyFinanceFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            [
                'search' => $this->search,
                'departemen_id' => $this->departemen_id,
                'status' => $this->status,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
            ],
            'menunggu_finance'
        );

        $pengajuans = $query->paginate(config('app.pagination.approval'));

        $totalPending = Pengajuan::where('status', 'menunggu_finance')->count();
        $totalNominalPending = Pengajuan::where('status', 'menunggu_finance')->sum('nominal');
        $departemens = Departemen::orderBy('nama_departemen')->get();

        return view('livewire.finance.approval-index', compact('pengajuans', 'totalPending', 'totalNominalPending', 'departemens'));
    }
}
