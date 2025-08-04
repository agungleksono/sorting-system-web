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
    <div class="shadow p-3 bg-body rounded">
        <h5 class="mb-2 py-1">Import Suspect Part</h5>
        <a href="{{ route('cases.create') }}">Add new case.</a>
        <div class="my-3 col-md-6">
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
    <div class="row d-flex justify-content-center mt-4">
        <div class="col-md-3">
            <div class="shadow p-3 mb-3 bg-info rounded">
                <!-- <h4 class="text-center mt-2">Part Di Scan</h4> -->
                <h4 class="text-center mt-2">Suspects Found</h4>
                <h1 class="my-3 text-center fw-bolder">{{ $scanProgress->current_progress ? $scanProgress->current_progress : '0' }}</h1>
            </div>
        </div>
        <div class="col-md-3">
            <div class="shadow p-3 mb-3 bg-warning rounded">
                <!-- <h4 class="text-center mt-2">Part Belum Di Scan</h4> -->
                <h4 class="text-center mt-2">Suspects Remain</h4>
                <h1 class="my-3 text-center fw-bolder">{{ $scanProgress->current_progress ? $scanProgress->max_progress - $scanProgress->current_progress : $scanProgress->max_progress }}</h1>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" id="deleteSuspect" onClick="deleteModalHandler('Suspect')" disabled>
            <span data-feather="trash-2" class="align-text-bottom me-1"></span>
            Delete Suspect Item
    </button>
    <button type="submit" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" id="deleteQR" onClick="deleteModalHandler('QR')" disabled>
            <span data-feather="refresh-ccw" class="align-text-bottom me-1"></span>
            Reset Scanned QR
    </button>
    <form id="suspectForm" method="POST" action="{{ route('suspects.delete') }}">
        @csrf
        @if(request()->has('caseId'))
        <input type="text" class="invisible" name="caseId" value="{{ request()->input('caseId') }}">
        @endif
        <input type="text" class="invisible" name="type" id="deleteType">
        <table id="suspectTable" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th class="text-center"><input type="checkbox" id="selectAll" onClick="deleteButtonsToggle()"></th>
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
                        <td class="text-center">
                            <input type="checkbox" name="selected[]" value="{{ $suspect['suspect_id'] }}" onClick="deleteButtonsToggle()">
                        </td>
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
    </form>
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
                        <label for="boxId" class="form-label">Box Id</label>
                        <input type="text" class="form-control" name="box_id" id="boxId">
                    </div>
                    <div class="mb-3">
                        <label for="containerNo" class="form-label">Container No.</label>
                        <input type="text" class="form-control" name="container_no" id="containerNo">
                    </div>
                    <div class="mb-3">
                        <label for="invoiceNo" class="form-label">Invoice No.</label>
                        <input type="text" class="form-control" name="invoice_no" id="invoiceNo">
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="text" class="form-control" name="quantity" id="quantity">
                    </div>
                    <div class="mb-3">
                        <label for="caseModal" class="form-label">Case</label>
                        <select class="form-select" id="caseModal" name="suspect_case_id" required>
                            <option value="" selected disabled>--- Pilih Case ---</option>
                            @foreach ($cases as $case)
                                <option value="{{ $case->suspect_case_id }}">{{ $case->title }}</option>
                            @endforeach
                        </select>
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

<!-- Deletion Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label id="deleteModalPrompt"></label>
                <div class="mt-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="suspectForm" id="deleteBtnModal" class="btn btn-danger btn-sm">Delete</button>
                </div>
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
            columnDefs: [
                { orderable: false, targets: 0 } // Disable sorting on the checkbox column
            ]
        });

        // Prevent sorting when clicking the checkbox in the header
        document.getElementById('selectAll').addEventListener('click', function (event) {
            event.stopPropagation(); // Prevent sort trigger
        });

        // Handle Select All checkbox toggle
        document.getElementById('selectAll').addEventListener('change', function () {
            let checkboxes = document.querySelectorAll('input[name="selected[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
            document.getElementById("deleteSuspect").disabled = !this.checked;
            document.getElementById("deleteQR").disabled = !this.checked;
        });

        // Change the Delete Modal's text and the form's type whether the user is deleting QR or Suspect.
        function deleteModalHandler(type) {
            if (type == "QR") {
                document.getElementById("deleteModalLabel").innerHTML = "Reset Scanned QR";
                document.getElementById("deleteModalPrompt").innerHTML = "Are you sure you want to reset scanned QR?";
                document.getElementById("deleteType").value = "QR";
                document.getElementById("deleteBtnModal").textContent = "Reset";
            } else {
                document.getElementById("deleteModalLabel").innerHTML = "Delete Suspect Item";
                document.getElementById("deleteModalPrompt").innerHTML = "Are you sure you want to delete suspect item?";
                document.getElementById("deleteType").value = "Suspect";
                document.getElementById("deleteBtnModal").textContent = "Delete";
            }
        }

        // The delete buttons should be disabled when no checkboxes are checked.
        function deleteButtonsToggle() {
            let checkboxes = document.getElementsByName("selected[]");
            for (let i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    document.getElementById("deleteSuspect").disabled = false;
                    document.getElementById("deleteQR").disabled = false;
                    return;
                }
            }

            document.getElementById("deleteSuspect").disabled = true;
            document.getElementById("deleteQR").disabled = true;
        }
    </script>
@endpush