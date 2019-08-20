<?php
//共通変数・関数ファイルを読込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザーページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
// ログイン認証
require('auth.php');
/*===========================
  画面処理
===========================*/
$u_id = $_GET['u_id'];
$dbPostData = '';
$dbPostGood = '';
$dbPostGoodNum = '';
//=============================
//get送信がある場合
if(!empty($_GET['u_id']) && !empty(getUser($u_id))) {
    $dbPostUserInfo = getUser($u_id);
    $dbPostData = getUserPostList($u_id);
    $dbPostGood = getUserGoodPostList($u_id);
    $dbPostGoodNum = count($dbPostGood['data']);
} else {
    header("Location:home.php");
}
?>
<?php
$siteTitle = ($_SESSION['user_id'] === $u_id) ? 'マイページ' : 'ユーザーページ';
require('head.php');
?>


<body class="page-2colum userpage">

    <!-- ヘッダー -->
    <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
       <section class="mydata">
        <div class="sp-prof-info">
            <div class="prof-icon-wrap">
                <img class="prof-icon" src="<?php echo showImg(sanitize($dbPostUserInfo['pic'])); ?>">
            </div>
            <div class="sp-username"><?php echo sanitize($dbPostUserInfo['username']); ?></div>

            <!-- メッセージがある場合のみ表示 -->
            <?php if(!empty($dbPostUserInfo['msg'])) { ?>
            <div class="sp-user-msg">
                <p><?php echo sanitize($dbPostUserInfo['msg']); ?></p>
            </div>
            <?php } ?>
        </div>
        <div class="post-info">
            <a href="userpage.php?u_id=<?php echo sanitize($u_id); ?>">投稿：<?php echo count($dbPostData); ?></a>
            <a href="userpage.php?u_id=<?php echo sanitize($u_id).'&good=exist'; ?>">いいね：<?php echo $dbPostGoodNum; ?></a>
        </div>
        <?php
           if(!empty($dbPostUserInfo['com'])) {
        ?>
        <div class="myname-list">
            <p><?php echo $dbPostUserInfo['com']; ?></p>
        </div>
        <?php } ?>

            <!-- サイドバー -->
            <?php
            if($_SESSION['user_id'] === $u_id) {
                    require('menu.php');
                }
             ?>
            </section>
            <section id="main" class="my-contents">
                <p id="js-show-msg" style="display: none;" class="msg-slide">
                    <?php echo getSessionFlash('msg_success'); ?>
                </p> 
                <?php
                require('records.php');
                ?>
                
            </section>
    </div>

<!-- フッター -->
<?php
require('footer.php');
?>