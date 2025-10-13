<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial; margin: 0; padding: 0; background: #f8fafc; color: #0f172a; }
        .container { max-width: 640px; margin: 10vh auto; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -4px rgba(0,0,0,.1); text-align: center; }
        h1 { font-size: 24px; margin: 0 0 8px; }
        p { margin: 0 0 16px; color: #475569; }
        a.button { display: inline-block; padding: 10px 16px; background: #0ea5e9; color: white; border-radius: 8px; text-decoration: none; }
        a.button:hover { background: #0284c7; }
    </style>
</head>
<body>
    <div class="container">
        <h1>404 Not Found</h1>
        <p>The page you are looking for could not be found.</p>
        <a href="{{ request()->fullUrl() }}" class="button">Try again</a>
    </div>
</body>
</html>


