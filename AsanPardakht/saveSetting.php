<html>
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
        <?php
            
        if (!isset($_POST['host'], $_POST['dbname'], $_POST['dbuser'], $_POST['gateway_id'], $_POST['gateway_api'], $_POST['username'], $_POST['password'], $_POST['password_confirm'])) {
            echo 'همه ی فیلدها باید پر شوند';
            die;
        }
        $setting = array(
            'host' => $_POST['host'],
            'dbname' => $_POST['dbname'],
            'gateway_id' => $_POST['gateway_id'],
            'gateway_api' => $_POST['gateway_api'],
            'adminName' => $_POST['username'],
            'adminPass' => $_POST['password'],
            'dbstring' => 'mysql:host=' . $_POST['host'] . ';dbname=' . $_POST['dbname'] . ';charset=utf8',
            'dbuser' => $_POST['dbuser'],
            'dbpass' => $_POST['dbpass'],
            'run' => '0',
        );

        $statement = 'CREATE TABLE IF NOT EXISTS `' . $_POST['dbname'] . '`.`bank10_payment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(256) NOT NULL,
  `email` VARCHAR(256) NOT NULL,
  `price` INT(11) NOT NULL,
  `status` TINYINT(4) NOT NULL DEFAULT "0",
  `time` INT(11) NOT NULL,
  `rand` CHAR(32) NULL DEFAULT NULL,
  `trans_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = MyISAM
AUTO_INCREMENT = 19
DEFAULT CHARACTER SET = utf8;';
        extract($setting);
        $db = new PDO($dbstring,$dbuser,$dbpass);
        $db->exec($statement);
        file_put_contents('data/Settings.php', "<?php\nreturn " . var_export($setting, true) . ';');
        Session_start();
        Session_destroy();
        header('Location: login.php');
        ?>

    </body></html>