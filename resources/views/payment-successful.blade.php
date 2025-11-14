@extends('app')

@section('title', 'YUKK Payment Status')

@section('content')
    <div class="row min-vh-100">
        <div class="col d-flex justify-content-center align-items-center">
            <div class="card">
                <div class="card-body text-center">
                    <div class="status-box mb-3 mx-3">
                        <h2 class="fw-bold text-center mb-5">Payment Successful</h2>
                        <img src="{{ asset('assets/img/icons/success-icon.png') }}" alt="" class="mb-5 w-25">
                        <br>
                        <a href="{{ env('WOOCOMMERCE_STORE_URL') }}/my-bookings" class="btn btn-success">{{ __('Return to Home')}}</a>
                    </div>
                </div>
            </div>
        </div>
        {{-- {{ json_encode($queryResult) }} --}}
    </div>
@endsection
