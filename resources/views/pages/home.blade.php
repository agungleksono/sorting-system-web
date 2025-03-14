@extends('layouts.dashboard')

@section('title', 'Sorting System')

@push('addon-style')
    <style>
        /* .btn-new-case {
            background-color: #37297e;
            color: white;
        } */
        .btn-import {
            /* background-color: #6a55f2; */
            background-color: #37297e;
            color: white;
        }
        .btn-add-manual {
            background-color: #22a78c;
            color: white;
        }
    </style>
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Suspect</h1>
    <!-- <a class="btn btn-new-case me-3 fw-semibold" href="{{ route('cases.create') }}" role="button">
        <span data-feather="plus" class="align-text-bottom"></span> 
        New Case
    </a> -->
</div>

{{-- Success Alert --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Error Alert --}}
@if (session('errors'))
    <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
        {{ session('errors') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif



<div class="container-fluid mt-4">
    <!-- <div class="shadow-sm p-3 mb-5 bg-body rounded">
        <a class="btn btn-info" href="#" role="button">
            <span data-feather="plus" class="align-text-bottom"></span> 
            New Case
        </a>
    </div> -->

    <div class="shadow p-3 bg-body rounded">
        <h5 class="mb-2 py-1">Import Suspect Part</h5>
        <a href="{{ route('cases.create') }}">Add new case.</a>
        <div class="my-3 col-md-5">
            <!-- <label for="formFile" class="form-label fw-semibold">Import Suspect Part</label> -->
            <form action="{{ route('suspects.import') }}" method="POST" class="row g-3" enctype="multipart/form-data">
                @csrf
                <div class="col-auto">
                    <select class="form-select" id="case" name="case" required>
                        <option value="" selected disabled>--- Pilih Case ---</option>
                        @foreach ($cases as $case)
                        <option value="{{ $case->suspect_case_id }}">{{ $case->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <input class="form-control" name="file" type="file" id="formFile" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-import fw-semibold">Import</button>
                </div>
            </form>
            <div class="form-text"><i>*) Pastikan format excel yang di upload sesuai dengan format <a href="{{ route('suspects.download-file') }}">berikut</a>.</i></div>
        </div>
        <!-- <div>
            <a class="btn btn-info" href="#" role="button">
                <span data-feather="plus" class="align-text-bottom"></span> 
                New Case
            </a>
        </div> -->
        
        <div class="">
            <button type="submit" class="btn btn-add-manual btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addSuspectModal">
                <span data-feather="plus" class="align-text-bottom"></span> 
                Add Manual
            </button>
        </div>
    </div>
</div>

<div class="mt-5">
    <h4 class="mb-3">Suspect Part Table</h4>
    <div class="col-md-4">
        <form action="{{ route('suspects.index') }}" method="get" class="row">
            @csrf
            <div class="col-auto">
                <select class="form-select" id="case" name="caseId" required>
                    <option value="" selected disabled>--- Pilih Case ---</option>
                    @foreach ($cases as $case)
                        @if(request()->has('caseId'))
                        <option value="{{ $case->suspect_case_id }}" {{ request('caseId') == $case->suspect_case_id ? 'selected' : '' }}>{{ $case->title }}</option>
                        @else
                        <option value="{{ $case->suspect_case_id }}">{{ $case->title }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-import fw-semibold"><span data-feather="search" class="align-text-bottom me-1"></span> Search</button>
            </div>
        </form>
    </div>
    <div class="row d-flex justify-content-center">
        <div class="col-md-3">
            <div class="shadow p-3 mb-3 bg-info rounded">
                <h4 class="text-center mt-2">Part Di Scan</h4>
                <h1 class="my-3 text-center fw-bolder">{{ request()->has('caseId') ? $scanProgress->current_progress : '0' }}</h1>
            </div>
        </div>
        <div class="col-md-3">
            <div class="shadow p-3 mb-3 bg-warning rounded">
                <h4 class="text-center mt-2">Part Belum Di Scan</h4>
                <h1 class="my-3 text-center fw-bolder">{{ request()->has('caseId') ? $scanProgress->max_progress - $scanProgress->current_progress : '0' }}</h1>
            </div>
        </div>
    </div>

    <table id="suspectTable" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Part No.</th>
                <th class="text-center">Lot No</th>
                <th class="text-center">Box Id</th>
                <th class="text-center">Invoice No</th>
                <th class="text-center">Container No</th>
                <th class="text-center">Quantity</th>
                <th class="text-center">Judgment</th>
                <th class="text-center">Scan Time</th>
                <th class="text-center">Scan By</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($suspects))
                @foreach($suspects as $suspect)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $suspect['part_no'] }}</td>
                    <td class="text-center">{{ $suspect['lot_no'] }}</td>
                    <td class="text-center">{{ $suspect['box_id'] }}</td>
                    <td class="text-center">{{ $suspect['invoice_no'] }}</td>
                    <td class="text-center">{{ $suspect['container_no'] }}</td>
                    <td class="text-center">{{ $suspect['quantity'] }}</td>
                    <td class="text-center">{!! $suspect['is_scanned'] ? '<span class="badge rounded-pill text-bg-danger">NG</span>' : '<span class="badge rounded-pill text-bg-secondary">Not Scanned</span>' !!}</td>
                    <td>{{ $suspect['scanned_at'] }}</td>
                    <td>{{ $suspect['scanned_by'] }}</td>
                </tr>
                @endforeach 
            @endif
        </tbody>
    </table>
</div>

<!-- Add Manual Suspect Modal -->
<div class="modal fade" id="addSuspectModal" tabindex="-1" aria-labelledby="addSuspectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addSuspectModalLabel">Add Suspect Part</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('suspects.manual-add') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="partNo" class="form-label">Part No.</label>
                        <input type="text" class="form-control" name="part_no" id="partNo">
                    </div>
                    <div class="mb-3">
                        <label for="lotNo" class="form-label">Lot No.</label>
                        <input type="text" class="form-control" name="lot_no" id="lotNo">
                    </div>
                    <div class="mb-3">
                        <label for="containerNo" class="form-label">Container No.</label>
                        <input type="text" class="form-control" name="container_no" id="containerNo">
                    </div>
                    <div class="mb-3">
                        <label for="invoiceNo" class="form-label">Invoice No.</label>
                        <input type="text" class="form-control" name="invoice_no" id="invoiceNo">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('addon-script')
    <script>
        new DataTable('#suspectTable', {
            layout: {
                topStart: {
                    buttons: ['excel']
                }
            },
            pageLength: 20,
        });
    </script>
@endpush