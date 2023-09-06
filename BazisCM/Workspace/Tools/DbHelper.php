<?php
namespace BazisСM\Workspace\Tools;

class DbHelper {

    
    public function adaptDB($p){

        $dataBaseInstance = new DataBase();
        $mysqli = $dataBaseInstance->mysqli;

        // Получаем список столбцов и их типов из базы данных
        $columns = $this->getTableColumns($p['table_name'], $mysqli);
        
        foreach($p['data'] as $datakey => $data){
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $columns)) {
                    $columnType = $columns[$key];
                    $convertedValue = $this->convertValue($value, $columnType);
                    $p['data'][$datakey][$key] = $convertedValue;
                }
                else{
                    unset($p['data']s[$datakey][$key]);
                }
            }
        }

        
        
        return $datas;
    }

    // Получаем список столбцов и их типов из базы данных
    private function getTableColumns($tableName, $mysqli) {
        $columns = array();
        $query = "DESCRIBE $tableName";
        $result = $mysqli->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = $row['Type'];
            }
        } else {
            echo "Ошибка выполнения запроса: " . $mysqli->error;
            exit();
        }
        return $columns;
    }


    // Преобразование значения в соответствии с типом столбца
    private function convertValue($value, $columnType) {
        if (strpos($columnType, 'timestamp') !== false) {
            // Преобразование timestamp
            $dateTime = new DateTime($value);
            return $dateTime->format('Y-m-d H:i:s');
        } elseif (strpos($columnType, 'int') !== false) {
            // Преобразование к целому числу
            return (int)$value;
        } elseif (strpos($columnType, 'float') !== false) {
            // Преобразование к числу с плавающей точкой
            return (float)$value;
        }
        // Возвращаем значение без преобразования для других типов
        return $value;
    }

    // Проверка наличия столбца в таблице
    private function columnExistsInTable($tableName, $columnName, $mysqli) {
        $query = "SHOW COLUMNS FROM $tableName LIKE '$columnName'";
        $result = $mysqli->query($query);
        return $result && $result->num_rows > 0;
    }
}


