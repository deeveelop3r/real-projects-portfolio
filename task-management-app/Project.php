<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'color',
        'owner_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    const STATUS_PLANNING = 'planning';
    const STATUS_ACTIVE = 'active';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED = 'archived';

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('due_date', 'asc');
    }

    public function members()
    {
        return $this->hasManyThrough(User::class, Task::class, 'project_id', 'id', 'id', 'assigned_to');
    }

    public function getProgressPercentage()
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()
            ->where('status', Task::STATUS_DONE)
            ->count();

        return round(($completedTasks / $totalTasks) * 100);
    }

    public function getCompletedTasksCount()
    {
        return $this->tasks()
            ->where('status', Task::STATUS_DONE)
            ->count();
    }

    public function getOverdueTasksCount()
    {
        return $this->tasks()
            ->whereDate('due_date', '<', today())
            ->where('status', '!=', Task::STATUS_DONE)
            ->count();
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }
}
