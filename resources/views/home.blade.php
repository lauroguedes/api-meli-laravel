@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <h4>Integrations</h4>
                    @if(auth()->user()->integrations)
                        @foreach (auth()->user()->integrations as $integration)
                            <div><strong>Token</strong>: {{ $integration->token }}</div>
                            <div><strong>Refresh Token</strong>: {{ $integration->refresh_token }}</div>
                            <div><strong>Integration User ID</strong>: {{ $integration->integration_user_id }}</div>
                            <div><strong>Integration</strong>: {{ $integration->integration }}</div>
                            <div><strong>App ID</strong>: {{ $integration->app_id }}</div>
                            <hr>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
