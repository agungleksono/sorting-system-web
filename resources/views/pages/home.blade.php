@extends('layouts.dashboard')

@section('title', 'Sorting System')

@section('content')

<div class="container-fluid mt-5">
    <!-- <h4>Import Suspect File</h4>
    <form action="" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".xlsx,.xls,.csv">
        <button type="submit">Import</button>
    </form> -->

    <div class="mb-3 col-md-4">
        <label for="formFile" class="form-label">Import Suspect Part</label>
        <form action="" method="POST" class="row g-3" enctype="multipart/form-data">
            <div class="col-auto">
                <input class="form-control" type="file" id="formFile" accept=".xlsx,.xls,.csv">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-3">Import</button>
            </div>
        </form>
    </div>
</div>

@endsection