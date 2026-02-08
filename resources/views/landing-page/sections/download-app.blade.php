<section id="download-app" class="download-section py-5">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- Left Content -->
            <div class="col-lg-6 text-center text-lg-start wow animate__fadeInLeft">
                <h2 class="fw-bold mb-3 download-title">
                    Download the <span class="wayak-span">Wayak App</span>
                </h2>

                <p class="text-light mb-4 download-text">
                    سواء كنت عميل أو سائق، Wayak بتقدملك أسرع، أسهل وأأمن تجربة توصيل
                    في مصر. امسح كود الـ QR، حمّل التطبيق وابدأ رحلتك دلوقتي!
                </p>

                <!-- QR Codes + Buttons -->
                <div class="d-flex flex-column gap-4">

                    <!-- User -->
                    <div class="d-flex align-items-center gap-3 justify-content-center justify-content-lg-start">
                        <img src="{{asset('/public/assets/admin-module/img/qr-code/user.png')}}" class="rounded-3" width="90">
                        <div class="d-flex flex-column gap-2">
                            <a target="_blank"
                               href="{{ $cta?->value['app_store']['user_download_link'] ?? '' }}">
                                <img src="{{ asset('public/landing-page/assets/img/app-store.png') }}" width="150">
                            </a>
                            <a target="_blank"
                               href="{{ $cta?->value['play_store']['user_download_link'] ?? '' }}">
                                <img src="{{ asset('public/landing-page/assets/img/play-store.png') }}" width="150">
                            </a>
                        </div>
                    </div>

                    <!-- Driver -->
                    <div class="d-flex align-items-center gap-3 justify-content-center justify-content-lg-start">
                        <img src="{{asset('/public/assets/admin-module/img/qr-code/driver.png')}}" class="rounded-3" width="90">
                        <div class="d-flex flex-column gap-2">
                            <a target="_blank"
                               href="{{ $cta?->value['app_store']['driver_download_link'] ?? '' }}">
                                <img src="{{ asset('public/landing-page/assets/img/app-store.png') }}" width="150">
                            </a>
                            <a target="_blank"
                               href="{{ $cta?->value['play_store']['driver_download_link'] ?? '' }}">
                                <img src="{{ asset('public/landing-page/assets/img/play-store.png') }}" width="150">
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phone Image -->
            <div class="col-lg-6 text-center wow animate__fadeInRight d-flex justify-content-center">
                <img src="{{ asset("public/landing-page/assets/img/wayak-clints3.png") }}" 
                     class="img-fluid phone-mockup rounded-4">
            </div>

        </div>
    </div>
</section>

<style>
.download-section {
    background: linear-gradient(135deg, #00579E 0%, #003A6F 50%, #FF7D2E 100%);
    border-radius: 0;
    position: relative;
    overflow: hidden;
}

/* Image Animation & Rounded */
.phone-mockup {
    max-width: 420px; /* أكبر شوية في Desktop */
    width: 100%;
    animation: float 3s ease-in-out infinite;
    border-radius: 1rem; /* Bootstrap-like rounded */
}

/* Float Animation */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-12px); }
    100% { transform: translateY(0px); }
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .phone-mockup {
        max-width: 360px;
    }
}

@media (max-width: 576px) {
    .phone-mockup {
        max-width: 100%; /* full width on mobile */
        height: auto;
    }

    .download-title {
        font-size: 1.5rem; /* أصغر على الموبايل */
    }

    .download-text {
        font-size: 0.95rem;
    }

    .download-section .row {
        text-align: center !important;
    }
}

/* Arabic font size adjustment for Desktop */
.download-title {
    font-size: 2rem;
}

.download-text {
    font-size: 1.1rem;
}
</style>
