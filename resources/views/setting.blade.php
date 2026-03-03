
@include('head')


    <div class="d-flex flex-column h-100">
        @include('navbar', ['merchant_store_id' => $merchant_store_id])

        <input type="text" hidden name="merchant_store_url" id="merchant_store_url" value="{{ $user_info['merchant_store_url'] }}">
        <div class="container mt-3">
            <form action="/save-setting" method="POST" class="a-auto">
                @csrf
                <div class="form-group mb-3">
                    <label for="icarry_store_url">Store URL</label>
                    <select class="form-control" id="icarry_store_url" name="icarry_store_url">
                        <option value="https://ksa.icarry.com/" {{ $user_info['iCARRYStoreURL'] == 'https://ksa.icarry.com/' ? "selected": "" }}>Saudi Arabia</option>
                        <option value="https://lb.icarry.com/" {{ $user_info['iCARRYStoreURL'] == 'https://lb.icarry.com/' ? "selected": "" }}>Lebanon</option>
                        <option value="https://uae.icarry.com/" {{ $user_info['iCARRYStoreURL'] == 'https://uae.icarry.com/' ? "selected": "" }}>United Arab Emirates</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="icarry_email">Email</label>
                    <input class="form-control" type="email" name="icarry_email" id="icarry_email" value="{{ $user_info['iCARRYEmail'] }}">
                </div>
                <div class="form-group mb-3">
                    <label for="icarry_password">Password</label>
                    <input class="form-control" type="password" name="icarry_password" id="icarry_password" value="{{ $user_info['iCARRYPassword'] }}">
                    <button class="btn btn-success mt-3" id="check-connectivity-btn" type="button">
                        Check Connectivity
                        <svg class="ml-2 d-none" id="check-connectivity-loading" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"/></svg>
                    </button>
                    <!--Message-->
                    <div class="mt-3">
                        <div class="alert alert-danger d-none" id="check_connectivity_error">
                            Connection error. Please check your credentials.
                        </div>
                        <div class="alert alert-success d-none" id="check_connectivity_success">
                            Connection successful
                        </div>
                    </div>
                </div>
                {{-- <div class="d-flex align-items-center mb-3">
                    <input class="mr-2" style="width: 20px;height: 20px;" type="checkbox"
                        name="icarry_enable_rates" id="icarry_enable_rates">
                    <label class="mb-0" for="icarry_password">Enable Rates</label>
                </div> --}}
                <div class="d-flex">
                    <button class="btn icarry_btn px-3 ml-auto" id="save-btn">Save</button>
                </div>
                @if(isset($success) && $success)
                    <div class="save-message alert alert-success mt-3">
                        Successfully Saved
                    </div>
                @elseif (isset($success) && !$success)
                    <div class="save-message alert alert-danger mt-3">
                        {{ $message }}
                    </div>
                @endif
            </form>
        </div>
    </div>
    <script src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('js/jquery.min.js') : secure_asset('js/jquery.min.js') }}"></script>
    <script src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('js/bootstrap.bundle.min.js') : secure_asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ env('APP_URL') === 'http://localhost:8000' ? asset('js/main.js') : secure_asset('js/main.js') }}"></script>

    <script>
        let saveMessage = $(".save-message");

        if (saveMessage.length > 0) {
            setTimeout(() => {
                saveMessage.addClass("d-none");
            }, 5000);
        }

        $("#save-btn").on("click", function() {
            $(this).addClass("disabled");
        });
        $("#check-connectivity-btn").on("click", function() {
            let checkConnectivityLoading = $("#check-connectivity-loading");
            let settings = {
                storeUrl: $("#icarry_store_url").val(),
                email: $("#icarry_email").val(),
                password: $("#icarry_password").val()
            }

            checkConnectivityLoading.removeClass("d-none");
            $.ajax({
                url: `${settings.storeUrl}api-frontend/Authenticate/GetTokenForCustomerApi`,
                //dataType: "json",
                type: "POST",
                headers: {
                    "accept": "application/json",
                    "Content-Type": "application/json-patch+json"
                },
                // async: true,
                data: JSON.stringify({
                    "email": settings.email,
                    "password": settings.password
                }),
                success: function(data) {
                    // console.log(data);
                    if (!data.site_url)
                    {
                        $("#check_connectivity_success").addClass("d-none");
                        $("#check_connectivity_error").removeClass("d-none");
                        return;
                    }
                    const currentStoreUrl = new URL($("#merchant_store_url").val());
                    const url = new URL(data.site_url);
                    const domain = url.hostname;

                    // console.log(url.hostname, currentStoreUrl.hostname);

                    if (url.hostname == currentStoreUrl.hostname) {
                        $("#check_connectivity_error").addClass("d-none");
                        $("#check_connectivity_success").removeClass("d-none");
                    } else {
                        $("#check_connectivity_success").addClass("d-none");
                        $("#check_connectivity_error").removeClass("d-none");
                    }

                },
                error: function(xhr, exception, thrownError) {
                    $("#check_connectivity_success").addClass("d-none");
                    $("#check_connectivity_error").removeClass("d-none");
                },
                complete: function() {
                    checkConnectivityLoading.addClass("d-none");
                }
            });
        });
    </script>

@include('footer')

