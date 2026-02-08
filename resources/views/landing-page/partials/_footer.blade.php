@php
    // Define Wayak colors
    $primary_color = '#00579E'; // Wayak Blue
    $secondary_color = '#FF7D2E'; // Wayak Orange
    $dark_blue_gradient = '#004070'; // Darker shade of blue for gradient end (Used if Orange is too much)
    $orange_gradient = '#FA7637'; // User's custom orange gradient end

    // Dynamic data retrieval with safe fallback initialization
    $footerLogo = getSession('footer_logo');
    $email = getSession('business_contact_email') ?: 'wayak@gmail.com';
    $contactNumber = getSession('business_contact_phone') ?: '01200924442';
    $businessAddress = getSession('business_address') ?: 'Fifth settlement , New cairo , Egypt .';
    $businessName = getSession('business_name') ?: 'Wayak';
    $cta = getSession('cta');
    $copyrightText = getSession('copyright_text');
    
    // Social Links with safe execution
    $links = collect(); 
    try {
        $links = \Modules\BusinessManagement\Entities\SocialLink::where(['is_active'=>1])->orderBy('name','asc')->get();
    } catch (\Exception $e) {
        $links = collect();
    }
    
    $wayak_description = 'Wayak is your next-generation Egyptian ride-hailing and parcel delivery application, offering flexible pricing, driver selection, and secure payment options via Instapay and cards. We connect users and captains through a reliable and rewarding platform.';
@endphp

<footer class="text-white py-5 footer-gradient-bg px-3 px-md-0">
    <div class="container">
        <div class="row g-4">
            
            {{-- Column 1: Logo, Description & Social Media (Large Column) --}}
            <div class="col-lg-5 col-md-12 order-md-1 order-lg-1">
                <a href="{{ route('index') }}" class="d-flex align-items-center mb-3">
                    <img src="{{ $footerLogo ? asset("storage/app/public/business/".$footerLogo) : asset('public/landing-page/assets/img/heroLogo.png') }}"
                         alt="Wayak Logo"
                         class="img-fluid"
                         style="height: 50px;"> {{-- Changed back to 50px for sizing consistency --}}
                </a>
                <p class="text-white-70 mt-3 small mb-4">
                    {{ translate($wayak_description) }}
                </p>
                <div class="social-icons d-flex gap-3 mt-2">
                    @foreach($links as $link)
                        @php
                            $icon_name = '';
                            $icon_path = '';
                            if ($link->name == "facebook") { $icon_name = 'Facebook'; $icon_path = 'public/landing-page/assets/img/footer/facebook.png'; }
                            elseif ($link->name == "instagram") { $icon_name = 'Instagram'; $icon_path = 'public/landing-page/assets/img/footer/instagram.png'; }
                            elseif ($link->name == "twitter") { $icon_name = 'Twitter'; $icon_path = 'public/landing-page/assets/img/footer/twitter.png'; }
                            elseif ($link->name == "linkedin") { $icon_name = 'LinkedIn'; $icon_path = 'public/landing-page/assets/img/footer/linkedin.png'; }
                        @endphp
                        @if($icon_path)
                            <a href="{{ $link->link }}" target="_blank" class="social-icon-wrapper"
                               title="{{ $icon_name }}"
                               style="display: flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.1); transition: all 0.3s ease;"
                               onmouseover="this.style.backgroundColor='{{ $secondary_color }}'; this.style.transform='scale(1.1)'; "
                               onmouseout="this.style.backgroundColor='rgba(255, 255, 255, 0.1)'; this.style.transform='scale(1)';">
                                <img src="{{ asset($icon_path) }}" alt="{{ $icon_name }}" style="width: 18px; height: 18px; filter: invert(1);">
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Column 2: Quick Links & Contact Info (Medium Column) --}}
            <div class="col-lg-3 col-md-6 order-md-3 order-lg-2">
                <h6 class="text-white mb-3 fw-bold border-start border-3 ps-3" style="border-color: {{ $secondary_color }} !important;">{{ translate('Quick Links') }}</h6>
                <ul class="list-unstyled">
                    <li class="mb-1"><a href="{{ route('index') }}" class="text-white-70 text-decoration-none footer-link-hover">{{ translate('Home') }}</a></li>
                    <li class="mb-1"><a href="{{ route('about-us') }}" class="text-white-70 text-decoration-none footer-link-hover">{{ translate('About Us') }}</a></li>
                    <li class="mb-1"><a href="{{ route('contact-us') }}" class="text-white-70 text-decoration-none footer-link-hover">{{ translate('Contact Us') }}</a></li>
                    <li class="mb-1"><a href="{{ route('privacy') }}" class="text-white-70 text-decoration-none footer-link-hover">{{ translate('Privacy Policy') }}</a></li>
                    <li class="mb-1"><a href="{{ route('terms') }}" class="text-white-70 text-decoration-none footer-link-hover">{{ translate('Terms & Condition') }}</a></li>
                </ul>

                <h6 class="text-white mt-4 mb-3 fw-bold border-start border-3 ps-3" style="border-color: {{ $secondary_color }} !important;">{{ translate('Hotline') }}</h6>
                <span class="d-block text-white fw-bold mb-1">{{ translate('Inquiries') }}</span>
                <span class="d-block text-white-70 fs-3" style="color: {{ $secondary_color }} !important; font-weight: 900;">15675</span>

            </div>
            
            {{-- Column 3: Contact Details & Download Apps (Medium Column) --}}
            <div class="col-lg-4 col-md-12 order-md-2 order-lg-3">
                <h6 class="text-white mb-3 fw-bold border-start border-3 ps-3" style="border-color: {{ $secondary_color }} !important;">{{ translate('Contact Details') }}</h6>
                <ul class="list-unstyled">
                    {{-- Address --}}
                    <li class="d-flex align-items-start mb-2">
                        <img src="{{ asset('public/landing-page/assets/img/footer/pin.png') }}" alt="Location" class="me-3 mt-1 icon-sm">
                        <span class="text-white-70">{{ $businessAddress }}</span>
                    </li>
                    {{-- Phone --}}
                    <li class="d-flex align-items-start mb-2">
                        <img src="{{ asset('public/landing-page/assets/img/footer/tel.png') }}" alt="Phone" class="me-3 mt-1 icon-sm">
                        <a href="Tel:{{ $contactNumber }}" class="text-white-70 text-decoration-none footer-link-hover">{{ $contactNumber }}</a>
                    </li>
                    {{-- Email --}}
                    <li class="d-flex align-items-start mb-4">
                        <img src="{{ asset('public/landing-page/assets/img/footer/mail.png') }}" alt="Email" class="me-3 mt-1 icon-sm">
                        <a href="Mailto:{{ $email }}" class="text-white-70 text-decoration-none footer-link-hover">{{ $email }}</a>
                    </li>
                </ul>

                <h6 class="text-white mt-4 mb-3 fw-bold border-start border-3 ps-3" style="border-color: {{ $secondary_color }} !important;">{{ translate('Download The App') }}</h6>
                
                {{-- Horizontal Download Links --}}
                <div class="d-flex flex-wrap gap-3">
                    {{-- User App --}}
                    <div class="d-flex flex-column gap-2">
                        <small class="text-white-70">{{ translate('User') }}</small>
                        <a target="_blank" href="{{ $cta && $cta['app_store']['user_download_link'] ? $cta['app_store']['user_download_link'] : "#" }}">
                            <img src="{{ asset('public/landing-page') }}/assets/img/app-store.png" alt="App Store" class="w-120px footer-app-btn">
                        </a>
                        <a target="_blank" href="{{ $cta && $cta['play_store']['user_download_link'] ? $cta['play_store']['user_download_link'] : "#" }}">
                            <img src="{{ asset('public/landing-page') }}/assets/img/play-store.png" alt="Play Store" class="w-120px footer-app-btn">
                        </a>
                    </div>
                    
                    {{-- Driver App --}}
                    <div class="d-flex flex-column gap-2">
                        <small class="text-white-70">{{ translate('Driver') }}</small>
                        <a target="_blank" href="{{ $cta && $cta['app_store']['driver_download_link'] ? $cta['app_store']['driver_download_link'] : "#" }}">
                            <img src="{{ asset('public/landing-page') }}/assets/img/app-store.png" alt="App Store" class="w-120px footer-app-btn">
                        </a>
                        <a target="_blank" href="{{ $cta && $cta['play_store']['driver_download_link'] ? $cta['play_store']['driver_download_link'] : "#" }}">
                            <img src="{{ asset('public/landing-page') }}/assets/img/play-store.png" alt="Play Store" class="w-120px footer-app-btn">
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <hr class="text-white-50 mt-5 mb-4 opacity-50">

        {{-- Copyright --}}
        <div class="text-center text-white-70 small">
            {{ $copyrightText ? $copyrightText : '© '.date('Y').' '.$businessName.'. '.translate('All rights reserved.') }}
        </div>
    </div>
</footer>

<style>
    /* Gradient Background (Blue to Orange) */
    .footer-gradient-bg {
        background: {{ $primary_color }};
        background: linear-gradient(135deg, {{ $primary_color }} 0%, {{ $orange_gradient }} 100%);
        position: relative;
        overflow: hidden;
    }

    /* Text Colors */
    .text-white-70 {
        color: rgba(255, 255, 255, 0.7) !important;
    }

  @media (min-width: 768px) and (max-width: 991px) {
    footer.footer-gradient-bg {
        padding-left: 20px !important;
        padding-right: 20px !important;
    }
}

/* تغيير لون عناوين الفوتر للون الأسود */
footer.footer-gradient-bg h6 {
    color: #000 !important;
}


    /* Link Hover Effect (Orange under-dot and indent) */
    .footer-link-hover {
        position: relative;
        transition: all 0.3s ease;
        padding-left: 0; 
        display: inline-block;
        white-space: nowrap; /* Prevent wrapping on small space */
    }

    .footer-link-hover:hover {
        color: {{ $secondary_color }} !important; 
        padding-left: 8px; 
    }

    .footer-link-hover::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%) scaleX(0);
        width: 5px;
        height: 5px;
        background-color: {{ $secondary_color }};
        border-radius: 50%;
        transition: transform 0.3s ease;
    }

    .footer-link-hover:hover::before {
        transform: translateY(-50%) scaleX(1);
    }
    
    /* Social Icons - Hover for background and scale */
    .social-icon-wrapper {
        transition: all 0.3s ease !important;
    }
    .social-icon-wrapper:hover {
        background-color: {{ $secondary_color }} !important;
        transform: scale(1.1);
    }

    /* App button hover effect (lift) */
    .footer-app-btn {
        transition: transform 0.2s ease-in-out;
    }

    .footer-app-btn:hover {
        transform: translateY(-3px) scale(1.05);
    }
    
    .w-120px {
        width: 120px; /* Reduced button width */
    }
    .icon-sm {
        width: 20px;
        height: 20px;
        filter: invert(0.8);
    }
</style>