<?php
namespace BazisCM;
// подключение классов
require_once ('head.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Type;

/**
  * Получаем сделку -> проверяем условия, получаем ID ответственного пользователя
  * Получение ID салона пользователя  
  *
  */

  class CrmCEvent
  {
      private $Base;
      private $Main;
  
      public function __construct() {
        $this->Main = new \BazisCM\Main();
        $this->Base = new \BazisCM\Workspace\Tools\Base();
      }

      public function GetManager($data) {
          if (\Bitrix\Main\Loader::IncludeModule("main")) {
              $ManagerID = $data['deal']['ASSIGNED_BY_ID'];
              $managerInfo = \Bitrix\Main\UserTable::GetList([
                  'select' => ['UF_BASIS_SALON'], // поле номер группы дизайнера
                  'filter' => ['ID' => $ManagerID] 
              ]);
              $this->Base->writeLog(['body' => $ManagerID, 'meta' => 'user']);
              foreach ($managerInfo as $fields) {
                  $this->Main->Conroller(['manager' => $fields, 'deal' => $data['deal']]);
                  break;
              }
              if(!isset($fields['UF_BASIS_SALON'])){
                $this->Base->writeLog(['body' => 'не найдено совпадений по пользователям', 'meta' => 'usersExcept']);
              }
          }
      }

      
      public function Check($arFields){
        if (
            ($arFields['UF_CRM_1694018792723'] == NULL) && (($arFields['CATEGORY_ID'] == '11') && ($arFields['STAGE_ID'] == 'C11:NEW') || ($arFields['CATEGORY_ID'] == '12') && ($arFields['STAGE_ID'] == 'C12:PREPAYMENT_INVOIC'))
        ) {
          $instance = new CrmCEvent();
          $manager = $instance->GetManager([
            'deal' => $arFields,
            ]);
        } else {
          $this->Base->writeLog(['body' => $arFields, 'meta' => 'crmEvent2']);
        }

      }

      public static function Controller(&$arFields)
      {

        $instance = new CrmCEvent(); 
        $manager = $instance->Check($arFields);
          
      }
  }
