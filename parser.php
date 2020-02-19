<?php

require "config.php";
$groupId = @$_GET['group_id'];
$fields = 'bdate,city,contacts';
$apiVersion = '5.95';

$page = 0;
$limit = 1000;
$users = array();

$j = 0; // номер п/п

// Create and open file for writing
$filepath = 'exports/';
$filename = $groupId . '.csv';
$openFile = fopen($filepath . $filename, 'w');
fputs($openFile, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM (Byte Order Mark)
fputcsv($openFile, array("id", "Имя", "Дата рождения", "Город", "Мобильный"), ';');

echo "<link rel='stylesheet' href='css/table.css'>";
echo "<table><tr><th>№</th><th>id</th><th>Имя</th><th>Дата рождения</th><th>Город</th><th>Мобильный</th><th>Домашний</th></tr>";

do {
    $offset = $page * $limit;
    // получаем список пользователей
    $members = json_decode(file_get_contents("https://api.vk.com/method/groups.getMembers?group_id={$groupId}&offset={$offset}&count={$limit}&fields={$fields}&access_token={$token}&v={$apiVersion}"), true);

    for ($i = 0; $i < count($members['response']['items']); $i++) {

        //$users []= $user; //добавляем юзера к юзерам

        // отбираем пользователей у кого есть мобильный
        if (isset($members['response']["items"][$i]['mobile_phone'])) { // проверяем есть ли мобильный телефон
            $mobile_phones = explode(",", $members['response']["items"][$i]['mobile_phone']);
            foreach ($mobile_phones as $mobile_phone) {
                if (preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/', $mobile_phone)) {
                    echo "<tr>";
                    $j = $j + 1; // номер п/п
                    echo "<td>{$j}</td>";
                    $id = $members['response']['items'][$i]['id'];
                    echo "<td>id{$id}</td>";
                    $first_name = $members['response']['items'][$i]['first_name'];
                    $last_name = $members['response']['items'][$i]['last_name'];
                    echo "<td>{$first_name} {$last_name}</td>";
                    $bdate = $members['response']["items"][$i]['bdate'];
                    echo "<td>{$bdate}</td>";
                    //$city = $members['response']['items'][$i]['city'][2]['title'];
                    $city = $members['response']['items'][$i]['city'];
                    $city = $city['title'];
                    echo "<td>{$city}</td>";
                    $mobile_phone = $members['response']['items'][$i]['mobile_phone'];
                    //$mobile_phone = preg_replace('![^0-9]+!', '', $mobile_phone); // удаляем лишние сиволы
                    $mobile_phone = str_replace(array('+', ' ', '(', ')', '-'), '', $mobile_phone); // удаляем лишние сиволы
                    echo "<td>{$mobile_phone}</td>";
                    $home_phone = $members['response']["items"][$i]['home_phone'];
                    $home_phone = str_replace(array('+', ' ', '(', ')', '-'), '', $home_phone); // удаляем лишние сиволы
                    $home_phone = preg_replace('/^[а-яА-ЯёЁa-zA-Z]+$/', '', $home_phone); // удаляем буквы
                    echo "<td>{$home_phone}</td>";
                    //print_r($j." | "."id".$id." | ".$first_name." ".$last_name." | ".$bdate." | ".$city." | ".$mobile_phone." | ".$home_phone."<br>");
                    fputcsv($openFile, array("id" . $id, $first_name . " " . $last_name, $bdate, $city, $mobile_phone), ';');
                }
            }
        }
    }
    sleep(1);
    $page++; // увеличиваем страницу
} while ($members['response']['count'] > $offset + $limit);

echo "</table>";

// Close file
fclose($openFile);
echo "Путь к файлу экпорта https://vkphones.tk/" . $filepath . $filename;

//foreach ($users as $n => $user) // ходим по юзерам
//if(@$user['deactivated']) // и забаненных
//unset($users[$n]); // удаляем
