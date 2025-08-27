<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Furniro - Premium Furniture')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ time() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}?v={{ time() }}">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --header-height: 80px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff;
        }
        
        /* Header Styles */
        .header {
            background: #fff;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        /* Add padding to body to compensate for fixed header */
        body {
            padding-top: var(--header-height);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            color: #B88E2F;
            text-decoration: none;
        }
        
        .logo i {
            font-size: 32px;
        }
        
        .nav-menu {
            display: flex;
            gap: 40px;
            list-style: none;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 16px;
            transition: color 0.3s;
        }
        
        .nav-menu a:hover,
        .nav-menu a.active {
            color: #B88E2F;
        }
        
        .header-icons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .header-icons a {
            color: #333;
            font-size: 18px;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .header-icons a:hover {
            color: #B88E2F;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #B88E2F;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Hero Section */
                .hero-section {
            background: url('/images/hero-bg.svg') center/cover;
            background-size: cover;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: #ffffff !important;
            padding: 120px 0;
            text-align: center;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(1px);
        }
        
        .hero-content {
            text-align: center;
            position: relative;
            z-index: 2;
            color: #ffffff !important;
        }
        
        .hero-content h1 {
            font-size: 48px;
            font-weight: 600;
            color: #ffffff !important;
            margin-bottom: 10px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #ffffff !important;
            font-size: 16px;
        }
        
        .breadcrumb a {
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb a:hover {
            color: #ffffff !important;
            text-decoration: underline;
        }

        .breadcrumb span {
            color: #ffffff !important;
        }
        
        .breadcrumb i {
            font-size: 12px;
            color: #ffffff !important;
        }
        
        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Filter Bar */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #F9F1E7;
            margin-bottom: 40px;
        }
        
        .filter-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .filter-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: none;
            color: #333;
            font-size: 16px;
            cursor: pointer;
        }
        
        .view-options {
            display: flex;
            gap: 10px;
        }
        
        .view-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .view-btn.active {
            background: #B88E2F;
            color: #fff;
            border-color: #B88E2F;
        }
        
        .filter-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .showing-text {
            color: #666;
        }
        
        .sort-select, .show-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: #fff;
            color: #333;
        }

        /* Section Card Styles */
        .section-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #f0f0f0;
        }

        .section-card h3 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-card h4 {
            font-size: 18px;
            font-weight: 500;
            color: #555;
            margin-bottom: 15px;
        }



        /* Button Styles */
        .btn-primary {
            background: #B88E2F;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }

        .btn-primary:hover {
            background: #9A7628;
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .qr-image {
                width: 200px;
                height: 200px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @include('customer.components.header')
    
    @yield('hero')
    
    <main class="main-content">
        @yield('content')
    </main>
    
    @include('customer.components.footer')
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>
