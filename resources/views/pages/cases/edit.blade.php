@extends('layouts.dashboard')

@section('title', 'Sorting System - Edit Cases')

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
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid mt-4">
    <div class="col-md-6">
        <form action="{{ route('cases.update', ['suspect_case_id' => $suspectCase->suspect_case_id]) }}" method="post">
            @csrf
            @method('PATCH')
            <div class="mb-3">
                <label for="title" class="form-label">Case Title</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $suspectCase->title }}" required>
            </div>
            <div class="mb-3">
                <label for="scanType" class="form-label">Scan Type</label>
                <select class="form-select" id="scanType" name="scanType" required>
                    <option value="" selected disabled>--- Pilih Scan Type ---</option>
                    @foreach ($scanTypes as $type)
                    <option value="{{ $type->scan_type_id }}" {{ $suspectCase->scan_type_id == $type->scan_type_id ? 'selected' : '' }}>{{ $type->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="scanParameter" class="form-label">Scan Parameter</label>
                <select class="form-select" id="scanParameter" name="scanParameter" required>
                    <option value="" selected disabled>--- Pilih Scan Parameter ---</option>
                    @foreach ($scanParameters as $parameter)
                    <option value="{{ $parameter->code }}" {{ $suspectCase->scan_parameter_code == $parameter->code ? 'selected' : '' }}>{{ $parameter->scan_parameter }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="qrContent" class="form-label">Scan QR (Opsional)</label>
                <textarea class="form-control" id="qrContent" name="qrContent" rows="3"></textarea>
                <div id="qrContentHelp" class="form-text">Scan sample QR untuk menghitung otomatis panjang karakter.</div>
            </div>
            <div class="mb-3">
                <label for="qrLength" class="form-label">Panjang Karakter QR</label>
                <input type="text" class="form-control" id="qrLength" name="qrLength" value="{{ $suspectCase->qr_length }}">
            </div>
            <div class="mb-3 row ps-3">
                <div class="form-check col-auto me-4">
                    <label class="form-check-label" for="caseStatusOpen">
                        <input class="form-check-input" type="radio" value="0" name="caseStatus" id="caseStatusOpen" {{ $suspectCase->is_closed == '0' ? 'checked' : '' }}>
                        Open
                    </label>
                </div>
                <div class="form-check col-auto">
                    <label class="form-check-label" for="caseStatusClose">
                        <input class="form-check-input" type="radio" value="1" name="caseStatus" id="caseStatusClose" {{ $suspectCase->is_closed == '1' ? 'checked' : '' }}>
                        Closed
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Save</button>
        </form>
    </div>
</div>

@endsection

@push('addon-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qrContent = document.getElementById('qrContent');

            qrContent.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    qrLength.value = qrContent.value.length;
                }
            })
        });
    </script>
@endpush