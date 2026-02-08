@php
    $primary_color = '#00579E'; // Wayak Blue
    $secondary_color = '#FF7D2E'; // Wayak Orange
    $light_blue = '#E9F6FF'; // Very light blue for background contrast
    $mockup_image_path = asset("public/landing-page/assets/img/wayak-clints3.png");
@endphp

<section id="wayak-showcase" class="wayak-showcase-section py-5 py-lg-7" style="background-color: {{ $light_blue }};">
    <div class="container">
        
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bolder mb-2" style="color: {{ $primary_color }};">
                {{ translate('Wayak نقلة نوعية في عالم التوصيل مع') }}
            </h2>
            <p class="lead text-muted mx-auto" style="max-width: 600px; font-size: 1.15rem;">
                {{ translate('وياك الحل الأمثل لتنقلاتك وشحناتك في مصر. يمنحك القوة لتختار السعر، مع أحدث طرق الدفع.') }}
            </p>
        </div>

        <div class="row align-items-center g-5">
            
            {{-- Image Column (Hidden on Mobile/Tablet) --}}
            <div class="col-lg-6 order-1 order-lg-1 d-none d-lg-block text-center">
                <div class="mockup-container">
                    <img src="{{ $mockup_image_path }}" 
                         alt="Wayak App Mockup"
                         class="img-fluid wayak-mockup-img shadow-lg"
                         style="max-width: 400px;">
                    
                    {{-- Blue/Orange Highlight Circle --}}
                    <div class="highlight-circle" style="background-color: {{ $primary_color }};"></div>
                </div>
            </div>

            {{-- Feature Highlights --}}
            <div class="col-lg-6 order-2 order-lg-2">
                
                {{-- Feature 1: Price Negotiation --}}
                <div class="feature-item p-4 mb-4 rounded-3 shadow-hover-wayak border-start border-5" style="border-color: {{ $secondary_color }} !important; background-color: white;">
                    <div class="d-flex align-items-start mb-2">
                        <i class="bi bi-tags-fill me-3 fs-3" style="color: {{ $secondary_color }};"></i>
                        <div>
                            <h5 class="fw-bold mb-1" style="color: {{ $primary_color }};">{{ translate('حدد سعرك وتفاوض!') }}</h5>
                            <p class="text-muted small mb-0">{{ translate('لك مطلق الحرية في المساومة على سعر رحلتك واختيار العرض الأنسب لك، لضمان أعلى توفير.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Feature 2: Security and Safety --}}
                <div class="feature-item p-4 mb-4 rounded-3 shadow-hover-wayak border-start border-5" style="border-color: {{ $secondary_color }} !important; background-color: white;">
                    <div class="d-flex align-items-start mb-2">
                        <i class="bi bi-shield-lock-fill me-3 fs-3" style="color: {{ $secondary_color }};"></i>
                        <div>
                            <h5 class="fw-bold mb-1" style="color: {{ $primary_color }};">{{ translate('أمان رحلتك أولويتنا (OTP)') }}</h5>
                            <p class="text-muted small mb-0">{{ translate('نظام تأكيد الركوب عبر كلمة مرور لمرة واحدة (OTP) لسلامتك، بالإضافة إلى التتبع المباشر.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Feature 3: Local Payment Innovation --}}
                <div class="feature-item p-4 rounded-3 shadow-hover-wayak border-start border-5" style="border-color: {{ $secondary_color }} !important; background-color: white;">
                    <div class="d-flex align-items-start mb-2">
                        <i class="bi bi-wallet-fill me-3 fs-3" style="color: {{ $secondary_color }};"></i>
                        <div>
                            <h5 class="fw-bold mb-1" style="color: {{ $primary_color }};">{{ translate('دفع مرن ومحفظة ذكية') }}</h5>
                            <p class="text-muted small mb-0">{{ translate('ندعم Instapay، البطاقات البنكية، والمحفظة الداخلية للعملاء والسائقين على حد سواء.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* CSS Utilities */
.py-5 { padding-top: 3rem !important; padding-bottom: 3rem !important; }
.py-lg-7 { padding-top: 5rem !important; padding-bottom: 5rem !important; }

/* 1. Base Section Styling */
.wayak-showcase-section {
    position: relative;
    overflow: hidden; /* **الحل لمشكلة التمرير الأفقي** */
}

/* 2. Mockup Container Styling */
.mockup-container {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}
.wayak-mockup-img {
    border-radius: 20px;
    transition: transform 0.6s ease-in-out;
    position: relative;
    z-index: 10;
}
.wayak-mockup-img:hover {
    transform: translateY(-8px) scale(1.02);
}

/* 3. Highlight Circle (Behind Mockup) */
.highlight-circle {
    width: 300px;
    height: 300px;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.2;
    box-shadow: 0 0 50px 30px var(--wayak-blue, #00579E);
}


/* 4. Feature Cards Styling */
.feature-item {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.shadow-hover-wayak:hover {
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
    border-color: var(--wayak-blue, #00579E) !important;
}

/* Hide Image on Mobile/Tablet */
@media (max-width: 991.98px) {
    .d-lg-block {
        display: none !important; /* إخفاء عمود الصورة بالكامل في الشاشات الصغيرة */
    }
}
</style>
@endpush