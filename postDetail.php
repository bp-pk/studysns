<?php

//共通変数・関数ファイルを読込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　投稿詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//ログイン認証
require('auth.php');

/*===========================
 画面処理
===========================*/
$r_id = '';
$dbPostData = '';
$dbPostUserInfo = '';
$dbCommentList = '';
$dbPostGoodNum = '';
$edit_flg = '';

// 画面表示用データ取得
//----------------------------
//get送信がある場合
if(!empty($_GET['r_id'])){
    // 投稿IDのGETパラメータを取得
    $r_id = $_GET['r_id'];
    // DBから投稿を取得
    $dbPostData = getPostData($r_id);
    debug('取得したDBデータ：'.print_r($dbPostData, true));
    // 投稿者の情報
    $dbPostUserInfo = getUser($dbPostData['user_id']);
    // DBからコメントを取得
    $dbCommentList = getComment($r_id);
    // DBからいいねを取得
    $dbPostGoodNum = count(getGood($r_id));
    //自分の投稿なら編集フラグを立てる
    $edit_flg = ($dbPostData['user_id'] === $_SESSION['user_id']) ? true : false;

    //パラメータに不正な値が入っているかチェック
    if(empty($dbPostData)){
        error_log('エラー発生：指定ページに不正な値が入りました');
        header("Loction:home.php");
    }
    debug('取得したDBデータ：'.print_r($dbPostData,true));
}else{
    header("Location:home.php");
}
debug('画面処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '投稿詳細';
require('head.php');
?>


<body class="page-1colum">
    <!-- ヘッダー -->
    <?php require('header.php'); ?>
    <!-- メインコンテンツ -->
    <main id="contents">
       <div class="site-width">

       <!-- 投稿詳細 -->
        <section class="post recording-main" data-postid="<?php echo sanitize($r_id); ?>">
            <div class="record-img">
                <a href="userpage.php?u_id=<?php echo sanitize($dbPostUserInfo['id']); ?>">
                    <img class="user-icon" src="<?php echo showImg(sanitize($dbPostUserInfo['pic'])); ?>"></a>
            </div>
            <div class="post-wrap">
                <div class="post-head">
                    <a href="userpage.php?u_id=<?php echo sanitize($dbPostUserInfo['id']); ?>" class="username"><?php echo sanitize($dbPostUserInfo['username']); ?></a>
                    <time><?php echo date('Y/m/d H:i:s', strtotime(sanitize($dbPostData['create_date']))); ?></time>
                </div>
                <p>内容：<?php echo nl2br(sanitize($dbPostData['content'])); ?></p>
                <p>時間：<?php echo date("H:i", strtotime($dbPostData['study_time'])); ?></p>
                <p>コメント：<?php echo sanitize($dbPostData['study_com']); ?></p>
                <?php if(!empty($dbPostData['post_img'])) : ?>
                <div class="post-img-wrap">
                    <a href="http://loaclhost:8888/studysns/<?php echo sanitize($dbPostData['post_img']); ?>" data-lightbox="post-img">
                        <img class="post-img" src="<?php echo sanitize($dbPostData['post_img']); ?>">
                    </a>
                </div>
                <?php endif; ?>

                <div class="post-foot">
                    <div class="btn-box">
                    <div class="btn-comment">
                        <a class="link-nomal" href="comment.php?r_id=<?php echo $dbPostData['id']; ?>">
                        <i class="far fa-comment-alt"></i><?php
                            echo count($dbCommentList); ?>
                        </a>
                    </div>
                    <!-- $login_flgをscript.jsに渡すための記述 -->
                    <?php $login_flg = !empty($_SESSION['user_id']); ?>
                    <script>var login_flg = "<?php echo $login_flg ?>"</script>
                    <div class="btn-good <?php if(isLike($_SESSION['user_id'],$dbPostData['id'])) echo 'active'; ?>" >
                    <!-- 自分がいいねした投稿にはグッドのスタイルを保持する -->
                    <i class="fa-thumbs-up my-big
                    <?php
                          if(isLike($_SESSION['user_id'],$dbPostData['id'])){
                              echo ' active fas';
                          }else{
                              echo ' far';
                      }; ?>"></i><span><?php echo $dbPostGoodNum; ?></span>
                      </div>
                    </div>
                      <!-- 自分の投稿には編集アイコンを表示する -->
                    <?php if($edit_flg) { ?>
                    <div class="bth-edit">
                    <a href="post.php?r_id=<?php echo $r_id; ?>">
                    <i class="far fa-edit my-big js-post-edit btn-edit"></i>
                    </a>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </section>

        <!-- コメント一覧 -->
        <?php
        foreach($dbCommentList as $key => $val) : 
        $dbCommentUserId = $dbCommentList[$key]['user_id'];
        $dbCommentUserInfo = getUser($dbCommentUserId);
        ?>
        <section class="recording-comment">
            <div class="record-img">
                <a href="userpage.php?u_id=<?php echo sanitize($dbCommentUserInfo['id']); ?>">
                    <img class="user-icon" src="<?php echo showImg(sanitize($dbCommentUserInfo['pic'])); ?>">
                </a>
            </div>
            <div class="post-wrap">
                <div class="post-head">
                    <a href="userpage.php?u_id=<?php echo sanitize($dbCommentUserInfo['id']); ?>" class="username"><?php echo sanitize($dbCommentUserInfo['username']); ?></a>
                    <time><?php echo date('Y/m/d H:i:s',strtotime(sanitize($val['create_date']))); ?></time>
                </div>
                <p>
                    <?php echo nl2br(sanitize($val['comment'])); ?>
                </p>
            </div>
        </section>
        <?php 
            endforeach;
        ?>
        </div>
    </main>


<!-- フッター -->
<?php require('footer.php'); ?>