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
    <title>Masuk - Focus Group Capital</title>
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
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/bootstrap.css">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link id="color" rel="stylesheet" href="../assets/css/color-1.css" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="../assets/css/responsive.css">
  </head>
  <body>
    <!-- Loader starts-->
    <div class="loader-wrapper">
      <div class="loader"></div>
    </div>
    <!-- Loader ends-->
    <!-- login page start-->
    <div class="container-fluid p-0">
      <div class="row m-0">
        <div class="col-12 p-0">
          <div class="login-card">
            <div>
              <div><a class="logo" href="{{ url('/') }}"><img class="img-fluid for-light" src="../assets/images/logo/login.png" alt="loginpage"></a></div>
              <div class="login-main">
                <form class="theme-form" method="post" action="{{ route('login.store') }}">
                  @csrf
                  <h4 class="text-center">Masuk ke akun</h4>
                  <p class="text-center">Masukkan email dan kata sandi untuk login</p>
                  <div class="form-group">
                    <label class="col-form-label">Alamat Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" name="email" type="email" required autocomplete="email" placeholder="nama@perusahaan.com" value="{{ old('email') }}">
                    @error('email')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Kata Sandi</label>
                    <div class="form-input position-relative">
                      <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="current-password" placeholder="*********">
                      <div class="show-hide"><span class="show"></span></div>
                    </div>
                    @error('password')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="form-group mb-0">
                    <div class="checkbox p-0">
                      <input id="checkbox1" type="checkbox" name="remember" value="1">
                      <label class="text-muted" for="checkbox1">Ingat saya</label>
                    </div>
                    <div class="text-end mt-3">
                      <button class="btn btn-primary btn-block w-100" type="submit">Masuk</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
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
      <!-- Sidebar jquery-->
      <script src="../assets/js/config.js"></script>
      <!-- Plugins JS start-->
      <!-- Plugins JS Ends-->
      <!-- Theme js-->
      <script src="../assets/js/script.js"></script>
      <!-- login js-->
      <!-- Plugin used-->
    </div>
  </body>
</html>
