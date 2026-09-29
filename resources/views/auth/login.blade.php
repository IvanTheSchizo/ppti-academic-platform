<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
</head>
<body>

    <h1>PPTI Academic Platform</h1>
    <h2>Admin Login</h2>
    <form method="POST" action="{{ route('login.submit') }}">
    @csrf

    <div>
        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            required
        >
    </div>

    <div>
        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >
    </div>

    <button type="submit">Login</button>
</form>

</body>
</html>