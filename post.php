<?php
//共通変数・関数ファイルを読込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　投稿ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//=====================================
// 画面処理
//=====================================
// 画像表示用データ取得
//=====================================
// GETデータを格納
$r_id =(!empty($_GET['r_id'])) ? $_GET['r_id'] : '';
// DBから投稿データを取得
$dbFormData = (!empty($r_id)) ? getPost($_SESSION['user_id'], $r_id) : '';
//新規投稿画面か編集画面か判別フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
debug('投稿ID：'.$r_id);
debug('フォーム用DBデータ:'.print_r($dbFormData,true));
// パラメータ改ざんチェック
//---------------------------
// GETパラメータが改ざんされている場合トップページへ遷移
if(!empty($r_id) && empty($dbFormData)){
    debug('GETパラメータの投稿IDが違います。トップページへ遷移');
    header("Location:home.php");
}
// DBから最新の投稿情報を取得
// --------------------------
$newpost_flg = ''; //初投稿フラグ
if(!empty(getMyRecords($_SESSION['user_id']))){
    $newpost_flg = true;
    //my投稿情報取得
    $userposts = getMyRecords($_SESSION['user_id']);
    debug('my投稿:'.print_r($userposts,true));
}else{
    $newpost_flg = true;
    debug('まだ投稿がありません');
}

if(!empty($_POST)){
    debug('POST送信があります。');
    
    if(empty($_POST['delete'])){
        //変数にユーザー情報を代入
        $content = $_POST['content'];
        $study_time = $_POST['study_time'];
        $study_com = $_POST['study_com'];
        
        //内容の最大文字数チェック
        validMaxLen($content, 'content');
        
        //コメントの最大文字数チェック
        validMaxLen($study_com, 'study_com');

        //未入力チェック
        validRequired($content, 'content');
        validRequired($study_time, 'study_time');
        
        if(empty($err_msg)){
            debug('バリデーションOKです。');
            
            //例外処理
            try {
                //DBへ接続
                $dbh = dbConnect();
                //SQL文作成
                if($edit_flg){
                    debug('DB更新です。');
                    $sql = 'UPDATE records SET content = :content, study_time = :study_time, study_com = :study_com WHERE user_id = :u_id AND id = :r_id';
                    $data = array(':content' => $content, ':study_time' => $study_time, ':study_com' => $study_com, ':u_id' => $_SESSION['user_id'], ':r_id' => $r_id);
                }elseif($newpost_flg){
                    $sql = 'INSERT INTO records (content, study_time, study_com, user_id, create_date) VALUES (:content, :study_time, :study_com, :u_id, :create_date)';
                    $data = array(':content' => $content, ':study_time' => $study_time, ':study_com' => $study_com, ':u_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s'));
                }else{
                    header("Location: home.php");
                }
                debug('SQL:'.$sql);
                debug('流し込みデータ：'.print_r($data,true));
                //クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
                print_r($stmt);

                //クエリ成功の場合
                if($stmt){
                    $_SESSION['msg_success'] = SUC04;
                    debug('マイページへ遷移します');
                    header("Location:home.php");
                }else{
                    debug('クエリ失敗しました');
                    $err_msg['common'] = MSG07;
                }
            } catch (Exception $e) {
                error_log('エラー発生:' . $e->getMessage());
                $err_msg['common'] = MSG07;
            }
        } 
    }else{
        debug('投稿を削除します');
        try {
            $dbh = dbConnect();
            $sql = 'UPDATE records SET delete_flg = 1 WHERE user_id = :u_id AND id = :r_id';
            $data = array(':u_id' => $_SESSION['user_id'], ':r_id' => $r_id);
            debug('SQL:'.$sql);
                debug('流し込みデータ：'.print_r($data,true));
            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            if($stmt){
                debug('削除しました');
                $_SESSION['msg_success'] = SUC05;
                header("Location:home.php");
            }
        } catch(Exception $e) {
            error_log('エラー発生：'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = (empty($_GET['r_id'])) ? '投稿' : '編集';
require('head.php');
?>


<body class="page-1colum">
<?php
require('header.php');
?>

<div id="contents" class="site-width">

<section id="main">
    <div class="form-container">
    <form class="form" method="post" action="" enctype="multipart/form-data">
       <h2 class="title">
       <?php echo empty($_GET['r_id']) ? '投稿する' : '編集する'; ?></h2>
        <div class="area-msg">
            <?php
            if(!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
        </div>
        <label class="<?php if(!empty($err_msg['content'])) echo 'err'; ?>">内容：
            <input type="text" name="content" value="<?php echo getFormData('content'); ?>">
        </label>
        <div class="area-msg">
            <?php
            if(!empty($err_msg['content'])) echo $err_msg['content'];
            ?>
        </div>
        <label class="<?php if(!empty($err_msg['study_time'])) echo 'err'; ?>">時間：
            <input type="time" name="study_time" value="<?php echo getFormData('study_time'); ?>">
        </label>
        <div class="area-msg">
            <?php
            if(!empty($err_msg['study_time'])) echo $err_msg['study_time'];
            ?>
        </div>
        <label>コメント：
            <textarea id="js-countup" rows="5" cols="60" name="study_com"><?php echo getFormData('study_com'); ?></textarea>
        </label>
        <p class="counter-text"><span id="js-countup-view">0</span>/255</p>
        <div class="area-msg">
            <?php
            if(!empty($err_msg['study_com'])) echo $err_msg['study_com'];
            ?>
        </div>
        <div class="btn-container">
           <?php if($edit_flg) echo '<input type="submit" name="delete" class="px-16 btn-gray btn-mid mr-24" value="削除">'; ?>
            <input type="submit" name="submit" class="btn-mid" value="投稿する">
        </div>
    </form>
    </div>
</section>
</div>


<!-- フッター -->
<?php require('footer.php'); ?>