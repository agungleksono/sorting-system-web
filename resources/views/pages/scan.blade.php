@extends('layouts.dashboard')

@section('title', 'Sorting System')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Scanning Data</h1>
</div>

{{-- Success Alert --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Error Alert --}}
@if(session('errors'))
    <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
        {{ session('errors') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif



<div class="container-fluid mt-4">
    <!-- <h4>Import Suspect File</h4>
    <form action="" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".xlsx,.xls,.csv">
        <button type="submit">Import</button>
    </form> -->

    <form method="GET" action="{{ url('scans') }}" class="row gy-2 gx-3 align-items-center">
        <!-- <div class="col-auto">
            <label for="partNo" class="form-label">Part No.</label>
            <input type="text" class="form-control" name="part_no" id="partNo">
        </div>
        <div class="col-auto">
            <label for="lotNo" class="form-label">Lot No.</label>
            <input type="text" class="form-control" name="lot_no" id="lotNo">
        </div>
        <div class="col-auto">
            <label for="containerNo" class="form-label">Container No.</label>
            <input type="text" class="form-control" name="container_no" id="containerNo">
        </div>
        <div class="col-auto">
            <label for="invoiceNo" class="form-label">Invoice No.</label>
            <input type="text" class="form-control" name="invoice_no" id="invoiceNo">
        </div> -->
        <div class="col-auto">
            <label for="startDate" class="form-label">Start Date</label>
            <input type="date" class="form-control" name="start_date" id="startDate" required>
        </div>
        <div class="col-auto">
            <label for="endDate" class="form-label">End Date</label>
            <input type="date" class="form-control" name="end_date" id="endDate" required>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary mt-4" name="submit" value="1">Search</button>
        </div>
    </form>
</div>

<div class="mt-5">
    <table id="suspectTable" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Part No.</th>
                <th class="text-center">Lot No</th>
                <th class="text-center">Container No</th>
                <th class="text-center">Invoice No</th>
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
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
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