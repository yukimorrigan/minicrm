<!DOCTYPE html>
<html lang="{{config('app.locale')}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdminLTE 3 | Dashboard</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!--Script -->
    <script src="{{ asset('js/app.js') }}"></script>
</head>
<body class="dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed" style="height: auto;">
<div class="wrapper">

    <!-- Preloader -->
    <x-preloader />

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Main Sidebar Container -->
    @include('layouts.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{$header}}</h1>
                    </div>
                    <x-breadcrums :prev="$prev" :prevHeader="$prevHeader">{{$header}}</x-breadcrums>
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            {{$slot}}
        </section>
    </div>
    <!-- /.content-header -->
    @include('layouts.footer')
</div>
<!-- ./wrapper -->
</body>
</html>
