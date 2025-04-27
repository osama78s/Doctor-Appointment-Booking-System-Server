<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
</head>
<body>
    <h1>Hello, {{ $first_name }} {{ $last_name }}</h1>
    <p>Click the link below to reset your password:</p>
    <a href="{{ $link }}" target="_blank" style="color: blue; text-decoration: underline;">Reset Password</a>
    <p>If you did not request this, please ignore this email.</p>
</body>
</html>
