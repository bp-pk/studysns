<header>

    <div class="site-width">
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
    </div>
</header>