<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>新規投稿</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
  <!-- google fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="{{asset('css/style.css')}}">
</head>

<body>
  <header>
    <nav class="navbar navbar-light bg-light">
      <div class="container">
        <a href="{{route('posts.index')}}" class="navbar-brand">投函アプリ</a>
        <ul class="navbar-nav">
          <li class="nav-item">
            <a href="{{route('logout')}}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit()">ログアウト</a>
            <form id="logout-form" action="{{route('logout')}}" method="POST">
              @csrf
            </form>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <main>
    <div class="container">
      <h1 class="fs-2 my-3">新規投稿</h1>

      @if($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      <div class="mb-2">
        <a href="{{ route('posts.index') }}" class="text-decoration-none">&lt; 戻る</a>
      </div>

      <form action="{{route('posts.store')}}" method="POST">
        @csrf
        <div class="form-group mb-3">
          <label for="title">タイトル</label>
          <!-- oldのヘルパ関数をvalueで持つことで、直前の入力を呼べる -->
          <input type="text" class="form-control" id="title" name="title" value="{{old('title')}}">
        </div>
        <div class="form-group mb-3">
          <label for="content">本文</label>
          <textarea class="form-control" id="content" name="content">{{old('content')}}</textarea>
        </div>
        <button type="submit" class="btn btn-outline-primary">投稿</button>
      </form>
    </div>
  </main>

  <footer class="d-flex justify-content-center align-items-center bg-light">
    <p>&copy; 投稿アプリ All rights reserved.</p>
  </footer>

  <!-- Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</body>

</html>