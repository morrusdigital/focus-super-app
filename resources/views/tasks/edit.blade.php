@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Edit Task — {{ $project->name }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.tasks.index', $project) }}">Tasks</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0">
            <h5>Edit Task: {{ $task->title }}</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('projects.tasks.update', [$project, $task]) }}">
                @csrf
                @method('put')
                @include('tasks._form', ['assigneeIds' => old('assignees', $assigneeIds)])
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Perbarui Task</button>
                    <a class="btn btn-secondary" href="{{ route('projects.tasks.index', $project) }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
