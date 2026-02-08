@php($primary_color = '#00579E')
@php($secondary_color = '#FF7D2E')

<header id="main-header" class="sticky-top shadow-none">
    <nav class="navbar navbar-expand-lg px-3 py-2 fixed-top" id="wayak-navbar">
        <div class="container">

            {{-- Logo --}}
            <a class="navbar-brand d-flex align-items-center py-0" href="#">
                @php($logo = getSession('header_logo'))
                <img
                    src="{{ asset('public/landing-page/assets/img/heroLogo.png') }}"
                    alt="Wayak Logo"
                    class="img-fluid"
                    style="height: 40px; transition: height 0.3s ease;"
                >
            </a>

            {{-- Hamburger --}}
            <button
                class="navbar-toggler border-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#landingNavbar"
                aria-controls="landingNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                {{-- Fixing the Toggler Icon Visibility on Transparent Background --}}
                <span class="navbar-toggler-icon" style="filter: invert(1); border: 1px solid white; border-radius: 3px;"></span>
            </button>

            {{-- Menu --}}
            <div class="collapse navbar-collapse" id="landingNavbar">
                <ul class="navbar-nav mx-auto gap-lg-3 mt-3 mt-lg-0">
                    {{-- Nav Links --}}
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#">{{ translate('Home') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#about">{{ translate('About Us') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#features">{{ translate('Features') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#earn-money">{{ translate('Earn Money') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#download-app">{{ translate('Download App') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#testimonials">{{ translate('testimonials') }}</a>
                    </li>

                    {{-- Contact Button (Mobile) --}}
                    <li class="nav-item d-lg-none">
                        <a class="btn w-100 mt-2 text-white fw-bold" href="#contact-us"
                           style="background-color: {{ $secondary_color }};">
                            {{ translate('Contact Us') }}
                        </a>
                    </li>
                </ul>

                {{-- Contact Button (Desktop) --}}
                <div class="d-none d-lg-block">
                    <a href="#contact-us"
                    class="btn text-white fw-bold px-4"
                    style="background-color: {{ $secondary_color }}; transition: background-color 0.3s ease;"
                    onmouseover="this.style.backgroundColor='{{ $primary_color }}';"
                    onmouseout="this.style.backgroundColor='{{ $secondary_color }}';"
                    >
                        {{ translate('Contact Us') }}
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

{{-- Smooth Scrolling and Scroll Effect Script --}}
<script>
    const primaryColor = '{{ $primary_color }}';
    const secondaryColor = '{{ $secondary_color }}';
    const navbar = document.getElementById('wayak-navbar');
    const navLinks = navbar.querySelectorAll('.nav-link');
    const togglerIcon = navbar.querySelector('.navbar-toggler-icon');
    const logoImg = navbar.querySelector('.navbar-brand img');

    // 1. Scroll Effect Functionality
    function handleScroll() {
        if (window.scrollY > 50) { // Change color after 50px scroll
            navbar.classList.add('scrolled');
            // Change text color to dark when background is light
            navLinks.forEach(link => link.style.color = 'white');
            togglerIcon.style.filter = 'invert(1)'; // Keep toggler icon white on dark background
            togglerIcon.style.borderColor = 'white';
        } else {
            navbar.classList.remove('scrolled');
            // Change text color to white on transparent background
            navLinks.forEach(link => link.style.color = 'white');
            togglerIcon.style.filter = 'invert(1)'; // Keep toggler icon white on transparent background
            togglerIcon.style.borderColor = 'white';
        }
    }

    window.addEventListener('scroll', handleScroll);
    document.addEventListener('DOMContentLoaded', handleScroll); // Check position on load

    // 2. Smooth Scrolling
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    let offset = 0;
                    if(href !== '#home') { // Adjust for fixed header height
                        offset = navbar.offsetHeight + 10;
                    }
                    window.scrollTo({
                        top: target.offsetTop - offset,
                        behavior: 'smooth'
                    });
                    
                    // Close the mobile menu after click
                    if (window.innerWidth < 992 && navbar.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(document.getElementById('landingNavbar'), {
                            toggle: false
                        });
                        bsCollapse.hide();
                    }
                }
            });
        });
    });

</script>

<style>
    /* 1. Base Styles for Header */
    #wayak-navbar {
        background-color: transparent; /* Start Transparent */
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        padding-top: 0.5rem !important; /* Reduced Padding */
        padding-bottom: 0.5rem !important;
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    
    .nav-link {
        color: white !important; /* Default text color on transparent background */
    }

    /* 2. Scrolled State */
    #wayak-navbar.scrolled {
        background-color: {{ $primary_color }} !important; /* Blue background on scroll */
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    /* 3. Hover Effect on Links */
    .nav-link:hover {
        color: {{ $secondary_color }} !important;
    }

    /* Adjustments for Mobile Menu (when open) */
    .navbar-collapse.show {
        background-color: {{ $primary_color }};
        padding: 10px;
        border-radius: 5px;
    }
</style>