<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Hugo 0.104.2">
        <title>Signin Template · Bootstrap v5.2</title>

        <link rel="canonical" href="https://getbootstrap.com/docs/5.2/examples/sign-in/">
        <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">

        <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
            font-size: 3.5rem;
            }
        }

        .b-example-divider {
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            vertical-align: -.125em;
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        </style>

        <!-- Custom styles for this template -->
        <link href="/login/signin.css" rel="stylesheet">
    </head>

    <body class="text-center">
    
        <main class="form-signin w-100 m-auto">
            <img class="mb-1" src="/images/denso.png" alt="" width="250">
            <h1 class="h3 mb-3 fw-bolder">Sorting System</h1>
            <h1 class="h3 mb-3 fw-normal">Login</h1>

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
                <p class="mt-4 mb-3 text-muted">&copy; 2025</p>
            </form>
        </main>

        <script src="/bootstrap/js/bootstrap.min.js"></script>
    </body>
</html>
