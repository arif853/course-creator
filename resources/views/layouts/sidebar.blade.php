<div class="sidebar sidebar-style-2">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="blue">

            <a href="{{ route('admin.dashboard') }}" class="logo">
                {{-- <img src="{{ asset('') }}" alt="navbar brand" class="navbar-brand"
                    height="20"> --}}
                    <h3>Course Creator</h3>
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>

        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper">
        <div class="sidebar-content">
            <div class="profile-section">
                <div class="user-profile d-flex flex-column align-items-center text-center py-4">
                    <div class="avatar avatar-xl mb-3">
                        <img src="{{ asset('') }}assets/img/profile2.jpg" alt="..."
                            class="avatar-img rounded-circle">
                    </div>
                    <div class="avatar avatar-minimize avatar-md mb-3 d-none">
                        <img src="{{ asset('') }}assets/img/profile2.jpg" alt="..."
                            class="avatar-img rounded-circle">
                    </div>
                    <span class="user-name fw-bold mb-1">{{ Auth::user()->name }}</span>
                    <span class="user-level op-7">Administrator</span>
                </div>
                <div class="row menubars border-top border-bottom text-center no-gutters px-4">
                    <div class="col-4 border-right">
                        <a href="#" class="menubar p-3" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-title="Notifications"><i class="fa fa-bell"></i></a>
                    </div>
                    <div class="col-4 border-right">
                        <a href="#" class="menubar p-3" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-title="Settings"><i class="fa fa-cog"></i></a>
                    </div>
                    <div class="col-4">
                        <a href="#" class="menubar p-3" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-title="Email"><i class="fa fa-envelope"></i></a>
                    </div>
                </div>
            </div>
            <div class="nav-sidebar-scroll scrollbar scrollbar-inner">
                <ul class="nav nav-primary">
                    <li class="nav-item active">
                        <a href="#dashboard" class="collapsed" aria-expanded="false">
                            <i class="fas fa-home"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section">Components</h4>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.courses.create')}}">
                            <i class="far fa-calendar-alt"></i>
                            <p>Create Course</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.courses.index')}}">
                            <i class="fas fa-desktop"></i>
                            <p>Mange Course</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
