<?php

/*=====================================
  画面処理
 =====================================*/
$dbPostList = '';
$dbPostUserid = '';
$dbPostUserInfo = '';
$dbPostGoodNum = '';
$edit_flg = '';
$currentPageNum = '';
/*-------------------------------------
  投稿一覧の表示処理
-------------------------------------*/
if(isset($u_id)) {
    if(empty($_GET['good'])){
        //DBからユーザーIDと一致した投稿情報を取得
        debug('ユーザーの投稿を取得');
        $dbPostList = getMyRecords($u_id);
    }else{
        //いいねした投稿を取得
        debug('ユーザーのいいねした投稿を取得');
        $dbPostList = getUserGoodPostList($u_id);
    }
}else{
    // カレントページ
    $currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページめ
    // パラメータに不正な値が入っているかチェック
    if(!is_int((int)$currentPageNum)){
      error_log('エラー発生:指定ページに不正な値が入りました');
      header("Location:home.php"); //トップページへ
}
    //DBから全ての投稿情報を取得
    debug('全ての投稿から5つ投稿を取得');
    // 表示件数
    $listSpan = 5;
    // 現在の表示レコード先頭を算出
    $currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1-1)*20 = 0 、 ２ページ目なら(2-1)*20 = 20
    // DBから商品データを取得
    $dbPostList = getProductList($currentMinNum);
}
if(!empty($dbPostList)){
    // 投稿情報がある場合は表示
    foreach($dbPostList['data'] as $key => $val):
    $dbPostUserId = $val['user_id'];
    $dbPostUserInfo = getUser($dbPostUserId);
    // コメント数取得
    $dbPostCommentNum = count(getComment($val['id']));
    // いいね数取得
    $dbPostGoodNum = count(getGood($val['id']));
    // 自分の投稿には編集フラグを立てる
    $edit_flg = (!empty($_SESSION['user_id']) && $val['user_id'] === $_SESSION['user_id']) ? true : false;
?>
<article class="recording post js-post-click" data-postid="<?php echo sanitize($val['id']); ?>">
    <div class="record-img">
        <a href="userpage.php?u_id=<?php echo sanitize($dbPostUserId); ?>">
            <img src="<?php echo showImg(sanitize($val['pic'])); ?>">
        </a>
    </div>
    <div class="post-wrap">
        <div class="post-head">
            <a href="userpage.php?u_id=<?php echo sanitize($dbPostUserId); ?>" class="username"><?php echo sanitize($val['username']); ?></a>
            <time><?php echo date('Y/m/d H:i:s', strtotime(sanitize($val['create_date']))); ?></time>
        </div>
        <p>内容：<?php echo sanitize($val['content']) ?></p>
        <p>時間：<?php echo date("H:i", strtotime($val['study_time'])); ?></p>
        <p>コメント：<?php echo $val['study_com']; ?></p>

        <div class="post-foot">
            <div class="btn-box">
            <div class="btn-comment">
                <a class="link-nomal" href="comment.php?r_id=<?php echo $val['id']; ?>">
                    <i class="far fa-comment-alt my-big"></i><span><?php echo $dbPostCommentNum; ?></span>
                </a>
            </div>
            <!-- $login_flgをscript.jsに渡すための記述 -->
            <?php $login_flg = !empty($_SESSION['user_id']); ?>
            <script>var login_flg = "<?php echo $login_flg ?>"</script>
            <div class="btn-good <?php if(isLike(isset($_SESSION['user_id']), $val['user_id'])) echo 'active'; ?>">
            <!-- 自分がいいねした投稿にはハートのスタイルを常に保持する -->
            <i class="fa-thumbs-up my-big
            <?php
                      if(!empty($_SESSION['user_id'])){
                          if(isLike($_SESSION['user_id'],$val['id'])){
                              echo ' active fas';
                          }else{
                              echo ' far';
                          }
                      }else{
                          echo ' far';
                      }; ?>"></i><span><?php echo $dbPostGoodNum; ?></span>
            </div>
            </div>
            <!-- 自分の投稿には編集アイコンを表示する -->
            <?php if($edit_flg){ ?>
            <div class="btn-edit">
            <a href="post.php?r_id=<?php echo sanitize($val['id']); ?>">
                <i class="far fa-edit my-big js-post-edit btn-edit"></i>
            </a>
            </div>
            <?php } ?>
        </div>
    </div>
</article>
<?php
	endforeach;
}else{
?>
	<?php
		if(isset($u_id) && !empty($_GET['good'])){
	?>
		<p style="text-align: center; margin-top: 64px;">いいねがありません</p>
	<?php
		}else{
	?>
		<p style="text-align: center; margin-top: 64px;">まだ投稿はありません</p>
	<?php
		}
}