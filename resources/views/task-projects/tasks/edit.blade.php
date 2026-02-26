@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Edit Task — {{ $taskProject->name }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('task-projects.index') }}">Task Projects</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('task-projects.show', $taskProject) }}">{{ $taskProject->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('task-projects.tasks.index', $taskProject) }}">Tasks</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0">
            <h5>Edit: {{ $task->title }}</h5>
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

            <form method="post" action="{{ route('task-projects.tasks.update', [$taskProject, $task]) }}">
                @csrf
                @method('put')
                @include('task-projects.tasks._form')
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Perbarui Task</button>
                    <a class="btn btn-secondary" href="{{ route('task-projects.tasks.index', $taskProject) }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
