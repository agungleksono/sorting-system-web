@extends('layouts.dashboard')

@section('title', 'Sorting System')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Upload Suspect Data</h1>
</div>

<div class="alert alert-warning alert-dismissible fade show mt-4" role="alert">
    <strong>Holy guacamole!</strong> You should check in on some of those fields below.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

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
                <input class="form-control" name="file" type="file" id="formFile" accept=".xlsx,.xls,.csv">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-3">Import</button>
            </div>
        </form>
    </div>
</div>

@endsection