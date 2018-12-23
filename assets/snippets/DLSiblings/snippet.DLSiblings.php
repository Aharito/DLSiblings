<?php
/**
 * DLSiblings
 * Вывод соседних ресурсов с шаблонизацией (множественная кольцевая перелинковка)
 * @category snippet
 *
 * @version   2.0
 * CMS version MODx Evo 1.4.7
 * @lastupdate 21/12/2017
 *
 * @author Aharito https://aharito.ru
 * на основе DLPrevNext @author Agel_Nash <Agel_Nash@xaker.ru>
 * 
 * @internal    @installset base, sample 
 *
 * @param int docid Если задан, то соседи выводятся для этого документа, default id текущего документа
 * @param int &prevQty Кол-во соседей-предшественников, default 2
 * @param int &nextQty Кол-во соседей-последователей, default 2
 *
 * @NOTE: Остальные параметры - как у DocLister
 *
 * @example
 * [[DLSiblings? &idType=`parents` &parents=`[*parent*]` &ownerTPL=`@CODE:<ul>[+dl.wrap+]</ul>` &tpl=`@CODE:<li><a href="[+url+]">[+title+]</a></li>` &tvList=`diam,vid` &tvSortType=`UNSIGNED, UNSIGNED` &orderBy=`diam ASC` &addWhereList=`c.template = 7` &filters=`AND(tv:vid:=:стальной)` ]]
 */

if ( ! defined('MODX_BASE_PATH')) { die('HACK???'); }

include_once(MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');
$DLDir = MODX_BASE_PATH . 'assets/snippets/DocLister/';
require_once($DLDir . "/lib/jsonHelper.class.php");

// Получаем параметры, заданные при вызове сниппета  DLSiblings
$params = is_array($modx->Event->params) ? $modx->Event->params : array();

// Параметры
$prevQty = \APIhelpers::getkey($params, 'prevQty', 2);
$nextQty = \APIhelpers::getkey($params, 'nextQty', 2);

$out = "";
$siblings = array();
$ID = isset($docid) ? $docid : $modx->documentIdentifier;
// мержим параметры для API-вызова
$paramsAPI = array_merge( $params, array('api' => 'id', 'display' => '0') );

// Этот вызов ДокЛистера выводит JSON строку с ИД ресурсов, отсортированную и отфильтрованную в соответствии с параметрами, заданными в вызове сниппета
$json = $modx->runSnippet("DocLister", $paramsAPI);
$children = jsonHelper::jsonDecode($json, array('assoc' => true)); // Перевод JSON  в массив
$children = is_array($children) ? $children : array(); // Тут проверка, что вернулся массив
$ids = array_keys($children); // Индексный массив ID в нашей выборке
$curIndex = array_search($ID, $ids); // Находим текущий индекс (индекс текущего ID)
$count = count($ids); // Длина массива $ids
$lastIndex = $count - 1; // Последний индекс массива $ids

if(($count - 1) <= $prevQty + $nextQty) { // Если длина выборки (за исключением текущего элемента) меньше нужного кол-ва
    // То просто будем выводить все элементы выборки, кроме текущего
    $outArr = $ids;
    unset($outArr[$curIndex]); 
} else { // Иначе ищем соседей
    for($i=1; $i<=$prevQty; $i++) {
        /**
        * Для Prev
        * Если "перескока" в хвост нет, то индекс вычисляется как $curIndex - $i
        * Если из начала $ids перескочили в его хвост, то индекс считаем как $count + $curIndex - $i
        */
        $index = ($curIndex - $i >= 0) ? $curIndex - $i : $count + $curIndex - $i;
        // Формируем массив $siblings с теми же индексами и значениями, как у $ids
        $siblings[$index] = $ids[$index];
    }
    for($i=1; $i<=$nextQty; $i++) {
        /**
        * Для Next
        * Если "перескока" на начало нет, то индекс вычисляется как $curIndex + $i
        * Если из хвоста $ids перескочили на его начало, то индекс считаем как $i - ($lastIndex - $curIndex) - 1
        */
        $index = ($curIndex + $i <= $lastIndex) ? $curIndex + $i : $i - ($lastIndex - $curIndex) - 1;
        // Дополняем массив $siblings с теми же индексами и значениями, как у $ids
        $siblings[$index] = $ids[$index];
    }
    /**
    * В итоге $siblings - это индексный массив с пропусками индексов, значения - ID ресурсов
    * Выглядит примерно так: Array ( [6] => 114, [0] => 18, [5] => 109, [1] => 95 )
    */
    
    /**
    * Будем выводить все элементы $siblings
    */
    $outArr = $siblings;
}

$documents = implode(",", $outArr);    
$paramsRender = array_merge($params, array("idType" => "documents", "documents" => $documents)); // Параметры для рендеринга
unset($paramsRender["parents"]); // На всякий случай удаляем параметр parents
$out = $modx->runSnippet("DocLister", $paramsRender);

return $out;
