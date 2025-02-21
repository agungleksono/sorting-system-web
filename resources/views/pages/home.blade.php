@extends('layouts.dashboard')

@section('title', 'Sorting System')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Suspect</h1>
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
    <div class="mb-3 col-md-4">
        <label for="formFile" class="form-label fw-semibold">Import Suspect Part</label>
        <form action="{{ route('suspects.import') }}" method="POST" class="row g-3" enctype="multipart/form-data">
            @csrf
            <div class="col-auto">
                <input class="form-control" name="file" type="file" id="formFile" accept=".xlsx,.xls,.csv" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
        <div class="form-text">Pastikan format excel yang di upload sesuai dengan format berikut.</div>
    </div>
    <div class="">
        <button type="submit" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSuspectModal">
            <span data-feather="plus" class="align-text-bottom"></span> 
            Add Manual
        </button>
    </div>
</div>

<div class="mt-5">
    <h4 class="mb-3">Suspect Part Table</h4>
    <div class="row d-flex justify-content-center">
        <div class="col-md-3">
            <div class="shadow p-3 mb-3 bg-info rounded">
                <h4 class="text-center mt-2">Part Di Scan</h4>
                <h1 class="my-3 text-center fw-bolder">0</h1>
            </div>
        </div>
        <div class="col-md-3">
            <div class="shadow p-3 mb-3 bg-warning rounded">
                <h4 class="text-center mt-2">Part Belum Di Scan</h4>
                <h1 class="my-3 text-center fw-bolder">0</h1>
            </div>
        </div>
    </div>

    <table id="suspectTable" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Part No.</th>
                <th class="text-center">Lot No</th>
                <th class="text-center">Container No</th>
                <th class="text-center">Invoice No</th>
                <th class="text-center">Judgment</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($suspects))
                @foreach($suspects as $suspect)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $suspect['part_no'] }}</td>
                    <td class="text-center">{{ $suspect['lot_no'] }}</td>
                    <td class="text-center">{{ $suspect['invoice_no'] }}</td>
                    <td class="text-center">{{ $suspect['container_no'] }}</td>
                    <td class="text-center">{!! $suspect['is_scanned'] ? '<span class="badge rounded-pill text-bg-danger">NG</span>' : '<span class="badge rounded-pill text-bg-secondary">Not Scanned</span>' !!}</td>
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