<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // Get all tasks (admin sees all, user sees only their tasks)
    public function index(Request $request)
    {
        if ($request->user()->role === 'admin') {
            $tasks = Task::all();
        } else {
            $tasks = $request->user()->tasks;
        }

        return response()->json($tasks);
    }

    // Create a new task (any authenticated user can create a task)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,completed,in_progress',
            'priority' => 'nullable|in:low,medium,high',
            'deadline' => 'required|date',
        ]);

        $task = $request->user()->tasks()->create($request->all());

        return response()->json($task, 201);
    }

    // Update a task (admin can update any task; users can update their own)
    public function update(Request $request, Task $task)
    {
        if ($request->user()->role !== 'admin' && $request->user()->id !== $task->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,completed,in_progress',
            'priority' => 'sometimes|in:low,medium,high',
            'deadline' => 'sometimes|date',
        ]);

        $task->update($request->all());

        return response()->json($task);
    }

    // Delete a task (admin can delete any task; users can delete their own)
    public function destroy(Request $request, Task $task)
    {
        if ($request->user()->role !== 'admin' && $request->user()->id !== $task->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}