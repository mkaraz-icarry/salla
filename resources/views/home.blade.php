
@include('head')


    <div class="d-flex flex-column h-100">
        @include('navbar', ['merchant_store_id' => $merchant_store_id])

        <div class="container my-auto">
            <h1 class="main__head text-center mb-3">Welcome to <span>iCARRY</span> App</h1>
            <p>
                We are born of the belief that delivery is much more than just moving a package from point A to
                point B. iCARRY’s seamless delivery solution, powered by innovative technology, connects merchants -
                large and small - E-commerce and Social Sellers with multiple carriers on a single platform.

                Our cutting-edge, best-in-class tools and technology provide an exceptional last-mile delivery
                experience while also promoting business scalability for our partners.
            </p>
        </div>

    </div>

    <script src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('js/jquery.min.js') : secure_asset('js/jquery.min.js') }}"></script>
    <script src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('js/bootstrap.bundle.min.js') : secure_asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('js/main.js') : secure_asset('js/main.js') }}"></script>



@include('footer')

