<section class="hero-section position-relative d-flex align-items-center">
    <div class="overlay"></div>
    <div class="container">
        <div class="row justify-content-center text-center py-5">
            <div class="col-lg-8 d-flex flex-column gap-5 ">
                
                <h1 class="fw-bold text-white mb-3 animate__animated animate__fadeInDown animate__delay-1s">
                    move smarter , Deliver Faster with
                    <span class="wayak-span">Wayak</span>
                </h1>

                <p class="text-light mb-4 animate__animated animate__fadeInUp animate__delay-2s">
                    Experience next-level delivery with seamless tracking, real-time updates,
                    and a modern platform built for speed, safety, and reliability.
                </p>

                <!-- Buttons -->
                <div class="d-flex justify-content-center flex-wrap gap-3 mt-5 animate__animated animate__fadeInUp animate__delay-3s buttons-wrapper">

                    <a href="#" class="btn wayak-btn-orange">
                        Download User App
                    </a>

                    <a href="#about" class="btn wayak-btn-blue">
                        Become a Driver
                    </a>

                </div>

            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
/*  color Wayak */
.wayak-span { color:#FF7D2E; }

/* bk img */
.hero-section {
    background: url('{{ asset("public/landing-page/assets/img/wayak-clints2.png") }}') no-repeat center center/cover;
    min-height: 90vh;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

/* Overlay */
.hero-section .overlay {
    position: absolute;
    inset: 0;
    background: rgba(52, 58, 64, 0.6);
    z-index: 1;
}

.hero-section * {
    position: relative;
    z-index: 2;
}


/*  general css */
.wayak-btn-orange,
.wayak-btn-blue {
    padding: 8px 20px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    border: 2px solid transparent;
    transition: all .3s ease;
    backdrop-filter: blur(2px);
}

/*  orange button  */
.wayak-btn-orange {
    color: #FF7D2E;
    background: rgba(255, 125, 46, 0.15);
    border-color: #FF7D2E;
}
.wayak-btn-orange:hover {
    background: #FF7D2E;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(255,125,46,0.5);
}

/*  blue button */
.wayak-btn-blue {
    color: #00579E;
    background: rgba(0, 87, 158, 0.15);
    border-color: #00579E;
}
.wayak-btn-blue:hover {
    background: #00579E;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0,87,158,0.5);
}

/* in mob and sm screen t7t b3d */
@media (max-width: 576px) {
    .wayak-btn-orange,
    .wayak-btn-blue {
        width: 48%;
        font-size: 0.85rem;
        padding: 14px;
    }
}

/* tablet and disktop ganb b3d*/
@media (min-width: 576px) {
    .wayak-btn-orange,
    .wayak-btn-blue {
        width: auto;
    }
}

/*  sizing */
@media (max-width: 992px) {
    .hero-section h1 { font-size: 2rem; }
    .hero-section p { font-size: 1rem; }
}

@media (max-width: 576px) {
    .hero-section h1 { font-size: 1.7rem; }
    .hero-section p { font-size: 0.95rem; }
}
</style>
