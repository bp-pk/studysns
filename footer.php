<footer id="footer">
      <div id="footerWrop">
       Copyright 2019 STUDY! All Rights Reserved.
    </div>
</footer>

<!-- jQuery -->
<script
    src="https://code.jquery.com/jquery-3.4.0.min.js"
    integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="
    crossorigin="anonymous">
</script>

<script>
    $(function(){

        // footer固定
        var $ftr = $('#footer');
        if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
          $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;' });
        }

        // メッセージ表示
        var $jsShowMsg = $('#js-show-msg');
        var msg = $jsShowMsg.text();
        if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
            $jsShowMsg.slideToggle('slow');
            setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
        }

        //画面ライブプレビュー
        var $dropArea = $('.area-drop');
        var $fileInput = $('.input-file');
        $dropArea.on('dragover', function(e){
            e.stopPropagation();
            e.preventDefault();
            $(this).css('border', '3px #ccc dashed');
        });
        $dropArea.on('dragleave', function(e){
            e.stopPropagation();
            e.preventDefault();
            $(this).css('border', 'none');
        });
        $fileInput.on('change', function(e){
            $dropArea.css('border', 'none');
            var file = this.files[0],
                $img = $(this).siblings('.prev-img'), 
                fileReader = new FileReader(); 
            //読み込みが完了して際のイベントハンドラ。imgのsrcにデータをセット
            fileReader.onload = function(event) {
                // 読み込んだデータをimgに設定
                $img.attr('src', event.target.result).show();
            };
            //画像読み込み
            fileReader.readAsDataURL(file);
        });

        // お気に入り登録・削除
        var $good = $('.btn-good'), //いいねボタンセレクタ
            goodPostId; //投稿ID
        $good.on('click',function(e){
            e.stopPropagation();
            var $this = $(this);
            //カスタム属性（postid）に格納された投稿ID取得
            goodPostId = $this.parents('.post').data('postid');
            $.ajax({
                type: 'POST',
                url: 'ajaxLikes.php', //post送信を受けとるphpファイル
                data: { productId: goodPostId} //{キー:投稿ID}
            }).done(function(data){
                console.log('Ajax Success');

                // いいねの総数を表示
                $this.children('span').html(data);
                // いいね取り消しのスタイル
                $this.children('i').toggleClass('far'); //空洞いいね
                // いいね押した時のスタイル
                $this.children('i').toggleClass('fas'); //塗りつぶしいいね
                $this.children('i').toggleClass('active');
                $this.toggleClass('active');
            }).fail(function(msg) {
                console.log('Ajax Error');
            });
        });

        //文字カウンター
        $('#js-countup').on('keyup', function(e) {
            var count = $(this).val().replace(/\n/g, '').length; //改行は文字数に含めない
            $('#js-countup-view').html(count);
        });

        //投稿一覧から投稿詳細ページへ
        var $post = $('.js-post-click') || null;
        if($post !== null) {
            $post.on('click', function(){
                var postId = $(this).data('postid');
                window.location.href = 'postDetail.php?r_id=' + postId;
            });
        } else {
            window.location.href = 'home.php';
        }

        // SPメニュー
        $('.js-toggle-sp-menu').on('click', function(){
            $(this).toggleClass('active');
            $('.js-toggle-sp-menu-target').toggleClass('active');
        });

        // SPメニュー クリック時
        $('.menu-link').on('click', function(){
            $('.js-toggle-sp-menu').toggleClass('active');
            $('.js-toggle-sp-menu-target').toggleClass('active');
        });
    });
</script>
</body>
</html>