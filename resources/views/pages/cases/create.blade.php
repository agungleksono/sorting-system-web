@extends('layouts.dashboard')

@section('title', 'Sorting System - New Cases')

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
        <form action="{{ route('cases.store') }}" method="post">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label">Case Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
                <!-- <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div> -->
            </div>
            <div class="mb-3">
                <label for="scanType" class="form-label">Scan Type</label>
                <select class="form-select" id="scanType" name="scanType" required>
                    <option value="" selected disabled>--- Pilih Scan Type ---</option>
                    @foreach ($scanTypes as $type)
                    <option value="{{ $type->scan_type_id }}">{{ $type->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="scanParameter" class="form-label">Scan Parameter</label>
                <select class="form-select" id="scanParameter" name="scanParameter" required>
                    <option value="" selected disabled>--- Pilih Scan Parameter ---</option>
                    @foreach ($scanParameters as $parameter)
                    <option value="{{ $parameter->code }}">{{ $parameter->scan_parameter }}</option>
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
                <input type="text" class="form-control" id="qrLength" name="qrLength">
            </div>
            <!-- <div class="mb-3">
                <label for="stringStartIndex" class="form-label">Digit Awal Kata Kunci</label>
                <input type="text" class="form-control" id="stringStartIndex" name="stringStartIndex">
            </div>
            <div class="mb-3">
                <label for="stringLength" class="form-label">Panjang Kata Kunci</label>
                <input type="text" class="form-control" id="stringLength" name="stringLength">
            </div> -->
            <button type="submit" class="btn btn-primary">Save</button>
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