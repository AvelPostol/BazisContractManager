<?php
namespace BazisСM;

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

require_once ('Main.php');
require_once ('Workspace/Tools/Base.php');


use BazisСM\Workspace\Tools\Base;

use Bitrix\Main\Loader;
use Bitrix\Main\Type;

/**
  * Получаем сделку -> проверяем условия, получаем ID ответственного пользователя
  * Получение ID салона пользователя  
  *
  */

  class CrmCEvent
  {
      private $tools;
      private $main;
  
      public function __construct(Main $main, Base $tools) {
        $this->main = $main;
        $this->tools = $tools;
      }

      public function GetManager($data) {
          if (\Bitrix\Main\Loader::IncludeModule("main")) {
              $managerInfo = \Bitrix\Main\UserTable::GetList([
                  'select' => ['UF_BASIS_SALON'], // поле номер группы дизайнера
                  'filter' => ['ID' => 31] // $data['deal']['ASSIGNED_BY_ID']]
              ]);
              foreach ($managerInfo as $userfield) {
                  $this->tools->writeLog(['body' => ['body' => $userfield['ID']], 'meta' => 'users']);
                  $this->main->Conroller(['body' => ['manager' => $userfield, 'deal' => $data['deal']]]);
                  break;
              }
              if(!isset($userfield['UF_BASIS_SALON'])){
                $this->tools->writeLog(['body' => ['body' => ['не найдено совпадений по пользователям']], 'meta' => 'users']);
              }
          }
      }

      public static function Controller(&$arFields)
      {
           $instance = new CrmCEvent(new Main(), new Base()); // Создаем экземпляр класса CrmCEvent
          if (
              !isset($arFields['UF_CRM_1694018792723']) &&
              ($arFields['CATEGORY_ID'] == '11') && ($arFields['STAGE_ID'] == 'NEW')
              ||
              ($arFields['CATEGORY_ID'] == '12') && ($arFields['STAGE_ID'] == 'C12:PREPAYMENT_INVOIC')
          ) {
               $manager = $instance->GetManager([
                  'deal' => $arFields,
              ]);
          } else {
              $instance->writeLog(['body' => ['body' => $arFields], 'meta' => 'crmEvent2']);
          }
      }
  }

