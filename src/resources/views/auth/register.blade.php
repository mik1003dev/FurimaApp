<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>会員登録</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="login-page-body">
    <main class="login-page">
        <section class="login-panel">
            <header class="header">
                <div class="header__inner">
                    <div class="header__logo">
                        <a href="{{ route('items.index') }}">
                            <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH" class="header__logo-image">
                        </a>
                    </div>
                </div>
            </header>

            <div class="login-panel__content">
                <h1 class="login-panel__title">会員登録</h1>

                @if ($errors->any())
                    <div class="login-panel__error">
                        @foreach ($errors->all() as $error)
                            <p class="login-panel__error-line">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST" class="login-form" novalidate autocomplete="off">
                    @csrf

                    <div class="login-form__group">
                        <label for="name" class="login-form__label">ユーザー名</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            autocomplete="off"
                            required
                            autofocus
                            class="login-form__input">
                    </div>

                    <div class="login-form__group">
                        <label for="email" class="login-form__label">メールアドレス</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="off"
                            required
                            class="login-form__input">
                    </div>

                    <div class="login-form__group">
                        <label for="password" class="login-form__label">パスワード</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            required
                            class="login-form__input">
                    </div>

                    <div class="login-form__group">
                        <label for="password_confirmation" class="login-form__label">確認用パスワード</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            required
                            class="login-form__input">
                    </div>

                    <button type="submit" class="login-form__submit">登録する</button>
                </form>

                <p class="login-panel__register">
                    <a href="{{ route('login') }}" class="login-panel__register-link">ログインはこちら</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
