<?php
namespace BazisCM;

error_reporting(E_ERROR);
ini_set('display_errors', 1);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS',true);

$_SERVER["DOCUMENT_ROOT"] = "/mnt/data/bitrix";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");


require_once ('Workspace/Tools/CurlManager.php');
require_once ('Workspace/Tools/Base.php');
require_once ('Workspace/Bitrix/Common.php');


class ChatManager {
  private $CurlManager;

  public function __construct($metod) {
    $apiKey = '02f532d7052b495dadb503a5c54d09f4';
    $this->CurlManager = new \BazisCM\Workspace\Tools\CurlManager($apiKey, $metod);

    $currentDateTime = new \DateTime();
    $this->to = $currentDateTime->format('Y-m-d\TH:i:s.u\Z');
    $oneWeekAgo = new \DateTime('-1 hour'); //  $oneWeekAgo = new \DateTime('-1 hour');
    $this->from = $oneWeekAgo->format('Y-m-d\TH:i:s.u\Z');

    $this->Base = new \BazisCM\Workspace\Tools\Base();
    $this->BitrixCommon = new \BazisCM\Workspace\Bitrix\Common();
  }

  // запрос на получение заказов с фильтром, получаем самые новые за некоторый период
  public function getOrders(){
    print_r(['from' => $this->from,'to' => $this->to]);
    $response = $this->CurlManager->Get(['from' => $this->from,'to' => $this->to, 'CheckContentName' => 'orders', 'ContentPars' => true]);
    print_r(['$response' => $response]);
    return $response;
  }

  // отдельный запрос на получение информации о конкретном заказе
  public function getOrderInfo($p){
    $response = $this->CurlManager->Get(['CheckContentName' => 'order', 'ContentPars' => false]);
    return $response;
  }

  // хелпер для поиска соответсвий стадий и статусов в битрикс и базис
  public function findValueInArray($inputValue, $array) {
    if (array_key_exists($inputValue, $array)) {
        return $array[$inputValue];
    } else {
        return null;
    }
  }


  // основная функция для обновления информации о клиенте в карточку клиента из данных базиса
  public function getClient($p){
    
    $client = $p['client']['data']['client'];
    $number = $p['client']['data']['number'];

    $BitrixCommon = $this->BitrixCommon->Check($p);

    if(!$BitrixCommon['CONTACT_ID'] || $BitrixCommon['CONTACT_ID'] == 0 || !isset($BitrixCommon['CONTACT_ID'])){
      // создание нового контакта исключено, так как они создаются вручную/при формировании сделки
      print_r(['Warning: сделка с номер договора не найдена']);
    }
    else{
      //проверяем нужно ли обновлять контакт
      $ContactGet = $this->BitrixCommon->ContactGet_check(['filter' => ['ID' => $BitrixCommon['CONTACT_ID']], 'check' => $client]);

      // обновляем контакт
      if($ContactGet == 'update'){
        $upd = $this->BitrixCommon->ContactUpdate(['ID' => $BitrixCommon['CONTACT_ID'], 'updateField' => $client]);
        if($upd){
          print_r(['обновили контакт']);
        }
        else{
          print_r(['Warning: не получилось обновить контакт']);
        }
      }
      else{
        print_r(['не требуется обновлять контакт']);
      }

    }

  }

  // основная функция для обновления информации о стадиях из данных базиса
  public function getState($p){

    $BitrixCommon = $this->BitrixCommon->Check($p);

    $ArrStageCategory_1 = [
      '02. Договор заключен' => 'C12:UC_CM88W2',
      '01. Повторная встреча' => 'C12:UC_YFU93G',
      '03. Договор не заключен' => 'C12:UC_MN8HQK',
    ];

    $ArrStageCategory_2 = [
      '02. Договор заключен' => 'C11:EXECUTING',
      '01. Повторная встреча' => 'C11:UC_2V1ONJ',
      '03. Договор не заключен' => 'C11:UC_6N89KF',
    ];

    $ArrStageCategory_allert = ['02. Договор заключен', '01. Повторная встреча', '03. Договор не заключен'];

    if(in_array($p['stateBl']['currentState'],$ArrStageCategory_allert)){
      if(($BitrixCommon['CATEGORY_ID'] == 12) || ($BitrixCommon['CATEGORY_ID'] == 11)){
        if($BitrixCommon['CATEGORY_ID'] == 12){
          $result = $this->findValueInArray($p['stateBl']['currentState'], $ArrStageCategory_1);
        }
        if($BitrixCommon['CATEGORY_ID'] == 11){
          $result = $this->findValueInArray($p['stateBl']['currentState'], $ArrStageCategory_2);
        }
        
  
        if($BitrixCommon['STAGE_ID'] !== $currentState){
  
          print_r(['стадия начальная' => $BitrixCommon['STAGE_ID']]);
          print_r(['стадия новая' => $currentState]);
  
          print_r(['требуется изменить стадию']);
          if($BitrixCommon['ID']){
            $UpdateCrmDeal = $this->BitrixCommon->UpdateCrmDeal(['ID' => $BitrixCommon['ID'], 'updateFields' => ['STAGE_ID' => $result]]);
  
            if($UpdateCrmDeal){
              print_r(['стадия изменена успешно']);
            }
            else{
              print_r(['Warning: ошибка изменении стадии']);
            }
          }
          else{
            print_r(['сделка не найдена']);
          }
        }
      }
      print_r(['сделка вне воронок' => $BitrixCommon['CATEGORY_ID']]);
    }
    else{
      print_r(['нет совпадений по стадиям воронок']);
    }
  
  }

  // основная функция для обновления информации о элементах списка/документах из данных базиса по номеру договора
  public function getFiles($p){

    $IblokItems = $this->BitrixCommon->GetIblokList($p);

    // если найден соответствующий элемент списка
    if($IblokItems){
      print_r(['уже есть такой документ']);
    
      // получаем файлы из базиса
      foreach($p['files'] as $keyFl => $file) {
        $metod = '/api-orders-exchange-public/orders/' . $p['orderId'] . '/file';
        $chatManager = new ChatManager($metod);
        $files[$file['name']] = $this->CurlManager->Get(['filename' => $file['name'],'CheckContentName' => 'file', 'ContentPars' => false]);
      }

      //проверяем нужно ли обновлять элемент списка/документа
      $IblokItemCheckBazis = $this->BitrixCommon->IblokItemCheck($p,$IblokItems,$files);

      print_r('------');
      print_r($IblokItemCheckBazis['upd']); // обновляемые позиции элемента
      print_r('-----');

      if(isset($IblokItemCheckBazis['orderIsChecked'])){
        print_r(['попытка обновить документ']);
        $CheckUpdate = $this->BitrixCommon->IblokItemUpdate($IblokItemCheckBazis['orderIsChecked'], $IblokItems, $IblokItemCheckBazis['updFiles']);
        if(!$CheckUpdate){
          print_r(['Fatall: не получилось обновить документ']);
          $this->Base->writeLog(['body' => ['TryCreateForNumerBazis' => $p['orderId']], 'meta' => 'Except_UpdateIblockElem']);
        }
        else{
          print_r(['получилось обновить документ']);
        }
      }
      else{
        print_r(['документ не нуждается в обновлении']);
      }
      
    }
    else{



      foreach($p['files'] as $file) {
        $metod = '/api-orders-exchange-public/orders/' . $p['orderId'] . '/file';
        $chatManager = new ChatManager($metod);
        $files[$file['name']] = $this->CurlManager->Get(['filename' => $file['name'],'CheckContentName' => 'file', 'ContentPars' => false]);
      }

      $CheckAdd = $this->BitrixCommon->AdaptForAddIblockElem($p, $files);

      $CheckAdd = $this->BitrixCommon->IblokItemAdd($CheckAdd['ord']['data'], $CheckAdd['files']);
      
      if(!$CheckAdd){
        print_r(['Fatall: не получилось сгенерировать документ']);
        $this->Base->writeLog(['body' => ['TryCreateForNumerBazis' => $p['orderId']], 'meta' => 'Except_CreateIblockElem']);
      }
      else{
        print_r(['создан новый элемент списка']);
      }

      
      //$this->Base->writeLog(['body' => ['TryCreateForNumerBazis' => $p['orderId']], 'meta' => 'Except_CreateFiles_nonCrmDeal_widthNumberInBazis']);
    }

    //далее в списках добавляем или обновляем файлы
  }

  public function Controller() {

    $this->Base->writeLog(['body' => ['tryStart' => '***'], 'meta' => 'check']);

    $metod = '/api-orders-exchange-public/orders';

    // для начала получим все заказы
    $chatManager = new ChatManager($metod);
    $orders = $chatManager->getOrders();

    //получаем инфо о каждом заказе
    foreach($orders['data'] as $key => $order){
 
      $metod = '/api-orders-exchange-public/orders/' . $order['id'];
      $chatManager = new ChatManager($metod);

      $OrderInfo = $chatManager->getOrderInfo($order);
    
        /*$chatManager->getClient(['client' => $OrderInfo]);*/
        $chatManager->getState(['stateBl' => $OrderInfo['data']]);
        /*$chatManager->getFiles(['files' => $OrderInfo['data']['files'], 'orderId' => $order['id'], 'data' => $OrderInfo['data'] ]);*/

    }
  }
}


$metod = '/api-orders-exchange-public/orders';

// для начала получим все заказы
$chatManager = new ChatManager($metod);
$orders = $chatManager->Controller();