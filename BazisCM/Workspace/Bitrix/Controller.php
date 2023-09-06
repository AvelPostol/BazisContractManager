<?php

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

  class Controller
  {
      public static function CheckBitrix(&$arFields)
      {
        $numer = $p['maxNumber']++;
        $ContractUID = $p['UserUID'].$p['maxNumber'];
      
        if (\Bitrix\Main\Loader::IncludeModule("crm")) {
          $DealInfo = \Bitrix\Crm\DealTable::GetList([
              'select' => ['UF_CRM_1694018792723'], 
              'filter' => ['UF_CRM_1694018792723' => $ContractUID] 
          ]);
      
          $firstRecord = $DealInfo->fetch();
      
          if ($firstRecord !== false) {
              $numer++;
              $ContractUID = $p['UserUID'].$numer;
          } 
          else{
              $ContractUID;
          }
          
        }

        return $ContractUID;
      }
  }


