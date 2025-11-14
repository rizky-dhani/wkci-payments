{{-- Footer --}}
<footer class="pt-5">
    <section class="d-flex justify-content-center mb-5">
        {{-- Footer Column --}}
        <div class="row width-100 width-lg-75">
            <div class="col-lg-4 mb-5 mb-lg-0">
                <div class="logo mb-5 text-center">
                    <a href="{{ route('home') }}"><img src="{{ asset('assets/img/logo/logo_color.webp') }}" class="width-75 width-lg-75"
                        alt="Logo World KPop Center Indonesia"></a>
                </div>
                <div class="follow-button mb-3">
                    <h3 class="fw-bolder text-center">
                        {{ __('Follow us') }}
                    </h3>
                </div>
                <div class="follow-icons d-flex justify-content-center">
                    <div class="row text-center width-lg-70">
                        <div class="col-4">
                            <a href="https://www.instagram.com/wkc.indonesia/" target="__blank"><i
                                    class="fab fa-instagram fa-2xl text-black"></i></a>
                        </div>
                        <div class="col-4">
                            <a href="https://www.tiktok.com/@wkc.indonesia" target="__blank"><i
                                    class="fab fa-tiktok fa-2xl text-black"></i></a>
                        </div>
                        <div class="col-4">
                            <a href="https://www.youtube.com/@worldkpopcenter" target="__blank"><i
                                    class="fab fa-youtube fa-2xl text-black"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-5 mb-lg-0">
                <div class="row footer-heading-text mb-3">
                    <h5 class="fw-bolder text-center">{{ __('Resources') }}</h5>
                </div>
                <div class="row navigation-text">
                    <div class="col d-flex flex-column justify-content-center align-items-center ">
                        @include('components.layouts.public.footer-navigation')
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-5 mb-lg-0">
                <div class="maps-wrapper text-center">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.105884989862!2d106.78704497455529!3d-6.116446193870147!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6a1d95bf7bceeb%3A0xdacb917bfb866141!2sPluit%20Village%20Mall!5e0!3m2!1sen!2sid!4v1739675421920!5m2!1sen!2sid" width="375" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </section>
    {{-- Footer Copyright --}}
    <div class="row align-middle me-lg-0 mb-4">
        <div class="h5 text-center">{{ config('app.name') . " © " . date('Y') }}</div>
    </div>
</footer>