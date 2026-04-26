@extends('backend.layout.master')


@section('content')
    <div class="main-content">
        <section class="section">
            <div class="row">
                <div class="col-md-12 mt-5">
                    <div class="card py-5">
                        <div class="card-body">
                            <div class="mt-5">
                                <h1 class="p-5 my-5 text-dark text-center text-uppercase">
                                    {{ __('WELCOME BACK' .' ' .auth()->guard('admin')->user()->name) }}</h1>
                                <h6 class="p-3 my-3 text-dark text-center">
                                    @php
                                        $date = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
                                        echo $date->format('D, M d, Y H:i:s A');
                                    @endphp
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
