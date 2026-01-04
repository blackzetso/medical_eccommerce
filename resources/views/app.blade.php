<!DOCTYPE html>
@php
    $locale = session('locale', app()->getLocale());
    $dir = in_array($locale, ['ar', 'ar_SA', 'ar-EG']) ? 'rtl' : 'ltr';
@endphp
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $dir }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    @inertiaHead

    <meta name="keywords" content="HTML5 Template" />
    <meta name="description" content="Porto - Bootstrap eCommerce Template">
    <meta name="author" content="SW-THEMES">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Medical Shop">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('front/theme1/images/icons/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('front/theme1/images/icons/favicon.png') }}">
    
    <!-- Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">

    @php
        $webfontUrl = asset('front/theme1/js/webfont.js');
    @endphp
    <script>
        WebFontConfig = {
            google: { families: [ 'Open+Sans:300,400,600,700,800', 'Poppins:300,400,500,600,700' ] }
        };
        ( function ( d ) {
            var wf = d.createElement( 'script' ), s = d.scripts[ 0 ];
            wf.src = '{{ $webfontUrl }}';
            wf.async = true;
            s.parentNode.insertBefore( wf, s );
        } )( document );
    </script>

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('front/theme1/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/theme1/css/demo3.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('front/theme1/vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('front/theme1/vendor/simple-line-icons/css/simple-line-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/theme1/css/demo/custome.css') }}">

    <style>
        body,h1,h2,h3,h4,h5,h6,a,p,span,div,button,input,textarea, select , label , optgroup, option , li , td , th , a , ul , nav {
            font-family: 'Cairo', sans-serif !important;
        }
    </style>
</head>

<body class="{{ $dir === 'rtl' ? 'client-rtl' : 'client-ltr' }}">
    <!-- End .page-wrapper -->
    @inertia

    <!-- End .mobile-menu-container -->
    <!-- Plugins JS File -->
    <script src="{{ asset('front/theme1/js/jquery.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/plugins.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/nouislider.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/jquery.appear.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/main.min.js') }}"></script>
    
    <!-- PWA Install Button - Always Visible -->
    <div id="pwa-install-banner" data-position="{{ $dir === 'rtl' ? 'left' : 'right' }}" style="display: none; position: fixed; bottom: 20px; background: white; padding: 15px 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; max-width: 320px; font-family: Cairo, sans-serif; direction: rtl;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <span style="font-size: 24px;">📱</span>
            <div>
                <div style="font-weight: bold; color: #333;">تثبيت التطبيق</div>
                <div style="font-size: 12px; color: #666;">احصل على تجربة أفضل وتطبيق أسرع</div>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <button id="pwa-install-button" style="flex: 1; padding: 10px 16px; background: #0d6efd; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: 'Cairo', sans-serif; font-weight: bold; font-size: 14px;">تثبيت الآن</button>
            <button id="pwa-dismiss-button" style="padding: 10px 16px; background: #f0f0f0; color: #333; border: none; border-radius: 5px; cursor: pointer; font-family: 'Cairo', sans-serif;">إخفاء</button>
        </div>
    </div>

    <script>
        console.log('🚀 PWA Script Loading...');
        
        // Check if app is already installed
        function isPWAInstalled() {
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                                window.navigator.standalone === true ||
                                document.referrer.includes('android-app://');
            
            if (isStandalone) {
                console.log('✅ PWA is already installed');
            }
            return isStandalone;
        }

        // Check if manifest.json exists and is valid
        async function checkManifest() {
            try {
                const response = await fetch('/manifest.json');
                if (response.ok) {
                    const manifest = await response.json();
                    console.log('✅ Manifest.json loaded successfully:', manifest.name);
                    return true;
                } else {
                    console.error('❌ Manifest.json not found (status:', response.status + ')');
                    return false;
                }
            } catch (error) {
                console.error('❌ Error loading manifest.json:', error);
                return false;
            }
        }

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                console.log('📋 Checking Service Worker support...');
                
                // Check manifest first
                const manifestOk = await checkManifest();
                
                if (!manifestOk) {
                    console.warn('⚠️ Manifest check failed, but continuing...');
                }
                
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('✅ ServiceWorker registered successfully!');
                        console.log('   Scope:', registration.scope);
                        console.log('   Active:', registration.active ? 'Yes' : 'No');
                        
                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            console.log('🔄 Service Worker update found');
                            const newWorker = registration.installing;
                            if (newWorker) {
                                newWorker.addEventListener('statechange', () => {
                                    console.log('   State changed:', newWorker.state);
                                });
                            }
                        });
                        
                        // Check if service worker is controlling the page
                        if (registration.active) {
                            console.log('✅ Service Worker is active and controlling the page');
                        }
                    })
                    .catch((error) => {
                        console.error('❌ ServiceWorker registration failed!');
                        console.error('   Error details:', error);
                        console.error('   Make sure /sw.js file exists in public folder');
                    });
            });
        } else {
            console.warn('⚠️ Service Workers are not supported in this browser');
        }
        
        // PWA Install Prompt
        let deferredPrompt = null;
        let installBanner = null;
        let installButton = null;
        let dismissButton = null;

        // Wait for DOM to be ready
        function initPWA() {
            installBanner = document.getElementById('pwa-install-banner');
            installButton = document.getElementById('pwa-install-button');
            dismissButton = document.getElementById('pwa-dismiss-button');

            if (!installBanner || !installButton) {
                console.error('❌ PWA install elements not found in DOM');
                return;
            }

            // Show install banner function
            function showInstallBanner() {
                if (isPWAInstalled()) {
                    console.log('ℹ️ PWA already installed, hiding banner');
                    return;
                }
                
                const isDismissed = localStorage.getItem('pwa-dismissed') === 'true';
                if (isDismissed) {
                    console.log('ℹ️ User previously dismissed banner');
                    return;
                }
                
                if (installBanner) {
                    installBanner.style.display = 'block';
                    console.log('📱 Showing install banner');
                }
            }

            // Hide install banner
            function hideInstallBanner() {
                if (installBanner) {
                    installBanner.style.display = 'none';
                }
            }

            // Dismiss button handler
            if (dismissButton) {
                dismissButton.addEventListener('click', () => {
                    localStorage.setItem('pwa-dismissed', 'true');
                    hideInstallBanner();
                    console.log('👆 User dismissed install banner');
                });
            }

            // Install button handler
            if (installButton) {
                installButton.addEventListener('click', async () => {
                    console.log('👆 Install button clicked');
                    
                    if (deferredPrompt) {
                        console.log('📱 Showing install prompt...');
                        // Show the install prompt
                        deferredPrompt.prompt();
                        
                        // Wait for the user to respond to the prompt
                        const { outcome } = await deferredPrompt.userChoice;
                        
                        console.log(`📱 User response: ${outcome}`);
                        
                        if (outcome === 'accepted') {
                            console.log('✅ User accepted the install prompt');
                        } else {
                            console.log('❌ User dismissed the install prompt');
                        }
                        
                        deferredPrompt = null;
                        hideInstallBanner();
                    } else {
                        console.log('ℹ️ No deferredPrompt available, showing instructions');
                        // Fallback: Show instructions in a better way
                        const instructionsHTML = '<div style="text-align: right; direction: rtl; font-family: Cairo, sans-serif; padding: 20px;">' +
                            '<h3 style="margin-bottom: 15px; color: #333;">📱 تثبيت التطبيق</h3>' +
                            '<div style="margin-bottom: 15px;">' +
                                '<strong>🌐 على الكمبيوتر:</strong>' +
                                '<ol style="margin-top: 5px; padding-right: 20px;">' +
                                    '<li>ابحث عن <strong>أيقونة التثبيت</strong> في <strong>شريط العنوان</strong> (بجانب رابط الموقع)</li>' +
                                    '<li>أو اضغط على <strong>القائمة (⋮)</strong> في المتصفح واختر <strong>"تثبيت Medical Ecommerce"</strong></li>' +
                                '</ol>' +
                            '</div>' +
                            '<div style="margin-bottom: 15px;">' +
                                '<strong>📱 على الهاتف:</strong>' +
                                '<ul style="margin-top: 5px; padding-right: 20px;">' +
                                    '<li><strong>Android:</strong> ستظهر مطالبة تلقائياً أو من قائمة المتصفح (⋮)</li>' +
                                    '<li><strong>iPhone:</strong> اضغط على زر <strong>المشاركة (Share)</strong> ثم <strong>"أضف إلى الشاشة الرئيسية"</strong></li>' +
                                '</ul>' +
                            '</div>' +
                            '<div style="background: #f0f7ff; padding: 10px; border-radius: 5px; margin-top: 15px; font-size: 12px; color: #666;">' +
                                '💡 <strong>نصيحة:</strong> إذا لم تظهر أيقونة التثبيت، انتظر قليلاً ثم أعد تحميل الصفحة (F5)' +
                            '</div>' +
                        '</div>';
                        
                        // Create a modal instead of alert
                        const modal = document.createElement('div');
                        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 20000; display: flex; align-items: center; justify-content: center;';
                        modal.innerHTML = '<div style="background: white; border-radius: 10px; max-width: 500px; max-height: 80vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">' +
                            instructionsHTML +
                            '<div style="padding: 15px 20px; border-top: 1px solid #eee; text-align: left;">' +
                                '<button id="pwa-modal-close-btn" style="padding: 10px 20px; background: #0d6efd; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: Cairo, sans-serif; font-weight: bold;">حسناً</button>' +
                            '</div>' +
                        '</div>';
                        
                        // Add close button handler
                        document.body.appendChild(modal);
                        const closeBtn = modal.querySelector('#pwa-modal-close-btn');
                        if (closeBtn) {
                            closeBtn.addEventListener('click', () => {
                                modal.remove();
                            });
                        }
                        document.body.appendChild(modal);
                        
                        // Close on background click
                        modal.addEventListener('click', (e) => {
                            if (e.target === modal) {
                                modal.remove();
                            }
                        });
                    }
                });
            }
            
            // Listen for beforeinstallprompt event (may take a few seconds)
            window.addEventListener('beforeinstallprompt', (e) => {
                console.log('🔔 beforeinstallprompt event fired!');
                // Prevent the mini-infobar from appearing on mobile
                e.preventDefault();
                // Stash the event so it can be triggered later
                deferredPrompt = e;
                
                console.log('✅ Install prompt is now available');
                // Show our custom install banner immediately
                showInstallBanner();
            });
            
            // Detect if app is already installed
            window.addEventListener('appinstalled', () => {
                console.log('✅ PWA was installed successfully!');
                hideInstallBanner();
                deferredPrompt = null;
            });

            // Check if app meets installability criteria
            async function checkInstallability() {
                console.log('🔍 Checking PWA installability...');
                
                // Check manifest
                try {
                    const manifestResponse = await fetch('/manifest.json');
                    if (!manifestResponse.ok) {
                        console.warn('⚠️ Manifest.json not accessible');
                        return false;
                    }
                    const manifest = await manifestResponse.json();
                    console.log('✅ Manifest OK:', manifest.name);
                } catch (e) {
                    console.error('❌ Error loading manifest:', e);
                    return false;
                }
                
                // Check service worker
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    if (registrations.length > 0) {
                        console.log('✅ Service Worker registered');
                        for (let reg of registrations) {
                            if (reg.active) {
                                console.log('   Active SW:', reg.active.scriptURL);
                            }
                        }
                    } else {
                        console.warn('⚠️ No Service Worker registrations found');
                    }
                }
                
                // Check if already installed
                if (isPWAInstalled()) {
                    console.log('ℹ️ PWA is already installed');
                    return false;
                }
                
                return true;
            }
            
            // Show banner after checking everything
            setTimeout(async () => {
                console.log('⏰ Final check for install banner...');
                const canInstall = await checkInstallability();
                console.log('   Can show banner:', canInstall);
                console.log('   Deferred Prompt:', deferredPrompt ? 'Available' : 'Not available yet');
                console.log('   Dismissed:', localStorage.getItem('pwa-dismissed'));
                
                if (canInstall && !localStorage.getItem('pwa-dismissed')) {
                    // Show banner even if deferredPrompt is not available yet
                    // This gives users instructions on how to install
                    showInstallBanner();
                    
                    // If deferredPrompt is still null after 5 seconds, log it
                    if (!deferredPrompt) {
                        console.log('ℹ️ beforeinstallprompt not fired yet. This is normal and may happen if:');
                        console.log('   1. User needs to interact with the site more');
                        console.log('   2. Browser is still evaluating installability');
                        console.log('   3. User previously dismissed the prompt');
                        console.log('');
                        console.log('💡 Solution: Look for install icon in address bar or use browser menu');
                    }
                }
            }, 2000);
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPWA);
        } else {
            initPWA();
        }
    </script>
</body>

</html>
