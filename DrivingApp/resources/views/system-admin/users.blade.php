@extends('layouts.system-admin')
@section('title', 'Users')
@section('page-title', 'All Users')
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
            <label>User Type</label>
            <select name="user_type" class="form-control">
                <option value="">All Types</option>
                <option value="student" {{ request('user_type') == 'student' ? 'selected' : '' }}>Students</option>
                <option value="instructor" {{ request('user_type') == 'instructor' ? 'selected' : '' }}>Instructors</option>
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

<!-- Students Section -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3>
            <i class="fas fa-user-graduate" style="margin-right: 0.5rem;"></i>
            Students ({{ isset($students) ? $students->count() : 0 }})
        </h3>
    </div>
    <div class="card-body">
        @if(isset($students) && $students->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                <tr>
                    <td><strong>{{ $student->name }}</strong></td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->school->name }}</td>
                    <td>
                        <span class="badge {{ $student->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                    <td>{{ $student->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #6c757d; padding: 2rem;">No students found.</p>
        @endif
    </div>
</div>

<!-- Instructors Section -->
<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-chalkboard-teacher" style="margin-right: 0.5rem;"></i>
            Instructors ({{ isset($instructors) ? $instructors->count() : 0 }})
        </h3>
    </div>
    <div class="card-body">
        @if(isset($instructors) && $instructors->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($instructors as $instructor)
                <tr>
                    <td><strong>{{ $instructor->name }}</strong></td>
                    <td>{{ $instructor->email }}</td>
                    <td>{{ $instructor->school->name }}</td>
                    <td>
                        <span class="badge {{ $instructor->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                            {{ ucfirst($instructor->status) }}
                        </span>
                    </td>
                    <td>{{ $instructor->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #6c757d; padding: 2rem;">No instructors found.</p>
        @endif
    </div>
</div>
@endsection
