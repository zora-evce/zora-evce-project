@extends('templates.template')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-3">Thank you for charging with us!</h3>
                    <p>Your payment has been received successfully. You can now proceed with your charging session.</p>
                    <p>If you need to manually force stop the charging, use the button below:</p>
                    <a href="{{ route('stop') }}" class="btn btn-danger">Force Stop Charging</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


