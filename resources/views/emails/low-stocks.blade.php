<!DOCTYPE html>
<html lang="es">

<head>
    <title>Inventario - TesLtda -- Alerta de existencias bajas --</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('build/assets/app-qqJ1WtSm.css') }}">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9fafb;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 2rem auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header {
            background-color: #f43f5e;
            color: #ffffff;
            padding: 1.5rem;
            text-align: center;
        }

        .email-header a {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }

        .email-body {
            padding: 2rem;
        }

        .email-body h2 {
            font-size: 1.25rem;
            color: #1f2937;
        }

        .email-body p {
            margin-top: 1rem;
            line-height: 1.6;
            color: #4b5563;
        }

        .email-body ul {
            margin: 1.5rem 0;
            padding-left: 1.5rem;
            color: #1f2937;
        }

        .email-body ul li {
            margin-bottom: 0.5rem;
        }

        .email-button {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background-color: #f43f5e;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .email-button:hover {
            background-color: #e11d48;
        }

        .email-footer {
            margin-top: 2rem;
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <header class="email-header">
            <a href="{{ env('APP_URL') }}">INVENTARIO_TESLTDA</a>
        </header>

        <div style="text-align: center;">
            <img src="{{ asset('images/logo.png') }}" alt="INVENTARIO_TESLTDA Logo" style="width: 100px; margin-bottom: 1rem;">
        </div>

        <div class="email-body">
            {{-- <h1>Low Stocks Alert</h1> --}}

            <h2>Hola {{ $user->name }},</h2>

            <p>
                Hemos observado que las existencias de algunos productos de tintas y tóners se están agotando. <br>
                <strong>A continuación figura la lista de elementos que requieren su atención:</strong>
            </p>

            <ul>
                @foreach ($products as $product)
                    <li><strong>{{ $product->name }}</strong>: Sólo quedan {{ $product->quantity }} en stock</li>
                @endforeach
            </ul>

            <p>
                Puedes comprobarlo directamente en la aplicación pulsando el botón de abajo.
            </p>

            <a href="{{ route('filament.admin.auth.login') }}" class="email-button">
                Iniciar sesión
            </a>

            <p class="email-footer">
                Gracias,  <br>
                Equipo de soporte de TesLtda
            </p>
        </div>
    </div>
</body>

</html>
