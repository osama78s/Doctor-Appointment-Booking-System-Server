<!DOCTYPE html>
<html>
<head>
    <title>Your Verification Code</title>
</head>
<body>
    <h1>Hello, {{ $first_name }} {{ $last_name }}</h1>
    <p>Your verification code is: <strong>{{ $code }}</strong></p>
    <p>This code will expire in 3 minutes.</p>
    <p>If you did not request this code, please ignore this email.</p>
</body>
</html>
