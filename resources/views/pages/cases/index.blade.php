@extends('layouts.dashboard')

@section('title', 'Sorting System - Edit Cases')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3">Case</h1>
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
    <a class="btn btn-info" href="{{ route('cases.create') }}" role="button">
        <span data-feather="plus" class="align-text-bottom"></span> 
        New Case
    </a>

    <table id="caseTable" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Case</th>
                <th class="text-center">Progress</th>
                <th class="text-center">Status</th>
                <th class="text-center">Issued By</th>
                <th class="text-center">Register Date</th>
                <th class="text-center"></th>
            </tr>
        </thead>
        <tbody>
            @if (isset($cases))
                @foreach($cases as $case)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $case->title }}</td>
                    <td class="text-center"><span class="badge rounded-pill text-bg-primary">{{ $case->current_progress . ' / ' . $case->max_progress }}</span></td>
                    <td class="text-center">{{ $case->is_closed ? 'Closed' : 'Open' }}</td>
                    <td class="text-center">{{ $case->created_by }}</td>
                    <td class="text-center">{{ date('d-m-Y', strtotime($case->created_at)) }}</td>
                    <td>
                        <div class="row">
                            <a href="{{ route('cases.edit', ['suspect_case_id' => $case->suspect_case_id]) }}" class="me col-auto">
                                <span data-feather="edit" class="align-text-bottom text-success"></span>
                            </a>
                            <form method="post" action="{{ route('cases.destroy', ['suspect_case_id' => $case->suspect_case_id]) }}" class="col-auto" onsubmit="return confirmDelete()">
                                @csrf
                                @method('DELETE')
                                <!-- <a href=""><span data-feather="trash-2" class="align-text-bottom text-danger"></span></a> -->
                                <button type="submit" style="background: none; border: none; padding: 0;">
                                    <span data-feather="trash-2" class="align-text-bottom text-danger"></span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach 
            @endif
        </tbody>
    </table>
</div>

@endsection

@push('addon-script')
    <script>
        new DataTable('#caseTable', {
            layout: {
                topStart: {
                    buttons: ['excel']
                }
            },
            pageLength: 20,
        });

        function confirmDelete() {
            // Show a confirmation alert before submitting the form
            return confirm('Are you sure you want to delete this case?');
        }
    </script>
@endpush