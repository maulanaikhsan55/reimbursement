@extends('layouts.app')

@section('title', 'Audit Budget Bulanan')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Audit Budget Bulanan" subtitle="Evaluasi penggunaan anggaran tiap departemen per bulan" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <!-- Filter Section -->
            <div class="filter-container">
                <form action="{{ route('finance.report.budget_audit') }}" method="GET" class="filter-form-audit">
                    <div class="filter-group-audit">
                        <label class="filter-label-audit">Pilih Tahun</label>
                        <select name="year" class="filter-input-audit">
                            @for($y = date('Y'); $y >= 2023; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="filter-group-audit">
                        <label class="filter-label-audit">Departemen</label>
                        <select name="departemen_id" class="filter-input-audit">
                            <option value="">Semua Departemen</option>
                            @foreach($allDepartemens as $dept)
                                <option value="{{ $dept->departemen_id }}" {{ $departemenId == $dept->departemen_id ? 'selected' : '' }}>{{ $dept->nama_departemen }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-actions-audit">
                        <button type="submit" class="btn-modern btn-modern-primary btn-modern-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('finance.report.budget_audit') }}" class="btn-reset-audit" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Data Table -->
            <section class="modern-section dashboard-card">
                <div class="data-table-wrapper" style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%; min-width: 1200px;">
                        <thead>
                            <tr>
                                <th style="width: 15%; position: sticky; left: 0; background: #f8fafc; z-index: 2;">Departemen</th>
                                <th style="width: 8%; text-align: right;">Limit</th>
                                @for($m = 1; $m <= 12; $m++)
                                    <th style="width: 6%; text-align: right;">{{ Carbon\Carbon::create(2000, $m, 1)->format('M') }}</th>
                                @endfor
                                <th style="width: 9%; text-align: right; background: #f1f5f9;">Total Tahun</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditData as $data)
                            <tr>
                                <td style="font-weight: 600; color: #1e293b; position: sticky; left: 0; background: #fff; z-index: 1; border-right: 1px solid #e2e8f0;">
                                    {{ $data['departemen']->nama_departemen }}
                                </td>
                                <td style="text-align: right; color: #64748b; font-size: 0.75rem;">{{ number_format($data['budget_limit'], 0, ',', '.') }}</td>
                                @foreach($data['monthly_usage'] as $month => $usage)
                                    <td style="text-align: right; font-weight: 500; {{ $usage > $data['budget_limit'] ? 'color: #ef4444; font-weight: 700;' : 'color: #475569;' }}">
                                        {{ $usage > 0 ? number_format($usage, 0, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                                <td style="text-align: right; font-weight: 800; color: #425d87; background: #f8fafc;">
                                    {{ number_format($data['total_year'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 1rem; padding: 1rem; background: #fef2f2; border-radius: 8px; border: 1px solid #fee2e2;">
                    <p style="margin: 0; font-size: 0.8rem; color: #ef4444; font-weight: 600;">
                        * Angka berwarna merah menunjukkan penggunaan melebihi limit budget bulanan departemen.
                    </p>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .filter-form-audit {
        display: grid;
        grid-template-columns: 1fr 1.5fr auto;
        gap: 1.25rem;
        align-items: flex-end;
    }
    
    .filter-group-audit {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-label-audit {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .filter-input-audit {
        width: 100%;
        padding: 0.65rem 1rem;
        border-radius: 12px;
        border: 1.5px solid #e5eaf2;
        background: #f8fafc;
        color: #1e293b;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .filter-input-audit:focus {
        border-color: #425d87;
        background: white;
        box-shadow: 0 0 0 4px rgba(66, 93, 135, 0.1);
        outline: none;
    }
    
    .filter-actions-audit {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    
    .btn-reset-audit {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #64748b;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .btn-reset-audit:hover {
        background: #e2e8f0;
        color: #1e293b;
        transform: rotate(90deg);
    }
    
    .btn-reset-audit svg {
        width: 20px;
        height: 20px;
    }

    .data-table-wrapper::-webkit-scrollbar {
        height: 8px;
    }
    .data-table-wrapper::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .data-table-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .data-table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush
