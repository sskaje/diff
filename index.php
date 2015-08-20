<?php header('content-type: text/html; charset=utf-8');?>
<html>
    <style type="text/css">
.hide {
    display: none;
}
.show {
    display: block;
}

</style>

<form action="" id="form" method="post" enctype="multipart/form-data">
    <label><input type="radio" name="is_upload" value="0" autocomplete="no" checked="checked" onclick="javascript: set_as_textarea()" />输入</label>
    <label><input type="radio" name="is_upload" value="1" autocomplete="no" onclick="javascript: set_as_upload()" />上传</label>

    <div id="input_textarea" class="show">
    <textarea name="left" cols="30" rows="10"><?php if (isset($_POST['left'])) echo $_POST['left'];?></textarea>
    ====
    <textarea name="right" cols="30" rows="10"><?php if (isset($_POST['right'])) echo $_POST['right'];?></textarea>
    </div>

    <div id="input_upload" class="hide">
    <label>左：<input type="file" name="left_file" autocomplete="no" /></label>
    <label>右：<input type="file" name="right_file" autocomplete="no" /></label>
    </div>

    <br>

    <label><input type="radio" name="type" value="0" autocomplete="no" />相同</label>
    <label><input type="radio" name="type" value="1" autocomplete="no" />不同</label>
    <label><input type="radio" name="type" value="2" autocomplete="no" />在左不在右</label>
    <label><input type="radio" name="type" value="3" autocomplete="no" />在右不在左</label>
    <br>
    <input type="submit">

</form>
<script type="text/javascript">
    function show(id) {
        document.getElementById(id).className = 'show';
    }
    function hide(id) {
        document.getElementById(id).className = 'hide';
    }

    function set_as_upload()
    {
        hide('input_textarea');
        show('input_upload');
        document.getElementById('form').setAttribute('enctype', 'multipart/form-data');
    }

    function set_as_textarea()
    {
        show('input_textarea');
        hide('input_upload');

        document.getElementById('form').removeAttribute('enctype');
    }



</script>

<?php


if (isset($_POST['is_upload']) && isset($_POST['type'])) {
    define('SAVE_DIRNAME', date('YmdHis'));

    require(__DIR__ . '/lib.php');

    $async = false;

    if ($_POST['is_upload']) {
        save_file($_FILES['left_file']['tmp_name'], 'input_left');
        save_file($_FILES['right_file']['tmp_name'], 'input_right');

        $async = true;


    } else {
        if (strlen($_POST['left']) > MAX_INPUT_SIZE || strlen($_POST['right']) > MAX_INPUT_SIZE) {
            save_file($_POST['left'], 'input_left', false);
            save_file($_POST['right'], 'input_right', false);

            $async = true;
        }
    }

    if ($async) {
        # 异步处理
        save_queue(SAVE_DIRNAME);
        header('Location: '. DATA_DIR . '/' . SAVE_DIRNAME);

    } else {
        $left = read_as_array($_POST['left'], false);
        $right = read_as_array($_POST['right'], false);

        $left = process_array($left);
        $right = process_array($right);

        $result = compare($_POST['type'], $left, $right);
        echo implode('<br />', $result);
    }


}
?>
</html>