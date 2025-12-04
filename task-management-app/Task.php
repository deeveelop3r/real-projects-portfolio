<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'priority',
        'status',
        'start_date',
        'due_date',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const STATUS_TODO = 'todo';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_DONE = 'done';
    const STATUS_BLOCKED = 'blocked';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'desc');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', today())
                     ->where('status', '!=', self::STATUS_DONE);
    }

    public function isOverdue()
    {
        return $this->due_date && $this->due_date < today() && $this->status !== self::STATUS_DONE;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_DONE;
    }

    public function canBeEdited()
    {
        return $this->status !== self::STATUS_DONE;
    }
}
