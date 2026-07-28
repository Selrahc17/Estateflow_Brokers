<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#d97706">
    <title>EstateFlow — You're Offline</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, sans-serif; background: #fff7ed; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem; }
        .card { background: white; border-radius: 1.5rem; padding: 2.5rem 2rem; text-align: center; max-width: 380px; width: 100%; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .icon { width: 64px; height: 64px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; }
        h1 { font-size: 1.25rem; font-weight: 700; color: #1c1917; margin-bottom: .5rem; }
        p { font-size: .875rem; color: #78716c; line-height: 1.6; margin-bottom: 1.5rem; }
        a { display: inline-block; background: #d97706; color: white; font-size: .875rem; font-weight: 600; padding: .625rem 1.5rem; border-radius: .75rem; text-decoration: none; }
        a:hover { background: #b45309; }
        .brand { display: flex; align-items: center; justify-content: center; gap: .5rem; margin-bottom: 1.5rem; }
        .brand-icon { width: 32px; height: 32px; background: #d97706; border-radius: .5rem; display: flex; align-items: center; justify-content: center; }
        .brand span { font-size: 1rem; font-weight: 700; color: #1c1917; }
        .brand span em { color: #d97706; font-style: normal; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <div class="brand-icon">
                <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <span>Estate<em>Flow</em></span>
        </div>
        <div class="icon">
            <svg width="32" height="32" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M12 12h.01M6.343 17.657a9 9 0 010-12.728M9.172 14.828a5 5 0 010-7.072"/></svg>
        </div>
        <h1>You're Offline</h1>
        <p>It looks like you've lost your internet connection. Please check your network and try again.</p>
        <a href="/" onclick="window.location.reload(); return false;">Try Again</a>
    </div>
</body>
</html>
