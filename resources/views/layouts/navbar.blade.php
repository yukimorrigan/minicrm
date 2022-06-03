<nav class="main-header navbar navbar-expand navbar-dark">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                {{App\Facades\LocalizationService::getLangText()}}
                <i class="fas fa-caret-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                @foreach(App\Facades\LocalizationService::getOtherLanguages() as $lang)
                    <a href="{{$lang['route']}}" class="dropdown-item dropdown-header">
                        {{$lang['text']}}
                    </a>
                @endforeach
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-sign-out-alt"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <form action="{{route('logout')}}" method="post">
                    @csrf
                    <input type="submit" value="{{ __('Log Out') }}" class="dropdown-item dropdown-header">
                </form>
            </div>
        </li>
    </ul>
</nav>


