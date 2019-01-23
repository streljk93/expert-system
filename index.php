<?php
define("START_TIME", microtime(true));

//--------------- Блок запуска автоматических конфигурационных функций ---------------
require_once ($_SERVER['DOCUMENT_ROOT']."/lib/config.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/lib/common.php");
config::main();

?>

<html>
<head>
    <title>EXPERT SYSTEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css" />
</head>
<body>
<?php

check_button_success();

list ($last_answers, $array_last_answers_2) = check_input_data();

// get common probability
$P_Ai = get_probability_all_data();

// run probability core
list ($data, $P_Qj_y, $P_Qj_n) = run_core();

$P_AiIB = get_P_AiIB($array_last_answers_2, $data, $P_Ai);

$question_id = get_question_for_view($data, $last_answers, $P_Ai, $P_AiIB, $P_Qj_y, $P_Qj_n);

// get data
$sql = "SELECT * FROM " . $GLOBALS['table_names']['questions'] .
" WHERE id='$question_id'";
$result = mysqli_query($GLOBALS['database']['connect'], $sql);
$result = mysqli_fetch_assoc($result);
$question_name = $result['name'];

?>
<div style="padding: 20px; margin: 50px; text-align: center; font-size: 50px">
    EXPERT SYSTEM
</div>
<div style="display: flex;">
    <div style="padding: 20px; flex: 2;">
        <form name="game" method="post" action="" class="ui segment piled">
          <?php if (isset($_REQUEST['question_number'])) echo 'Вопрос №' . ($_REQUEST['question_number']+1); else echo 'Вопрос №' . '1'; ?>
          <br />
          <b><?=$question_name?></b>
          <br /><br />

          <input type="hidden" name="last_answers" value="<?=$last_answers?>">

          <input type="hidden" name="question_id" value="<?=$question_id?>">

          <?php if (isset($_REQUEST['fail_datacore_id'])) { ?>
          <input type="hidden" name="fail_datacore_id" value="<?=$_REQUEST['fail_datacore_id']?>">
          <?php } ?>

          <input type="hidden" name="question_number" value="<?php if (isset($_REQUEST['question_number'])) echo ($_REQUEST['question_number']+1); else echo '1'; ?>">

          <input type="radio" name="answer" value="y" style="width:100px; height:25px; border: 1px solid black; background-color: green;"> <span style="color: green">Да, скорее всего да</span> <br />
          <br />
          <input type="radio" name="answer" value="n" style="width:100px; height:25px; border: 1px solid black; background-color: red;"> <span style="color: red">Нет, скорее всего нет</span> <br />
          <br />
          <input type="radio" name="answer" value="0" style="width:100px; height:25px; border: 1px solid black; background-color: gray;" checked> <span style="color: gray">Не знаю, не помню или нельзя ответить более-менее однозначно</span> <br />

          <br /><br />
          <input type="submit" name="game" value="Ok" class="ui green button" />
        </form>

        <div style="margin-top: 80px; font-size: 12px;">
            <div style="margin-bottom: 10px;">
                <div class="ui label">
                    <i aria-hidden="true" class="user outline icon"></i>
                    Руководитель
                    <div class="detail">
                        Асадуллаев Рустам Геннадьевич
                    </div>
                </div>
            </div>
            <div style="margin-bottom: 10px;">
                <div class="ui label">
                    <i aria-hidden="true" class="user icon"></i>
                    Разработчик
                    <div class="detail">
                        <a href="http://strel-jk.ru/" target="_blank" style="color: rgb(92, 92, 92);">
                            Стерльников Евгений Михайлович
                        </a>
                    </div>
                </div>
            </div>
            <div style="margin-bottom: 10px;">
                <div class="ui label">
                    <i aria-hidden="true" class="code icon"></i>
                    Версия
                    <div class="detail">
                        0.0.1
                    </div>
                </div>&nbsp; &nbsp;
                <div class="ui label">
                    <i aria-hidden="true" class="calendar icon"></i>
                    Дата
                    <div class="detail">
                        2019
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php

?>
    <div style="padding: 20px; flex: 4;">
        <table class="ui table fluid">
            <tr>
                <td>№</td>
                <td>Автобус</td>
                <td>%</td>
                <td>&nbsp;</td>
            </tr>
            <?php
                $i=1;
                $uslovie = '';
                foreach ($P_AiIB as $id => $value) {
                    $uslovie .= " id='$id' OR ";
                    if ($i == 20) break;
                    $i++;
                }
                $uslovie = preg_replace('{OR $}isu', '', $uslovie);
                $sql = "SELECT id, name FROM " . $GLOBALS['table_names']['datacores'] .
                " WHERE ($uslovie)";
                $result = mysqli_query($GLOBALS['database']['connect'], $sql);
                for ($arr = array(); $row = mysqli_fetch_assoc($result); $arr[$row['id']] = $row['name']);

                $i=1;
                foreach ($P_AiIB as $id => $value) {
            ?><tr><?php
                ?><td><?=$i?></td><?php
                ?><td><?=$arr[$id]?></td><?php
                ?><td><?=($value*100)?></td><?php
                ?><td><?php if ($i <= 15) {    // ($value*100) >= 30
                    ?><form name="sucess" method="post" action="" style="display:inline;">
                    <input type="hidden" name="last_answers" value="<?=$last_answers?>">
                    <input type="hidden" name="datacore_id" value="<?=$id?>">
                    <input type="submit" name="sucess" value="Угадан!" class="ui green button" />
                    </form>
                    <form name="game" method="post" action="" style="display:inline;">
                    <input type="hidden" name="last_answers" value="<?=$last_answers?>">
                    <input type="hidden" name="question_number" value="<?php if (isset($_REQUEST['question_number'])) echo ($_REQUEST['question_number']); else echo '1'; ?>">
                    <input type="hidden" name="fail_datacore_id" value="<?=$id?>">
                    <input type="submit" name="game" value="Не тот!" class="ui red button" />
                    </form>
                    <?php
                } ?>&nbsp;</td><?php
            ?></tr><?php
            if ($i == 20) break;
                $i++;
            }
            ?>
            </table>
        </div>
    </div>

<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>-->
</body>
</html>