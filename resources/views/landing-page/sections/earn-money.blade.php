<section id="earn-money" class="py-5 bg-gradient-to-b from-gray-50 to-gray-100">
    <div class="container">
        <div class="row align-items-center g-5">

            {{-- Text Content --}}
            <div class="col-12 col-lg-6 order-2 order-lg-1" data-aos="fade-right">
                <h2 class="fw-bold text-primary mb-3">
                    Drive with <span class="wayak-span">Wayak</span> & Earn Money!
                </h2>

                <p class="text-secondary fs-5 mb-4">
                    Start earning from every ride and delivery with Wayak. Flexible hours, secure payments, and rewards that grow your income.
                </p>

                <ul class="list-unstyled mb-4 fs-5">
                    <li class="mb-3 d-flex align-items-center icon-hover">
                        <i class="bi bi-check-circle-fill me-3 feature-icon"></i>
                        Flexible working hours – drive anytime.
                    </li>
                    <li class="mb-3 d-flex align-items-center icon-hover">
                        <i class="bi bi-check-circle-fill me-3 feature-icon"></i>
                        Earn points and convert them to real rewards.
                    </li>
                    <li class="mb-3 d-flex align-items-center icon-hover">
                        <i class="bi bi-check-circle-fill me-3 feature-icon"></i>
                        Safe & verified users on every trip.
                    </li>
                </ul>

                <!-- Animated Button -->
                <a href="#download" class="btn-animated">
                    Join Now & Download App
                </a>
            </div>

            {{-- Image --}}
            <div class="col-12 col-lg-6 order-1 order-lg-2 text-center" data-aos="fade-left">
                <div class="image-wrapper">
                    <img src="{{ asset('public/landing-page/assets/img/rider.png') }}"
                         alt="Drive with Wayak"
                         class="img-fluid custom-img">
                </div>
            </div>

        </div>
    </div>
</section>

{{-- AOS --}}
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true
        });
    });
</script>

<style>
/* Image Hover + Mobile Fix */
.image-wrapper {
    overflow: hidden;
    border-radius: 35px;
}
.custom-img {
    border-radius: 35px;
    transition: transform 0.5s ease;
}
.custom-img:hover {
    transform: scale(1.07);
}

/* Mobile Image (Full width but half height) */
@media (max-width: 768px) {
    .custom-img {
        width: 100%;
        height: 50vh;
        object-fit: cover;
        border-radius: 28px;
    }
}

/* List Icons Glow Effect */
.feature-icon {
    color: #FF7D2E;
    font-size: 1.5rem;
    transition: 0.3s ease;
}
.icon-hover:hover .feature-icon {
    color: #00579E;
    text-shadow: 0 0 12px rgba(0, 87, 158, 0.6);
    transform: scale(1.2);
}

/* Super Animated Button */
.btn-animated {
    display: inline-block;
    padding: 14px 40px;
    font-weight: 700;
    font-size: 1.2rem;
    color: white;
    border-radius: 50px;
    background: linear-gradient(90deg, #FF7D2E, #FF9F60);
    box-shadow: 0px 8px 18px rgba(255, 125, 46, 0.4);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

/* Shine effect inside button */
.btn-animated::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 60%;
    height: 100%;
    background: rgba(255, 255, 255, 0.35);
    transform: skewX(-20deg);
    transition: 0.5s ease;
}

/* Hover Animation */
.btn-animated:hover {
    transform: scale(1.07);
    box-shadow: 0px 12px 25px rgba(255, 125, 46, 0.55);
}
.btn-animated:hover::before {
    left: 130%;
}
  /* **الكود المضاف لمعالجة مشكلة التمرير الأفقي (Fixing Horizontal Scroll Issue)** */
body {
    overflow-x: hidden;
}
#download-earn {
    overflow: hidden;
}
</style>
