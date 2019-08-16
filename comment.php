<?php

//共通変数・関数ファイルを読込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　コメントページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//ログイン認証
require('auth.php');
/*================================
 画面処理
================================*/
// GETデータを格納
$r_id = (!empty($_GET['r_id'])) ? $_GET['r_id'] : '';
if(!empty($_POST)){
    debug('post情報'.print_r($_POST,true));
    $comment = $_POST['comment'];
    //未入力チェック
    validRequired($comment, 'comment');
    //最大文字数チェック
    validMaxLen($comment, 'comment');
    if(empty($err_msg)){
        debug('バリデーションOK');
        
        try {
            $dbh = dbConnect();
            $sql = 'INSERT INTO comment (record_id, user_id, comment, create_date) VALUES(:r_id, :u_id, :comment, :date)';
            $data = array(':r_id' => $r_id, ':u_id' => $_SESSION['user_id'], ':comment' => $comment, ':date' => date('Y-m-d H:i:s'));
            //クエリ実行
            $stmt = queryPost($dbh,$sql,$data);
            if($stmt){
                header("Location:postDetail.php?r_id=".$r_id);
            }
        } catch(Exception $e) {
            error_log('エラー発生：'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'コメント送信';
require('head.php');
?>

<body class="page-1colum">
    <!-- ヘッダー -->
    <?php require('header.php'); ?>
    
    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
        <div class="site-wrap">
            <form action="" method="post" class="form">
                <h2 class="title">コメントする</h2>
                <div class="form-wrap">

                    <label>
                        <textarea id="js-countup" name="comment" cols=63 rows=20><?php echo getFormData('comment'); ?></textarea>
                    </label>
                    <p class="counter-text"><span id="js-countup-view">0</span>/255</p>
                    <div class="area-msg">
                        <?php
                        if(!empty($err_msg['comment'])) echo $err_msg['comment'];
                        ?>
                    </div>
                    <div class="err_msg">
                        <input type="submit" class="btn-primary" value="送信">
                    </div>
                </div>
            </form> 
        </div>
    </div>
    
<!-- フッター -->
<?php require('footer.php'); ?>
