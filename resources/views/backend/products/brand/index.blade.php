@extends('backend.layout.master')


@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                        @if (auth()->guard('admin')->user()->can('brand_add'))
                            <button data-href="{{ route('admin.brand.store') }}" data-name="Brand" class="btn btn-primary create"><i class="fas fa-plus-circle"></i> {{ __('Create Brand') }}</button>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table">
                                    <thead>
                                        <tr>
                                            <th>{{__('#')}}</th>
                                            <th>{{ __('Brand Name') }}</th>
                                            <th>{{ __('Created At')}}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($brands as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="text-capitalize">{{ $item->name }}</td>
                                            <td>{{ $item->created_at->format('d M, Y H:i A') }}</td>
                                            <td>
                                            @if (auth()->guard('admin')->user()->can('brand_edit'))
                                                <button class="btn btn-primary btn-sm edit mr-1"
                                                    data-href="{{ route('admin.brand.update', $item->id) }}"   
                                                    data-item="{{$item->name}}"      
                                                    data-name="Brand"                                          
                                                    data-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                @endif
                                                @if (auth()->guard('admin')->user()->can('brand_delete'))
                                                <button class="btn btn-danger delete btn-sm"
                                                    data-href="{{ route('admin.brand.destroy', $item->id) }}"
                                                    data-toggle="tooltip" title="Delete" type="button">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                            
                                        @endforelse
                                       
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>
    
@endsection
