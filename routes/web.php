<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use PHPHtmlParser\Dom; //@todo del

Route::get('/', function () {
    return view('welcome');
});

//@todo Dom в фасад, иначе в тесте контреллера придется создавать ожидаемый объект, хотя важна только возврщаемая строка
Route::get('/getslots', function (Dom $dom) { //@todo унисти в контреллер, класс..
    //@todo controller для апи и view одно и тоже?
    //@todo 2023-02-20 00:44 там еще была 2023-02-19 Это проблема?
    $responseDates = Http::get(env('PLATFORM_FAVORITE_TUTOR'));
    $result = [];
    foreach (array_column($responseDates->json(), 'date') as $date) {
        //@todo кешировать, и перед бронью проверять, что действительно доступно
        $schedule = $dom->loadFromUrl(env('PLATFORM_SCHEDULE_FAVORITE_TUTOR') . "?date=$date");
        foreach ($schedule->getElementsByClass('slot-available') as $slot) {
            $result[$date][] = $slot->getTag()->getAttribute('data-slot_start')['value']; //@todo перевод времени в локальный пояс
        }
    }

    return $result;
});
