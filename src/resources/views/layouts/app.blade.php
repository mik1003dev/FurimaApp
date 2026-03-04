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

    {{-- COACHTECH 提供のCSSを読み込む --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
                        <li class="header__nav-item">
                            <a href="#" class="header__nav-link">ログイン</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="#" class="header__nav-link">マイページ</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="#" class="header__nav-button">出品</a>
                        </li>
                    </ul>
                </nav>
            </div>

            {{-- 2段目タブ：商品一覧画面のみ表示（商品詳細では出さない） --}}
            @if (request()->routeIs('items.index'))
            <div class="header__tabs">
                <a
                    href="{{ route('items.index') }}"
                    class="header__tab {{ request('tab') !== 'mylist' ? 'is-active' : '' }}">
                    おすすめ
                </a>

                <a
                    href="{{ route('items.index', ['tab' => 'mylist']) }}"
                    class="header__tab {{ request('tab') === 'mylist' ? 'is-active' : '' }}">
                    マイリスト
                </a>
            </div>
            @endif
        </header>

        {{-- フラッシュメッセージ --}}
        @if (session('status'))
        <div class="flash flash--success">
            {{ session('status') }}
        </div>
        @endif

        <main class="main {{ request()->routeIs('items.show') ? 'main--detail' : '' }}">
            <div class="main__inner">
                @yield('content')
            </div>
        </main>

        <footer class="footer">
            <div class="footer__inner">
                <small>&copy; {{ date('Y') }} フリマアプリ</small>
            </div>
        </footer>

    </div>
</body>

</html>