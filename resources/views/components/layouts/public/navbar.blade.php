{{-- NAVBAR SEVEN PART START --}}

<section class="navbar-area navbar-wkci">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg fixed-top py-3 py-lg-0">
                    <a class="navbar-brand width-60 width-lg-15" href="javascript:void(0)">
                        <img src="{{ asset('assets/img/logo/logo_color.webp') }}" alt="Logo WKCI"/>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarseven"
                        aria-controls="navbarseven" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="toggler-icon"></span>
                        <span class="toggler-icon"></span>
                        <span class="toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse sub-menu-bar" id="navbarseven">
                        <ul class="navbar-nav mx-auto">
                            <li class="nav-item">
                                <a class="page-scroll active" data-bs-toggle="collapse" data-bs-target="#sub-nav13"
                                    aria-controls="sub-nav13" aria-expanded="false" aria-label="Toggle navigation"
                                    href="javascript:void(0)">Home
                                    <div class="sub-nav-toggler">
                                        <span><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                </a>
                                <ul class="sub-menu collapse" id="sub-nav13">
                                    <li><a href="javascript:void(0)">Creative Home</a></li>
                                    <li>
                                        <a class="page-scroll active" data-bs-toggle="collapse"
                                            data-bs-target="#sub-nav14" aria-controls="sub-nav14" aria-expanded="false"
                                            aria-label="Toggle navigation" href="javascript:void(0)">Corporate Home
                                            <div class="sub-nav-toggler">
                                                <span><i class="fas fa-chevron-right"></i></span>
                                            </div>
                                        </a>
                                        <ul class="sub-menu collapse" id="sub-nav14">
                                            <li><a href="javascript:void(0)">Home One</a></li>
                                            <li><a href="javascript:void(0)">Home Two</a></li>
                                            <li><a href="javascript:void(0)">Home Three</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="javascript:void(0)">Business Home</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a href="javascript:void(0)">About Us</a>
                            </li>
                            <li class="nav-item">
                                <a href="javascript:void(0)">Services</a>
                            </li>
                            <li class="nav-item">
                                <a href="javascript:void(0)">Team</a>
                            </li>
                            <li class="nav-item">
                                <a href="javascript:void(0)">Contact</a>
                            </li>
                        </ul>
                    </div>

                    <div class="navbar-btn d-none d-sm-inline-block">
                        <ul>
                            <li>
                                <a class="btn primary-btn" href="javascript:void(0)">Sign In</a>
                            </li>
                            <li>
                                <a class="btn primary-btn primary-color" href="javascript:void(0)">Sign Up</a>
                            </li>
                        </ul>
                    </div>
                </nav>
                {{-- navbar --}}
            </div>
        </div>
        {{-- row --}}
    </div>
    {{-- container --}}
</section>

{{-- NAVBAR SEVEN PART ENDS --}}
    <script>
        // close navbar-collapse when a  clicked
        let navbarTogglerSeven = document.querySelector(
            ".navbar-seven .navbar-toggler"
        );
        navbarTogglerSeven.addEventListener("click", function () {
            navbarTogglerSeven.classList.toggle("active");
        });

        // navbar seven sideMenu
        let sideMenuRightSeven = document.querySelector(
            ".navbar-seven .menu-bar"
        );

        sideMenuRightSeven.addEventListener("click", function () {
            sidebarRight.classList.add("open");
            overlayRight.classList.add("open");
        });
        // right sidebar toggle
        let sidebarRight = document.querySelector(".sidebar-right");
        let overlayRight = document.querySelector(".overlay-right");
        let sidebarRightClose = document.querySelector(".sidebar-right .close");

        overlayRight.addEventListener("click", function () {
            sidebarRight.classList.toggle("open");
            overlayRight.classList.toggle("open");
        });
        sidebarRightClose.addEventListener("click", function () {
            sidebarRight.classList.remove("open");
            overlayRight.classList.remove("open");
        });
    </script>