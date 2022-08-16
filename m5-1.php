

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
    <style type="text/css">
    p {
        line-height:0;
    }

    .bottom_space {
        margin-bottom: 2em;
    }
    </style>
</head>
<body>
    <!--PHPによる新規投稿・削除・編集機能-->
    <?php
        //error_reporting(0);

        //編集時に編集前の情報を呼び出して代入する変数
        $editName = "";
        $editComment = "";
        $editNum = "";
        $editPassword = "";

        //DB設定
        $dbName = 'データベース名';
        $dbUser = 'ユーザ名';
        $dbPassword = 'パスワード';
        $pdo = new PDO($dbName, $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

        //存在しない場合、テーブル作成
        $sql = "CREATE TABLE IF NOT EXISTS m5table"
        ."("
        ."id INT AUTO_INCREMENT PRIMARY KEY,"
        ."drName char(32),"
        ."drComment TEXT,"
        ."drDate TEXT,"
        ."drPassword char(32)"
        .");";
        $stmt = $pdo->query($sql);

        //投稿+編集
        if(isset($_POST["postBtn"])) {
            //テーブルm5tableの有無を確認
            $sql = 'SHOW TABLES';
            $result = $pdo -> query($sql);
            foreach ($result as $row) {
                echo $row[0];
                echo "<br>";
            }
            echo "<hr>";

            //ブラウザの入力（名前,コメント,パスワード）を受信+日時を取得+編集時のキーを取得
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $date = date('Y/m/d H:i:s');
            $password = $_POST["password"];
            $editNumKey = $_POST["editNumKey"];

            //編集キーがない場合->新規投稿
            if(!empty($name) and !empty($comment) and !empty($password) and empty($editNumKey)) {
                //投稿処理 dr:date record
                $sql = $pdo -> prepare("INSERT INTO m5table (drName, drComment, drDate, drPassword)
                                        VALUES (:drName, :drComment, :drDate, :drPassword)");
                $sql -> bindParam(':drName', $drName, PDO::PARAM_STR);
                $sql -> bindParam(':drComment', $drComment, PDO::PARAM_STR);
                $sql -> bindParam(':drDate', $drDate, PDO::PARAM_STR);
                $sql -> bindParam(':drPassword', $drPassword, PDO::PARAM_STR);
                $drName = $name;
                $drComment = $comment;
                $drDate = $date;
                $drPassword = $password;
                $sql -> execute();

            //編集キーがある場合->編集
            } elseif(!empty($name) and !empty($comment) and !empty($password) and !empty($editNumKey)) {
                //編集処理
                $sql = 'UPDATE m5table SET drName=:drName,drComment=:drComment,drDate=:drDate,drPassword=:drPassword WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':drName', $drName, PDO::PARAM_STR);
                $stmt->bindParam(':drComment', $drComment, PDO::PARAM_STR);
                $stmt -> bindParam(':drDate', $drDate, PDO::PARAM_STR);
                $stmt -> bindParam(':drPassword', $drPassword, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $drName = $name;
                $drComment = $comment;
                $drDate = $date;
                $drPassword = $password;
                $id = $editNumKey;
                $stmt->execute();
            }

        //削除
        } elseif(isset($_POST["delBtn"])) {
            //ブラウザの入力（削除対象番号,パスワード）を受信
            $delNum = $_POST["delNum"];
            $delPassword = $_POST["delPassword"];

            if(!empty($delNum) and !empty($delPassword)) {
                //削除処理
                $id = $delNum;
                $password = $delPassword;
                $sql = 'delete from m5table where id=:id and drPassword=:password';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->execute();
            }

        //編集
        } elseif(isset($_POST["editBtn"])) {
            //ブラウザの入力（編集対象番号,パスワード）を受信
            $editNum = $_POST["editNum"];
            $editPassword = $_POST["editPassword"];

            if(!empty($editNum) and !empty($editPassword)) {
                //編集前の名前と投稿内容を呼び出す
                $id = $editNum;
                $password = $editPassword;
                $sql = 'SELECT * FROM m5table WHERE id=:id and drPassword=:password';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->execute();

                $results = $stmt->fetchAll();
                foreach ($results as $row) {
                    //HTMLの投稿フォームに呼び出したdrNameとdrCommentを代入
                    $editName = $row['drName'];
                    $editComment = $row['drComment'];
                }
            }
        }
    ?>

    <!--HTMLによる入力フォーマット-->
    <form action ="" method="post">
        <p>*：入力必須</p>
        <br>
        <p>名前*</p>
        <input type="text" name ="name" placeholder="山田太郎" value=<?php echo $editName;?>>
        <p>コメント*</p>
        <input type="text" name ="comment" placeholder="コメントです" value=<?php echo $editComment;?>>
        <input type="text" name ="editNumKey" value=<?php echo $editNum; ?>>
        <p>パスワード*</p>
        <input type="text" name ="password" placeholder="abc123">
        <br>
        <button type="submit" name="postBtn" class="bottom_space">送信</button>
        <br>

        <p>削除対象番号*</p>
        <input type="number" name ="delNum" placeholder="1">
        <p>パスワード*</p>
        <input type="text" name ="delPassword" placeholder="abc123">
        <br>
        <button type="submit" name="delBtn" class="bottom_space">削除</button>
        <br>

        <p>編集対象番号*</p>
        <input type="number" name ="editNum" placeholder="1">
        <p>パスワード*</p>
        <input type="text" name ="editPassword" placeholder="abc123">
        <br>
        <button type="submit" name="editBtn" class="bottom_space">編集</button>
    </form>

    <!--PHPによるブラウザ表示-->
    <?php
    echo "【ファイルの中身】<br>";

    //現在のデータレコードを表示
    $sql = 'SELECT * FROM m5table';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row) {
        echo $row['id'].' ';
        echo $row['drName'].' ';
        echo $row['drComment'].' ';
        echo $row['drDate'].'<br>';
        echo "<hr>";
    }
    ?>
</body>
</html>
