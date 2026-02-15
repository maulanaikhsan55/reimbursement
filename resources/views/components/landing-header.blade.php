<header class="landing-header">
    <div class="header-container">
        <a href="#home" class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-image">
        </a>

        <nav class="nav-menu">
            <a href="#home" class="nav-link">Home</a>
            <a href="#features" class="nav-link">Technology</a>
            <a href="#roles" class="nav-link">Ecosystem</a>
            <a href="#faq" class="nav-link">FAQ</a>
        </nav>

        <div class="header-actions">
            @auth
                @if(auth()->user()->isFinance())
                    <a href="{{ route('finance.dashboard') }}" class="btn btn-primary btn-sm" data-external>Dashboard</a>
                @elseif(auth()->user()->isAtasan())
                    <a href="{{ route('atasan.dashboard') }}" class="btn btn-primary btn-sm" data-external>Dashboard</a>
                @elseif(auth()->user()->isPegawai())
                    <a href="{{ route('pegawai.dashboard') }}" class="btn btn-primary btn-sm" data-external>Dashboard</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm" data-external>Get Started</a>
            @endauth
        </div>

        <div class="menu-toggle" id="menuToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</header>
