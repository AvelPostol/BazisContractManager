<?php
namespace BazisСM\Event;

//require_once ('/head.php');
/*
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");*/

use Bitrix\Main\Loader;
use Bitrix\Main\Type;

/**
  * Получаем сделку -> проверяем условия, получаем ID ответственного пользователя
  * Получение ID салона пользователя  
  *
  */

  class CrmCEvent
  {
     /* private $tools;
      private $main;
  
      public function __construct(BazisСM\Workspace\Tools\Base $tools, BazisСM\Main $main) {
          $this->tools = $tools;
          $this->main = $main;
      }*/

      public static function writeLog($data) {
        $logFile = __DIR__.'/log_'.$data['meta'].time().'.txt';
        $formattedData = var_export($data['body'], true);
        file_put_contents($logFile, '<?php $array = ' . $formattedData . ';', FILE_APPEND);
    }

      public static function manager($data) {
          if (CModule::IncludeModule("main")) {
              $managerInfo = \Bitrix\Main\UserTable::GetList([
                  'select' => ['UF_BASIS_SALON'], // поле номер группы дизайнера
                  'filter' => ['ID' => $data['deal']['ASSIGNED_BY_ID']]
              ]);
              foreach ($managerInfo as $userfield) {
                  self::writeLog(['body' => ['body' => $userfield['ID']], 'meta' => 'users']);
                 // $this->tools->writeLog(['body' => ['body' => $userfield['ID']], 'meta' => 'users']);
                 // $this->main->main(['body' => ['manager' => $userfield, 'deal' => $data['deal']]]);
                  break;
              }
              if(!isset($userfield['ID'])){
               // $this->tools->writeLog(['body' => ['body' => ['не найдено совпадений по пользователям']], 'meta' => 'users']);
               self::writeLog(['body' => ['body' => ['не найдено совпадений по пользователям']], 'meta' => 'users']);
              }
          }
      }
      
      public static function Controller(&$arFields)
      {
        if(
            !isset($arFields['UF_CRM_1694018792723']) &&
            ($arFields['CATEGORY_ID'] == '11') && ($arFields['STAGE_ID'] == 'NEW')
            ||
            ($arFields['CATEGORY_ID'] == '12') && ($arFields['STAGE_ID'] == 'C12:PREPAYMENT_INVOIC')
          ){
            self::writeLog(['body' => ['body' => ['успешно']], 'meta' => 'crmEvent1']);
            /*$manager = $this->manager([
                'deal' => $arFields,
            ]);*/
          }
          else{
            self::writeLog(['body' => ['body' => $arFields], 'meta' => 'crmEvent2']);
            //$this->tools->writeLog(['body' => ['body' => ['не выполнено условие вхождения на генерацию договора, сделка не в ходит в целевую категорию']], 'meta' => 'crmEvent']);
          }
      }
  }

