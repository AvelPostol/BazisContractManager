<?php
namespace BazisСM\Workspace\Bazis;

error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
*
* EXAMPLE 
* ------------
* $database = new DataBase();
* $database->GetMaxContractNumber(['UserUID' => 'N69']);
*
*/


class DataBase {

    private $db;
    private $user;
    private $pass;
    private $mysqli;
    
    public function __construct() {
        $this->db = 'bazis';
        $this->user = 'python';
        $this->pass = 'Deep1993';
        $this->port = '30305';
        $this->mysqli = new \mysqli("10.178.200.13", $this->user, $this->pass, $this->db, $this->port);
        
        if ($this->mysqli->connect_error) {
            die("Ошибка подключения: " . $this->mysqli->connect_error);
        }
    }

    public function Get($p) {
        $query = $p['request'];

        $result = $this->mysqli->query($query);

        if (!$result) {
            echo "Ошибка выполнения запроса: (" . $this->mysqli->errno . ") " . $this->mysqli->error;
            return false;
        }

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $result->free();

        return $data;
    }

    public function GetMaxContractNumber($p) {
      // Создаем экземпляр класса DataBase
      $database = new DataBase();
      $prefix = $p['UserUID'];

      // Запрос для выбора менеджера
      $managerQuery = "SELECT name FROM bazis_managers WHERE article='$prefix'";
      $managerResult = $database->Get(['request' => $managerQuery]);

      if (!$managerResult) {
          echo "Ошибка при выборе менеджера";
          exit();
      }

      // Получаем имя менеджера
      $managerName = $managerResult[0]['name'];

      // Запрос для нахождения максимального числа
      $maxNumberQuery = "
      SELECT MAX(CAST(SUBSTRING(number, LENGTH('$prefix') + 1) AS UNSIGNED)) AS max_number
      FROM bazis_orders
      WHERE number LIKE '$prefix%'
        AND manager = '$managerName'
      ";
      $maxNumberResult = $database->Get(['request' => $maxNumberQuery]);

      if (!$maxNumberResult) {
          echo "Ошибка при нахождении максимального числа";
          exit();
      }

      $maxNumber = $maxNumberResult[0]['max_number'];

      return [
        'maxNumber' => $maxNumber,
        'UserUID' => $p['UserUID']
      ];

    }
}
