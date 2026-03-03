@include('head')


<div class="d-flex flex-column h-100">
    @include('navbar', ['merchant_store_id' => $merchant_store_id])
    <div class="container-fluid mt-3">
        @csrf
        <div class="d-flex align-items-center mb-2">
            <h1>Orders</h1>
            <div class="ml-auto">
                <a class="btn btn-secondary" href="/orders">Refresh</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Order_status</th>
                        <th scope="col">Customer name</th>
                        <th scope="col">Customer email</th>
                        <th scope="col">Order totals</th>
                        <th scope="col">Shipping method</th>
                        <th scope="col">Payment method</th>
                        <th scope="col">Payment status</th>
                        <th scope="col">Created at</th>
                        <th scope="col">Updated at</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['orders'] as $order)
                        <tr>
                            <th scope="row">{{ $order['id'] }}</th>
                            <td>{{ $order['order_status']['code'] }}</td>
                            <td>{{ $order['customer']['name'] }}</td>
                            <td>{{ $order['customer']['email'] }}</td>
                            <td>{{ $order['order_total_string'] }}</td>
                            <td>{{ $order['payment_status'] }}</td>
                            <td>{{ $order['shipping']['method']['name'] }}</td>
                            <td>{{ $order['payment']['method']['name'] }}</td>
                            <td>{{ $order['created_at'] }}</td>
                            <td>{{ $order['updated_at'] }}</td>
                            <td>
                                <div class="actions-box">
                                    <?php
                                        $trackingNumber = $order['shipping']['method']['tracking']['number'];
                                        $trackingURL = $order['shipping']['method']['tracking']['url'];
                                    ?>
                                    <button class="btn icarry_btn send-to-icarry-btn {{ isset($trackingNumber) ? 'd-none' : '' }}" type="button" data-order-id="{{ $order['id'] }}">
                                        Send to iCARRY
                                        <svg class="ml-2 d-none check-connectivity-loading" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"/></svg>
                                    </button>
                                    <a class="order__tracking-number {{ isset($trackingNumber) ? '' : 'd-none' }}" href="{{ $trackingURL }}" target="_blank">{{ $trackingNumber }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <?php

            $totalCount = $data['total_order_count'];
            // $totalCount = 1000;
            $totalPages = ceil($totalCount / $countPerPage);
        ?>
        <div class="d-flex">
            <nav class="mx-auto" aria-label="Page navigation example">
                <ul class="pagination">
                    @if ($currentPage > 1)
                        <li class="page-item prev-btn">
                            <a class="page-link" href="/orders?page=1" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    @endif


                    @for ($page = 1; $page <= $totalPages; $page++)
                        @if ($page == $currentPage)
                            <li class="page-item {{ $page == $currentPage ? 'active' : ''  }}"><a class="page-link" href="/orders?page={{$page}}">{{$page}}</a></li>
                        @elseif($page == $currentPage - 1)
                            <li class="page-item"><a class="page-link" href="/orders?page={{$page}}">{{$page}}</a></li>
                        @elseif($page == $currentPage + 1)
                            <li class="page-item"><a class="page-link" href="/orders?page={{$page}}">{{$page}}</a></li>
                            @break
                        @endif
                    @endfor


                    @if ($currentPage < $totalPages)
                        <li class="page-item next-btn">
                            <a class="page-link" href="/orders?page={{$totalPages}}" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
</div>
<script src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('js/jquery.min.js') : secure_asset('js/jquery.min.js') }}"></script>
<script src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('js/bootstrap.bundle.min.js') : secure_asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('js/main.js') : secure_asset('js/main.js') }}"></script>

<script>
    $(document).ready(function() {
        $(".send-to-icarry-btn").on("click", function() {
            const _this = this;
            $(_this).addClass("disabled").find(".check-connectivity-loading").removeClass("d-none");

            let csrfToken = $('input[name="_token"]').attr('value');
            let orderId = $(this).data("order-id");
            $.ajax({
                url: '/send-order-to-icarry',
                type: 'POST',
                dataType: 'json',
                data: {
                    orderId: orderId
                },
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(response) {
                    // Handle the API response here
                    // For example, display the data on the page
                    // $('#result').html(JSON.stringify(response, null, 2));
                    // console.log(response);

                    if (!response.order) {
                        console.log(response);
                        return;
                    }

                    const trackingNumber = response.order.shipping.method.tracking.number;
                    const trackingURL = response.order.shipping.method.tracking.url;
                    // console.log("Tracking number", trackingNumber);
                    if (trackingNumber) {
                        $(_this).addClass("d-none").parents(".actions-box").find(".order__tracking-number").removeClass("d-none")
                            .prop("href", trackingURL)
                            .html(trackingNumber);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error cases
                    console.error(error);
                },
                complete: function() {
                    $(_this).removeClass("disabled").find(".check-connectivity-loading").addClass("d-none");
                }
            });
        });
    });
</script>



@include('footer')
