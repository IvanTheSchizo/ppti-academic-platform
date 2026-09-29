<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PPTI Academic Platform</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #0098d9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 32px;
            width: 100%;
            max-width: 400px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 32px;
        }

        .icon-box {
            background-color: #dbeafe;
            color: #3b82f6;
            border-radius: 4px;
            padding: 8px 12px;
            font-weight: bold;
        }

        .title {
            color: #374151;
            font-size: 17px;
            font-weight: bold;
        }

        .input-group {
            position: relative;
            margin-bottom: 16px;
        }

        .input-group.last {
            margin-bottom: 24px;
        }

        .input-field {
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            background-color: #f9fafb;
            font-size: 14px;
            outline: none;
        }

        .input-field:focus {
            border-color: #60a5fa;
            background-color: #ffffff;
        }

        .submit-btn {
            width: 100%;
            background-color: #0f4a73;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            letter-spacing: 0.5px;
        }

        .submit-btn:hover {
            background-color: #0c3a5a;
        }

        .error-message {
            color: #dc2626;
            font-size: 13px;
            margin-bottom: 16px;
        }
    </style>
</head>

<body>

    <div class="card">

        <div class="header">
            <div class="icon-box">🎓</div>
            <h1 class="title">PPTI Academic Platform</h1>
        </div>

        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">

            @csrf

            <div class="input-group">
                <input
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="Username"
                    class="input-field"
                    required
                >
            </div>

            <div class="input-group last">
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    class="input-field"
                    required
                >
            </div>

            <button type="submit" class="submit-btn">
                SIGN IN
            </button>

        </form>

    </div>

</body>
</html>