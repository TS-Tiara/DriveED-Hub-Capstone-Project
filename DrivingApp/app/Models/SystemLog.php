<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
        'user_type',
        'level',
        'category',
        'action',
        'message',
        'exception_class',
        'stack_trace',
        'context',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'notified_admin',
        'notified_system_admin',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'context' => 'array',
        'notified_admin' => 'boolean',
        'notified_system_admin' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scopes
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function scopeCritical($query)
    {
        return $query->whereIn('level', ['emergency', 'alert', 'critical', 'error']);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Static helper methods for logging
     */
    public static function logError(
        string $message,
        string $category = 'other',
        ?\Throwable $exception = null,
        array $context = [],
        ?int $schoolId = null,
        string $action = null
    ): self {
        return self::createLog('error', $message, $category, $exception, $context, $schoolId, $action);
    }

    public static function logCritical(
        string $message,
        string $category = 'other',
        ?\Throwable $exception = null,
        array $context = [],
        ?int $schoolId = null,
        string $action = null
    ): self {
        return self::createLog('critical', $message, $category, $exception, $context, $schoolId, $action);
    }

    public static function logWarning(
        string $message,
        string $category = 'other',
        array $context = [],
        ?int $schoolId = null,
        string $action = null
    ): self {
        return self::createLog('warning', $message, $category, null, $context, $schoolId, $action);
    }

    public static function logInfo(
        string $message,
        string $category = 'other',
        array $context = [],
        ?int $schoolId = null,
        string $action = null
    ): self {
        return self::createLog('info', $message, $category, null, $context, $schoolId, $action);
    }

    private static function createLog(
        string $level,
        string $message,
        string $category,
        ?\Throwable $exception,
        array $context,
        ?int $schoolId,
        ?string $action
    ): self {
        $guardName = self::getCurrentGuardName();
        $user = Auth::guard($guardName)->user();

        $log = self::create([
            'school_id' => $schoolId ?? ($user->school_id ?? null),
            'user_id' => $user->id ?? null,
            'user_type' => $guardName ?? 'system',
            'level' => $level,
            'category' => $category,
            'action' => $action,
            'message' => $message,
            'exception_class' => $exception ? get_class($exception) : null,
            'stack_trace' => $exception ? $exception->getTraceAsString() : null,
            'context' => $context,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
        ]);

        // Notify if critical
        if (in_array($level, ['emergency', 'alert', 'critical', 'error'])) {
            $log->notifyAdmins();
        }

        return $log;
    }

    private static function getCurrentGuardName(): ?string
    {
        foreach (['admin', 'instructor', 'student'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }

    /**
     * Mark as resolved
     */
    public function resolve(string $notes = null): bool
    {
        $guardName = self::getCurrentGuardName();
        $user = Auth::guard($guardName)->user();

        return $this->update([
            'resolved_at' => now(),
            'resolved_by' => $user->id ?? null,
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Notify admins about this error
     */
    public function notifyAdmins(): void
    {
        // TODO: Implement email/notification system
        // For now, just mark as notified
        $this->update([
            'notified_admin' => true,
            'notified_system_admin' => true,
        ]);
    }

    /**
     * Get formatted error details
     */
    public function getFormattedDetails(): string
    {
        $details = [];
        $details[] = "Level: " . strtoupper($this->level);
        $details[] = "Category: " . ucfirst($this->category);
        
        if ($this->action) {
            $details[] = "Action: " . $this->action;
        }
        
        if ($this->school) {
            $details[] = "School: " . $this->school->name;
        }
        
        if ($this->user) {
            $details[] = "User: " . $this->user->name . " ({$this->user_type})";
        }
        
        $details[] = "Message: " . $this->message;
        
        if ($this->exception_class) {
            $details[] = "Exception: " . $this->exception_class;
        }
        
        $details[] = "Time: " . $this->created_at->format('Y-m-d H:i:s');
        $details[] = "URL: " . $this->url;
        
        return implode("\n", $details);
    }
}
