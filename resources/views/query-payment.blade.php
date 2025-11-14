@extends('app')

@section('title', 'Payment Status')

@section('content')
    <div class="row min-vh-100">
        <div class="col d-flex justify-content-center align-items-center">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="fw-bold text-center">Payment Status</h2>
                    <hr style="border border-3 pb-3">
                    <div class="status-box mb-3 mx-3">
                        <div class="text-center d-flex justify-content-center mb-3">{!! $qr !!}</div>
                        <h5 class="text-center">Reference No.</h5>
                        <h5 class="fw-bold text-center">{{ $queryResult['originalPartnerReferenceNo'] }}</h5>
                        <br>
                        <h5 class="text-center">Status</h5>
                        <h5 class="fw-bold text-center">{{ $queryResult['transactionStatusDesc'] }}</h5>
                    </div>
                    <br>
                    <h3 class="fw-bold mb-5">Rp{{ number_format($queryResult['amount']['value'], 2, ',', '.') }}</h3>
                    <a href="#" class="btn btn-success btn-block mb-3" onclick="window.location.reload()">Check Payment Status</a>
                    <br>
                    <a href="https://k-popindonesia.id/class" class="btn btn-primary btn-block mb-3">Back to class selection</a>
                </div>
            </div>
        </div>
    </div>
@endsection
