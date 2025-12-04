<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Task::with('project', 'assignee', 'creator');

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned to current user
        if ($request->has('my_tasks') && $request->my_tasks) {
            $query->where('assigned_to', auth()->id());
        }

        // Filter overdue tasks
        if ($request->has('overdue') && $request->overdue) {
            $query->overdue();
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'due_date');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = Task::STATUS_TODO;

        $task = Task::create($validated);

        // Create activity log
        $this->createActivityLog('task_created', $task);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task->load('project', 'assignee', 'creator'),
        ], 201);
    }

    public function show(Task $task)
    {
        return response()->json([
            'success' => true,
            'data' => $task->load('project', 'assignee', 'creator', 'comments'),
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:todo,in_progress,in_review,done,blocked',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);

        // Create activity log
        $this->createActivityLog('task_updated', $task);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task->load('project', 'assignee', 'creator'),
        ]);
    }

    public function destroy(Task $task)
    {
        $this->createActivityLog('task_deleted', $task);
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    public function addComment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        $this->createActivityLog('task_comment_added', $task);

        return response()->json([
            'success' => true,
            'message' => 'Comment added',
            'data' => $comment->load('user'),
        ], 201);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,in_review,done,blocked',
        ]);

        $oldStatus = $task->status;
        $task->update($validated);

        $this->createActivityLog("task_status_changed_from_{$oldStatus}_to_{$validated['status']}", $task);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated',
            'data' => $task,
        ]);
    }

    public function assign(Request $request, Task $task)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task->update($validated);

        $this->createActivityLog('task_assigned', $task);

        return response()->json([
            'success' => true,
            'message' => 'Task assigned',
            'data' => $task->load('assignee'),
        ]);
    }

    private function createActivityLog($action, $task)
    {
        // Activity log creation
        // Can be implemented with Activity Log package
    }
}
