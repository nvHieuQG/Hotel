<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ route('index') }}">MARRON</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>
        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active"><a href="{{ route('index') }}" class="nav-link">Home</a></li>
                <li class="nav-item">
                    <a href="{{ route('rooms') }}" class="nav-link">Phòng</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('promotions.index') }}" class="nav-link">Khuyến mãi</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('about') }}" class="nav-link">Giới thiệu</a>
                </li>
                <li class="nav-item"><a href="{{ route('blog') }}" class="nav-link">Blog</a></li>
                <li class="nav-item"><a href="{{ route('contact') }}" class="nav-link">Contact</a></li>
                <li class="nav-item">
                    <a href="{{ route('chatbot.index') }}" class="nav-link">
                        <i class="icon-cog"></i> Chat AI
                    </a>
                </li>
                
                @guest
                    <li class="nav-item"><a href="{{ route('login') }}" class="nav-link">Đăng nhập</a></li>
                    <li class="nav-item"><a href="{{ route('register') }}" class="nav-link">Đăng ký</a></li>
                @else
                    <li class="nav-item"><a href="{{ route('tour-booking.search') }}" class="nav-link">Đặt Tour</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="{{ route('user.profile') }}">Quản lý tài khoản</a>
                            <a class="dropdown-item" href="{{ route('tour-booking.index') }}">Tour Bookings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Đăng xuất</button>
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
