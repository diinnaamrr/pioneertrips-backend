    <section id="testimonials" class="testimonial-section pt-50 pb-50">
        <div class="container-fluid">
            <h2 class="section-title mb-0 wow animate__fadeInUp"><span class="text--base">2000+</span> {{ translate('People Share Their Love') }}</h2>
            <div class="wow animate__fadeInDown">
                <div class="testimonial-slider owl-theme owl-carousel">
                    @if($testimonialListCount>0)
                        @foreach($testimonials as $testimonial)
                            @if($testimonial?->value['status'] == 1)
                                <!-- Testimonial Slider Single Slide -->
                                <div class="testimonial__item">
                                    <div class="testimonial__item-img">
                                        <img src="{{ onErrorImage(
                                        $testimonial?->value && $testimonial?->value['reviewer_image'] ? $testimonial?->value['reviewer_image'] : '',
                                        $testimonial?->value && $testimonial?->value['reviewer_image'] ? asset('storage/app/public/business/landing-pages/testimonial/'.$testimonial?->value['reviewer_image']) : '',
                                        asset('public/landing-page/assets/img/client/user.png'),
                                        'business/landing-pages/testimonial/',
                                    ) }}" alt="client">

                                    </div>
                                    <div class="testimonial__item-cont">
                                        <p class="mb-2 name text-dark"><strong>{{ $testimonial?->value && $testimonial?->value['reviewer_name'] ? $testimonial?->value['reviewer_name']: "" }}</strong></p>
                                        <p class="text--base mb-0">{{ $testimonial?->value && $testimonial?->value['designation'] ? $testimonial?->value['designation']: "" }}</p>
                                        <div class="rating mb-2">
                                            @php($count = $testimonial?->value && $testimonial?->value['rating'] ? $testimonial?->value['rating'] : 0)

                                            @for($inc=1;$inc<=5;$inc++)
                                                @if ($inc <= (int)$count)
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                @elseif ($count != 0 && $inc <= (int)$count + 1.1 && $count > ((int)$count))
                                                    <i class="bi bi-star-half text-warning"></i>
                                                @else
                                                    <i class="bi bi-star text-warning"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <p>
                                            <blockquote>
                                                {{ $testimonial?->value && $testimonial?->value['review'] ? $testimonial?->value['review'] : "" }}
                                            </blockquote>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="testimonial__item">
                            <div class="testimonial__item-img">
                                <img src="{{asset('public/landing-page/assets/img/client/user.png')}}" alt="client">
                            </div>
                            <div class="testimonial__item-cont">
                                <p class="mb-2 name text-dark"><strong>{{ "Roofus K." }}</strong><p>
                                <p class="text--base mb-0">{{ "Customer" }}</p>
                                <div class="rating mb-2">
                                    @php($count = 5)

                                    @for($inc=1;$inc<=5;$inc++)
                                        @if ($inc <= (int)$count)
                                            <i class="bi bi-star-fill text-warning"></i>
                                        @elseif ($count != 0 && $inc <= (int)$count + 1.1 && $count > ((int)$count))
                                            <i class="bi bi-star-half text-warning"></i>
                                        @else
                                            <i class="bi bi-star text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                                <p>
                                    <blockquote>
                                        {{ "Exceeded my expectations! Customer support is responsive and helpful. Fantastic experience!" }}
                                    </blockquote>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Testimonial Slider Bottom Counter and Nav Icons -->
                <div class="slider-bottom d-flex justify-content-center">
                    <div class="owl-btn testimonial-owl-prev">
                        <i class="las la-long-arrow-alt-left"></i>
                    </div>
                    <div class="slider-counter mx-3"></div>
                    <div class="owl-btn testimonial-owl-next">
                        <i class="las la-long-arrow-alt-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>
