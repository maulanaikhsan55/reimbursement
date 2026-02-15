<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- PREVENT FOUC - MUST BE AT TOP -->
    <style>
        body { 
            opacity: 0; 
            visibility: hidden;
            background: #ffffff;
        }
        body.ready { 
            opacity: 1; 
            visibility: visible;
            transition: opacity 0.2s ease-out;
        }
    </style>

    <title>@yield('title', 'Smart Reimbursement')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js', 'resources/js/auth.js'])
    @stack('styles')
    <style>
        @keyframes clip-loader-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .clip-loader {
            background: transparent !important;
            width: 32px;
            height: 32px;
            border-radius: 100%;
            border: 3px solid;
            border-color: #425d87;
            border-bottom-color: transparent;
            display: inline-block;
            animation: clip-loader-spin 0.75s 0s infinite linear;
            animation-fill-mode: both;
        }
    </style>
</head>
<body>
    <!-- Global Loader -->
    <div id="global-loader" style="position: fixed; inset: 0; z-index: 9999; background-color: rgba(255, 255, 255, 0.9); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; transition: opacity 0.3s ease-out;">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
            <div class="clip-loader"></div>
            <span class="text-xs text-gray-500 font-medium tracking-wide" style="font-family: 'Poppins', sans-serif;">loading...</span>
        </div>
    </div>

    <div class="guest-page">
        <div class="guest-content">
            @yield('content')
        </div>
    </div>
    
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('global-loader');
            
            // Function to hide loader
            const hideLoader = () => {
                loader.style.opacity = '0';
                loader.style.pointerEvents = 'none';
                document.body.classList.add('ready');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 300);
            };

            // Function to show loader immediately
            const showLoader = (e) => {
                if (e && (e.ctrlKey || e.metaKey || e.button === 1)) return;
                
                loader.style.display = 'flex';
                requestAnimationFrame(() => {
                    loader.style.opacity = '1';
                    loader.style.pointerEvents = 'auto';
                });
            };

            // Hide initially
            setTimeout(hideLoader, 50); 

            // Intercept links
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link) {
                    const href = link.getAttribute('href');
                    const target = link.getAttribute('target');
                    
                    if (href && 
                        !href.startsWith('#') && 
                        !href.startsWith('javascript:') && 
                        !href.startsWith('mailto:') && 
                        !href.startsWith('tel:') && 
                        target !== '_blank' &&
                        href !== window.location.href
                    ) {
                        try {
                            const url = new URL(href, window.location.origin);
                            if (url.origin === window.location.origin) {
                                showLoader(e);
                            }
                        } catch (err) {}
                    }
                }
            });

            // Handle Forms
            document.addEventListener('submit', function(e) {
                showLoader();
            });

            // Handle Back Button
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    hideLoader();
                }
            });
            
            // Fallback
            window.addEventListener('load', hideLoader);
        });
    </script>
</body>
</html>
