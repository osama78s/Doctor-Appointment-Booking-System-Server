<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        h2 {
            color: #007bff;
        }

        p {
            margin: 10px 0;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }

        img {
            width: 100px;
            max-width: 100%;
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>New Contact Message</h2>
        <p><strong>First Name:</strong> {{ $first_name }}</p>
        <p><strong>Last Name:</strong> {{ $last_name }}</p>
        <p><strong>Email:</strong> {{ $email }}</p>
        <p><strong>Message:</strong></p>
        <p>{{ $msg }}</p>
        <hr>
        <p class="footer">This email was sent from the contact form on your website.</p>
    </div>
</body>

</html>
