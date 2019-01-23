<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 2019-01-23
 * Time: 08:40
 */

// include common model
require_once ($_SERVER['DOCUMENT_ROOT']."/models/index.php");

if (!function_exists('get_P_AiIB')) {
    function get_P_AiIB ($array_last_answers_2, $data, $P_Ai) {
        if (isset($_REQUEST['game'])) {
            foreach ($P_Ai as $id_datacore=>$value) {
                $P_BIAi[$id_datacore] = 1;

                foreach ($array_last_answers_2 as $id_question=>$answer_letter) {
                    if ($answer_letter) {
                        if (isset($data[$id_datacore][$id_question])) {
                            if (!($data[$id_datacore][$id_question]['a_yes'] + $data[$id_datacore][$id_question]['a_no'])) $mnojitel = 0.5;
                            else {
                                if ($answer_letter == 'y') $mnojitel = ($data[$id_datacore][$id_question]['a_yes']+1) / ($data[$id_datacore][$id_question]['a_yes'] + $data[$id_datacore][$id_question]['a_no'] + 2);
                                elseif ($answer_letter == 'n') $mnojitel = ($data[$id_datacore][$id_question]['a_no']+1) / ($data[$id_datacore][$id_question]['a_yes'] + $data[$id_datacore][$id_question]['a_no'] + 2);
                            }
                        }
                        else $mnojitel = 0.5;
                        $P_BIAi[$id_datacore] *= $mnojitel;
                    }
                }
            }
        }
        else {
            foreach ($P_Ai as $id=>$value) $P_BIAi[$id]=0.5;
            $last_answers = '';
        }
        foreach ($P_Ai as $id=>$value) {
            $P_BIAi_x_P_Ai[$id] = $P_BIAi[$id] * $P_Ai[$id];
        }
        if (isset($_REQUEST['fail_datacore_id'])) $P_BIAi_x_P_Ai[$_REQUEST['fail_datacore_id']] = 0;
        $P_B = 0;
        foreach ($P_BIAi_x_P_Ai as $id=>$value) {
            $P_B += $value;
        }
        foreach ($P_BIAi_x_P_Ai as $id=>$value) {
            $P_AiIB[$id] = $value / $P_B;
        }
        arsort($P_AiIB);

        return $P_AiIB;
    }
}

if (!function_exists('check_button_success')) {
    function check_button_success () {
        if (isset($_REQUEST['sucess'])) {
            $sql = "UPDATE " . $GLOBALS['table_names']['datacores'] .
                " SET games=(games+1) " .
                " WHERE id='".$_REQUEST['datacore_id']."'";
            mysqli_query($GLOBALS['database']['connect'], $sql) or die (mysqli_error($GLOBALS['database']['connect']) . '<br />' . $sql);
            preg_match_all('{([0-9]+)[=]([0yn])}isu', $_REQUEST['last_answers'], $ar_answers);
            foreach ($ar_answers[1] as $key=>$id) $ar_answers_2[$id] = $ar_answers[2][$key];
            unset($ar_answers);
            foreach ($ar_answers_2 as $question_id=>$letter) {
                if ($letter) {
                    $sql = "SELECT * FROM " . $GLOBALS['table_names']['answers'] .
                        " WHERE datacores_id='".$_REQUEST['datacore_id']."' AND questions_id='".$question_id."'";
                    $result = mysqli_query($GLOBALS['database']['connect'], $sql);
                    if (mysqli_num_rows($result)) {
                        $result = mysqli_fetch_assoc($result);
                        $a_yes = $result['a_yes'];
                        $a_no = $result['a_no'];
                        if ($letter == 'y') $a_yes++;
                        elseif ($letter == 'n') $a_no++;
                        $sql = "UPDATE " . $GLOBALS['table_names']['answers'] .
                            " SET a_yes='$a_yes', a_no='$a_no' " .
                            " WHERE datacores_id='".$_REQUEST['datacore_id']."' AND questions_id='".$question_id."'";
                        mysqli_query($GLOBALS['database']['connect'], $sql) or die (mysqli_error($GLOBALS['database']['connect']) . '<br />' . $sql);
                    }
                    else {
                        $a_yes = 0;
                        $a_no = 0;
                        if ($letter == 'y') $a_yes++;
                        elseif ($letter == 'n') $a_no++;
                        $sql = "INSERT into " . $GLOBALS['table_names']['answers'] .
                            '(
                                `id`, 
                                `datacores_id`, 
                                `questions_id`, 
                                `a_yes`, 
                                `a_no` 
                                ) 
                                VALUES ('.
                                                    "
                                null,
                                '".$_REQUEST['datacore_id']."',
                                '$question_id',
                                '$a_yes',
                                '$a_no'
                                );";

                        mysqli_query($GLOBALS['database']['connect'], $sql) or die(mysqli_error($GLOBALS['database']['connect'])."<br />\r\n".$sql);
                    }
                }
            }
        }
    }
}

if (!function_exists('check_input_data')) {
    function check_input_data () {
        if (isset($_REQUEST['game'])) {
            // push new answer in string
            $last_answers = $_REQUEST['last_answers'];
            if (isset($_REQUEST['question_id'])) {
                if ($last_answers) $last_answers .= '_' . $_REQUEST['question_id'] . '=' . $_REQUEST['answer'];
                else $last_answers .= $_REQUEST['question_id'] . '=' . $_REQUEST['answer'];
            }
            // ecrypt $last_answers in array last_answers
            preg_match_all('{([0-9]+)[=]([0yn])}isu', $last_answers, $array_last_answers); //print_r($array_last_answers);
            foreach ($array_last_answers[1] as $key=>$id) $array_last_answers_2[$id] = $array_last_answers[2][$key]; //print_r($array_last_answers_2);
            unset($array_last_answers);
        }
        else {
            $last_answers = '';
            $array_last_answers_2 = array();
        }

        return [
            $last_answers,
            $array_last_answers_2,
        ];
    }
}

if (!function_exists('get_probability_all_data')) {
    function get_probability_all_data () {

        // select data
        $data_list = get_data_list_without_name();

        // search for all data $P_Ai - probability only periodicity step
        $sum_games = 0;
        foreach ($data_list as $id=>$games) $sum_games += $games;
        foreach ($data_list as $id=>$games) $P_Ai[$id] = 1/count($data_list);

        return $P_Ai;
    }
}

if (!function_exists('run_core')) {
    function run_core () {

        $data = get_answer_list_on_question_list();

        // вычисляем мультипликаторы для всех вопросов с их возможными ответами
        foreach ($data as $d_f_id=>$array_work) {
            foreach ($array_work as $d_q_id=>$yes_no) {
                if (!isset($multiplicators[$d_q_id]['a_yes'])) $multiplicators[$d_q_id]['a_yes'] = 0;
                if (!isset($multiplicators[$d_q_id]['a_no'])) $multiplicators[$d_q_id]['a_no'] = 0;
                if (!isset($multiplicators[$d_q_id]['q_count'])) $multiplicators[$d_q_id]['q_count'] = 0;
                @$multiplicators[$d_q_id]['a_yes'] += (($yes_no['a_yes']) / ($yes_no['a_yes']+$yes_no['a_no']));
                @$multiplicators[$d_q_id]['a_no'] += (($yes_no['a_no']) / ($yes_no['a_yes']+$yes_no['a_no']));
                @$multiplicators[$d_q_id]['a_yes_ef'] += (($yes_no['a_yes']+1) / ($yes_no['a_yes']+$yes_no['a_no']+1));
                @$multiplicators[$d_q_id]['a_no_ef'] += (($yes_no['a_no']+1) / ($yes_no['a_yes']+$yes_no['a_no']+1));
                @$multiplicators[$d_q_id]['q_count'] += 1;
            }
        }

            // дополнительньо ищем $P_Qj_y и $P_Qj_n - взвешенная совокупная вероятность ответа да/нет на каждый вопрос
        foreach ($multiplicators as $d_q_id=>$yes_no) {
            if (!isset($P_Qj_y[$d_q_id])) $P_Qj_y[$d_q_id] = 0;
            $P_Qj_y[$d_q_id] += (($yes_no['a_yes']) / ($yes_no['a_yes'] + $yes_no['a_no']));
            if (!isset($P_Qj_n[$d_q_id])) $P_Qj_n[$d_q_id] = 0;
            $P_Qj_n[$d_q_id] += (($yes_no['a_no']) / ($yes_no['a_yes'] + $yes_no['a_no']));

            $P_Qj_y[$d_q_id] = $P_Qj_y[$d_q_id] / ($P_Qj_y[$d_q_id]+$P_Qj_n[$d_q_id]);
            $P_Qj_n[$d_q_id] = $P_Qj_n[$d_q_id] / ($P_Qj_y[$d_q_id]+$P_Qj_n[$d_q_id]);
        }
        unset($multiplicators);

        return [$data, $P_Qj_y, $P_Qj_n];
    }
}

if (!function_exists('get_question_for_view')) {
    function get_question_for_view ($data, $last_answers, $P_Ai, $P_AiIB, $P_Qj_y, $P_Qj_n) {
        if (!isset($_REQUEST['game'])) { // в хорошем алгоритме случайность основывается на БД и с проверкой
            // проверка, существует ли этот вопрос в БД
            while (!isset($isset_question)) {
                $question_id = mt_rand(1, 79);
                $sql = "SELECT id FROM " . $GLOBALS['table_names']['questions'] . " WHERE id='$question_id' LIMIT 1";
                $result = mysqli_query($GLOBALS['database']['connect'], $sql);
                if (mysqli_num_rows($result)) $isset_question = true;
            }
        }
// Иначе считаем изменения вероятности топового datacore при разных ответах для всех вопросов - с максимальным значением - наш
        else {
            // Берем в массив список всех вопросов
            $sql = "SELECT id FROM " . $GLOBALS['table_names']['questions'];
            $result = mysqli_query($GLOBALS['database']['connect'], $sql);
            for ($questions=array(); $row=mysqli_fetch_assoc($result); $questions[$row['id']] = 0);

            preg_match_all('{([0-9]+)[=]([0yn])}isu', $last_answers, $array_last_answers); //print_r($array_last_answers);
            foreach ($array_last_answers[1] as $key=>$id) $array_last_answers_2[$id] = $array_last_answers[2][$key]; //print_r($array_last_answers_2);
            unset($array_last_answers);

            foreach ($questions as $id_question=>$nouse) {
                // сначала проверяется, задавался ли этот вопрос в прошлом
                if (isset($array_last_answers_2[$id_question])) $questions[$id_question] = 0;
                else {
                    // массив вероятностей для ответа да и суммарная энтропия для всех объектов
                    $array_last_answers_2_work = $array_last_answers_2;
                    $array_last_answers_2_work[$id_question] = 'y';
                    $P_AiIB_work = get_P_AiIB($array_last_answers_2_work, $data, $P_Ai);
                    $E_y = 0;
                    foreach ($P_AiIB_work as $id_datacore => $P) {
                        $E_y += abs($P_AiIB[$id_datacore] - $P); //echo abs($P_AiIB[$id_datacore] - $P) . ' ';
                    }
                    //echo '<br>';
                    $array_last_answers_2_work = $array_last_answers_2;
                    $array_last_answers_2_work[$id_question] = 'n';
                    $P_AiIB_work = get_P_AiIB($array_last_answers_2_work, $data, $P_Ai);
                    $E_n = 0;
                    foreach ($P_AiIB_work as $id_datacore => $P) {
                        $E_n += abs($P_AiIB[$id_datacore] - $P); //echo abs($P_AiIB[$id_datacore] - $P) . ' ';
                    }
                    $questions[$id_question] = $E_y * $P_Qj_y[$id_question] + $E_n * $P_Qj_n[$id_question];
                }
            }
            arsort($questions);

            foreach ($questions as $id => $value) {
                $question_id = $id;
                break;
            }
            unset ($questions);
            unset ($answers);
        }

        return $question_id;
    }
}