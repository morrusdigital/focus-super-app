<!DOCTYPE html>
<html lang="id">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Enzo admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Enzo admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <base href="{{ url('/html/template') }}/">
    <link rel="icon" href="../assets/images/favicon/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/images/favicon/favicon.png" type="image/x-icon">
    <title>@yield('title', 'Dashboard') - Focus Group Capital</title>
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/font-awesome.css">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/icofont.css">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/themify.css">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/flag-icon.css">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/feather-icon.css">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/scrollbar.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/animate.css">
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/bootstrap.css">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link id="color" rel="stylesheet" href="../assets/css/color-1.css" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="../assets/css/responsive.css">
    @stack('styles')
  </head>
  <body>
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <div class="loader-wrapper">
      <div class="loader"></div>
    </div>
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
      <div class="page-header">
        <div class="header-wrapper row m-0">
          <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper"><a href="{{ url('/') }}"><img class="img-fluid" src="../assets/images/logo/login.png" alt=""></a></div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
          </div>
          <div class="nav-right col-8 pull-right right-header p-0 ms-auto">
            <ul class="nav-menus">
              <li class="profile-nav onhover-dropdown p-0 me-0">
                <div class="d-flex profile-media">
                  <img class="b-r-50" src="../assets/images/dashboard/profile.jpg" alt="">
                  <div class="flex-grow-1">
                    <span>{{ auth()->user()->name ?? 'User' }}</span>
                    <p class="mb-0 font-roboto">{{ auth()->user()->role ?? '' }} <i class="middle fa fa-angle-down"></i></p>
                  </div>
                </div>
                <ul class="profile-dropdown onhover-show-div">
                  <li>
                    <form method="post" action="{{ route('logout') }}">
                      @csrf
                      <button class="dropdown-item w-100 text-start bg-transparent border-0" type="submit">
                        <i data-feather="log-out"></i><span>Keluar</span>
                      </button>
                    </form>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="page-body-wrapper">
        <div class="sidebar-wrapper">
          <div>
            <div class="logo-wrapper">
              <a href="{{ url('/') }}"><img class="img-fluid for-light" src="../assets/images/logo/logo.png" alt=""></a>
              <div class="back-btn"><i class="fa fa-angle-left"></i></div>
              <div class="toggle-sidebar"><i class="fa fa-cog status_toggle middle sidebar-toggle"></i></div>
            </div>
            <div class="logo-icon-wrapper"><a href="{{ url('/') }}"><img class="img-fluid" src="../assets/images/logo/logo-icon1.png" alt=""></a></div>
            <nav class="sidebar-main">
              <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
              <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                  <li class="back-btn">
                    <a href="{{ url('/') }}"><img class="img-fluid" src="../assets/images/logo/logo-icon.png" alt=""></a>
                    <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                  </li>
                  <li class="sidebar-main-title">
                    <div>
                      <h6>Budget Planning</h6>
                    </div>
                  </li>

                  @if (auth()->user()?->isAdminCompany())
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('budget-plans.create') ? 'active' : '' }}"
                         href="{{ route('budget-plans.create') }}">
                        <i data-feather="plus-circle"></i><span>Ajukan BP</span>
                      </a>
                    </li>
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('budget-plans.index') && request('status') !== 'submitted' ? 'active' : '' }}"
                         href="{{ route('budget-plans.index') }}">
                        <i data-feather="file-text"></i><span>Daftar BP Saya</span>
                      </a>
                    </li>
                  @endif

                  @if (auth()->user()?->isFinanceHolding())
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('budget-plans.index') && request('status') === 'submitted' ? 'active' : '' }}"
                         href="{{ route('budget-plans.index', ['status' => 'submitted']) }}">
                        <i data-feather="clipboard"></i><span>Review BP</span>
                      </a>
                    </li>
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('budget-plans.index') && request('status') !== 'submitted' ? 'active' : '' }}"
                         href="{{ route('budget-plans.index') }}">
                        <i data-feather="list"></i><span>Semua BP</span>
                      </a>
                    </li>
                  @endif

                  <li class="sidebar-main-title">
                    <div>
                      <h6>Project Management</h6>
                    </div>
                  </li>
                  <li class="sidebar-list">
                    <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                      <i data-feather="pie-chart"></i><span>Portfolio Dashboard</span>
                    </a>
                  </li>
                  <li class="sidebar-list">
                    <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                      <i data-feather="briefcase"></i><span>Projects</span>
                    </a>
                  </li>
                </ul>
              </div>
            </nav>
          </div>
        </div>
        <div class="page-body">
          @if(session('success'))
            <div class="container-fluid">
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            </div>
          @endif

          @if(session('error'))
            <div class="container-fluid">
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            </div>
          @endif

          @yield('content')
        </div>
      </div>
    </div>
    <!-- latest jquery-->
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap js-->
    <script src="../assets/js/bootstrap/bootstrap.bundle.min.js"></script>
    <!-- feather icon js-->
    <script src="../assets/js/icons/feather-icon/feather.min.js"></script>
    <script src="../assets/js/icons/feather-icon/feather-icon.js"></script>
    <!-- scrollbar js-->
    <script src="../assets/js/scrollbar/simplebar.js"></script>
    <script src="../assets/js/scrollbar/custom.js"></script>
    <!-- Sidebar jquery-->
    <script src="../assets/js/config.js"></script>
    <script src="../assets/js/sidebar-menu.js"></script>
    <!-- Theme js-->
    <script src="../assets/js/script.js"></script>
    <script>
      // Hide loader immediately
      document.addEventListener('DOMContentLoaded', function () {
        var loader = document.querySelector('.loader-wrapper');
        if (loader) {
          loader.style.display = 'none';
        }
      });

      window.addEventListener('load', function () {
        var loader = document.querySelector('.loader-wrapper');
        if (loader) {
          loader.style.display = 'none';
        }
      });
    </script>

    @stack('scripts')
  </body>
</html>
