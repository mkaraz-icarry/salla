
@include('head')


    <div class="d-flex flex-column h-100">
        @include('navbar', ['merchant_store_id' => $merchant_store_id])
        @if (!isset($data['data']))
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
        @else
            <div class="container mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">event</th>
                            <th scope="col">target_url</th>
                            <th scope="col">store_id</th>
                            <th scope="col">original_id</th>
                            <th scope="col">subscriber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['data'] as $webhook)

                            <tr>
                                <th scope="row">{{ $webhook['id'] }}</th>
                                <td>{{ $webhook['event'] }}</td>
                                <td>{{ $webhook['target_url'] }}</td>
                                <td>{{ $webhook['store_id'] }}</td>
                                <td>{{ $webhook['original_id'] }}</td>
                                <td>{{ $webhook['subscriber'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>


    @include('footer')
