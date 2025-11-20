@extends('layouts.system-admin')
@section('title', 'Schools')
@section('page-title', 'All Schools')
@section('content')
<div class="card">
    <div class="card-header"><h3>All Schools ({{ $schools->total() }})</h3></div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>School Name</th>
                    <th>Slug</th>
                    <th>Email</th>
                    <th>Students</th>
                    <th>Instructors</th>
                    <th>Admins</th>
                    <th>Courses</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schools as $school)
                <tr>
                    <td><strong>{{ $school->name }}</strong></td>
                    <td>{{ $school->slug }}</td>
                    <td>{{ $school->email ?? 'N/A' }}</td>
                    <td>{{ $school->students_count }}</td>
                    <td>{{ $school->instructors_count }}</td>
                    <td>{{ $school->admins_count }}</td>
                    <td>{{ $school->courses_count }}</td>
                    <td>{{ $school->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $schools->links() }}
    </div>
</div>
@endsection
