<?php

// 共通変数・関数ファイルを読込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' 退会ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');


//=================================
// 画面処理
//=================================
// postに送信されていた場合
if(!empty($_POST)) {
    debug('POST送信があります。');
    //例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL作成
        $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
        $sql2 = 'UPDATE recodes SET delete_flg = 1 WHERE user_id = :us_id';
        $sql3 = 'UPDATE likes SET delete_flg = 1 WHERE user_id = :us_id';
        // データ流し込み
        $data = array(':us_id' => $_SESSION['user_id']);
        // クエリ発行
        $stmt1 = queryPost($dbh, $sql1, $data);
        $stmt2 = queryPost($dbh, $sql2, $data);
        $stmt3 = queryPost($dbh, $sql3, $data);
        
        // クエリ実行成功の場合（最悪userテーブルのみ削除成功していいれば良しとする）
        if($stmt1) {
            // セッション削除
            session_destroy();
            debug('セッション変数の中身：'.print_r($_SESSION,true));
            debug('トップページへ遷移します。');
            header('Loction:index.php');
        } else {
            debug('クエリが失敗しました。');
            $err_msg['common'] = MSG07;
        }
        
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '退会';
require('head.php')
?>

<body class="page-signup page-1colum">
<?php
require('header.php');
?>

<!-- メイン -->
<div id="contents" class="site-width">
    <style>
      .form .btn{
        float: none;
      }
      .form{
        text-align: center;
      }
    </style>
    <!-- Main -->
    <section id="main">
        <div class="form-container">
            <form class="form" method="post" action="">
                <h2 class="title">退会</h2>
                <div class="area-msg">
                <?php
                if(!empty($err_msg['common'])) echo $err_msg['common'];
                ?>
                </div>
                <input type="submit" class="btn btn-mid" value="退会する">
            </form>
        </div>
    </section>
</div>

<?php
require('footer.php');
?>