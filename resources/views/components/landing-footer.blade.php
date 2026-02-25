<footer class="landing-footer">
    <div class="footer-container">
        <div class="footer-top-band">
            <div class="footer-top-copy">
                <p class="footer-eyebrow">Ready to streamline reimbursement?</p>
                <h3>Built for teams that need speed, control, and audit-ready integrity.</h3>
            </div>
            <a href="#contact" class="btn btn-secondary footer-top-btn">Talk to Us</a>
        </div>

        <div class="footer-content">
            <div class="footer-brand">
                <a href="#home" class="footer-logo">
                    <img src="/images/logo.png" alt="Logo" class="footer-logo-img" width="350" height="100" loading="lazy" decoding="async">
                </a>
                <p>One modern platform for faster submissions, stronger controls, and cleaner reporting.</p>
            </div>

            <div class="footer-links-grid">
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#roles">Ecosystem</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Platform</h4>
                    <ul class="footer-links">
                        <li><a href="#proof">Operational Highlights</a></li>
                        <li><a href="#roles">Role Experiences</a></li>
                        <li><a href="#contact">Get Started</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Access</h4>
                    <ul class="footer-links">
                        @auth
                            @if(auth()->user()->isFinance())
                                <li><a href="{{ route('finance.dashboard') }}" data-external>Finance Dashboard</a></li>
                            @elseif(auth()->user()->isAtasan())
                                <li><a href="{{ route('atasan.dashboard') }}" data-external>Manager Dashboard</a></li>
                            @elseif(auth()->user()->isPegawai())
                                <li><a href="{{ route('pegawai.dashboard') }}" data-external>Employee Dashboard</a></li>
                            @endif
                        @else
                            <li><a href="{{ route('login') }}" data-external>Login</a></li>
                        @endauth
                        <li><a href="#how-it-works">Workflow Overview</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="footer-copyright">&copy; {{ date('Y') }} ReimburseSmart. All rights reserved.</p>
            <p class="footer-credit">Designed for modern finance operations.</p>
        </div>
    </div>
</footer>
