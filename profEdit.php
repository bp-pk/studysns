<?php

// 共通変数・関数ファイルを読込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' プロフィール変更ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');


//==============================
// 画面処理
//==============================
// DBからユーザーデータを取得
$dbFormData = getUser($_SESSION['user_id']);
// DBからカテゴリデータを取得
$dbCategoryData = getCategory();

debug('取得したユーザー情報：'.print_r($dbFormData,true));
debug('カテゴリデータ：'.print_r($dbCategoryData,true));

// POST送信された場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    debug('FILE情報：'.print_r($_FILES,true));

    // 変数にユーザー情報を代入
    $username = $_POST['username'];
    $email = $_POST['email'];
    $com = $_POST['com'];
    $job = $_POST['job_id'];
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic') : '';
    $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

    //DBの情報と入力情報が異なる場合にバリデーションを行う
    if($dbFormData['username'] !== $username){
        // ユーザー名の最大文字数チェック
        validMaxLen($username, 'username');
    }
    if($dbFormData['email'] !== $email){
        // emailの形式チェック
        validEmail($email, 'email');
        // emailの最大文字数チェック
        validMaxLen($email, 'email');
        if(empty($err_msg['email'])){
            // emailの重複チェック
            validEmailDup($email);
        }
        // emailの未入力チェック
        validRequired($email,'email');
    }
    if($dbFormData['com'] !== $com){
        // プロフィールの最大文字数チェック
        validMaxLen($com, 'com');
    }

    if(empty($err_msg)){
        debug('バリデーションOKです。');
        // 例外処理
        try{
            // DBへ接続
            $dbh = dbConnect();
            // SQL文作成
            $sql = 'UPDATE users SET username = :u_name, email = :email, com = :com, pic = :pic, job_id = :job WHERE id = :u_id';
            $data = array(':u_name' => $username, ':email' => $email, ':com' => $com, ':pic' => $pic, ':job' => $job, ':u_id' => $dbFormData['id']);
            // クエリ発行
            $stmt = queryPost($dbh, $sql, $data);
            // クエリ成功の場合
            if($stmt){
                debug('クエリ成功');
                debug('マイページへ遷移します');
                header('Location:home.php');
            }else{
                debug('クエリに失敗しました。');
                $err_msg['common'] = MSG08;
            }
        } catch (Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'プロフィール変更';
require('head.php');
?>


<body class="page-2colum">
<?php
require('header.php');
?>

<div id="contents" class="site-width">


    <h1 class="page-title">プロフィール変更</h1>
    <section class="mydata">
    <?php
    require('menu.php');
    ?>
    </section>
    <section id="main">
    <div class="form-container2">
        <form class="form" method="post" action="" enctype="multipart/form-data">
           <div class="area-msg">
               <?php
               if(!empty($err_msg['common'])) echo $err_msg['common'];
               ?>
           </div>
            <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            ユーザー名
            <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
            </label>
            <div class="area-msg">
                <?php
                if(!empty($err_msg['username'])) echo $err_msg['username'];
                ?>
            </div>
            <label class="<?php if(!empty($err_msg['com'])) echo 'err'; ?>">目標・プロフィール
            <textarea rows="5" cols="60" name="com"><?php echo getFormData('com'); ?></textarea>
            </label>
            <div class="area-msg">
                <?php if(!empty($err_msg['com'])) echo $err_msg['com']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['job_id'])) echo 'err' ?>">ご職業
            <select name="job_id">
                <option value="0" <?php if(!empty($err_msg['job_id']) == 0) { echo 'selected';} ?>>選択してください</option>
                <?php 
                foreach($dbCategoryData as $key => $val) {
                ?>
                <option value="<?php echo $val['id'] ?>" <?php if(getFormData('job_id') == $val['id']) { echo 'selected';} ?>>
                <?php echo $val['name']; ?>
                </option>
                <?php 
                }
                ?>
            </select>
            </label>
            <div class="area-msg">
                <?php
                if(!empty($err_msg['job_id'])) echo $err_msg['job_id'];
                ?>
            </div>
            <label class="<?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>">Email
            <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
            </label>
            <div class="area-msg">
                <?php
                if(!empty($err_msg['email'])) echo $err_msg['email'];
                ?>
            </div>
            <div style="overflow:hidden;">
                <div class="imgDrop-container">
                    プロフィール画像
                    <label class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="pic" class="input-file">
                        <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
                        ドラッグ＆ドロップ
                    </label>
                    <div class="area-msg">
                        <?php 
                        if(!empty($err_msg['pic'])) echo $err_msg['pic'];
                        ?>
                    </div>
                </div>
            </div>
            <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="変更する">
            </div>
        </form>
    </div>
    </section>
</div>


<?php
require('footer.php');
?>