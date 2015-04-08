<?php

/**
 * Created by PhpStorm.
 * User: Don
 * Date: 3/26/2015
 * Time: 4:10 PM
 */
class r
{
    private $location;
    private $database;
    private $dir;
    private $tmp_dir;

    function __construct()
    {
        global $plugin;
        global $database;

        $this->location = $plugin->config["losses.lime.survey.calculate"]["LSC_R_LOCATION"];
        $this->database = $database;
        $this->dir = str_replace('\\', '/', dirname(__FILE__)) . '/';
        $this->tmp_dir = $this->dir . "tmp/";
    }

    public function export_tables()
    {
        global $current_time;

        //$sheets = '"' . implode('","', func_get_args()) . '"';
        //$where_query = "WHERE `sheet` IN ($sheets)";

        $sheets = func_get_args();

        $random_file_name = md5($current_time . rand(0, 500));
        /*
                $result = $this->database->query("SELECT *
                                                  FROM `limesurvey`
                                                  $where_query
                                                  INTO OUTFILE '$this->tmp_dir$random_file_name.csv'
                                                  FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
                                                  LINES TERMINATED BY '\\n';
                                                  SELECT 'DONE'");
                                        //->fetchAll();

        */
        $result = $this->database->select('limesurvey', '*', [
            'sheet' => $sheets
        ]);

        $file_steam = fopen("$this->tmp_dir$random_file_name.csv", 'w');

        fputcsv($file_steam, ['id', 'sheet', 'type', 'key', 'value', 'user']);

        foreach ($result as $fields) {
            fputcsv($file_steam, $fields);
        }

        fclose($file_steam);

        return "$this->tmp_dir$random_file_name.csv";
    }

    public function run()
    {
        $arguments = func_get_args();
        $file_name = 'scripts/' . $arguments[0] . '.r';
        $r_arguments = '';

        for ($i = 1; $i < count($arguments); $i++) {
            $r_arguments .= escapeshellcmd($arguments[$i]);

            if ($i = count($arguments) - 1)
                break;

            $r_arguments .= ',';
        }

        exec("$this->location $file_name $r_arguments", $result);
        var_dump($result);

        print_r("$this->location $this->dir$file_name $r_arguments");
    }
}