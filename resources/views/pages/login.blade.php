<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Hugo 0.104.2">
        <title>Sorting System - Login</title>

        <link rel="stylesheet" href="{{ url('/bootstrap/css/bootstrap.min.css') }}">

        <!-- Custom styles for this template -->
        <link href="{{ url('/login/signin.css') }}" rel="stylesheet">
    </head>

    <body class="text-center">
    
        <main class="form-signin w-100 m-auto">
            <img class="mb-1" src="{{ url('/images/denso.png') }}" alt="" width="250">
            <h1 class="h3 mb-3 fw-bolder">Sorting System</h1>
            <!-- <h1 class="h3 mb-3 fw-normal">Login</h1> -->

            {{-- Error Alert --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ url('signin') }}">
                @csrf
                <div class="form-floating">
                <input type="text" class="form-control" name="npk" id="npk" placeholder="name@example.com">
                <label for="npk">NPK</label>
                </div>
                <div class="form-floating">
                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                <label for="password">Password</label>
                </div>

                <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Login</button>
                <p class="mt-4 mb-3 text-muted">&copy; 2025. Sorting System v1.1.0</p>
            </form>
        </main>

        <script src="{{ url('/bootstrap/js/bootstrap.min.js') }}"></script>
    </body>
</html>
