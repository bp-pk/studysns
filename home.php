<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　投稿ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
/*=====================================
  画面処理
 =====================================*/
//DBからユーザーデータを取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData,true));
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


<?php
$siteTitle = 'Home';
require('head.php');
?>
<body class="page-2colum">
    
<!-- ヘッダー -->
<?php
require('header.php');
?>

<p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
</p>
<!-- メイン -->
<div id="contents" class="site-width">

<section class="mydata">
    <div class="mypanel">
        <a href="userpage.php?u_id=<?php echo sanitize($userData['id']); ?>"><img src="<?php echo showImg(sanitize($userData['pic'])); ?>" alt=""></a>
        <h2><?php echo $userData['username']; ?></h2>
    </div>
    <?php
    if(!empty($userData['com'])){
    ?>
    <div class="myname-list">
        <p><?php echo $userData['com']; ?></p>
    </div>
    <?php
    }
    ?>
    <?php
    require('menu.php');
    ?>
</section>

<!-- メインコンテンツ -->
<section id="main">
<?php
require('records.php');
?>
    <div class="pagination">
          <ul class="pagination-list">
            <?php
              $pageColNum = 5;
              $totalPageNum = $dbPostList['total_page'];
              // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
              if( $currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum - 4;
                $maxPageNum = $currentPageNum;
              // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
              }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum - 3;
                $maxPageNum = $currentPageNum + 1;
              // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
              }elseif( $currentPageNum == 2 && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum - 1;
                $maxPageNum = $currentPageNum + 3;
              // 現ページが1の場合は左に何も出さない。右に５個出す。
              }elseif( $currentPageNum == 1 && $totalPageNum >= $pageColNum){
                $minPageNum = $currentPageNum;
                $maxPageNum = 5;
              // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
              }elseif($totalPageNum < $pageColNum){
                $minPageNum = 1;
                $maxPageNum = $totalPageNum;
              // それ以外は左に２個出す。
              }else{
                $minPageNum = $currentPageNum - 2;
                $maxPageNum = $currentPageNum + 2;
              }
            ?>
            <?php if($currentPageNum != 1): ?>
              <li class="list-item"><a href="?p=1">&lt;</a></li>
            <?php endif; ?>
            <?php
              for($i = $minPageNum; $i <= $maxPageNum; $i++):
            ?>
              <li class="list-item <?php if($currentPageNum == $i ) echo 'active'; ?>"><a href="?p=<?php echo $i; ?>"><?php echo $i; ?></a></li>
            <?php
              endfor;
            ?>
            <?php if($currentPageNum != $maxPageNum): ?>
              <li class="list-item"><a href="?p=<?php echo $maxPageNum; ?>">&gt;</a></li>
            <?php endif; ?>
          </ul>
        </div>
</section>
</div>


<?php
require('footer.php');
?>