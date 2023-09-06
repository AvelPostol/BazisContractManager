<?php
namespace BazisСM;
// подключение классов
require_once ('Workspace/Bazis/Controller.php');

use BazisСM\Workspace\Bazis\Controller;

  /**
  * Контроллер связи базиса и битрикс
  *
  *  1) отработка события перехода на стадию сделки в CRM
  *  2) поиск, старшего по номеру, договора в базисе
  *  3) увеличиваем значение договора на 1
  *  4) ищем в битрикс24 этот номер договора, если находим, то меняем на + 1 и проверяем еще раз
  *  5) после выполнения условий записываем сгенерированый номер в карточку сделки
  */

  /**
  * НОМЕР ДОГОВОРА
  * ----------------
  * ID ПОЛЬЗОВАТЕЛЯ + ID САЛОНА + СТАРШЕЕ ЧИСЛО
  */


  
class Main {

  public function __construct(Base $tools, Controller $BazisMain) {
    $this->tools = $tools;
    $this->BazisMain = $BazisMain;
  }

  public function Conroller($p){

  $bazis = $this->BazisMain->Check(['NumerGroupSalon' => $p['manager']['UF_']]);

  }
  
}
