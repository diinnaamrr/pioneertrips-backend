<section id="how" class="basic-info-section py-5">
    <div class="container">
        <h2 class="text-center fw-bold mb-5" style="color:#00579E;">
    		Our Achievements in Numbers
		</h2>

        <div class="row g-4 justify-content-center">

            {{-- Card 1 --}}
            <div class="col-6 col-md-6 col-lg-3 wow animate__animated animate__fadeInUp" data-wow-delay="0s">
                <div class="basic-info-card text-center p-4 rounded shadow-sm h-100">
                    <div class="icon-wrapper mb-3">
                        <img src="{{ asset('public/landing-page/assets/img/icons/1.png') }}" alt="Download App" class="img-fluid">
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color:#FF7D2E;">1M+</h3>
                    <p class="text-muted">Download</p>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="col-6 col-md-6 col-lg-3 wow animate__animated animate__fadeInUp" data-wow-delay="0.1s">
                <div class="basic-info-card text-center p-4 rounded shadow-sm h-100">
                    <div class="icon-wrapper mb-3">
                        <img src="{{ asset('public/landing-page/assets/img/icons/2.png') }}" alt="Complete Ride" class="img-fluid">
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color:#FF7D2E;">1M+</h3>
                    <p class="text-muted">Complete Ride</p>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="col-6 col-md-6 col-lg-3 wow animate__animated animate__fadeInUp" data-wow-delay="0.2s">
                <div class="basic-info-card text-center p-4 rounded shadow-sm h-100">
                    <div class="icon-wrapper mb-3">
                        <img src="{{ asset('public/landing-page/assets/img/icons/3.png') }}" alt="Happy Customer" class="img-fluid">
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color:#FF7D2E;">1M+</h3>
                    <p class="text-muted">Happy Customer</p>
                </div>
            </div>

            {{-- Card 4 --}}
            <div class="col-6 col-md-6 col-lg-3 wow animate__animated animate__fadeInUp" data-wow-delay="0.3s">
                <div class="basic-info-card text-center p-4 rounded shadow-sm h-100">
                    <div class="icon-wrapper mb-3">
                        <img src="{{ asset('public/landing-page/assets/img/icons/4.png') }}" alt="24/7 Support" class="img-fluid">
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color:#FF7D2E;">24/7</h3>
                    <p class="text-muted">Support</p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Animate.css + WOW.js --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
<script>
    new WOW().init();
</script>

<style>
.basic-info-section {
    background-color: #f9f9f9;
}

/* Cards */
.basic-info-card {
    background: #fff;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    cursor: pointer;
    border-top: 4px solid #FF7D2E;
}

.basic-info-card:hover {
    transform: translateY(-10px) rotate(-1deg);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    border-top-color: #00579E;
}

.basic-info-card .icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, #00579E 50%, #FF7D2E 100%);
    transition: background 0.4s ease;
}

.basic-info-card:hover .icon-wrapper {
    background: linear-gradient(135deg, #FF7D2E 0%, #00579E 100%);
}

.basic-info-card img {
    width: 50%;
    transition: transform 0.4s ease;
}

.basic-info-card:hover img {
    transform: scale(1.2);
}

/* Responsive */
@media (max-width: 992px) {
    .basic-info-card .icon-wrapper {
        width: 70px;
        height: 70px;
    }
}

@media (max-width: 576px) {
    .basic-info-card .icon-wrapper {
        width: 60px;
        height: 60px;
    }
    .col-6 {
        width: 50% !important; /* 2 cards per row */
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}
</style>
