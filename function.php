<?php

//===============================
// ログ
//===============================
// ログを取るか
ini_set('log_errors','on');
// ログの出力ファイルを指定
ini_set('error_log','php.log');

//===============================
// デバック
//===============================
// デバッグフラグ
$debug_flg = true;
// デバッグログ関数
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ:'.$str);
    }
}

//===============================
// セッション準備・セッション有効期限を伸ばす
//===============================
// セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp");
// ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ100分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
// ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime', 60*60*24*30);
// セッションを使う
session_start();
// 現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//===============================
// 画面表示処理開始ログ吐き出し関数
//===============================
function debugLogStart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
    debug('セッションID：'.session_id());
    debug('セッション変数の中身：'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ：'.time());
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
        debug('ログイン期限日時タイムスタンプ：'.($_SESSION['login_date'] + $_SESSION['login_limit']));
    }
}
//===============================
// 定数
//===============================
// エラーメッセージを定数に指定
define('MSG01','入力必須です');
define('MSG02','Emailの形式で入力してください');
define('MSG03','パスワード（再入力）があっていません');
define('MSG04','半角英数字のみご利用いただけます');
define('MSG05','6文字以上で入力してください');
define('MSG06','256文字以内で入力してください');
define('MSG07','エラーが発生しました、しばらく経ってからやり直してください');
define('MSG08','そのEmailはすでに登録されています');
define('MSG09','メールアドレスまたはパスワードが違います');
define('MSG10','古いパスワードが違います');
define('MSG11','古いパスワードと同じです');
define('MSG12','文字で入力してください');
define('MSG13','正しくありません');
define('MSG14','有効期限が切れています');
define('SUC01','パスワードを変更しました');
define('SUC02','プロフィールを変更しました');
define('SUC03','メールを送信しました');
define('SUC04','投稿が完了しました');
define('SUC05','削除しました');

//================================
// グローバル変数
//================================
// エラーメッセージ格納用の配列
$err_msg = array();

//================================
// バリデーション関数
//================================
// バリデーション関数（未入力チェック）
function validRequired($str, $key){
    if(empty($str)){
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}
// バリデーション関数（Email形式チェック）
function validEmail($str, $key){
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}
// バリデーション関数（Email重複チェック）
function validEmailDup($email){
    global $err_msg;
    // 例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty(array_shift($result))) {
            $err_msg['email'] = MSG08;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
// バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG03;
    }
}
// バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6){
    if(mb_strlen($str) < $min){
        global $err_msg;
        $err_msg[$key] = MSG05;
    }
}
// バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 255){
    if(mb_strlen($str) > $max){
        global $err_msg;
        $err_msg[$key] = MSG06;
    }
}
// バリデーション関数（半角チェック）
function validHalf($str, $key){
    if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}
// 固定長チェック
function validLength($str, $key, $len = 8){
    if(mb_strlen($str) !== $len){
        global $err_msg;
        $err_msg[$key] = ›len . MSG12;
    }
}
// パスワードチェック
function validPass($str, $key){
    // 半角英数字チェック
    validHalf($str, $key);
    // 最大文字数チェック
    validMaxLen($str, $key);
    // 最小文字数チェック
    validMinLen($str, $key);
}
// エラーメッセージ表示
function getErrMsg($key){
    global $err_msg;
    if(!empty($err_msg[$key])){
        return $err_msg[$key];
    }
}
//================================
// ログイン認証
//================================
function isLogin(){
  // ログインしている場合
  if( !empty($_SESSION['login_date']) ){
    debug('ログイン済みユーザーです');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです');

      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です');
      return true;
    }

  }else{
    debug('未ログインユーザーです');
    return false;
  }
}
//================================
// データベース
//================================
// DB接続関数
function dbConnect(){
    // DBへの接続準備
    $dsn = 'mysql:dbname=studylog;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
    $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    //PDOオブジェクト生成（DBへ接続）
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
}
// SQL実行関数
function queryPost($dbh, $sql, $data){
    // クエリ作成
    $stmt = $dbh->prepare($sql);
    // プレースホルダに値をセットし、SQL文を実行
    if(!$stmt->execute($data)){
        debug('クエリに失敗しました');
        $err_msg['common'] = MSG07;
        return 0;
    }
    debug('クエリ成功');
    return $stmt;
}
function getUser($u_id){
    debug('ユーザー情報を取得します');
    // 例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM users WHERE id= :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果のデータを１レコード返却
        if($stmt){
            debug('クエリ成功');
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            debug('クエリ失敗');
             return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
function getAllRecords($u_id){
  debug('全ての投稿を取得します');
  debug('ユーザーID：'.$u_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT r.content, r.study_time, r.study_com, u.pic, u.username FROM users As u INNER JOIN records AS r ON u.id = r.user_id';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
function getMyRecords($u_id){
  debug('自分の投稿を取得します');
  debug('ユーザーID：'.$u_id);
  //例外処理
  try {
        $dbh = dbConnect();
        $sql = 'SELECT r.id, r.content, r.study_time, r.study_com, r.user_id, r.create_date, u.pic, u.username FROM users As u INNER JOIN records AS r ON u.id = r.user_id WHERE r.user_id = :u_id AND r.delete_flg = 0 ORDER BY r.id DESC';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt) {
            $rst['data'] = $stmt->fetchAll();
            return $rst;
        }else{
            return false;
        }
    } catch(Exception $e) {
        error_log('エラーが発生しました');
    }
}
function isLike($u_id, $r_id){
  debug('お気に入り情報があるか確認します');
  debug('ユーザーID：'.$u_id);
  debug('商品ID：'.$r_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM likes WHERE record_id = :r_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':r_id' => $r_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt->rowCount()){
      debug('お気に入りです');
      return true;
    }else{
      debug('特に気に入ってません');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
function getGood($r_id){
    debug(' いいねを取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM likes WHERE record_id = :r_id';
        $data = array(':r_id' => $r_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
    }
}
function getPost($u_id, $r_id){
    debug('投稿情報を取得します');
    debug('ユーザーID：'.$u_id);
    debug('商品ID：'.$r_id);

    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM records WHERE user_id = :u_id AND id = :r_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id, ':r_id' => $r_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:'.$e->getMessage());
    }
}
function getPostData($r_id){
    debug('投稿データを取得します');
    debug('投稿ID：'.$r_id);
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM records WHERE id = :r_id AND delete_flg = 0';
        $data = array(':r_id' => $r_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:'.$e->getMessage());
    }
}
function getPostList(){
    debug('全ての投稿情報を取得します');
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM records WHERE delete_flg = 0 ORDER BY created_date DESC';
        $data = array();
        debug('SQL:'.$sql);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'. $e->getMessage());
    }
}
function getUserPostList($u_id){
    debug('自分の投稿情報を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM records WHERE user_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt) {
            return $stmt->fetchAll();
        }else{
            return false;
        }
    } catch(Exception $e) {
        error_log('エラーが発生しました');
    }
}
function getUserGoodPostList($u_id){
    debug(' 自分のいいねした投稿を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT r.id, r.content, r.study_time, r.study_com, r.user_id, r.create_date, u.pic, u.username, l.user_id FROM records As r INNER JOIN users AS u ON r.user_id = u.id INNER JOIN likes AS l ON r.id = l.record_id WHERE l.user_id = :u_id AND r.delete_flg = 0 ORDER BY r.id DESC';
        $data = array(':u_id' => $u_id);
        debug('SQL：'.$sql);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            $rst['data'] = $stmt->fetchAll();
            return $rst;
        }else{
            return false;
        }

    } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
    }
}
function getProductList($currentMinNum = 1, $span = 5){
  debug('投稿情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM records WHERE delete_flg = 0';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    // ページング用のSQL文作成
    $sql = 'SELECT r.id, r.content, r.study_time, r.study_com, r.user_id, r.create_date, u.pic, u.username FROM users As u INNER JOIN records AS r ON u.id = r.user_id WHERE r.delete_flg = 0 ORDER BY r.id DESC';

    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL：'.$sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
function getComment($r_id){
    debug('コメントを取得します');
    debug('投稿ID：'.$r_id);
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM comment WHERE record_id = :r_id AND delete_flg = 0';
        $data = array(':r_id' => $r_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//================================
// メール送信
//================================
function sendMail($from, $to, $subject, $comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        // メールを送信（送信結果はtrueかfalseで返ってくる）
        $result = mb_send_mail($to, $subject,$comment, "From:".$from);
        // 送信結果を判定
        if($result){
            debug('メールを送信しました');
        }else{
            debug('【エラー発生】メールの送信に失敗しました');
        }
    }
}
//================================
// その他
//================================
// サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}
// フォーム入力保持
function getFormData($str){
    global $dbFormData;
    // ユーザーデータがある場合
    if(!empty($dbFormData)){
        // フォームのエラーがある場合
        if(!empty($err_msg[$str])){
            // POSTがデータがある場合
            if(isset($_POST[$str])){ // 金額や郵便番号などフォームで数字や数値の０が入っている場合もあるので、issetを使うこと
                return $_POST[$str];
            }else{
                // ない場合（フォームにエラーがある＝POSTされているはずなので、まずあり得ないが）はDBの情報を表示
                return $dbFormData[$str];
            }
        }else{
            // POSTにデータがあり、DBの情報と違う場合
            if(isset($_POST[$str]) && $_POST[$str] !== $dbFormData[$str]){
                return $_POST[$str];
            }else{ // そもそも変更していない
                return $dbFormData[$str];
            }
        }
    }else{
        if(isset($_POST[$str])) {
        return $_POST[$str];
        }
    }
}
// sessionを一回取得
function getSessionFlash($key) {
    if(!empty($_SESSION[$key])) {
        $data = $_SESSION[$key];
        $_SESSION[$key] = '';
        return $data;
    }
}
// 認証キー生成
function makeRandkey($length = 8) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0,61)];
    }
    return $str;
}
// 画面処理
function uploadImg($file, $key){
    debug('画像アップロード処理開始');
    debug('FILE情報：'.print_r($file,true));
    
    if(isset($file['error']) && is_int($file['error'])) {
        try {
            switch ($file['error']) {
                case UPLOAD_ERR_OK: //ok
                    break;
                case UPLOAD_ERR_NO_FILE: //ファイル未選択の場合
                    throw new RuntimeException('ファイルが選択されていません');
                case UPLOAD_ERR_INI_SIZE: //php ini定義の最大サイズが超過した場合
                case UPLOAD_ERR_FORM_SIZE: //フォーム定義の最大サイズ超過した場合
                    throw new RuntimeException('ファイルサイズが大きすぎます');
                default: //その他の場合
                    throw new RuntimeException('その他のエラーが発生しました');
            }
            $type = @exif_imagetype($file['tmp_name']);
            if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                throw new RuntimeException('画像形式が未対応です');
            }
            
            $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
            if(!move_uploaded_file($file['tmp_name'], $path)) {
                throw new RuntimeException('ファイル保持時にエラーが発生しました');
            }
            // 保持したファイルパスのパーミッション（権限）を変更する
            chmod($path, 0644);
            
            debug('ファイルは正常にアップロードされました');
            debug('ファイルパス：'.$path);
            return $path;
            
        } catch (RuntimeException $e) {
            
            debug($e->getMessage());
            global $err_msg;
            $err_msg[$key] = $e->getMessage();
        }
    }
}
function getCategory(){
    debug('カテゴリー情報を取得します');
    //例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM category';
        $data = array();
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            // クエリ結果の全データを返却
            return $stmt->fetchAll();
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
function showImg($path){
    if(empty($path)){
        return 'img/no_icon.png';
    }else{
        return $path;
    }
}