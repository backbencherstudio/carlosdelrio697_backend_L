@extends('layouts.master')
@section('title', 'Softvence :: Role Edit')
@section('page-title', 'Edit Role')

@section('content')

<div class="py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item"><a href="#">Softvence</a></li>
            <li class="breadcrumb-item"><a href="{{ route('role.index') }}">Roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow components-section">
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('role.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Role Name --}}
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5 d-flex align-items-center">
                            <label class="form-label me-2 mb-0" style="min-width: 90px;">Role Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                        </div>

                        <div class="col-md-3 d-flex align-items-center">
                            {{-- Global Check All --}}
                            <div class="form-check ms-3 mb-0">
                                <input type="checkbox" class="form-check-input" id="checkAllGlobal">
                                <label for="checkAllGlobal" class="form-check-label mb-0">Give all Permissions</label>
                            </div>
                        </div>
                    </div>

                    {{-- Loop through groups --}}
                    @foreach($permissions->groupBy('group_name') as $groupName => $groupPermissions)
                        <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                            <h5>{{ $groupName }}</h5>
                            {{-- Group Check All --}}
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input checkAllGroup" data-group="{{ Str::slug($groupName) }}">
                                <label class="form-check-label">Select All</label>
                            </div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>#</th>
                                        <th>Permission Name</th>
                                        <th>Create</th>
                                        <th>Read</th>
                                        <th>Update</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sl = 1; @endphp
                                    @foreach($groupPermissions->groupBy('permission_name') as $permissionName => $perms)
                                        <tr class="text-center">
                                            <td>{{ $sl++ }}</td>
                                            <td class="text-start">{{ $permissionName }}</td>
                                            @foreach (['create','view','edit','destroy'] as $type)
                                                <td>
                                                    @php
                                                        $perm = $perms->where('permission_type', $type)->first();
                                                        $checked = $perm && $role->permissions->contains($perm->id) ? 'checked' : '';
                                                    @endphp

                                                    @if($perm)
                                                        <input type="checkbox"
                                                            name="permissions[{{ $perm->id }}][]"
                                                            value="{{ $type }}"
                                                            class="perm-checkbox perm-{{ $type }}-{{ Str::slug($groupName) }}"
                                                            {{ $checked }}>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                    <div class="text-end mt-3">
                        <a href="{{ route('role.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-success">Update Role</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    // Global check all
    document.getElementById('checkAllGlobal').addEventListener('change', function() {
        const checked = this.checked;
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = checked);
        document.querySelectorAll('.checkAllGroup').forEach(g => g.checked = checked);
    });

    // Group check all
    document.querySelectorAll('.checkAllGroup').forEach(groupCheck => {
        groupCheck.addEventListener('change', function() {
            const groupSlug = this.dataset.group;
            const checked = this.checked;
            document.querySelectorAll('.perm-checkbox.perm-create-' + groupSlug +
                                   ', .perm-checkbox.perm-view-' + groupSlug +
                                   ', .perm-checkbox.perm-edit-' + groupSlug +
                                   ', .perm-checkbox.perm-destroy-' + groupSlug).forEach(cb => cb.checked = checked);
        });
    });
</script>
@endsection
