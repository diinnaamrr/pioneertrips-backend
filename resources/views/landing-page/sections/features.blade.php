<section id="features" class="py-5 bg-light">
    <div class="container">

        {{-- Section Header --}}
        <div class="text-center mb-5">
            <h2 class="fw-bold text-primary mb-3">Why Choose <span class="wayak-span">Wayak?</span></h2>
            <p class="text-secondary fs-5 mx-auto" style="max-width:700px;">
                Wayak combines ride, delivery, and loyalty in one seamless platform. Smart, secure, and rewarding for both users and drivers.
            </p>
        </div>

        {{-- Features Row --}}
        <div class="row g-4 justify-content-center">

            {{-- Feature Card --}}
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card text-center border-0 shadow-sm p-4 feature-card">
                    <div class="bg-primary bg-opacity-10 rounded-circle mx-auto mb-3" style="width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
                        <img src="{{ asset('public/landing-page/assets/img/platform/1.png') }}" class="img-fluid" alt="Accounts" style="width:50px; height:50px;">
                    </div>
                    <h5 class="fw-bold text-primary mb-2 feature-title">User & Driver Accounts</h5>
                    <p class="text-secondary">Choose your account type – User or Driver. Drivers can deliver people, goods, or both.</p>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card text-center border-0 shadow-sm p-4 feature-card">
                    <div class="bg-warning bg-opacity-10 rounded-circle mx-auto mb-3" style="width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
                        <img src="{{ asset('public/landing-page/assets/img/platform/1.png') }}" class="img-fluid" alt="Smart Trip Booking" style="width:50px; height:50px;">
                    </div>
                    <h5 class="fw-bold text-primary mb-2 feature-title">Smart Trip Booking</h5>
                    <p class="text-secondary">Instant or scheduled trips, regular, shared, or cargo rides with real-time pricing and negotiation.</p>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card text-center border-0 shadow-sm p-4 feature-card">
                    <div class="bg-success bg-opacity-10 rounded-circle mx-auto mb-3" style="width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
                        <img src="{{ asset('public/landing-page/assets/img/platform/1.png') }}" class="img-fluid" alt="Secure Trips" style="width:50px; height:50px;">
                    </div>
                    <h5 class="fw-bold text-primary mb-2 feature-title">Secure & Verified</h5>
                    <p class="text-secondary">Driver verification, OTP confirmation, and full transaction logs for security.</p>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card text-center border-0 shadow-sm p-4 feature-card">
                    <div class="bg-warning bg-opacity-10 rounded-circle mx-auto mb-3" style="width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
                        <img src="{{ asset('public/landing-page/assets/img/platform/1.png') }}" class="img-fluid" alt="Loyalty" style="width:50px; height:50px;">
                    </div>
                    <h5 class="fw-bold text-primary mb-2 feature-title">Loyalty & Rewards</h5>
                    <p class="text-secondary">Earn points that can be converted to cash. Invite friends and get rewards.</p>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card text-center border-0 shadow-sm p-4 feature-card">
                    <div class="bg-info bg-opacity-10 rounded-circle mx-auto mb-3" style="width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
                        <img src="{{ asset('public/landing-page/assets/img/platform/1.png') }}" class="img-fluid" alt="Chat" style="width:50px; height:50px;">
                    </div>
                    <h5 class="fw-bold text-primary mb-2 feature-title">Real-time Communication</h5>
                    <p class="text-secondary">Chat directly with drivers or users for seamless coordination.</p>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card text-center border-0 shadow-sm p-4 feature-card">
                    <div class="bg-danger bg-opacity-10 rounded-circle mx-auto mb-3" style="width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
                        <img src="{{ asset('public/landing-page/assets/img/platform/1.png') }}" class="img-fluid" alt="Payments" style="width:50px; height:50px;">
                    </div>
                    <h5 class="fw-bold text-primary mb-2 feature-title">Flexible Payments</h5>
                    <p class="text-secondary">Pay using InstaPay, card, or mobile wallet for rides or deliveries.</p>
                </div>
            </div>

        </div>

        {{-- CTA Button --}}
        <div class="text-center mt-5">
            <a href="#download" class="btn btn-warning btn-lg fw-bold px-5 py-3">Download Wayak Now</a>
        </div>

    </div>
</section>

{{-- Scroll Animation with AOS --}}
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
.feature-card {
    transition: transform 0.4s, box-shadow 0.4s;
}
.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}
.feature-title:hover {
    color: #FF7D2E !important;
}
</style>
