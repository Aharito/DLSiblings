<?php
/**
 * DLSiblings
 * вывод соседних ресурсов с шаблонизацией (множественная кольцевая перелинковка)
 * @category snippet
 *
 * @version   0.2
 * CMS version MODx Evo 7.1.6
 * @lastupdate 03/04/2017
 *
 * @author Aharito http://aharito.ru на основе DLPrevNext @author Agel_Nash <Agel_Nash@xaker.ru>
 *
 * @params &idType, &parents, &documents, &ignoreEmpty - как в DocLister
 * @param int &Qty Кол-во соседей с каждой стороны, имеет приоритет над &prevQty и &nextQty, default 2
 * @param int &prevQty Кол-во соседей-предшественников. Приоритет меньше $Qty, default 2
 * @param int &nextQty Кол-во соседей-последователей. Приоритет меньше $Qty, default 2
 * @param string &ownerTPL Шаблон-обертка, должен содержать плейсхолдер [+wrap+], default null (вывод не оборачивается в ownerTPL)
 * @params string &tpl, &tplOdd и &tplEven, &tplIdN, &tplFirst и &tplLast Шаблоны элемента как в DocLister в порядке увеличения приоритета
 * @param string &noneTPL Шаблон с информацией, что ничего нет как в DocLister,  default null (пусто).
 * @param (0|1) &noneWrapOuter Как в DocLister, оборачивать ли шаблон noneTPL в обёртку ownerTPL.
 * Параметр &noneWrapOuter имеет смысл, только если ничего не нашлось и при этом задан ownerTPL.
 * @param string &prepare Как в DocLister.
 *
 * @NOTE: Другие шаблоны из набора DocLister не используются.
 * @NOTE: Остальные параметры - как у DocLister
 *
 * @example
 *       [[DLSiblings? &idType=`parents` &parents=`[*parent*]` &tpl=`@CODE:<a href="[+url+]">[+tv_h1+]</a><br>` &Qty=`2` &tvList=`h1` ]]
**/

/** Для теста:
[!DLSiblings?
    &idType=`parents`
    &parents=`[*parent*]`
    &ownerTPL=`@CODE:<div>[+wrap+]</div><hr>`
    &tpl=`@CODE:<p>tpl</p>`
    //tplEven=`@CODE:<p>tplEven</p>`
    //tplOdd=`@CODE:<p>tplOdd</p>`
    &tplId1=`@CODE:<p>tplId1</p>`
    //tplId4=`@CODE:<p>tplId4</p>`
    //tplFirst=`@CODE:<p>tplFirst</p>`
    &tplLast=`@CODE:<p>tplLast</p>`
    &prevQty=`2`
    &nextQty=`2`
    &orderBy=`if(pub_date=0,createdon,pub_date) DESC`
!]
**/


if ( ! defined('MODX_BASE_PATH')) { die('HACK???'); }

// Получаем параметры, заданные при вызове сниппета  DLSiblings
$params = is_array($modx->Event->params) ? $modx->Event->params : array();

/**
 * Задаем дефолтные значения новым DL-стилем :)
 *
 * Некешир. сниппет на некешир. ресурсе
 * Mem : 3.5 mb, MySQL: 0.0190 s, 17 request(s), PHP: 0.1800 s, total: 0.1990 s, document from database
 *
 * Разница со старым стилем (через isset) в пределах погрешности
 */

// Шаблоны
$ownerTPL = \APIhelpers::getkey($params, 'ownerTPL', null);
$noneTPL = \APIhelpers::getkey($params, 'noneTPL', null);
$tpl = \APIhelpers::getkey($params, 'tpl', '@CODE:<a href="[+url+]">[+e_title+]</a>');

// Параметры
$Qty = \APIhelpers::getkey($params, 'Qty', 2);
$prevQty = \APIhelpers::getkey($params, 'prevQty', $Qty);
$nextQty = \APIhelpers::getkey($params, 'nextQty', $Qty);
$noneWrapOuter = \APIhelpers::getkey($params, 'noneWrapOuter', 1);


$out = "";
$siblings = array();

$ID = $modx->documentIdentifier;

// мержим 'display' => '0' (выводить все док-ты), потому что за кол-во отвечает Qty, prevQty и nextQty
$params = array_merge( $params, array('debug' => '0', 'display' => '0') );
$time = array();
$time[] = microtime(true);
// Этот вызов ДокЛистера обрабатывает все наши параметры, кроме шаблонов и подстановки плейсхолдера [+sysKey.class+]
$json = $modx->runSnippet("DocLister", $params);
$time[] = microtime(true); // @NOTE:Отработка DL
$children = jsonHelper::jsonDecode($json, array('assoc' => true));
$children = is_array($children) ? $children : array(); // Тут проверка, что вернулся массив
$time[] = microtime(true); // @NOTE:Перевод JSON  в массив
$ids = array_keys($children); //Индексный массив ID в выборке (потом избавиться от него через prev-next?)
$time[] = microtime(true); // @NOTE:Создание индексного массива
$curIndex = array_search($ID, $ids); //Текущий индекс (индекс текущего ID)
$time[] = microtime(true); // @NOTE:Поиск текущего индекса
$count = count($ids); // Длина массива $ids
$lastIndex = $count - 1; // Последний индекс массива $ids
$time[] = microtime(true); // @NOTE:Вычисление длины индексного массива
$TPL = DLTemplate::getInstance($modx);

if($count-1 > 0) {// Если длина выборки (за исключением текущего элемента) больше 0

    if(($count - 1) <= $prevQty + $nextQty) { // Если длина выборки (за исключением текущего элемента) меньше нужного кол-ва
        // То просто выводим все элементы выборки
        for($i=0; $i<=$lastIndex; $i++) {
            $out .= ($curIndex == $i) ? "" : $TPL->parseChunk($tpl, $children[$ids[$i]]);
        }

    } else { // Иначе ищем соседей

        for($i=1; $i<=$prevQty; $i++) {

            /**
             * Для Prev
             * Если "перескока" в хвост нет, то индекс вычисляется как $curIndex - $i
             * Если из начала $ids перескочили в его хвост, то индекс считаем как $count + $curIndex - $i
             */
            $index = ($curIndex - $i >= 0) ? $curIndex - $i : $count + $curIndex - $i;

            // Формируем массив $siblings с теми же индексами и значениями, как у $ids ($ids уже упорядочен как надо)
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
         * До сортировки выглядит примерно так: Array ( [6] => 114, [0] => 18, [5] => 109, [1] => 95 )
         */
    $time[] = microtime(true); // @NOTE:Поиск соседей
        // Сортируем по индексам (ключам) этот небольшой массив $siblings (не более 8 элементов, а скорее всего 2+2 или 3+3)
        ksort($siblings);
    $time[] = microtime(true);  // @NOTE:Сортировка соседей
        /**
         * После сортировки выглядит так: Array ( [0] => 18, [1] => 95, [5] => 109, [6] => 114 )
         * Теперь он отсортирован точно так же, как и было в выходных данных ДокЛистера
         */

        /**
         * Выводим все элементы $siblings с шаблонизацией
         * $i - номер итерации начиная с 1
         */
        $i = 1;

        foreach($siblings as $value) {
            $iterationName = ($i % 2 == 1) ? 'Odd' : 'Even';

            // Какой шаблон выводить на этой итерации?
            // Идут сверху вниз по убыванию приоритета
            $renderTPL = $tpl;                                                              // tpl
            $renderTPL = \APIhelpers::getkey($params, 'tpl'.$iterationName, $renderTPL);    // tplOdd или tplEven
            $renderTPL = \APIhelpers::getkey($params, 'tplId'.$i, $renderTPL);              // tplIdN начиная с 1

            if ($i == 1) {
                $renderTPL = \APIhelpers::getkey($params, 'tplFirst', $renderTPL);          // tplFirst
            }
            if ($i == $prevQty + $nextQty) {
                $renderTPL = \APIhelpers::getkey($params, 'tplLast', $renderTPL);           // tplLast
            }

            $out .= $TPL->parseChunk($renderTPL, $children[$value]);

            $i++; // Увеличим $i на 1
        }

    }

    // Оборачиваем в ownerTPL, если он не null
    if( $ownerTPL )
        $out = $TPL->parseChunk( $ownerTPL, array('wrap' => $out) );

} else { // Если длина выборки (за исключением текущего элемента) <= 0 (нет элементов, кроме текущего, или вообще нет)

    // Далее копируем поведение ДокЛистер для параметра &noneWrapOuter и шаблонов &noneTPL и &ownerTPL

    // Если noneTPL не null, парсим его без параметров
    if( $noneTPL )
        $out = $TPL->parseChunk( $noneTPL, array() );

    // Если noneWrapOuter не 0, и ownerTPL не null
    if( $noneWrapOuter && $ownerTPL )
        // то распарсенный noneTPL оборачиваем в ownerTPL
        $out = $TPL->parseChunk( $ownerTPL, array('wrap' => $out) );

}
$time[] = microtime(true); // @NOTE:Шаблонизация

$intervalName = array();
$intervalName[] = "Отработка DL";
$intervalName[] = "Перевод JSON  в массив";
$intervalName[] = "Создание индексного массива";
$intervalName[] = "Поиск текущего индекса";
$intervalName[] = "Вычисление длины индексного массива";
$intervalName[] = "Поиск соседей";
$intervalName[] = "Сортировка соседей";
$intervalName[] = "Шаблонизация";


if ( ($length = count($time) - 1) == count($intervalName) ) {
    $info = "<h4>Тесты</h4>";
    $info .= "<p>&api = <b>`".$params['api']."`</b></p>";
    $info .= '<table class="table table-striped">';
    $info .= '<thead><tr><th>#</th><th>Этап</th><th>Время, сек</th></tr></thead>';
    $info .= "<tbody>";
    
    $interval = array();
    
    for ($i=0; $i <= $length - 1; $i++) {
        $num = $i + 1;
       $interval[] = round((float)$time[$i+1] - (float)$time[$i], 4);
       $info .= "<tr><th>$num</th><td>$intervalName[$i]</td><td>$interval[$i]</td></tr>";
    }
    $info .= "</tbody></table>";

    return $out.$info;  
} else {
    return "<h2>Длины массивов time и intervalName не совпадают!</h2>";
}
