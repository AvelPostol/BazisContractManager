<?php
namespace BazisCM;
error_reporting(E_ERROR);
ini_set('display_errors', 1);

  /**
  * Контроллер связи базиса и битрикс
  *
  *  1) отработка события перехода на стадию сделки в CRM
  *  -----------------------------------------------------
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

  public function __construct() {
    $this->BazisController = new Workspace\Bazis\Controller;
    $this->BitrixController = new Workspace\Bitrix\Controller;
    $this->Base = new \BazisCM\Workspace\Tools\Base();
  }

  public function Conroller($p){

  $BazisMaxNumer = $this->BazisController->Check(['ManagerUserField' => $p['manager']['UF_BASIS_SALON']]);

  if($BazisMaxNumer){
    $NewNumberContract = $this->BitrixController->Check(['ManagerUserField' => $p['manager']['UF_BASIS_SALON'], 'maxNumber' => $BazisMaxNumer['maxNumber']]);
  } else{
    return null;
  }
  
  $this->BitrixController->AddContractNumber(['manager' => $p['manager'], 'NewNumberContract' => $NewNumberContract, 'deal' => $p['deal']]);

  }
  
}
