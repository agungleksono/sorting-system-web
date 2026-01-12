@extends('layouts.dashboard')

@section('title', 'Sorting System - New Cases')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">New Case</h1>
</div>

{{-- Alert --}}
<div id="alert-container"></div>

<div class="container-fluid mt-4">
    <div class="col-md-6">
        <form id="caseForm">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label">Case Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="scanType" class="form-label">Scan Type</label>
                <select class="form-select" id="scanType" name="scanType" required>
                    <option value="" selected disabled>--- Choose Scan Type ---</option>
                    @foreach ($scanTypes as $type)
                    <option value="{{ $type->scan_type_id }}">{{ $type->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="scanParameter" class="form-label">Traced By</label>
                <select class="form-select" id="scanParameter" name="scanParameter" required>
                    <option value="" selected disabled>--- Choose Trace By ---</option>
                    @foreach ($scanParameters as $parameter)
                    <option value="{{ $parameter->code }}">{{ $parameter->scan_parameter }}</option>
                    @endforeach
                </select>
            </div>
            <!-- <div class="mb-3">
                <label for="qrContent" class="form-label">Scan QR</label>
                <textarea class="form-control" id="qrContent" name="qrContent" rows="3"></textarea>
                <div id="qrContentHelp" class="form-text">Scan sample QR untuk menghitung otomatis panjang karakter.</div>
            </div>
            <div class="mb-3">
                <label for="qrLength" class="form-label">Panjang Karakter QR</label>
                <input type="text" class="form-control" id="qrLength" name="qrLength">
            </div> -->
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

@endsection

@push('addon-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const API_BEARER_TOKEN = "{{ $bearerToken }}";
            const userId = "{{ session('npk') }}";
            const form = document.getElementById('caseForm');
            // const qrContent = document.getElementById('qrContent');
            // const qrLength = document.getElementById('qrLength');

            // qrContent.addEventListener('keydown', (e) => {
            //     if (e.key === 'Enter' || e.key === 'Tab') {
            //         qrLength.value = qrContent.value.length;
            //     }
            // })

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
                    // qrContent: qrContent.value,
                    // qr_length: parseInt(qrLength.value),
                    user_id: userId,
                };

                try {
                    await apiFetch('/api/v1/cases', 'POST', data, API_BEARER_TOKEN);
                    showAlert('New case created successfully.', 'success');
                    form.reset();
                } catch (error) {
                    showAlert('Failed to create a new case.', 'danger');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
        });
    </script>
@endpush