<div>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 800px;">
            <div class="card-body p-3 p-md-4">
                <div class="row">
                    {{-- Left Column: Customer and Product Data --}}
                    <div class="col-12 mb-3 mb-lg-0 px-4">
                        <h5 class="card-title fs-5 fw-bold fs-md-4 text-center pb-3">Order Detail</h5>
                        <!-- Customer Data -->
                        <div class="row order-id align-items-center mb-2 px-3 me-0 px-lg-1">
                            <div class="col-2 px-1 width-20">
                                <p class="fw-bold mb-1">Order #</p>
                            </div>
                            <div class="col-1 px-1 width-5">
                                <p class="mb-1">:</p>
                            </div>
                            <div class="col-9 px-1 width-75">
                                <p class="text-end mb-1">#{{ $order_id ?? 'John Doe' }}</p>
                            </div>
                        </div>
                        <div class="row name align-items-center mb-2 px-3 me-0 px-lg-1">
                            <div class="col-2 px-1 width-20">
                                <p class="fw-bold mb-1">Name</p>
                            </div>
                            <div class="col-1 px-1 width-5">
                                <p class="mb-1">:</p>
                            </div>
                            <div class="col-9 px-1 width-75">
                                <p class="text-end mb-1">{{ $data['customer_name'] ?? 'John Doe' }}</p>
                            </div>
                        </div>
                        <div class="row email align-items-center mb-2 px-3 me-0 px-lg-1">
                            <div class="col-2 px-1 width-20">
                                <p class="fw-bold mb-1">Email</p>
                            </div>
                            <div class="col-1 px-1 width-5">
                                <p class="mb-1">:</p>
                            </div>
                            <div class="col-9 px-1 width-75">
                                <p class="text-end mb-1">{{ $data['customer_email'] ?? 'johndoe@gmail.com' }}</p>
                            </div>
                        </div>
                        <div class="row phone align-items-center mb-2 px-3 me-0 px-lg-1">
                            <div class="col-2 px-1 width-20">
                                <p class="fw-bold mb-1">Phone</p>
                            </div>
                            <div class="col-1 px-1 width-5">
                                <p class="mb-1">:</p>
                            </div>
                            <div class="col-9 px-1 width-75">
                                <p class="text-end mb-1">{{ $data['customer_phone'] ?? '+6281234567890' }}</p>
                            </div>
                        </div>
                        <hr class="my-5">
                        <div class="row mx-0 class-description align-items-center p-3 p-lg-2 border border-1 rounded-lg">
                            <div class="col-2 px-1 width-15">
                                <p class="mb-1">Total</p>
                            </div>
                            <div class="col-1 px-1 width-5">
                                <p class="mb-1">:</p>
                            </div>
                            <div class="col-9 px-1 width-80">
                                <p class="fw-bold fs-5 text-end mb-1">Rp{{ number_format($data['total'], 0, ',', '.') ?? '0' }}</p>
                            </div>
                        </div>
                        <!-- Full-size Next Button -->
                        <button class="btn btn-primary w-100 mt-4" wire:click="proceedPayment">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
