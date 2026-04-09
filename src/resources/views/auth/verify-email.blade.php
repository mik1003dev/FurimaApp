<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>メール認証</title>
    @php
        $sanitizeCssVersion = file_exists(public_path('css/sanitize.css')) ? filemtime(public_path('css/sanitize.css')) : null;
        $appCssVersion = file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : null;
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}{{ $sanitizeCssVersion ? '?v=' . $sanitizeCssVersion : '' }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}{{ $appCssVersion ? '?v=' . $appCssVersion : '' }}">
</head>
<body class="verification-page-body">
    <main class="verification-page">
        <section class="verification-panel">
            <header class="header">
                <div class="header__inner">
                    <div class="header__logo">
                        <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH" class="header__logo-image">
                    </div>
                </div>
            </header>

            <div class="verification-panel__content">
                @if (session('status') === 'verification-link-sent')
                    <div class="verification-panel__success">
                        認証メールを再送しました。
                    </div>
                @endif

                <p class="verification-panel__message">
                    登録していただいたメールアドレスに認証メールを送付しました。<br>
                    メール認証を完了してください。
                </p>

                <a href="http://localhost:8025" target="_blank" rel="noopener noreferrer" class="verification-panel__primary-button">
                    認証はこちらから
                </a>

                <form method="POST" action="{{ route('verification.send') }}" class="verification-panel__resend-form">
                    @csrf
                    <button type="submit" class="verification-panel__resend-link">認証メールを再送する</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
