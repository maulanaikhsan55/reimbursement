@php
    $user = Auth::user();
    $routeName = request()->route()?->getName() ?? '';
    $segments = array_values(array_filter(explode('.', $routeName)));
    $skipSegments = ['index'];
    $labelMap = [
        'finance' => 'Finance',
        'pegawai' => 'Pegawai',
        'atasan' => 'Atasan',
        'dashboard' => 'Dashboard',
        'masterdata' => 'Master Data',
        'report' => 'Laporan',
        'reports' => 'Laporan',
        'approval' => 'Persetujuan',
        'history' => 'Riwayat',
        'disbursement' => 'Pencairan',
        'notifikasi' => 'Notifikasi',
        'profile' => 'Profil',
        'pengajuan' => 'Pengajuan',
        'kas_bank' => 'Kas/Bank',
        'kategori_biaya' => 'Kategori Biaya',
        'jurnal_umum' => 'Jurnal Umum',
        'buku_besar' => 'Buku Besar',
        'laporan_arus_kas' => 'Arus Kas',
        'budget_audit' => 'Budget Audit',
        'reconciliation' => 'Rekonsiliasi',
    ];

    $roleKey = strtolower(trim((string) ($user->role ?? '')));
    $crumbs = [];
    foreach ($segments as $index => $segment) {
        $segmentKey = strtolower(trim((string) $segment));
        if ($index === 0 && $segmentKey === $roleKey) {
            continue;
        }
        if (in_array($segment, $skipSegments, true)) {
            continue;
        }
        $label = $labelMap[$segment] ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', $segment));
        if (!empty($crumbs) && end($crumbs)['label'] === $label) {
            continue;
        }
        $crumbs[] = [
            'key' => $segment . '-' . $index,
            'label' => $label,
        ];
    }

    $homeLabel = ucfirst($user->role ?? 'User');
    $homeRouteName = ($user->role ?? '') . '.dashboard';
    $homeUrl = \Illuminate\Support\Facades\Route::has($homeRouteName) ? route($homeRouteName) : '#';

    $profileRoute = match($user->role ?? null) {
        'finance' => route('finance.profile.index'),
        'pegawai' => route('pegawai.profile.index'),
        'atasan' => route('atasan.profile.index'),
        default => '#'
    };
@endphp

<div class="page-header">
    <div class="page-header-left">
        <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ $homeUrl }}" class="breadcrumb-link">{{ $homeLabel }}</a>
            @foreach($crumbs as $crumb)
                <span class="breadcrumb-sep" aria-hidden="true">&rsaquo;</span>
                <span class="breadcrumb-current">{{ $crumb['label'] }}</span>
            @endforeach
        </nav>
        <h1 class="page-title">{{ $title }}</h1>
        @if($subtitle ?? false)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="page-header-right">
        <div class="page-time-inline" data-wib-clock aria-live="polite">
            <span class="day-value">---</span>
            <span class="time-value">--:--</span>
        </div>

        @if($showNotification ?? false)
            <div class="header-action-slot">
                <livewire:notification-bell />
            </div>
        @endif

        @if($showProfile ?? false)
            <a href="{{ $profileRoute }}" class="header-profile" title="Profil">
                <div class="header-profile-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
            </a>
        @endif
    </div>
</div>

<style>
    .page-header {
        --ph-accent: #3f5f94;
        --ph-accent-700: #2c4772;
        --ph-ink: #1d2a40;
        --ph-muted: #6a7d98;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.85rem;
        padding: 0.2rem 0.1rem 0.82rem;
        border-bottom: 1px solid rgba(63, 95, 148, 0.15);
        margin-top: -0.14rem;
        margin-bottom: 0.56rem;
        border-radius: 0;
        width: 100%;
        box-sizing: border-box;
    }

    .page-header-left {
        display: flex;
        flex-direction: column;
        gap: 0.16rem;
        min-width: 0;
        align-items: flex-start;
        text-align: left;
    }

    .page-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.48rem;
        font-size: 0.78rem;
        flex-wrap: wrap;
        margin-bottom: 0;
    }

    .breadcrumb-link {
        color: var(--ph-muted);
        text-decoration: none;
        font-weight: 500;
    }

    .breadcrumb-link:hover {
        color: var(--ph-accent);
    }

    .breadcrumb-sep {
        color: #8ea0ba;
        font-weight: 700;
        font-size: 0.9em;
        line-height: 1;
    }

    .breadcrumb-current {
        color: var(--ph-ink);
        font-weight: 700;
    }

    .page-title {
        font-size: 1.34rem;
        font-weight: 800;
        color: var(--ph-ink);
        margin: 0;
        line-height: 1.15;
        letter-spacing: 0.01em;
    }

    .page-subtitle {
        font-size: 0.82rem;
        color: var(--ph-muted);
        margin: 0;
        font-weight: 500;
        letter-spacing: 0.01em;
    }

    .page-header-right {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.58rem;
        flex-shrink: 0;
    }

    .page-time-inline {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 1.1;
    }

    .day-value {
        font-size: 0.66rem;
        color: #7b8ea8;
        font-weight: 600;
        text-transform: capitalize;
        letter-spacing: 0.01em;
    }

    .time-value {
        font-size: 0.74rem;
        color: #2b3d5f;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .header-action-slot {
        display: inline-flex;
        align-items: center;
    }

    .header-profile {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        cursor: pointer;
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid rgba(63, 95, 148, 0.18);
        background: #ffffff;
        transition: all 0.22s ease;
        text-decoration: none;
    }

    .header-profile:hover {
        background: #f9fbff;
        border-color: rgba(63, 95, 148, 0.24);
        box-shadow: 0 8px 18px rgba(38, 61, 100, 0.12);
        transform: translateY(-1px);
    }

    .header-profile-avatar {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        background: linear-gradient(145deg, var(--ph-accent) 0%, var(--ph-accent-700) 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.82rem;
        flex-shrink: 0;
        box-shadow: none;
    }

    @media (max-width: 1200px) {
        .header-profile {
            width: 38px;
            height: 38px;
        }
    }

    @media (max-width: 900px) {
        .page-header {
            gap: 0.5rem;
        }

        .page-time-inline {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            grid-template-columns: 1fr;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.18rem 0 0.58rem;
        }

        .page-header-right {
            width: 100%;
            justify-content: flex-end;
        }

        .page-title {
            font-size: 1.14rem;
        }

        .page-subtitle {
            font-size: 0.75rem;
        }
    }
</style>

<script data-navigate-once>
    function initPageHeaderWibClock() {
        const cards = document.querySelectorAll('[data-wib-clock]');
        if (!cards.length) return;

        const formatterDay = new Intl.DateTimeFormat('id-ID', {
            weekday: 'long',
            timeZone: 'Asia/Jakarta'
        });

        const formatterTime = new Intl.DateTimeFormat('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            timeZone: 'Asia/Jakarta'
        });

        const render = () => {
            const now = new Date();
            const dayName = formatterDay.format(now);
            const time = formatterTime.format(now);

            cards.forEach((card) => {
                const dayEl = card.querySelector('.day-value');
                const timeEl = card.querySelector('.time-value');
                if (dayEl) dayEl.textContent = dayName;
                if (timeEl) timeEl.textContent = `${time} WIB`;
            });
        };

        render();
        if (window.__wibClockInterval) {
            clearInterval(window.__wibClockInterval);
        }
        window.__wibClockInterval = setInterval(render, 60000);
    }

    document.addEventListener('DOMContentLoaded', initPageHeaderWibClock);
    document.addEventListener('livewire:navigated', initPageHeaderWibClock);
</script>
