@extends('layouts.system-admin')
@section('title', 'Admins')
@section('page-title', 'All Admins')
@section('content')
<div class="filters">
    <form method="GET" class="filter-grid">
        <div class="form-group">
            <label>School</label>
            <select name="school_id" class="form-control">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="">All Roles</option>
                <option value="system_admin" {{ request('role') == 'system_admin' ? 'selected' : '' }}>System Admin</option>
                <option value="school_admin" {{ request('role') == 'school_admin' ? 'selected' : '' }}>School Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email" class="form-control">
        </div>
        <div class="form-group" style="display: flex; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Filter</button>
        </div>
    </form>
</div>
<div class="card">
    <div class="card-header"><h3>Admins ({{ $admins->total() }})</h3></div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>School</th>
                    <th>Role</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                <tr>
                    <td><strong>{{ $admin->name }}</strong></td>
                    <td>{{ $admin->email }}</td>
                    <td>{{ $admin->school ? $admin->school->name : 'System' }}</td>
                    <td>
                        <span class="badge {{ $admin->role === 'system_admin' ? 'badge-danger' : 'badge-primary' }}">
                            {{ $admin->role === 'system_admin' ? 'System Admin' : 'School Admin' }}
                        </span>
                    </td>
                    <td>{{ $admin->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $admins->appends(request()->query())->links() }}
    </div>
</div>
@endsection
