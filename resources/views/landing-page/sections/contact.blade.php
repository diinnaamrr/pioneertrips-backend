@php
    $primary_color = '#00579E'; // Wayak Blue
    $secondary_color = '#FF7D2E'; // Wayak Orange
    
    // Fallback Data (as previously defined)
    $email = 'wayak@gmail.com';
    $contactNumber = '01200924442';
    $businessAddress = 'Fifth settlement , New cairo , Egypt .';
@endphp

<section id="contact-us" class="py-5 bg-light">
    <div class="container">
        
        {{-- Section Title --}}
        <div class="text-center mb-5">
            <h2 class="fw-bold display-5 mb-2" style="color: {{ $primary_color }};">
                {{ translate('Contact Us') }}
            </h2>
            <p class="lead" style="color: #6c757d;">
                {{ translate('We are here to help you move forward. Reach out to the Wayak team.') }}
            </p>
        </div>

        {{-- Contact Cards Grid (Compact and Responsive) --}}
        <div class="row justify-content-center g-4">
            
            {{-- Card 1: Address --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="contact-card p-4 rounded-3 text-center shadow-sm">
                    <div class="icon-circle mb-3 mx-auto" style="background-color: {{ $primary_color }};">
                        {{-- Placeholder for Location Icon --}}
                        <i class="bi bi-geo-alt-fill text-white fs-4"></i>
                    </div>
                    <h6 class="fw-bold mb-2" style="color: {{ $primary_color }};">{{ translate('Our Main Office') }}</h6>
                    <p class="text-muted small">{{ $businessAddress }}</p>
                </div>
            </div>

            {{-- Card 2: Hotline / Phone --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="contact-card p-4 rounded-3 text-center shadow-sm">
                    <div class="icon-circle mb-3 mx-auto" style="background-color: {{ $primary_color }};">
                        {{-- Placeholder for Phone Icon --}}
                        <i class="bi bi-telephone-fill text-white fs-4"></i>
                    </div>
                    <h6 class="fw-bold mb-2" style="color: {{ $primary_color }};">{{ translate('Call Our Hotline') }}</h6>
                    <p class="fs-4 fw-bolder mb-1" style="color: {{ $secondary_color }};">15675</p>
                    <p class="text-muted small">({{ translate('General Inquiries & Support') }})</p>
                </div>
            </div>

            {{-- Card 3: Email --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="contact-card p-4 rounded-3 text-center shadow-sm">
                    <div class="icon-circle mb-3 mx-auto" style="background-color: {{ $primary_color }};">
                        {{-- Placeholder for Email Icon --}}
                        <i class="bi bi-envelope-fill text-white fs-4"></i>
                    </div>
                    <h6 class="fw-bold mb-2" style="color: {{ $primary_color }};">{{ translate('Email Support') }}</h6>
                    <a href="mailto:{{ $email }}" class="text-decoration-none fw-semibold" style="color: {{ $secondary_color }};">{{ $email }}</a>
                    <p class="text-muted small mt-1">({{ translate('We typically respond within 24 hours') }})</p>
                </div>
            </div>

        </div>
    </div>
</section>

<link rel="stylesheet" href="{{ asset('public/landing-page') }}/assets/css/bootstrap-icons.min.css" />
<style>
/* Custom Styles for the Contact Section */
.contact-card {
    background-color: #ffffff;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    min-height: 200px;
}

.contact-card:hover {
    border-color: {{ $primary_color }};
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 87, 158, 0.1) !important;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0, 87, 158, 0.3);
    transition: all 0.3s ease;
}

.contact-card:hover .icon-circle {
    background-color: {{ $secondary_color }} !important;
    box-shadow: 0 4px 10px rgba(255, 125, 46, 0.5);
}

/* Responsive adjustments for Font size */
@media (max-width: 576px) {
    .contact-card h6 {
        font-size: 1rem;
    }
    .contact-card p {
        font-size: 0.85rem;
    }
}
</style>