@extends('layout')
@section('title')
    <title>Wallet</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style=" background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }}) "></div>
        <div class="container">
            <div class="col-lg-12">
                <div class="inner-banner-df">
                    <h1 class="inner-banner-taitel">Wallet</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard">
        <div class="container">
            <div class="row">
                @include('profile.sidebar')
                <div class="col-lg-9">
                    <div class="dashboard-item mb-4">
                        <div class="dashboard-inner">
                            <div class="dashboard-inner-text">
                                <h5>Current Balance</h5>
                                <h3>{{ currency(optional($wallet)->balance ?? 0) }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="manage-car two">
                        <h5 class="mb-3">Wallet Transactions</h5>
                        <div class="car_list_table">
                            <table class="table two">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Balance After</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $trx)
                                        <tr>
                                            <td>{{ $trx->created_at?->format('d M Y H:i') }}</td>
                                            <td>{{ strtoupper($trx->type) }}</td>
                                            <td>{{ currency($trx->amount) }}</td>
                                            <td>{{ currency($trx->balance_after) }}</td>
                                            <td>{{ $trx->description ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No transactions yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($transactions, 'links'))
                            <div class="mt-3">{{ $transactions->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('profile.logout')
</main>
@endsection

