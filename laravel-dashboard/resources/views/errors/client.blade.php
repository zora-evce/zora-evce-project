<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Something went wrong' }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, "Apple Color Emoji", "Segoe UI Emoji"; margin: 0; padding: 0; background: #f8fafc; color: #0f172a; }
        .container { max-width: 640px; margin: 10vh auto; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -4px rgba(0,0,0,.1); }
        h1 { font-size: 20px; margin: 0 0 8px; }
        p { margin: 0 0 16px; color: #475569; }
        a.button { display: inline-block; padding: 10px 16px; background: #0ea5e9; color: white; border-radius: 8px; text-decoration: none; }
        a.button:hover { background: #0284c7; }
    </style>
    </head>
<body>
    <div class="container">
        <h1>{{ $title ?? 'Unable to process request' }}</h1>
        <p>{{ $message ?? 'Please try again later.' }}</p>
        <a href="/" class="button">Go back home</a>
    </div>
</body>
</html>


