<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[PRIM] You are now registered as a student!</title>
</head>
<body>
    <h1>Welcome, {{ $user->first_name }}!</h1>
    <p>Your registration as a <b>{{ $user->user_type }}</b> was successful. You can now log in to your account.</p>
    <a href="#" target="_blank" style="border-radius: 16px; padding-top: 8px; padding-bottom: 8px; padding-left: 16px; padding-right: 16px; background-color: black; color: white; margin-top: 8px; font-size: 28px;">OPEN APP</a>
</body>
</html>