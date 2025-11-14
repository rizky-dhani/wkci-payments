@extends('app')

@section('title', 'QR Payment')

@section('content')
    <div class="row min-vh-100">
        <div class="col d-flex justify-content-center align-items-center">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-center d-flex justify-content-center">{!! $qrWeb !!}</div>
                    <hr style="border border-3">
                    <h2 class="fw-bold text-center">QRIS YUKK</h2>
                    <br class="pb-3">
                    <h5 class="fw-bold mb-3">Please screenshot QR code above <br> and use your banking or e-wallet apps to pay.</h5>
                    <h6 class="mb-3">QR above is valid for <p class="fw-bolder text-danger">5 minutes</p> Click button below to check the status of your order</h6>
                    <a href="{{ route('query_payment') }}" class="btn btn-success btn-block mb-3">Click here to proceed</a>
                    <br>
                    <a href="https://k-popindonesia.id/class" class="btn btn-primary btn-block mb-3">Back to class selection</a>
                </div>
            </div>
        </div>
        {{-- {{ json_encode($result) }} --}}
    </div>
@endsection
