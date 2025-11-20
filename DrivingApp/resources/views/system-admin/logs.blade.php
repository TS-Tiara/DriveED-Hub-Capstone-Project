<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - System Administrator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }

        .stat-card.critical {
            border-left: 4px solid #ef4444;
        }

        .stat-card.unresolved {
            border-left: 4px solid #f59e0b;
        }

        .stat-card.total {
            border-left: 4px solid #3b82f6;
        }

        .stat-card.today {
            border-left: 4px solid #10b981;
        }

        .filters {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .filters h3 {
            margin-bottom: 15px;
            color: #374151;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-group select,
        .form-group input {
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .logs-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-emergency,
        .badge-critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-alert,
        .badge-error {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info,
        .badge-notice {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-debug {
            background: #e5e7eb;
            color: #374151;
        }

        .badge-resolved {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-unresolved {
            background: #fee2e2;
            color: #991b1b;
        }

        .log-message {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 20px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: #374151;
            background: white;
            border: 1px solid #e5e7eb;
        }

        .pagination a:hover {
            background: #f9fafb;
            border-color: #667eea;
        }

        .pagination .active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .no-logs {
            padding: 40px;
            text-align: center;
            color: #6b7280;
        }

        .view-btn {
            padding: 6px 12px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .view-btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>🔧 System Logs</h1>
                <p>Monitor and manage all system errors and logs across all schools</p>
            </div>
            <div style="text-align: right;">
                <p style="margin-bottom: 10px; opacity: 0.9;">
                    👤 {{ Auth::guard('admin')->user()->name }}
                </p>
                <form method="POST" action="{{ route('system-admin.logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card critical">
                <h3>{{ $stats['critical'] }}</h3>
                <p>Critical Errors</p>
            </div>
            <div class="stat-card unresolved">
                <h3>{{ $stats['unresolved'] }}</h3>
                <p>Unresolved Issues</p>
            </div>
            <div class="stat-card today">
                <h3>{{ $stats['today'] }}</h3>
                <p>Today's Logs</p>
            </div>
            <div class="stat-card total">
                <h3>{{ $stats['this_week'] }}</h3>
                <p>This Week</p>
            </div>
            <div class="stat-card total">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Logs</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <h3>🔍 Filter Logs</h3>
            <form method="GET">
                <div class="filter-grid">
                    <div class="form-group">
                        <label>Level</label>
                        <select name="level">
                            <option value="">All Levels</option>
                            <option value="emergency" {{ request('level') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                            <option value="alert" {{ request('level') === 'alert' ? 'selected' : '' }}>Alert</option>
                            <option value="critical" {{ request('level') === 'critical' ? 'selected' : '' }}>Critical</option>
                            <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>Error</option>
                            <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="notice" {{ request('level') === 'notice' ? 'selected' : '' }}>Notice</option>
                            <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>Info</option>
                            <option value="debug" {{ request('level') === 'debug' ? 'selected' : '' }}>Debug</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="database" {{ request('category') === 'database' ? 'selected' : '' }}>Database</option>
                            <option value="validation" {{ request('category') === 'validation' ? 'selected' : '' }}>Validation</option>
                            <option value="authentication" {{ request('category') === 'authentication' ? 'selected' : '' }}>Authentication</option>
                            <option value="authorization" {{ request('category') === 'authorization' ? 'selected' : '' }}>Authorization</option>
                            <option value="file_upload" {{ request('category') === 'file_upload' ? 'selected' : '' }}>File Upload</option>
                            <option value="email" {{ request('category') === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="payment" {{ request('category') === 'payment' ? 'selected' : '' }}>Payment</option>
                            <option value="api" {{ request('category') === 'api' ? 'selected' : '' }}>API</option>
                            <option value="system" {{ request('category') === 'system' ? 'selected' : '' }}>System</option>
                            <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>School</label>
                        <select name="school_id">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="resolved">
                            <option value="">All</option>
                            <option value="no" {{ request('resolved') === 'no' ? 'selected' : '' }}>Unresolved</option>
                            <option value="yes" {{ request('resolved') === 'yes' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}">
                    </div>

                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ url()->current() }}" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="logs-table">
            @if($logs->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Time</th>
                            <th>Level</th>
                            <th>Category</th>
                            <th>School</th>
                            <th>User</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>#{{ $log->id }}</td>
                                <td>{{ $log->created_at->format('M d, H:i') }}</td>
                                <td><span class="badge badge-{{ $log->level }}">{{ $log->level }}</span></td>
                                <td>{{ ucfirst($log->category) }}</td>
                                <td>{{ $log->school->name ?? 'System' }}</td>
                                <td>{{ $log->user->name ?? 'N/A' }} <small>({{ $log->user_type }})</small></td>
                                <td class="log-message" title="{{ $log->message }}">{{ $log->message }}</td>
                                <td>
                                    <span class="badge badge-{{ $log->resolved_at ? 'resolved' : 'unresolved' }}">
                                        {{ $log->resolved_at ? 'Resolved' : 'Unresolved' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('system-admin.logs.show', $log) }}" class="view-btn">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="no-logs">
                    <p>No logs found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
