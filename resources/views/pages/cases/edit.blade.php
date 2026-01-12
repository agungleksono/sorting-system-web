@extends('layouts.dashboard')

@section('title', 'Sorting System - Edit Cases')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Edit Case</h1>
</div>

{{-- Alert --}}
<div id="alert-container"></div>

<div class="container-fluid mt-4">
    <div class="col-md-6">
        <form id="caseForm">
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
            <!-- <div class="mb-3">
                <label for="qrContent" class="form-label">Scan QR (Opsional)</label>
                <textarea class="form-control" id="qrContent" name="qrContent" rows="3"></textarea>
                <div id="qrContentHelp" class="form-text">Scan sample QR untuk menghitung otomatis panjang karakter.</div>
            </div>
            <div class="mb-3">
                <label for="qrLength" class="form-label">Panjang Karakter QR</label>
                <input type="text" class="form-control" id="qrLength" name="qrLength" value="{{ $suspectCase->qr_length }}">
            </div> -->
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
            <button type="submit" id="submitBtn" class="btn btn-primary mt-2">Save</button>
        </form>
    </div>
</div>

@endsection

@push('addon-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const API_BEARER_TOKEN = "{{ $bearerToken }}";
            const suspectCaseId = "{{ $suspectCase->suspect_case_id }}";
            const userId = "{{ session('npk') }}";
            const form = document.getElementById('caseForm');
            // const qrContent = document.getElementById('qrContent');
            // const qrLength = document.getElementById('qrLength');

            // qrContent.addEventListener('keydown', (e) => {
            //     if (e.key === 'Enter') {
            //         qrLength.value = qrContent.value.length;
            //     }
            // });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...`;

                // Collect form data
                const data = {
                    title: document.getElementById('title').value,
                    scan_type: document.getElementById('scanType').value,
                    scan_parameter: document.getElementById('scanParameter').value,
                    // qr_length: qrLength.value,
                    is_closed: document.querySelector('input[name="caseStatus"]:checked')?.value || 0,
                    user_id: userId,
                };

                try {
                    const response = await apiFetch(`/api/v1/cases/${suspectCaseId}`, 'PATCH', data, API_BEARER_TOKEN);
                    console.log(response)

                    document.getElementById('title').value = response.data.title;
                    document.getElementById('scanType').value = response.data.scan_type_id;
                    document.getElementById('scanParameter').value = response.data.scan_parameter_code;
                    // document.getElementById('qrLength').value = response.data.qr_length;

                    if (response.data.is_closed == 1) {
                        document.getElementById('caseStatusClose').checked = true;
                    } else {
                        document.getElementById('caseStatusOpen').checked = true;
                    }
                    showAlert('Case updated successfully!', 'success');
                } catch (error) {
                    showAlert('Failed to update case.', 'danger');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });
    </script>
@endpush