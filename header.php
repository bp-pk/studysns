<header>
   
    <div class="site-width header">
    <?php
        $url = $_SERVER['REQUEST_URI'];
    ?>
     <?php 
        if(empty($_SESSION['user_id'])){
     ?>
      <h1><a href="index.php">STUDY!</a></h1>
      <?php
        }else{
      ?>
      <h1><a href="home.php">STUDY!</a></h1>
      <?php
        }
      ?>
      <nav id="top-nav">
        <ul>
           <?php
            if(empty($_SESSION['user_id'])){
            ?>
            <li><a href="signup.php">ユーザー登録</a></li>
            <li><a href="login.php">ログイン</a></li>
            <?php
            }else{
            ?>
            <li><a href="post.php">投稿</a></li>
            <li><a href="home.php">ホーム</a></li>
            <?php
            }
            ?>
        </ul>
       </nav>
       <div class="menu-trigger js-toggle-sp-menu"><!-- ハンバーガーメニュー -->
       <span></span>
       <span></span>
       <span></span>
       </div>
       <nav id="top-nav-min" class="js-toggle-sp-menu-target">
           <ul class="mune">
           <?php
            if(empty($_SESSION['user_id'])){
            ?>
            <li class="menu-item"><a class="menu-link" href="signup.php">ユーザー登録</a></li>
            <li class="menu-item"><a class="menu-link" href="login.php">ログイン</a></li>
            <?php
            }else{
            ?>
            <!-- マイページ取得用 ユーザーデータ -->
            <?php $userData = getUser($_SESSION['user_id']); ?>
            <li class="menu-item"><a class="menu-link" href="home.php">ホーム</a></li>
            <li class="menu-item"><a class="menu-link" href="userpage.php?u_id=<?php echo sanitize($userData['id']); ?>">マイページ</a></li>
            <li class="menu-item"><a class="menu-link" href="post.php">投稿</a></li>
            <li class="menu-item"><a class="menu-link" href="profEdit.php">プロフィール</a></li>
            <li class="menu-item"><a class="menu-link" href="passEdit.php">パスワード変更</a></li>
            <li class="menu-item"><a class="menu-link" href="logout.php">ログアウト</a></li>
            <li class="menu-item"><a class="menu-link" href="withdraw.php">退会</a></li>
            <?php
            }
            ?>
        </ul>
       </nav>
    </div>
</header>