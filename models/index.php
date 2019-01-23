<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 2019-01-23
 * Time: 09:07
 */

if (!function_exists('get_data_list_without_name')) {
    function get_data_list_without_name () {
        $sql = "SELECT id, games FROM " . $GLOBALS['table_names']['datacores'];
        $result = mysqli_query($GLOBALS['database']['connect'], $sql);
        for ($data_list_without_name=array(); $row=mysqli_fetch_assoc($result); $data_list_without_name[$row['id']]=$row['games']);

        return $data_list_without_name;
    }
}

if (!function_exists('get_answer_list_on_question_list')) {
    function get_answer_list_on_question_list () {
        $sql = "SELECT * FROM " . $GLOBALS['table_names']['answers'];
        $result = mysqli_query($GLOBALS['database']['connect'], $sql);
        for ($data = array(); $row = mysqli_fetch_assoc($result); 1) {
            $data[$row['datacores_id']][$row['questions_id']]['a_yes'] = $row['a_yes'];
            $data[$row['datacores_id']][$row['questions_id']]['a_no'] = $row['a_no'];
        }

        return $data;
    }
}