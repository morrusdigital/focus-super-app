@extends('layouts.app')

@section('title', 'Ubah Budget Plan')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Ubah Budget Plan</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('budget-plans.index') }}">Budget Plan</a></li>
            <li class="breadcrumb-item active">Ubah</li>
          </ol>
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('budget-plans.update', $budgetPlan) }}">
      @csrf
      @method('put')
      @include('budget_plans._form', ['budgetPlan' => $budgetPlan])
      <div class="card">
        <div class="card-body text-end">
          <a class="btn btn-light" href="{{ route('budget-plans.show', $budgetPlan) }}">Batal</a>
          <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
@endsection
