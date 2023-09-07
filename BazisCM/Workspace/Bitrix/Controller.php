<?php
namespace BazisCM\Workspace\Bitrix;

  class Controller
  {
      public static function Check($p)
      {
        $maxNumberBazis = $p['maxNumber'];
        $numer = ++$p['maxNumber'];
        $ContractUID = $p['ManagerUserField'].$p['maxNumber'];
      
        if (\Bitrix\Main\Loader::IncludeModule("crm")) {
          $DealInfo = \Bitrix\Crm\DealTable::GetList([
              'select' => ['ID'], 
              'filter' => ['UF_CRM_1694018792723' => $ContractUID] 
          ]);
      
          $firstRecord = $DealInfo->fetch();
      
          if ($firstRecord !== false) {
              $numer++;
              $ContractUID = $p['ManagerUserField'].$numer;
          } 
          else{
              $ContractUID;
          }
          
        }

        return $ContractUID;
      }

      public function AddContractNumber($p){
/*
        if (\Bitrix\Main\Loader::IncludeModule("crm")) {
          $DealInfo = \Bitrix\Crm\DealTable::Update(
            $p['deal']['ID'], 
            ['UF_CRM_1694018792723' => $p['NewNumberContract']]
          );
        }
*/
      }

      
  }


