@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Dashboard</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0">
        <h5>Selamat datang</h5>
      </div>
      <div class="card-body">
        <p>Silakan gunakan menu di sidebar untuk mengakses modul Budget Plan.</p>
      </div>
    </div>
  </div>
@endsection
