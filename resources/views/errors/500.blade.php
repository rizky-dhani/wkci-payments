@extends('errors.layout')

@section('content')
    <div class="card shadow-lg text-center p-4">
        <h1 class="text-danger display-4">500</h1>
        <h2 class="text-dark">Internal Server Error</h2>
        <p class="text-muted mt-3">
            There's a little bit problem on our side. Please click the Refresh button below to refresh this page.
        </p>
        <button onclick="location.reload();" class="btn btn-primary mt-3">
            Refresh
        </button>
    </div>
@endsection