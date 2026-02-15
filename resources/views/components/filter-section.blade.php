@props([
    'action',
    'searchLabel' => 'Pencarian',
    'searchPlaceholder' => 'Cari...',
    'departemens' => [],
    'currentSearch' => '',
    'currentDepartemen' => '',
    'currentStartDate' => '',
    'currentEndDate' => '',
    'showSearch' => true,
    'showDepartemen' => true,
    'showDate' => true,
    'searchName' => 'search',
    'departemenName' => 'departemen_id',
    'startDateName' => 'start_date',
    'endDateName' => 'end_date',
])

<div class="filter-container">
    <form id="filterForm" action="{{ $action }}" method="GET" class="filter-form">
        @if($showSearch)
        <div class="filter-group search-wrapper">
            <label class="filter-label">{{ $searchLabel }}</label>
            <div class="search-group">
                <div class="search-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" name="{{ $searchName }}" id="searchInput" value="{{ $currentSearch }}" class="filter-input search-input" placeholder="{{ $searchPlaceholder }}">
            </div>
        </div>
        @endif

        @if($showDepartemen && count($departemens) > 0)
        <div class="filter-group">
            <label class="filter-label">Departemen</label>
            <select name="{{ $departemenName }}" id="departemenInput" class="filter-select">
                <option value="">Semua Departemen</option>
                @foreach($departemens as $dept)
                    <option value="{{ $dept->departemen_id }}" {{ $currentDepartemen == $dept->departemen_id ? 'selected' : '' }}>
                        {{ $dept->nama_departemen }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        @if($showDate)
        <div class="filter-group">
            <label class="filter-label">Tanggal</label>
            <div class="date-group">
                <input type="date" name="{{ $startDateName }}" id="startDateInput" value="{{ $currentStartDate }}" class="filter-input">
                <span class="date-separator">-</span>
                <input type="date" name="{{ $endDateName }}" id="endDateInput" value="{{ $currentEndDate }}" class="filter-input">
            </div>
        </div>
        @endif

        <div class="filter-actions">
            <button type="submit" class="btn-filter">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filter
            </button>
            <a href="{{ $action }}" class="btn-reset" title="Reset Filter">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </a>
        </div>
    </form>
</div>

@push('styles')
<style>
    .filter-container {
        padding: 1.5rem;
        background: #f8fafc;
        border: 1px solid #e8ecf1;
        border-radius: 1rem;
        margin-bottom: 0;
    }

    .filter-form {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto auto;
        gap: 1rem;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        margin: 0;
        line-height: 1;
        padding-left: 0.25rem;
    }

    .search-group {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }

    .search-icon svg {
        width: 18px;
        height: 18px;
    }

    .filter-input, .filter-select {
        height: 42px;
        padding: 0 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        color: #1e293b;
        background: white;
        transition: all 0.2s;
        outline: none;
    }

    .search-input {
        padding-left: 3rem;
    }

    .date-group {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0;
        overflow: hidden;
        display: flex;
        align-items: center;
        flex: 0 1 auto;
    }

    .date-group .filter-input {
        border: none;
        border-radius: 0;
        width: 140px;
        padding: 0 0.5rem;
        color: #475569;
        height: 42px;
    }

    .date-separator {
        color: #94a3b8;
        font-weight: 500;
        padding: 0 0.25rem;
    }

    .filter-select {
        min-width: 140px;
        flex: 0 1 auto;
    }

    .filter-input:focus, .filter-select:focus {
        border-color: #5575a2;
        box-shadow: 0 0 0 3px rgba(85, 117, 162, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 0.5rem;
        flex: 0 1 auto;
    }

    .btn-filter {
        height: 42px;
        padding: 0 1.5rem;
        background: #5575a2;
        color: white;
        border: none;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-filter:hover {
        background: #3d5885;
        box-shadow: 0 4px 12px rgba(85, 117, 162, 0.2);
        transform: translateY(-1px);
    }

    .btn-filter svg {
        width: 18px;
        height: 18px;
    }

    .btn-reset {
        height: 42px;
        width: 42px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        flex-shrink: 0;
    }

    .btn-reset:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .btn-reset svg {
        width: 18px;
        height: 18px;
        color: #64748b;
    }

    @media (max-width: 1024px) {
        .filter-form {
            grid-template-columns: 1fr 1fr auto auto;
            gap: 0.75rem;
        }

        .search-group {
            min-width: 200px;
        }
    }

    @media (max-width: 768px) {
        .filter-container {
            padding: 1rem;
        }

        .filter-form {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .filter-actions {
            width: 100%;
            gap: 0.5rem;
        }

        .btn-filter {
            flex: 1;
            justify-content: center;
        }

        .search-group {
            width: 100%;
            min-width: unset;
        }

        .date-group {
            width: 100%;
        }

        .date-group .filter-input {
            flex: 1;
            width: auto;
        }
    }
</style>
@endpush
