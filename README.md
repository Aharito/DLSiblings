# DLSiblings

### Вывод соседних ресурсов с шаблонизацией (множественная кольцевая перелинковка)

Разработчик: [Aharito](https://aharito.ru/)

*Подробнее о кольцевой схеме перелинковки под НЧ-запросы [читайте здесь](https://aharito.ru/seo-prodvizhenie/shema-perelinkovki-stranic-sajta-pod-nch)*

## Параметры сниппета:
- **&prevQty** Кол-во соседей-предшественников, default 2
- **&nextQty** Кол-во соседей-последователей, default 2
- **все** остальные параметры и шаблоны **как в DocLister**

Можно использовать унаследованные от DocLister (такие же, как у него): условия выборки `&addWhereList` и  `&filters`, условия сортировки `&orderBy`, глубину выборки `&depth`, prepare-сниппеты и многие другие параметры и **все шаблоны**.

Нужно лишь понимать, имеют ли смысл эти параметры в вызове. Например, если задать **&idType=\`documents\`** и **&documents=\`1,2,3\`** (всего 3 документа), а **&prevQty** и **&prevQty** задать по 4 (в общем 8 соседей), то выводиться все равно будут только эти 3 документа - смысла в таком сочетании параметров нет.

### Параметры-исключения:
- параметр **&display** из DocLister здесь не имеет смсыла и не работает, так как за кол-во выводимых соседей отвечают параметры **&prevQty** и **&nextQty**.

## Шаблоны сниппета
Все шаблоны - точно такие же, **как у DocLister**. Плейсхолдеры в шаблонах - тоже точно такие же, **как в DocLister**.


## ПРИМЕР

Простой вызов сниппета.

	[[DLSiblings?
		&idType=`parents`
		&parents=`[*parent*]`
		&tpl=`@CODE: <a href="[+url+]">[+tv.h1+]</a><br>`
		&prevQty=`2`
		&nextQty=`2`
		&tvList=`h1`
	]]


### Результат работы

Результат работы более сложного вызова сниппета с выводом превьюшек, даты и заголовка может выглядеть примерно так:
![siblings_demo_1](https://user-images.githubusercontent.com/6253807/50377091-b58ff300-0649-11e9-8880-f2672927e4af.png)
