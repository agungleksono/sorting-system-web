@extends('layouts.dashboard')

@section('title', 'Sorting System')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Upload Suspect Data</h1>
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
    <!-- <h4>Import Suspect File</h4>
    <form action="" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".xlsx,.xls,.csv">
        <button type="submit">Import</button>
    </form> -->

    <div class="mb-3 col-md-4">
        <label for="formFile" class="form-label fw-semibold">Import Suspect Part</label>
        <form action="{{ url('suspect/import') }}" method="POST" class="row g-3" enctype="multipart/form-data">
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
</div>

<div class="mt-5">
    <h4 class="mb-3">Suspect Part Data</h4>
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