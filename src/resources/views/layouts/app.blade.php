{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'フリマアプリ')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Inter フォント読み込み --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @php
        $sanitizeCssVersion = file_exists(public_path('css/sanitize.css')) ? filemtime(public_path('css/sanitize.css')) : null;
        $appCssVersion = file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : null;
    @endphp

    {{-- ベースリセットを先に読み込み、画面差異を減らす --}}
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}{{ $sanitizeCssVersion ? '?v=' . $sanitizeCssVersion : '' }}">

    {{-- CSS更新時にブラウザキャッシュが残らないようにする --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}{{ $appCssVersion ? '?v=' . $appCssVersion : '' }}">
    @stack('styles')
</head>

<body class="app">
    <div class="page-wrapper">

        <header class="header">
            <div class="header__inner">
                {{-- 左：ロゴ --}}
                <div class="header__logo">
                    <a href="{{ route('items.index') }}">
                        <img
                            src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}"
                            alt="COACHTECH"
                            class="header__logo-image">
                    </a>
                </div>

                {{-- 中央：検索フォーム（入力欄だけ・ボタンなし） --}}
                <div class="header__search">
                    <form action="{{ route('items.index') }}" method="GET" class="search-form">
                        {{-- タブ保持（mylist のまま検索したい場合） --}}
                        @if (request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif

                        <input
                            type="text"
                            name="keyword"
                            value="{{ request('keyword') }}"
                            class="search-form__input"
                            placeholder="なにをお探しですか？">
                        {{-- ボタンはFigma上は非表示なので置かない（Enterキーで送信） --}}
                    </form>
                </div>

                {{-- 右：ログイン / マイページ / 出品 --}}
                <nav class="header__nav">
                    <ul class="header__nav-list">
                        @guest
                            <li class="header__nav-item">
                                <a href="{{ route('login') }}" class="header__nav-link">ログイン</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="{{ route('login') }}" class="header__nav-link">マイページ</a>
                            </li>
                        @endguest
                        @auth
                            <li class="header__nav-item">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="header__nav-link header__nav-logout">ログアウト</button>
                                </form>
                            </li>
                            <li class="header__nav-item">
                                <a href="{{ route('profile.show') }}" class="header__nav-link">マイページ</a>
                            </li>
                        @endauth
                        <li class="header__nav-item">
                            @guest
                                <a href="{{ route('login') }}" class="header__nav-button">出品</a>
                            @else
                                <a href="{{ route('items.create') }}" class="header__nav-button">出品</a>
                            @endguest
                        </li>
                    </ul>
                </nav>
            </div>

            {{-- 2段目タブ：商品一覧画面のみ表示（商品詳細では出さない） --}}
            @if (request()->routeIs('items.index'))
            <div class="header__tabs">
                <a
                    href="{{ route('items.index', array_filter(['keyword' => request('keyword')])) }}"
                    class="header__tab {{ request('tab') !== 'mylist' ? 'is-active' : '' }}">
                    おすすめ
                </a>

                <a
                    href="{{ route('items.index', array_filter(['tab' => 'mylist', 'keyword' => request('keyword')])) }}"
                    class="header__tab {{ request('tab') === 'mylist' ? 'is-active' : '' }}">
                    マイリスト
                </a>
            </div>
            @endif
        </header>

        {{-- フラッシュメッセージ --}}
        @if (session('status') && !request()->routeIs('profile.edit'))
        <div class="flash flash--success" role="status" aria-live="polite">
            <span class="flash__accent" aria-hidden="true"></span>
            <div class="flash__body">
                <p class="flash__message">{{ session('status') }}</p>
            </div>
        </div>
        @endif

        <main class="main {{ request()->routeIs('items.show') ? 'main--detail' : '' }}">
            <div class="main__inner">
                @yield('content')
            </div>
        </main>

    </div>
</body>

</html>
